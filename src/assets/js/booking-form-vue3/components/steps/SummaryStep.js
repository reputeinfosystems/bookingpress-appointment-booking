/**
 * SummaryStep — customer block + appointment block + payment picker + total
 * + Book button.
 *
 * Markup mirrors the released `[bookingpress_form]` step-4 panel 1:1
 * (`core/views/frontend/appointment_booking_form.php` L791–L1015):
 *   - `.bpa-front-default-card` > `.bpa-front-dc--body` + `.bpa-front-dc--footer`.
 *   - `.bpa-front-module-container.bpa-front-module--booking-summary.bpa-fm__booking-summary-v47`.
 *     - `.bpa-front-module--bs-head` with the decorative SVG, heading, summary-content text, and note.
 *     - `.bpa-front-module--bs-summary-content.bpa-front-module--bs-customer-detail`
 *       (customer name → fallback chain firstname+lastname → email).
 *     - `.bpa-front-module--bs-summary-content.bpa-front-summary-content__lg` (desktop service + date/time pair).
 *     - `.bpa-front-module--bs-summary-content.bpa-front-summary-content__sm` (mobile stacked variant).
 *     - `.bpa-front-module--bs-amount-details` total row (only when price > 0).
 *   - `.bpa-front-module-container.bpa-front-module--payment-methods`
 *     (only when total > 0; iterates `state.config.payment_methods`).
 *     - `.bpa-front-module--pm-body__item.bpa_focusable` per gateway,
 *       `__bpa-is-selected` when current, with `.bpa-front-si-card--checkmark-icon` overlay.
 *   - `.bpa-front-dc--footer > .bpa-front-tabs--foot` with borderless Go Back
 *     + primary `.summery-book-appointment-btn` Book button (released class hook).
 *
 * The four slot anchors required by the §M9 Pro/add-on slot inventory
 * are preserved as invisible `<div class="bp-v3-slot" data-bp-v3-slot="…">`
 * elements — these are addon injection points, not visual chrome, so
 * they keep their `bp-v3-*` namespace.
 *
 * Submits via `useSubmission.submit()`.
 */
import { computed, inject, nextTick, onMounted, ref, watch } from 'vue';
import { useRovingTabindex } from '../../composables/useA11yNav.js?v=1';
import { formatPrice as formatPriceUtil } from '../../utils/currency.js';
import { formatDate as formatDateUtil, formatTime as formatTimeUtil } from '../../utils/datetime.js?v=2';
import { effectivePrice } from '../../utils/pricing.js?v=2';
import { payableAmount } from '../../utils/payable.js?v=1';
import { dayServiceEndDate, isSelectedDayService, selectedServiceDurationValue } from '../../utils/service.js?v=2';

// --- Inline SVGs from the released template -------------------------------

/** Filled check-circle painted on the selected payment-method card. */
const ICON_CHECKMARK =
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
    '<path d="M0 0h24v24H0V0z" fill="none"/>' +
    '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM9.29 16.29 5.7 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l6.88-6.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-7.59 7.59c-.38.39-1.02.39-1.41 0z"/>' +
  '</svg>';

/** Left-arrow on the "Go Back" footer button. */
const ICON_ARROW_LEFT =
  '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">' +
    '<rect fill="none" height="24" width="24"/>' +
    '<path d="M9.71,18.29L9.71,18.29c0.39-0.39,0.39-1.02,0-1.41L5.83,13H21c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5.83l3.88-3.88 c0.39-0.39,0.39-1.02,0-1.41l0,0c-0.39-0.39-1.02-0.39-1.41,0L2.71,11.3c-0.39,0.39-0.39,1.02,0,1.41l5.59,5.59 C8.68,18.68,9.32,18.68,9.71,18.29z"/>' +
  '</svg>';

/** Info-circle painted into the validation toast banner. */
const ICON_ERROR =
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
    '<path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"/>' +
  '</svg>';


/**
 * Decorative summary illustration painted above the head heading.
 * Copied verbatim from the released `appointment_booking_form.php`
 * L804–L832. Uses `.bpa-head__vector` / `.bpa-head__vector-item` class
 * hooks so the released theme stylesheet can apply primary-colour
 * fills to its inner shapes.
 */
const ICON_SUMMARY_HEAD_VECTOR =
  '<svg width="137" height="99" viewBox="0 0 137 99" fill="none" xmlns="http://www.w3.org/2000/svg" class="bpa-head__vector">' +
    // --- Top calendar card --------------------------------------------------
    '<path d="M15.8625 62.0651H97.6116C98.7623 62.0661 99.8656 62.5237 100.679 63.3374C101.493 64.1511 101.951 65.2543 101.952 66.4051V79.6535C101.951 80.8042 101.493 81.9075 100.679 82.7212C99.8656 83.5349 98.7623 83.9924 97.6116 83.9935H15.8625C14.7117 83.9924 13.6085 83.5349 12.7948 82.7212C11.9811 81.9075 11.5235 80.8042 11.5225 79.6535V66.4051C11.5235 65.2543 11.9811 64.1511 12.7948 63.3374C13.6085 62.5237 14.7117 62.0661 15.8625 62.0651Z" fill="#E9EDF5"/>' +
    '<path d="M15.7854 30.7217H97.5345C98.6852 30.7227 99.7885 31.1803 100.602 31.994C101.416 32.8077 101.873 33.911 101.875 35.0617V48.3101C101.873 49.4608 101.416 50.5641 100.602 51.3778C99.7885 52.1915 98.6852 52.6491 97.5345 52.6501H15.7854C14.6346 52.6491 13.5313 52.1915 12.7177 51.3778C11.904 50.5641 11.4464 49.4608 11.4454 48.3101V35.0617C11.4464 33.911 11.904 32.8077 12.7177 31.994C13.5313 31.1803 14.6346 30.7227 15.7854 30.7217ZM11.9022 48.3101C11.9037 49.3395 12.3133 50.3264 13.0412 51.0543C13.7691 51.7822 14.7559 52.1918 15.7854 52.1933H97.5345C98.5639 52.1918 99.5508 51.7822 100.279 51.0543C101.007 50.3264 101.416 49.3395 101.418 48.3101V35.0617C101.416 34.0322 101.007 33.0454 100.279 32.3175C99.5508 31.5896 98.5639 31.18 97.5345 31.1785H15.7854C14.7559 31.18 13.7691 31.5896 13.0412 32.3175C12.3133 33.0454 11.9037 34.0322 11.9022 35.0617V48.3101Z" fill="#535D71"/>' +
    '<path d="M26.9395 47.8533H66.2023C66.687 47.8533 67.1517 47.6608 67.4944 47.3181C67.8371 46.9754 68.0297 46.5106 68.0297 46.026C68.0297 45.5413 67.8371 45.0765 67.4944 44.7338C67.1517 44.3911 66.687 44.1986 66.2023 44.1986H26.9395C26.4548 44.1986 25.99 44.3911 25.6473 44.7338C25.3046 45.0765 25.1121 45.5413 25.1121 46.026C25.1121 46.5106 25.3046 46.9754 25.6473 47.3181C25.99 47.6608 26.4548 47.8533 26.9395 47.8533Z" fill="#E9EDF5"/>' +
    '<path d="M53.2079 40.0871H66.2023C66.687 40.0871 67.1517 39.8946 67.4944 39.5519C67.8371 39.2092 68.0297 38.7444 68.0297 38.2597C68.0297 37.7751 67.8371 37.3103 67.4944 36.9676C67.1517 36.6249 66.687 36.4324 66.2023 36.4324H53.2079C52.7232 36.4324 52.2584 36.6249 51.9157 36.9676C51.573 37.3103 51.3805 37.7751 51.3805 38.2597C51.3805 38.7444 51.573 39.2092 51.9157 39.5519C52.2584 39.8946 52.7232 40.0871 53.2079 40.0871Z" fill="#E9EDF5"/>' +
    '<path class="bpa-head__vector-item" d="M80.8983 49.24C82.3923 49.24 83.8527 48.797 85.095 47.9669C86.3372 47.1369 87.3054 45.9572 87.8771 44.5769C88.4489 43.1966 88.5985 41.6778 88.307 40.2125C88.0155 38.7472 87.2961 37.4012 86.2397 36.3448C85.1833 35.2884 83.8373 34.5689 82.372 34.2775C80.9067 33.986 79.3879 34.1356 78.0076 34.7073C76.6274 35.2791 75.4476 36.2472 74.6176 37.4895C73.7876 38.7317 73.3445 40.1921 73.3445 41.6861C73.3468 43.6888 74.1434 45.6088 75.5595 47.025C76.9756 48.4411 78.8956 49.2377 80.8983 49.24Z" />' +
    '<path d="M79.7164 44.3433C79.529 44.3436 79.3466 44.283 79.1966 44.1705L79.1874 44.1636L77.2295 42.6659C77.1386 42.5964 77.0623 42.5098 77.005 42.4108C76.9476 42.3118 76.9103 42.2025 76.8953 42.0892C76.8802 41.9758 76.8877 41.8606 76.9172 41.7501C76.9467 41.6396 76.9977 41.536 77.0673 41.4452C77.1369 41.3544 77.2237 41.2783 77.3227 41.2211C77.4218 41.1639 77.5311 41.1268 77.6445 41.1119C77.7579 41.097 77.8731 41.1046 77.9836 41.1343C78.094 41.164 78.1976 41.2152 78.2882 41.2849L79.5564 42.2574L82.553 38.3479C82.6225 38.2572 82.7092 38.1812 82.8081 38.124C82.9069 38.0669 83.0161 38.0298 83.1293 38.0148C83.2426 37.9999 83.3576 38.0073 83.468 38.0369C83.5783 38.0664 83.6817 38.1173 83.7724 38.1868L83.7726 38.187L83.754 38.2128L83.7731 38.187C83.956 38.3276 84.0756 38.5349 84.1058 38.7635C84.1359 38.9922 84.0742 39.2234 83.934 39.4066L80.4092 44.003C80.3277 44.1089 80.2229 44.1946 80.1028 44.2534C79.9828 44.3122 79.8509 44.3427 79.7172 44.3423L79.7164 44.3433Z" fill="white"/>' +
    // --- Bottom (smaller) card ----------------------------------------------
    '<path d="M50.9109 66.3674H132.66C133.811 66.3685 134.914 66.8261 135.728 67.6397C136.541 68.4534 136.999 69.5567 137 70.7074V83.9558C136.999 85.1066 136.541 86.2099 135.728 87.0235C134.914 87.8372 133.811 88.2948 132.66 88.2958H50.9109C49.7601 88.2948 48.6568 87.8372 47.8432 87.0235C47.0295 86.2099 46.5719 85.1066 46.5709 83.9558V70.7074C46.5719 69.5567 47.0295 68.4534 47.8432 67.6397C48.6568 66.8261 49.7601 66.3685 50.9109 66.3674Z" fill="white"/>' +
    '<path d="M50.9109 66.3674H132.66C133.811 66.3685 134.914 66.8261 135.728 67.6397C136.541 68.4534 136.999 69.5567 137 70.7074V83.9558C136.999 85.1066 136.541 86.2099 135.728 87.0235C134.914 87.8372 133.811 88.2948 132.66 88.2958H50.9109C49.7601 88.2948 48.6568 87.8372 47.8432 87.0235C47.0295 86.2099 46.5719 85.1066 46.5709 83.9558V70.7074C46.5719 69.5567 47.0295 68.4534 47.8432 67.6397C48.6568 66.8261 49.7601 66.3685 50.9109 66.3674ZM47.0277 83.9558C47.0292 84.9853 47.4388 85.9721 48.1667 86.7001C48.8946 87.428 49.8814 87.8375 50.9109 87.839H132.66C133.689 87.8375 134.676 87.428 135.404 86.7001C136.132 85.9721 136.542 84.9853 136.543 83.9558V70.7074C136.542 69.678 136.132 68.6911 135.404 67.9632C134.676 67.2353 133.689 66.8257 132.66 66.8243H50.9109C49.8814 66.8257 48.8946 67.2353 48.1667 67.9632C47.4388 68.6911 47.0292 69.678 47.0277 70.7074V83.9558Z" fill="#535D71"/>' +
    '<path d="M62.0646 83.4989H101.327C101.812 83.4989 102.277 83.3063 102.62 82.9636C102.962 82.6209 103.155 82.1561 103.155 81.6715C103.155 81.1868 102.962 80.722 102.62 80.3793C102.277 80.0366 101.812 79.8441 101.327 79.8441H62.0646C61.58 79.8441 61.1152 80.0366 60.7725 80.3793C60.4298 80.722 60.2373 81.1868 60.2373 81.6715C60.2373 82.1561 60.4298 82.6209 60.7725 82.9636C61.1152 83.3063 61.58 83.4989 62.0646 83.4989Z" fill="#E9EDF5"/>' +
    '<path d="M88.3324 75.7326H101.327C101.567 75.7326 101.804 75.6854 102.026 75.5935C102.248 75.5017 102.449 75.3671 102.619 75.1974C102.789 75.0277 102.923 74.8263 103.015 74.6046C103.107 74.3829 103.154 74.1452 103.154 73.9052C103.154 73.6653 103.107 73.4277 103.015 73.2059C102.923 72.9842 102.789 72.7828 102.619 72.6131C102.449 72.4434 102.248 72.3088 102.026 72.217C101.804 72.1252 101.567 72.0779 101.327 72.0779H88.3324C87.8478 72.0779 87.383 72.2704 87.0403 72.6131C86.6976 72.9558 86.5051 73.4206 86.5051 73.9052C86.5051 74.3899 86.6976 74.8547 87.0403 75.1974C87.383 75.5401 87.8478 75.7326 88.3324 75.7326Z" fill="#E9EDF5"/>' +
    '<path class="bpa-head__vector-item" d="M116.024 84.8853C117.518 84.8854 118.978 84.4423 120.22 83.6123C121.463 82.7823 122.431 81.6026 123.002 80.2223C123.574 78.842 123.724 77.3232 123.432 75.8579C123.141 74.3926 122.421 73.0466 121.365 71.9902C120.309 70.9338 118.963 70.2143 117.497 69.9229C116.032 69.6314 114.513 69.781 113.133 70.3527C111.753 70.9244 110.573 71.8926 109.743 73.1348C108.913 74.3771 108.47 75.8375 108.47 77.3315C108.472 79.3342 109.269 81.2542 110.685 82.6704C112.101 84.0865 114.021 84.8831 116.024 84.8853Z" />' +
    '<path d="M114.842 79.9884C114.654 79.9887 114.472 79.9281 114.322 79.8157L114.313 79.8087L112.355 78.311C112.264 78.2416 112.188 78.1549 112.13 78.0559C112.073 77.957 112.036 77.8477 112.021 77.7343C112.006 77.6209 112.013 77.5057 112.043 77.3952C112.072 77.2847 112.123 77.1811 112.193 77.0903C112.262 76.9996 112.349 76.9234 112.448 76.8662C112.547 76.809 112.656 76.7719 112.77 76.757C112.883 76.7421 112.998 76.7498 113.109 76.7795C113.219 76.8091 113.323 76.8603 113.414 76.93L114.682 77.9025L117.678 73.993C117.748 73.9024 117.835 73.8263 117.933 73.7692C118.032 73.712 118.141 73.6749 118.255 73.66C118.368 73.645 118.483 73.6525 118.593 73.682C118.704 73.7115 118.807 73.7625 118.898 73.832L118.879 73.858L118.898 73.8322C119.081 73.9727 119.201 74.18 119.231 74.4087C119.261 74.6373 119.2 74.8686 119.059 75.0517L115.535 79.6481C115.453 79.754 115.348 79.8397 115.228 79.8985C115.108 79.9574 114.976 79.9878 114.843 79.9874L114.842 79.9884Z" fill="white"/>' +
    // --- Floor / baseline ---------------------------------------------------
    '<path d="M32.7836 98.9999H0.260187C0.191181 98.9999 0.124997 98.9725 0.0762029 98.9237C0.0274083 98.8749 0 98.8087 0 98.7397C0 98.6707 0.0274083 98.6045 0.0762029 98.5557C0.124997 98.5069 0.191181 98.4795 0.260187 98.4795H32.7836C32.8526 98.4795 32.9188 98.5069 32.9676 98.5557C33.0164 98.6045 33.0438 98.6707 33.0438 98.7397C33.0438 98.8087 33.0164 98.8749 32.9676 98.9237C32.9188 98.9725 32.8526 98.9999 32.7836 98.9999Z" fill="#202C45"/>' +
    // --- Human character (head, body, arms, legs, phone) -------------------
    '<path d="M28.0083 17.5336C27.7067 17.724 27.4505 17.9782 27.2577 18.2783C27.065 18.5784 26.9404 18.9171 26.8927 19.2706C26.845 19.624 26.8754 19.9837 26.9818 20.3241C27.0881 20.6646 27.2678 20.9776 27.5082 21.2411L25.1438 26.2902L27.8616 28.4754L31.1005 21.2982C31.5164 20.865 31.759 20.2943 31.7822 19.6943C31.8054 19.0942 31.6077 18.5064 31.2266 18.0424C30.8454 17.5784 30.3073 17.2702 29.7141 17.1765C29.121 17.0827 28.514 17.2097 28.0083 17.5336V17.5336Z" fill="#FFB8B8"/>' +
    '<path d="M14.4212 19.5826C15.1694 19.1331 16.062 18.9889 16.9138 19.1798C17.7656 19.3708 18.5112 19.8822 18.996 20.6081L23.8341 27.8524L25.5138 24.1495C25.6186 23.9183 25.8044 23.7334 26.0361 23.6296C26.2679 23.5258 26.5295 23.5103 26.7719 23.586L29.307 24.3779C29.4394 24.4193 29.5622 24.4867 29.6682 24.5763C29.7742 24.6658 29.8613 24.7756 29.9242 24.8993C29.9871 25.023 30.0247 25.158 30.0347 25.2964C30.0447 25.4348 30.0269 25.5737 29.9824 25.7052L28.2471 30.8294C28.0275 31.4776 27.6608 32.0661 27.1755 32.5487C26.6903 33.0313 26.0998 33.3948 25.4505 33.6108C24.8011 33.8268 24.1105 33.8894 23.4329 33.7935C22.7552 33.6977 22.1091 33.4461 21.545 33.0586C21.2815 32.8774 21.0383 32.6683 20.8198 32.4348L13.69 24.8167C13.3438 24.4468 13.0847 24.0041 12.9317 23.521C12.7787 23.038 12.7356 22.5269 12.8057 22.0251C12.8758 21.5233 13.0573 21.0435 13.3368 20.6209C13.6163 20.1982 13.9868 19.8435 14.4212 19.5826Z" fill="#CFD6E6"/>' +
    '<path d="M30.0722 12.0974C30.202 12.1007 30.3253 12.1553 30.4148 12.2495C30.5044 12.3436 30.5529 12.4694 30.5496 12.5992L30.5079 14.2807L30.5922 14.2884L30.5619 15.4305L30.4796 15.4202L30.3299 21.4442C30.3248 21.6503 30.2449 21.8476 30.105 21.9992C29.9651 22.1507 29.7749 22.2462 29.5697 22.2678L25.7393 22.6711C25.6461 22.6809 25.5518 22.6702 25.4631 22.6397C25.3744 22.6092 25.2935 22.5597 25.2259 22.4947C25.1584 22.4296 25.1059 22.3506 25.0721 22.2631C25.0384 22.1756 25.0242 22.0818 25.0305 21.9882L25.6115 13.4105C25.6237 13.2303 25.6968 13.0597 25.8189 12.9266C25.9409 12.7935 26.1046 12.7059 26.283 12.6782L29.9808 12.1037C30.011 12.0988 30.0416 12.0967 30.0722 12.0974Z" fill="#202C45"/>' +
    '<path d="M27.1076 12.9902L29.0515 12.6882C29.0865 12.6827 29.1187 12.6659 29.1432 12.6403C29.1677 12.6147 29.183 12.5818 29.1869 12.5466C29.1912 12.5067 29.2086 12.4694 29.2363 12.4404C29.264 12.4114 29.3005 12.3923 29.3401 12.3862L29.7496 12.3226C29.8055 12.3139 29.8626 12.3176 29.9169 12.3334C29.9712 12.3492 30.0214 12.3767 30.0638 12.414C30.1063 12.4513 30.1401 12.4975 30.1628 12.5493C30.1856 12.601 30.1966 12.6572 30.1953 12.7137L29.9848 21.4474C29.9822 21.5558 29.9397 21.6594 29.8656 21.7385C29.7915 21.8176 29.6908 21.8667 29.5828 21.8762L25.7552 22.2166C25.7064 22.2209 25.6572 22.2146 25.611 22.1982C25.5648 22.1818 25.5227 22.1556 25.4875 22.1215C25.4524 22.0873 25.425 22.0459 25.4073 22.0002C25.3896 21.9545 25.382 21.9055 25.385 21.8566L25.9014 13.3581C25.9091 13.2329 25.9593 13.1141 26.0437 13.0213C26.1282 12.9286 26.2418 12.8675 26.3658 12.8483L26.7975 12.7812C26.8133 12.8487 26.8541 12.9077 26.9116 12.9464C26.9691 12.9852 27.0391 13.0008 27.1076 12.9902Z" fill="white"/>' +
    '<path d="M18.0885 95.8415L21.063 95.8413L22.4776 84.368L18.0875 84.3686L18.0885 95.8415Z" fill="#FFB8B8"/>' +
    '<path d="M26.8003 98.603L17.4519 98.6039L17.4515 94.992L23.1881 94.9914C23.6624 94.9914 24.1321 95.0848 24.5703 95.2663C25.0086 95.4477 25.4068 95.7137 25.7422 96.0491C26.0776 96.3845 26.3437 96.7826 26.5253 97.2208C26.7068 97.659 26.8003 98.1287 26.8003 98.603L26.8003 98.603Z" fill="#202C45"/>' +
    '<path d="M8.94684 95.8415L11.9213 95.8413L13.3359 84.368L8.9458 84.3686L8.94684 95.8415Z" fill="#FFB8B8"/>' +
    '<path d="M17.6581 98.603L8.30959 98.6039L8.30927 94.992L14.0458 94.9914C15.0038 94.9914 15.9225 95.3718 16.5999 96.0491C17.2773 96.7264 17.658 97.6451 17.6581 98.603L17.6581 98.603Z" fill="#202C45"/>' +
    '<path d="M17.6582 15.6833C21.1875 15.6833 24.0486 12.8222 24.0486 9.29281C24.0486 5.76345 21.1875 2.90234 17.6582 2.90234C14.1288 2.90234 11.2677 5.76345 11.2677 9.29281C11.2677 12.8222 14.1288 15.6833 17.6582 15.6833Z" fill="#FFB8B8"/>' +
    '<path d="M20.9469 26.3904C20.8947 24.7474 20.4322 23.1436 19.6014 21.7251C18.7707 20.3067 17.5982 19.1187 16.1908 18.2694C14.5398 17.2965 12.7327 16.9171 11.295 18.3548C10.4317 19.2397 9.74034 20.2774 9.25615 21.4149C7.92622 24.5147 7.83921 28.0072 9.01314 31.1694L12.435 40.623L20.3549 41.481C20.5036 41.4971 20.6541 41.481 20.7961 41.4337C20.938 41.3863 21.0681 41.309 21.1775 41.2068C21.2868 41.1047 21.3728 40.9802 21.4297 40.8417C21.4865 40.7033 21.5129 40.5543 21.5069 40.4048L20.9469 26.3904Z" fill="#535D71"/>' +
    '<path d="M12.9372 39.4589C12.9372 39.4589 7.02024 41.6678 8.56723 51.0212C9.95541 59.4144 8.85224 87.273 8.61112 92.9044C8.59964 93.1728 8.6925 93.4352 8.87023 93.6367C9.04796 93.8382 9.29678 93.963 9.56452 93.9851L13.0185 94.2729C13.289 94.2955 13.5577 94.2114 13.7671 94.0387C13.9765 93.866 14.1102 93.6183 14.1396 93.3484L15.9234 76.9635C15.9304 76.8992 15.9611 76.8398 16.0095 76.7969C16.0579 76.754 16.1206 76.7307 16.1852 76.7314C16.2499 76.7322 16.312 76.7571 16.3593 76.8011C16.4067 76.8452 16.4359 76.9053 16.4413 76.9698L17.7706 92.7359C17.7929 93.0015 17.9164 93.2484 18.1155 93.4257C18.3146 93.603 18.5741 93.6971 18.8405 93.6887L21.7417 93.5969C21.8784 93.5926 22.0129 93.5613 22.1375 93.505C22.2622 93.4486 22.3745 93.3683 22.468 93.2685C22.5616 93.1687 22.6345 93.0514 22.6827 92.9234C22.7309 92.7954 22.7534 92.6592 22.7489 92.5225L21.04 40.5514L12.9372 39.4589Z" fill="#535D71"/>' +
    '<path d="M18.9407 8.8717C21.0779 9.74826 23.8073 8.72431 24.8406 6.65829C25.874 4.59227 25.0562 1.79419 23.0729 0.609824C21.0896 -0.574543 18.2383 0.0325306 16.9095 1.9221C15.8286 -0.0252986 12.9969 -0.521775 11.0526 0.564842C9.10839 1.65146 8.04938 3.92749 7.92378 6.15122C7.79818 8.37495 8.47424 10.5656 9.29896 12.6346C10.633 15.9813 14.9276 17.7833 18.2502 16.3905C16.7291 14.125 17.0294 10.8096 18.9407 8.8717Z" fill="#202C45"/>' +
    '<path class="bpa-head__vector-item" d="M27.5531 18.3085C27.4963 18.3086 27.4409 18.2902 27.3954 18.256L27.3926 18.2539L26.7984 17.7994C26.7709 17.7783 26.7478 17.752 26.7304 17.722C26.7131 17.6919 26.7018 17.6588 26.6973 17.6244C26.6927 17.5901 26.695 17.5551 26.704 17.5216C26.7129 17.4881 26.7284 17.4567 26.7495 17.4292C26.7706 17.4017 26.7969 17.3786 26.8269 17.3612C26.857 17.3439 26.8901 17.3326 26.9245 17.3281C26.9589 17.3235 26.9938 17.3258 27.0273 17.3348C27.0608 17.3437 27.0922 17.3592 27.1197 17.3803L27.5045 17.6754L28.414 16.489C28.4351 16.4615 28.4614 16.4384 28.4914 16.421C28.5214 16.4037 28.5545 16.3924 28.5889 16.3879C28.6232 16.3834 28.6582 16.3856 28.6916 16.3946C28.7251 16.4035 28.7565 16.419 28.784 16.4401L28.7841 16.4402L28.7784 16.448L28.7842 16.4402C28.8397 16.4828 28.876 16.5457 28.8852 16.6151C28.8943 16.6845 28.8756 16.7547 28.8331 16.8103L27.7634 18.2052C27.7386 18.2373 27.7068 18.2633 27.6704 18.2812C27.634 18.299 27.5939 18.3082 27.5534 18.3081L27.5531 18.3085Z" />' +
    '<path d="M25.5268 30.6751C25.1983 30.5362 24.8433 30.471 24.4869 30.4841C24.1305 30.4972 23.7812 30.5882 23.4638 30.7509C23.1464 30.9136 22.8685 31.1438 22.6497 31.4255C22.4309 31.7072 22.2765 32.0334 22.1974 32.3812L16.7021 33.3226L16.4597 36.8016L24.2014 35.3632C24.7945 35.4573 25.4015 35.3306 25.9074 35.0071C26.4133 34.6836 26.783 34.1857 26.9464 33.6079C27.1098 33.03 27.0555 32.4123 26.7938 31.8718C26.5322 31.3312 26.0814 30.9055 25.5268 30.6751L25.5268 30.6751Z" fill="#FFB8B8"/>' +
    '<path d="M16.0873 20.6895C16.8836 21.0473 17.5116 21.6978 17.8411 22.5061C18.1706 23.3144 18.1764 24.2186 17.8572 25.031L14.6718 33.139L18.6708 32.404C18.9205 32.3581 19.1784 32.4051 19.3959 32.5361C19.6134 32.667 19.7755 32.873 19.8517 33.1152L20.6489 35.6486C20.6905 35.781 20.7053 35.9203 20.6923 36.0585C20.6793 36.1966 20.6388 36.3308 20.5732 36.453C20.5076 36.5753 20.4182 36.6832 20.3103 36.7705C20.2024 36.8577 20.0781 36.9224 19.9448 36.9609L14.7471 38.4617C14.0896 38.6515 13.397 38.6864 12.7238 38.5635C12.0505 38.4407 11.4149 38.1635 10.8668 37.7536C10.3188 37.3438 9.87314 36.8125 9.56495 36.2015C9.25676 35.5905 9.09439 34.9163 9.09058 34.232C9.0888 33.9122 9.12167 33.5931 9.18861 33.2804L11.3726 23.0775C11.4787 22.582 11.6942 22.1166 12.0034 21.7152C12.3127 21.3138 12.7077 20.9867 13.1597 20.7577C13.6118 20.5288 14.1093 20.4038 14.6158 20.392C15.1224 20.3802 15.6251 20.4819 16.0873 20.6895Z" fill="#CFD6E6"/>' +
  '</svg>';

export default {
  name: 'SummaryStep',
  setup() {
    const state      = inject('state');
    const nav        = inject('nav');
    const submission = inject('submission');
    const readiness  = inject('readiness');

    const selectedService = computed(() => {
      const id = parseInt(state.appointment_step_form_data.selected_service || 0, 10);
      return state.services.find(s => parseInt(s.serviceId, 10) === id) || null;
    });

    // Generic multi-select names — inert in Lite (`selected_services` empty → just
    // the single service). When a multi-select add-on (Multi Service Booking)
    // populates it, the Summary "Service" row lists every selected service name
    // (legacy multi-service parity).
    const summaryServiceNames = computed(() => {
      const fd = state.appointment_step_form_data || {};
      const ids = Array.isArray(fd.selected_services)
        ? fd.selected_services.map((x) => parseInt(x, 10)).filter((n) => n > 0)
        : [];
      if (ids.length >= 2) {
        const svcs = Array.isArray(state.services) ? state.services : [];
        return ids.map((id) => {
          const s = svcs.find((x) => parseInt(x.serviceId, 10) === id);
          return s ? s.serviceName : ('#' + id);
        });
      }
      return selectedService.value ? [selectedService.value.serviceName] : [];
    });

    // `selected_date` is stored as the canonical "YYYY-MM-DD" used for backend
    // queries; format it for display per the admin "Date format" setting
    // (`state.config.dateFormat`). Released-form parity with the vue2
    // `| bookingpress_format_date` filter. Empty when no date is picked yet so
    // the template's `|| '—'` fallback still applies.
    const selectedDateLabel = computed(() => {
      // Overnight (Pro): when the picked slot's window runs into the next day,
      // `selected_actual_date` holds the day the appointment really falls on
      // (e.g. 16 Jun for a 01:00–02:00 slot picked under 15 Jun), so the Summary
      // matches what the backend stores. Falls back to the calendar
      // `selected_date` for plain slots (and for Lite, where it is never set).
      const ymd = state.appointment_step_form_data.selected_actual_date
        || state.appointment_step_form_data.selected_date;
      return ymd ? formatDateUtil(state.config, ymd) : '';
    });

    const appointmentDateTimeLabel = computed(() => {
      const fd = state.appointment_step_form_data || {};
      if (isSelectedDayService(state)) {
        const start = String(fd.selected_date || '');
        if (!start) return '';
        const duration = Math.max(1, selectedServiceDurationValue(state) || 1);
        if (duration <= 1) return formatDateUtil(state.config, start);
        const end = fd.selected_end_date || dayServiceEndDate(start, duration);
        return end
          ? `${formatDateUtil(state.config, start)} - ${formatDateUtil(state.config, end)}`
          : formatDateUtil(state.config, start);
      }
      // `selected_start_time` / `selected_end_time` hold the raw 24-hour
      // "HH:MM" the backend parses; re-apply the admin time format
      // (`config.phpTimeFormat`) for display so the Summary matches the
      // slot label the user picked (12h/24h/WP-inherit).
      const startLabel = fd.selected_start_time ? formatTimeUtil(state.config, fd.selected_start_time) : '—';
      const endLabel   = fd.selected_end_time   ? formatTimeUtil(state.config, fd.selected_end_time)   : '—';
      return `${selectedDateLabel.value || '—'}, ${startLabel} - ${endLabel}`;
    });

    function formatPrice(p) {
      return formatPriceUtil(state.config, p);
    }

    const total = computed(() => {
      if (!selectedService.value) return 0;
      // Generic recompute dependency: a Pro price filter (e.g. staff pricing)
      // keys off the selected staff, which lives outside this computed's natural
      // deps — read it so the total re-evaluates when it changes. Inert in Lite.
      // eslint-disable-next-line no-unused-expressions
      state.appointment_step_form_data.selected_staff_member_id;
      const base = Number(selectedService.value.servicePrice) || 0;
      return effectivePrice(state, base, selectedService.value.serviceId);
    });

    // The amount actually charged NOW (order total minus any prepaid tender /
    // partial-payment feature: deposit, coupon-to-zero, redeemed gift card / package).
    // Runs the generic `bookingpress_form_v3_payable_amount` seam. When this is 0
    // there is nothing to pay, so the whole payment-gateway area is suppressed.
    const payableNow = computed(() => {
      if (!selectedService.value) return 0;
      return payableAmount(state, total.value, selectedService.value.serviceId);
    });

    // The order cost BEFORE any discount — service (+ staff price override,
    // Service Extras, Multiple Quantity) but WITHOUT coupon / package / points /
    // offer-discount. Resolved via the dedicated `'subtotal'` price context: the
    // discount-type effective_price consumers opt out of it, so this yields the
    // single "Subtotal" row at the top of the amount block (#4). Lite alone
    // returns the plain service price (no consumers), so subtotal === total.
    const subtotal = computed(() => {
      if (!selectedService.value) return 0;
      // eslint-disable-next-line no-unused-expressions
      state.appointment_step_form_data.selected_staff_member_id;
      const base = Number(selectedService.value.servicePrice) || 0;
      return effectivePrice(state, base, selectedService.value.serviceId, 'subtotal');
    });

    // Show the Subtotal row only when the amount block will carry a breakdown:
    // either the running payable already differs from the pre-discount subtotal
    // (a discount / tax / tip / deposit is in effect) OR an amount-modifier
    // module is enabled (so its box/line renders even before a value applies —
    // legacy shows the Subtotal whenever such a module is present). Otherwise the
    // block shows the Total row alone (Lite: plain service price).
    const summaryModuleEnabled = computed(() => {
      const c = state.config || {};
      return !!(
        c.bookingpress_coupon_enabled || c.bookingpress_package_enabled
        || c.bookingpress_deposit_enabled || c.bookingpress_tax_enabled
        || c.bookingpress_tip_enabled || c.bookingpress_gift_card_enabled
        || c.bookingpress_discount_enabled || c.bookingpress_mycred_enabled
      );
    });
    const showSubtotal = computed(() =>
      subtotal.value > 0
      && (summaryModuleEnabled.value
        || Math.round(subtotal.value * 100) !== Math.round(payableNow.value * 100))
    );

    const customerName = computed(() => {
      const f = state.appointment_step_form_data;
      const full = (f.customer_name || `${f.customer_firstname || ''} ${f.customer_lastname || ''}`).trim();
      return full || '—';
    });

    const customerEmail = computed(() => state.appointment_step_form_data.customer_email || '—');
    const customerPhone = computed(() => state.appointment_step_form_data.customer_phone || '');

    const bus = inject('bus', null);
    const selectedPaymentMethod = computed({
      get() { return String(state.appointment_step_form_data.selected_payment_method || ''); },
      set(v) {
        state.appointment_step_form_data.selected_payment_method = String(v);
        // Per plan §3.3.
        bus && bus.emit('bp-v3:payment-method-selected', {
          instanceId: state.instanceId,
          method: String(v),
        });
      },
    });

    function next() { /* Summary has Book, not Next. */ }
    function prev() { nav.goPrev(); }

    const api = inject('api');

    /** True when the selected gateway is PayPal — drives the alternate
     *  button area and the PayPal-specific click flow. */
    const isPayPalSelected = computed(
      () => 'paypal' === String(selectedPaymentMethod.value)
    );

    /** Resolved payment-method object for the current selection. The
     *  released PaymentService emits each method with a `mode` field
     *  (`'popup'` | `'redirect'` for PayPal, `'on_site'` for on-site).
     *  Lookup is by id against `state.config.payment_methods`. */
    const selectedMethodObj = computed(() => {
      const methods = (state.config && state.config.payment_methods) || [];
      return methods.find(m => String(m.id) === String(selectedPaymentMethod.value)) || null;
    });

    /** True only when PayPal is selected AND the configured mode is
     *  `popup`. Drives the empty `#paypal-button-container` (SDK render
     *  target) and hides the normal Book button. PayPal-redirect mode
     *  keeps the normal Book button (legacy parity — clicking it runs
     *  the validate → redirect_url flow in `book()`). */
    const isPayPalPopupSelected = computed(
      () => isPayPalSelected.value
        && selectedMethodObj.value
        && 'popup' === String(selectedMethodObj.value.mode)
        // Nothing to pay → no PayPal button; the normal Book button finalises the
        // free booking inline.
        && payableNow.value > 0
    );

    /**
     * True while the PayPal JS SDK is initializing (popup mode only).
     * Starts false in M3 (stub). M4 flips this to true on mount when
     * popup mode is configured, then back to false once SDK is ready
     * and has rendered into #paypal-button-container.
     */
    const paypalButtonLoading = ref(false);

    /**
     * Render the PayPal JS SDK buttons into `#paypal-button-container` —
     * port of legacy `bookingpress_after_selecting_payment_method_data`
     * (booking-form.js:1772-1850). Invoked only when PayPal popup mode
     * is the active selection. The SDK script is enqueued server-side
     * by `bookingpress_paypal_scripts_add` when popup mode is on, so
     * `window.paypal` is expected to exist by the time this runs.
     *
     * Defensive: no-ops if the SDK is missing, the container has not
     * mounted yet, or `paypal.Buttons` throws during init.
     */
    function renderPayPalButtons() {
      if (typeof window === 'undefined' || !window.paypal || typeof window.paypal.Buttons !== 'function') {
        return;
      }
      const container = document.getElementById('paypal-button-container');
      if (!container) return;
      // Wipe any previous render before re-mounting — re-entrant safe.
      container.innerHTML = '';
      try {
        window.paypal.Buttons({
          createOrder: async () => {
            // Clear any prior error before a fresh attempt.
            submission.submitError.value = '';
            try {
              // Mirror useSubmission.js — send the full appointment_step_form_data
              // bucket plus the resolved service price hint so the server's
              // anti-tamper price check matches the picked service.
              const selectedId = parseInt(state.appointment_step_form_data.selected_service || 0, 10);
              const svc        = state.services.find(s => parseInt(s.serviceId, 10) === selectedId);
              const full       = svc ? effectivePrice(state, svc.servicePrice, svc.serviceId) : 0;
              // Charge the amount payable now (deposit when active; full otherwise).
              const price      = payableAmount(state, full, selectedId);
              const resp = await api.paypalValidate({
                ...state.appointment_step_form_data,
                service_price_without_currency: price,
              });
              if (resp && resp.ok && resp.data && resp.data.order_id) {
                if (resp.data.paypal_success_url) state.paypal_success_url = resp.data.paypal_success_url;
                if (resp.data.paypal_cancel_url)  state.paypal_cancel_url  = resp.data.paypal_cancel_url;
                if (resp.data.entry_id)           state.paypal_entry_id    = resp.data.entry_id;
                return resp.data.order_id;
              }
              // Surface the structured server error verbatim — the user
              // needs to see why the booking did NOT get staged.
              const msg = (resp && resp.error && resp.error.message) || 'Failed to create PayPal order';
              submission.submitError.value = msg;
              return 0;
            } catch (e) {
              submission.submitError.value = (e && e.message) || 'Failed to create PayPal order';
              return 0;
            }
          },
          onCancel: function () {
            // Legacy parity: PayPal popup cancel is a soft no-op — the
            // stage-1 entry stays as pending_payment and the user can
            // retry. We surface a hint so they're not confused.
            submission.submitError.value = 'PayPal payment was cancelled. You can retry below.';
          },
          onApprove: (data, actions) => {
            return actions.order.capture().then(async (orderData) => {
              try {
                // The server identifies the entry via PayPal's verified
                // reference_id — only the capture body is required.
                const resp = await api.paypalConfirm({
                  bookingpress_payment_res: orderData,
                });
                if (resp && resp.ok) {
                  // finalize_booking() returns `redirect_data` (the v3
                  // envelope key) alongside the legacy `redirect_url`
                  // fallback. Prefer the server-issued URL — the entry
                  // is now a real booking. Fall back to the validate
                  // success_url only if finalize did not provide one.
                  const url = (resp.data && (resp.data.redirect_data || resp.data.redirect_url)) || state.paypal_success_url || '';
                  if (url) {
                    submission.redirectUrl.value = url;
                    submission.submitOk.value    = true;
                    state.submitOk    = true;
                    state.redirectUrl = url;
                    window.location.href = url;
                  } else {
                    // Booking finalised but no redirect URL — surface a
                    // non-error message so the user knows the payment
                    // succeeded.
                    submission.submitOk.value = true;
                    state.submitOk = true;
                  }
                } else {
                  // Server REFUSED to finalize — strict mode: no booking
                  // is treated as successful. Show the server's message
                  // so the user knows the payment did not finalize.
                  submission.submitError.value =
                    (resp && resp.error && resp.error.message)
                    || 'Payment could not be confirmed. Please contact the site owner.';
                }
              } catch (e) {
                submission.submitError.value = (e && e.message) || 'Payment failed';
              }
            });
          },
          style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'paypal', fundingicons: false },
        }).render('#paypal-button-container');
      } catch (e) {
        // SDK threw during init — leave container empty; the user can
        // re-select to retry. We do NOT silently fall back to a fake
        // "Book" button (that was the v5 bug).
        console.warn('[bp-v3 SummaryStep] paypal.Buttons render threw:', e);
      }
    }

    /** Inject the PayPal Standard ("Legacy") auto-submit form returned by
     *  `paypal-redirect-prepare` and hand the browser off to paypal.com.
     *  The server markup includes a trailing <script> that self-submits, but
     *  HTML assigned via innerHTML never executes its <script>, so we locate
     *  the form and submit it explicitly. */
    function submitPayPalRedirectForm(html) {
      if (typeof document === 'undefined' || !html) return false;
      const holder = document.createElement('div');
      holder.style.display = 'none';
      holder.innerHTML = String(html);
      document.body.appendChild(holder);
      const form = holder.querySelector('form')
        || document.getElementById('bookingpress_paypal_form');
      if (form && typeof form.submit === 'function') {
        form.submit();
        return true;
      }
      return false;
    }

    /** Clear any previously-rendered SDK content from the container.
     *  Used when the user switches away from PayPal popup mode. */
    function clearPayPalContainer() {
      if (typeof document === 'undefined') return;
      const c = document.getElementById('paypal-button-container');
      if (c) c.innerHTML = '';
    }

    // Trigger SDK render after Vue has updated the DOM (the container
    // is gated by `isPayPalPopupSelected` in the template, so it does
    // not exist until the watcher fires).
    watch(isPayPalPopupSelected, (val) => {
      if (val) {
        nextTick(() => { renderPayPalButtons(); });
      } else {
        clearPayPalContainer();
      }
    });
    onMounted(() => {
      if (isPayPalPopupSelected.value) {
        nextTick(() => { renderPayPalButtons(); });
      }
    });

    // Toast error surfaced when the user clicks "Book Appointment"
    // without satisfying a readiness gate — released-form parity with
    // the Service / Date-Time / Basic-Details steps. Auto-dismisses
    // after 5s to match the other steps' UX.
    const errorMsg = ref('');
    let errorTimer = 0;

    function setError(msg) {
      errorMsg.value = String(msg || '');
      if (errorTimer) clearTimeout(errorTimer);
      errorTimer = 0;
      if (errorMsg.value) {
        errorTimer = setTimeout(() => { errorMsg.value = ''; errorTimer = 0; }, 5000);
      }
    }

    function clearError() {
      if (errorTimer) clearTimeout(errorTimer);
      errorTimer = 0;
      errorMsg.value = '';
    }

    /**
     * Resolve the configured terms-and-conditions error message from
     * `state.customer_details_rule`. The terms field's rule is built by
     * `StateBuilder::build_field_rules()` with the field's
     * `fieldErrorMessage` (or its label as fallback) — matching the
     * released form. Returns '' when no terms field is required.
     */
    function termsErrorMessage() {
      const termsField = (state.customer_form_fields || [])
        .find(f => f.fieldName === 'terms_and_conditions');
      if (!termsField || !termsField.fieldRequired) return '';
      const rules = (state.customer_details_rule || {})[termsField.vModelValue] || [];
      const required = rules.find(r => r.required);
      return (required && required.message) || '';
    }

    async function book() {
      if (submission.isSubmitting.value) return;

      // Client-side validation — surface the configured admin message on
      // the top-of-card toast instead of a silent no-op (legacy form
      // parity). The server re-runs every gate; this is purely UX.
      if (!readiness.gateService.value) {
        setError(state.strings.no_service_selected_error);
        return;
      }
      if (!readiness.gateDatetime.value) {
        setError(
          state.appointment_step_form_data.selected_date
            ? state.strings.no_appointment_time_error
            : state.strings.no_appointment_date_error
        );
        return;
      }
      if (!readiness.gateBasicDetails.value) {
        // Defensive — Basic Details' own next() should prevent this, but
        // surface a hint if the user navigated back and cleared a field.
        setError(state.strings.basic_details_step_name
          ? ('Please complete ' + state.strings.basic_details_step_name + '.')
          : 'Please complete the required details.');
        return;
      }
      if (!readiness.gatePayment.value) {
        setError(state.strings.no_payment_method_picked);
        return;
      }
      if (!readiness.gateTerms.value) {
        setError(termsErrorMessage() || 'Please accept the terms and conditions.');
        return;
      }

      clearError();

      // PayPal REDIRECT ("Legacy") branch: the server stages the booking as
      // `pending_payment`, then we ask it to build the PayPal Standard
      // auto-submit form and hand the browser off to paypal.com. The booking
      // is finalized asynchronously by the IPN listener once PayPal confirms
      // the payment. (Popup mode never reaches book() — its SDK smart button
      // in #paypal-button-container drives createOrder/onApprove instead.)
      if (isPayPalSelected.value) {
        const env = await submission.submit();
        // submission.submit() returns the raw envelope for the
        // pending_payment variant (and `false` / `true` for the others).
        if (env && typeof env === 'object' && 'entry_id' in env) {
          try {
            const resp = await api.paypalRedirectPrepare({
              entry_id: env.entry_id,
            });
            // Success: inject the returned auto-submit form and redirect.
            if (resp && resp.ok && resp.data && resp.data.redirect_data) {
              if (submitPayPalRedirectForm(resp.data.redirect_data)) {
                submission.submitOk.value = true;
                state.submitOk = true;
                return;
              }
              submission.submitError.value =
                'Could not open PayPal. Please try again or contact the site owner.';
              return;
            }
            // Non-OK — surface the server error in the page toast so the
            // user knows the booking is not yet paid for.
            const msg = (resp && resp.error && resp.error.message)
              || 'PayPal payment is not available right now. Please contact the site owner.';
            submission.submitError.value = msg;
          } catch (e) {
            submission.submitError.value =
              (e && e.message) || 'PayPal request failed.';
          }
        } else if (env && typeof env === 'object') {
          // pending_payment envelope arrived without entry_id — surface it
          // so the user is not silently stuck with a non-advancing form.
          submission.submitError.value = 'Booking could not be staged. Please try again.';
        }
        return;
      }

      // Default flow (on-site / zero-price): the server finalises inline.
      await submission.submit();
    }

    // --- Tiny template helpers ------------------------------------------

    /**
     * Mirrors the released gate
     *   `service_price_without_currency != '0' && is_only_onsite_enabled != '1'`
     * via primitives we already have. Hides the payment-methods block when
     * the service is free OR when only a single gateway is enabled
     * (released parity: with one method there's nothing to choose, so the
     * picker is suppressed and the only method is pre-selected silently
     * by the auto-select effect below).
     */
    const showPaymentBlock = computed(() => {
      const methods = (state.config && state.config.payment_methods) || [];
      // Gate on the amount payable NOW, not the order total: a prepaid-tender
      // feature (e.g. a redeemed Gift Card) can drop the payable to 0 while the
      // total stays > 0 — there is then nothing to pay, so the gateways hide
      // (mirrors the readiness gate's own payable-based zero bypass). Lite has no
      // such feature (payable === total), so this is unchanged for Lite; deposit
      // keeps payable > 0 (gateways still shown); coupon-zero / free services drop
      // the total to 0 too, so they behave exactly as before.
      return payableNow.value > 0 && methods.length > 1;
    });

    /**
     * Auto-select the only enabled payment method (released parity:
     * when only on-site OR only paypal is enabled the form sets
     * `selected_payment_method` silently so the readiness gate passes
     * and Book is clickable without the user ever seeing a picker).
     *
     * Runs once at setup time — payment_methods is delivered via the
     * JSON island and does not change after mount.
     */
    {
      const _methods = (state.config && state.config.payment_methods) || [];
      if (
        _methods.length === 1 &&
        !state.appointment_step_form_data.selected_payment_method
      ) {
        state.appointment_step_form_data.selected_payment_method = String(_methods[0].id);
      }
    }

    // --- Keyboard navigation (APG radio-group pattern) ----------------------
    //
    // Payment methods behave as true radios: Tab enters the group on the
    // selected (or first) method, arrow keys move focus AND selection
    // (selection-follows-focus — picking a gateway is non-destructive),
    // Enter/Space also select for consistency with the other steps.
    const paymentMethods = computed(() => (state.config && state.config.payment_methods) || []);
    const pmRov = useRovingTabindex({
      count: () => paymentMethods.value.length,
      selectedIndex: () =>
        paymentMethods.value.findIndex((m) => String(m.id) === String(selectedPaymentMethod.value)),
      onActivate: (i) => {
        const m = paymentMethods.value[i];
        if (m) selectedPaymentMethod.value = m.id;
      },
      onMove: (i) => {
        const m = paymentMethods.value[i];
        if (m) selectedPaymentMethod.value = m.id;
      },
    });
    const pmHeadingId = 'bp-v3-pm-heading-' + state.instanceId;

    return {
      state,
      selectedService,
      summaryServiceNames,
      selectedDateLabel,
      appointmentDateTimeLabel,
      total,
      payableNow,
      subtotal,
      showSubtotal,
      formatPrice,
      customerName,
      customerEmail,
      customerPhone,
      selectedPaymentMethod,
      prev,
      book,
      submission,
      readiness,
      // Template-only helpers (kept minimal per the safe-patch policy).
      pmRov,
      pmHeadingId,
      showPaymentBlock,
      isPayPalSelected,
      isPayPalPopupSelected,
      paypalButtonLoading,
      errorMsg,
      ICON_CHECKMARK,
      ICON_ARROW_LEFT,
      ICON_ERROR,
      ICON_SUMMARY_HEAD_VECTOR,
    };
  },
  template: `
    <div class="bpa-front-default-card">
      <!-- Top-of-card error toast — surfaces client-side validation
           (e.g. "Please select a payment method to proceed with the
           booking.") AND server-rejected submissions. Client-side
           errorMsg takes priority over the lingering submitError so a
           fresh user action always wins the toast.
           Mirrors the released form's .bpa-front-toast-notification.--bpa-error. -->
      <div v-if="errorMsg || submission.submitError.value" class="bpa-front-toast-notification --bpa-error" :aria-label="errorMsg || submission.submitError.value" role="alert">
        <div class="bpa-front-tn-body">
          <span v-html="ICON_ERROR"></span>
          <p>{{ errorMsg || submission.submitError.value }}</p>
        </div>
      </div>

      <div class="bpa-front-dc--body">
        <div class="bpa-front-module-container bpa-front-module--booking-summary bpa-fm__booking-summary-v47">
          <!-- Head: decorative illustration + title + (optional) summary content text + (optional) note. -->
          <div class="bpa-front-module--bs-head">
            <span aria-hidden="true" v-html="ICON_SUMMARY_HEAD_VECTOR"></span>
            <div class="bpa-front-module-heading" role="heading" aria-level="2" tabindex="-1" data-bp-step-heading>{{ state.strings.summary_step_name }}</div>
            <p v-if="state.strings.summary_content_text">{{ state.strings.summary_content_text }}</p>
            <div v-if="state.strings.summary_step_note" class="bpa-front-module--note-desc" v-html="state.strings.summary_step_note"></div>
          </div>

          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:before-customer" :data-bp-v3-instance="state.instanceId"></div>

          <!-- Customer block — released uses a 3-way v-if/v-else-if/v-else fallback
               (customer_name → firstname+lastname → email). Reproduced verbatim. -->
          <div class="bpa-front-module--bs-summary-content bpa-front-module--bs-customer-detail">
            <div class="bpa-front-module--bs-summary-content-item">
              <span role="heading" aria-level="3">{{ state.strings.customer_text }}</span>
              <div
                v-if="state.appointment_step_form_data.customer_name"
                class="bpa-front-bs-sm__item-val"
              >{{ state.appointment_step_form_data.customer_name }}</div>
              <div
                v-else-if="state.appointment_step_form_data.customer_firstname || state.appointment_step_form_data.customer_lastname"
                class="bpa-front-bs-sm__item-val"
              >{{ state.appointment_step_form_data.customer_firstname }} {{ state.appointment_step_form_data.customer_lastname }}</div>
              <div
                v-else
                class="bpa-front-bs-sm__item-val"
              >{{ state.appointment_step_form_data.customer_email }}</div>
            </div>
          </div>

          <!-- Slot immediately after the customer block. The Cart add-on injects
               its per-item Service / Date & Time (/ Extras) summary list here so it
               sits between the customer block and the amount details — matching the
               released form, where the cart appointment list renders in the
               summary-content position, not below the totals. Empty in Lite. -->
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:after-customer" :data-bp-v3-instance="state.instanceId"></div>

          <!-- Desktop variant — service + date/time as a two-item row. Hidden
               when a multi-appointment cart is active (the cart add-on injects its
               own per-item list into the summary slots). Inert in Lite. -->
          <div v-if="!state.summaryCartActive && !state.summaryRecurringActive" class="bpa-front-module--bs-summary-content bpa-front-summary-content__lg">
            <div class="bpa-front-module--bs-summary-content-item">
              <span role="heading" aria-level="3">{{ state.strings.service_text }}</span>
              <div class="bpa-front-bs-sm__item-val">
                <template v-if="summaryServiceNames.length"><span v-for="(nm, i) in summaryServiceNames" :key="i" class="bpa-front-bs-sm__svc-name">{{ nm }}</span></template>
                <template v-else>—</template>
              </div>
            </div>
            <div class="bpa-front-module--bs-summary-content-item">
              <span role="heading" aria-level="3">{{ state.strings.date_time_text }}</span>
              <div
                class="bpa-front-bs-sm__item-val"
              >{{ appointmentDateTimeLabel || '—' }}</div>
            </div>
            <div class="bp-v3-slot bpa-front-module--bs-summary-content-item" data-bp-v3-slot="summary-step:after-datetime" :data-bp-v3-instance="state.instanceId"></div>
          </div>

          <!-- Mobile variant — same data stacked under a single label.
               Visibility is controlled by the released stylesheet's
               .bpa-front-summary-content__sm / __lg media queries. -->
          <div v-if="!state.summaryCartActive && !state.summaryRecurringActive" class="bpa-front-module--bs-summary-content bpa-front-summary-content__sm">
            <div class="bpa-front-module--bs-summary-content-item">
              <span role="heading" aria-level="3">{{ state.strings.appointment_details_title }}</span>
              <div class="bpa-front-bs-sm__item-vals">
                <div class="bpa-front-bs-sm__item-val">
                  <template v-if="summaryServiceNames.length"><span v-for="(nm, i) in summaryServiceNames" :key="i" class="bpa-front-bs-sm__svc-name">{{ nm }}</span></template>
                  <template v-else>—</template>
                </div>
                <div
                  class="bpa-front-bs-sm__item-val"
                >{{ appointmentDateTimeLabel || '—' }}</div>
              </div>
            </div>
          </div>

          <!-- ============================================================
               Amount block — a single ordered sequence (released-form parity):
                 #4  Subtotal          (Lite-owned, below)
                 #5  Offer Discount    → summary-step:discount
                 #6  Tax               → summary-step:tax
                 #7  Redeem Package    → summary-step:package
                 #8  Coupon            → summary-step:coupon
                 #8b MyCred points     → summary-step:mycred
                 #9  Gift Card         → summary-step:gift-card
                 #10 Tip               → summary-step:tip
                 (Online Payment Discount + Cart summary → summary-step:before-total)
                 #12 Total payable     (Lite-owned, below)
                 #11 Deposit breakdown → summary-step:after-total
               Each amount add-on renders ONLY its own delta line into its slot;
               the single Subtotal (#4) and single Total (#12) are owned here.
               ============================================================ -->

          <!-- #4 Subtotal — the single pre-discount order-cost row. Shown only
               when the amount block carries a breakdown (a discount / tax / tip /
               deposit is in effect, or an amount module is enabled — see
               showSubtotal); a plain Lite order shows the Total row alone. Stands
               down for a multi-appointment Cart / Recurring order (they render
               their own per-item breakdown). -->
          <div v-if="showSubtotal && !state.summaryCartActive && !state.summaryRecurringActive" class="bpa-front-module--bs-amount-details">
            <div class="bpa-fm--bs-amount-item">
              <div class="bpa-bs-ai__item" :aria-label="state.strings.subtotal_text || 'Subtotal'">{{ state.strings.subtotal_text || 'Subtotal' }}</div>
              <div class="bpa-bs-ai__item">{{ formatPrice(subtotal) }}</div>
            </div>
          </div>

          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:discount" :data-bp-v3-instance="state.instanceId"></div>
          <!-- Tax module slot — renders the "+Tax" delta line (exclusive) or an
               informational "(Inc. GST)" tax line (inclusive). Empty in Lite. -->
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:tax" :data-bp-v3-instance="state.instanceId"></div>
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:package" :data-bp-v3-instance="state.instanceId"></div>
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:coupon" :data-bp-v3-instance="state.instanceId"></div>
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:gift-card" :data-bp-v3-instance="state.instanceId"></div>
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:mycred" :data-bp-v3-instance="state.instanceId"></div>
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:tip" :data-bp-v3-instance="state.instanceId"></div>

          <!-- Total-adjacent slot (bottom): the Advanced Discount module's Online
               Payment Discount + reduced-total rows, and the Cart summary total.
               Positioned just above the Total row. Empty in Lite. -->
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:before-total" :data-bp-v3-instance="state.instanceId"></div>

          <!-- #12 Total payable — the single grand-total row. Shows the amount
               payable now (payableNow = order total + tax + tip − gift card).
               Hidden while the Deposit module owns the paying-now / remaining
               breakdown (state.summaryDepositActive), the Advanced Discount module
               owns a reduced online-payment total (state.summaryOnlineDiscountActive),
               or a Cart / Recurring order renders its own total. Carries the
               inclusive-tax "(Inc. GST)" label when the Tax module publishes one
               via state.summaryTotalTaxLabel. Inert flags in Lite → row shows the
               plain service total. -->
          <div v-if="(subtotal > 0 || payableNow > 0) && !state.summaryDepositActive && !state.summaryCartActive && !state.summaryRecurringActive && !state.summaryOnlineDiscountActive" class="bpa-front-module--bs-amount-details">
            <div class="bpa-fm--bs-amount-item bpa-is-total-row">
              <div class="bpa-bs-ai__item">
                <span :aria-label="state.strings.total_amount_label">{{ state.strings.total_amount_label }}</span> <span v-if="state.summaryTotalTaxLabel" class="bpa-fm-tr__tax-included-label" :aria-label="state.summaryTotalTaxLabel">{{ state.summaryTotalTaxLabel }}</span>
              </div>
              <div class="bpa-front-module--bs-ad--price">{{ formatPrice(payableNow) }}</div>
            </div>
          </div>

          <!-- #11 Deposit — paying-now + remaining breakdown; owns the total area
               (hides the Total row above) when active. Empty in Lite. -->
          <div class="bp-v3-slot" data-bp-v3-slot="summary-step:after-total" :data-bp-v3-instance="state.instanceId"></div>
        </div>

        <!-- Payment methods — only when there's a price to pay and at least one gateway is enabled. -->
        <div v-if="showPaymentBlock" class="bpa-front-module-container bpa-front-module--payment-methods">
          <div class="bpa-front-module--pm-head">
            <div class="bpa-front-module-heading" role="heading" aria-level="3" :id="pmHeadingId">{{ state.strings.payment_method_label }}</div>
          </div>
          <div class="bpa-front-module--pm-body">
            <div class="bpa-front--pm-body-items" role="radiogroup" :aria-labelledby="pmHeadingId">
              <div
                v-for="(m, mi) in state.config.payment_methods"
                :key="m.id"
                :class="['bpa-front-module--pm-body__item', 'bpa_focusable', String(selectedPaymentMethod) === String(m.id) ? '__bpa-is-selected' : '']"
                role="radio"
                :tabindex="pmRov.tabindexFor(mi)"
                :ref="(el) => pmRov.setItemRef(el, mi)"
                :aria-checked="String(selectedPaymentMethod) === String(m.id) ? 'true' : 'false'"
                :aria-label="m.label"
                @focus="pmRov.onItemFocus(mi)"
                @keydown="pmRov.onKeydown($event, mi)"
                @click="selectedPaymentMethod = m.id"
              >
                <!-- Payment-method icon — rendered as a native template SVG
                     (NOT via \`v-html\` on a <span> wrapper) so the DOM matches
                     the released form's layout where the SVG is a direct
                     child of \`.bpa-front-module--pm-body__item\`. The
                     wrapper otherwise reads as an extra layout element
                     and visibly shifted the checkmark overlay's position
                     in the V3 dev form (Issue 2 in batch-7 manual QA). -->
                <svg
                  v-if="m.id === 'on-site'"
                  class="bpa-front-pm-pay-local-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  enable-background="new 0 0 24 24"
                  viewBox="0 0 24 24"
                >
                  <g><g><rect fill="none" height="24" width="24"/><rect fill="none" height="24" width="24"/></g></g>
                  <g><path d="M21.9,7.89l-1.05-3.37c-0.22-0.9-1-1.52-1.91-1.52H5.05c-0.9,0-1.69,0.63-1.9,1.52L2.1,7.89C1.64,9.86,2.95,11,3,11.06V19 c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2v-7.94C22.12,9.94,22.09,8.65,21.9,7.89z M13,5h1.96l0.54,3.52C15.59,9.23,15.11,10,14.22,10 C13.55,10,13,9.41,13,8.69V5z M6.44,8.86C6.36,9.51,5.84,10,5.23,10C4.3,10,3.88,9.03,4.04,8.36L5.05,5h1.97L6.44,8.86z M11,8.69 C11,9.41,10.45,10,9.71,10c-0.75,0-1.3-0.7-1.22-1.48L9.04,5H11V8.69z M18.77,10c-0.61,0-1.14-0.49-1.21-1.14L16.98,5l1.93-0.01 l1.05,3.37C20.12,9.03,19.71,10,18.77,10z"/></g>
                </svg>
                <svg
                  v-else-if="m.id === 'paypal'"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path d="M17.9588 8.24063L8.75722 18.2812H5.38222C5.14786 18.2812 4.96036 18.0469 5.00723 17.8125L7.25722 3.5625C7.30412 3.23438 7.58537 3 7.91347 3H13.6322C17.5697 3.14062 18.6479 5.15622 17.9447 8.25002L17.9588 8.24063Z" fill="#002C8A"/>
                  <path d="M18.1088 7.3125C19.5151 8.0625 19.8432 9.4687 19.3744 11.3437C18.7651 14.1094 16.9369 15.2812 14.2651 15.3281L13.5151 15.375C13.2338 15.375 13.0463 15.5625 12.9994 15.8437L12.3901 19.5469C12.3432 19.875 12.0619 20.1094 11.7338 20.1094H8.9213C8.6869 20.1094 8.4994 19.875 8.5463 19.6406L9.57755 12.9375C9.6244 12.7031 18.1088 7.3125 18.1088 7.3125Z" fill="#009BE1"/>
                  <path d="M9.52148 13.2656L10.459 7.31252C10.4897 7.17152 10.5661 7.04458 10.6762 6.95138C10.7864 6.85818 10.9242 6.80388 11.0683 6.79688H15.5683C16.6465 6.79688 17.4433 6.98437 18.0996 7.31252C17.8652 9.37502 16.8808 12.7031 12.0996 12.7969H10.0371C9.80268 12.7969 9.56833 12.9844 9.52148 13.2656Z" fill="#001F6B"/>
                </svg>
                <!-- Generic icon for a Pro-added gateway (e.g. Stripe): the
                     method may carry an inline-SVG \`icon\` string. Rendered into
                     a \`display:contents\` wrapper so the inner SVG lays out as a
                     direct child of the item (matching the built-in icons above).
                     Inert for on-site / paypal (they have no \`icon\` and use the
                     hardcoded SVGs above). -->
                <span v-else-if="m.icon" style="display:contents" v-html="m.icon"></span>
                <p>{{ m.label }}</p>
                <div
                  v-if="String(selectedPaymentMethod) === String(m.id)"
                  class="bpa-front-si-card--checkmark-icon"
                  v-html="ICON_CHECKMARK"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty-state fallback when no gateways are enabled at all.
             Matches the released '.bpa-front-module--pm__empty-view' >
             '.bpa-front-pm-ev__title' text element (illustration omitted
             for byte-cost reasons; the released CSS still styles the
             title alone if no SVG is present). -->
        <div v-else-if="total > 0 && (!state.config.payment_methods || state.config.payment_methods.length === 0)" class="bpa-front-module-container bpa-front-module--payment-methods">
          <div class="bpa-front-module--pm__empty-view">
            <div class="bpa-front-pm-ev__title">{{ state.strings.no_payment_method }}</div>
          </div>
        </div>

        <!-- Server-rejected gates: surfaced inline (the top-of-card toast
             already shows submitError; this row supplements with the
             specific gate names so the user can see which step failed). -->
        <p v-if="submission.failedGates.value && submission.failedGates.value.length > 0" class="bpa-front-form-error" role="alert">
          {{ (state.strings.failed_gates_prefix || 'Server rejected the booking. Please review:') }} {{ submission.failedGates.value.join(', ') }}.
        </p>

        <div class="bp-v3-slot" data-bp-v3-slot="summary-step:above-actions" :data-bp-v3-instance="state.instanceId"></div>
      </div>

      <div class="bpa-front-dc--footer">
        <div class="bpa-front-tabs--foot">
          <button
            type="button"
            class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--borderless bpa_focusable"
            :aria-label="state.strings.goback_button"
            :disabled="submission.isSubmitting.value"
            @click="prev()"
          >
            <span v-html="ICON_ARROW_LEFT"></span>&nbsp;{{ state.strings.goback_button }}
          </button>
          <!-- Normal Book button — shown for on-site / zero-price AND
               for PayPal-redirect mode (legacy parity: redirect mode
               keeps the standard book button, clicking it submits and
               follows \`redirect_url\` from \`api.paypalValidate\`). Hidden
               only when PayPal *popup* mode is the active selection. -->
          <!-- Book button is always clickable (gated only by the
               in-flight submission flag) so the @click handler can run
               client-side validation and surface a toast for missing
               selections (legacy parity — released form does the same
               via bookingpress_step_navigation). -->
          <button
            v-if="!isPayPalPopupSelected"
            type="button"
            :class="['bpa-front-btn', 'bpa-front-btn__medium', 'bpa-front-btn--primary', 'summery-book-appointment-btn', 'bpa_focusable', submission.isSubmitting.value ? 'bpa-front-btn--is-loader' : '']"
            :aria-label="state.strings.book_button"
            :disabled="submission.isSubmitting.value"
            @click="book()"
          >
            <span class="bpa-btn__label" v-html="state.strings.book_button"></span>
            <div class="bpa-front-btn--loader__circles">
              <div></div>
              <div></div>
              <div></div>
            </div>
          </button>
          <!-- PayPal popup container — empty SDK render target. The
               \`renderPayPalButtons()\` watcher mounts \`paypal.Buttons\`
               into this div on nextTick after Vue creates it. No
               hand-rolled fallback button here — that was the v5 bug
               where a button with the \`summery-book-appointment-btn\`
               class stayed in the DOM and shadowed the SDK render. -->
          <div
            v-else
            id="paypal-button-container"
            class="bpa-front-paypal-btn-container"
          ></div>
        </div>
      </div>
    </div>
  `,
};

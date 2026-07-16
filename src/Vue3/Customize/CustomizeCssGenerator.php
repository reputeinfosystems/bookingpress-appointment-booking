<?php
/**
 * CustomizeCssGenerator — compile customize settings to a CSS file.
 *
 * Per `BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md` §6 option 2: shared storage
 * with the legacy admin Customize panel. The generator reads from
 * `bookingpress_customize_settings` (via `CustomizeRepository`) and writes
 * the compiled CSS to `wp-content/uploads/bookingpress/form-v3-custom.css`.
 *
 * The file path is deliberately distinct from the legacy
 * `bookingpress_front_custom_css` so the two paths never collide.
 *
 * Lite scope: a documented subset of the legacy customize keys covering the
 * common cases (primary color, surface colors, label/content text colors,
 * border color). The generator is extension-friendly — Pro / add-ons can
 * filter via `bookingpress_form_v3_custom_css` before write.
 *
 * @package BookingPress\Vue3\Customize
 * @see     docs/migration/BOOKINGPRESS_FORM_VUE3_GREENFIELD_PLAN.md §6
 */

namespace BookingPress\Vue3\Customize;

use BookingPress\Vue3\Repositories\CustomizeRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomizeCssGenerator {

	/** Relative path under wp-content/uploads/bookingpress/. */
	const RELATIVE_PATH = 'form-v3-custom.css';

	/** @var CustomizeRepository */
	private $customize;

	public function __construct( ?CustomizeRepository $customize = null ) {
		$this->customize = $customize ?: new CustomizeRepository();
	}

	/**
	 * Read the current customize values and compile to CSS.
	 *
	 * @return string
	 */
	public function compile_from_settings() {
		$bf = $this->customize->get_group( CustomizeRepository::GROUP_BOOKING_FORM );
		return self::compile( $bf );
	}

	/**
	 * Pure compile helper — used by both the live generator and the pure
	 * test runner.
	 *
	 * Recognized keys (subset documented for Lite):
	 *   - primary_color          (default #12D488)
	 *   - background_color       (default #fff)
	 *   - footer_background_color (default #f4f7fb)
	 *   - content_color          (default #727E95)
	 *   - label_title_color      (default #202C45)
	 *   - sub_title_color        (default #535D71)
	 *   - price_button_text_color (default #fff)
	 *   - primary_background_color (default #e2faf1)
	 *   - border_color           (default #CFD6E5)
	 *
	 * Unrecognized keys are ignored; Pro can append via
	 * `bookingpress_form_v3_custom_css` (filter is applied by the caller
	 * around this function, not here, so the pure helper stays side-effect-free).
	 *
	 * @param array<string, string> $bf  Customize map (booking_form group).
	 *
	 * @return string
	 */
	public static function compile( array $bf ) {
		$d = static function ( $key, $default ) use ( $bf ) {
			return isset( $bf[ $key ] ) && '' !== $bf[ $key ] ? (string) $bf[ $key ] : $default;
		};
		$colors = array(
			'primary'             => $d( 'primary_color',             '#12D488' ),
			'bg'                  => $d( 'background_color',          '#ffffff' ),
			'footer_bg'           => $d( 'footer_background_color',   '#f4f7fb' ),
			'content'             => $d( 'content_color',             '#727E95' ),
			'label_title'         => $d( 'label_title_color',         '#202C45' ),
			'sub_title'           => $d( 'sub_title_color',           '#535D71' ),
			'button_text'         => $d( 'price_button_text_color',   '#ffffff' ),
			'primary_background'  => $d( 'primary_background_color',  '#e2faf1' ),
			'border'              => $d( 'border_color',              '#CFD6E5' ),
		);

		// Validate hex / rgb / hsl shapes; reject anything else so a malicious
		// admin value can't inject `; expression(...)` or similar.
		foreach ( $colors as $k => $v ) {
			$colors[ $k ] = self::sanitize_color( $v );
		}

		$lines = array();
		$lines[] = '/*! BookingPress Vue3 — generated customize CSS. Do not edit by hand. */';
		// Scope the generated rules to the released `.bpa-frontend-main-container.bpa-frontend-vue3`
		// root so customize colors apply to the same markup the released form uses.
		// No `background-color` / `color` / `border` is emitted on the root — the
		// released form has none, and adding them would drop an outer chrome
		// rectangle the released design doesn't have.
		$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 {';
		$lines[] = '    --bp-v3-primary: ' . $colors['primary'] . ';';
		$lines[] = '    --bp-v3-primary-bg: ' . $colors['primary_background'] . ';';
		$lines[] = '    --bp-v3-bg: ' . $colors['bg'] . ';';
		$lines[] = '    --bp-v3-footer-bg: ' . $colors['footer_bg'] . ';';
		$lines[] = '    --bp-v3-content: ' . $colors['content'] . ';';
		$lines[] = '    --bp-v3-label: ' . $colors['label_title'] . ';';
		$lines[] = '    --bp-v3-sub-title: ' . $colors['sub_title'] . ';';
		$lines[] = '    --bp-v3-button-text: ' . $colors['button_text'] . ';';
		$lines[] = '    --bp-v3-border: ' . $colors['border'] . ';';
		$lines[] = '}';
		$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bp-v3-step-title { color: ' . $colors['label_title'] . '; }';
		$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bp-v3-subtitle { color: ' . $colors['sub_title'] . '; }';
		$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bp-v3-button-primary { background-color: ' . $colors['primary'] . '; color: ' . $colors['button_text'] . '; }';
		$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bp-v3-button-primary:hover { background-color: ' . $colors['primary_background'] . '; color: ' . $colors['label_title'] . '; }';
		$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bp-v3-step-footer { background-color: ' . $colors['footer_bg'] . '; }';

		// ---- Released-parity rules for `.bpa-front-*` markup -----------------
		// The legacy customize layer (core/classes/class.bookingpress.php
		// lines 9836-10522) emits a 6% alpha tint of the primary colour for
		// the selected timeslot pill. Mirror that exactly so the selected
		// slot's background matches the released form (a light tint, NOT
		// the solid primary green — solid green would make the inner
		// grey-text `<span>` unreadable).
		$primary_alpha_06 = self::primary_alpha( $colors['primary'], 0.06 );
		if ( '' !== $primary_alpha_06 ) {
			$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bpa-front--dt__ts-body--item.__bpa-is-selected { background-color: ' . $primary_alpha_06 . ' !important; }';
		}

		// Disabled / booked timeslot pill — released customize layer
		// (class.bookingpress.php line 10376) overrides the bluish base
		// rule in `bookingpress_front.css` (`rgba(15,100,237,0.1)`) with
		// a 10% alpha tint of the border colour, which renders as a
		// neutral pale grey. Without this override the dev form shows
		// the bluish base; with it, booked pills match the released
		// form's muted look.
		$border_alpha_10 = self::primary_alpha( $colors['border'], 0.10 );
		if ( '' !== $border_alpha_10 ) {
			$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bpa-front--dt__ts-body--item.__bpa-is-disabled,';
			$lines[] = '.bpa-frontend-main-container.bpa-frontend-vue3 .bpa-front--dt__ts-body--item.__bpa-is-disabled:hover { background-color: ' . $border_alpha_10 . ' !important; }';
		}

		// Mirror the legacy `booking_form` customize ruleset
		// (class.bookingpress.php `bookingpress_save_booking_form_settings`
		// branch, lines 10230-10689) so the Vue 3 form gets visible
		// parity for primary/background/border/text/font customization.
		// Scoped under `.bpa-frontend-vue3` so other instances of the
		// legacy form on the same page are not double-styled.
		$title_font = $d( 'title_font_family', 'Poppins' );
		if ( 'Inherit Fonts' === $title_font ) {
			$title_font = 'inherit';
		}
		$legacy_parity = self::compile_legacy_booking_form_parity( $colors, $title_font );
		if ( '' !== $legacy_parity ) {
			$lines[] = $legacy_parity;
		}

		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * Emit the legacy `booking_form` customize ruleset, scoped under
	 * `.bpa-frontend-vue3` so we only style the Vue 3 form instance.
	 *
	 * Verbatim port of the rules in
	 * `core/classes/class.bookingpress.php`
	 * (`bookingpress_save_booking_form_settings` branch, ~lines 10230-10689),
	 * minus the rules that target `.bpa-appointment-cancellation_container`
	 * (legacy cancel screen only — Vue 3 does not render that wrapper).
	 *
	 * The selectors here intentionally match the same `.bpa-front-*`
	 * classes the Vue 3 components emit (ServiceStep, DateTimeStep,
	 * BasicDetailsStep, SummaryStep, StepNav).
	 *
	 * @param array<string,string> $colors      Sanitised color map (from compile()).
	 * @param string               $title_font  Font family value (already inherits-resolved).
	 *
	 * @return string
	 */
	private static function compile_legacy_booking_form_parity( array $colors, $title_font ) {
		$primary       = $colors['primary'];
		$primary_bg    = $colors['primary_background'];
		$bg            = $colors['bg'];
		$footer_bg     = $colors['footer_bg'];
		$content       = $colors['content'];
		$label_title   = $colors['label_title'];
		$sub_title     = $colors['sub_title'];
		$button_text   = $colors['button_text'];
		$border        = $colors['border'];

		$scope = '.bpa-frontend-vue3';

		// rgba helpers used by the legacy generator.
		$placeholder = self::hex_to_rgba( $content, 0.75, '#535D71' );
		$border_rgba = self::hex_to_rgba( $border,  0.10, '#CFD6E5' );

		$css  = '';

		// CSS variable scope (legacy emits on .bpa-front-tabs).
		// Derive the 12% alpha shade from the primary color directly so that
		// changing primary_color in Customize automatically updates the wizard-tab
		// icon background shade (Issue 1 — was using primary_background_color which
		// is a separate setting and doesn't auto-update with primary_color changes).
		$primary_alpha_12 = self::hex_to_rgba( $primary, 0.12, '#12D488' );
		$css .= $scope . ' .bpa-front-tabs {'
			. ' --bpa-pt-main-green: ' . $primary . ' !important;'
			. ' --bpa-pt-main-green-darker: ' . $primary . ' !important;'
			. ' --bpa-pt-main-green-alpha-12: ' . $primary_alpha_12 . ' !important;'
			. ' --bpa-pt-border-color: ' . $border . ' !important;'
			. ' --bpa-pt-background-color: ' . $bg . ' !important;'
			. ' --bpa-pt-price-button-text-color: ' . $button_text . ' !important;'
			. ' }';

		// Background colors.
		$css .= $scope . ' .bpa-front-tabs .bpa-front-tab-menu,'
			. $scope . ' .bpa-front-tabs .bpa-front-default-card,'
			. $scope . ' .bpa-full-container-loader,'
			. $scope . ' .bpa-front-tabs .bpa-front-tabs--foot,'
			. $scope . ' .bpa-front-data-empty-view,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-list,'
			. $scope . ' .bpa-front-thankyou-module-container,'
			. $scope . ' .bpa-front--dt__calendar .vc-nav-popover-container'
			. ' { background-color: ' . $bg . ' !important; }';

		// Vue 3 UI component wrappers — number input, text/email inputs, select boxes
		$css .= $scope . ' .bpa-form-control.bp-input .bp-input__wrapper,'
			. $scope . ' .bpa-form-control.bp-input-number .bp-input__wrapper,'
			. $scope . ' .bpa-form-control.bp-select .bp-select__wrapper,'
			. $scope . ' .bpa-form-control.bp-tel-input .bp-ui-tel-input__surface,'
			. $scope . ' .bpa-form-control.bp-date-editor .bp-input__wrapper,'
			. $scope . ' .bpa-form-control.bp-date-editor.bp-input__wrapper'
			. ' { background: ' . $bg . ' !important; }';

		// Select dropdown panel — teleported to <body> by Vue 3 so must be unscoped - fixed.
		$primary_fill_light = self::hex_to_rgba( $primary, 0.1, '#12D488' );
		$css .= '.bp-select-dropdown {'
			. ' background-color: ' . $bg . ' !important;'
			. ' --bp-color-primary: ' . $primary . ' !important;'
			. ' --bp-fill-color-light: ' . $primary_fill_light . ' !important;'
			. ' }';

			// Select dropdown selected / active item 
		$css .= '.bp-select-dropdown__item.is-selected,'
			. '.bp-select-dropdown__item.selected'
			. ' { color: ' . $primary . ' !important;'
			. ' background-color: ' . $primary_fill_light . ' !important; }';

		// Hovering (non-selected) item background.
		$css .= '.bp-select-dropdown__item.is-hovering,'
			. '.bp-select-dropdown__item:hover'
			. ' { background-color: ' . $primary_fill_light . ' !important; }';

		// Datepicker panel — teleported to body by Vue 3 so must be unscoped
		$css .= '.bp-picker-panel {'
			. ' background-color: ' . $bg . ' !important;'
			. ' border-color: ' . $border . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-picker-panel__footer,'
			. ' .bp-picker-panel .bp-time-panel {'
			. ' background-color: ' . $bg . ' !important;'
			. ' border-color: ' . $border . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-picker-panel__footer {'
			. ' border-top-color: ' . $border . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-date-picker__time-header {'
			. ' border-bottom-color: ' . $border . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-date-picker__time-header .bp-input__wrapper {'
			. ' background-color: ' . $bg . ' !important;'
			. ' border-color: ' . $border . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-date-picker__time-header .bp-input__inner {'
			. ' color: ' . $label_title . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-time-panel__content::after,'
			. ' .bp-picker-panel .bp-time-panel__content::before,'
			. ' .bp-picker-panel .bp-time-panel__footer {'
			. ' border-color: ' . $border . ' !important;'
			. ' }'
			. ' .bp-date-picker__header-label,'
			. ' .bp-picker-panel__content .bp-date-table th,'
			. ' .bp-picker-panel .bp-time-spinner__item {'
			. ' color: ' . $sub_title . ' !important;'
			. ' font-family: ' . $title_font . ' !important;'
			. ' }'
			. ' .bp-picker-panel__content .bp-date-table td span {'
			. ' color: ' . $content . ' !important;'
			. ' font-family: ' . $title_font . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-picker-panel__icon-btn {'
			. ' color: ' . $content . ' !important;'
			. ' }'
			. ' .bp-picker-panel .bp-picker-panel__icon-btn:hover {'
			. ' color: ' . $primary . ' !important;'
			. ' }'
			. ' .bp-date-table td.today:not(.current),'
			. ' .bp-date-table td.today:not(.current) span,'
			. ' .bp-date-picker__header-label:hover,'
			. ' .bp-picker-panel__content .bp-date-table td:not(.current):not(.today) span:hover,'
			. ' .bp-picker-panel__content .bp-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span:hover {'
			. ' color: ' . $primary . ' !important;'
			. ' }'
			. ' .bp-date-table td.current:not(.disabled) span,'
			. ' .bp-picker-panel .bp-time-spinner__item.is-active:not(.disabled) {'
			. ' background-color: ' . $primary . ' !important;'
			. ' color: ' . $button_text . ' !important;'
			. ' }'
			. ' .bp-picker-panel__footer .bp-button {'
			. ' height: auto !important;'
			. ' padding: 8px 16px !important;'
			. ' line-height: 16px !important;'
			. ' }'
			. ' .bp-picker-panel__footer .bp-button.is-plain {'
			. ' background-color: ' . $primary . ' !important;'
			. ' border-color: ' . $primary . ' !important;'
			. ' color: ' . $button_text . ' !important;'
			. ' font-family: ' . $title_font . ' !important;'
			. ' }'
			. ' .bp-picker-panel__footer .bp-button.is-text {'
			. ' color: ' . $primary . ' !important;'
			. ' font-family: ' . $title_font . ' !important;'
			. ' }';

		$css .= $scope . ' .bpa-front-form-control input::placeholder,'
			. $scope . ' .bpa-front-form-control .el-textarea__inner::placeholder,'
			. $scope . ' .bpa-front-form-control--file-upload .bpa-fu__placeholder,'
			// Vue 3 form puts `.bpa-front-form-control` directly on <input>/<textarea>;
			// add the direct selectors so the placeholder color tracks customize too.
			. $scope . ' input.bpa-front-form-control::placeholder,'
			. $scope . ' textarea.bpa-front-form-control::placeholder,'
			// Vue 3 dev form renders <input class="bp-input__inner"> + <textarea
			// class="bp-textarea__inner"> (Issue 8 in batch-5 manual QA — Firstname/
			// Lastname/Email placeholders weren't tracking customize color/font
			// because the Element-Plus-flavoured class names don't match the legacy
			// `.bpa-front-form-control` shapes above).
			. $scope . ' .bp-input__inner::placeholder,'
			. $scope . ' .bp-textarea__inner::placeholder,'
			. $scope . ' .el-date-picker__time-header .el-input .el-input__inner::placeholder'
			. ' { color: ' . $placeholder . ' !important; }';

		$css .= $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown:hover,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted,'
			. $scope . ' .bpa-front-toast-notification.--bpa-error,'
			. $scope . ' .bpa-front-toast-notification.--bpa-success,'
			. $scope . ' .bpa-front-thankyou-module-container .bpa-front-cc__error-toast-notification'
			. ' { background-color: ' . $footer_bg . ' !important; }';

		// Border colors.
		$css .= $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu,'
			. $scope . ' .bpa-front-default-card,'
			. $scope . ' .bpa-front-module--service-item .bpa-front-si-card,'
			. $scope . ' .bpa-front--dt__time-slots,'
			. $scope . ' .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item,'
			. $scope . ' .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,'
			. $scope . ' .bpa-front-tabs--foot,'
			. $scope . ' .bpa-front--dt__calendar .vc-container,'
			. $scope . ' .bpa-front--dt__calendar .vc-header,'
			. $scope . ' .bpa-front--dt__calendar .vc-day,'
			. $scope . ' .bpa-front-form-control input,'
			. $scope . ' .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown,'
			. $scope . ' .bpa-front-form-control .el-textarea__inner,'
			. $scope . ' .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item,'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item .bpa-front-tm--item-icon,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-list,'
			. $scope . ' .bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-fm--bs__coupon-module-textbox,'
			. $scope . ' .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item,'
			. $scope . ' .bpa-front-thankyou-module-container,'
			. $scope . ' .bpa-front-module--add-to-calendar,'
			. $scope . ' .bpa-front-module--atc-wrapper .bpa-front-btn,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown,'
			. $scope . ' .bpa-front-module--service-items-row .bpa-front-module--service-item,'
			. $scope . ' .bpa-front-form-control--checkbox .el-checkbox__inner'
			. ' { border-color: ' . $border . ' !important; }';

		// Selected/active border = primary. Focus inputs also use primary border per legacy
		// (legacy `bookingpress_from_css_{key}.css` applied primary border-color on :focus).
		$primary_alpha_12_focus = self::hex_to_rgba( $primary, 0.24, '#12D488' );
		$css .= $scope . ' .bpa-front-module--service-item.__bpa-is-selected .bpa-front-si-card,'
			. $scope . ' .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag.__bpa-is-active,'
			. $scope . ' .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag:hover,'
			. $scope . ' .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item:hover,'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active .bpa-front-tm--item-icon,'
			. $scope . ' .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item.__bpa-is-selected,'
			. $scope . ' .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item.__is-selected,'
			. $scope . ' .bpa-front-form-control--checkbox .el-checkbox__input.is-checked .el-checkbox__inner,'
			. $scope . ' .bpa-front-form-control--checkbox .el-checkbox__inner:hover,'
			. $scope . ' .el-radio__input.is-checked .el-radio__inner,'
			. $scope . ' .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item.__bpa-is-selected'
			. ' { border-color: ' . $primary . ' !important; }';
		// Issue 2 (86d31wem6): focus border must use primary color, matching the legacy
		// `bookingpress_from_css_{key}.css` behaviour (border-color + focus ring = primary).
		// The Vue 3 form renders inputs with the `bpa-front-form-control` class DIRECTLY
		// on the <input>/<textarea> (not on a wrapper as the legacy form does), so the
		// `.bpa-front-form-control input:focus` descendant selector does not match. We
		// emit BOTH the legacy descendant form AND the direct `.bpa-front-form-control:focus`
		// shape so the same generated CSS file styles both renderers correctly.
		$css .= $scope . ' .bpa-front-form-control input:focus,'
			. $scope . ' .bpa-front-form-control .el-textarea__inner:focus,'
			. $scope . ' input.bpa-front-form-control:focus,'
			. $scope . ' textarea.bpa-front-form-control:focus'
			. ' { border-color: ' . $primary . ' !important;'
			. ' box-shadow: 0 0 0 3px ' . $primary_alpha_12_focus . ' !important; }';

		// Issue 4 (86d31wjhn): checked checkbox background must be primary color so the
		// tick is visible (base CSS only sets border-color on the checked state).
		$css .= $scope . ' .bpa-front-form-control--checkbox .el-checkbox__input.is-checked .el-checkbox__inner'
			. ' { background-color: ' . $primary . ' !important; }';

		// Disabled day-cell + booked timeslot background (10% border tint).
		$css .= $scope . ' .bpa-front--dt__calendar .vc-day .vc-day-content.is-disabled,'
			. $scope . ' .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item.__bpa-is-disabled,'
			. $scope . ' .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item.__bpa-is-disabled:hover'
			. ' { background-color: ' . $border_rgba . ' !important; }';

		// Primary-color rules (active step pill, etc.).
		// NOTE: today-calendar color is emitted separately AFTER the content_color block below
		// to guarantee higher specificity wins — content_color uses .bpa-front-tabs prefix (5 classes);
		// the today rule uses .bpa-front-tabs + .is-today (6 classes) and must follow in source order.
		$css .= $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu a.bpa-front-tab-menu--item.__bpa-is-active,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price,'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active::before,'
			. $scope . ' .bpa-front-form-control--checkbox .bp-checkbox__input.is-checked + .bp-checkbox__label,'
			. $scope . ' .bpa-front-form-control--checkbox .el-checkbox__input.is-checked + .el-checkbox__label'
			. ' { color: ' . $primary . ' !important; }';

		// Primary button bg + text color. Text color is set on the element itself (not
		// just on span/strong children) so the direct "Next" text node inherits it.
		$css .= $scope . ' .bpa-front-btn--primary,'
			. $scope . ' .bpa-front-btn--primary:hover'
			. ' { background: ' . $primary . ' !important; border-color: ' . $primary . ' !important; color: var(--bp-v3-button-text) !important; }';

		// Service price chip — legacy renders this as a filled green
		// badge via `css/bookingpress_front.css:1785`:
		//   background-color: var(--bpa-pt-main-green);
		//   color: var(--bpa-cl-white);
		//   padding: 4px 8px; border-radius: 4px;
		//   display: inline-flex; line-height: 20px;
		//
		// Legacy admin live-preview ground truth
		// (`core/views/customize/manage_form_customize.php:257`) confirms
		// the chip background tracks the `primary_color` backend key:
		//   :style="{ 'background-color': primary_color, ... }"
		//
		// We deliberately do NOT redeclare padding / radius / line-height /
		// display here — those come from the legacy stylesheet (enqueued
		// via `Assets::STYLE_LEGACY_FRONT`), preserving the chip's exact
		// legacy shape and spacing. We only assert `background-color`
		// here as a direct, !important rule so the chip background
		// tracks the customize primary even if a downstream theme/plugin
		// redefines `--bpa-pt-main-green` at `:root`.
		//
		// The declaration uses the project's existing `--bp-v3-primary`
		// variable (emitted once at the form root by `compile()` from
		// the same `primary_color` backend key) — this makes the
		// customize-key linkage visible in DevTools and avoids the chip
		// drifting from the other primary-coloured elements if the
		// generator vocabulary ever changes.
		$css .= $scope . ' .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body strong.--is-service-price'
			. ' { background-color: var(--bp-v3-primary) !important; }';

		// Vector fills using primary.
		$css .= $scope . ' .bpa-front-module--booking-summary .bpa-front-module--bs-head .bpa-head__vector-item,'
			. $scope . ' .bpa-front-module--confirmation .bpa-head__vector--confirmation .bpa-head__vector-item,'
			. $scope . ' .bpa-front-thankyou-module-container .bpa-front-tmc__head .bpa-front-tmc__vector--confirmation .bpa-head__vector-item'
			. ' { fill: ' . $primary . ' !important; }';

		// Label/title color (form headings + summary values).
		// Batch-7 Issue 1: dev form's text/email inputs render as
		// <input class="bp-input__inner"> and textarea as <textarea
		// class="bp-textarea__inner">; neither matches the legacy
		// `.bpa-front-form-control input` / `.el-textarea__inner` shapes,
		// so the typed content text would render in the browser default
		// colour instead of the customize `label_title_color`.
		$css .= $scope . ' .bpa-front-ci-pill.__bpa-is-active .bpa-front-ci-item-title,'
			. $scope . ' .bpa-front-tabs .bpa-front-form-control input,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item .bpa-front-bs-sm__item-val,'
			. $scope . ' .bpa-front-tabs .bpa-front-form-control .el-textarea__inner,'
			. $scope . ' .bp-input__inner,'
			. $scope . ' .bp-textarea__inner,'
			. $scope . ' .bpa-front-tabs .bpa-front-module-heading,'
			. $scope . ' .bpa-front-module--bs-amount-details .bpa-fm--bs-amount-item .bpa-front-total-payment-amount-label,'
			. $scope . ' .bpa-front-tmc__head .bpa-front-tmc__title,'
			. $scope . ' .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__val,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted strong,'
			. $scope . ' .bpa-front-form-control.--bpa-country-dropdown .vti__dropdown-item.highlighted span'
			. ' { color: ' . $label_title . ' !important; }';

		// Sub-title color (secondary text in headings, helper labels, go-back link).
		// `.bpa-front-tabs--foot .bpa-front-btn--borderless` added to cover the
		// "Go Back" navigation link (Issue 12 — was missing from this selector list).
		$css .= $scope . ' .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt-ts__sub-heading,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__calendar .vc-title,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item span,'
			. $scope . ' .bpa-front--dt__ts-sm-back-btn label,'
			. $scope . ' .bpa-front-tabs .el-form-item__label span,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span,'
			. $scope . ' .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item p,'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu a,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__calendar .vc-weeks .vc-weekday,'
			. $scope . ' .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__label,'
			. $scope . ' .bpa-front-tabs--foot .bpa-front-btn--borderless'
			. ' { color: ' . $sub_title . ' !important; }';

		// Primary button text color. Emitted AFTER the sub_title block on
		// purpose: the sub_title selector list above contains the
		// unqualified `... p strong` selector, which has identical
		// specificity to the qualified `... p strong.--is-service-price`
		// selector below. CSS resolves identical-specificity ties by
		// later-rule-wins, so this block MUST follow the sub_title block
		// for the service price chip's text to render in
		// `price_button_text_color` (white by default) rather than the
		// `sub_title` gray. Matches legacy emit order in
		// `class.bookingpress.php` (sub_title at ~line 10451 then
		// price_button_text at ~line 10511) AND legacy admin live
		// preview (`manage_form_customize.php:257`) which uses
		// `selected_colorpicker_values.price_button_text_color` for the
		// chip text.
		//
		// Declarations reference the existing `--bp-v3-button-text` CSS
		// variable that `compile()` already emits at the scoped form
		// root from the `price_button_text_color` backend key. This
		// makes the customize-key linkage explicit and audit-able in
		// DevTools (no opaque hex), and the chip can never visually
		// drift away from the primary button text — both consume the
		// same variable.
		// Note: the legacy generator emitted two extra selectors here for the
		// "today + highlighted" calendar cell text. V-Calendar v3 renders the
		// selected-day chrome via our own `.__bp-v3-selected` sweep (in
		// `booking-form-vue3.css`), so those two selectors were never matched
		// against real DOM and only created an off-by-one chrome regression
		// on the current-date cell. Removed per Issue 4 in batch-5 manual QA.
		$css .= $scope . ' .bpa-front-tabs--foot .bpa-front-btn--primary span,'
			. $scope . ' .bpa-front-tabs--foot .bpa-front-btn--primary strong,'
			. $scope . ' .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong.--is-service-price'
			. ' { color: var(--bp-v3-button-text) !important; }';
		$css .= $scope . ' .bpa-front-tabs--foot .bpa-front-btn--primary svg'
			. ' { fill: var(--bp-v3-button-text) !important; }';

		// Content color (helper paragraph copy, secondary captions, calendar day numbers).
		// Calendar day numbers are added here (before the button_text block above) so
		// today/.vc-highlights + .vc-day-content rules — which are more specific — still win.
		$css .= $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p,'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu a span,'
			. $scope . ' .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p,'
			. $scope . ' .bpa-front-tmc__booking-id .bpa-front-bi__label,'
			. $scope . ' .bpa-front-tmc__head p,'
			. $scope . ' .bpa-front-module--add-to-calendar .bpa-fm--atc__heading,'
			. $scope . ' .bpa-front-module--atc-wrapper .bpa-front-btn:hover,'
			. $scope . ' .bpa-front-data-empty-view .bpa-front-dev__title,'
			. $scope . ' .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,'
			. $scope . ' .bpa-front-module--note-desc,'
			. $scope . ' .bpa-front-form-control--checkbox .el-checkbox__label,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__calendar .vc-day .vc-day-content'
			. ' { color: ' . $content . ' !important; }';

		// Issue 5 (86d324u34): today's date text must be primary color when unselected.
		// Emitted here — AFTER the content_color block above — so it wins the cascade.
		// Selector adds .bpa-front-tabs (.is-today = 6 classes) vs content_color's 5 classes,
		// giving higher specificity even if order were swapped; emitting last is extra insurance.
		$css .= $scope . ' .bpa-front-tabs .bpa-front--dt__calendar .vc-day.is-today .vc-day-content'
			. ' { color: ' . $primary . ' !important; }';

		// Issue 5 (86d325gb7 batch-4 add-on): service-image placeholder SVG must
		// follow content_color. The Vue 3 ServiceStep renders a stock mountain
		// SVG inside `.bpa-front-si__default-img`, whose <path> elements have
		// no explicit fill and therefore default to solid black. Setting `fill`
		// on the SVG (and its paths, since some Element-Plus styles can break
		// the default fill inheritance) makes the placeholder track customize.
		$css .= $scope . ' .bpa-front-module--service-item .bpa-front-si__default-img svg,'
			. $scope . ' .bpa-front-module--service-item .bpa-front-si__default-img svg path:not([fill="none"])'
			. ' { fill: ' . $content . ' !important; }';

		// Issue 3 (86d31wf41): payment card checkmark container has `background: var(--bpa-cl-white)`
		// hardcoded in base CSS. Override so it tracks the customize background_color, preventing
		// a white rectangle from appearing on non-white themed cards.
		$css .= $scope . ' .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item .bpa-front-si-card--checkmark-icon'
			. ' { background: ' . $bg . ' !important; }';

		// Font family (title_font_family) — applied to the same wide
		// selector list the legacy generator uses.
		// Batch-7 Issue 1: see the matching note in the label_title color
		// block above. The dev form's input + textarea use Element-Plus
		// flavoured `.bp-input__inner` / `.bp-textarea__inner` classes
		// rather than the legacy `.bpa-front-form-control` shapes, so we
		// emit both shapes here to cover content typed by the user.
		$css .= $scope . ' .bpa-front-tabs .bpa-front-module-heading,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__calendar .vc-weeks .vc-weekday,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item span,'
			. $scope . ' .bpa-front-tabs .bpa-front-form-control input,'
			. $scope . ' .bpa-front-tabs .bpa-front-form-control .el-textarea__inner,'
			. $scope . ' .bp-input__inner,'
			. $scope . ' .bp-textarea__inner,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item .bpa-front-bs-sm__item-val,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p,'
			. $scope . ' .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p,'
			. $scope . ' .bpa-front-tabs .el-form-item__label .bpa-front-form-label,'
			. $scope . ' .bpa-front-tabs .bp-form-item__label .bpa-front-form-label,'
			. $scope . ' .bpa-front-tabs .bpa-front-form-error,'
			. $scope . ' .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc,'
			. $scope . ' .bpa-front-module--payment-methods .bpa-front-module--pm-body .bpa-front-module--pm-body__item p,'
			. $scope . ' .bpa-front-tabs .bpa-front-tab-menu .bpa-front-tab-menu--item,'
			. $scope . ' .bpa-front-module--category .bpa-front-cat-items .bpa-front-ci-pill.el-tag,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt-ts__sub-heading,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span,'
			. $scope . ' .bpa-front-module--bs-amount-details .bpa-fm--bs-amount-item .bpa-front-total-payment-amount-label,'
			. $scope . ' .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price,'
			. $scope . ' .bpa-front-tabs .bpa-front--dt__calendar .vc-title,'
			. $scope . ' .bpa-front-tabs--foot .bpa-front-btn,'
			. $scope . ' .bpa-front-tmc__head .bpa-front-tmc__title,'
			. $scope . ' .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__label,'
			. $scope . ' .bpa-front-tmc__summary-content .bpa-front-tmc__sc-item .bpa-front-sc-item__val,'
			. $scope . ' .bpa-front-module--add-to-calendar .bpa-fm--atc__heading,'
			. $scope . ' .bpa-front-form-control input::placeholder,'
			. $scope . ' .bpa-front-form-control .el-textarea__inner::placeholder,'
			. $scope . ' .bpa-front-form-control--file-upload .bpa-fu__placeholder,'
			// Issue 8 (86d327y7w): Vue 3 form renders <input class="bpa-front-form-control">
			// and <textarea class="bpa-front-form-control"> directly. The descendant
			// selectors above (`.bpa-front-form-control input::placeholder`) do not match
			// those, so the placeholder font would fall back to the browser default
			// instead of the customize title_font_family. Add the direct selectors here.
			. $scope . ' input.bpa-front-form-control::placeholder,'
			. $scope . ' textarea.bpa-front-form-control::placeholder,'
			// Batch-5 Issue 8 follow-up: the dev form renders text/email inputs as
			// <input class="bp-input__inner"> (chrome-less inner of bp-input__wrapper)
			// and — after the textarea wrap fix — <textarea class="bp-textarea__inner">.
			// Neither matches the `.bpa-front-form-control` shapes above, so emit the
			// Element-Plus-flavoured shapes too. Without this, Firstname/Lastname/
			// Email placeholders render in the browser default font instead of the
			// customize `title_font_family`.
			. $scope . ' .bp-input__inner::placeholder,'
			. $scope . ' .bp-textarea__inner::placeholder,'
			. $scope . ' .bpa-front--dt__calendar .vc-day .vc-day-content,'
			. $scope . ' .bpa-front-form-control--checkbox .el-checkbox__label'
			. ' { font-family: ' . $title_font . ' !important; }';

		// Sidebar step-icon color. The inactive icon uses content_color; the active icon
		// sits on a solid primary-color background (see base CSS line ~1121:
		// `background-color: var(--bpa-pt-main-green)`) so its text/SVG must be
		// button_text (white by default) to remain visible.
		// `fill: currentColor` on the svg element lets the SVG paths inherit `color`
		// without needing separate fill declarations per step icon.
		// Issue 1 (86d31w610): the base CSS box-shadow on the active icon is hardcoded
		// rgba(18,212,136,…); override here so it tracks the customize primary_color.
		$primary_alpha_06 = self::hex_to_rgba( $primary, 0.06, '#12D488' );
		$primary_alpha_16 = self::hex_to_rgba( $primary, 0.16, '#12D488' );
		$css .= $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item .bpa-front-tm--item-icon'
			. ' { color: ' . $content . ' !important; }';
		// Base CSS at `css/bookingpress_front.css:1117-1135` targets BOTH
		// `.__bpa-is-active span` AND `.__bpa-is-active .bpa-front-tm--item-icon`
		// with the hardcoded green box-shadow + green background. The icon span
		// matches BOTH selectors, but the equivalent `span` selector in the
		// active tab also matches the (Vue 3) label `<div>` indirectly via
		// descendant in some renderers. Override the `span` form explicitly
		// alongside the icon form so the rule wins regardless of which
		// selector the cascade picks.
		// Also force `background-color` to the primary so the cascade can't
		// fall back to the default `var(--bpa-pt-main-green)` lookup if a
		// downstream stylesheet redefines that variable at `:root`.
		$css .= $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active span,'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active .bpa-front-tm--item-icon'
			. ' { color: ' . $button_text . ' !important;'
			. ' background-color: ' . $primary . ' !important;'
			. ' border-color: ' . $primary . ' !important;'
			. ' box-shadow: 0 4px 8px ' . $primary_alpha_06 . ', 0 8px 16px ' . $primary_alpha_16 . ' !important; }';
		$css .= $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item .bpa-front-tm--item-icon svg'
			. ' { fill: currentColor; }';

		// Mobile (≤576px): the legacy base CSS (`bookingpress_front.css`
		// @media max-width:576px) strips the active icon's box (transparent
		// background/border, no shadow) and paints the GLYPH in the primary
		// color with an underline instead. The `!important` background-color
		// emitted above would otherwise keep the desktop green box on phones
		// — and with the non-important `fill: currentColor` losing to the
		// base sheet's mobile `fill: var(--bpa-pt-main-green) !important`,
		// the icon rendered as a solid green square (green glyph on green
		// box). Re-neutralise here, in source order after the desktop rules
		// so this override wins at equal importance.
		$css .= '@media (max-width: 576px) {'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active span,'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active .bpa-front-tm--item-icon'
			. ' { background-color: transparent !important;'
			. ' border-color: transparent !important;'
			. ' box-shadow: none !important; }'
			. $scope . ' .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active .bpa-front-tm--item-icon svg'
			. ' { fill: ' . $primary . ' !important; }'
			. '}';

		return $css;
	}

	/**
	 * Hex (`#RRGGBB` / `#RGB`) → `rgba(r,g,b,a)` helper used to mirror the
	 * legacy generator's `sscanf(...)` shaping for placeholder + border
	 * tints.
	 *
	 * @param string $hex       Hex color value.
	 * @param float  $alpha     0.0 - 1.0
	 * @param string $fallback  Fallback hex (used when `$hex` parse fails).
	 *
	 * @return string `rgba(R,G,B,A)`
	 */
	private static function hex_to_rgba( $hex, $alpha, $fallback = '#000000' ) {
		$h = is_string( $hex ) ? trim( $hex ) : '';
		if ( 0 === strpos( $h, '#' ) ) {
			$h = substr( $h, 1 );
		}
		if ( 3 === strlen( $h ) ) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', (string) $h ) ) {
			$h = ltrim( $fallback, '#' );
		}
		$r = hexdec( substr( $h, 0, 2 ) );
		$g = hexdec( substr( $h, 2, 2 ) );
		$b = hexdec( substr( $h, 4, 2 ) );
		$a = max( 0.0, min( 1.0, (float) $alpha ) );
		return sprintf( 'rgba(%d,%d,%d,%s)', $r, $g, $b, rtrim( rtrim( sprintf( '%.3F', $a ), '0' ), '.' ) );
	}

	/**
	 * Compile from settings and write to disk. Returns the absolute path
	 * written, or null on failure.
	 *
	 * @return array{ path:string|null, url:string|null }
	 */
	public function write_file() {
		$css = $this->compile_from_settings();

		/**
		 * Filter the compiled CSS before write.
		 *
		 * @param string $css
		 */
		$css = (string) apply_filters( 'bookingpress_form_v3_custom_css', $css );

		$paths = self::resolve_paths();
		if ( null === $paths['path'] ) {
			return array( 'path' => null, 'url' => null );
		}
		$dir = dirname( $paths['path'] );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$ok = file_put_contents( $paths['path'], $css );
		if ( false === $ok ) {
			return array( 'path' => null, 'url' => null );
		}
		return $paths;
	}

	/**
	 * Resolve the absolute path + public URL for the generated CSS file.
	 *
	 * @return array{ path:string|null, url:string|null }
	 */
	public static function resolve_paths() {
		$upload = wp_upload_dir();
		if ( ! is_array( $upload ) || empty( $upload['basedir'] ) || empty( $upload['baseurl'] ) ) {
			return array( 'path' => null, 'url' => null );
		}
		$path = trailingslashit( $upload['basedir'] ) . 'bookingpress/' . self::RELATIVE_PATH;
		$url  = trailingslashit( $upload['baseurl'] ) . 'bookingpress/' . self::RELATIVE_PATH;
		return array( 'path' => $path, 'url' => $url );
	}

	/**
	 * Whitelist colour values to a small, safe set of formats.
	 *
	 * @param string $value
	 *
	 * @return string Sanitised color (empty string falls back to '#000').
	 */
	/**
	 * Convert a `#RRGGBB` (or `#RGB`) hex into an `rgba(r,g,b,a)` string.
	 *
	 * Used to emit the same alpha-tinted primary colour the released
	 * customize layer uses for the selected-slot pill (see legacy
	 * `core/classes/class.bookingpress.php` line 9838 — `primary_alpha_color`).
	 *
	 * @param string $hex   `#RRGGBB` or `#RGB`. Falls back to `#12D488`.
	 * @param float  $alpha 0.0 - 1.0
	 *
	 * @return string `rgba(R,G,B,A)` or empty string on bad input.
	 */
	private static function primary_alpha( $hex, $alpha ) {
		$hex = is_string( $hex ) ? trim( $hex ) : '';
		if ( 0 === strpos( $hex, '#' ) ) {
			$hex = substr( $hex, 1 );
		}
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', (string) $hex ) ) {
			$hex = '12D488';
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		$a = max( 0.0, min( 1.0, (float) $alpha ) );
		return sprintf( 'rgba(%d,%d,%d,%s)', $r, $g, $b, rtrim( rtrim( sprintf( '%.3F', $a ), '0' ), '.' ) );
	}

	private static function sanitize_color( $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '#000000';
		}
		// #rgb / #rrggbb / #rrggbbaa.
		if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value ) ) {
			return $value;
		}
		// rgb()/rgba()/hsl()/hsla() with simple value patterns.
		if ( preg_match( '/^(rgb|rgba|hsl|hsla)\([0-9\.,\s%\/]+\)$/', $value ) ) {
			return $value;
		}
		// Named colors — a tiny safelist.
		$named = array( 'transparent', 'inherit', 'currentColor', 'black', 'white' );
		if ( in_array( strtolower( $value ), array_map( 'strtolower', $named ), true ) ) {
			return $value;
		}
		return '#000000';
	}
}

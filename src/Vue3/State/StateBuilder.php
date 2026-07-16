<?php
/**
 * StateBuilder — compose the per-instance initial-state payload.
 *
 * The output of `build()` is what gets embedded in the JSON island
 * (`<script type="application/json" id="wp-script-module-data-bookingpress-form-v3-loader">`)
 * the M6 client reads on mount.
 *
 * Composition pipeline:
 *   1. Resolve preselection (§M0.1).
 *   2. Pull services + categories + default-category seed (§M0.2).
 *   3. Pull customer form fields + build rules + messages (§M0.8).
 *   4. Build the step schema via StepSchemaService (§M0.9.B).
 *   5. Build the payment methods list (§M0.10) + auto-select rule.
 *   6. Pull all customize strings (labels, button text, etc.).
 *   7. Pull date/time formatting + max-date cap.
 *   8. Wrap with nonces + REST hints (passed from Assets).
 *   9. Fire `bookingpress_form_v3_initial_state`.
 *
 * @package BookingPress\Vue3\State
 * @see     docs/migration/LEGACY_BEHAVIOR_CONTRACT.md (full contract)
 */

namespace BookingPress\Vue3\State;

use BookingPress\Vue3\Contracts\PaymentServiceInterface;
use BookingPress\Vue3\Contracts\ServiceCatalogServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\CustomerRepository;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\FormFieldRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;
use BookingPress\Vue3\Services\DateFormatService;
use BookingPress\Vue3\Services\PricingService;
use BookingPress\Vue3\Services\ServiceLocator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class StateBuilder {

	/** @var ServiceCatalogServiceInterface */
	private $catalog;
	/** @var PaymentServiceInterface */
	private $payment;
	/** @var StepSchemaService */
	private $steps;
	/** @var FormFieldRepository */
	private $form_fields;
	/** @var CustomizeRepository */
	private $customize;
	/** @var SettingsRepository */
	private $settings;
	/** @var DateFormatService */
	private $dates;
	/** @var PricingService */
	private $pricing;
	/** @var CustomerRepository */
	private $customers;

	public function __construct(
		?ServiceCatalogServiceInterface $catalog = null,
		?PaymentServiceInterface $payment = null,
		?StepSchemaService $steps = null,
		?FormFieldRepository $form_fields = null,
		?CustomizeRepository $customize = null,
		?SettingsRepository $settings = null,
		?DateFormatService $dates = null,
		?PricingService $pricing = null,
		?CustomerRepository $customers = null
	) {
		$this->catalog     = $catalog     ?: ServiceLocator::get( ServiceCatalogServiceInterface::class );
		$this->payment     = $payment     ?: ServiceLocator::get( PaymentServiceInterface::class );
		$this->steps       = $steps       ?: new StepSchemaService();
		$this->form_fields = $form_fields ?: new FormFieldRepository();
		$this->customize   = $customize   ?: new CustomizeRepository();
		$this->settings    = $settings    ?: new SettingsRepository();
		$this->dates       = $dates       ?: new DateFormatService();
		$this->pricing     = $pricing     ?: new PricingService();
		$this->customers   = $customers   ?: new CustomerRepository();
	}

	/**
	 * Compose the initial-state payload for one render.
	 *
	 * @param string $instance_id 12-char id from Routing::generate_unique_id().
	 * @param array  $atts        Sanitised shortcode attributes.
	 *
	 * @return array
	 */
	public function build( $instance_id, array $atts ) {
		// 1. Preselection.
		$preselection = $this->catalog->resolve_preselection( $atts );

		// 2. Services + categories.
		//
		// `category_id` forwards the `[bookingpress_form category=N]` shortcode
		// attribute (resolved into `$preselection['category']`) down to the
		// service query so the Service step only lists services in that
		// category. Without this the attribute was parsed but dropped, and the
		// form rendered every service. Note: `resolve_preselection()` zeroes
		// `category` on an `s_id` share-URL load, so a URL preselection always
		// sees the full catalogue (needed for the invalid-`s_id` fallback).
		$services_ctx = array(
			'service_csv'      => isset( $preselection['service'] ) ? (string) $preselection['service'] : '',
			'selected_service' => isset( $preselection['selected_service'] ) ? (int) $preselection['selected_service'] : 0,
			'category_id'      => isset( $preselection['category'] ) ? (int) $preselection['category'] : 0,
		);
		$services   = $this->catalog->get_services( $services_ctx );

		// Validate that a preselected service (URL `s_id`/`bpservice_id` or the
		// `selected_service` attribute) is actually *bookable* — i.e. present
		// in the post-`FILTER_SERVICES` list. `resolve_preselection()` only
		// confirms via `find()` that the row EXISTS in the table; a service
		// that is disabled / outside its booking window is dropped by
		// `get_services()` and must not count as "selected". When it isn't
		// bookable we drop the preselection so the Service step renders in full
		// (and the `allow_modify=0` skip below is suppressed by the fallback).
		if ( (int) $preselection['selected_service'] > 0
			&& ! $this->service_is_bookable( $services, (int) $preselection['selected_service'] ) ) {
			$preselection['selected_service'] = 0;
			$preselection['is_from_url']      = 0;
		}

		$categories = $this->catalog->get_categories( $services );

		// 3. Customize strings + general / message / payment settings used downstream.
		$bf_strings = $this->customize->get_group( CustomizeRepository::GROUP_BOOKING_FORM );
		$general    = $this->settings->get_group( SettingsRepository::GROUP_GENERAL );
		$message_settings = $this->settings->get_group( SettingsRepository::GROUP_MESSAGE );
		$payment    = $this->settings->get_group( SettingsRepository::GROUP_PAYMENT );

		// 4. Default category seed (only when no preselected service).
		$default_cat_seed = $this->catalog->resolve_default_category(
			$categories,
			$bf_strings,
			array(
				'selected_category' => $preselection['selected_service'] > 0
					? $this->category_for_service( $services, (int) $preselection['selected_service'] )
					: '',
			)
		);

		// 5. Form fields (visible only) + rules + messages.
		$visible_fields = $this->form_fields->get_visible();
		$fields_for_wire = $this->prepare_form_fields( $visible_fields, $general );

		$rules    = $this->build_field_rules( $fields_for_wire, $bf_strings );
		$messages = $this->build_field_messages( $fields_for_wire );

		/**
		 * Reshape the ordered fields payload (after rule/message build).
		 *
		 * @param array $fields  The ordered field payload.
		 * @param array $context Caller context.
		 */
		$fields_for_wire = apply_filters( Hooks::FILTER_FORM_FIELDS_ORDERED, $fields_for_wire, array(
			'preselection' => $preselection,
		) );
		$rules    = apply_filters( Hooks::FILTER_FORM_FIELDS_RULES,    $rules,    $fields_for_wire, array() );
		$messages = apply_filters( Hooks::FILTER_FORM_FIELDS_MESSAGES, $messages, $fields_for_wire, array() );

		// 6. Step schema.
		$hide_service_setting = isset( $bf_strings['hide_category_service_selection'] ) && 'true' === $bf_strings['hide_category_service_selection'];

		// Share-URL override for the Service step (legacy parity:
		// class.bookingpress_appointment_bookings.php:5528-5543):
		//   ?allow_modify=0 → force-HIDE the Service step (the customer can't
		//                     change the shared service) → land on Date & Time.
		//   ?allow_modify=1 → force-SHOW the Service step.
		// This runs AFTER the `s_id → selected_service` resolution + bookable
		// validation above, so the fallback can see a zeroed selected_service.
		// Only a genuine share-URL load carries a non-null `allow_modify`, so
		// the plain global `hide_category_service_selection` setting (and its
		// existing auto-select-first-service behaviour below) is untouched when
		// no URL params are present.
		$allow_modify = isset( $preselection['allow_modify'] ) ? $preselection['allow_modify'] : null;
		if ( 0 === $allow_modify ) {
			$hide_service_setting = true;
		} elseif ( 1 === $allow_modify ) {
			$hide_service_setting = false;
		}
		// Fallback: never skip the Service step on a share-URL load when no
		// valid service ended up selected (invalid/deleted/disabled `s_id`),
		// otherwise the user would be stranded on Date & Time with nothing
		// chosen. Gated on `allow_modify !== null` so the global-hide path
		// keeps its own auto-select-first behaviour.
		if ( $hide_service_setting && null !== $allow_modify && (int) $preselection['selected_service'] <= 0 ) {
			$hide_service_setting = false;
		}

		// When hide-service is active and no service was pre-selected via URL,
		// auto-select the first service so the DATETIME step's entry gate passes.
		// $services already reflects shortcode attribute ordering, which takes
		// priority over the DB sort order per the user clarification.
		//
		// Issue 86d326893 (batch-4 add-on): also expose the legacy
		// `bookingpress_modify_default_servide_id` filter so Pro / addons can
		// override the auto-picked id (matches `class.bookingpress_appointment_bookings.php:6609`).
		$effective_selected_service = (int) $preselection['selected_service'];
		if ( $hide_service_setting && $effective_selected_service <= 0 && ! empty( $services ) ) {
			$first = reset( $services );
			$effective_selected_service = isset( $first['serviceId'] ) ? (int) $first['serviceId'] : 0;
			// The Pro handler (`bookingpress_pro_appointment_bookings->bookingpress_update_default_service_id`,
			// class.bookingpress_appointment_bookings.php:18953) reads
			// `$data['bookingpress_all_services_data']` — a map keyed by service id
			// where each entry carries an `is_disabled` flag — and falls back to the
			// first non-disabled service. Legacy passed the whole vue-data array
			// (which holds that key); Vue 3 must supply the same shape or the handler
			// warns ("Undefined array key" + `foreach() … null given`). Vue 3 already
			// drops out-of-window / disabled services upstream via FILTER_SERVICES, so
			// every entry in `$services` is bookable — build the map with
			// `is_disabled => false` accordingly.
			$all_services_map = array();
			foreach ( $services as $svc ) {
				$sid = isset( $svc['serviceId'] ) ? (int) $svc['serviceId'] : 0;
				if ( $sid <= 0 ) {
					continue;
				}
				$all_services_map[ (string) $sid ] = array(
					'bookingpress_service_id' => $sid,
					'is_visible'              => true,
					'is_disabled'             => false,
				);
			}
			$effective_selected_service = (int) apply_filters(
				'bookingpress_modify_default_servide_id',
				$effective_selected_service,
				array(
					'bookingpress_all_services_data' => $all_services_map,
					'services'                       => $services,
					'context'                        => 'vue3_state_builder',
				)
			);
			if ( $effective_selected_service > 0 ) {
				$default_cat_seed = $this->catalog->resolve_default_category(
					$categories,
					$bf_strings,
					array(
						'selected_category' => $this->category_for_service( $services, $effective_selected_service ),
					)
				);
			}
		}

		$steps_schema = $this->steps->build( array(
			'is_service_loaded_from_url' => (int) $preselection['is_from_url'],
			'hide_category_service'      => $hide_service_setting ? '1' : '0',
			'selected_service'           => $effective_selected_service,
		) );

		// 7. Payment methods + auto-select rule (§M0.10).
		$payment_methods    = $this->payment->get_enabled_methods( array() );
		$auto_selected_pay  = ( 1 === count( $payment_methods ) ) ? $payment_methods[0]['id'] : '';

		// 8. Date formatting + caps.
		$today      = current_time( 'Y-m-d' );
		/**
		 * Maximum selectable calendar date. Lite default is today + 1 year; Pro's
		 * "Period available for booking in advance" replaces it with today + the
		 * configured period. This is the seed the calendar uses before the first
		 * timeslot fetch; afterwards the client syncs `config.maxDate` from the
		 * payload's `max_available_date` (same filter, plus any per-service
		 * expiration cap). Inert in Lite (no callback).
		 *
		 * @see Hooks::FILTER_MAX_BOOKING_DATE
		 */
		$max_date   = (string) apply_filters(
			Hooks::FILTER_MAX_BOOKING_DATE,
			gmdate( 'Y-m-d', strtotime( $today . ' +1 year' ) ),
			$today,
			$atts
		);
		// WordPress `start_of_week` is 0-6 (0 = Sunday) but V-Calendar v3
		// `firstDayOfWeek` is 1-7 (1 = Sunday). Convert here so the
		// downstream JS doesn't need to know about the off-by-one.
		// Matches legacy parity at class.bookingpress_settings.php:3019
		// (`= intval(...) + 1`).
		$first_dow  = ( (int) get_option( 'start_of_week', 0 ) ) + 1;

		// 9. Initial form_data seed.
		$step_form_data = array(
			'selected_service'              => (string) ( $effective_selected_service ?: '' ),
			'selected_category'             => isset( $default_cat_seed['selected_category'] ) ? (string) $default_cat_seed['selected_category'] : '0', //if no category defined, then make `All` category default selected
			'selected_cat_name'             => isset( $default_cat_seed['selected_cat_name'] ) ? (string) $default_cat_seed['selected_cat_name'] : '',
			'selected_service_duration'     => '',
			'selected_service_duration_unit' => '',
			'selected_date'                 => '',
			'selected_start_time'           => '',
			'selected_end_time'             => '',
			'selected_payment_method'       => $auto_selected_pay,
			'appointment_terms_conditions'  => array(),
		);
		if ( $effective_selected_service > 0 ) {
			foreach ( $services as $svc ) {
				if ( isset( $svc['serviceId'] ) && (int) $svc['serviceId'] === (int) $effective_selected_service ) {
					$step_form_data['selected_service_duration']      = (string) ( isset( $svc['serviceDurationVal'] ) ? (int) $svc['serviceDurationVal'] : '' );
					$step_form_data['selected_service_duration_unit'] = (string) ( isset( $svc['serviceDurationUnit'] ) ? $svc['serviceDurationUnit'] : '' );
					break;
				}
			}
		}
		// Seed the customer fields with their default values.
		foreach ( $fields_for_wire as $f ) {
			$key = $f['vModelValue'];
			if ( ! isset( $step_form_data[ $key ] ) ) {
				$step_form_data[ $key ] = 'appointment_terms_conditions' === $key ? array() : '';
			}
		}

		// 9b. Pre-fill customer fields from the logged-in user (released-form parity).
		$step_form_data = $this->prefill_logged_in_user( $step_form_data );

		// 10. Initial tab — first step whose entry_gates are satisfied.
		$initial_tab = $this->pick_initial_tab( $steps_schema, $step_form_data );

		// 11. Compose the final payload.
		$state = array(
			'instanceId'  => (string) $instance_id,
			'atts'        => $atts,
			'config'      => array(
				'maxDate'             => $max_date,
				'today'               => $today,
				'firstDayOfWeek'      => $first_dow,
				'dateFormat'          => $this->dates->date_format(),
				'timeFormat'          => $this->dates->time_format(),
				// Exact PHP date() format for time display (e.g. 'H:i',
				// 'g:i a'). Timeslot labels are baked server-side with this
				// same format, so client-side labels built from the raw
				// 'HH:MM' bucket values (Summary step) stay identical to the
				// slot the user picked.
				'phpTimeFormat'       => $this->dates->php_time_format(),
				'currency'            => (string) $this->settings->get( 'payment_default_currency', SettingsRepository::GROUP_PAYMENT, 'USD' ),
				// Currency formatting parity with legacy
				// `bookingpress_price_formatter_with_currency_symbol`
				// (class.bookingpress.php:5997+). The frontend formatPrice
				// helper in utils/currency.js mirrors the same four
				// switches: symbol position, separator preset, decimals,
				// and the custom comma/dot pair (used when separator ==
				// 'Custom').
				'currencySymbol'      => $this->resolve_currency_symbol(
					(string) ( isset( $payment['payment_default_currency'] ) ? $payment['payment_default_currency'] : 'USD' )
				),
				'symbolPosition'      => isset( $payment['price_symbol_position'] ) && '' !== $payment['price_symbol_position']
					? (string) $payment['price_symbol_position']
					: 'before',
				'priceSeparator'      => isset( $payment['price_separator'] ) && '' !== $payment['price_separator']
					? (string) $payment['price_separator']
					: 'comma-dot',
				'priceDecimals'       => isset( $payment['price_number_of_decimals'] ) && '' !== $payment['price_number_of_decimals']
					? (int) $payment['price_number_of_decimals']
					: 2,
				'customCommaSeparator'    => isset( $payment['custom_comma_separator'] ) ? (string) $payment['custom_comma_separator'] : ',',
				'customThousandSeparator' => isset( $payment['custom_dot_separator'] ) ? (string) $payment['custom_dot_separator'] : '.',
				'payment_methods'     => $payment_methods,
				'has_paypal'          => $this->has_method( $payment_methods, 'paypal' ),
				'has_onsite'          => $this->has_method( $payment_methods, 'on-site' ),
				// Sidebar/step-nav layout — `booking_form_tabs_position`
				// in the `booking_form` customize group. Legacy reads
				// the same key in
				// `class.bookingpress_appointment_bookings.php:5477` and
				// branches the wrapper class in
				// `core/views/frontend/appointment_booking_form.php:49`,
				// emitting `bpa-front-tabs--left` when the value is
				// `'left'` and `--bpa-top` otherwise. We surface the
				// raw value here (default `'left'`) so the Vue 3 root
				// can apply the same ternary. The full top-layout CSS
				// lives in the already-enqueued legacy
				// `bookingpress_front.css` (~lines 2695-2737), so this
				// single state field is enough to flip the layout.
				'tabsPosition'        => isset( $bf_strings['booking_form_tabs_position'] ) && '' !== $bf_strings['booking_form_tabs_position']
					? (string) $bf_strings['booking_form_tabs_position']
					: 'left',
				// display_service_description: the customize key is named
				// `display_service_description` but the admin UI labels the
				// switch "Hide Service Description" — when the switch is ON
				// the stored value is 'true' and the description must be HIDDEN
				// (legacy admin preview at core/views/customize/manage_form_customize.php:249
				// renders the description only when the value == false).
				// Default is 'show' when the key is unset.
				'displayServiceDescription' => ! isset( $bf_strings['display_service_description'] ) || 'true' !== (string) $bf_strings['display_service_description'],
				// Hide Service Duration / Hide Service Price — booking_form
				// customize toggles stored as `'true'`/`'false'` (admin switch
				// ON => stored 'true' => HIDDEN). Same inverse-flag convention as
				// `displayServiceDescription`: the config flag means "show", so a
				// stored 'true' (hidden) maps to false here. Default is show when
				// the key is unset. These are Lite-side options gating the Lite
				// ServiceStep specs (not a Pro feature).
				'displayServiceDuration' => ! isset( $bf_strings['hide_service_duration'] ) || 'true' !== (string) $bf_strings['hide_service_duration'],
				'displayServicePrice'    => ! isset( $bf_strings['hide_service_price'] ) || 'true' !== (string) $bf_strings['hide_service_price'],
				// Generic "block the whole form + show the empty illustration"
				// flag — the Vue 3 analog of the legacy
				// `bookingpress_display_no_service_placeholder`. When true the
				// root component hides the step tabs and renders the
				// `#bpa-front-data-empty-view` placeholder ("No categories and
				// services added!"). Lite seeds it FALSE (inert): a Lite-only
				// render is unchanged. It is a reusable seam a Pro feature flips
				// via `FILTER_INITIAL_STATE` — e.g. the Staff Member module
				// blocks the form when it is active but no staff is available.
				'showEmptyPlaceholder'   => false,
				// Generic seams for the Booking Form Sequence placing the Service
				// step AFTER Date & Time (the grid then loads before a service is
				// chosen). Both inert in Lite:
				//  - `slotServiceDriverId` (0 = unset): a service id `useTimeslots`
				//    loads the grid for when no service is selected yet; a Pro
				//    feature sets it to a carrier service so the serviceless grid
				//    can be built + cached. 0 → fall back to the chosen service.
				//  - `preserveDatetimeOnServiceChange` (false): when true the Service
				//    step does NOT clear the already-chosen date/time on selection
				//    (the date/time came first); the Pro layer recomputes the
				//    duration-aware end time from the now-known service.
				'slotServiceDriverId'            => 0,
				'preserveDatetimeOnServiceChange' => false,
				'defaultCountry'      => strtolower( (string) $this->settings->get( 'default_phone_country_code', SettingsRepository::GROUP_GENERAL, 'us' ) ),
				'afternoonStartHour'  => (int) explode( ':', (string) $this->settings->get( 'bpa_afternoon_start_time', SettingsRepository::GROUP_GENERAL, '12:00:00' ) )[0],
				'eveningStartHour'    => (int) explode( ':', (string) $this->settings->get( 'bpa_evening_start_time',   SettingsRepository::GROUP_GENERAL, '17:00:00' ) )[0],
				'nightStartHour'      => (int) explode( ':', (string) $this->settings->get( 'bpa_night_start_time',    SettingsRepository::GROUP_GENERAL, '21:00:00' ) )[0],
			),
			'services'              => $services,
			'categories'            => $categories,
			'customer_form_fields'  => $fields_for_wire,
			'customer_details_rule' => $rules,
			'customer_details_msg'  => $messages,
			'steps'                 => $steps_schema,
			'currentTab'            => $initial_tab,
			'appointment_step_form_data' => $step_form_data,
			'strings'               => $this->compose_strings( $bf_strings, $message_settings ),
			'preselection'          => $preselection,
		);

		/**
		 * Reshape the entire initial-state payload before it leaves the builder.
		 *
		 * @param array  $state
		 * @param array  $atts
		 * @param string $instance_id
		 */
		$state = (array) apply_filters( Hooks::FILTER_INITIAL_STATE, $state, $atts, $instance_id );

		return $state;
	}

	/**
	 * Whether the given service id is present in the bookable service list
	 * (the post-`FILTER_SERVICES` payload). Used to reject a preselected
	 * service that exists in the table but is not actually bookable
	 * (disabled / outside its booking window / dropped by an add-on).
	 *
	 * @param array $services
	 * @param int   $service_id
	 *
	 * @return bool
	 */
	private function service_is_bookable( array $services, $service_id ) {
		$service_id = (int) $service_id;
		if ( $service_id <= 0 ) {
			return false;
		}
		foreach ( $services as $s ) {
			if ( isset( $s['serviceId'] ) && (int) $s['serviceId'] === $service_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Find the category id for a given service row (or empty string).
	 *
	 * @param array $services
	 * @param int   $service_id
	 *
	 * @return string
	 */
	private function category_for_service( array $services, $service_id ) {
		foreach ( $services as $s ) {
			if ( (int) $s['serviceId'] === (int) $service_id ) {
				return isset( $s['categoryId'] ) && $s['categoryId'] > 0 ? (string) $s['categoryId'] : '';
			}
		}
		return '';
	}

	/**
	 * Pre-fill the customer-field slots of `appointment_step_form_data`
	 * from the currently-logged-in WordPress user. Mirrors the released
	 * form's path in `class.bookingpress_appointment_bookings.php` lines
	 * 6667-6731:
	 *
	 *   1. If the user is logged in AND a `bookingpress_customers` row
	 *      exists (matched by `bookingpress_wpuser_id` + `user_type=2`),
	 *      use the stored firstname/lastname/email/phone/username/full_name.
	 *
	 *   2. Otherwise fall back to WP user data — `user_login` /
	 *      `user_email` + `first_name` / `last_name` user-meta.
	 *
	 *   3. Anonymous users get an unmodified seed (empty strings).
	 *
	 * Only writes keys that already exist in `$step_form_data` (so a
	 * disabled field that isn't in the form schema doesn't get a stray
	 * value), and never overwrites a non-empty existing value (so URL-
	 * preselection or shortcode-supplied seeds always win).
	 *
	 * @param array $step_form_data
	 *
	 * @return array
	 */
	private function prefill_logged_in_user( array $step_form_data ) {
		if ( ! function_exists( 'is_user_logged_in' ) || ! is_user_logged_in() ) {
			return $step_form_data;
		}
		$wp_user_id = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
		if ( $wp_user_id <= 0 ) {
			return $step_form_data;
		}

		// Map of `appointment_step_form_data` key → resolved value. Built
		// once below from either the customer row or the WP user fallback,
		// then applied with the "only-fill-if-empty" rule.
		$prefill = array();

		$customer = $this->customers->find_by_wp_user_id( $wp_user_id );
		if ( is_array( $customer ) ) {
			// Released-form parity (class.bookingpress_appointment_bookings.php:6694):
			// the phone field is pre-filled with the stored
			// `bookingpress_user_phone` value verbatim. The legacy form does
			// NOT reconstruct a `+<dial><local>` E.164 string and does NOT
			// blank values that "look non-numeric" — it assigns the column
			// as-is and lets the tel-input's `default-country` (seeded from
			// the `default_phone_country_code` setting via
			// `config.defaultCountry`, mirroring legacy's `customer_phone_country`)
			// pick the flag. The previous reconstruction/guard diverged from
			// legacy and dropped values the released form displayed (e.g. a
			// stored "test"), which is why the field came up empty.
			$prefill = array(
				'customer_firstname' => (string) ( $customer['userFirstname'] ?? '' ),
				'customer_lastname'  => (string) ( $customer['userLastname'] ?? '' ),
				'customer_name'      => (string) ( $customer['customerFullName'] ?? '' ),
				'customer_username'  => (string) ( $customer['userName'] ?? '' ),
				'customer_email'     => (string) ( $customer['userEmail'] ?? '' ),
				'customer_phone'     => (string) ( $customer['userPhone'] ?? '' ),
			);
		} else {
			$wp_user = function_exists( 'get_userdata' ) ? get_userdata( $wp_user_id ) : null;
			if ( $wp_user ) {
				$firstname = function_exists( 'get_user_meta' ) ? (string) get_user_meta( $wp_user_id, 'first_name', true ) : '';
				$lastname  = function_exists( 'get_user_meta' ) ? (string) get_user_meta( $wp_user_id, 'last_name', true ) : '';
				$login     = isset( $wp_user->user_login ) ? (string) $wp_user->user_login : '';
				$email     = isset( $wp_user->user_email ) ? (string) $wp_user->user_email : '';
				$prefill = array(
					'customer_firstname' => $firstname,
					'customer_lastname'  => $lastname,
					'customer_name'      => trim( $firstname . ' ' . $lastname ) !== '' ? trim( $firstname . ' ' . $lastname ) : $login,
					'customer_username'  => $login,
					'customer_email'     => $email,
					// Phone has no WP-meta equivalent in core; left empty.
					'customer_phone'     => '',
				);
			}
		}

		foreach ( $prefill as $key => $val ) {
			// Only fill keys the schema actually exposes, and never
			// overwrite a non-empty pre-existing value (preselection
			// from URL / shortcode wins).
			if ( ! array_key_exists( $key, $step_form_data ) ) {
				continue;
			}
			$existing = $step_form_data[ $key ];
			if ( $existing === '' || $existing === null ) {
				$step_form_data[ $key ] = $val;
			}
		}

		/**
		 * Reshape the prefilled step_form_data before it leaves the
		 * builder. Add-ons can inject Pro custom-field seeds here.
		 *
		 * @param array $step_form_data
		 * @param int   $wp_user_id
		 */
		return (array) apply_filters( 'bookingpress_form_v3_prefill_step_form_data', $step_form_data, $wp_user_id );
	}

	/**
	 * Pluck the entry/field shape M6 will consume, including the v_model_value
	 * + field_type contracts from §M0.8. Mirrors the legacy switch verbatim.
	 *
	 * @param array $rows    Raw field rows (camelCase, post FormFieldRepository).
	 * @param array $general General-settings group (for phone_number_mandatory).
	 *
	 * @return array
	 */
	private function prepare_form_fields( array $rows, array $general ) {
		$phone_required_setting = isset( $general['phone_number_mandatory'] ) && 'true' === $general['phone_number_mandatory'];

		$out = array();
		foreach ( $rows as $r ) {
			$name = isset( $r['fieldName'] ) ? (string) $r['fieldName'] : '';
			if ( '' === $name ) {
				continue;
			}

			// v_model_value mapping (§M0.8 — verbatim).
			$vmodel = '';
			switch ( $name ) {
				case 'fullname':
					$vmodel = 'customer_name';
					break;
				case 'firstname':
					$vmodel = 'customer_firstname';
					break;
				case 'lastname':
					$vmodel = 'customer_lastname';
					break;
				case 'email_address':
					$vmodel = 'customer_email';
					break;
				case 'phone_number':
					$vmodel = 'customer_phone';
					break;
				case 'note':
					$vmodel = 'appointment_note';
					break;
				case 'username':
					$vmodel = 'customer_username';
					break;
				case 'terms_and_conditions':
					$vmodel = 'appointment_terms_conditions';
					break;
				default:
					// Custom field — uses meta_key (Pro schema only).
					$vmodel = isset( $r['fieldMetaKey'] ) && '' !== (string) $r['fieldMetaKey']
						? (string) $r['fieldMetaKey']
						: '';
			}
			if ( '' === $vmodel ) {
				continue;
			}

			// field_type mapping (§M0.8 — verbatim).
			$type = 'Text';
			switch ( $name ) {
				case 'fullname':
				case 'firstname':
				case 'lastname':
				case 'username':
					$type = 'Text';
					break;
				case 'email_address':
					$type = 'Email';
					break;
				case 'phone_number':
					$type = 'Phone';
					break;
				case 'note':
					$type = 'Textarea';
					break;
				case 'terms_and_conditions':
					$type = 'terms_and_conditions';
					break;
				default:
					$type = isset( $r['fieldType'] ) && '' !== (string) $r['fieldType']
						? (string) $r['fieldType']
						: 'Text';
			}

			$required = (bool) ( ! empty( $r['fieldRequired'] ) );
			if ( 'phone_number' === $name && $phone_required_setting ) {
				$required = true;
			}

			$out[] = array(
				'fieldId'         => isset( $r['fieldId'] ) ? (int) $r['fieldId'] : 0,
				'fieldName'       => $name,
				'fieldLabel'      => isset( $r['fieldLabel'] ) ? (string) $r['fieldLabel'] : '',
				'fieldPlaceholder' => isset( $r['fieldPlaceholder'] ) ? (string) $r['fieldPlaceholder'] : '',
				'fieldType'       => $type,
				'fieldRequired'   => $required,
				'fieldErrorMessage' => isset( $r['fieldErrorMessage'] ) ? (string) $r['fieldErrorMessage'] : '',
				'fieldOptions'    => isset( $r['fieldOptions'] ) ? $r['fieldOptions'] : null,
				'vModelValue'     => $vmodel,
				'isBuiltin'       => ! empty( $r['isBuiltin'] ),
				'isCustom'        => ! empty( $r['isCustom'] ),
				'maxlength'       => in_array( $name, array( 'fullname', 'firstname', 'lastname', 'email_address', 'username' ) ) ? 255 : ( 'phone_number' === $name ? 63 : null ),
			);
		}
		return $out;
	}

	/**
	 * Build the customer_details_rule map for Element-Plus.
	 *
	 * Per §M0.8:
	 *   - required: `[{ required: true, message: <label>, trigger: 'blur' }]`
	 *   - terms required: `[{ required: true, message: <label>, trigger: 'change' }]`
	 *   - email: append `{ type: 'email', message: <emailMsg>, trigger: 'blur' }`.
	 *
	 * @param array $fields
	 * @param array $bf_strings  Customize strings.
	 *
	 * @return array<string, array>
	 */
	private function build_field_rules( array $fields, array $bf_strings ) {
		$rules = array();
		foreach ( $fields as $f ) {
			$key      = $f['vModelValue'];
			$required = ! empty( $f['fieldRequired'] );
			if ( ! $required ) {
				if ( 'Email' === $f['fieldType'] ) {
					$rules[ $key ] = array( array(
						'type'    => 'email',
						'message' => $this->t_or( $bf_strings, 'invalid_email_message', 'Please enter a valid email address' ),
						'trigger' => 'blur',
					) );
				}
				continue;
			}

			$trigger = ( 'terms_and_conditions' === $f['fieldName'] ) ? 'change' : 'blur';
			$rule = array( array(
				'required' => true,
				'message'  => $f['fieldErrorMessage'] !== '' ? $f['fieldErrorMessage'] : $f['fieldLabel'],
				'trigger'  => $trigger,
			) );

			if ( 'Email' === $f['fieldType'] ) {
				$rule[] = array(
					'type'    => 'email',
					'message' => $this->t_or( $bf_strings, 'invalid_email_message', 'Please enter a valid email address' ),
					'trigger' => 'blur',
				);
			}
			$rules[ $key ] = $rule;
		}
		return $rules;
	}

	/**
	 * Build the customer_details_msg map.
	 *
	 * @param array $fields
	 *
	 * @return array<string, string>
	 */
	private function build_field_messages( array $fields ) {
		$out = array();
		foreach ( $fields as $f ) {
			$out[ $f['vModelValue'] ] = $f['fieldErrorMessage'] !== '' ? $f['fieldErrorMessage'] : $f['fieldLabel'];
		}
		return $out;
	}

	/**
	 * Compose the strings bag for the client (labels, button text, etc.).
	 *
	 * @param array $bf       Customize-layer strings (`booking_form` group).
	 * @param array $messages Settings-layer messages (`message_setting` group),
	 *                        supplies the per-step validation copy that the
	 *                        released form surfaces as toast errors.
	 *
	 * @return array
	 */
	private function compose_strings( array $bf, array $messages = array() ) {
		$d = function ( $key, $default ) use ( $bf ) {
			return isset( $bf[ $key ] ) && '' !== $bf[ $key ] ? (string) $bf[ $key ] : $default;
		};
		$m = function ( $key, $default ) use ( $messages ) {
			return isset( $messages[ $key ] ) && '' !== $messages[ $key ] ? (string) $messages[ $key ] : $default;
		};
		return array(
			'service_heading'         => $d( 'service_heading_title', 'Select Service' ),
			'category_heading'        => $d( 'category_title', 'Select Category' ),
			'all_category_label'      => $d( 'all_category_title', 'All' ),
			'service_duration_label'  => $d( 'service_duration_label', 'Duration:' ),
			'service_price_label'     => $d( 'service_price_label', 'Price:' ),
			'next_button'             => $d( 'next_button_text', 'Next' ),
			'goback_button'           => $d( 'goback_button_text', 'Go Back' ),
			'book_button'             => $d( 'book_appointment_btn_text', 'Book Appointment' ),
			'total_amount_label'      => $d( 'total_amount_text', 'Total Amount Payable' ),
			'no_service_text'         => $d( 'no_service_text', 'No services available.' ),
			'no_categories_services'  => $d( 'no_categories_services_text', 'No categories and services added!' ),
			'no_timeslot_available'   => $d( 'no_timeslot_available', 'No time slots available for the selected date.' ),
			'select_date_title'       => $d( 'select_date_title', 'Select Date' ),
			'timeslot_text'           => $d( 'timeslot_text', 'Available Time Slots' ),
			'morning_text'            => $d( 'morning_text', 'Morning' ),
			'afternoon_text'          => $d( 'afternoon_text', 'Afternoon' ),
			'evening_text'            => $d( 'evening_text', 'Evening' ),
			'night_text'              => $d( 'night_text', 'Night' ),
			'service_step_name'       => $d( 'service_title', 'Service' ),
			'datetime_step_name'      => $d( 'datetime_title', 'Date & Time' ),
			'basic_details_step_name' => $d( 'basic_details_title', 'Your Details' ),
			'summary_step_name'       => $d( 'summary_title', 'Summary' ),
			'customer_text'           => $d( 'customer_text', 'Customer' ),
			'service_text'            => $d( 'service_text', 'Service' ),
			'date_time_text'          => $d( 'date_time_text', 'Date & Time' ),
			'appointment_details_title' => $d( 'appointment_details_title_text', 'Appointment Details' ),
			'payment_method_label'    => $d( 'payment_method_text', 'Select Payment Method' ),
			'pay_locally'             => $d( 'locally_text', 'Pay Locally' ),
			'paypal'                  => $d( 'paypal_text', 'PayPal' ),
			// Step-level note / intro copy. Legacy customize keys
			// (`bookingpress_customize_settings` group `booking_form`):
			//   - `summary_content_text` ("Your appointment booking summary")
			//   - `summary_step_note`    (admin-defined HTML shown below)
			//   - `date_time_step_note`  (admin-defined HTML on Date & Time step)
			// Default to '' so the templates' `v-if` guards skip rendering
			// when the admin hasn't set any copy.
			'summary_content_text'    => $d( 'summary_content_text', '' ),
			'summary_step_note'       => $d( 'summary_step_note', '' ),
			'date_time_step_note'     => $d( 'date_time_step_note', '' ),
			// Validation toast copy — surfaced when the user presses
			// "Next" / "Book Appointment" without making the required
			// selection on a step. Sourced from the `message_setting`
			// group exactly like the released form's
			// `bookingpress_step_navigation()` path.
			'no_service_selected_error'    => $m( 'no_service_selected_for_the_booking',          'Please select any service to book the appointment' ),
			'no_appointment_date_error'    => $m( 'no_appointment_date_selected_for_the_booking', 'Please select appointment date to proceed with the booking.' ),
			'no_appointment_time_error'    => $m( 'no_appointment_time_selected_for_the_booking', 'Please select a time slot to proceed with the booking.' ),
			'no_payment_method'            => $m( 'no_payment_method_available',                  'Oops! There is no payment method available.' ),
			'no_payment_method_picked'     => $m( 'no_payment_method_is_selected_for_the_booking', 'Please select a payment method to proceed with the booking.' ),
		);
	}

	/**
	 * Pick the initial `currentTab` per §M0.9.B.
	 *
	 * "Pick the first step (by sorted order) whose entry_gates are already
	 * satisfied. If none, pick the first step regardless."
	 *
	 * @param array $steps
	 * @param array $form_data
	 *
	 * @return string
	 */
	private function pick_initial_tab( array $steps, array $form_data ) {
		// Build a quick lookup of which gates are currently passing based on
		// the seeded form_data. Lite's gate.service: requires non-empty
		// selected_service; gate.datetime: requires both date+time.
		$gates = array(
			StepSchemaService::STEP_SERVICE  => '' !== (string) ( isset( $form_data['selected_service'] ) ? $form_data['selected_service'] : '' ),
			StepSchemaService::STEP_DATETIME => '' !== (string) ( isset( $form_data['selected_date'] ) ? $form_data['selected_date'] : '' )
				&& '' !== (string) ( isset( $form_data['selected_start_time'] ) ? $form_data['selected_start_time'] : '' ),
		);

		foreach ( $steps as $step ) {
			if ( empty( $step['is_display_step'] ) ) {
				continue;
			}
			$entry_gates = isset( $step['entry_gates'] ) && is_array( $step['entry_gates'] ) ? $step['entry_gates'] : array();
			$all_pass = true;
			foreach ( $entry_gates as $g ) {
				if ( empty( $gates[ $g ] ) ) {
					$all_pass = false;
					break;
				}
			}
			if ( $all_pass ) {
				return (string) $step['id'];
			}
		}
		return isset( $steps[0]['id'] ) ? (string) $steps[0]['id'] : '';
	}

	/**
	 * @param array $payment_methods
	 * @param string $id
	 *
	 * @return bool
	 */
	private function has_method( array $payment_methods, $id ) {
		foreach ( $payment_methods as $m ) {
			if ( isset( $m['id'] ) && $id === $m['id'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Read a customize string with fallback.
	 *
	 * @param array  $bf
	 * @param string $key
	 * @param string $default
	 *
	 * @return string
	 */
	private function t_or( array $bf, $key, $default ) {
		return isset( $bf[ $key ] ) && '' !== $bf[ $key ] ? (string) $bf[ $key ] : $default;
	}

	/**
	 * Resolve an ISO currency code to its display symbol via the legacy
	 * countries JSON (the same source `bookingpress_get_currency_symbol`
	 * uses in class.bookingpress.php:5939). Falls back to the ISO code
	 * when the global isn't initialised or the code isn't listed.
	 *
	 * @param string $code
	 *
	 * @return string
	 */
	private function resolve_currency_symbol( $code ) {
		$code = (string) $code;
		if ( '' === $code ) {
			return '';
		}
		global $bookingpress_global_options;
		if ( is_object( $bookingpress_global_options ) && method_exists( $bookingpress_global_options, 'bookingpress_global_options' ) ) {
			$opts = $bookingpress_global_options->bookingpress_global_options();
			if ( ! empty( $opts['countries_json_details'] ) ) {
				$rows = json_decode( $opts['countries_json_details'] );
				if ( is_array( $rows ) ) {
					foreach ( $rows as $row ) {
						if ( isset( $row->code, $row->symbol ) && $row->code === $code ) {
							return (string) $row->symbol;
						}
					}
				}
			}
		}
		return $code;
	}
}

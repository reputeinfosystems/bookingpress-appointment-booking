<?php
/**
 * Vue 3 + Element Plus frontend booking form shortcode.
 *
 * Registers `[bookingpress_form_vue3]` and bootstraps the pre-built Vue 3
 * / BpUi* asset bundle shipped in `src/assets/`. Reuses the same WordPress
 * script-module architecture already adopted by the admin Calendar page.
 *
 * @package BookingPress
 */

namespace BookingPress\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vue 3 booking form controller.
 */
class BookingForm extends Base {

	/**
	 * Shortcode tag.
	 */
	const SHORTCODE = 'bookingpress_form_vue3';

	/**
	 * Script module handles.
	 */
	const MODULE_VUE         = 'vue';
	const MODULE_UI          = 'bookingpress-ui';
	const MODULE_VCALENDAR   = 'bookingpress-vcalendar';
	const MODULE_FORM        = 'bookingpress-form-vue3';
	const MODULE_FORM_LOADER = 'bookingpress-form-vue3-loader';

	/**
	 * Style handles.
	 */
	const STYLE_UI        = 'bookingpress-ui';
	const STYLE_VCALENDAR = 'bookingpress-vcalendar';
	const STYLE_FORM      = 'bookingpress-form-vue3';

	/**
	 * Per-instance state, keyed by unique shortcode instance ID.
	 *
	 * Populated by `render_shortcode()` and consumed by the
	 * `script_module_data_bookingpress-form-vue3-loader` filter,
	 * which WordPress emits as a JSON island for the loader module.
	 *
	 * @var array<string,array>
	 */
	private static $instances_data = array();

	/**
	 * Idempotency guard for asset registration.
	 *
	 * @var bool
	 */
	private static $assets_registered = false;

	/**
	 * Register hooks.
	 *
	 * **M7 — intentionally a no-op.** Before M7 this class registered
	 * `[bookingpress_form_vue3]` and conditionally overrode `[bookingpress_form]`,
	 * and ran the JSON-island filter for its own loader handle. All of
	 * those responsibilities moved to `BookingPress\Vue3\Routing` and
	 * `BookingPress\Vue3\Assets` in M7. This `init()` stays as a method on
	 * the class so existing callers (`BookingPressLoader::init` and any
	 * third-party that may have referenced this) keep working without
	 * fatal errors — it simply does nothing now.
	 *
	 * The class file itself remains in the tree through one deprecation
	 * cycle. M9 (or a later cleanup ticket) will remove it.
	 *
	 * Called from `BookingPress\BookingPressLoader::init()` on `plugins_loaded`.
	 *
	 * @return void
	 */
	public static function init() {
		// no-op — see method-level docblock.
	}

	/**
	 * Whether the BookingPress Pro add-on is currently active.
	 *
	 * Loads `wp-admin/includes/plugin.php` on demand because frontend
	 * requests do not pull it by default.
	 *
	 * @return bool
	 */
	private static function is_pro_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$plugin_file = ABSPATH . 'wp-admin/includes/plugin.php';
			if ( ! file_exists( $plugin_file ) ) {
				return false;
			}
			require_once $plugin_file;
		}
		return is_plugin_active( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' );
	}

	/**
	 * Register all script modules and styles used by the shortcode.
	 *
	 * Registers only; enqueuing is handled inside `render_shortcode()`
	 * so assets load only on pages that actually contain the shortcode.
	 *
	 * @return void
	 */
	public static function register_assets() {
		if ( self::$assets_registered ) {
			return;
		}
		self::$assets_registered = true;

		if ( ! function_exists( 'wp_register_script_module' ) ) {
			return;
		}

		$base    = untrailingslashit( BOOKINGPRESS_URL );
		$version = static::$version;

		wp_register_script_module(
			self::MODULE_VUE,
			$base . '/src/assets/js/vue.min.js',
			array(),
			$version
		);

		wp_register_script_module(
			self::MODULE_UI,
			$base . '/src/assets/js/bookingpress-ui.min.js',
			array(),
			$version
		);

		// VCalendar ships as its own self-contained bundle (its own Vue
		// runtime baked in). Do NOT declare `vue` as a dep — that would
		// double-load. Bridge to our Vue 3 app is via `window.BpVCalendar`
		// which the bundle exposes on load.
		wp_register_script_module(
			self::MODULE_VCALENDAR,
			$base . '/src/assets/js/bp-vcalendar.js',
			array(),
			$version
		);

		$form_script = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			? 'booking-form.js'
			: 'booking-form.min.js';

		wp_register_script_module(
			self::MODULE_FORM,
			$base . '/src/assets/js/' . $form_script,
			array( self::MODULE_VUE, self::MODULE_UI, self::MODULE_VCALENDAR ),
			$version
		);

		wp_register_script_module(
			self::MODULE_FORM_LOADER,
			$base . '/src/assets/js/booking-form-loader.js',
			array( self::MODULE_FORM, self::MODULE_UI ),
			$version
		);

		wp_register_style(
			self::STYLE_UI,
			$base . '/src/assets/css/bookingpress-ui.min.css',
			array(),
			$version
		);

		wp_register_style(
			self::STYLE_VCALENDAR,
			$base . '/src/assets/css/bp-vcalendar.css',
			array(),
			$version
		);

		wp_register_style(
			self::STYLE_FORM,
			$base . '/src/assets/css/booking-form.css',
			array( self::STYLE_UI, self::STYLE_VCALENDAR ),
			$version
		);

		wp_register_style(
			'bookingpress-bp-theme',
			$base . '/src/assets/css/bookingpress_bp_theme.css',
			array(),
			$version
		);
	}

	/**
	 * Shortcode callback: renders one Vue 3 mount point per invocation.
	 *
	 * @param array|string $atts    Raw shortcode attributes.
	 * @param string|null  $content Inner content (unused).
	 * @param string       $tag     Shortcode tag name.
	 *
	 * @return string
	 */
	public static function render_shortcode( $atts, $content = null, $tag = '' ) {
		$atts = shortcode_atts(
			array(
				'service'          => '',
				'category'         => '',
				'selected_service' => '',
				'selected_staff'   => '',
			),
			is_array( $atts ) ? $atts : array(),
			self::SHORTCODE
		);

		$atts = array_map( 'sanitize_text_field', $atts );

		$uniq_id       = self::generate_unique_id();
		$initial_state = self::build_initial_state( $uniq_id, $atts );

		/**
		 * Filter the initial Vue 3 state payload for a shortcode instance.
		 *
		 * @param array  $initial_state Initial state passed to the Vue app.
		 * @param array  $atts          Sanitized shortcode attributes.
		 * @param string $uniq_id       Unique instance identifier.
		 */
		$initial_state = apply_filters(
			'bookingpress_form_vue3_initial_state',
			$initial_state,
			$atts,
			$uniq_id
		);

		self::$instances_data[ $uniq_id ] = $initial_state;

		// Safety: ensure assets are registered even if the shortcode runs
		// outside a normal `wp_enqueue_scripts` flow (e.g. REST render).
		self::register_assets();
		self::enqueue_for_render();

		return parent::render_view(
			'appointment_booking_form_vue3',
			array(
				'uniq_id'       => $uniq_id,
				'atts'          => $atts,
				'initial_state' => $initial_state,
			)
		);
	}

	/**
	 * Inject per-instance state into the loader's script module data island.
	 *
	 * @param mixed $data Existing module data.
	 *
	 * @return array
	 */
	public static function filter_module_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( empty( $data['instances'] ) || ! is_array( $data['instances'] ) ) {
			$data['instances'] = array();
		}

		$data['instances'] = array_merge( $data['instances'], self::$instances_data );

		/**
		 * Filter the full module data payload before it is emitted as JSON.
		 *
		 * @param array $data Module data (includes `instances` map).
		 */
		return apply_filters( 'bookingpress_form_vue3_module_data', $data );
	}

	/**
	 * Enqueue the loader module and styles for a concrete render pass.
	 *
	 * @return void
	 */
	private static function enqueue_for_render() {
		if ( function_exists( 'wp_enqueue_script_module' ) ) {
			wp_enqueue_script_module( self::MODULE_FORM_LOADER );
		}

		if ( ! wp_style_is( self::STYLE_UI, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_UI );
		}

		if ( ! wp_style_is( self::STYLE_VCALENDAR, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_VCALENDAR );
		}

		if ( ! wp_style_is( self::STYLE_FORM, 'enqueued' ) ) {
			wp_enqueue_style( self::STYLE_FORM );
		}

		if ( ! wp_style_is( 'bookingpress-bp-theme', 'enqueued' ) ) {
			wp_enqueue_style( 'bookingpress-bp-theme' );
		}

		// Load the legacy-generated dynamic customize CSS (primary color,
		// fonts, background, custom_css). The file is written by
		// bookingpress_generate_customize_css_func() under wp-content/uploads
		// and enqueued as the `bookingpress_front_custom_css` style handle.
		// Without this call, Vue 3 renders with only the base styles from
		// bookingpress_front.css — customize settings silently do nothing.
		$helper = self::get_legacy_helper();
		if ( $helper && method_exists( $helper, 'bookingpress_load_booking_form_custom_css' ) ) {
			$helper->bookingpress_load_booking_form_custom_css();
		}

		// PayPal JS SDK — port of legacy `bookingpress_paypal_scripts_add`
		// (class.bookingpress_appointment_bookings.php:683). The legacy
		// hook fires on `bookingpress_add_frontend_js` which the V3
		// shortcode does NOT trigger, so we enqueue here explicitly when
		// PayPal popup mode is enabled. Required for the v3
		// `renderPayPalButtons()` `paypal.Buttons(...).render(...)` call
		// in SummaryStep.js to work.
		self::maybe_enqueue_paypal_sdk();
	}

	/**
	 * Enqueue the PayPal JS SDK if PayPal popup mode is enabled and the
	 * credentials are set. Mirrors legacy `bookingpress_paypal_scripts_add`
	 * 1:1 so popup mode behaves the same way in the Vue 3 render path.
	 *
	 * @return void
	 */
	private static function maybe_enqueue_paypal_sdk() {
		$helper = self::get_legacy_helper();
		if ( ! $helper || ! method_exists( $helper, 'bookingpress_get_settings' ) ) {
			return;
		}

		$paypal_payment      = $helper->bookingpress_get_settings( 'paypal_payment', 'payment_setting' );
		$paypal_payment_mode = $helper->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );

		// Treat the stored value the same way PaymentService::is_truthy does.
		$is_paypal_on = ( 'true' === strtolower( (string) $paypal_payment ) || '1' === (string) $paypal_payment );
		if ( ! $is_paypal_on || 'popup' !== (string) $paypal_payment_mode ) {
			return;
		}

		$client_id     = (string) $helper->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
		$client_secret = (string) $helper->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );
		if ( '' === $client_id || '' === $client_secret ) {
			return;
		}

		$currency_name = (string) $helper->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
		$currency_code = method_exists( $helper, 'bookingpress_get_currency_code' )
			? (string) $helper->bookingpress_get_currency_code( $currency_name )
			: ( '' !== $currency_name ? $currency_name : 'USD' );
		if ( '' === $currency_code ) {
			$currency_code = 'USD';
		}

		if ( ! wp_script_is( 'bookingpress-paypal-script', 'enqueued' ) && ! wp_script_is( 'bookingpress-paypal-script', 'registered' ) ) {
			wp_enqueue_script(
				'bookingpress-paypal-script',
				'https://www.paypal.com/sdk/js?client-id=' . rawurlencode( $client_id ) . '&currency=' . rawurlencode( $currency_code ) . '&disable-funding=credit,card',
				array(),
				null,
				true
			);
		}
	}

	/**
	 * Retrieve the legacy BookingPress service/category helpers instance.
	 */
	private static function get_legacy_helper() {
		if ( isset( $GLOBALS['BookingPress'] ) && is_object( $GLOBALS['BookingPress'] ) ) {
			return $GLOBALS['BookingPress'];
		}
		return null;
	}

	/**
	 * Resolve the shortcode-driven service preselection precedence.
	 */
	private static function resolve_service_preselection( $atts ) {
		$service          = '';
		$category         = isset( $atts['category'] ) ? intval( $atts['category'] ) : 0;
		$selected_service = isset( $atts['selected_service'] ) ? intval( $atts['selected_service'] ) : 0;
		$is_from_url      = 0;

		if ( ! empty( $atts['service'] ) ) {
			$service_csv = array_filter( array_map( 'intval', explode( ',', (string) $atts['service'] ) ) );
			if ( ! empty( $service_csv ) ) {
				$service = implode( ',', $service_csv );
			}
		}

		$bpservice_id = isset( $_GET['bpservice_id'] ) ? intval( wp_unslash( $_GET['bpservice_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$s_id         = isset( $_GET['s_id'] ) ? intval( wp_unslash( $_GET['s_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $bpservice_id ) && empty( $s_id ) ) {
			$selected_service = $bpservice_id;
			$is_from_url      = 1;
		} elseif ( ! empty( $s_id ) ) {
			$selected_service = $s_id;
			$service          = '';
			$category         = 0;
			$is_from_url      = 1;
		}

		return array(
			'service'          => $service,
			'category'         => $category,
			'selected_service' => $selected_service,
			'is_from_url'      => $is_from_url,
		);
	}

	/**
	 * Hydrate services + categories + service-tab customize strings.
	 */
	private static function hydrate_service_category_data( $atts ) {
		$helper = self::get_legacy_helper();
		if ( null === $helper ) {
			return array();
		}

		$pre = self::resolve_service_preselection( $atts );

		$all_services = array();
		if ( method_exists( $helper, 'bookingpress_retrieve_all_services' ) ) {
			$all_services = $helper->bookingpress_retrieve_all_services(
				$pre['service'],
				$pre['selected_service'],
				$pre['category']
			);
		}

		$all_categories = array();
		if ( method_exists( $helper, 'bookingpress_retrieve_all_categories' ) ) {
			$all_categories = $helper->bookingpress_retrieve_all_categories( $all_services );
		}

		$hide_category_selection = false;
		if ( is_array( $all_categories ) && 1 === count( $all_categories ) && ! empty( $all_categories[0]['category_id'] ) ) {
			$hide_category_selection = true;
		}

		$no_service_placeholder = empty( $all_services );

		$seed = array();
		if ( ! empty( $pre['selected_service'] ) && isset( $all_services[ (string) $pre['selected_service'] ] ) ) {
			$svc                                    = $all_services[ (string) $pre['selected_service'] ];
			$seed['selected_service']               = (string) $pre['selected_service'];
			$seed['selected_service_name']          = isset( $svc['bookingpress_service_name'] ) ? (string) $svc['bookingpress_service_name'] : '';
			$seed['selected_service_price']         = isset( $svc['bookingpress_service_price'] ) ? (string) $svc['bookingpress_service_price'] : '';
			$seed['service_price_without_currency'] = isset( $svc['service_price_without_currency'] ) ? (string) $svc['service_price_without_currency'] : '';
			$seed['selected_service_duration']      = isset( $svc['bookingpress_service_duration_val'] ) ? (string) $svc['bookingpress_service_duration_val'] : '';
			$seed['selected_service_duration_unit'] = isset( $svc['bookingpress_service_duration_unit'] ) ? (string) $svc['bookingpress_service_duration_unit'] : '';
			$seed['selected_category']              = isset( $svc['bookingpress_category_id'] ) ? (string) $svc['bookingpress_category_id'] : '';
		} elseif ( ! empty( $pre['category'] ) ) {
			$seed['selected_category'] = (string) $pre['category'];
		}

		// Legacy parity (class.bookingpress_appointment_bookings.php L6601-L6803):
		// `hide_category_service` mirrors the admin setting
		// `hide_category_service_selection`. It is NOT auto-enabled when a
		// service is preselected or when the form is loaded via share URL —
		// legacy actually inverts it back to 'false' in exactly those cases
		// (L6802) so the Service sidebar step and Go Back button stay
		// reachable. Real value is computed below once $settings_map is
		// populated; this is a defensive default for the rare path where
		// the settings helper is unavailable.
		$hide_category_service = '0';

		$strings = array(
			'category_title'         => __( 'Select Category', 'bookingpress-appointment-booking' ),
			'service_heading_title'  => __( 'Select Service', 'bookingpress-appointment-booking' ),
			'service_duration_text'  => __( 'Duration:', 'bookingpress-appointment-booking' ),
			'service_price_text'     => __( 'Price:', 'bookingpress-appointment-booking' ),
			'next_btn_text'          => __( 'Next', 'bookingpress-appointment-booking' ),
			'goback_btn_text'        => __( 'Go Back', 'bookingpress-appointment-booking' ),
			'no_service_text'        => __( 'No services available.', 'bookingpress-appointment-booking' ),
			'no_categories_services_text' => __( 'No categories and services added!', 'bookingpress-appointment-booking' ),
			'all_category_text'      => __( 'All', 'bookingpress-appointment-booking' ),
			'service_tab_name'       => __( 'Service', 'bookingpress-appointment-booking' ),
			'datetime_tab_name'      => __( 'Date & Time', 'bookingpress-appointment-booking' ),
			'basic_details_tab_name' => __( 'Your Details', 'bookingpress-appointment-booking' ),
			'summary_tab_name'       => __( 'Summary', 'bookingpress-appointment-booking' ),
			'timeslot_text'          => __( 'Available Time Slots', 'bookingpress-appointment-booking' ),
			'morning_text'           => __( 'Morning', 'bookingpress-appointment-booking' ),
			'afternoon_text'         => __( 'Afternoon', 'bookingpress-appointment-booking' ),
			'evening_text'           => __( 'Evening', 'bookingpress-appointment-booking' ),
			'night_text'             => __( 'Night', 'bookingpress-appointment-booking' ),
			'date_time_step_note'    => '',
			'no_timeslot_available'  => __( 'No time slots available for the selected date.', 'bookingpress-appointment-booking' ),
			'select_date_title'      => __( 'Select Date', 'bookingpress-appointment-booking' ),
			// Step 3F — Summary tab strings (fallbacks mirror legacy defaults
			// in class.bookingpress_appointment_bookings.php L5392-5466).
			'summary_content_text'              => '',
			'summary_step_note'                 => '',
			'customer_text'                     => __( 'Customer', 'bookingpress-appointment-booking' ),
			'service_text'                      => __( 'Service', 'bookingpress-appointment-booking' ),
			'date_time_text'                    => __( 'Date & Time', 'bookingpress-appointment-booking' ),
			'appointment_details_title_text'    => __( 'Appointment Details', 'bookingpress-appointment-booking' ),
			'payment_method_text'               => __( 'Select Payment Method', 'bookingpress-appointment-booking' ),
			'locally_text'                      => __( 'Pay Locally', 'bookingpress-appointment-booking' ),
			'paypal_text'                       => __( 'PayPal', 'bookingpress-appointment-booking' ),
			'total_amount_text'                 => __( 'Total Amount Payable', 'bookingpress-appointment-booking' ),
			'book_appointment_btn_text'         => __( 'Book Appointment', 'bookingpress-appointment-booking' ),
			'no_payment_method_available'       => __( 'No payment method is available for this booking.', 'bookingpress-appointment-booking' ),
			'no_payment_method_is_selected_for_the_booking' => __( 'Please select a payment method.', 'bookingpress-appointment-booking' ),
		);

		$display_service_description = '0';
		$tabs_position               = '';
		if ( method_exists( $helper, 'bookingpress_get_customize_settings' ) ) {
			$settings_map = $helper->bookingpress_get_customize_settings(
				array(
					'category_title',
					'service_heading_title',
					'service_duration_label',
					'service_price_label',
					'next_button_text',
					'goback_button_text',
					'service_title',
					'datetime_title',
					'basic_details_title',
					'summary_title',
					'all_category_title',
					'default_select_all_category',
					'hide_category_service_selection',
					'booking_form_tabs_position',
					'display_service_description',
					'timeslot_text',
					'morning_text',
					'afternoon_text',
					'evening_text',
					'night_text',
					'date_time_step_note',
					// Step 3F — Summary tab customize keys.
					'summary_content_text',
					'summary_step_note',
					'customer_text',
					'service_text',
					'date_time_text',
					'appointment_details',
					'payment_method_text',
					'locally_text',
					'paypal_text',
					'total_amount_text',
					'book_appointment_btn_text',
				),
				'booking_form'
			);
			if ( is_array( $settings_map ) ) {
				if ( ! empty( $settings_map['category_title'] ) ) {
					$strings['category_title'] = stripslashes_deep( $settings_map['category_title'] );
				}
				if ( ! empty( $settings_map['service_heading_title'] ) ) {
					$strings['service_heading_title'] = stripslashes_deep( $settings_map['service_heading_title'] );
				}
				if ( ! empty( $settings_map['service_duration_label'] ) ) {
					$strings['service_duration_text'] = stripslashes_deep( $settings_map['service_duration_label'] );
				}
				if ( ! empty( $settings_map['service_price_label'] ) ) {
					$strings['service_price_text'] = stripslashes_deep( $settings_map['service_price_label'] );
				}
				if ( ! empty( $settings_map['next_button_text'] ) ) {
					$strings['next_btn_text'] = stripslashes_deep( $settings_map['next_button_text'] );
				}
				if ( ! empty( $settings_map['goback_button_text'] ) ) {
					$strings['goback_btn_text'] = stripslashes_deep( $settings_map['goback_button_text'] );
				}
				if ( ! empty( $settings_map['service_title'] ) ) {
					$strings['service_tab_name'] = stripslashes_deep( $settings_map['service_title'] );
				}
				if ( ! empty( $settings_map['datetime_title'] ) ) {
					$strings['datetime_tab_name'] = stripslashes_deep( $settings_map['datetime_title'] );
				}
				if ( ! empty( $settings_map['basic_details_title'] ) ) {
					$strings['basic_details_tab_name'] = stripslashes_deep( $settings_map['basic_details_title'] );
				}
				if ( ! empty( $settings_map['summary_title'] ) ) {
					$strings['summary_tab_name'] = stripslashes_deep( $settings_map['summary_title'] );
				}
				if ( ! empty( $settings_map['all_category_title'] ) ) {
					$strings['all_category_text'] = stripslashes_deep( $settings_map['all_category_title'] );
				}
				if ( ! empty( $settings_map['booking_form_tabs_position'] ) ) {
					$tabs_position = (string) $settings_map['booking_form_tabs_position'];
				}
				// Issue 9.1 — admin setting is labeled "Hide service description"
				// (manage_form_customize.php L156). Toggle ON → setting === 'true'
				// → admin wants to HIDE the description → state = '0'.
				// Toggle OFF → setting === 'false' → admin wants to SHOW it →
				// state = '1'. Mirrors legacy semantics at
				// class.bookingpress_appointment_bookings.php L6531-L6533, which
				// only sets the state to '1' when the customize value is 'false'.
				$_dsd_setting = isset( $settings_map['display_service_description'] )
					? (string) $settings_map['display_service_description']
					: 'true';
				$display_service_description = ( 'false' === $_dsd_setting ) ? '1' : '0';

				// Legacy parity: compute `hide_category_service` from the real
				// admin setting, and apply the L6802 inversion — if the setting
				// is ON but no service was preselected AND the form is loaded
				// via share URL, legacy flips it back OFF so the sidebar Service
				// step and the Go Back control stay visible/reachable.
				$_hcs_setting = isset( $settings_map['hide_category_service_selection'] )
					? (string) $settings_map['hide_category_service_selection']
					: 'false';
				if ( 'true' === $_hcs_setting
					&& empty( $pre['selected_service'] )
					&& 1 === (int) $pre['is_from_url']
				) {
					$_hcs_setting = 'false';
				}
				$hide_category_service = ( 'true' === $_hcs_setting ) ? '1' : '0';

				// Issue 9.2 — when "Hide Category & Service Selection" is ON
				// and the shortcode did not preselect a service, auto-pick
				// the first available service so the Date & Time step has a
				// concrete service to fetch slots for. Mirrors the legacy
				// auto-fill at class.bookingpress_appointment_bookings.php
				// L6605-L6622: when `hide_category_service_selection == 'true'`
				// the legacy code populates `appointment_step_form_data.selected_service`
				// with the first service in `service_data` (first by category)
				// or `all_services_data[0]` as a fallback.
				if ( '1' === $hide_category_service
					&& empty( $seed['selected_service'] )
					&& ! empty( $all_services )
					&& is_array( $all_services )
				) {
					$first_svc = null;
					foreach ( $all_services as $sv_row ) {
						if ( is_array( $sv_row ) && ! empty( $sv_row['bookingpress_service_id'] ) ) {
							$first_svc = $sv_row;
							break;
						}
					}
					if ( $first_svc ) {
						$seed['selected_service']               = (string) $first_svc['bookingpress_service_id'];
						$seed['selected_service_name']          = isset( $first_svc['bookingpress_service_name'] ) ? (string) $first_svc['bookingpress_service_name'] : '';
						$seed['selected_service_price']         = isset( $first_svc['bookingpress_service_price'] ) ? (string) $first_svc['bookingpress_service_price'] : '';
						$seed['service_price_without_currency'] = isset( $first_svc['service_price_without_currency'] ) ? (string) $first_svc['service_price_without_currency'] : '';
						$seed['selected_service_duration']      = isset( $first_svc['bookingpress_service_duration_val'] ) ? (string) $first_svc['bookingpress_service_duration_val'] : '';
						$seed['selected_service_duration_unit'] = isset( $first_svc['bookingpress_service_duration_unit'] ) ? (string) $first_svc['bookingpress_service_duration_unit'] : '';
						if ( isset( $first_svc['bookingpress_category_id'] ) ) {
							$seed['selected_category'] = (string) $first_svc['bookingpress_category_id'];
						}
					}
				}
				if ( ! empty( $settings_map['timeslot_text'] ) ) {
					$strings['timeslot_text'] = stripslashes_deep( $settings_map['timeslot_text'] );
				}
				if ( ! empty( $settings_map['morning_text'] ) ) {
					$strings['morning_text'] = stripslashes_deep( $settings_map['morning_text'] );
				}
				if ( ! empty( $settings_map['afternoon_text'] ) ) {
					$strings['afternoon_text'] = stripslashes_deep( $settings_map['afternoon_text'] );
				}
				if ( ! empty( $settings_map['evening_text'] ) ) {
					$strings['evening_text'] = stripslashes_deep( $settings_map['evening_text'] );
				}
				if ( ! empty( $settings_map['night_text'] ) ) {
					$strings['night_text'] = stripslashes_deep( $settings_map['night_text'] );
				}
				if ( ! empty( $settings_map['date_time_step_note'] ) ) {
					$strings['date_time_step_note'] = stripslashes_deep( $settings_map['date_time_step_note'] );
				}
				// Step 3F — Summary tab strings. Unlike the bucket above,
				// empty/blank customize values are kept as-is (legacy lets
				// the summary_content_text / summary_step_note render blank
				// when the admin left them empty). Non-blank overrides
				// replace the i18n default.
				if ( isset( $settings_map['summary_content_text'] ) ) {
					$strings['summary_content_text'] = stripslashes_deep( (string) $settings_map['summary_content_text'] );
				}
				if ( isset( $settings_map['summary_step_note'] ) ) {
					$strings['summary_step_note'] = stripslashes_deep( (string) $settings_map['summary_step_note'] );
				}
				if ( ! empty( $settings_map['customer_text'] ) ) {
					$strings['customer_text'] = stripslashes_deep( $settings_map['customer_text'] );
				}
				if ( ! empty( $settings_map['service_text'] ) ) {
					$strings['service_text'] = stripslashes_deep( $settings_map['service_text'] );
				}
				if ( ! empty( $settings_map['date_time_text'] ) ) {
					$strings['date_time_text'] = stripslashes_deep( $settings_map['date_time_text'] );
				}
				if ( ! empty( $settings_map['appointment_details'] ) ) {
					$strings['appointment_details_title_text'] = stripslashes_deep( $settings_map['appointment_details'] );
				}
				if ( ! empty( $settings_map['payment_method_text'] ) ) {
					$strings['payment_method_text'] = stripslashes_deep( $settings_map['payment_method_text'] );
				}
				if ( ! empty( $settings_map['locally_text'] ) ) {
					$strings['locally_text'] = stripslashes_deep( $settings_map['locally_text'] );
				}
				if ( ! empty( $settings_map['paypal_text'] ) ) {
					$strings['paypal_text'] = stripslashes_deep( $settings_map['paypal_text'] );
				}
				if ( ! empty( $settings_map['total_amount_text'] ) ) {
					$strings['total_amount_text'] = stripslashes_deep( $settings_map['total_amount_text'] );
				}
				if ( ! empty( $settings_map['book_appointment_btn_text'] ) ) {
					$strings['book_appointment_btn_text'] = stripslashes_deep( $settings_map['book_appointment_btn_text'] );
				}
			}

			// Legacy message_setting-sourced strings for the payment block.
			// These live in a different settings group than `booking_form`,
			// so they're fetched via `bookingpress_get_settings()`.
			if ( method_exists( $helper, 'bookingpress_get_settings' ) ) {
				$no_pm_available = $helper->bookingpress_get_settings( 'no_payment_method_available', 'message_setting' );
				if ( ! empty( $no_pm_available ) ) {
					$strings['no_payment_method_available'] = stripslashes_deep( (string) $no_pm_available );
				}
				$no_pm_selected = $helper->bookingpress_get_settings( 'no_payment_method_is_selected_for_the_booking', 'message_setting' );
				if ( ! empty( $no_pm_selected ) ) {
					$strings['no_payment_method_is_selected_for_the_booking'] = stripslashes_deep( (string) $no_pm_selected );
				}
			}
		}

		// Default category selection — legacy parity port of
		// class.bookingpress_appointment_bookings.php L6411-6434 + L6956-6964.
		//
		// Runs only when no shortcode-driven preselection has already seeded
		// a category (URL / attribute flow still wins). Two cases:
		//
		//   1. Only one category present → select it (may be the "All"
		//      pseudo-id when uncategorized services exist).
		//   2. Multiple categories → skip the leading `category_id === 0`
		//      ("All") entry and pick the first real category, matching the
		//      L6418-6431 loop exactly.
		//
		// After that resolution, honour the `default_select_all_category`
		// customize toggle (L6956-6964): when truthy AND the category row is
		// not hidden, override back to `'0'` so the "All" pill is selected
		// by default. Using array-last-write semantics so it wins over the
		// first-category fallback, mirroring legacy execution order.
		if ( ! isset( $seed['selected_category'] ) || '' === $seed['selected_category'] ) {
			if ( ! empty( $all_categories ) ) {
				$count = count( $all_categories );
				if ( 1 === $count ) {
					$first_id = isset( $all_categories[0]['category_id'] ) ? $all_categories[0]['category_id'] : null;
					if ( null !== $first_id ) {
						$seed['selected_category'] = (string) $first_id;
						if ( ! empty( $all_categories[0]['category_name'] ) ) {
							$seed['selected_cat_name'] = (string) $all_categories[0]['category_name'];
						}
					}
				} else {
					$n = 0;
					foreach ( $all_categories as $cat_row ) {
						$cat_id = isset( $cat_row['category_id'] ) ? (int) $cat_row['category_id'] : 0;
						if ( 0 === $n && 0 === $cat_id ) {
							$n++;
							continue;
						}
						if ( $n < 2 ) {
							$seed['selected_category'] = (string) $cat_id;
							if ( ! empty( $cat_row['category_name'] ) ) {
								$seed['selected_cat_name'] = (string) $cat_row['category_name'];
							}
							break;
						}
						$n++;
					}
				}
			}

			$default_all = isset( $settings_map )
				&& is_array( $settings_map )
				&& ! empty( $settings_map['default_select_all_category'] )
				&& 'true' === $settings_map['default_select_all_category']
				&& ( empty( $settings_map['hide_category_service_selection'] )
					|| 'true' !== $settings_map['hide_category_service_selection'] );

			if ( $default_all ) {
				$seed['selected_category'] = '0';
				$seed['selected_cat_name']  = isset( $strings['all_category_text'] ) ? (string) $strings['all_category_text'] : '';
			}
		}

		return array(
			'services'                    => $all_services,
			'categories'                  => $all_categories,
			'hide_category_selection'     => $hide_category_selection,
			'hide_category_service'       => $hide_category_service,
			'is_service_loaded_from_url'  => (string) $pre['is_from_url'],
			'no_service_placeholder'      => $no_service_placeholder,
			'seed'                        => $seed,
			'strings'                     => $strings,
			'display_service_description' => $display_service_description,
			'tabs_position'               => $tabs_position,
		);
	}

	/**
	 * Hydrate the Basic Details step's field descriptors, validation rules
	 * and `appointment_step_form_data` seed keys.
	 *
	 * Straight port of the transform loop at
	 * core/classes/frontend/class.bookingpress_appointment_bookings.php
	 * lines 5556-5674. Kept in a dedicated helper so `build_initial_state()`
	 * does not balloon, and so Step 3G-era Pro extensions have a single
	 * choke-point to filter on.
	 *
	 * The cache key `bpa_appointment_booking_form_fields_data_` matches the
	 * legacy key exactly so a page running both `[bookingpress_form]` and
	 * `[bookingpress_form_vue3]` under the same request queries the DB once.
	 *
	 * @return array{
	 *     fields:     array<int, array<string,mixed>>,
	 *     rules:      array<string, array<int, array<string,mixed>>>,
	 *     seed_keys:  array<string, mixed>,
	 * }
	 */
	private static function hydrate_form_fields_data() {
		global $wpdb;

		$empty = array(
			'fields'    => array(),
			'rules'     => array(),
			'seed_keys' => array(),
		);

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return $empty;
		}

		$tbl = $wpdb->prefix . 'bookingpress_form_fields';

		// Reuse the legacy cache key so a mixed-shortcode page does not
		// double-query. Legacy populates the same key in its own render
		// path; either shortcode can fill it, whoever runs first.
		$rows = wp_cache_get( 'bpa_appointment_booking_form_fields_data_' );
		if ( false === $rows ) {
			$rows = $wpdb->get_results( "SELECT * FROM {$tbl} ORDER BY bookingpress_field_position ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from $wpdb->prefix; caching handled immediately below.
			if ( is_array( $rows ) ) {
				wp_cache_set( 'bpa_appointment_booking_form_fields_data_', $rows );
			}
		}

		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return $empty;
		}

		/** Preserve the legacy addon hook. */
		$rows = apply_filters( 'bookingpress_modify_field_data_before_prepare', $rows );

		$fields    = array();
		$rules     = array();
		$seed_keys = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			// Legacy ignores hidden rows when constructing the field list.
			if ( isset( $row['bookingpress_field_is_hide'] ) && (int) $row['bookingpress_field_is_hide'] !== 0 ) {
				continue;
			}

			$field_name = isset( $row['bookingpress_form_field_name'] ) ? (string) $row['bookingpress_form_field_name'] : '';

			// v_model_value mapping — verbatim copy of L5566-5584. These
			// keys are the contract with every downstream AJAX handler;
			// do not diverge.
			switch ( $field_name ) {
				case 'fullname':
					$v_model_value = 'customer_name';
					break;
				case 'firstname':
					$v_model_value = 'customer_firstname';
					break;
				case 'lastname':
					$v_model_value = 'customer_lastname';
					break;
				case 'email_address':
					$v_model_value = 'customer_email';
					break;
				case 'phone_number':
					$v_model_value = 'customer_phone';
					break;
				case 'note':
					$v_model_value = 'appointment_note';
					break;
				case 'username':
					$v_model_value = 'customer_username';
					break;
				case 'terms_and_conditions':
					$v_model_value = 'appointment_terms_conditions';
					break;
				default:
					$v_model_value = isset( $row['bookingpress_field_meta_key'] ) ? (string) $row['bookingpress_field_meta_key'] : '';
			}

			if ( '' === $v_model_value ) {
				continue;
			}

			// Seed the reactive step-form bucket. Terms & conditions must
			// be an array (el-checkbox expects array with :label="true");
			// everything else is an empty string.
			$seed_keys[ $v_model_value ] = ( 'appointment_terms_conditions' === $v_model_value ) ? array() : '';

			// field_type mapping — verbatim copy of L5591-5610.
			switch ( $field_name ) {
				case 'fullname':
				case 'firstname':
				case 'lastname':
				case 'username':
					$field_type = 'Text';
					break;
				case 'email_address':
					$field_type = 'Email';
					break;
				case 'phone_number':
					$field_type = 'Dropdown';
					break;
				case 'note':
					$field_type = 'Textarea';
					break;
				case 'terms_and_conditions':
					$field_type = 'terms_and_conditions';
					break;
				default:
					$field_type = isset( $row['bookingpress_field_type'] ) ? (string) $row['bookingpress_field_type'] : 'Text';
			}

			$is_required = isset( $row['bookingpress_field_required'] ) ? ( (int) $row['bookingpress_field_required'] === 1 ) : false;

			$field_descriptor = array(
				'id'             => isset( $row['bookingpress_form_field_id'] ) ? (int) $row['bookingpress_form_field_id'] : 0,
				'field_name'     => $field_name,
				'field_type'     => $field_type,
				'is_edit'        => false,
				'is_required'    => $is_required,
				'label'          => isset( $row['bookingpress_field_label'] ) ? stripslashes_deep( (string) $row['bookingpress_field_label'] ) : '',
				'placeholder'    => isset( $row['bookingpress_field_placeholder'] ) ? stripslashes_deep( (string) $row['bookingpress_field_placeholder'] ) : '',
				'error_message'  => isset( $row['bookingpress_field_error_message'] ) ? stripslashes_deep( (string) $row['bookingpress_field_error_message'] ) : '',
				'is_hide'        => false, // we already skipped hidden rows above
				'field_position' => isset( $row['bookingpress_field_position'] ) ? (float) $row['bookingpress_field_position'] : 0.0,
				'v_model_value'  => $v_model_value,
			);

			/** Legacy addon hook — keep firing. */
			$field_descriptor = apply_filters( 'bookingpress_arrange_form_fields_outside', $field_descriptor, $row );

			$fields[] = $field_descriptor;

			if ( ! $is_required ) {
				continue;
			}

			// Rule construction — verbatim copy of L5636-5666.
			$error_message = isset( $field_descriptor['error_message'] ) ? (string) $field_descriptor['error_message'] : '';

			if ( 'customer_email' === $v_model_value ) {
				$rules[ $v_model_value ] = array(
					array(
						'required' => true,
						'message'  => $error_message,
						'trigger'  => 'blur',
					),
					array(
						'type'    => 'email',
						'message' => esc_html__( 'Please enter valid email address', 'bookingpress-appointment-booking' ),
						'trigger' => 'blur',
					),
				);
			} elseif ( 'appointment_terms_conditions' === $v_model_value ) {
				$rules[ $v_model_value ][] = array(
					'required' => true,
					'message'  => $error_message,
					'trigger'  => 'change',
				);
			} else {
				$rules[ $v_model_value ][] = array(
					'required' => true,
					'message'  => $error_message,
					'trigger'  => 'blur',
				);
			}

			// Fallback "<label> is required" when no explicit error_message.
			if ( isset( $rules[ $v_model_value ][0]['message'] ) && '' === $rules[ $v_model_value ][0]['message'] ) {
				$label = isset( $field_descriptor['label'] ) ? (string) $field_descriptor['label'] : '';
				$rules[ $v_model_value ][0]['message'] = '' !== $label
					? $label . ' ' . esc_html__( 'is required', 'bookingpress-appointment-booking' )
					: '';
			}

			/** Legacy addon hook — keep firing per-field. */
			$rules = apply_filters( 'bookingpress_modify_form_fields_rules_arr', $rules, $field_descriptor );
		}

		/** Legacy addon hook — final pass on the whole rules map. */
		$rules = apply_filters( 'bookingpress_modify_form_fields_msg_array', $rules );

		return array(
			'fields'    => $fields,
			'rules'     => is_array( $rules ) ? $rules : array(),
			'seed_keys' => $seed_keys,
		);
	}

	/**
	 * Apply the logged-in user prefill + phone-field settings to the Basic
	 * Details seed data.
	 *
	 * Port of class.bookingpress_appointment_bookings.php L6656-6738. For
	 * logged-in users we hydrate whatever of the following keys exist in
	 * `$step_form_data` (populated by `hydrate_form_fields_data()` from the
	 * `bookingpress_form_fields` table):
	 *
	 *   customer_name, customer_username, customer_email,
	 *   customer_firstname, customer_lastname, customer_phone
	 *
	 * Primary source is a row in `{$wpdb->prefix}bookingpress_customers`
	 * keyed on the WP user id with `bookingpress_user_type = 2`. If no
	 * such row exists we fall back to the WP user record + the
	 * `first_name`/`last_name` user-meta pair (legacy `elseif` branch).
	 *
	 * Also ports the phone-field bits that live just below the prefill
	 * block in legacy:
	 *   - Adds a `required`/blur rule for `customer_phone` when the
	 *     `phone_number_mandatory` general setting is 'true'.
	 *   - Seeds `customer_phone_country` from `default_phone_country_code`
	 *     and `customer_phone_dial_code` as empty.
	 *
	 * @param array $step_form_data      The current `appointment_step_form_data` seed.
	 * @param array $customer_rules_map  The current `customer_details_rule` map.
	 * @return array{
	 *     step_form_data:                  array,
	 *     customer_details_rule:           array,
	 *     check_bookingpress_username_set: int,
	 * }
	 */
	private static function apply_logged_in_user_prefill( array $step_form_data, array $customer_rules_map ) {
		global $wpdb;

		$check_username_set = 0;

		// --- phone-field settings (apply regardless of login state) ---
		$helper                = self::get_legacy_helper();
		$phone_mandatory       = '';
		$phone_default_country = '';
		if ( $helper && method_exists( $helper, 'bookingpress_get_settings' ) ) {
			$phone_mandatory       = (string) $helper->bookingpress_get_settings( 'phone_number_mandatory', 'general_setting' );
			$phone_default_country = (string) $helper->bookingpress_get_settings( 'default_phone_country_code', 'general_setting' );
		}

		if ( 'true' === $phone_mandatory ) {
			// Match the shape used by every other rule — a list of rule
			// objects — so `validateBasicDetails()` walks it uniformly.
			$customer_rules_map['customer_phone'] = array(
				array(
					'required' => true,
					'message'  => esc_html__( 'Please enter customer phone number', 'bookingpress-appointment-booking' ),
					'trigger'  => 'blur',
				),
			);
		}

		// Phone component helper keys. Always present so the BpUiTelInput
		// has a stable `defaultCountry` binding and a bucket for the dial
		// code emitted by `country-changed`.
		$step_form_data['customer_phone_country']   = $phone_default_country;
		$step_form_data['customer_phone_dial_code'] = '';

		// --- logged-in user prefill ---
		if ( ! is_user_logged_in() || ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return array(
				'step_form_data'                  => $step_form_data,
				'customer_details_rule'           => $customer_rules_map,
				'check_bookingpress_username_set' => $check_username_set,
			);
		}

		$current_user_id = get_current_user_id();
		if ( empty( $current_user_id ) ) {
			return array(
				'step_form_data'                  => $step_form_data,
				'customer_details_rule'           => $customer_rules_map,
				'check_bookingpress_username_set' => $check_username_set,
			);
		}

		$tbl_customers = $wpdb->prefix . 'bookingpress_customers';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- parity with legacy; identical query.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$tbl_customers} WHERE bookingpress_wpuser_id = %d AND bookingpress_user_type = 2", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
				$current_user_id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! empty( $row ) && is_array( $row ) ) {
			$firstname = isset( $row['bookingpress_user_firstname'] ) ? stripslashes_deep( (string) $row['bookingpress_user_firstname'] ) : '';
			$lastname  = isset( $row['bookingpress_user_lastname'] )  ? stripslashes_deep( (string) $row['bookingpress_user_lastname'] )  : '';
			$username  = isset( $row['bookingpress_user_name'] )      ? stripslashes_deep( (string) $row['bookingpress_user_name'] )      : '';
			$fullname  = isset( $row['bookingpress_customer_full_name'] ) ? stripslashes_deep( (string) $row['bookingpress_customer_full_name'] ) : '';
			$email     = isset( $row['bookingpress_user_email'] )     ? stripslashes_deep( (string) $row['bookingpress_user_email'] )     : '';
			$phone     = isset( $row['bookingpress_user_phone'] )     ? (string) $row['bookingpress_user_phone']                           : '';

			if ( '' !== $username ) {
				$check_username_set = 1;
			}

			if ( array_key_exists( 'customer_name', $step_form_data ) ) {
				$step_form_data['customer_name'] = $fullname;
			}
			if ( array_key_exists( 'customer_username', $step_form_data ) ) {
				$step_form_data['customer_username'] = $username;
			}
			if ( array_key_exists( 'customer_phone', $step_form_data ) ) {
				$step_form_data['customer_phone'] = $phone;
			}
			if ( array_key_exists( 'customer_email', $step_form_data ) ) {
				$step_form_data['customer_email'] = $email;
			}
			if ( array_key_exists( 'customer_firstname', $step_form_data ) ) {
				$step_form_data['customer_firstname'] = $firstname;
			}
			if ( array_key_exists( 'customer_lastname', $step_form_data ) ) {
				$step_form_data['customer_lastname'] = $lastname;
			}
		} else {
			// No customer row — fall back to WP user data + user_meta.
			$wp_user = get_userdata( $current_user_id );
			if ( $wp_user && isset( $wp_user->data ) ) {
				$login_name = ! empty( $wp_user->data->user_login ) ? (string) $wp_user->data->user_login : '';
				$user_email = ! empty( $wp_user->data->user_email ) ? (string) $wp_user->data->user_email : '';
				$firstname  = (string) get_user_meta( $current_user_id, 'first_name', true );
				$lastname   = (string) get_user_meta( $current_user_id, 'last_name', true );

				if ( '' !== $login_name ) {
					$check_username_set = 1;
				}

				if ( array_key_exists( 'customer_username', $step_form_data ) ) {
					$step_form_data['customer_username'] = stripslashes_deep( $login_name );
				}
				if ( array_key_exists( 'customer_name', $step_form_data ) ) {
					$step_form_data['customer_name'] = stripslashes_deep( $login_name );
				}
				if ( array_key_exists( 'customer_email', $step_form_data ) ) {
					$step_form_data['customer_email'] = stripslashes_deep( $user_email );
				}
				if ( array_key_exists( 'customer_firstname', $step_form_data ) ) {
					$step_form_data['customer_firstname'] = stripslashes_deep( $firstname );
				}
				if ( array_key_exists( 'customer_lastname', $step_form_data ) ) {
					$step_form_data['customer_lastname'] = stripslashes_deep( $lastname );
				}
			}
		}

		return array(
			'step_form_data'                  => $step_form_data,
			'customer_details_rule'           => $customer_rules_map,
			'check_bookingpress_username_set' => $check_username_set,
		);
	}

	/**
	 * Build the initial reactive state payload for a single instance.
	 *
	 * Step 3C populates the service + category slice of the legacy
	 * `bpaInitialState`. Step 3E wires in Basic Details — form fields,
	 * validation rules, and the per-field seed keys on
	 * `appointment_step_form_data`. Timings stay empty (Step 3F).
	 */
	private static function build_initial_state( $uniq_id, $atts ) {
		$hydrated = self::hydrate_service_category_data( $atts );
		$seed     = isset( $hydrated['seed'] ) && is_array( $hydrated['seed'] ) ? $hydrated['seed'] : array();
		$strings  = isset( $hydrated['strings'] ) && is_array( $hydrated['strings'] ) ? $hydrated['strings'] : array();

		// Rehydrate `hide_category_service` (computed in
		// hydrate_service_category_data) so the sidebar step flags and the
		// initial `bookingpress_current_tab` can branch on it below. Mirrors
		// legacy class.bookingpress_appointment_bookings.php L6898 / L7441.
		$hide_category_service      = isset( $hydrated['hide_category_service'] ) ? (string) $hydrated['hide_category_service'] : '0';
		$is_service_loaded_from_url = isset( $hydrated['is_service_loaded_from_url'] ) ? (string) $hydrated['is_service_loaded_from_url'] : '0';

		// Step 3E — Basic Details hydration. Field descriptors, rules map,
		// and per-field seed keys for `appointment_step_form_data`.
		$form_fields_hydrated = self::hydrate_form_fields_data();
		$form_fields          = isset( $form_fields_hydrated['fields'] ) && is_array( $form_fields_hydrated['fields'] ) ? $form_fields_hydrated['fields'] : array();
		$form_rules           = isset( $form_fields_hydrated['rules'] ) && is_array( $form_fields_hydrated['rules'] ) ? $form_fields_hydrated['rules'] : array();
		$form_seed_keys       = isset( $form_fields_hydrated['seed_keys'] ) && is_array( $form_fields_hydrated['seed_keys'] ) ? $form_fields_hydrated['seed_keys'] : array();

		// Legacy tab-icon SVG strings — mirrors the sidebar step seeds at
		// core/classes/frontend/class.bookingpress_appointment_bookings.php:6822+
		// (pro <1.7 fallback to material-icon names is not needed here since
		// Step 3C intentionally does not branch on Pro state).
		$tab_icon_service       = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 13H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-4c0-1.1-.9-2-2-2zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM19 3H5c-1.1 0-2 .9-2 2v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM7 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>';
		$tab_icon_datetime      = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 4h-1V3c0-.55-.45-1-1-1s-1 .45-1 1v1H8V3c0-.55-.45-1-1-1s-1 .45-1 1v1H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1V9h14v10zM7 11h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/></svg>';
		$tab_icon_basic_details = '<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><g><rect fill="none" height="24" width="24"/><path d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M13,17H8c-0.55,0-1-0.45-1-1 c0-0.55,0.45-1,1-1h5c0.55,0,1,0.45,1,1C14,16.55,13.55,17,13,17z M16,13H8c-0.55,0-1-0.45-1-1c0-0.55,0.45-1,1-1h8 c0.55,0,1,0.45,1,1C17,12.55,16.55,13,16,13z M16,9H8C7.45,9,7,8.55,7,8c0-0.55,0.45-1,1-1h8c0.55,0,1,0.45,1,1 C17,8.55,16.55,9,16,9z"/></g></svg>';
		$tab_icon_summary       = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9.29 16.29L6.7 13.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L10 14.17l5.88-5.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-6.59 6.59c-.38.39-1.02.39-1.41 0z"/></svg>';

		$service_tab_name       = isset( $strings['service_tab_name'] ) ? $strings['service_tab_name'] : __( 'Service', 'bookingpress-appointment-booking' );
		$datetime_tab_name      = isset( $strings['datetime_tab_name'] ) ? $strings['datetime_tab_name'] : __( 'Date & Time', 'bookingpress-appointment-booking' );
		$basic_details_tab_name = isset( $strings['basic_details_tab_name'] ) ? $strings['basic_details_tab_name'] : __( 'Your Details', 'bookingpress-appointment-booking' );
		$summary_tab_name       = isset( $strings['summary_tab_name'] ) ? $strings['summary_tab_name'] : __( 'Summary', 'bookingpress-appointment-booking' );

		$sidebar_steps = array(
			'service'       => array(
				'name'              => 'service',
				'tab_value'         => 'service',
				'tab_name'          => $service_tab_name,
				'tab_icon'          => $tab_icon_service,
				'previous_tab_name' => '',
				'next_tab_name'     => 'datetime',
				'is_allow_navigate' => 1,
				'is_display_step'   => 1,
			),
			'datetime'      => array(
				'name'              => 'datetime',
				'tab_value'         => 'datetime',
				'tab_name'          => $datetime_tab_name,
				'tab_icon'          => $tab_icon_datetime,
				'previous_tab_name' => 'service',
				'next_tab_name'     => 'basic_details',
				'is_allow_navigate' => 0,
				'is_display_step'   => 1,
			),
			'basic_details' => array(
				'name'              => 'basic_details',
				'tab_value'         => 'basic_details',
				'tab_name'          => $basic_details_tab_name,
				'tab_icon'          => $tab_icon_basic_details,
				'previous_tab_name' => 'datetime',
				'next_tab_name'     => 'summary',
				'is_allow_navigate' => 0,
				'is_display_step'   => 1,
			),
			'summary'       => array(
				'name'              => 'summary',
				'tab_value'         => 'summary',
				'tab_name'          => $summary_tab_name,
				'tab_icon'          => $tab_icon_summary,
				'previous_tab_name' => 'basic_details',
				'next_tab_name'     => '',
				'is_allow_navigate' => 0,
				'is_display_step'   => 1,
			),
		);

		// Legacy parity (class.bookingpress_appointment_bookings.php L6898-L6909):
		// when the shortcode is rendered with a preselected service or a URL-
		// driven service load, the sidebar's Service step is hidden (still
		// present in the array, just with `is_display_step => 0`). The Lite
		// build has no Pro branch, so the simple assignment matches legacy's
		// else-branch exactly.
		if ( '1' === (string) $hide_category_service ) {
			$sidebar_steps['service']['is_display_step'] = 0;
		}

		$step_form_data = array_merge(
			array(
				'selected_category'              => '',
				'selected_cat_name'              => '',
				'selected_service'               => '',
				'selected_service_name'          => '',
				'selected_service_price'         => '',
				'service_price_without_currency' => '',
				'selected_service_duration'      => '',
				'selected_service_duration_unit' => '',
				'selected_staff_member'          => '',
				'selected_date'                  => '',
				'selected_start_time'            => '',
				'selected_end_time'              => '',
				'selected_formatted_start_time'  => '',
				'selected_formatted_end_time'    => '',
				'selected_formatted_start_end_time' => '',
				'selected_formatted_booked_date' => '',
				'selected_payment_method'        => '',
			),
			// Per-field Basic Details seeds (Step 3E). Union'd before the
			// shortcode `$seed` so attribute-driven preselections still win.
			$form_seed_keys,
			$seed
		);

		// Step 3E — logged-in user prefill + phone-field settings. Runs
		// AFTER shortcode-driven `$seed` values, so an explicit attribute
		// wins over a WP-user default (attribute-driven preselections are
		// intentional). Phone component helper keys and the conditional
		// `customer_phone` required rule are set unconditionally inside
		// the helper, regardless of login state.
		$prefill = self::apply_logged_in_user_prefill( $step_form_data, $form_rules );
		if ( isset( $prefill['step_form_data'] ) && is_array( $prefill['step_form_data'] ) ) {
			$step_form_data = $prefill['step_form_data'];
		}
		if ( isset( $prefill['customer_details_rule'] ) && is_array( $prefill['customer_details_rule'] ) ) {
			$form_rules = $prefill['customer_details_rule'];
		}
		$check_bookingpress_username_set = isset( $prefill['check_bookingpress_username_set'] )
			? (int) $prefill['check_bookingpress_username_set']
			: 0;

		// Date / time scaffolding (Step 3D). Pulls the legacy customize
		// date format if the helper is available, else falls back to the
		// WordPress site date_format. `bookingpress_site_date` is "today"
		// from WP's site timezone — the calendar uses it as min-date.
		$helper_for_date     = self::get_legacy_helper();
		$wp_default_date_fmt = get_option( 'date_format', 'Y-m-d' );
		$wp_default_time_fmt = get_option( 'time_format', 'H:i' );
		$bpa_front_date_fmt  = $wp_default_date_fmt;
		$bpa_front_dtime_fmt = $wp_default_date_fmt . ' ' . $wp_default_time_fmt;
		if ( $helper_for_date && method_exists( $helper_for_date, 'bookingpress_check_common_date_format' ) ) {
			$bpa_default_date_setting = method_exists( $helper_for_date, 'bookingpress_get_settings' )
				? $helper_for_date->bookingpress_get_settings( 'default_date_format', 'general_setting' )
				: '';
			$bpa_default_time_setting = method_exists( $helper_for_date, 'bookingpress_get_settings' )
				? $helper_for_date->bookingpress_get_settings( 'default_time_format', 'general_setting' )
				: '';
			$source_date_fmt = ! empty( $bpa_default_date_setting ) ? $bpa_default_date_setting : $wp_default_date_fmt;
			$maybe_fmt       = $helper_for_date->bookingpress_check_common_date_format( $source_date_fmt );
			if ( ! empty( $maybe_fmt ) ) {
				$bpa_front_date_fmt  = (string) $maybe_fmt;
				$bpa_front_dtime_fmt = ( 'H:i' === $bpa_default_time_setting )
					? $bpa_front_date_fmt . ' HH:mm'
					: $bpa_front_date_fmt . ' hh:mm a';
			}
		}
		$site_today   = current_time( 'Y-m-d' );
		// Legacy parity: Vue2 caps booking_cal_maxdate at +1 year (see
		// class.bookingpress_appointment_bookings.php L6306, where the JS
		// computes `Date.now() + 3600*1000*24*365`). Previously this was
		// incorrectly set to `+2 years`, which widened the picker by an
		// extra year and drove the progressive month walker through twice
		// as many admin-ajax round-trips on forward navigation.
		$max_date_raw = strtotime( '+1 year', strtotime( $site_today ) );
		$cal_max_date = $max_date_raw ? gmdate( 'Y-m-d', $max_date_raw ) : $site_today;
		$first_dow    = (int) get_option( 'start_of_week', 0 );
		// VCalendar uses 1 = Sunday ... 7 = Saturday. WP uses 0 = Sunday.
		$vc_first_day = ( $first_dow % 7 ) + 1;

		// Step 3F — payment gateway visibility flags. Mirrors legacy
		// class.bookingpress_appointment_bookings.php L6627-6647 so the
		// summary tab shows the right combination of cards.
		$on_site_payment            = '';
		$paypal_payment             = '';
		$paypal_payment_method_type = '';
		$paypal_client_id           = '';
		$is_only_onsite_enabled     = 0;
		if ( $helper_for_date && method_exists( $helper_for_date, 'bookingpress_get_settings' ) ) {
			$on_site_payment            = (string) $helper_for_date->bookingpress_get_settings( 'on_site_payment', 'payment_setting' );
			$paypal_payment             = (string) $helper_for_date->bookingpress_get_settings( 'paypal_payment', 'payment_setting' );
			$paypal_payment_method_type = (string) $helper_for_date->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );
			// Client ID is public-safe (it's in the PayPal SDK URL and
			// surfaces in the DOM either way). The secret stays server-side
			// and is only read by the paypal-validate AJAX handler.
			$paypal_client_id           = (string) $helper_for_date->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );

			$on_site_on = ( 'true' === $on_site_payment || '1' === $on_site_payment );
			$paypal_on  = ( 'true' === $paypal_payment  || '1' === $paypal_payment );
			if ( $on_site_on && ! $paypal_on ) {
				$is_only_onsite_enabled = 1;
			}

			// Issue 7 — auto-preselect the payment method when only one
			// gateway is enabled. Legacy parity: when the payment-methods
			// row is hidden because there is only a single option, the
			// summary submit must still send a non-empty
			// `selected_payment_method`, otherwise the server returns
			// "Please select payment method" (or fails the nonce step).
			// Only override when the user has not already chosen one (the
			// shortcode `$seed` might have a value in unusual flows).
			if ( empty( $step_form_data['selected_payment_method'] ) ) {
				if ( $on_site_on && ! $paypal_on ) {
					$step_form_data['selected_payment_method'] = 'on-site';
				} elseif ( $paypal_on && ! $on_site_on ) {
					$step_form_data['selected_payment_method'] = 'paypal';
				}
			}
		}

		// Issue 3 — Timeslot grouping bucket boundaries. Vue 3's
		// `bookingpressCategoriesTimeslots()` reads
		// `state.bpa_afternoon_slots_timing`, `state.bpa_evening_slots_timing`,
		// `state.bpa_night_slots_timing` (hours, 0-23) to assign each slot
		// to morning / afternoon / evening / night. The legacy server-side
		// bucketing at class.bookingpress_appointment_bookings.php
		// L5077-L5117 reads the same admin settings
		// (`bpa_afternoon_start_time`, `bpa_evening_start_time`,
		// `bpa_night_start_time`) and converts each to its hour-of-day. We
		// mirror that here so the Vue 3 grouping respects the admin UI
		// sliders. Defaults match the JS-side defaults (12/17/21) for the
		// case where the helper is unavailable or settings are blank.
		$bpa_afternoon_hour = 12;
		$bpa_evening_hour   = 17;
		$bpa_night_hour     = 21;
		if ( $helper_for_date && method_exists( $helper_for_date, 'bookingpress_get_settings' ) ) {
			$_at = (string) $helper_for_date->bookingpress_get_settings( 'bpa_afternoon_start_time', 'general_setting' );
			$_et = (string) $helper_for_date->bookingpress_get_settings( 'bpa_evening_start_time',   'general_setting' );
			$_nt = (string) $helper_for_date->bookingpress_get_settings( 'bpa_night_start_time',     'general_setting' );
			if ( '' !== $_at && false !== strtotime( $_at ) ) {
				$bpa_afternoon_hour = (int) gmdate( 'H', strtotime( $_at ) );
			}
			if ( '' !== $_et && false !== strtotime( $_et ) ) {
				$bpa_evening_hour = (int) gmdate( 'H', strtotime( $_et ) );
			}
			if ( '' !== $_nt && false !== strtotime( $_nt ) ) {
				$bpa_night_hour = (int) gmdate( 'H', strtotime( $_nt ) );
			}
		}

		$state_scaffold = array(
			'appointment_step_form_data'                  => $step_form_data,
			'bookingpress_all_services_data'              => isset( $hydrated['services'] ) ? $hydrated['services'] : array(),
			'bookingpress_all_categories'                 => isset( $hydrated['categories'] ) ? $hydrated['categories'] : array(),
			'service_categories'                          => isset( $hydrated['categories'] ) ? $hydrated['categories'] : array(),
			'bookingpress_sidebar_step_data'              => $sidebar_steps,
			// Legacy parity (class.bookingpress_appointment_bookings.php
			// L7441-L7443): the onload handler jumps the form to the
			// datetime step when the service is preselected or loaded from
			// URL. We compute that upfront on the server so the initial
			// render already shows the correct tab (avoids a flash of the
			// Service panel before the onload snap).
			'bookingpress_current_tab'                    => (
				'1' === $hide_category_service || '1' === $is_service_loaded_from_url
			) ? 'datetime' : 'service',
			// Step 3E — Basic Details descriptors. `customer_form_fields`
			// is the canonical array (matches legacy L6297). The legacy
			// `customer_form_fields_data` slot is also populated as a
			// temporary alias for back-compat; callers should migrate to
			// `customer_form_fields` and the alias will be dropped in a
			// later step.
			'customer_form_fields'                        => $form_fields,
			'customer_form_fields_data'                   => $form_fields,
			// Cast to (object) so an empty rules map serialises as `{}`
			// (an Element-Plus / BpUiForm `:rules` prop rejects `[]`).
			'customer_details_rule'                       => ! empty( $form_rules ) ? $form_rules : new \stdClass(),
			// Matches legacy L6656+: `1` if the logged-in user already
			// has a username bound to their customer row (so the
			// `username` input is rendered disabled), else `0`.
			'check_bookingpress_username_set'             => $check_bookingpress_username_set,
			// Toggled by `validateBasicDetails()` inside booking-form.js
			// — keeps Element-Plus-style reactive validation state
			// decoupled from the BpUiForm ref.
			'is_basic_details_validated'                  => false,
			'v_calendar_available_dates'                  => array(),
			'v_calendar_blocked_dates'                    => array(),
			'service_timing'                              => array(
				'morning_time'   => array(),
				'afternoon_time' => array(),
				'evening_time'   => array(),
				'night_time'     => array(),
			),
			'bpa_front_date_format'                       => $bpa_front_date_fmt,
			'bpa_front_date_time_format'                  => $bpa_front_dtime_fmt,
			'bookingpress_site_date'                      => $site_today,
			'booking_cal_maxdate'                         => $cal_max_date,
			'first_day_of_week'                           => $vc_first_day,
			'site_locale'                                 => determine_locale(),
			'is_timeslot_loading'                         => '0',
			'is_date_loading'                             => '0',
			'is_booking_form_empty_loader'                => '0',
			// Issue 3 — Timeslot grouping bucket hours (read by
			// bookingpressCategoriesTimeslots in booking-form.js).
			'bpa_afternoon_slots_timing'                  => $bpa_afternoon_hour,
			'bpa_evening_slots_timing'                    => $bpa_evening_hour,
			'bpa_night_slots_timing'                      => $bpa_night_hour,
			'hide_category_selection'                     => ! empty( $hydrated['hide_category_selection'] ),
			'hide_category_service'                       => isset( $hydrated['hide_category_service'] ) ? $hydrated['hide_category_service'] : '0',
			'is_service_loaded_from_url'                  => isset( $hydrated['is_service_loaded_from_url'] ) ? $hydrated['is_service_loaded_from_url'] : '0',
			'bookingpress_display_no_service_placeholder' => ! empty( $hydrated['no_service_placeholder'] ),
			'display_service_description'                 => isset( $hydrated['display_service_description'] ) ? $hydrated['display_service_description'] : '0',
			'is_display_error'                            => '0',
			'is_error_msg'                                => '',
			'strings'                                     => isset( $hydrated['strings'] ) ? $hydrated['strings'] : array(),
			// Orientation for the outer `#bpa-front-tabs` shell. Legacy
			// template uses `bpa-front-tabs--left` when customize setting
			// `booking_form_tabs_position` === 'left', else `--bpa-top`.
			'bookingpress_tabs_position'                  => isset( $hydrated['tabs_position'] ) ? $hydrated['tabs_position'] : '',
			// Reserved for sticky-footer logic migrated in a later step;
			// legacy applies it on the outer container as a bound class.
			'bookingpress_container_dynamic_class'        => '',
			// Step 3F — Summary tab runtime + payment flags.
			// Payment gateway visibility (legacy L6627-6647).
			'on_site_payment'                             => $on_site_payment,
			'paypal_payment'                              => $paypal_payment,
			'is_only_onsite_enabled'                      => (string) $is_only_onsite_enabled,
			// PayPal config surfaced for the Summary step. `paypal_payment_method_type`
			// is the gating signal — when equal to 'popup', selecting PayPal
			// flips to the PayPal SDK button path (legacy L700-818).
			// `paypal_client_id` is public-safe (appears in the SDK URL).
			'paypal_payment_method_type'                  => $paypal_payment_method_type,
			'paypal_client_id'                            => $paypal_client_id,
			// PayPal SDK UI flags. Initial values match legacy (L6973-6977)
			// so the default book_appointment button shows on first render
			// and the PayPal popup button is revealed only after the user
			// selects PayPal with popup mode active.
			'paypal_button_loader'                        => 'false',
			'paypal_success_url'                          => '',
			'paypal_cancel_url'                           => '',
			'paypal_booking_form_redirection_mode'        => '',
			// Submission loader/disabled toggles.
			'isLoadBookingLoader'                         => '0',
			'isBookingDisabled'                           => false,
			// Legacy gate for the default book_appointment button (legacy
			// template L1005): the default submit renders when
			// `show_paypal_popup_button === 'false'`. Initial state is
			// 'false' (legacy L6973) so the default button is visible on
			// first render; `select_payment_method` flips it to 'true' when
			// PayPal is picked with popup mode active, which hides the
			// default submit and reveals the SDK button container.
			'show_paypal_popup_button'                    => 'false',
			// Server-returned redirect HTML sink (PayPal redirect form).
			// Lite never writes a non-empty value here; kept so Pro's
			// `variant: redirect` response path has a stable target.
			'bookingpress_external_html'                  => '',
			// HTML-allowed summary strings surfaced at the top of the
			// state payload (legacy exposes them on the root model).
			'bookingpress_total_amount_text'              => isset( $strings['total_amount_text'] ) ? $strings['total_amount_text'] : '',
			'bookingpress_book_appointment_btn_text'      => isset( $strings['book_appointment_btn_text'] ) ? $strings['book_appointment_btn_text'] : '',
			'summary_step_note'                           => isset( $strings['summary_step_note'] ) ? $strings['summary_step_note'] : '',
		);

		return array(
			'instanceId'     => $uniq_id,
			'shortcodeAttrs' => $atts,
			'nonce'          => wp_create_nonce( 'bpa_wp_nonce' ),
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'restUrl'        => esc_url_raw( rest_url( 'bookingpress-app/v1/' ) ),
			'restNonce'      => wp_create_nonce( 'wp_rest' ),
			'locale'         => determine_locale(),
			'isRtl'          => is_rtl(),
			'state'          => $state_scaffold,
		);
	}

	/**
	 * Generate a unique, DOM-safe identifier for a shortcode instance.
	 *
	 * @return string
	 */
	private static function generate_unique_id() {
		if ( function_exists( 'wp_unique_id' ) ) {
			return wp_unique_id( 'bpv3_' );
		}
		return 'bpv3_' . substr( md5( uniqid( '', true ) ), 0, 12 );
	}
}

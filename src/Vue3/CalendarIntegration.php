<?php
/**
 * CalendarIntegration — Vue 3-safe "Add to Calendar" buttons.
 *
 * WHAT IT DOES
 * ------------
 * Replaces the legacy `[bookingpress_appointment_calendar_integration]`
 * shortcode output on the Vue 3 path. The legacy shortcode emits Vue 2 template
 * markup (`v-for` / `v-if` / `{{ }}` / `el-button`) inside
 * `#bpa-front-module-calendar-integration` and hydrates it from a `wp_footer`
 * Vue 2 app. With the Vue 3 Complete Payment enabled there is no Vue 2 runtime,
 * so the buttons never appear. This class renders the same buttons as static
 * `<a>` anchors instead — no Vue at all — reusing the legacy class names so the
 * existing `bookingpress_front.css` styling applies unchanged. It deliberately
 * omits the `#bpa-front-module-calendar-integration` id, which the legacy CSS
 * hides until its Vue 2 app mounts.
 *
 * LINKS
 * -----
 *   Single appointment:
 *     - Google / Yahoo  → external calendar URLs built from the timezone-aware
 *       data of the Lite `bookingpress_get_appointment_details_for_calendar_func()`.
 *     - Outlook / iCal  → the Lite `?bpa_page=bookingpress_download&action=generate_ics`
 *       endpoint (nonce + entry id).
 *   Order (Cart / Recurring / Multi-Service — more than one appointment per booking):
 *     - ALL four buttons → the add-ons' `?page=bookingpress_download&action=generate_ics_with_cart`
 *       endpoint (nonce + order id + `selectedCalendar`), so the customer
 *       downloads one `.ics` holding every appointment of the order. This is
 *       what the Cart / Recurring add-ons did in Vue 2 through the
 *       `bookingpress_calendar_integration_events` action and the
 *       `bookingpress_change_calendar_url` JS filter.
 *
 * Used by:
 *   - the Lite shortcode callback, when {@see ProRouting::should_load_complete_v3()}
 *     is on (external thank-you page, full page load) — {@see render_shortcode()};
 *   - {@see RedirectionFeature}, while rendering the In-Built thank-you message
 *     inside the REST submit — {@see render_buttons_html()}.
 *
 * @package BookingPress\Vue3
 */

namespace BookingPress\Vue3;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CalendarIntegration {

	/**
	 * Shortcode tag this class stands in for.
	 *
	 * @var string
	 */
	const SHORTCODE = 'bookingpress_appointment_calendar_integration';

	/**
	 * Render the shortcode on the thank-you page from the redirect URL context.
	 *
	 * Reads the same query args the legacy shortcode reads: `appointment_id`
	 * (base64 id) + `bp_tp_nonce`, and the `is_cart` / `is_recurring` flags the
	 * Cart / Recurring features add when `appointment_id` carries the ORDER id.
	 * Renders nothing when the nonce does not verify (legacy parity).
	 *
	 * @param array|string $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function render_shortcode( $atts ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- verified below via bp_tp_nonce.
		$raw_id = ! empty( $_GET['appointment_id'] ) ? sanitize_text_field( wp_unslash( $_GET['appointment_id'] ) ) : '';
		if ( '' === $raw_id ) {
			return '';
		}
		$decoded = base64_decode( $raw_id ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$nonce   = ! empty( $_GET['bp_tp_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['bp_tp_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bpa_nonce_url-' . md5( (string) $decoded ) ) ) {
			return '';
		}
		$is_order = ! empty( $_GET['is_cart'] ) || ! empty( $_GET['is_recurring'] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return self::render_buttons_html( (int) $decoded, is_array( $atts ) ? $atts : array(), $is_order );
	}

	/**
	 * Render the "Add to Calendar" buttons as static HTML.
	 *
	 * Honours the legacy `gateways` / `gateways_label` attributes.
	 *
	 * @param int   $id       Entry id for a single booking, ORDER id when `$is_order`.
	 * @param array $atts     Shortcode attributes.
	 * @param bool  $is_order True for a Cart / Recurring / Multi-Service order.
	 *
	 * @return string Buttons HTML, or '' when nothing can be rendered.
	 */
	public static function render_buttons_html( $id, array $atts, $is_order = false ) {
		$id = (int) $id;
		if ( $id <= 0 ) {
			return '';
		}

		$args = shortcode_atts(
			array(
				'gateways'       => 'google,yahoo,outlook,ical',
				'gateways_label' => '',
				'button_spacing' => '12',
			),
			$atts,
			self::SHORTCODE
		);

		$gateways = array_values( array_filter( array_map( 'trim', explode( ',', (string) $args['gateways'] ) ) ) );
		if ( empty( $gateways ) ) {
			return '';
		}
		$custom_labels = array_map( 'trim', explode( ',', (string) $args['gateways_label'] ) );

		$default_labels = array(
			'google'  => esc_html__( 'Google Calendar', 'bookingpress-appointment-booking' ),
			'yahoo'   => esc_html__( 'Yahoo Calendar', 'bookingpress-appointment-booking' ),
			'outlook' => esc_html__( 'Outlook Calendar', 'bookingpress-appointment-booking' ),
			'ical'    => esc_html__( 'iCal Calendar', 'bookingpress-appointment-booking' ),
		);

		$links = $is_order ? self::order_links( $id ) : self::single_links( $id );
		$icons = self::icons();

		$items_html = '';
		foreach ( $gateways as $index => $gateway ) {
			if ( ! isset( $default_labels[ $gateway ] ) ) {
				continue; // Unknown gateway token — skip.
			}

			$href = isset( $links[ $gateway ] ) ? $links[ $gateway ] : '';
			/**
			 * Server-side counterpart of the Vue 2 `bookingpress_change_calendar_url`
			 * JS filter: lets an add-on point a calendar button elsewhere.
			 *
			 * @param string $href     Button URL.
			 * @param string $gateway  `google` | `yahoo` | `outlook` | `ical`.
			 * @param int    $id       Entry id, or order id when `$is_order`.
			 * @param bool   $is_order Whether `$id` is an order id.
			 */
			$href = (string) apply_filters( 'bookingpress_v3_calendar_integration_url', $href, $gateway, $id, $is_order );
			if ( '' === $href ) {
				continue; // Couldn't resolve a link for this gateway — skip it.
			}

			// External calendars open in a new tab; `.ics` downloads stay on the page.
			$target = ( ! $is_order && ( 'google' === $gateway || 'yahoo' === $gateway ) ) ? '_blank' : '_self';

			$label = ( isset( $custom_labels[ $index ] ) && '' !== $custom_labels[ $index ] )
				? sanitize_text_field( $custom_labels[ $index ] )
				: $default_labels[ $gateway ];

			$items_html .= sprintf(
				'<div class="bpa-front-module--atc__item bpa-fm--atc__%1$s_calendar" style="margin:0px 12px 12px 0px;">'
					. '<a href="%2$s" target="%3$s" rel="noopener" class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--full-width bpa-front-link--no-underline bpa_focusable" role="button" aria-label="%4$s">'
					. '<span class="bpa-thank-you-add-cal">%5$s</span> %4$s'
					. '</a>'
				. '</div>',
				esc_attr( $gateway ),
				esc_url( $href ),
				esc_attr( $target ),
				esc_html( $label ),
				isset( $icons[ $gateway ] ) ? $icons[ $gateway ] : '' // Trusted inline SVG from icons().
			);
		}

		if ( '' === $items_html ) {
			return '';
		}

		return '<div class="bpa-front-module-calendar-integration--v3">'
			. '<div class="bpa-front-module--atc-wrapper">' . $items_html . '</div>'
			. '</div>';
	}

	/**
	 * Links for a single appointment (entry id).
	 *
	 * @param int $entry_id
	 *
	 * @return array<string,string>
	 */
	private static function single_links( $entry_id ) {
		$links = array();

		global $bookingpress_appointment_bookings;
		if ( is_object( $bookingpress_appointment_bookings )
			&& method_exists( $bookingpress_appointment_bookings, 'bookingpress_get_appointment_details_for_calendar_func' ) ) {
			$details = $bookingpress_appointment_bookings->bookingpress_get_appointment_details_for_calendar_func( $entry_id, true );
			if ( is_array( $details ) && isset( $details['variant'] ) && 'success' === $details['variant'] ) {
				$links['google'] = 'https://www.google.com/calendar/render?action=TEMPLATE&text=' . $details['google_calendar_link'];
				$links['yahoo']  = 'http://calendar.yahoo.com/?v=60&view=d&type=20&title=' . $details['yahoo_calendar_link'];
			}
		}

		if ( defined( 'BOOKINGPRESS_HOME_URL' ) ) {
			$ics = add_query_arg(
				array(
					'bpa_page'       => 'bookingpress_download',
					'action'         => 'generate_ics',
					'state'          => wp_create_nonce( 'bookingpress_calendar_ics' ),
					'appointment_id' => $entry_id,
				),
				BOOKINGPRESS_HOME_URL
			);
			$links['outlook'] = $ics;
			$links['ical']    = $ics;
		}

		return $links;
	}

	/**
	 * Links for an order: every button downloads the order-wide `.ics` served
	 * by the Cart / Recurring add-ons' `generate_ics_with_cart` handler.
	 *
	 * `selectedCalendar` mirrors the values the Vue 2 buttons sent (the
	 * Recurring handler formats times differently for `yahoo_calendar`).
	 *
	 * @param int $order_id
	 *
	 * @return array<string,string>
	 */
	private static function order_links( $order_id ) {
		$selected = array(
			'google'  => 'google_calendar',
			'yahoo'   => 'yahoo_calendar',
			'outlook' => 'outlook_calendar',
			'ical'    => 'ical',
		);
		$nonce = wp_create_nonce( 'bookingpress_calendar_ics' );

		$links = array();
		foreach ( $selected as $gateway => $calendar ) {
			$links[ $gateway ] = add_query_arg(
				array(
					'page'             => 'bookingpress_download',
					'action'           => 'generate_ics_with_cart',
					'state'            => $nonce,
					'order_id'         => $order_id,
					'selectedCalendar' => $calendar,
				),
				home_url()
			);
		}
		return $links;
	}

	/**
	 * Inline SVG icons, keyed by gateway token. Same artwork as the legacy
	 * shortcode markup.
	 *
	 * @return array<string,string>
	 */
	private static function icons() {
		return array(
			'ical'    => '<svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.21165 1.39313C8.26508 0.00788564 9.72934 0 9.72934 0C9.72934 0 9.94793 1.30376 8.89977 2.55758C7.78313 3.89814 6.51375 3.67734 6.51375 3.67734C6.51375 3.67734 6.2741 2.6233 7.21165 1.39313Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M6.64714 4.59251C7.18965 4.59251 8.19568 3.84863 9.50456 3.84863C11.7589 3.84863 12.6438 5.44942 12.6438 5.44942C12.6438 5.44942 10.9109 6.33524 10.9109 8.48014C10.9109 10.901 13.0704 11.7369 13.0704 11.7369C13.0704 11.7369 11.5614 15.9794 9.52037 15.9794C8.58281 15.9794 7.85595 15.3486 6.86836 15.3486C5.86233 15.3486 4.86421 16.0031 4.21372 16.0031C2.35178 16.0004 0 11.9787 0 8.743C0 5.55982 1.99098 3.89069 3.85818 3.89069C5.07226 3.89332 6.01508 4.59251 6.64714 4.59251Z" fill="black"/></svg>',
			'google'  => '<svg width="16" class="bookingpress_google_calendar_svg" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.6444 8.17812C15.6444 7.64479 15.5556 7.02257 15.4667 6.57812H8V9.68924H12.2667C12.0889 10.667 11.5556 11.467 10.6667 12.0892V14.1337H13.3333C14.8444 12.7115 15.6444 10.5781 15.6444 8.17812Z" fill="#4285F4"/><path fill-rule="evenodd" clip-rule="evenodd" d="M7.99978 15.9996C10.1331 15.9996 11.9998 15.2885 13.3331 14.0441L10.6664 12.0885C9.95534 12.5329 9.06645 12.8885 7.99978 12.8885C5.95534 12.8885 4.17756 11.4663 3.55534 9.59961H0.888672V11.5552C2.13312 14.2218 4.88867 15.9996 7.99978 15.9996Z" fill="#34A853"/><path fill-rule="evenodd" clip-rule="evenodd" d="M3.55556 9.511C3.37778 9.06656 3.28889 8.53322 3.28889 7.99989C3.28889 7.46656 3.37778 6.93322 3.55556 6.48878V4.44434H0.888889C0.355556 5.511 0 6.75545 0 7.99989C0 9.24434 0.266667 10.4888 0.888889 11.5554L3.55556 9.511Z" fill="#FBBC05"/><path fill-rule="evenodd" clip-rule="evenodd" d="M7.99978 3.2C9.15534 3.2 10.222 3.64444 11.022 4.35556L13.3331 2.04444C11.9998 0.8 10.1331 0 7.99978 0C4.88867 0 2.13312 1.77778 0.888672 4.44444L3.55534 6.48889C4.17756 4.62222 5.95534 3.2 7.99978 3.2Z" fill="#EA4335"/></svg>',
			'outlook' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.57897 0H0V7.57897H7.57897V0Z" fill="#F25022"/><path d="M7.57897 8.4209H0V15.9999H7.57897V8.4209Z" fill="#00A4EF"/><path d="M16.0008 0H8.42188V7.57897H16.0008V0Z" fill="#7FBA00"/><path d="M16.0008 8.4209H8.42188V15.9999H16.0008V8.4209Z" fill="#FFB900"/></svg>',
			'yahoo'   => '<svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 3.89506H3.43247L5.43118 9.00836L7.45588 3.89506H10.7976L5.76558 16H2.40215L3.77968 12.7924L0.000106295 3.89506H0ZM14.6891 7.98076H10.9461L14.2682 0L17.9975 0.000159442L14.6891 7.98076V7.98076ZM11.9266 8.74459C13.075 8.74459 14.006 9.67558 14.006 10.8238C14.006 11.9721 13.075 12.9031 11.9266 12.9031C10.7783 12.9031 9.84751 11.9721 9.84751 10.8238C9.84751 9.67558 10.7784 8.74459 11.9266 8.74459Z" fill="#5F01D1"/></svg>',
		);
	}
}

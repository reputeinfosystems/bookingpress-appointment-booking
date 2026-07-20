<?php
/**
 * PaymentService — gateway list + PayPal validate/confirm.
 *
 * Per §M0.10. Lite ships two gateways: on-site and paypal. Pro adds Stripe,
 * Razorpay, etc. via `Hooks::FILTER_PAYMENT_METHODS` (and/or by overriding
 * this service via `Hooks::FILTER_SERVICE`).
 *
 * NOTE: Auto-selection of the payment method when only one gateway is
 * enabled is **not** this service's responsibility. That logic lives in M5's
 * StateBuilder, which consumes `get_enabled_methods()` and seeds
 * `selected_payment_method` accordingly.
 *
 * @package BookingPress\Vue3\Services
 */

namespace BookingPress\Vue3\Services;

use BookingPress\Vue3\Contracts\PaymentServiceInterface;
use BookingPress\Vue3\Contracts\SubmissionServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Repositories\CustomizeRepository;
use BookingPress\Vue3\Repositories\EntryRepository;
use BookingPress\Vue3\Repositories\PaymentTransactionRepository;
use BookingPress\Vue3\Repositories\SettingsRepository;
use BookingPress\Vue3\Services\ServiceLocator;
use BookingPress\Vue3\Services\SubmissionService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentService implements PaymentServiceInterface {

	/** @var SettingsRepository */
	private $settings;

	public function __construct( ?SettingsRepository $settings = null ) {
		$this->settings = $settings ?: new SettingsRepository();
	}

	/**
	 * @inheritDoc
	 */
	public function get_enabled_methods( array $context = array() ) {
		$payment = $this->settings->get_group( SettingsRepository::GROUP_PAYMENT );

		$on_site_on = $this->is_truthy( isset( $payment['on_site_payment'] ) ? $payment['on_site_payment'] : '' );
		$paypal_on  = $this->is_truthy( isset( $payment['paypal_payment'] ) ? $payment['paypal_payment'] : '' );

		$methods = array();

		$form_context = isset( $context['context'] ) ? $context['context'] : 'booking_form';

		$customize     = new CustomizeRepository();

		if ( $on_site_on && $form_context !== 'complete_payment' ) {
			$methods[] = array(
				'id'    => 'on-site',
				'label' => $customize->get( 'locally_text', CustomizeRepository::GROUP_BOOKING_FORM, 0 ) ?: 'Pay Locally',
				'mode'  => 'on_site',
				'icon'  => '',
				'extra' => array(),
			);
		}

		if ( $paypal_on ) {
			$mode  = isset( $payment['paypal_payment_method_type'] ) ? (string) $payment['paypal_payment_method_type'] : 'redirect';
			$mode  = ( 'popup' === $mode ) ? 'popup' : 'redirect';
			$methods[] = array(
				'id'    => 'paypal',
				'label' => $customize->get( 'paypal_text', CustomizeRepository::GROUP_BOOKING_FORM, 0 ) ?: 'PayPal',
				'mode'  => $mode,
				'icon'  => '',
				'extra' => array(
					'client_id' => isset( $payment['paypal_client_id'] ) ? (string) $payment['paypal_client_id'] : '',
				),
			);
		}

		/**
		 * Filter the enabled payment methods.
		 *
		 * @param array $methods
		 * @param array $context
		 */
		$methods = apply_filters( Hooks::FILTER_PAYMENT_METHODS, $methods, $context );
		return is_array( $methods ) ? $methods : array();
	}

	/**
	 * @inheritDoc
	 *
	 * Port of the legacy `bookingpress_paypal_booking_validate_lite_func`
	 * (class.bookingpress_appointment_bookings.php:415-681). Performs the
	 * exact same three-step pipeline:
	 *
	 *   1. Re-run Submit gates + price check + insert stage-1 entries row.
	 *      Delegated to SubmissionService::stage1_for_paypal() to keep the
	 *      validation logic in a single place.
	 *   2. OAuth against PayPal /v1/oauth2/token with client credentials.
	 *   3. Create a PayPal order via /v2/checkout/orders with
	 *      `reference_id = entry_id` so paypal_confirm() can recover the
	 *      entry from the verified PayPal response (NOT from the client).
	 *
	 * Returns `{ order_id, entry_id, paypal_success_url, paypal_cancel_url }`.
	 * The success URL is the same `appointment_id=base64(entry_id)&bp_tp_nonce`
	 * convention SubmissionService uses for on-site finalize, so the SDK
	 * `onApprove` redirect lands on the same thank-you page.
	 */
	public function paypal_validate( array $payload ) {
		$payment        = $this->settings->get_group( SettingsRepository::GROUP_PAYMENT );
		$client_id      = isset( $payment['paypal_client_id'] ) ? (string) $payment['paypal_client_id'] : '';
		$client_secret  = isset( $payment['paypal_client_secret'] ) ? (string) $payment['paypal_client_secret'] : '';
		$mode           = isset( $payment['paypal_payment_mode'] ) ? (string) $payment['paypal_payment_mode'] : '';

		if ( '' === $client_id ) {
			throw new \RuntimeException( 'Please configure PayPal Client ID' );
		}
		if ( '' === $client_secret ) {
			throw new \RuntimeException( 'Please Configure PayPal Client Secret' );
		}

		// Stage the booking through the canonical submit pipeline (readiness
		// gates, anti-tamper price check, entries insert). For a PayPal booking
		// this returns a `pending_payment` envelope carrying the primary
		// `entry_id`; the booking is finalized later by paypal_confirm() after
		// the SDK capture. The popup/Smart-Buttons `createOrder` posts here
		// directly WITHOUT a prior /submit, so we must stage here — the previous
		// implementation looked up a non-existent `appointment_id`, so it never
		// found an entry and sent PayPal a zero amount (which PayPal rejects as
		// "Request is not well-formed / violates schema").
		$submission = $this->get_submission_service();
		$staged     = $submission->submit( $payload );

		// Surface any staging failure (price mismatch, readiness gate, insert
		// failure) verbatim so the SDK button shows why the order was refused.
		if ( is_array( $staged ) && isset( $staged['variant'] ) && 'error' === $staged['variant'] ) {
			$msg = isset( $staged['error_message'] ) ? (string) $staged['error_message'] : '';
			if ( '' === $msg && isset( $staged['message'] ) ) {
				$msg = (string) $staged['message'];
			}
			throw new \RuntimeException( '' !== $msg ? $msg : 'Could not stage the booking for PayPal.' );
		}

		$entry_id = isset( $staged['entry_id'] ) ? (int) $staged['entry_id'] : 0;
		if ( $entry_id <= 0 ) {
			throw new \RuntimeException( 'Could not stage the booking for PayPal.' );
		}

		// Read the SERVER-authoritative amount + currency off the staged entry —
		// never the client-sent value. Mirrors paypal_redirect_prepare(): a
		// pending Complete Payment entry charges the remaining payable, a
		// booking-form entry charges the amount staged on the entry.
		$entry = ( new EntryRepository() )->find( $entry_id );
		if ( null === $entry ) {
			throw new \RuntimeException( 'Could not resolve the staged booking for PayPal.' );
		}

		$cp_payable = $submission->complete_payment_payable_for_entry( $entry_id );
		$total = ( null !== $cp_payable )
			? (float) $cp_payable
			: ( isset( $entry['bookingpress_paid_amount'] ) ? (float) $entry['bookingpress_paid_amount'] : 0.0 );

		$currency_code = isset( $entry['bookingpress_service_currency'] ) && '' !== $entry['bookingpress_service_currency']
			? (string) $entry['bookingpress_service_currency']
			: (string) $this->settings->get( 'payment_default_currency', SettingsRepository::GROUP_PAYMENT, 'USD' );

		if ( $total <= 0.0 ) {
			throw new \RuntimeException( 'Service price must be more than 0 for PayPal.' );
		}

		// PayPal endpoints (sandbox vs live — legacy parity).
		$sandbox   = ( 'sandbox' === $mode );
		$token_url = $sandbox ? 'https://api-m.sandbox.paypal.com/v1/oauth2/token' : 'https://api-m.paypal.com/v1/oauth2/token';
		$api_url   = $sandbox ? 'https://api-m.sandbox.paypal.com/v2/checkout/orders' : 'https://api-m.paypal.com/v2/checkout/orders';

		$access_token = $this->paypal_oauth_token( $token_url, $client_id, $client_secret );

		// amount.value MUST be a plain numeric string: '.' decimal separator,
		// NO thousands separator, currency-appropriate decimals. number_format()
		// on the float guarantees this regardless of the site's display locale
		// (e.g. a comma-dot "1,000.00" or dot-comma "1.000,00" display never
		// leaks into the payload). Zero-decimal currencies carry no fraction.
		$decimals = intval( $this->settings->get( 'price_number_of_decimals', SettingsRepository::GROUP_PAYMENT, 2 ) );
		if ( in_array( $currency_code, array( 'HUF', 'JPY', 'TWD' ), true ) ) {
			$decimals = 0;
		}
		$amount_value = number_format( (float) $total, $decimals, '.', '' );

		// Build order. Reference id MUST equal entry_id — paypal_confirm
		// reads this back from the verified PayPal response to know which
		// stage-1 entry to finalize.
		$create_body = array(
			'intent'         => 'CAPTURE',
			'purchase_units' => array(
				array(
					'reference_id' => (string) $entry_id,
					'description'  => esc_html__( 'Appointment Booking', 'bookingpress-appointment-booking' ),
					'amount'       => array(
						'currency_code' => $currency_code,
						'value'         => $amount_value,
					),
				),
			),
		);

		$create_response = wp_remote_post( $api_url, array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body'    => wp_json_encode( $create_body ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $create_response ) ) {
			throw new \RuntimeException( $create_response->get_error_message() );
		}

		$order_body = wp_remote_retrieve_body( $create_response );
		$order_data = json_decode( $order_body, true );
		$order_id   = isset( $order_data['id'] ) ? (string) $order_data['id'] : '';
		if ( '' === $order_id ) {
			// PayPal's generic top-level `message` (e.g. "...failed business
			// validation.") is useless on its own — the actionable reason is in
			// `details[].issue` / `details[].description`. Surface it (and log the
			// raw body + HTTP status) so 422 UNPROCESSABLE_ENTITY rejections
			// (CURRENCY_NOT_SUPPORTED, DECIMAL_PRECISION, etc.) are diagnosable.
			$http_status = (int) wp_remote_retrieve_response_code( $create_response );
			$msg         = isset( $order_data['message'] ) ? (string) $order_data['message'] : 'Failed to create PayPal order';
			if ( ! empty( $order_data['details'] ) && is_array( $order_data['details'] ) ) {
				$parts = array();
				foreach ( $order_data['details'] as $detail ) {
					$issue = isset( $detail['issue'] ) ? (string) $detail['issue'] : '';
					$desc  = isset( $detail['description'] ) ? (string) $detail['description'] : '';
					$field = isset( $detail['field'] ) ? (string) $detail['field'] : '';
					$line  = trim( $issue . ( '' !== $desc ? ': ' . $desc : '' ) . ( '' !== $field ? ' [' . $field . ']' : '' ) );
					if ( '' !== $line ) {
						$parts[] = $line;
					}
				}
				if ( ! empty( $parts ) ) {
					$msg .= ' (' . implode( '; ', $parts ) . ')';
				}
			}
			error_log( 'BOOKINGPRESS PayPal create-order failed [HTTP ' . $http_status . '] body=' . $order_body );
			throw new \RuntimeException( $msg );
		}

		// Success URL — same base64(entry_id) + nonce convention used by
		// SubmissionService::finalize_booking() so the thank-you page can
		// pick up the booking the same way as on-site flows.
		//$success_url = $submission->build_redirect_url_for_entry( $entry_id );

		// Cancel URL — `after_failed_payment_redirection` page from the
		// customize panel, with `is_cancel=1` (legacy parity).
		$customize     = new CustomizeRepository();
		$cancel_page_id = (int) $customize->get( 'after_failed_payment_redirection', CustomizeRepository::GROUP_BOOKING_FORM, 0 );
		$cancel_url     = ( $cancel_page_id > 0 ) ? get_permalink( $cancel_page_id ) : home_url( '/' );
		if ( empty( $cancel_url ) ) {
			$cancel_url = home_url( '/' );
		}
		$cancel_url = add_query_arg( 'is_cancel', 1, esc_url_raw( $cancel_url ) );

		$success_url  = "";
		return array(
			'order_id'           => $order_id,
			'entry_id'           => $entry_id,
			'paypal_success_url' => $success_url,
			'paypal_cancel_url'  => $cancel_url,
		);
	}

	/**
	 * @inheritDoc
	 *
	 * Port of legacy `bookingpress_paypal_booking_payment_confirm_lite`
	 * (class.bookingpress_appointment_bookings.php:301-409).
	 *
	 * The client passes the raw PayPal capture response under
	 * `bookingpress_payment_res` (matching the legacy field name). The
	 * `id` inside that response is the PayPal order id — we re-fetch the
	 * order server-side via GET /v2/checkout/orders/{id} so we are NOT
	 * trusting any field the browser could forge. From the verified
	 * server response we read:
	 *
	 *   - purchase_units[0].reference_id  → our entry_id (set in validate)
	 *   - purchase_units[0].payments.captures[0].id     → transaction_id
	 *   - purchase_units[0].payments.captures[0].status → PAID vs PENDING
	 *   - purchase_units[0].amount.value/currency_code  → paid_amount, currency
	 *
	 * Order MUST be `COMPLETED` — anything else is rejected as an error
	 * (no booking written).
	 *
	 * Once verified, hand off to SubmissionService::finalize_booking()
	 * which materialises appointment_bookings + payment_transactions
	 * rows and returns the standard `{ variant: 'redirect_url', ... }`
	 * envelope — identical to the on-site/zero-price success path.
	 */
	public function paypal_confirm( array $payload ) {
		// Legacy parity (class.bookingpress_appointment_bookings.php:318):
		// log the popup response before any validation so we still get a
		// row when the subsequent verification fails.
		global $bookingpress_debug_payment_log_id;
		do_action( 'bookingpress_payment_log_entry', 'paypal', 'payment popup response data', 'bookingpress pro', $payload, $bookingpress_debug_payment_log_id );

		$payment_res = isset( $payload['bookingpress_payment_res'] ) ? $payload['bookingpress_payment_res'] : null;
		if ( is_string( $payment_res ) ) {
			$payment_res = json_decode( stripslashes_deep( $payment_res ), true );
		}
		if ( ! is_array( $payment_res ) ) {
			throw new \RuntimeException( 'Missing payment response.' );
		}

		$order_id = isset( $payment_res['id'] ) ? (string) $payment_res['id'] : '';
		if ( '' === $order_id ) {
			throw new \RuntimeException( 'Missing PayPal order id.' );
		}

		// Server-side verification — never trust the client capture body.
		$order = $this->fetch_paypal_order( $order_id );
		if ( false === $order || ! is_array( $order ) ) {
			throw new \RuntimeException( 'Could not validate PayPal order.' );
		}

		$order_status   = isset( $order['status'] ) ? (string) $order['status'] : '';
		$reference_id   = isset( $order['purchase_units'][0]['reference_id'] ) ? (string) $order['purchase_units'][0]['reference_id'] : '';
		$transaction_id = isset( $order['purchase_units'][0]['payments']['captures'][0]['id'] ) ? (string) $order['purchase_units'][0]['payments']['captures'][0]['id'] : '';
		$capture_status = isset( $order['purchase_units'][0]['payments']['captures'][0]['status'] ) ? (string) $order['purchase_units'][0]['payments']['captures'][0]['status'] : '';
		$amount         = isset( $order['purchase_units'][0]['amount']['value'] ) ? (float) $order['purchase_units'][0]['amount']['value'] : 0.0;
		$currency_code  = isset( $order['purchase_units'][0]['amount']['currency_code'] ) ? (string) $order['purchase_units'][0]['amount']['currency_code'] : 'USD';

		if ( 'COMPLETED' !== $order_status ) {
			throw new \RuntimeException(
				sprintf( 'Sorry, payment is not successed with the paypal. (status: %s)', $order_status )
			);
		}

		$entry_id = (int) $reference_id;
		if ( $entry_id <= 0 ) {
			throw new \RuntimeException( 'Could not resolve booking entry from PayPal order.' );
		}

		$payment_status_code = ( 'PENDING' === $capture_status )
			? PaymentTransactionRepository::STATUS_PENDING
			: PaymentTransactionRepository::STATUS_PAID;

		$submission = $this->get_submission_service();

		// finalize_booking materialises appointment_bookings +
		// payment_transactions rows and returns
		// `{ variant: 'redirect_url', redirect_data, ... }`.
		return $submission->finalize_booking( $entry_id, array(
			'payment_gateway' => 'paypal',
			'payment_status'  => $payment_status_code,
			'transaction_id'  => $transaction_id,
			'paid_amount'     => $amount,
			'currency'        => $currency_code,
			'payload'         => array(),
		) );
	}

	/**
	 * @inheritDoc
	 *
	 * PayPal Standard ("Legacy") redirect. Port of the legacy webscr flow in
	 * `bookingpress_save_appointment_booking_func`
	 * (class.bookingpress_appointment_bookings.php:4424-4548). The booking is
	 * already staged as `pending_payment` by SubmissionService::submit(), so
	 * we only receive its `entry_id`; from the staged entries row we read the
	 * charged amount / currency / service name / customer email and build the
	 * auto-submit form that redirects the browser to PayPal.
	 */
	public function paypal_redirect_prepare( array $payload ) {
		$submission     = $this->get_submission_service();
		$appointment_id = isset( $payload['appointment_id'] ) ? (int) $payload['appointment_id'] : 0;
		$entry_id       = isset( $payload['entry_id'] ) ? (int) $payload['entry_id'] : 0;

		// Complete Payment passes `appointment_id`; resolve its staged entry.
		if ( $entry_id <= 0 && $appointment_id > 0 ) {
			global $wpdb, $tbl_bookingpress_appointment_bookings;
			$entry_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT bookingpress_entry_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",
					$appointment_id
				)
			);
		}
		if ( $entry_id <= 0 ) {
			throw new \RuntimeException( 'Missing booking reference for PayPal.' );
		}

		$entry = ( new EntryRepository() )->find( $entry_id );
		if ( null === $entry ) {
			throw new \RuntimeException( 'Could not resolve the staged booking for PayPal.' );
		}

		$payment         = $this->settings->get_group( SettingsRepository::GROUP_PAYMENT );
		$merchant_email  = isset( $payment['paypal_merchant_email'] ) ? trim( (string) $payment['paypal_merchant_email'] ) : '';
		$mode            = isset( $payment['paypal_payment_mode'] ) ? (string) $payment['paypal_payment_mode'] : '';

		if ( '' === $merchant_email ) {
			throw new \RuntimeException( esc_html__( 'Please configure merchant email address', 'bookingpress-appointment-booking' ) );
		}

		// Amount: for a pending Complete Payment entry the charge is the SINGLE
		// server-authoritative remaining payable (due − coupon − gift + tip);
		// for a booking-form entry it is the amount staged on the entry. Never
		// trust a client-supplied total.
		$cp_payable = $submission->complete_payment_payable_for_entry( $entry_id );
		$is_complete_payment = ( null !== $cp_payable );
		$amount = $is_complete_payment
			? (float) $cp_payable
			: ( isset( $entry['bookingpress_paid_amount'] ) ? (float) $entry['bookingpress_paid_amount'] : 0.0 );

		$currency_code = isset( $entry['bookingpress_service_currency'] ) && '' !== $entry['bookingpress_service_currency']
			? (string) $entry['bookingpress_service_currency']
			: (string) $this->settings->get( 'payment_default_currency', SettingsRepository::GROUP_PAYMENT, 'USD' );
		$service_name  = isset( $entry['bookingpress_service_name'] ) && '' !== $entry['bookingpress_service_name']
			? (string) $entry['bookingpress_service_name']
			: esc_html__( 'Appointment Booking', 'bookingpress-appointment-booking' );
		$customer_email = isset( $entry['bookingpress_customer_email'] ) ? (string) $entry['bookingpress_customer_email'] : '';

		if ( $amount <= 0.0 ) {
			throw new \RuntimeException( 'Service price must be more than 0 for PayPal.' );
		}

		// Return / cancel / notify URLs. For a booking-form entry `return`
		// reuses the base64(entry_id)+nonce thank-you convention; for Complete
		// Payment it is the complete-payment page (so the returning buyer sees
		// the paid/success state). `notify_url` is the public IPN listener that
		// actually finalizes the booking.
		$success_url = $submission->build_redirect_url_for_entry( $entry_id );
		if ( $is_complete_payment ) {
			// Complete Payment is a Pro feature and `$is_complete_payment` can only
			// be true when Pro answered the payable filter above, so Pro's callback
			// is guaranteed to be registered here.
			$success_url = (string) apply_filters( Hooks::FILTER_COMPLETE_PAYMENT_RETURN_URL, $success_url, (int) $entry_id );
		}

		$customize      = new CustomizeRepository();
		$cancel_page_id = (int) $customize->get( 'after_failed_payment_redirection', CustomizeRepository::GROUP_BOOKING_FORM, 0 );
		$cancel_url     = ( $cancel_page_id > 0 ) ? get_permalink( $cancel_page_id ) : home_url( '/' );
		if ( empty( $cancel_url ) ) {
			$cancel_url = home_url( '/' );
		}
		$cancel_url = add_query_arg( 'is_cancel', 1, esc_url_raw( $cancel_url ) );

		$notify_url = $this->paypal_ipn_url();

		$sandbox   = ( 'live' !== $mode ) ? 'sandbox.' : '';
		$paypal_ep = 'https://www.' . $sandbox . 'paypal.com/cgi-bin/webscr';

		// Auto-submit form — mirrors the legacy `_xclick` markup. `custom`
		// carries the entry_id so the IPN can recover the staged booking.
		$fields = array(
			'cmd'           => '_xclick',
			'business'      => $merchant_email,
			'amount'        => number_format( $amount, 2, '.', '' ),
			'currency_code' => $currency_code,
			'item_name'     => $service_name,
			'item_number'   => '1',
			'custom'        => (string) $entry_id,
			'notify_url'    => $notify_url,
			'return'        => $success_url,
			'cancel_return' => $cancel_url,
			'rm'            => '2',
			'no_shipping'   => '1',
			'lc'            => 'en_US',
			'charset'       => 'UTF-8',
			'page_style'    => 'primary',
			'on0'           => 'user_email',
			'os0'           => $customer_email,
		);

		$form  = '<form name="_xclick" id="bookingpress_paypal_form" action="' . esc_url( $paypal_ep ) . '" method="post">';
		foreach ( $fields as $name => $value ) {
			$form .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
		}
		$form .= '</form>';
		$form .= '<script type="text/javascript">document.getElementById("bookingpress_paypal_form").submit();</script>';

		global $bookingpress_debug_payment_log_id;
		do_action( 'bookingpress_payment_log_entry', 'paypal', 'payment form redirected data', 'bookingpress', $form, $bookingpress_debug_payment_log_id );

		return array(
			'variant'       => 'redirect',
			'is_redirect'   => 1,
			'redirect_data' => $form,
			'entry_id'      => $entry_id,
		);
	}

	/**
	 * @inheritDoc
	 *
	 * PayPal Standard IPN listener. Verifies the notification with PayPal and
	 * finalizes the staged booking on a confirmed payment. Never trusts the
	 * POST body until PayPal echoes `VERIFIED`.
	 */
	public function paypal_ipn( array $post ) {
		global $bookingpress_debug_payment_log_id;
		do_action( 'bookingpress_payment_log_entry', 'paypal', 'legacy ipn received', 'bookingpress', $post, $bookingpress_debug_payment_log_id );

		if ( empty( $post ) ) {
			return false;
		}

		$payment        = $this->settings->get_group( SettingsRepository::GROUP_PAYMENT );
		$merchant_email = isset( $payment['paypal_merchant_email'] ) ? strtolower( trim( (string) $payment['paypal_merchant_email'] ) ) : '';
		$mode           = isset( $payment['paypal_payment_mode'] ) ? (string) $payment['paypal_payment_mode'] : '';

		// 1. Verify authenticity — re-post verbatim with cmd=_notify-validate.
		if ( ! $this->paypal_ipn_is_verified( $post, $mode ) ) {
			do_action( 'bookingpress_payment_log_entry', 'paypal', 'legacy ipn NOT verified', 'bookingpress', $post, $bookingpress_debug_payment_log_id );
			return false;
		}

		// 2. Resolve the staged entry from the tamper-proof `custom` field.
		$entry_id = isset( $post['custom'] ) ? (int) $post['custom'] : 0;
		if ( $entry_id <= 0 ) {
			return false;
		}

		$entry = ( new EntryRepository() )->find( $entry_id );
		if ( null === $entry ) {
			return false;
		}

		// 3. Idempotency — a FINALIZED appointment for this entry means a prior
		//    IPN already completed it (PayPal retries IPNs). On Lite an
		//    appointment row exists for an entry ONLY after finalize, so any
		//    match means "already done". Pro's Complete Payment also stages a
		//    PENDING appointment against the entry (its
		//    `bookingpress_complete_payment_token` — a Pro-only column — still
		//    set); Pro's callback on the refinement filter returns false for it
		//    so finalize_booking can complete that payment (that path has its
		//    own marker-based idempotency).
		global $wpdb, $tbl_bookingpress_appointment_bookings;
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d LIMIT 1",
				$entry_id
			)
		);
		if ( $existing && (bool) apply_filters( Hooks::FILTER_PAYPAL_IPN_ENTRY_FINALIZED, true, (int) $entry_id, (int) $existing ) ) {
			return true;
		}

		// 4. Business-rule checks against the staged entry.
		$receiver_email = isset( $post['receiver_email'] ) ? strtolower( trim( (string) $post['receiver_email'] ) ) : '';
		if ( '' !== $merchant_email && $receiver_email !== $merchant_email ) {
			do_action( 'bookingpress_payment_log_entry', 'paypal', 'legacy ipn receiver mismatch', 'bookingpress', array( 'expected' => $merchant_email, 'got' => $receiver_email ), $bookingpress_debug_payment_log_id );
			return false;
		}

		$mc_gross    = isset( $post['mc_gross'] ) ? (float) $post['mc_gross'] : 0.0;
		$mc_currency = isset( $post['mc_currency'] ) ? (string) $post['mc_currency'] : '';
		// Expected charge: Complete Payment charges the server-authoritative
		// remaining payable; a booking-form entry charges its staged amount.
		$cp_payable  = $this->get_submission_service()->complete_payment_payable_for_entry( $entry_id );
		$staged_amt  = ( null !== $cp_payable )
			? (float) $cp_payable
			: ( isset( $entry['bookingpress_paid_amount'] ) ? (float) $entry['bookingpress_paid_amount'] : 0.0 );
		if ( abs( $mc_gross - $staged_amt ) > 0.01 ) {
			do_action( 'bookingpress_payment_log_entry', 'paypal', 'legacy ipn amount mismatch', 'bookingpress', array( 'expected' => $staged_amt, 'got' => $mc_gross ), $bookingpress_debug_payment_log_id );
			return false;
		}

		$payment_status = isset( $post['payment_status'] ) ? (string) $post['payment_status'] : '';
		if ( 'Completed' !== $payment_status && 'Pending' !== $payment_status ) {
			// Failed / refunded / denied — do not finalize.
			return false;
		}

		$status_code = ( 'Pending' === $payment_status )
			? PaymentTransactionRepository::STATUS_PENDING
			: PaymentTransactionRepository::STATUS_PAID;

		$txn_id = isset( $post['txn_id'] ) ? (string) $post['txn_id'] : '';

		// 5. Finalize — materialise the appointment + payment rows.
		$this->get_submission_service()->finalize_booking( $entry_id, array(
			'payment_gateway' => 'paypal',
			'payment_status'  => $status_code,
			'transaction_id'  => $txn_id,
			'paid_amount'     => $mc_gross,
			'currency'        => '' !== $mc_currency ? $mc_currency : ( isset( $entry['bookingpress_service_currency'] ) ? (string) $entry['bookingpress_service_currency'] : 'USD' ),
			'payload'         => array(),
		) );

		return true;
	}

	/**
	 * Public URL of the IPN listener REST route (the webscr `notify_url`).
	 *
	 * @return string
	 */
	private function paypal_ipn_url() {
		$route = \BookingPress\Vue3\REST\RouteRegistrar::REST_NAMESPACE . '/'
			. \BookingPress\Vue3\REST\RouteRegistrar::ROUTE_PREFIX . '/payment/paypal-ipn';
		return esc_url_raw( rest_url( $route ) );
	}

	/**
	 * Re-post an IPN to PayPal with `cmd=_notify-validate` and return whether
	 * PayPal echoes `VERIFIED`.
	 *
	 * @param array  $post The received IPN fields.
	 * @param string $mode `live` or (default) sandbox.
	 *
	 * @return bool
	 */
	private function paypal_ipn_is_verified( array $post, $mode ) {
		$sandbox    = ( 'live' !== $mode );
		$verify_url = $sandbox
			? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr'
			: 'https://ipnpb.paypal.com/cgi-bin/webscr';

		$body = array_merge( array( 'cmd' => '_notify-validate' ), $post );

		$response = wp_remote_post( $verify_url, array(
			'method'      => 'POST',
			'timeout'     => 30,
			'httpversion' => '1.1',
			'headers'     => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Connection'   => 'Close',
			),
			'body'        => $body,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return 'VERIFIED' === trim( (string) wp_remote_retrieve_body( $response ) );
	}

	/**
	 * OAuth round-trip against PayPal — returns the access token string.
	 *
	 * @param string $token_url
	 * @param string $client_id
	 * @param string $client_secret
	 *
	 * @return string
	 *
	 * @throws \RuntimeException On HTTP error or PayPal auth error.
	 */
	private function paypal_oauth_token( $token_url, $client_id, $client_secret ) {
		$response = wp_remote_post( $token_url, array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
			),
			'body'    => array(
				'grant_type' => 'client_credentials',
			),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$auth = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $auth ) && ! empty( $auth['error'] ) ) {
			$msg = isset( $auth['error_description'] ) ? (string) $auth['error_description'] : (string) $auth['error'];
			throw new \RuntimeException( $msg );
		}
		if ( empty( $auth ) || empty( $auth['access_token'] ) ) {
			throw new \RuntimeException( 'PayPal authentication failed.' );
		}

		return (string) $auth['access_token'];
	}

	/**
	 * Fetch a PayPal order by id and return the decoded body.
	 *
	 * Port of legacy `validate_paypal_order()` — does a fresh OAuth then
	 * GETs `/v2/checkout/orders/{id}` so we have the authoritative status
	 * + reference_id + capture details to drive `paypal_confirm`.
	 *
	 * @param string $order_id
	 *
	 * @return array|false
	 *
	 * @throws \RuntimeException On HTTP / auth / API error.
	 */
	private function fetch_paypal_order( $order_id ) {
		$payment       = $this->settings->get_group( SettingsRepository::GROUP_PAYMENT );
		$client_id     = isset( $payment['paypal_client_id'] ) ? (string) $payment['paypal_client_id'] : '';
		$client_secret = isset( $payment['paypal_client_secret'] ) ? (string) $payment['paypal_client_secret'] : '';
		$mode          = isset( $payment['paypal_payment_mode'] ) ? (string) $payment['paypal_payment_mode'] : '';

		if ( '' === $client_id || '' === $client_secret ) {
			throw new \RuntimeException( 'PayPal credentials are not configured.' );
		}

		$sandbox   = ( 'sandbox' === $mode );
		$token_url = $sandbox ? 'https://api-m.sandbox.paypal.com/v1/oauth2/token' : 'https://api-m.paypal.com/v1/oauth2/token';
		$api_url   = $sandbox ? 'https://api-m.sandbox.paypal.com/v2/checkout' : 'https://api-m.paypal.com/v2/checkout';

		$access_token = $this->paypal_oauth_token( $token_url, $client_id, $client_secret );

		$response = wp_remote_get( $api_url . '/orders/' . rawurlencode( $order_id ), array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
			),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $body ) && ! empty( $body['error'] ) ) {
			$msg = isset( $body['error_description'] ) ? (string) $body['error_description'] : (string) $body['error'];
			throw new \RuntimeException( $msg );
		}

		return is_array( $body ) ? $body : false;
	}

	/**
	 * Resolve the SubmissionService. Routed through ServiceLocator so
	 * Pro / addons that swap SubmissionServiceInterface still get the
	 * override; falls back to a direct instance if the locator is not
	 * primed in the current call context.
	 *
	 * @return SubmissionService
	 */
	private function get_submission_service() {
		try {
			$svc = ServiceLocator::get( SubmissionServiceInterface::class );
			if ( $svc instanceof SubmissionService ) {
				return $svc;
			}
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
			// Fall through to direct instantiation.
		}
		return new SubmissionService();
	}

	/**
	 * Truthy admin-setting normalizer (settings carry `'true'`/`'false'`/`'1'`/`'0'`).
	 *
	 * @param mixed $v
	 *
	 * @return bool
	 */
	private function is_truthy( $v ) {
		$v = strtolower( (string) $v );
		return ( 'true' === $v || '1' === $v );
	}
}

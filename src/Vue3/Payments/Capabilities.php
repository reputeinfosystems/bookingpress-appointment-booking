<?php
/**
 * Capabilities — the negotiated contract between core and a gateway add-on.
 *
 * WHY
 * ---
 * Sealing the amount (see {@see PaymentReference}) removes the need to
 * re-release gateways when a new CONTEXT or a new amount-affecting ADD-ON
 * lands. It does not remove the need when core asks gateways to do something
 * genuinely new — refunds, saved cards, subscriptions, 3DS step-up.
 *
 * Rather than pretend that never happens, make it safe:
 *
 *   - A gateway declares what it can do via `supports()`.
 *   - A context declares what it requires via `get_required_capabilities()`.
 *   - {@see GatewayRegistry} hides gateways that cannot satisfy the context.
 *
 * The result: shipping a capability that an old gateway lacks makes that
 * gateway quietly unavailable where it is not viable, instead of fatalling or
 * silently charging the wrong amount. Old gateways keep working for everything
 * they already supported. Merchants see a correct list. Nobody is forced into a
 * same-day release.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capabilities {

	/**
	 * Payment API contract version.
	 *
	 * Bump MAJOR only for a breaking change to an interface or hook signature.
	 * A gateway declares the maximum it was built against; the registry refuses
	 * to load one built against a MAJOR it does not understand rather than
	 * letting it half-work.
	 */
	const API_VERSION = '1.0.0';

	// --- Flow shapes ------------------------------------------------------

	/** Gateway collects card details inline and confirms client-side (Stripe Payment Elements, Square). */
	const INLINE_FIELDS = 'inline_fields';

	/** Gateway opens its own popup/overlay (PayPal Smart Buttons, Razorpay checkout). */
	const POPUP = 'popup';

	/** Gateway takes over the page and returns via a URL (PayPal Standard, Mollie, Stripe Checkout). */
	const REDIRECT = 'redirect';

	/** Gateway finalizes out-of-band via a server-to-server callback (IPN, webhook). */
	const WEBHOOK = 'webhook';

	// --- Behaviours -------------------------------------------------------

	/**
	 * Gateway can charge an amount decided by core rather than by its own
	 * hosted cart. Required by every context — a gateway that cannot honour a
	 * server-sealed amount cannot participate in this architecture.
	 */
	const SEALED_AMOUNT = 'sealed_amount';

	/** Gateway can charge one total covering many bookings (Cart, Recurring, Multi-Service). */
	const SHARED_ORDER = 'shared_order';

	/** Gateway can charge less than the order total (Deposit, partial Gift Card redemption). */
	const PARTIAL_PAYMENT = 'partial_payment';

	/** Gateway exposes a programmatic refund. */
	const REFUND = 'refund';

	/** Gateway can vault a payment method for later reuse. */
	const SAVED_METHOD = 'saved_method';

	/** Gateway supports recurring / scheduled charges natively. */
	const RECURRING = 'recurring';

	/**
	 * Capabilities every gateway must declare to be usable at all.
	 *
	 * @return string[]
	 */
	public static function baseline() {
		return array( self::SEALED_AMOUNT );
	}

	/**
	 * All known capability strings — used to validate a gateway's declaration
	 * so a typo ('refunds') fails loudly in debug instead of silently never
	 * matching.
	 *
	 * @return string[]
	 */
	public static function all() {
		return array(
			self::INLINE_FIELDS,
			self::POPUP,
			self::REDIRECT,
			self::WEBHOOK,
			self::SEALED_AMOUNT,
			self::SHARED_ORDER,
			self::PARTIAL_PAYMENT,
			self::REFUND,
			self::SAVED_METHOD,
			self::RECURRING,
		);
	}

	/**
	 * Whether a gateway built against `$declared` can run on this core.
	 *
	 * @param string $declared The gateway's declared API version.
	 *
	 * @return bool
	 */
	public static function is_compatible( $declared ) {
		$declared = (string) $declared;
		if ( '' === $declared ) {
			return false;
		}
		$gateway_major = (int) strtok( $declared, '.' );
		$core_major    = (int) strtok( self::API_VERSION, '.' );
		return $gateway_major === $core_major;
	}
}

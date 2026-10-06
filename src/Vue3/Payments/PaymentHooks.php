<?php
/**
 * PaymentHooks — hook names owned by the gateway-agnostic payment layer.
 *
 * Kept separate from {@see \BookingPress\Vue3\Hooks} (which is already ~950
 * lines) so the payment contract can be read, versioned and documented on its
 * own. Same naming convention: everything is prefixed `bookingpress_payments_`.
 *
 * STABILITY PROMISE
 * -----------------
 * These names, and the signatures documented beside them, are what add-on
 * authors build against. Changing one is a breaking change requiring a bump of
 * {@see Capabilities::API_VERSION}. Adding one is not.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentHooks {

	// --- Registration -----------------------------------------------------

	/**
	 * Register payment gateways.
	 *
	 * THIS IS THE ONLY THING A GATEWAY ADD-ON MUST DO. It replaces, for every
	 * gateway: `register_rest_route()` calls (one set per form), the
	 * `FILTER_PAYMENT_METHODS` callback, the `FILTER_INITIAL_STATE` +
	 * `FILTER_PRO_INITIAL_STATE` pair, and the hand-rolled NonceGate / Response
	 * / entry-lookup / finalize plumbing.
	 *
	 * Filter signature: `(PaymentGatewayInterface[] $gateways): PaymentGatewayInterface[]`
	 * Keyed by gateway id.
	 */
	const FILTER_REGISTER_GATEWAYS = 'bookingpress_payments_register_gateways';

	/**
	 * Register payment contexts (the things one can pay FOR).
	 *
	 * Lite registers `booking_form`. Pro registers `complete_payment`. Gift Card
	 * registers `gift_card`. Package registers `package`. A future module
	 * registers its own — and no gateway ships again.
	 *
	 * Filter signature: `(PaymentContextInterface[] $contexts): PaymentContextInterface[]`
	 * Keyed by context id.
	 */
	const FILTER_REGISTER_CONTEXTS = 'bookingpress_payments_register_contexts';

	// --- Amount pipeline --------------------------------------------------

	/**
	 * Order-level payable adjustment, after per-item resolution.
	 *
	 * For add-ons whose effect is not decomposable per line item (an order-wide
	 * gift card balance, a cart-level coupon, a minimum charge). Runs once per
	 * order. Per-item adjustments belong on the existing
	 * `Hooks::FILTER_PAYABLE_AMOUNT`.
	 *
	 * Filter signature: `(float $payable, float $order_total, array $context): float`
	 */
	const FILTER_ORDER_PAYABLE = 'bookingpress_payments_order_payable';

	/**
	 * Fired whenever a sealed amount is replaced via `AmountResolver::reseal()`.
	 *
	 * Action signature: `(Money $sealed, Money $updated, string $reason, array $context)`
	 */
	const ACTION_AMOUNT_RESEALED = 'bookingpress_payments_amount_resealed';

	// --- Method presentation ----------------------------------------------

	/**
	 * Final say over which gateways are offered for a given context.
	 *
	 * Runs AFTER each gateway's own `is_enabled()` / `supports()` check, so it
	 * is the seam for policy rather than capability: per-service gateway
	 * restrictions, Multi-Language label overrides, role-based rules.
	 *
	 * Filter signature: `(array $descriptors, string $context_id, array $context): array`
	 */
	const FILTER_AVAILABLE_METHODS = 'bookingpress_payments_available_methods';

	// --- Lifecycle --------------------------------------------------------

	/**
	 * After a gateway has produced its prepare result, before it reaches the
	 * browser. Seam for Conversion Tracking and analytics add-ons.
	 *
	 * Filter signature: `(PrepareResult $result, PaymentRequest $request): PrepareResult`
	 */
	const FILTER_PREPARE_RESULT = 'bookingpress_payments_prepare_result';

	/**
	 * After a gateway has verified a payment, before the context finalizes it.
	 *
	 * Filter signature: `(PaymentResult $result, PaymentRequest $request): PaymentResult`
	 */
	const FILTER_PAYMENT_RESULT = 'bookingpress_payments_payment_result';

	/**
	 * Fired after a context has successfully finalized a paid reference.
	 *
	 * Action signature: `(PaymentReference $reference, PaymentResult $result, array $envelope)`
	 */
	const ACTION_PAYMENT_COMPLETED = 'bookingpress_payments_completed';

	/**
	 * Fired when a payment attempt fails at any stage.
	 *
	 * Action signature: `(string $stage, \Throwable $error, array $context)`
	 */
	const ACTION_PAYMENT_FAILED = 'bookingpress_payments_failed';

	/**
	 * Which context owns a staged entry, and under what reference.
	 *
	 * A gateway route that only knows an `entry_id` — the PayPal endpoints are
	 * the case — cannot tell whether that entry is a booking being taken or a
	 * balance being settled. Those are different contexts with different
	 * reference identities: the booking form is addressed by entry id and its
	 * entry token, Complete Payment by appointment id and the pay-page token.
	 *
	 * Rather than teach Lite what a Complete Payment is, it asks. The default
	 * answer is the booking form, so a context that claims nothing changes
	 * nothing.
	 *
	 * Returning a target for an entry you do not own will route somebody else's
	 * payment into your context. Claim only what is unambiguously yours.
	 *
	 * A caller that already knows the correlation key passes it as the fourth
	 * argument — a gateway reporting which purchase was paid is a better source
	 * than an entry id, and for a context whose reference is NOT an entry id it
	 * is the only correct one. Honour it when present.
	 *
	 * Filter signature: `(array $target, int $entry_id, string $entry_token, string $correlation_key): array`
	 * where `$target` is `['context_id' => string, 'reference_id' => string, 'token' => string]`.
	 */
	const FILTER_PAYMENT_TARGET = 'bookingpress_payments_resolve_target';

	/**
	 * Attach extra, SERVER-SIDE-ONLY data to a sealed PaymentReference.
	 *
	 * Some gateways need facts about the purchase that the reference does not
	 * carry by default — Mollie and Klarna want a billing address, assembled
	 * from whichever custom form fields the merchant mapped to it. The gateway
	 * itself must not go and fetch that (interface rule 4: "NEVER reference
	 * SubmissionService, EntryRepository, or any context internals"), so the
	 * add-on's FEATURE class contributes it here and the gateway reads it back
	 * with `$reference->meta( ... )`.
	 *
	 * Metadata is not part of `PaymentReference::to_client_array()`, so nothing
	 * added here reaches the browser. It does reach whatever the gateway sends
	 * onward, so add only what that gateway genuinely needs.
	 *
	 * @param array  $metadata   Accumulated metadata.
	 * @param array  $entry      The staged entry row.
	 * @param string $context_id Which context is sealing.
	 */
	const FILTER_REFERENCE_METADATA = 'bookingpress_payments_reference_metadata';
}

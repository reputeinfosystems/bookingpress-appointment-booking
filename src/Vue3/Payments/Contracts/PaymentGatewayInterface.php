<?php
/**
 * PaymentGatewayInterface — what a payment gateway add-on implements.
 *
 * THE WHOLE POINT
 * ---------------
 * Nothing in this interface mentions a booking, an appointment, an entry, a
 * gift card, a package, a form, or a REST route. A gateway moves money for an
 * opaque {@see \BookingPress\Vue3\Payments\PaymentReference} and reports what
 * happened. That is all.
 *
 * Because of that, adding a new thing to sell — or a new add-on that changes
 * the price of an existing thing — cannot require a gateway release. The
 * gateway has no surface that either of those could break.
 *
 * WHAT THIS REPLACES, PER GATEWAY
 * -------------------------------
 * Look at `bookingpress-stripe/src/Vue3/StripeFeature.php` (802 lines). Of it,
 * roughly 600 lines are not Stripe: nonce gating, route registration (twice —
 * once per form), entry lookup, amount re-derivation, currency minor-unit
 * maths, `finalize_booking()` orchestration, envelope filtering, error-code
 * mapping, idempotency. Multiply by the ~20 gateways in the catalogue. All of
 * it moves to {@see \BookingPress\Vue3\Payments\PaymentOrchestrator}, once.
 * What is left in a gateway is the part that is genuinely about that gateway.
 *
 * IMPLEMENTATION RULES
 * --------------------
 *   1. NEVER compute an amount. Charge `$request->get_amount()` exactly.
 *   2. NEVER trust `$request->untrusted()` for money or identity. Use it only
 *      to locate a remote object, then re-fetch that object from your API.
 *   3. NEVER call `register_rest_route()`. Core owns the endpoints.
 *   4. NEVER reference `SubmissionService`, `EntryRepository`, or any context
 *      internals. If you feel you need to, the contract is missing something —
 *      raise it, do not reach through.
 *   5. Write `$reference->get_correlation_key()` into your gateway's own
 *      metadata so an out-of-band webhook can be routed home.
 *
 * @package BookingPress\Vue3\Payments\Contracts
 */

namespace BookingPress\Vue3\Payments\Contracts;

use BookingPress\Vue3\Payments\PaymentRequest;
use BookingPress\Vue3\Payments\PaymentResult;
use BookingPress\Vue3\Payments\PrepareResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface PaymentGatewayInterface {

	/**
	 * Stable machine id — `stripe`, `paypal`, `razorpay`, `on-site`.
	 *
	 * Must match the value historically stored in
	 * `bookingpress_payment_gateway` so existing rows, reports and exports keep
	 * resolving after migration.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * API contract version this gateway was built against.
	 *
	 * Return {@see \BookingPress\Vue3\Payments\Capabilities::API_VERSION} as of
	 * your build. The registry refuses to load a gateway whose MAJOR it does
	 * not understand, rather than letting it half-work.
	 *
	 * @return string
	 */
	public function get_api_version();

	/**
	 * Capabilities this gateway can honour.
	 *
	 * Must include `Capabilities::SEALED_AMOUNT` plus at least one flow shape
	 * (INLINE_FIELDS / POPUP / REDIRECT). A context whose requirements are not
	 * met simply will not offer this gateway — no fatal, no mischarge.
	 *
	 * @return string[]
	 */
	public function get_capabilities();

	/**
	 * Whether the merchant has configured and switched this gateway on.
	 *
	 * Credentials only. Do NOT branch on which form is asking — that is what
	 * capabilities are for.
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * How this gateway presents itself in the payment picker.
	 *
	 * Replaces every gateway's `Hooks::FILTER_PAYMENT_METHODS` callback AND its
	 * `FILTER_INITIAL_STATE` / `FILTER_PRO_INITIAL_STATE` pair — core injects
	 * this into whichever form is rendering, so a new form needs no gateway
	 * change.
	 *
	 * @param string $context_id Which context is asking (`booking_form`,
	 *                           `complete_payment`, `gift_card`, `package`).
	 *                           Provided for labelling only — e.g. "Pay deposit
	 *                           with card". Do not gate availability on it.
	 *
	 * @return array Keys: `id`, `label`, `icon`, `description`,
	 *               `client` (public config for this gateway's JS — publishable
	 *               keys and the like; NEVER secrets), `assets` (registered
	 *               script/style handles for core to enqueue).
	 */
	public function get_descriptor( $context_id );

	/**
	 * Begin a payment for a sealed reference.
	 *
	 * Charge `$request->get_amount()`. Do not recompute it, do not round it, do
	 * not apply your own fees to it.
	 *
	 * @param PaymentRequest $request
	 *
	 * @return PrepareResult
	 *
	 * @throws \RuntimeException On misconfiguration or a gateway API failure.
	 *                           The orchestrator maps this to a client error.
	 */
	public function prepare( PaymentRequest $request );

	/**
	 * Verify a payment the customer has completed, server-side.
	 *
	 * Re-fetch the transaction from your own API. Report only what that
	 * response says. The orchestrator independently checks the returned amount
	 * against the sealed amount before anything is finalized, so an
	 * optimistic answer here will be caught — but do not rely on that.
	 *
	 * @param PaymentRequest $request Carries the reference plus the client payload.
	 *
	 * @return PaymentResult
	 *
	 * @throws \RuntimeException When verification cannot be performed at all.
	 */
	public function confirm( PaymentRequest $request );

	/**
	 * Recover the authoritative correlation key from a client payload, when the
	 * GATEWAY is the source of truth for which purchase this is.
	 *
	 * PayPal is the motivating case: on `onApprove` the browser sends a capture
	 * body, and the trustworthy identifier is `purchase_units[0].reference_id`
	 * read back from a server-side re-fetch of the order — NOT whatever the
	 * browser claims the reference is. A gateway that works this way returns the
	 * key here and core uses it in place of the client-supplied reference.
	 *
	 * Return null when the client-supplied reference is authoritative (the
	 * normal case — Stripe, Square, on-site). Core then falls back to it, still
	 * guarded by the context's single-use token.
	 *
	 * Implementations that re-fetch a remote object here SHOULD memoize it for
	 * the request, so the subsequent `confirm()` does not pay for a second
	 * round trip.
	 *
	 * @param array $client_payload UNTRUSTED.
	 *
	 * @return string|null A `context:reference` correlation key, or null.
	 */
	public function resolve_reference_key( array $client_payload );

	/**
	 * Translate a server-to-server callback into a result.
	 *
	 * Authenticate FIRST (signature check, or re-post as PayPal IPN does), then
	 * read `PaymentReference::parse_correlation_key()` out of your metadata to
	 * identify what was paid. Return null for callbacks that are authentic but
	 * irrelevant (unrelated event types) — the orchestrator will 200 them
	 * without acting, which is what every gateway wants.
	 *
	 * Core handles idempotency, so a duplicate callback is safe.
	 *
	 * @param array  $payload Raw callback body.
	 * @param array  $headers Raw headers — signatures live here.
	 * @param string $raw_body Unparsed body, for signature verification.
	 *
	 * @return array|null Keys: `correlation_key` (string), `result`
	 *                    (PaymentResult). Null when not actionable.
	 */
	public function handle_webhook( array $payload, array $headers, $raw_body );

	/**
	 * Whether this gateway needs the public webhook route mounted for it.
	 *
	 * Gateways migrating from a legacy listener URL should return true here AND
	 * keep their old listener alive as an alias — merchant dashboards point at
	 * the old URL and those cannot be rewritten remotely.
	 *
	 * @return bool
	 */
	public function needs_webhook();
}

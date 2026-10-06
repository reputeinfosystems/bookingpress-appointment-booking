<?php
/**
 * PaymentContextInterface — what a PAYABLE THING implements.
 *
 * A context is "something a customer can pay for". Four exist today:
 *
 *   booking_form      Lite   — new appointment via [bookingpress_form]
 *   complete_payment  Pro    — settle the balance on an existing appointment
 *   gift_card         Add-on — buy a gift card
 *   package           Add-on — buy a package
 *
 * A context owns the three things gateways must never touch:
 *
 *   1. STAGING — turning a submission into a persisted, server-authoritative
 *      record, and running the amount pipeline exactly once via
 *      {@see \BookingPress\Vue3\Payments\AmountResolver}. All the amount-moving
 *      add-ons (Tax, Tip, Coupon, Deposit, Gift Card, Discounts, MyCRED,
 *      ARMember, Happy Hours, Cart, Recurring, Multi-Service, Waiting List...)
 *      participate HERE, inside the context, before any gateway exists.
 *
 *   2. RESUMING — rebuilding that record later from storage alone, for a
 *      webhook or a return URL arriving with no session.
 *
 *   3. FINALIZING — materialising the purchase once payment is verified.
 *      `booking_form` calls `SubmissionService::finalize_booking()`;
 *      `gift_card` will issue a gift card instead. This is the ONLY place the
 *      four contexts genuinely differ, and gateways never see it.
 *
 * ADDING A CONTEXT SHIPS NO GATEWAY. That is the acceptance test for this
 * design: when Gift Card and Package finish their Vue 3 conversion, they each
 * implement this interface and register it — and Stripe, PayPal, Razorpay,
 * Mollie and the rest keep working untouched.
 *
 * @package BookingPress\Vue3\Payments\Contracts
 */

namespace BookingPress\Vue3\Payments\Contracts;

use BookingPress\Vue3\Payments\PaymentReference;
use BookingPress\Vue3\Payments\PaymentResult;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface PaymentContextInterface {

	/**
	 * Stable machine id — `booking_form`, `complete_payment`, `gift_card`,
	 * `package`. Forms the first half of a correlation key.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Capabilities a gateway must declare to be offered in this context.
	 *
	 * Cart / Recurring make the booking form require `SHARED_ORDER`; Deposit
	 * makes it require `PARTIAL_PAYMENT`. A gateway lacking one is hidden here
	 * but remains available everywhere it does work — which is how a new core
	 * capability rolls out without a coordinated release of 20 add-ons.
	 *
	 * @return string[] See {@see \BookingPress\Vue3\Payments\Capabilities}.
	 */
	public function get_required_capabilities();

	/**
	 * Validate and persist a submission, seal the amount, return the reference.
	 *
	 * Implementations MUST:
	 *   - re-run every readiness/validation gate server-side;
	 *   - expand line items (`Hooks::FILTER_SUBMIT_LINE_ITEMS`) so Cart and
	 *     Recurring collapse to one charge;
	 *   - call `AmountResolver::resolve()` EXACTLY ONCE and seal the result
	 *     into the reference;
	 *   - persist that sealed amount so `resume()` can recover it without
	 *     re-running any filter;
	 *   - issue a single-use token proving this caller staged this reference.
	 *
	 * Implementations MUST NOT trust any client-supplied price. Compare against
	 * the client's hint for anti-tamper if you like, but charge the sealed value.
	 *
	 * @param array $payload Submitted form data.
	 *
	 * @return PaymentReference
	 *
	 * @throws \BookingPress\Vue3\Exceptions\ReadinessFailedException When a gate fails.
	 * @throws \RuntimeException On persistence failure.
	 */
	public function stage( array $payload );

	/**
	 * Rebuild a reference from storage — no session, no request context.
	 *
	 * This runs for webhooks and return URLs, possibly minutes later, possibly
	 * on a different request from a different IP. It MUST read the sealed
	 * amount from storage and MUST NOT re-run the pricing pipeline: the
	 * add-ons that shaped that amount may not see the same state now, and
	 * re-deriving is how a customer gets charged a different number than they
	 * agreed to.
	 *
	 * @param string $reference_id
	 *
	 * @return PaymentReference|null Null when unknown or already consumed.
	 */
	public function resume( $reference_id );

	/**
	 * Verify a token issued by `stage()` for this reference.
	 *
	 * Generalises `EntryRepository::verify_paypal_entry_token()`, which is
	 * PayPal-named for historical reasons but is really the generic
	 * proof-of-possession guard. Verify BEFORE reading the reference, so a
	 * sequential id cannot be used as an existence or data oracle.
	 *
	 * @param string $reference_id
	 * @param string $token
	 *
	 * @return bool
	 */
	public function verify_token( $reference_id, $token );

	/**
	 * Materialise the purchase after verified payment.
	 *
	 * The orchestrator has already: verified the gateway's result server-side,
	 * checked the paid amount against the sealed amount, and applied
	 * idempotency. Implementations should still be internally idempotent —
	 * a webhook and a return URL can land concurrently.
	 *
	 * @param PaymentReference $reference
	 * @param PaymentResult    $result
	 *
	 * @return array Response envelope — `variant`, `is_redirect`,
	 *               `redirect_data`. Matching the existing submit envelope keeps
	 *               the Vue 3 client unchanged.
	 *
	 * @throws \RuntimeException On unrecoverable failure after money moved.
	 *                           The orchestrator logs, alerts, and never
	 *                           reports success.
	 */
	public function finalize( PaymentReference $reference, PaymentResult $result );

	/**
	 * Release a staged-but-unpaid reference: cancelled, declined, abandoned.
	 *
	 * Free whatever `stage()` reserved — the slot, the capacity, a gift card
	 * usage lock — so an abandoned checkout does not hold inventory.
	 *
	 * @param PaymentReference $reference
	 * @param PaymentResult    $result Carries status and customer-safe message.
	 *
	 * @return void
	 */
	public function abandon( PaymentReference $reference, PaymentResult $result );

	/**
	 * Where the browser returns to after leaving for a hosted page.
	 *
	 * @param PaymentReference $reference
	 *
	 * @return array Keys `return_url` and `cancel_url`.
	 */
	public function get_return_urls( PaymentReference $reference );
}

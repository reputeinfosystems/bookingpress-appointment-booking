<?php
/**
 * SteppedPaymentGatewayInterface — for gateways that need a server round trip
 * in the MIDDLE of the payment, not just at the ends.
 *
 * WHY THIS EXISTS
 * ---------------
 * {@see PaymentGatewayInterface} assumes a payment has exactly two server
 * moments: `prepare()` before the customer acts, and `confirm()` after. That
 * holds for Stripe, PayPal, Square, Razorpay, Braintree, Klarna and every
 * redirect gateway — sixteen of seventeen ported.
 *
 * ECPay does not fit it. Its flow is:
 *
 *   1. server  — create a session, get a token          (prepare)
 *   2. browser — the SDK renders, the customer picks a method, returns a
 *                `pay_token`
 *   3. SERVER  — CreatePayment with that pay_token, which answers with a URL
 *                the customer must be sent to                   <-- no home
 *   4. browser — 3DS / bank page
 *   5. server  — the webhook settles
 *
 * Step 3 is a server call whose result is another CLIENT instruction, and the
 * four `PrepareResult` handshakes had nowhere to put it. The tempting bodges
 * are both wrong:
 *
 *   - Doing it in `confirm()` means returning a `PaymentResult`, and the only
 *     status that lets the flow continue is PENDING — which `is_finalizable()`
 *     treats as bookable. That books the appointment BEFORE the customer has
 *     paid.
 *   - Calling `prepare()` a second time re-runs `stage()`, which inserts a
 *     second entry row for one booking.
 *
 * So the contract grows one optional method instead. A gateway that does not
 * implement this interface is unaffected, and core only offers the route to
 * gateways that do.
 *
 * WHAT A STEP MAY AND MAY NOT DO
 * ------------------------------
 *   - It MAY call its own API and return any {@see PrepareResult} shape —
 *     typically REDIRECT or another CLIENT_ACTION.
 *   - It MUST NOT settle anything. A step never finalizes a booking; only
 *     `confirm()` and `webhook()` do. If a step discovers the payment is
 *     already complete, it returns CLIENT_ACTION and lets the client call
 *     `confirm()`, which verifies properly.
 *   - It MUST NOT recompute the amount, exactly as `prepare()` must not. The
 *     same sealed `PaymentRequest` is handed to it.
 *
 * Core verifies the context's single-use token before a step runs, so a step
 * is no more reachable than a confirm.
 *
 * @package BookingPress\Vue3\Payments\Contracts
 */

namespace BookingPress\Vue3\Payments\Contracts;

use BookingPress\Vue3\Payments\PaymentRequest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface SteppedPaymentGatewayInterface {

	/**
	 * Continue a payment that is already under way.
	 *
	 * Called by `POST /payment-v3/step` against the SAME sealed reference the
	 * prepare leg created — nothing is re-staged and nothing is re-priced.
	 *
	 * @param PaymentRequest $request Carries the sealed reference plus the
	 *                                client payload from this step.
	 *
	 * @return \BookingPress\Vue3\Payments\PrepareResult The next client
	 *                                                   instruction.
	 *
	 * @throws \RuntimeException On a gateway API failure. The orchestrator maps
	 *                           this to a client error.
	 */
	public function step( PaymentRequest $request );
}

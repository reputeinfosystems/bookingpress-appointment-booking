<?php
/**
 * AmountResolver — the ONE place the charged amount is computed.
 *
 * WHY A DEDICATED CLASS
 * ---------------------
 * The amount is the most heavily extended value in the product. These add-ons
 * already move it, and the list is explicitly open-ended:
 *
 *   ORDER TOTAL  (Hooks::FILTER_SUMMARY_TOTAL, priority order matters)
 *     Staff@10, HappyHours@12, CustomServiceDuration@13, MultipleQuantity@15,
 *     ServiceExtras@20, MultiService@25, Coupon@30, Package@40,
 *     Location, MultiStaff, MyCRED, ARMember, Advance/Offer Discount
 *
 *   AMOUNT DUE NOW (Hooks::FILTER_PAYABLE_AMOUNT, priority order matters)
 *     Deposit@10, Discount@11, MultiService@11 (neutralise), Tax@20,
 *     Package@20, Tip, GiftCard@25, WaitingList@30
 *
 * Every one of those is a WordPress filter callback. Filters are ordered,
 * re-entrant and side-effect prone: running the pipeline twice with slightly
 * different context is how you get "the summary said 90, Stripe charged 100".
 * Historically each gateway ran its own variant of this at intent-creation
 * time. This class exists so that can never happen again.
 *
 * CONTRACT
 * --------
 *   1. `resolve()` runs both pipelines exactly once and returns a sealed
 *      {@see Money}. It is called by a CONTEXT at stage time — never by a
 *      gateway, never by the orchestrator, never at confirm time.
 *   2. The sealed value is persisted by the context alongside its reference
 *      (for the booking form that is the stage-1 entries row) so a later leg
 *      — webhook, return URL, resumed redirect — reads storage instead of
 *      re-running filters against a request context that no longer exists.
 *   3. `reseal()` is the only sanctioned way to change a sealed amount. It is
 *      deliberately noisy: it demands a reason and fires an audit action. A
 *      gateway calling it is a bug, and reviewable as one.
 *
 * MULTI-ITEM ORDERS
 * -----------------
 * Cart / Recurring / Multi-Service expand one submit into N line items via
 * `FILTER_SUBMIT_LINE_ITEMS`, then collapse to one charge via
 * `FILTER_SUBMIT_PAYMENT_GROUP`. `resolve()` mirrors
 * `SubmissionService::compute_order_amounts()` exactly — including the
 * `item_index` / `item_count` / `is_order` context keys those add-ons branch
 * on — so behaviour is identical to the current pipeline.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

use BookingPress\Vue3\Contracts\PricingServiceInterface;
use BookingPress\Vue3\Hooks;
use BookingPress\Vue3\Payments\Exceptions\PaymentAmountMismatchException;
use BookingPress\Vue3\Services\ServiceLocator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AmountResolver {

	/**
	 * Run the full pricing pipeline for a set of line items and seal the result.
	 *
	 * @param array  $line_items Expanded line items (post `FILTER_SUBMIT_LINE_ITEMS`).
	 *                           A single-item order is just `array( $payload )`.
	 * @param string $currency   ISO currency for the order.
	 * @param array  $context    Extra context merged into BOTH filter payloads.
	 *                           Must carry `context_id` so an add-on can branch
	 *                           per form (booking_form / complete_payment /
	 *                           gift_card / package) without the gateway knowing.
	 *
	 * @return array Keys: `payable` (Money, SEALED — charge this), `order_total`
	 *               (Money, display only), `items` (per-item full/payable floats).
	 */
	public static function resolve( array $line_items, $currency, array $context = array() ) {
		$pricing = ServiceLocator::get( PricingServiceInterface::class );

		$item_count    = count( $line_items );
		$full_total    = 0.0;
		$payable_total = 0.0;
		$items         = array();

		foreach ( $line_items as $item_index => $item ) {
			$item       = is_array( $item ) ? $item : array();
			$service_id = isset( $item['selected_service'] ) ? (int) $item['selected_service'] : 0;

			// --- Pipeline 1: ORDER TOTAL -------------------------------------
			// PricingService::compute_total() fires FILTER_SUMMARY_TOTAL, which
			// is where Staff / Happy Hours / Extras / Quantity / Coupon /
			// Package / MyCRED / ARMember / Discount / Location all live.
			$full = (float) $pricing->compute_total(
				array(
					'service_id' => $service_id,
					'form_data'  => $item,
				)
			);

			// --- Pipeline 2: AMOUNT DUE NOW ----------------------------------
			// Deposit / Tax / Tip / Gift Card / Waiting List / Package.
			// Context keys are byte-identical to
			// SubmissionService::compute_order_amounts() so every existing
			// callback behaves exactly as it does today.
			$filter_context = array_merge(
				$context,
				array(
					'service_id' => $service_id,
					'form_data'  => $item,
					'item_index' => (int) $item_index,
					'item_count' => $item_count,
					'is_order'   => $item_count > 1,
				)
			);

			$payable = (float) apply_filters( Hooks::FILTER_PAYABLE_AMOUNT, $full, $filter_context );

			$full_total    += $full;
			$payable_total += $payable;

			$items[] = array(
				'full'    => $full,
				'payable' => $payable,
			);
		}

		/**
		 * Final order-level adjustment, AFTER per-item resolution.
		 *
		 * This is the seam for an add-on whose effect is not decomposable per
		 * item — an order-wide gift card balance, a cart-level coupon, a
		 * minimum-charge rule. Runs once per order regardless of item count.
		 *
		 * @param float $payable_total
		 * @param float $full_total
		 * @param array $context
		 */
		$payable_total = (float) apply_filters(
			PaymentHooks::FILTER_ORDER_PAYABLE,
			$payable_total,
			$full_total,
			array_merge( $context, array( 'items' => $items ) )
		);

		// A negative charge is always a bug in a callback, never a refund.
		// Clamp rather than let a gateway receive it.
		if ( $payable_total < 0.0 ) {
			$payable_total = 0.0;
		}

		// Seal at the currency's minor unit. A filter chain can easily land on an
		// amount the currency cannot express (a percentage discount, a tax rate,
		// a split deposit), and such a value is not chargeable, refundable or
		// reconcilable. See Money::quantized().
		return array(
			'payable'     => ( new Money( $payable_total, $currency ) )->quantized(),
			'order_total' => ( new Money( $full_total, $currency ) )->quantized(),
			'items'       => $items,
		);
	}

	/**
	 * Refuse a sealed amount that exceeds the ceiling a context says it has.
	 *
	 * **Opt-in, and deliberately NOT applied inside `resolve()`.** There is no
	 * universal ceiling: on the booking form the payable is allowed to exceed
	 * the order total, because `FILTER_PAYABLE_AMOUNT` carries Tax and Tip and
	 * both legitimately add to it. Asserting `payable <= order_total` globally
	 * would be correct only until Tax lands in the Vue 3 pipeline, and would
	 * then fatal the booking form — the worst kind of guard, one that holds
	 * right up to the day someone adds the feature it was never told about.
	 *
	 * A context that DOES have a hard ceiling calls this. Complete Payment is
	 * the first: it collects a balance read from storage, and the only things
	 * allowed to touch that number reduce it (Coupon, Gift Card). Anything
	 * that raises it is a callback that did not recognise the `context_id` and
	 * ran anyway — Deposit re-applying itself to a balance, say — and the
	 * consequence is overcharging a customer for money they already paid.
	 *
	 * This is the structural half of **D28**. The conventions there (pass
	 * `context_id`, opt in by context, scope Deposit to the booking form) are
	 * what SHOULD keep a stray callback out; this is what happens when one of
	 * them is forgotten. Conventions decay; the assertion does not.
	 *
	 * Throws rather than clamps. Clamping would charge the ceiling, which is
	 * plausible enough to go unnoticed for a long time — and a pricing
	 * callback firing in a context it knows nothing about is a bug that should
	 * be found, not absorbed.
	 *
	 * @param Money  $sealed  The amount the pipeline produced.
	 * @param Money  $ceiling The most this context may ever charge.
	 * @param array  $context Correlation data for the message (`context_id`,
	 *                        `reference_id`).
	 *
	 * @return Money `$sealed`, unchanged, when it is within bounds.
	 *
	 * @throws \InvalidArgumentException  When the currencies differ.
	 * @throws PaymentAmountMismatchException When the ceiling is exceeded.
	 */
	public static function assert_within( Money $sealed, Money $ceiling, array $context = array() ) {
		if ( $sealed->get_currency() !== $ceiling->get_currency() ) {
			throw new \InvalidArgumentException(
				sprintf(
					'AmountResolver::assert_within() compared %s against %s.',
					$sealed->get_currency(),
					$ceiling->get_currency()
				)
			);
		}

		// Compared in minor units, like every other amount comparison in this
		// layer, so float noise from a long filter chain cannot trip it.
		if ( $sealed->to_minor() <= $ceiling->to_minor() ) {
			return $sealed;
		}

		$correlation = '';
		if ( isset( $context['context_id'] ) ) {
			$correlation = (string) $context['context_id'];
			if ( isset( $context['reference_id'] ) ) {
				$correlation .= ':' . (string) $context['reference_id'];
			}
		}

		throw new PaymentAmountMismatchException( $ceiling, $sealed, $correlation );
	}

	/**
	 * Deliberately awkward escape hatch for the rare case where a context must
	 * change an already-sealed amount — e.g. Complete Payment recomputing the
	 * remaining balance because a coupon was applied on the pay page after the
	 * entry was staged.
	 *
	 * Only a {@see Contracts\PaymentContextInterface} implementation may call
	 * this. It fires an audit action so a reseal is always traceable in the
	 * debug log next to the charge it produced.
	 *
	 * @param Money  $sealed  The currently sealed amount.
	 * @param Money  $updated The replacement.
	 * @param string $reason  Non-empty, human-readable, logged.
	 * @param array  $context Correlation data (context_id, reference_id).
	 *
	 * @return Money
	 *
	 * @throws \InvalidArgumentException When no reason is supplied, or the
	 *                                   currency would change.
	 */
	public static function reseal( Money $sealed, Money $updated, $reason, array $context = array() ) {
		$reason = trim( (string) $reason );
		if ( '' === $reason ) {
			throw new \InvalidArgumentException( 'AmountResolver::reseal() requires an explicit reason.' );
		}
		if ( $sealed->get_currency() !== $updated->get_currency() ) {
			throw new \InvalidArgumentException( 'AmountResolver::reseal() must not change currency.' );
		}

		/**
		 * Audit trail for a resealed amount.
		 *
		 * @param Money  $sealed
		 * @param Money  $updated
		 * @param string $reason
		 * @param array  $context
		 */
		do_action( PaymentHooks::ACTION_AMOUNT_RESEALED, $sealed, $updated, $reason, $context );

		return $updated;
	}
}

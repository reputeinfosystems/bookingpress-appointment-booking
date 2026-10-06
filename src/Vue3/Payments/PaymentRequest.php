<?php
/**
 * PaymentRequest — everything a gateway is allowed to see.
 *
 * Deliberately narrow. A gateway receives a sealed {@see PaymentReference},
 * the URLs to come back to, and whatever its own client SDK sent up. It does
 * NOT receive: the submit payload, the entries row, the service, the customer
 * record, the pricing filters, or the context object itself.
 *
 * That narrowness is what keeps gateways stable. `StripeFeature::rest_intent()`
 * currently reaches for `EntryRepository::find()`, reads
 * `bookingpress_service_price` off the row, then calls
 * `SubmissionService::complete_payment_payable_for_entry()` to second-guess it.
 * Three couplings to core internals, in one gateway, for one number that core
 * had already computed. None of that is reachable from here.
 *
 * @package BookingPress\Vue3\Payments
 */

namespace BookingPress\Vue3\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PaymentRequest {

	/** @var PaymentReference */
	private $reference;

	/** @var string Where the gateway sends the browser after success. */
	private $return_url;

	/** @var string Where the gateway sends the browser when the customer backs out. */
	private $cancel_url;

	/**
	 * Where the gateway sends the browser after a DECLINED payment.
	 *
	 * Defaults to the cancel URL, because today both come from the same
	 * `after_failed_payment_redirection` setting and no context is required to
	 * invent a second one. Split out so a gateway that genuinely distinguishes
	 * "I changed my mind" from "my card was declined" can. Stripe Checkout does
	 * not — a decline keeps the customer on Stripe's page to retry — so Stripe
	 * will never read this.
	 *
	 * @var string
	 */
	private $failure_url;

	/** @var string Server-to-server callback URL for this gateway. */
	private $webhook_url;

	/** @var array Untrusted client payload (SDK response, nonce, element id...). */
	private $client_payload;

	/** @var string Locale for the gateway's hosted UI, e.g. `en_US`. */
	private $locale;

	/**
	 * @param array $args reference, return_url, cancel_url, failure_url,
	 *                    webhook_url, client_payload, locale.
	 *
	 *                    `failure_url` is optional and falls back to
	 *                    `cancel_url`. There is deliberately no
	 *                    `idempotency_key` argument — see
	 *                    {@see self::get_idempotency_key()}.
	 */
	public function __construct( array $args ) {
		$this->reference = isset( $args['reference'] ) && $args['reference'] instanceof PaymentReference
			? $args['reference']
			: null;

		$this->return_url  = isset( $args['return_url'] ) ? (string) $args['return_url'] : '';
		$this->cancel_url  = isset( $args['cancel_url'] ) ? (string) $args['cancel_url'] : '';
		$this->webhook_url = isset( $args['webhook_url'] ) ? (string) $args['webhook_url'] : '';

		// Fall back rather than default to empty: a gateway reading an empty
		// failure URL would send a declined customer nowhere.
		$this->failure_url = ( isset( $args['failure_url'] ) && '' !== (string) $args['failure_url'] )
			? (string) $args['failure_url']
			: $this->cancel_url;

		$this->locale         = isset( $args['locale'] ) ? (string) $args['locale'] : '';
		$this->client_payload = isset( $args['client_payload'] ) && is_array( $args['client_payload'] )
			? $args['client_payload']
			: array();
	}

	/** @return PaymentReference */
	public function get_reference() {
		return $this->reference;
	}

	/**
	 * Shortcut to the sealed amount — the number to charge.
	 *
	 * @return Money
	 */
	public function get_amount() {
		return $this->reference->get_amount();
	}

	/** @return string */
	public function get_return_url() {
		return $this->return_url;
	}

	/** @return string */
	public function get_cancel_url() {
		return $this->cancel_url;
	}

	/**
	 * Where to send a customer whose payment was DECLINED.
	 *
	 * Falls back to the cancel URL, so this is always safe to use.
	 *
	 * @return string
	 */
	public function get_failure_url() {
		return $this->failure_url;
	}

	/** @return string */
	public function get_webhook_url() {
		return $this->webhook_url;
	}

	/**
	 * Idempotency key for this payment attempt.
	 *
	 * WHAT IT IS FOR
	 * --------------
	 * Processors remember this key against the object it created (Stripe: 24h)
	 * and return that same object instead of creating a second one. Without it
	 * a retried `prepare()` — a double click, a stalled request the browser
	 * re-sends — leaves orphan PaymentIntents, or two live Checkout Sessions
	 * with two URLs, one of which the customer may still be holding. Square
	 * REQUIRES a key on CreatePayment; PayPal has `PayPal-Request-Id`.
	 *
	 * WHY THE AMOUNT IS IN THE HASH
	 * -----------------------------
	 * It is what makes a re-priced attempt a NEW attempt. If the key were the
	 * correlation key alone, a customer who went back and added an extra would
	 * hit the same key, and the processor would hand back the OLD, CHEAPER
	 * object. That undercharge is invisible to the seal, because the
	 * substitution happens inside the processor rather than in our code.
	 *
	 * WHY IT IS DERIVED AND NOT PASSED IN
	 * -----------------------------------
	 * A constructor argument is a thing a caller can forget, or compute
	 * differently. Deriving it from the sealed reference means the prepare leg
	 * and the confirm leg cannot disagree, and `with_client_payload()` rebuilds
	 * it identically for free.
	 *
	 * Gateways that have no such mechanism simply ignore this.
	 *
	 * @return string Empty only when there is no reference to derive from.
	 */
	public function get_idempotency_key() {
		if ( ! $this->reference instanceof PaymentReference ) {
			return '';
		}

		$amount = $this->reference->get_amount();

		return 'bp_' . hash(
			'sha256',
			$this->reference->get_correlation_key()
				. '|' . $amount->to_minor()
				. '|' . $amount->get_currency()
		);
	}

	/** @return string */
	public function get_locale() {
		return $this->locale;
	}

	/**
	 * Read one field from the UNTRUSTED client payload.
	 *
	 * Named to be uncomfortable on purpose. Anything read here came from the
	 * browser. A gateway may use it to locate a remote object (a PaymentIntent
	 * id, an order id) but must then re-fetch that object from the gateway's own
	 * API and trust only the response. Never derive an amount or a booking
	 * identity from this.
	 *
	 * @param string $key
	 * @param mixed  $default_value
	 *
	 * @return mixed
	 */
	public function untrusted( $key, $default_value = null ) {
		return array_key_exists( $key, $this->client_payload ) ? $this->client_payload[ $key ] : $default_value;
	}

	/** @return array */
	public function get_client_payload() {
		return $this->client_payload;
	}

	/**
	 * Derive a copy of this request with a different client payload — used by
	 * the orchestrator when moving from the prepare leg to the confirm leg
	 * without re-staging.
	 *
	 * @param array $client_payload
	 *
	 * @return self
	 */
	public function with_client_payload( array $client_payload ) {
		return new self(
			array(
				'reference'      => $this->reference,
				'return_url'     => $this->return_url,
				'cancel_url'     => $this->cancel_url,
				'failure_url'    => $this->failure_url,
				'webhook_url'    => $this->webhook_url,
				'locale'         => $this->locale,
				'client_payload' => $client_payload,
			)
		);
	}
}

<?php

namespace EasyCommerce\Controllers\Payment\Stripe\API;

use EasyCommerce\Models\Order;
use EasyCommerce\Models\Order_Meta;
use EasyCommerce\Traits\Rest;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook as StripeWebhook;
use WP_Error;
use WP_REST_Request;

class Webhook {

	use Rest;

	/**
	 * Order-meta keys that bind a Stripe payment intent to an order.
	 *
	 * `stripe_payment_intent_id` is written by the synchronous checkout flow
	 * (Stripe.php); `_ec_pending_stripe_intent_id` is written by the order-pay
	 * flow (Payment_Intent.php). Either can resolve an inbound webhook to its order.
	 *
	 * @var string[]
	 */
	private const INTENT_ORDER_META_KEYS = array(
		'stripe_payment_intent_id',
		'_ec_pending_stripe_intent_id',
	);

	/**
	 * Prefix for the per-event dedup mark stored on the order.
	 *
	 * @var string
	 */
	private const PROCESSED_META_PREFIX = '_ec_processed_evt_';

	/**
	 * Prefix for the MySQL advisory lock that serialises same-event deliveries.
	 *
	 * @var string
	 */
	private const LOCK_PREFIX = 'ec_stripe_evt_';

	/**
	 * Seconds to wait for the advisory lock before giving up (Stripe then retries).
	 *
	 * @var int
	 */
	private const LOCK_TIMEOUT = 5;

	/**
	 * Validates the incoming request to ensure it is a legitimate webhook event from Stripe.
	 * It checks for the presence of required parameters and verifies the request signature.
	 *
	 * @see https://docs.stripe.com/webhooks?snapshot-or-thin=thin#verify-webhook-signatures-with-official-libraries
	 *
	 * @param WP_REST_Request $request The incoming REST request to validate.
	 *
	 * @return Event|WP_Error
	 */
	public function validate_request( WP_REST_Request $request ) {
		if ( ! easycommerce_stripe_is_ready() || empty( easycommerce_stripe_get_webhook_secret() ) || ! easycommerce_stripe_get_api_client() ) {
			return new WP_Error( 'invalid_request', __( 'Invalid request.', 'easycommerce' ) );
		}

		$sig_header = $request->get_header( 'stripe-signature' );

		if ( empty( $sig_header ) ) {
			return new WP_Error( 'missing_signature', __( 'Missing Stripe signature.', 'easycommerce' ) );
		}

		try {
			return StripeWebhook::constructEvent(
				$request->get_body(),
				$sig_header,
				easycommerce_stripe_get_webhook_secret()
			);
		} catch ( SignatureVerificationException $e ) {
			return new WP_Error( 'invalid_signature', $e->getMessage() );
		}
	}

	/**
	 * Listen to webhook events.
	 *
	 * @see https://docs.stripe.com/webhooks#webhook-endpoint-def
	 *
	 * @return void
	 */
	public function listen( WP_REST_Request $request ) {
		$event = $this->validate_request( $request );
		if ( is_wp_error( $event ) ) {
			$this->response_error( $event->get_error_message() );
			return;
		}

		$handled = apply_filters( 'easycommerce_stripe_webhook_handle_event', false, $event, $this );

		if ( $handled ) {
			return;
		}

		switch ( $event->type ) {
			case Event::PAYMENT_INTENT_SUCCEEDED:
			case Event::PAYMENT_INTENT_PAYMENT_FAILED:
				// process_event() fires easycommerce_stripe_webhook_received once,
				// only on a genuine first-time processing (idempotent downstreams).
				$result = $this->process_event( $event );

				if ( $result['code'] >= 400 ) {
					$this->response_error( $result['message'], $result['code'] );
				} else {
					$this->response_success( $result['message'], $result['code'] );
				}
				break;
			default:
				do_action( 'easycommerce_stripe_webhook_received_unknown', $event );
				$this->response_success( 'Webhook received' );
				break;
		}
	}

	/**
	 * Process a payment_intent.* event idempotently.
	 *
	 * Serialises concurrent deliveries of the same event on a MySQL advisory
	 * lock, then dedups on an order-meta mark so a replayed webhook cannot
	 * re-run the completion path. The event is recorded as processed only
	 * after the completion path succeeds, so a mid-processing failure lets
	 * Stripe retry cleanly.
	 *
	 * @param Event|object $event The Stripe event object.
	 *
	 * @return array{code:int,message:string,processed:bool} HTTP code + whether the completion path ran this call.
	 */
	public function process_event( $event ) {
		$order = $this->resolve_order_from_intent( $event->data->object );

		// Not order-resolvable — cannot dedup against an order (out of scope).
		// Ack so Stripe stops retrying, and surface it for observability.
		if ( ! $order ) {
			do_action( 'easycommerce_stripe_webhook_unresolved_order', $event );
			return array(
				'code'      => 200,
				'message'   => 'Webhook received (no resolvable order)',
				'processed' => false,
			);
		}

		$lock_name = $this->lock_name( $event->id );

		// Serialise concurrent deliveries of the SAME event; the check-then-write
		// below is a read-modify-write on order meta that must run under the lock.
		if ( ! $this->acquire_lock( $lock_name ) ) {
			return array(
				'code'      => 503,
				'message'   => 'Could not acquire processing lock, retry later',
				'processed' => false,
			);
		}

		$meta_key = self::PROCESSED_META_PREFIX . $event->id;
		$result   = null;

		// Critical section: keep it tight. Only the check-then-mark read-modify-write
		// (and the completion it guards) runs under the lock — no third-party I/O.
		try {
			// Already handled by a prior (or concurrent, now-completed) delivery.
			if ( $order->get_meta( $meta_key ) ) {
				$result = array(
					'code'      => 200,
					'message'   => 'Event already processed',
					'processed' => false,
				);
			} else {
				$this->dispatch_event( $event );

				// Record the dedup mark only after the completion path succeeds.
				$recorded = $order->add_meta( $meta_key, time() );
				if ( null === $recorded ) {
					// The work already ran; a lost mark means a later replay could
					// re-run it (mitigated by #3128's UNIQUE(transaction_id)). Log loudly.
					do_action(
						'easycommerce_log',
						array(
							'object'    => 'order',
							'action'    => 'stripe_webhook',
							'object_id' => $order->get_id(),
							'note'      => sprintf( 'Failed to record processed Stripe event %s', $event->id ),
							'type'      => 'error',
						)
					);
				}

				$result = array(
					'code'      => 200,
					'message'   => 'Webhook processed',
					'processed' => true,
				);
			}
		} catch ( \Throwable $e ) {
			// The completion path threw: the dedup mark is NOT written, so a
			// Stripe retry re-runs it cleanly. Return an error to trigger the retry.
			do_action(
				'easycommerce_log',
				array(
					'object'    => 'order',
					'action'    => 'stripe_webhook',
					'object_id' => $order->get_id(),
					'note'      => sprintf( 'Stripe event %s processing failed: %s', $event->id, $e->getMessage() ),
					'type'      => 'error',
				)
			);

			$result = array(
				'code'      => 500,
				'message'   => 'Processing failed, retry later',
				'processed' => false,
			);
		} finally {
			$this->release_lock( $lock_name );
		}

		// Fire the generic "received" signal exactly once, only after a genuine
		// first-time processing — never on a replay or failure. Deliberately AFTER
		// the lock is released and OUTSIDE the payment catch: the sole live consumer
		// (outbound Zapier webhooks) makes a blocking HTTP call, which must not be
		// held under the advisory lock, and a throwing downstream notify must not
		// turn an already-completed event into a retry-triggering payment failure.
		if ( ! empty( $result['processed'] ) ) {
			try {
				do_action( 'easycommerce_stripe_webhook_received', $event );
			} catch ( \Throwable $e ) {
				do_action(
					'easycommerce_log',
					array(
						'object'    => 'order',
						'action'    => 'stripe_webhook',
						'object_id' => $order->get_id(),
						'note'      => sprintf( 'Stripe event %s post-process notify failed: %s', $event->id, $e->getMessage() ),
						'type'      => 'error',
					)
				);
			}
		}

		return $result;
	}

	/**
	 * Resolve the order bound to a Stripe payment intent.
	 *
	 * Reverse-looks-up order meta on the intent id (the intent's own Stripe
	 * metadata does not carry the order id).
	 *
	 * @param object $payment_intent The Stripe payment intent object (needs ->id).
	 *
	 * @return Order|null The bound order, or null when none is resolvable.
	 */
	public function resolve_order_from_intent( $payment_intent ) {
		$intent_id = isset( $payment_intent->id ) ? (string) $payment_intent->id : '';

		if ( '' === $intent_id ) {
			return null;
		}

		$order_meta = new Order_Meta();

		foreach ( self::INTENT_ORDER_META_KEYS as $meta_key ) {
			$row = $order_meta->get_row(
				array(
					'meta_key'   => $meta_key,
					'meta_value' => $intent_id,
				)
			);

			if ( $row && ! empty( $row->order_id ) ) {
				$order = new Order( (int) $row->order_id );

				if ( $order->exists() ) {
					return $order;
				}
			}
		}

		return null;
	}

	/**
	 * Dispatch the event to its completion handler (fires the completion action).
	 *
	 * @param Event|object $event The Stripe event object.
	 *
	 * @return void
	 */
	private function dispatch_event( $event ) {
		switch ( $event->type ) {
			case Event::PAYMENT_INTENT_SUCCEEDED:
				$this->payment_intent_succeeded( $event );
				break;
			case Event::PAYMENT_INTENT_PAYMENT_FAILED:
				$this->payment_intent_failed( $event );
				break;
		}
	}

	/**
	 * Build the advisory-lock name for an event, bounded to MySQL's 64-char limit.
	 *
	 * Standard Stripe event ids keep the readable `ec_stripe_evt_{id}` form; an
	 * over-long id (which would otherwise error GET_LOCK and 503 forever) falls
	 * back to a hashed, fixed-width name.
	 *
	 * @param string $event_id The Stripe event id.
	 *
	 * @return string
	 */
	private function lock_name( $event_id ) {
		$lock_name = self::LOCK_PREFIX . $event_id;

		if ( strlen( $lock_name ) > 64 ) {
			$lock_name = self::LOCK_PREFIX . md5( $event_id );
		}

		return $lock_name;
	}

	/**
	 * Acquire a MySQL advisory lock scoped to this DB connection.
	 *
	 * @param string $lock_name The lock name.
	 * @param int    $timeout   Seconds to wait.
	 *
	 * @return bool True if the lock was acquired.
	 */
	private function acquire_lock( $lock_name, $timeout = self::LOCK_TIMEOUT ) {
		global $wpdb;

		$acquired = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $lock_name, $timeout ) );

		return '1' === (string) $acquired;
	}

	/**
	 * Release a previously acquired advisory lock.
	 *
	 * @param string $lock_name The lock name.
	 *
	 * @return void
	 */
	private function release_lock( $lock_name ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
	}

	/**
	 * Handle successful payment intent (fires the completion action).
	 *
	 * @param Event $event The event object.
	 *
	 * @return void
	 */
	public function payment_intent_succeeded( $event ) {
		$payment_intent = $event->data->object;

		do_action( 'easycommerce_stripe_payment_intent_succeeded', $payment_intent, $event );
	}

	/**
	 * Handle failed payment intent (fires the completion action).
	 *
	 * @param Event $event The event object.
	 *
	 * @return void
	 */
	public function payment_intent_failed( $event ) {
		$payment_intent = $event->data->object;

		do_action( 'easycommerce_stripe_payment_intent_failed', $payment_intent, $event );
	}
}

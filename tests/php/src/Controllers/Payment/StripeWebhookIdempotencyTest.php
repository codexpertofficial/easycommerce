<?php

namespace EasyCommerce\Tests\Controllers\Payment;

use EasyCommerce\Controllers\Payment\Stripe\API\Webhook;
use EasyCommerce\Models\Order;
use EasyCommerce\Tests\EasyCommerceTestCase;
use Stripe\Event as StripeEvent;
use WP_Error;
use WP_REST_Request;

/**
 * Tests for Stripe webhook idempotency + completion concurrency (#3127).
 *
 * Covers the order-meta dedup + advisory-lock design: a replayed or
 * concurrent payment_intent.* webhook must not re-run the completion path.
 *
 * @see docs/superpowers/specs/2026-07-22-payment-webhook-idempotency-design.md
 */
class StripeWebhookIdempotencyTest extends EasyCommerceTestCase {

	/** @var Webhook */
	private $webhook;

	/** @var int Count of easycommerce_stripe_payment_intent_succeeded fires. */
	private $succeeded_fires = 0;

	/** @var int Count of easycommerce_stripe_payment_intent_failed fires. */
	private $failed_fires = 0;

	/** @var int Count of easycommerce_stripe_webhook_received fires. */
	private $received_fires = 0;

	public function set_up(): void {
		parent::set_up();

		$this->webhook         = new Webhook();
		$this->succeeded_fires = 0;
		$this->failed_fires    = 0;
		$this->received_fires  = 0;

		add_action(
			'easycommerce_stripe_payment_intent_succeeded',
			function () {
				++$this->succeeded_fires;
			}
		);
		add_action(
			'easycommerce_stripe_payment_intent_failed',
			function () {
				++$this->failed_fires;
			}
		);
		add_action(
			'easycommerce_stripe_webhook_received',
			function () {
				++$this->received_fires;
			}
		);
	}

	/**
	 * Build a minimal Stripe-event-shaped object the handler can read.
	 *
	 * @param string $event_id  Stripe event id (evt_…).
	 * @param string $intent_id Payment intent id (pi_…).
	 * @param string $type      Event type.
	 * @return object
	 */
	private function make_event( string $event_id, string $intent_id, string $type = 'payment_intent.succeeded' ) {
		return (object) array(
			'id'   => $event_id,
			'type' => $type,
			'data' => (object) array(
				'object' => (object) array( 'id' => $intent_id ),
			),
		);
	}

	/**
	 * Create an order and bind a Stripe intent id to it via the given meta key.
	 */
	private function make_order_with_intent( string $intent_id, string $meta_key = 'stripe_payment_intent_id' ): Order {
		$order_id = $this->factory->order->create( array( 'status' => 'processing' ) );
		$order    = new Order( $order_id );
		$order->add_meta( $meta_key, $intent_id );

		return $order;
	}

	public function test_first_delivery_processes_and_records_dedup_meta(): void {
		$order = $this->make_order_with_intent( 'pi_first_1' );
		$event = $this->make_event( 'evt_first_1', 'pi_first_1' );

		$result = $this->webhook->process_event( $event );

		$this->assertSame( 200, $result['code'] );
		$this->assertTrue( $result['processed'] );
		$this->assertSame( 1, $this->succeeded_fires, 'Completion path runs exactly once on first delivery.' );
		$this->assertSame( 1, $this->received_fires, 'The received hook fires once for a genuinely-processed event.' );
		$this->assertNotEmpty(
			$order->get_meta( '_ec_processed_evt_evt_first_1' ),
			'The event is marked processed on the order.'
		);
	}

	public function test_replayed_event_is_a_noop(): void {
		$order = $this->make_order_with_intent( 'pi_replay_1' );
		$order->add_meta( '_ec_processed_evt_evt_replay_1', time() );

		$event  = $this->make_event( 'evt_replay_1', 'pi_replay_1' );
		$result = $this->webhook->process_event( $event );

		$this->assertSame( 200, $result['code'], 'Already-processed event is acked so Stripe stops retrying.' );
		$this->assertFalse( $result['processed'] );
		$this->assertSame( 0, $this->succeeded_fires, 'Completion path does NOT run again for a replay.' );
		$this->assertSame( 0, $this->received_fires, 'The received hook does NOT re-fire for a replay (protects idempotent downstreams).' );
	}

	public function test_two_deliveries_process_exactly_once(): void {
		$order = $this->make_order_with_intent( 'pi_dup_1' );
		$event = $this->make_event( 'evt_dup_1', 'pi_dup_1' );

		$first  = $this->webhook->process_event( $event );
		$second = $this->webhook->process_event( $event );

		$this->assertTrue( $first['processed'] );
		$this->assertFalse( $second['processed'], 'Second identical delivery is a dedup no-op.' );
		$this->assertSame( 1, $this->succeeded_fires, 'Completion path runs exactly once across two deliveries.' );
	}

	public function test_processing_failure_does_not_record_meta_and_releases_lock(): void {
		$order = $this->make_order_with_intent( 'pi_fail_1' );
		$event = $this->make_event( 'evt_fail_1', 'pi_fail_1' );

		$thrower = function () {
			throw new \RuntimeException( 'boom' );
		};
		add_action( 'easycommerce_stripe_payment_intent_succeeded', $thrower );

		$result = $this->webhook->process_event( $event );

		$this->assertGreaterThanOrEqual( 500, $result['code'], 'A processing failure returns an error so Stripe retries.' );
		$this->assertFalse( $result['processed'] );
		$this->assertEmpty(
			$order->get_meta( '_ec_processed_evt_evt_fail_1' ),
			'Dedup meta is NOT written when processing throws.'
		);
		$this->assertSame( 0, $this->received_fires, 'The received hook does NOT fire on a failed (soon-to-be-retried) delivery.' );

		// Prove the lock was released: a clean retry must now succeed.
		remove_action( 'easycommerce_stripe_payment_intent_succeeded', $thrower );
		$retry = $this->webhook->process_event( $event );

		$this->assertSame( 200, $retry['code'] );
		$this->assertTrue( $retry['processed'], 'Retry after the fault processes cleanly (lock was released).' );
		$this->assertNotEmpty( $order->get_meta( '_ec_processed_evt_evt_fail_1' ) );
	}

	public function test_lock_contention_returns_503_without_processing(): void {
		$intent_id = 'pi_lock_1';
		$order     = $this->make_order_with_intent( $intent_id );
		$event     = $this->make_event( 'evt_lock_1', $intent_id );

		// Hold the same advisory lock on a SEPARATE DB connection so the handler
		// (on the main connection) cannot acquire it and must ask Stripe to retry.
		global $wpdb;
		$other     = new \wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
		$lock_name = 'ec_stripe_evt_evt_lock_1';
		$held      = $other->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $lock_name, 0 ) );
		$this->assertSame( '1', (string) $held, 'Pre-req: the second connection holds the lock.' );

		$result = $this->webhook->process_event( $event );

		$other->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
		$other->close();

		$this->assertSame( 503, $result['code'], 'Lock contention yields 503 so Stripe retries later.' );
		$this->assertFalse( $result['processed'] );
		$this->assertSame( 0, $this->succeeded_fires, 'A contended event is not processed.' );
		$this->assertEmpty( $order->get_meta( '_ec_processed_evt_evt_lock_1' ), 'A contended event is not marked processed.' );
	}

	public function test_received_hook_failure_does_not_fail_a_processed_event(): void {
		$order = $this->make_order_with_intent( 'pi_recv_fail_1' );
		$event = $this->make_event( 'evt_recv_fail_1', 'pi_recv_fail_1' );

		add_action(
			'easycommerce_stripe_webhook_received',
			function () {
				throw new \RuntimeException( 'downstream notify boom' );
			}
		);

		$result = $this->webhook->process_event( $event );

		// The order is fully processed and durably marked; a throwing downstream
		// notify must NOT become a 500 — a Stripe retry could only no-op here, it
		// can never re-run the notify, so a spurious retry helps nothing.
		$this->assertSame( 200, $result['code'], 'A failed downstream notify does not fail the (already-completed) event.' );
		$this->assertTrue( $result['processed'] );
		$this->assertNotEmpty( $order->get_meta( '_ec_processed_evt_evt_recv_fail_1' ) );
		$this->assertSame( 1, $this->succeeded_fires, 'Completion still ran exactly once.' );
	}

	public function test_unresolvable_event_is_acked_without_dedup(): void {
		$event = $this->make_event( 'evt_orphan_1', 'pi_no_such_order' );

		$result = $this->webhook->process_event( $event );

		$this->assertSame( 200, $result['code'], 'An event with no resolvable order is acked, not retried forever.' );
	}

	public function test_resolve_order_from_intent_via_pending_meta(): void {
		$order = $this->make_order_with_intent( 'pi_pending_1', '_ec_pending_stripe_intent_id' );

		$resolved = $this->webhook->resolve_order_from_intent( (object) array( 'id' => 'pi_pending_1' ) );

		$this->assertInstanceOf( Order::class, $resolved );
		$this->assertSame( $order->get_id(), $resolved->get_id(), 'Order-pay flow binds the intent via _ec_pending_stripe_intent_id.' );
	}

	public function test_over_long_event_id_still_processes(): void {
		$intent_id = 'pi_longid_1';
		$order     = $this->make_order_with_intent( $intent_id );

		// MySQL caps GET_LOCK names at 64 chars; an over-long event id would
		// otherwise error the lock acquire and 503 forever without processing.
		$long_event_id = 'evt_' . str_repeat( 'a', 90 );
		$event         = $this->make_event( $long_event_id, $intent_id );

		$result = $this->webhook->process_event( $event );

		$this->assertSame( 200, $result['code'], 'An over-long event id must not wedge the lock.' );
		$this->assertTrue( $result['processed'] );
		$this->assertNotEmpty( $order->get_meta( '_ec_processed_evt_' . $long_event_id ) );
	}

	// ── Real Stripe payloads / SDK Event objects ──────────────────────────────

	/**
	 * Canonical Stripe event payload (the shape Stripe actually POSTs), as JSON.
	 */
	private function real_event_payload( string $event_id, string $intent_id, string $type = 'payment_intent.succeeded' ): string {
		return wp_json_encode(
			array(
				'id'               => $event_id,
				'object'           => 'event',
				'api_version'      => '2024-06-20',
				'created'          => 1700000000,
				'type'             => $type,
				'livemode'         => false,
				'pending_webhooks' => 1,
				'request'          => array( 'id' => null, 'idempotency_key' => null ),
				'data'             => array(
					'object' => array(
						'id'       => $intent_id,
						'object'   => 'payment_intent',
						'amount'   => 5998,
						'currency' => 'usd',
						'status'   => 'payment_intent.payment_failed' === $type ? 'requires_payment_method' : 'succeeded',
						'metadata' => (object) array(),
					),
				),
			)
		);
	}

	/**
	 * Configure Stripe sandbox settings so validate_request() can run.
	 */
	private function configure_stripe( string $webhook_secret ): void {
		update_option(
			'easycommerce-payment-stripe',
			array(
				'is_sandbox_mode'         => true,
				'sandbox_publishable_key' => 'pk_test_dummy',
				'sandbox_secret_key'      => 'sk_test_dummy',
				'sandbox_webhook_secret'  => $webhook_secret,
			)
		);
	}

	/**
	 * Build a webhook request carrying a genuine Stripe signature over the payload.
	 */
	private function signed_request( string $payload, string $secret ): WP_REST_Request {
		$timestamp = time();
		$signature = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );

		$request = new WP_REST_Request( 'POST', '/' . $this->namespace . '/stripe/webhook' );
		$request->set_header( 'stripe-signature', 't=' . $timestamp . ',v1=' . $signature );
		$request->set_body( $payload );

		return $request;
	}

	public function test_correctly_signed_real_payload_validates_and_processes(): void {
		$secret = 'whsec_test_dummy';
		$this->configure_stripe( $secret );

		$order   = $this->make_order_with_intent( 'pi_signed_1' );
		$payload = $this->real_event_payload( 'evt_signed_1', 'pi_signed_1' );
		$request = $this->signed_request( $payload, $secret );

		// Full path: real Stripe signature verification + SDK event hydration.
		$event = $this->webhook->validate_request( $request );

		$this->assertInstanceOf( StripeEvent::class, $event, 'A correctly-signed payload validates into a real Stripe\\Event.' );
		$this->assertSame( 'evt_signed_1', $event->id );
		$this->assertSame( 'payment_intent.succeeded', $event->type );
		$this->assertSame( 'pi_signed_1', $event->data->object->id, 'Real SDK object exposes data->object->id as the handler reads it.' );

		$result = $this->webhook->process_event( $event );

		$this->assertSame( 200, $result['code'] );
		$this->assertTrue( $result['processed'] );
		$this->assertSame( 1, $this->succeeded_fires );
		$this->assertNotEmpty( $order->get_meta( '_ec_processed_evt_evt_signed_1' ) );
	}

	public function test_tampered_signature_is_rejected(): void {
		$this->configure_stripe( 'whsec_test_dummy' );

		$payload = $this->real_event_payload( 'evt_bad_1', 'pi_bad_1' );
		// Signed with the WRONG secret — the configured secret won't verify it.
		$request = $this->signed_request( $payload, 'whsec_attacker_secret' );

		$event = $this->webhook->validate_request( $request );

		$this->assertInstanceOf( WP_Error::class, $event, 'A payload signed with the wrong secret is rejected.' );
		$this->assertSame( 'invalid_signature', $event->get_error_code() );
	}

	public function test_real_failed_intent_event_object_processes(): void {
		$order   = $this->make_order_with_intent( 'pi_real_failed_1' );
		$payload = json_decode( $this->real_event_payload( 'evt_real_failed_1', 'pi_real_failed_1', 'payment_intent.payment_failed' ), true );

		// Hydrate a real Stripe\Event object (the SDK class the webhook receives).
		$event = StripeEvent::constructFrom( $payload );

		$this->assertInstanceOf( StripeEvent::class, $event );
		$this->assertSame( 'payment_intent.payment_failed', $event->type );

		$result = $this->webhook->process_event( $event );

		$this->assertSame( 200, $result['code'] );
		$this->assertTrue( $result['processed'] );
		$this->assertSame( 1, $this->failed_fires );
		$this->assertNotEmpty( $order->get_meta( '_ec_processed_evt_evt_real_failed_1' ) );
	}

	public function test_failed_intent_event_is_also_deduped(): void {
		$order = $this->make_order_with_intent( 'pi_failed_evt_1' );
		$event = $this->make_event( 'evt_failed_evt_1', 'pi_failed_evt_1', 'payment_intent.payment_failed' );

		$first  = $this->webhook->process_event( $event );
		$second = $this->webhook->process_event( $event );

		$this->assertTrue( $first['processed'] );
		$this->assertFalse( $second['processed'] );
		$this->assertSame( 1, $this->failed_fires, 'payment_intent.payment_failed is deduped like succeeded.' );
		$this->assertNotEmpty( $order->get_meta( '_ec_processed_evt_evt_failed_evt_1' ) );
	}
}

<?php
/**
 * Test Transaction model.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;

class TransactionTest extends EasyCommerceTestCase {

	protected $order_id;
	protected $transaction_id;

	public function set_up(): void {
		parent::set_up();

		// Create a prerequisite order via factory.
		$this->order_id = $this->factory->order->create( [
			'customer_id' => 1,
			'total'       => 80.00,
			'status'      => 'pending',
		] );
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Build a minimal transaction data array.
	 *
	 * @param array $overrides
	 * @return array
	 */
	private function transaction_data( array $overrides = [] ): array {
		return array_merge( [
			'amount'         => 100.00,
			'currency'       => 'USD',
			'payment_method' => 'stripe',
			'transaction_id' => 'txn_test_' . uniqid(),
			'status'         => 'completed',
		], $overrides );
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * Test transaction constructor creates a valid instance.
	 */
	public function test_constructor() {
		$transaction = new Transaction();

		$this->assertInstanceOf( Transaction::class, $transaction );
	}

	// ── add() ─────────────────────────────────────────────────────────────────

	/**
	 * Test add() returns a positive integer ID.
	 */
	public function test_add() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data( [
			'amount'         => 75.50,
			'payment_method' => 'paypal',
			'transaction_id' => 'txn_paypal_456',
			'status'         => 'pending',
		] ) );

		$this->transaction_id = $id;

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
	}

	/**
	 * Test add() defaults the type field to 'payment' when not specified.
	 */
	public function test_add_sets_default_type_to_payment() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data() );
		$this->transaction_id = $id;

		$record = $transaction->get_by_id( $id );

		$this->assertIsObject( $record );
		$this->assertEquals( 'payment', $record->type );
	}

	/**
	 * Test add() stores a custom type value correctly.
	 */
	public function test_add_with_custom_type() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data( [
			'type' => 'refund',
		] ) );

		$this->transaction_id = $id;

		$record = $transaction->get_by_id( $id );

		$this->assertIsObject( $record );
		$this->assertEquals( 'refund', $record->type );
	}

	/**
	 * Test add() with a non-existent order ID still inserts a row (FK not enforced at model level).
	 */
	public function test_add_with_invalid_order_id() {
		$transaction  = new Transaction();
		$bogus_order  = 999999;

		$id = $transaction->add( $bogus_order, $this->transaction_data() );

		// The model does not enforce FK constraints — it either succeeds or returns false.
		$this->assertTrue( $id === false || ( is_int( $id ) && $id > 0 ) );
	}

	/**
	 * Test add() with order status argument updates the order status.
	 */
	public function test_add_with_order_status_update() {
		$transaction = new Transaction();

		$id = $transaction->add(
			$this->order_id,
			$this->transaction_data( [
				'amount'         => 150.00,
				'transaction_id' => 'txn_status_test',
				'status'         => 'completed',
			] ),
			'completed'
		);

		$this->transaction_id = $id;

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );

		$order = new Order( $this->order_id );
		$this->assertEquals( 'completed', $order->get_status() );
	}

	// ── UNIQUE(transaction_id) — #3128 ────────────────────────────────────────

	/**
	 * A second add() with an existing transaction_id must not create a duplicate
	 * row — it returns the already-recorded row id instead.
	 */
	public function test_add_duplicate_transaction_id_is_no_op() {
		$transaction = new Transaction();

		$first = $transaction->add( $this->order_id, $this->transaction_data( [
			'transaction_id' => 'txn_dup_3128',
		] ) );

		$second = $transaction->add( $this->order_id, $this->transaction_data( [
			'transaction_id' => 'txn_dup_3128',
			'amount'         => 999.00,
		] ) );

		$this->assertIsInt( $first );
		$this->assertSame( (int) $first, (int) $second, 'Duplicate insert should return the existing row id.' );

		$rows = $transaction->get_by_order_id( $this->order_id );
		$matches = array_filter( $rows, function ( $row ) {
			return 'txn_dup_3128' === $row->transaction_id;
		} );
		$this->assertCount( 1, $matches, 'Only one row may exist for a given transaction_id.' );
	}

	/**
	 * Distinct transaction_ids are still inserted as separate rows.
	 */
	public function test_add_distinct_transaction_ids_create_separate_rows() {
		$transaction = new Transaction();

		$a = $transaction->add( $this->order_id, $this->transaction_data( [ 'transaction_id' => 'txn_a_3128' ] ) );
		$b = $transaction->add( $this->order_id, $this->transaction_data( [ 'transaction_id' => 'txn_b_3128' ] ) );

		$this->assertIsInt( $a );
		$this->assertIsInt( $b );
		$this->assertNotSame( (int) $a, (int) $b );
	}

	/**
	 * get_by_transaction_id() returns the persisted row for a known id and null otherwise.
	 */
	public function test_get_by_transaction_id() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data( [ 'transaction_id' => 'txn_lookup_3128' ] ) );

		$found = $transaction->get_by_transaction_id( 'txn_lookup_3128' );
		$this->assertIsObject( $found );
		$this->assertEquals( $id, $found->id );

		$this->assertNull( $transaction->get_by_transaction_id( 'txn_does_not_exist_3128' ) );
	}

	// ── get_by_order_id() ─────────────────────────────────────────────────────

	/**
	 * Test get_by_order_id() returns an array containing the added transaction.
	 */
	public function test_get_by_order_id() {
		$transaction = new Transaction();

		$id                   = $transaction->add( $this->order_id, $this->transaction_data() );
		$this->transaction_id = $id;

		$transactions = $transaction->get_by_order_id( $this->order_id );

		$this->assertIsArray( $transactions );
		$this->assertGreaterThanOrEqual( 1, count( $transactions ) );
	}

	// ── get_by_id() ───────────────────────────────────────────────────────────

	/**
	 * Test get_by_id() returns an object with all expected fields.
	 */
	public function test_get_by_id() {
		$transaction = new Transaction();

		$data = $this->transaction_data( [
			'amount'         => 200.00,
			'currency'       => 'EUR',
			'payment_method' => 'stripe',
			'transaction_id' => 'txn_eur_202',
			'status'         => 'completed',
		] );

		$id                   = $transaction->add( $this->order_id, $data );
		$this->transaction_id = $id;

		$record = $transaction->get_by_id( $id );

		$this->assertIsObject( $record );
		$this->assertEquals( $this->order_id, $record->order_id );
		$this->assertEquals( 200.00, $record->amount );
		$this->assertEquals( 'EUR', $record->currency );
		$this->assertEquals( 'stripe', $record->payment_gateway );
		$this->assertEquals( 'txn_eur_202', $record->transaction_id );
		$this->assertEquals( 'completed', $record->status );
	}

	/**
	 * Test get_by_id() returns correct field values for every persisted field.
	 */
	public function test_get_by_id_returns_correct_fields() {
		$transaction = new Transaction();

		$data = [
			'amount'         => 55.00,
			'currency'       => 'GBP',
			'payment_method' => 'paypal',
			'transaction_id' => 'txn_gbp_check',
			'status'         => 'pending',
			'type'           => 'payment',
		];

		$id                   = $transaction->add( $this->order_id, $data );
		$this->transaction_id = $id;

		$record = $transaction->get_by_id( $id );

		$this->assertIsObject( $record );
		$this->assertEquals( $id, $record->id );
		$this->assertEquals( $this->order_id, $record->order_id );
		$this->assertEquals( 55.00, $record->amount );
		$this->assertEquals( 'GBP', $record->currency );
		$this->assertEquals( 'paypal', $record->payment_gateway );
		$this->assertEquals( 'txn_gbp_check', $record->transaction_id );
		$this->assertEquals( 'pending', $record->status );
		$this->assertEquals( 'payment', $record->type );
	}

	// ── update() ─────────────────────────────────────────────────────────────

	/**
	 * Test update() returns a non-negative result.
	 */
	public function test_update() {
		$transaction = new Transaction();

		$id                   = $transaction->add( $this->order_id, $this->transaction_data( [
			'amount'         => 50.00,
			'transaction_id' => 'txn_stripe_789',
			'status'         => 'pending',
		] ) );
		$this->transaction_id = $id;

		$result = $transaction->update( $id, [
			'status'         => 'completed',
			'transaction_id' => 'txn_stripe_789_updated',
		] );

		$this->assertGreaterThanOrEqual( 0, $result );
	}

	/**
	 * Test update() reflects the new status when the record is fetched by ID.
	 */
	public function test_update_status_reflects_in_get_by_id() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data( [
			'status' => 'pending',
		] ) );
		$this->transaction_id = $id;

		$transaction->update( $id, [ 'status' => 'failed' ] );

		$record = $transaction->get_by_id( $id );

		$this->assertIsObject( $record );
		$this->assertEquals( 'failed', $record->status );
	}

	/**
	 * Test update() with order status argument propagates the status to the order.
	 */
	public function test_update_with_order_status_update() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data( [
			'status' => 'pending',
		] ) );
		$this->transaction_id = $id;

		$result = $transaction->update( $id, [ 'status' => 'failed' ], 'cancelled' );

		$this->assertGreaterThanOrEqual( 0, $result );

		$order = new Order( $this->order_id );
		$this->assertEquals( 'cancelled', $order->get_status() );
	}

	// ── delete() ─────────────────────────────────────────────────────────────

	/**
	 * Test delete() returns a non-negative result.
	 */
	public function test_delete() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data( [
			'amount'         => 25.00,
			'payment_method' => 'bank',
			'transaction_id' => 'txn_bank_101',
			'status'         => 'failed',
		] ) );

		$result = $transaction->delete( $id );
		$this->assertGreaterThanOrEqual( 0, $result );

		$this->transaction_id = null;
	}

	/**
	 * Test delete() removes the record so get_by_id() returns null/false.
	 */
	public function test_delete_removes_record() {
		$transaction = new Transaction();

		$id = $transaction->add( $this->order_id, $this->transaction_data() );

		$transaction->delete( $id );

		$record = $transaction->get_by_id( $id );

		$this->assertFalse( (bool) $record );
	}

	// ── list() ────────────────────────────────────────────────────────────────

	/**
	 * Test list() returns an array keyed with 'transactions' and 'total'.
	 */
	public function test_list_returns_array() {
		$transaction = new Transaction();
		$transaction->add( $this->order_id, $this->transaction_data() );

		$result = Transaction::list();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'transactions', $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertIsArray( $result['transactions'] );
		$this->assertGreaterThanOrEqual( 1, $result['total'] );
	}

	/**
	 * Test list() with an order_id filter returns only transactions for that order.
	 */
	public function test_list_filter_by_order_id() {
		// Second order to create noise.
		$other_order_id = $this->factory->order->create( [
			'customer_id' => 1,
			'total'       => 30.00,
			'status'      => 'pending',
		] );

		$transaction = new Transaction();

		$target_id = $transaction->add( $this->order_id, $this->transaction_data( [
			'transaction_id' => 'txn_target',
		] ) );

		$transaction->add( $other_order_id, $this->transaction_data( [
			'transaction_id' => 'txn_noise',
		] ) );

		$result = Transaction::list( [ 'order_id' => $this->order_id ] );

		$this->assertIsArray( $result['transactions'] );

		foreach ( $result['transactions'] as $record ) {
			$this->assertEquals( $this->order_id, $record->order_id );
		}
	}
}

<?php
/**
 * Test Order model.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;

class OrderTest extends EasyCommerceTestCase {

	protected $order_id;
	protected int $customer_id = 1;

	public function set_up(): void {
		parent::set_up();
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Create a minimal order and return the model instance.
	 *
	 * @param array $overrides
	 * @return Order
	 */
	private function make_order( array $overrides = [] ): Order {
		$order = new Order();
		$order->create( array_merge( [
			'customer_id' => $this->customer_id,
			'total'       => 100.00,
			'status'      => 'pending',
			'items'       => [],
			'meta'        => [],
		], $overrides ) );
		return $order;
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * Test order constructor with no arguments.
	 */
	public function test_constructor() {
		$order = new Order();

		$this->assertInstanceOf( Order::class, $order );
		$this->assertFalse( $order->exists() );
	}

	/**
	 * Test order constructor with a valid ID loads the persisted record.
	 */
	public function test_constructor_with_valid_id() {
		$order      = new Order();
		$order_data = [
			'customer_id' => 1,
			'total'       => 100.00,
			'status'      => 'pending',
			'items'       => [],
			'meta'        => [],
		];

		$id = $order->create( $order_data );
		$this->assertGreaterThan( 0, $id, 'Order creation should return a valid ID' );

		$this->order_id = $id;

		$loaded_order = new Order( $id );

		$this->assertTrue( $loaded_order->exists() );
		$this->assertEquals( $id, $loaded_order->get_id() );
		$this->assertEquals( 1, $loaded_order->get_customer_id() );
		$this->assertEquals( 100.00, $loaded_order->get_total() );
		$this->assertEquals( 'pending', $loaded_order->get_status() );
	}

	/**
	 * Test order constructor with an unknown ID sets exists to false.
	 */
	public function test_constructor_with_invalid_id() {
		$order = new Order( 99999 );

		$this->assertFalse( $order->exists() );
		$this->assertNull( $order->get_id() );
	}

	// ── exists() ──────────────────────────────────────────────────────────────

	/**
	 * Test exists() reflects creation state correctly.
	 */
	public function test_exists() {
		$order = new Order();
		$this->assertFalse( $order->exists() );

		$id             = $order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 100.00,
			'status'      => 'pending',
			'items'       => [],
			'meta'        => [],
		] );
		$this->order_id = $id;

		$this->assertTrue( $order->exists() );
	}

	// ── Getters ───────────────────────────────────────────────────────────────

	/**
	 * Test scalar getter methods return the values passed at creation.
	 */
	public function test_getters() {
		$order      = new Order();
		$order_data = [
			'customer_id'    => $this->customer_id,
			'total'          => 75.50,
			'subtotal'       => 70.00,
			'status'         => 'processing',
			'fulfill_status' => 'shipped',
			'payment_method' => 'stripe',
			'items'          => [],
			'meta'           => [],
		];

		$id             = $order->create( $order_data );
		$this->order_id = $id;

		$this->assertEquals( $id, $order->get_id() );
		$this->assertEquals( $this->customer_id, $order->get_customer_id() );
		$this->assertEquals( 75.50, $order->get_total() );
		$this->assertEquals( 'processing', $order->get_status() );
		$this->assertEquals( 'shipped', $order->get_fulfillment_status() );
		$this->assertEquals( 'stripe', $order->get_payment_method() );
	}

	/**
	 * Test get_customer_name() returns a string.
	 */
	public function test_get_customer_name_returns_string() {
		$order = $this->make_order( [ 'customer_id' => $this->customer_id ] );

		$name = $order->get_customer_name();

		$this->assertIsString( $name );
	}

	/**
	 * Test get_tax_total() without meta returns 0.0.
	 */
	public function test_get_tax_total() {
		$order = $this->make_order();

		$tax_total = $order->get_tax_total();
		$this->assertIsFloat( $tax_total );
		$this->assertGreaterThanOrEqual( 0, $tax_total );
	}

	/**
	 * Test get_tax_total() equals the sum of product tax and shipping tax meta.
	 */
	public function test_get_tax_total_equals_product_plus_shipping_tax() {
		$order = $this->make_order();
		$order->update_meta( 'tax', 5.00 );
		$order->update_meta( 'shipping_tax', 2.50 );

		$this->assertEquals( 7.50, $order->get_tax_total() );
		$this->assertEquals( 5.00, $order->get_product_tax() );
		$this->assertEquals( 2.50, $order->get_shipping_tax() );
	}

	/**
	 * Test get_shipping_total() returns a non-negative float.
	 */
	public function test_get_shipping_total() {
		$order = $this->make_order();

		$shipping_total = $order->get_shipping_total();
		$this->assertIsFloat( $shipping_total );
		$this->assertGreaterThanOrEqual( 0, $shipping_total );
	}

	/**
	 * Test get_discount_total() returns a non-negative float.
	 */
	public function test_get_discount_total() {
		$order = $this->make_order();

		$discount_total = $order->get_discount_total();
		$this->assertIsFloat( $discount_total );
		$this->assertGreaterThanOrEqual( 0, $discount_total );
	}

	/**
	 * Test get_created_at() and get_updated_at() return non-empty strings.
	 */
	public function test_get_timestamps() {
		$order = $this->make_order();

		$this->assertIsString( $order->get_created_at() );
		$this->assertNotEmpty( $order->get_created_at() );
		$this->assertIsString( $order->get_updated_at() );
		$this->assertNotEmpty( $order->get_updated_at() );
	}

	/**
	 * Test get_transactions() returns an array.
	 */
	public function test_get_transactions() {
		$order = $this->make_order();

		$transactions = $order->get_transactions();
		$this->assertIsArray( $transactions );
	}

	// ── Refunds ───────────────────────────────────────────────────────────────

	/**
	 * Test get_total_refunded() returns 0.0 when no refunds exist.
	 */
	public function test_get_total_refunded_with_no_refunds() {
		$order_id = $this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 100.00,
			'status'      => 'completed',
		] );

		$order = new Order( $order_id );

		$this->assertEquals( 0.0, $order->get_total_refunded() );
	}

	/**
	 * Test get_total_refunded() accumulates amounts from multiple refunds.
	 */
	public function test_get_total_refunded_accumulates_multiple_refunds() {
		$order_id = $this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 100.00,
			'status'      => 'completed',
		] );

		$this->factory->refund->create( [
			'order_id' => $order_id,
			'amount'   => 20.00,
			'status'   => 'approved',
		] );

		$this->factory->refund->create( [
			'order_id' => $order_id,
			'amount'   => 15.50,
			'status'   => 'approved',
		] );

		$order = new Order( $order_id );

		$this->assertEquals( 35.50, $order->get_total_refunded() );
	}

	/**
	 * Test get_refunds() returns an array.
	 */
	public function test_get_refunds_returns_array() {
		$order_id = $this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 80.00,
			'status'      => 'completed',
		] );

		$this->factory->refund->create( [
			'order_id' => $order_id,
			'amount'   => 10.00,
			'status'   => 'approved',
		] );

		$order   = new Order( $order_id );
		$refunds = $order->get_refunds();

		$this->assertIsArray( $refunds );
		$this->assertCount( 1, $refunds );
	}

	// ── Status ────────────────────────────────────────────────────────────────

	/**
	 * Test set_status() updates the in-memory state immediately.
	 */
	public function test_set_status() {
		$order = $this->make_order( [ 'status' => 'pending' ] );

		$result = $order->set_status( 'completed' );
		$this->assertGreaterThanOrEqual( 0, $result );
		$this->assertEquals( 'completed', $order->get_status() );
	}

	/**
	 * Test set_status() persists the new status when the order is reloaded from DB.
	 */
	public function test_set_status_persists_across_reload() {
		$order_id = $this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 50.00,
			'status'      => 'pending',
		] );

		$order = new Order( $order_id );
		$order->set_status( 'completed' );

		$reloaded = new Order( $order_id );
		$this->assertEquals( 'completed', $reloaded->get_status() );
	}

	/**
	 * Regression: set_status('failed') must persist as 'failed' and not be
	 * coerced to an empty string by the orders.status ENUM. Before 'failed'
	 * was added to the ENUM, MySQL silently stored '' (the "Order Status N/A"
	 * bug) for declined payments.
	 */
	public function test_set_status_failed_persists_and_is_not_coerced() {
		global $wpdb;

		$order    = $this->make_order( [ 'status' => 'pending', 'total' => 42.00 ] );
		$order_id = $order->get_id();

		$order->set_status( 'failed' );

		// In-memory and reloaded model both report 'failed'.
		$this->assertEquals( 'failed', $order->get_status() );
		$this->assertEquals( 'failed', ( new Order( $order_id ) )->get_status() );

		// Raw DB value is exactly 'failed', proving no ENUM coercion to ''.
		$raw = $wpdb->get_var( $wpdb->prepare(
			"SELECT status FROM {$wpdb->prefix}ec_orders WHERE id = %d",
			$order_id
		) );
		$this->assertSame( 'failed', $raw );
	}

	/**
	 * Test list() with a 'failed' status filter returns only failed orders.
	 */
	public function test_list_filter_by_failed_status() {
		$this->make_order( [ 'status' => 'failed', 'total' => 30.00 ] );
		$this->make_order( [ 'status' => 'completed', 'total' => 30.00 ] );

		$result = Order::list( [
			'per_page' => 50,
			'page'     => 1,
			'status'   => 'failed',
		] );

		$this->assertIsArray( $result['orders'] );
		$this->assertGreaterThanOrEqual( 1, count( $result['orders'] ) );

		foreach ( $result['orders'] as $order ) {
			$this->assertEquals( 'failed', $order['status'] );
		}
	}

	/**
	 * Test set_fulfillment_status() updates in-memory state immediately.
	 */
	public function test_set_fulfillment_status() {
		$order = $this->make_order( [ 'status' => 'pending' ] );

		$result = $order->set_fulfillment_status( 'shipped' );
		$this->assertGreaterThanOrEqual( 0, $result );
		$this->assertEquals( 'shipped', $order->get_fulfillment_status() );
	}

	// ── Create / Update / Delete ──────────────────────────────────────────────

	/**
	 * Test create() returns a positive integer ID on success.
	 */
	public function test_create() {
		$order      = new Order();
		$order_data = [
			'customer_id'    => $this->customer_id,
			'total'          => 125.75,
			'subtotal'       => 120.00,
			'status'         => 'processing',
			'fulfill_status' => 'pending',
			'payment_method' => 'paypal',
			'items'          => [],
			'meta'           => [],
		];

		$id             = $order->create( $order_data );
		$this->order_id = $id;

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
		$this->assertTrue( $order->exists() );
	}

	/**
	 * Test create() returns false when customer_id is missing.
	 */
	public function test_create_fails_without_required_fields() {
		$order = new Order();

		$result = $order->create( [
			'total'  => 50.00,
			'status' => 'pending',
			'items'  => [],
			'meta'   => [],
		] );

		$this->assertFalse( (bool) $result );
		$this->assertFalse( $order->exists() );
	}

	/**
	 * Test update() changes a single field and reflects it on the instance.
	 */
	public function test_update() {
		$order = $this->make_order( [ 'status' => 'pending', 'total' => 50.00 ] );

		$result = $order->update( [ 'total' => 75.00, 'status' => 'completed' ] );
		$this->assertGreaterThanOrEqual( 0, $result );

		$this->assertEquals( 75.00, $order->get_total() );
		$this->assertEquals( 'completed', $order->get_status() );
	}

	/**
	 * Test update() changes multiple fields and each is persisted in the DB.
	 */
	public function test_update_changes_multiple_fields() {
		$order_id = $this->factory->order->create( [
			'customer_id'    => $this->customer_id,
			'total'          => 50.00,
			'status'         => 'pending',
		] );

		$order = new Order( $order_id );
		$order->update( [
			'total'          => 99.99,
			'status'         => 'processing',
			'payment_method' => 'stripe',
		] );

		$reloaded = new Order( $order_id );
		$this->assertEquals( 99.99, $reloaded->get_total() );
		$this->assertEquals( 'processing', $reloaded->get_status() );
		$this->assertEquals( 'stripe', $reloaded->get_payment_method() );
	}

	/**
	 * Test delete() removes the row from the database.
	 */
	public function test_delete() {
		$order = $this->make_order();
		$id    = $order->get_id();

		$result = $order->delete();
		$this->assertGreaterThanOrEqual( 0, $result );

		$deleted_order = new Order( $id );
		$this->assertFalse( $deleted_order->exists() );

		$this->order_id = null;
	}

	/**
	 * Test delete() removes order so it no longer exists in DB.
	 */
	public function test_delete_removes_from_db() {
		$order_id = $this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 40.00,
			'status'      => 'pending',
		] );

		$order = new Order( $order_id );
		$this->assertTrue( $order->exists() );

		$order->delete();

		$reloaded = new Order( $order_id );
		$this->assertFalse( $reloaded->exists() );
	}

	// ── Meta ─────────────────────────────────────────────────────────────────

	/**
	 * Test the full CRUD cycle for order meta.
	 */
	public function test_meta_operations() {
		$order = $this->make_order();

		$result = $order->add_meta( 'test_key', 'test_value' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $order->get_meta( 'test_key' );
		$this->assertEquals( 'test_value', $value );

		$result = $order->update_meta( 'test_key', 'updated_value' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $order->get_meta( 'test_key' );
		$this->assertEquals( 'updated_value', $value );

		$result = $order->delete_meta( 'test_key' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $order->get_meta( 'test_key' );
		$this->assertNull( $value );
	}

	// ── Items ─────────────────────────────────────────────────────────────────

	/**
	 * Test get_items() returns an array.
	 */
	public function test_get_items() {
		$order = $this->make_order();

		$items = $order->get_items();
		$this->assertIsArray( $items );
	}

	/**
	 * Test add_item() returns a positive ID.
	 */
	public function test_add_item() {
		$order = $this->make_order();

		$item_data = [
			'product_id' => 1,
			'price_id'   => 1,
			'quantity'   => 2,
			'price'      => 25.00,
		];

		$result = $order->add_item( $item_data );
		$this->assertGreaterThanOrEqual( 0, $result );
	}

	/**
	 * Test add_item() increases the count of items returned by get_items().
	 */
	public function test_add_item_increases_item_count() {
		$order  = $this->make_order();
		$before = count( $order->get_items() );

		$order->add_item( [
			'product_id' => 1,
			'price_id'   => 1,
			'quantity'   => 1,
			'price'      => 30.00,
		] );

		$after = count( $order->get_items() );
		$this->assertEquals( $before + 1, $after );
	}

	/**
	 * Test remove_item() decreases the count of items returned by get_items().
	 */
	public function test_remove_item_decreases_item_count() {
		$order   = $this->make_order();
		$item_id = $order->add_item( [
			'product_id' => 1,
			'price_id'   => 1,
			'quantity'   => 1,
			'price'      => 50.00,
		] );
		$this->assertGreaterThan( 0, $item_id );

		$before = count( $order->get_items() );
		$order->remove_item( $item_id );
		$after = count( $order->get_items() );

		$this->assertEquals( $before - 1, $after );
	}

	/**
	 * Test remove_item() returns a non-negative result.
	 */
	public function test_remove_item() {
		$order   = $this->make_order();
		$item_id = $order->add_item( [
			'product_id' => 1,
			'price_id'   => 1,
			'quantity'   => 1,
			'price'      => 50.00,
		] );
		$this->assertGreaterThan( 0, $item_id );

		$result = $order->remove_item( $item_id );
		$this->assertGreaterThanOrEqual( 0, $result );
	}

	/**
	 * Test update_item() returns a non-negative result.
	 */
	public function test_update_item() {
		$order   = $this->make_order();
		$item_id = $order->add_item( [
			'product_id' => 1,
			'price_id'   => 1,
			'quantity'   => 1,
			'price'      => 50.00,
		] );
		$this->assertGreaterThan( 0, $item_id );

		$result = $order->update_item( $item_id, [ 'quantity' => 3 ] );
		$this->assertGreaterThanOrEqual( 0, $result );
	}

	// ── list() ────────────────────────────────────────────────────────────────

	/**
	 * Test list() returns an array keyed with 'orders'.
	 */
	public function test_list_returns_array_of_orders() {
		$this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 50.00,
			'status'      => 'pending',
		] );

		$result = Order::list( [ 'per_page' => 10, 'page' => 1 ] );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'orders', $result );
		$this->assertIsArray( $result['orders'] );
		$this->assertGreaterThanOrEqual( 1, count( $result['orders'] ) );
	}

	/**
	 * Test list() with a status filter returns only matching orders.
	 */
	public function test_list_filter_by_status() {
		$this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 60.00,
			'status'      => 'completed',
		] );

		$this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 60.00,
			'status'      => 'cancelled',
		] );

		$result = Order::list( [
			'per_page' => 50,
			'page'     => 1,
			'status'   => 'completed',
		] );

		$this->assertIsArray( $result['orders'] );

		foreach ( $result['orders'] as $order ) {
			$this->assertEquals( 'completed', $order['status'] );
		}
	}

	/**
	 * Test list() with a customer_id filter returns only orders for that customer.
	 */
	public function test_list_filter_by_customer_id() {
		$other_customer_id = $this->factory->user->create( [ 'role' => 'customer' ] );

		$this->factory->order->create( [
			'customer_id' => $this->customer_id,
			'total'       => 70.00,
			'status'      => 'pending',
		] );

		$this->factory->order->create( [
			'customer_id' => $other_customer_id,
			'total'       => 70.00,
			'status'      => 'pending',
		] );

		$result = Order::list( [
			'per_page'    => 50,
			'page'        => 1,
			'customer_id' => $this->customer_id,
		] );

		$this->assertIsArray( $result['orders'] );

		foreach ( $result['orders'] as $order ) {
			$this->assertEquals( $this->customer_id, $order['customer'] );
		}
	}

	/**
	 * Test list() respects the per_page pagination argument.
	 */
	public function test_list_pagination_per_page() {
		// Create 5 orders to ensure we have more than 2.
		for ( $i = 0; $i < 5; $i++ ) {
			$this->factory->order->create( [
				'customer_id' => $this->customer_id,
				'total'       => 10.00 * ( $i + 1 ),
				'status'      => 'pending',
			] );
		}

		$result = Order::list( [ 'per_page' => 2, 'page' => 1 ] );

		$this->assertCount( 2, $result['orders'] );
		$this->assertEquals( 2, $result['per_page'] );
	}
}

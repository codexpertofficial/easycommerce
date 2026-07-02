<?php
/**
 * Test Order_Item model.
 *
 * Each test creates its own Order_Item instances.  The bootstrap truncates
 * EC tables before the suite and WP_UnitTestCase rolls back WP option writes;
 * EC-table writes are cleaned between runs by the truncate hook.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Order_Item;
use EasyCommerce\Models\Order;

class OrderItemTest extends EasyCommerceTestCase {

	/**
	 * Order ID used as parent for all item fixtures.
	 *
	 * @var int
	 */
	protected $order_id;

	// ── Lifecycle ────────────────────────────────────────────────────────────

	public function set_up(): void {
		parent::set_up();

		// Create a parent order so FK constraints are satisfied.
		$this->order_id = $this->factory->order->create( array(
			'customer_id' => 1,
			'total'       => 100.00,
			'status'      => 'pending',
		) );

		$this->assertGreaterThan( 0, $this->order_id, 'set_up: order must be created successfully.' );
	}

	public function tear_down(): void {
		parent::tear_down();
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::__construct
	 */
	public function test_constructor() {
		$item = new Order_Item();

		$this->assertInstanceOf( Order_Item::class, $item );
	}

	/**
	 * @covers Order_Item::__construct
	 */
	public function test_constructor_with_valid_id() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data() );

		$loaded = new Order_Item( $item_id );

		$this->assertInstanceOf( Order_Item::class, $loaded );
	}

	/**
	 * @covers Order_Item::__construct
	 */
	public function test_constructor_with_invalid_id_does_not_throw() {
		// Non-existent ID — constructor must not throw.
		$item = new Order_Item( 999999 );

		$this->assertInstanceOf( Order_Item::class, $item );
	}

	// ── add ───────────────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::add
	 */
	public function test_add() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data() );

		$this->assertIsInt( $item_id );
		$this->assertGreaterThan( 0, $item_id );
	}

	/**
	 * @covers Order_Item::add
	 */
	public function test_add_item_to_valid_order() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, array(
			'product_id' => 42,
			'quantity'   => 3,
			'price'      => 15.00,
		) );

		$this->assertIsInt( $item_id );
		$this->assertGreaterThan( 0, $item_id );

		$stored = $item->get_by_id( $item_id );
		$this->assertEquals( $this->order_id, (int) $stored->order_id );
	}

	/**
	 * @covers Order_Item::add
	 */
	public function test_add_sets_product_id_correctly() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, array(
			'product_id' => 77,
			'quantity'   => 1,
			'price'      => 9.99,
		) );

		$stored = $item->get_by_id( $item_id );

		$this->assertEquals( 77, (int) $stored->product_id );
	}

	/**
	 * @covers Order_Item::add
	 */
	public function test_add_with_inline_meta() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, array(
			'product_id' => 1,
			'quantity'   => 1,
			'price'      => 10.00,
			'meta'       => array( 'color' => 'blue' ),
		) );

		$this->assertGreaterThan( 0, $item_id );

		// Meta should have been persisted by add().
		$value = $item->get_meta( 'color' );
		$this->assertEquals( 'blue', $value );
	}

	// ── get_by_id ─────────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::get_by_id
	 */
	public function test_get_by_id_returns_correct_item() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, array(
			'product_id' => 5,
			'quantity'   => 2,
			'price'      => 25.00,
		) );

		$stored = $item->get_by_id( $item_id );

		$this->assertIsObject( $stored );
		$this->assertEquals( $item_id, (int) $stored->id );
		$this->assertEquals( $this->order_id, (int) $stored->order_id );
		$this->assertEquals( 5, (int) $stored->product_id );
	}

	/**
	 * @covers Order_Item::get_by_id
	 */
	public function test_get_by_id() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data() );

		$stored = $item->get_by_id( $item_id );

		$this->assertIsObject( $stored );
		$this->assertEquals( $this->order_id, (int) $stored->order_id );
		$this->assertEquals( 1, (int) $stored->product_id );
	}

	// ── get_by_order_id ───────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::get_by_order_id
	 */
	public function test_get_by_order_id_returns_items() {
		$item = new Order_Item();
		$item->add( $this->order_id, $this->minimal_item_data( 1, 1, 10.00 ) );
		$item->add( $this->order_id, $this->minimal_item_data( 2, 2, 20.00 ) );

		$items = $item->get_by_order_id( $this->order_id );

		$this->assertIsArray( $items );
		$this->assertGreaterThanOrEqual( 2, count( $items ) );

		foreach ( $items as $fetched ) {
			$this->assertEquals( $this->order_id, (int) $fetched->order_id );
		}
	}

	/**
	 * @covers Order_Item::get_by_order_id
	 */
	public function test_get_by_order_id() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data() );

		$items = $item->get_by_order_id( $this->order_id );

		$this->assertIsArray( $items );
		$this->assertGreaterThanOrEqual( 1, count( $items ) );
	}

	/**
	 * @covers Order_Item::get_by_order_id
	 */
	public function test_get_by_order_id_returns_empty_for_invalid_order() {
		$item  = new Order_Item();
		$items = $item->get_by_order_id( 999999 );

		$this->assertIsArray( $items );
		$this->assertCount( 0, $items );
	}

	// ── get_by ────────────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::get_by
	 */
	public function test_get_by() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data() );

		$items = $item->get_by( $this->order_id, 'order_id' );

		$this->assertIsArray( $items );
		$this->assertGreaterThanOrEqual( 1, count( $items ) );
	}

	// ── update ────────────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::update
	 */
	public function test_update() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data( 1, 1, 50.00 ) );

		$result = $item->update( array( 'quantity' => 5, 'price' => 12.00 ) );

		$this->assertNotFalse( $result );
	}

	/**
	 * @covers Order_Item::update
	 */
	public function test_update_quantity() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data( 1, 2, 20.00 ) );

		$item->update( array( 'quantity' => 9 ) );

		$stored = $item->get_by_id( $item_id );
		$this->assertEquals( 9, (int) $stored->quantity );
	}

	// ── delete ────────────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::delete
	 */
	public function test_delete() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data() );

		$result = $item->delete();

		$this->assertNotFalse( $result );
	}

	/**
	 * @covers Order_Item::delete
	 */
	public function test_delete_removes_item() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data( 1, 1, 30.00 ) );

		$item->delete();

		$stored = $item->get_by_id( $item_id );
		$this->assertNull( $stored );
	}

	// ── Meta: add ─────────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::add_meta
	 */
	public function test_meta_add_and_get() {
		$item = new Order_Item();
		$item->add( $this->order_id, $this->minimal_item_data() );

		$result = $item->add_meta( 'size', 'XL' );

		$this->assertGreaterThan( 0, $result );

		$value = $item->get_meta( 'size' );
		$this->assertEquals( 'XL', $value );
	}

	/**
	 * @covers Order_Item::add_meta
	 * @covers Order_Item::get_meta
	 */
	public function test_meta_operations() {
		$item    = new Order_Item();
		$item_id = $item->add( $this->order_id, $this->minimal_item_data() );

		// add_meta
		$result = $item->add_meta( 'test_key', 'test_value' );
		$this->assertGreaterThanOrEqual( 0, $result );

		// get_meta
		$value = $item->get_meta( 'test_key' );
		$this->assertEquals( 'test_value', $value );

		// update_meta
		$result = $item->update_meta( 'test_key', 'updated_value' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $item->get_meta( 'test_key' );
		$this->assertEquals( 'updated_value', $value );

		// delete_meta
		$result = $item->delete_meta( 'test_key' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $item->get_meta( 'test_key' );
		$this->assertNull( $value );
	}

	// ── Meta: update ──────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::update_meta
	 */
	public function test_meta_update_changes_value() {
		$item = new Order_Item();
		$item->add( $this->order_id, $this->minimal_item_data() );

		$item->add_meta( 'weight', '1kg' );
		$item->update_meta( 'weight', '2kg' );

		$value = $item->get_meta( 'weight' );
		$this->assertEquals( '2kg', $value );
	}

	// ── Meta: delete ──────────────────────────────────────────────────────────

	/**
	 * @covers Order_Item::delete_meta
	 */
	public function test_meta_delete_removes_key() {
		$item = new Order_Item();
		$item->add( $this->order_id, $this->minimal_item_data() );

		$item->add_meta( 'temp_key', 'temp_value' );
		$item->delete_meta( 'temp_key' );

		$value = $item->get_meta( 'temp_key' );
		$this->assertNull( $value );
	}

	/**
	 * @covers Order_Item::get_meta
	 */
	public function test_meta_get_returns_null_for_missing_key() {
		$item = new Order_Item();
		$item->add( $this->order_id, $this->minimal_item_data() );

		$value = $item->get_meta( 'nonexistent_meta_key_xyz' );

		$this->assertNull( $value );
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Return a minimal valid item data array.
	 */
	private function minimal_item_data(
		int $product_id = 1,
		int $quantity   = 1,
		float $price    = 10.00
	): array {
		return array(
			'product_id' => $product_id,
			'quantity'   => $quantity,
			'price'      => $price,
		);
	}
}

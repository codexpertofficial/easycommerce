<?php
/**
 * Test Coupon model.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Coupon;

class CouponTest extends EasyCommerceTestCase {

	/**
	 * @var int|null Coupon ID created by the most recent test (for tear_down cleanup).
	 */
	protected $coupon_id;

	// ── Lifecycle ─────────────────────────────────────────────────────────────

	public function set_up(): void {
		parent::set_up();
		$this->coupon_id = null;
	}

	public function tear_down(): void {
		if ( $this->coupon_id ) {
			$coupon = new Coupon( $this->coupon_id );
			if ( $coupon->exists() ) {
				$coupon->delete();
			}
		}

		parent::tear_down();
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Build a minimal valid args array for a percentage coupon.
	 */
	private function valid_coupon_args( array $overrides = [] ): array {
		return array_merge( [
			'name'  => 'Test Coupon',
			'code'  => 'TEST-' . uniqid(),
			'type'  => 'percentage',
			'offer' => 10,
		], $overrides );
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * Test coupon constructor without arguments.
	 */
	public function test_constructor() {
		$coupon = new Coupon();

		$this->assertInstanceOf( Coupon::class, $coupon );
		$this->assertFalse( $coupon->exists() );
	}

	/**
	 * Test coupon constructor with a non-existent code identifier.
	 */
	public function test_constructor_with_identifier() {
		$coupon = new Coupon( 'NONEXISTENTCODE' );

		$this->assertInstanceOf( Coupon::class, $coupon );
		$this->assertFalse( $coupon->exists() );
	}

	/**
	 * Constructor loads coupon by code when a string is passed.
	 */
	public function test_constructor_loads_by_code() {
		$code       = 'LOAD-BY-CODE-' . uniqid();
		$coupon_id  = $this->factory->coupon->create( [ 'code' => $code, 'name' => 'Load By Code' ] );
		$this->coupon_id = $coupon_id;

		$loaded = new Coupon( $code );

		$this->assertTrue( $loaded->exists() );
		$this->assertSame( $coupon_id, $loaded->get_id() );
	}

	/**
	 * Constructor loads coupon by numeric ID when an integer is passed.
	 */
	public function test_constructor_loads_by_id() {
		$coupon_id       = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon_id;

		$loaded = new Coupon( $coupon_id );

		$this->assertTrue( $loaded->exists() );
		$this->assertSame( $coupon_id, $loaded->get_id() );
	}

	// ── exists() ─────────────────────────────────────────────────────────────

	/**
	 * Test exists method for a non-persisted coupon.
	 */
	public function test_exists() {
		$coupon = new Coupon();
		$this->assertFalse( $coupon->exists() );
	}

	// ── create() — success paths ───────────────────────────────────────────

	/**
	 * Test create method returns a positive integer ID.
	 */
	public function test_create() {
		$coupon = new Coupon();
		$result = $coupon->create( $this->valid_coupon_args() );

		$this->assertIsInt( $result );
		$this->assertGreaterThan( 0, $result );

		$this->coupon_id = $result;
	}

	/**
	 * create() stores a percentage coupon and exposes the right type/offer.
	 */
	public function test_create_percentage_coupon() {
		$code   = 'PCT-' . uniqid();
		$coupon = new Coupon();
		$id     = $coupon->create( [
			'name'  => 'Percentage Coupon',
			'code'  => $code,
			'type'  => 'percentage',
			'offer' => 10,
		] );

		$this->coupon_id = $id;
		$this->assertGreaterThan( 0, $id );

		$loaded = new Coupon( $id );
		$this->assertSame( 'percentage', $loaded->get_type() );
		$this->assertEquals( 10, $loaded->get_offer() );
	}

	/**
	 * create() stores a fixed-amount coupon.
	 */
	public function test_create_fixed_coupon() {
		$code   = 'FIXED-' . uniqid();
		$coupon = new Coupon();
		$id     = $coupon->create( [
			'name'  => 'Fixed Coupon',
			'code'  => $code,
			'type'  => 'fixed',
			'offer' => 5,
		] );

		$this->coupon_id = $id;
		$this->assertGreaterThan( 0, $id );

		$loaded = new Coupon( $id );
		$this->assertSame( 'fixed', $loaded->get_type() );
		$this->assertEquals( 5, $loaded->get_offer() );
	}

	/**
	 * create() accepts free_shipping coupons (no offer required).
	 */
	public function test_create_free_shipping_coupon() {
		$code   = 'SHIP-' . uniqid();
		$coupon = new Coupon();
		$id     = $coupon->create( [
			'name' => 'Free Shipping Coupon',
			'code' => $code,
			'type' => 'free_shipping',
		] );

		$this->coupon_id = $id;
		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );

		$loaded = new Coupon( $id );
		$this->assertSame( 'free_shipping', $loaded->get_type() );
	}

	// ── create() — failure paths ──────────────────────────────────────────

	/**
	 * create() returns false when name, code, or type is missing.
	 */
	public function test_create_fails_without_required_fields() {
		// Missing 'name'.
		$coupon = new Coupon();
		$result = $coupon->create( [ 'code' => 'X', 'type' => 'percentage', 'offer' => 10 ] );
		$this->assertFalse( $result, 'Expected false when name is missing' );

		// Missing 'code'.
		$coupon2 = new Coupon();
		$result2 = $coupon2->create( [ 'name' => 'X', 'type' => 'percentage', 'offer' => 10 ] );
		$this->assertFalse( $result2, 'Expected false when code is missing' );

		// Missing 'type'.
		$coupon3 = new Coupon();
		$result3 = $coupon3->create( [ 'name' => 'X', 'code' => 'Y', 'offer' => 10 ] );
		$this->assertFalse( $result3, 'Expected false when type is missing' );

		// Non-free_shipping coupon missing 'offer'.
		$coupon4 = new Coupon();
		$result4 = $coupon4->create( [ 'name' => 'X', 'code' => 'Y', 'type' => 'percentage' ] );
		$this->assertFalse( $result4, 'Expected false when offer is missing for percentage coupon' );
	}

	// ── Getters ───────────────────────────────────────────────────────────────

	/**
	 * Test getter methods return expected types and values.
	 */
	public function test_getters() {
		$unique_code = 'TEST-' . uniqid();
		$id          = $this->factory->coupon->create( [
			'name'  => 'Test Coupon',
			'code'  => $unique_code,
			'type'  => 'percentage',
			'offer' => 10,
		] );
		$this->coupon_id = $id;

		$coupon = new Coupon( $unique_code );

		$this->assertGreaterThan( 0, $coupon->get_id() );
		$this->assertIsString( $coupon->get_name() );
		$this->assertEquals( $unique_code, $coupon->get_code() );
		$this->assertIsString( $coupon->get_type() );
		$this->assertThat( $coupon->get_offer(), $this->logicalOr(
			$this->isType( 'int' ),
			$this->isType( 'float' ),
			$this->isType( 'string' )
		) );
	}

	/**
	 * get_code() returns the exact code used at creation.
	 */
	public function test_get_code_returns_correct_value() {
		$code            = 'EXACT-CODE-' . uniqid();
		$this->coupon_id = $this->factory->coupon->create( [ 'code' => $code ] );

		$coupon = new Coupon( $this->coupon_id );

		$this->assertSame( $code, $coupon->get_code() );
	}

	/**
	 * get_type() returns the type set at creation.
	 */
	public function test_get_type_returns_correct_value() {
		$this->coupon_id = $this->factory->coupon->create( [ 'type' => 'fixed', 'offer' => 5 ] );

		$coupon = new Coupon( $this->coupon_id );

		$this->assertSame( 'fixed', $coupon->get_type() );
	}

	/**
	 * get_offer() returns a numeric value.
	 */
	public function test_get_offer_returns_numeric() {
		$this->coupon_id = $this->factory->coupon->create( [ 'type' => 'percentage', 'offer' => 15 ] );

		$coupon = new Coupon( $this->coupon_id );

		$this->assertTrue( is_numeric( $coupon->get_offer() ) );
		$this->assertEquals( 15, $coupon->get_offer() );
	}

	// ── Active status ─────────────────────────────────────────────────────────

	/**
	 * Test get_usage_count method.
	 */
	public function test_get_usage_count() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon->get_id();

		$count = $coupon->get_usage_count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
	}

	/**
	 * Newly created coupon is active (active=1) by default.
	 */
	public function test_is_active_after_create() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon->get_id();

		$this->assertTrue( $coupon->is_active() );
	}

	/**
	 * Test is_active method independently.
	 */
	public function test_is_active() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon->get_id();

		$this->assertTrue( $coupon->is_active() );
	}

	// ── set_status() ─────────────────────────────────────────────────────────

	/**
	 * Test set_status method — basic call.
	 */
	public function test_set_status() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon->get_id();

		$result = $coupon->set_status( 0 );

		$this->assertThat( $result, $this->logicalOr(
			$this->isType( 'bool' ),
			$this->isType( 'int' ),
			$this->isNull()
		) );
	}

	/**
	 * set_status(0) deactivates a coupon so that a freshly loaded instance is inactive.
	 */
	public function test_set_status_deactivates_coupon() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$coupon = new Coupon( $id );
		$coupon->set_status( 0 );

		$reloaded = new Coupon( $id );
		$this->assertFalse( $reloaded->is_active() );
	}

	/**
	 * set_status(1) re-activates a previously deactivated coupon.
	 */
	public function test_set_status_reactivates_coupon() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args( [ 'active' => 0 ] ) );
		$this->coupon_id = $id;

		$coupon = new Coupon( $id );
		$coupon->set_status( 1 );

		$reloaded = new Coupon( $id );
		$this->assertTrue( $reloaded->is_active() );
	}

	// ── update() ─────────────────────────────────────────────────────────────

	/**
	 * Test update method — returns truthy.
	 */
	public function test_update() {
		$coupon = new Coupon();
		$id     = $coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$result = $coupon->update( [ 'name' => 'Updated Coupon' ] );

		$this->assertThat( $result, $this->logicalOr(
			$this->isType( 'bool' ),
			$this->isType( 'int' )
		) );
	}

	/**
	 * update() persists a new offer value to the database.
	 */
	public function test_update_changes_offer_value() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args( [ 'offer' => 10 ] ) );
		$this->coupon_id = $id;

		$coupon = new Coupon( $id );
		$coupon->update( [ 'offer' => 25, 'type' => 'percentage' ] );

		$reloaded = new Coupon( $id );
		$this->assertEquals( 25, $reloaded->get_offer() );
	}

	// ── delete() ─────────────────────────────────────────────────────────────

	/**
	 * Test delete method removes the coupon.
	 */
	public function test_delete() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = null; // Deleted below, skip tear_down cleanup.

		$result = $coupon->delete();

		$this->assertGreaterThanOrEqual( 0, $result );
		$this->assertFalse( $coupon->exists() );
	}

	/**
	 * delete() removes the coupon so it can no longer be loaded.
	 */
	public function test_delete_removes_coupon() {
		$code = 'DEL-' . uniqid();
		$id   = $this->factory->coupon->create( [ 'code' => $code ] );

		$coupon = new Coupon( $id );
		$coupon->delete();
		$this->coupon_id = null; // Already deleted.

		$reloaded = new Coupon( $id );
		$this->assertFalse( $reloaded->exists() );
	}

	// ── Rules ─────────────────────────────────────────────────────────────────

	/**
	 * Test get_rules method returns an array.
	 */
	public function test_get_rules() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon->get_id();

		$rules = $coupon->get_rules();

		$this->assertIsArray( $rules );
	}

	/**
	 * get_rules() always returns an array, even with no rules.
	 */
	public function test_get_rules_returns_array() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$coupon = new Coupon( $id );
		$rules  = $coupon->get_rules();

		$this->assertIsArray( $rules );
	}

	/**
	 * Test add_rule method — basic insertion.
	 */
	public function test_add_rule() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon->get_id();

		$result = $coupon->add_rule( [
			'type'  => 'minimum_amount',
			'value' => 50,
		] );

		$this->assertGreaterThanOrEqual( 0, $result );
	}

	/**
	 * add_rule() + get_rules() round-trip: the added rule is retrievable.
	 */
	public function test_add_rule_and_get_rules() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$coupon = new Coupon( $id );
		$coupon->add_rule( [ 'type' => 'min_spend', 'value' => 100 ] );

		$rules = $coupon->get_rules();

		$this->assertIsArray( $rules );
		$this->assertNotEmpty( $rules );

		$types = array_column( (array) $rules, 'type' );
		$this->assertContains( 'min_spend', $types );
	}

	/**
	 * A min_spend rule is stored with the correct value.
	 */
	public function test_add_min_spend_rule() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$coupon = new Coupon( $id );
		$coupon->add_rule( [ 'type' => 'min_spend', 'value' => 50 ] );

		$rules = $coupon->get_rules();

		$min_rule = null;
		foreach ( $rules as $rule ) {
			if ( $rule->type === 'min_spend' ) {
				$min_rule = $rule;
				break;
			}
		}

		$this->assertNotNull( $min_rule );
		$this->assertEquals( 50, $min_rule->value );
	}

	/**
	 * A max_spend rule is stored with the correct value.
	 */
	public function test_add_max_spend_rule() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$coupon = new Coupon( $id );
		$coupon->add_rule( [ 'type' => 'max_spend', 'value' => 500 ] );

		$rules = $coupon->get_rules();

		$max_rule = null;
		foreach ( $rules as $rule ) {
			if ( $rule->type === 'max_spend' ) {
				$max_rule = $rule;
				break;
			}
		}

		$this->assertNotNull( $max_rule );
		$this->assertEquals( 500, $max_rule->value );
	}

	/**
	 * A products rule stores product data correctly.
	 */
	public function test_add_product_rule() {
		$product_id = $this->factory->product->create( [ 'title' => 'Rule Product' ] );
		$id         = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$coupon    = new Coupon( $id );
		$rule_data = [ [ 'id' => $product_id, 'name' => 'Rule Product' ] ];
		$coupon->add_rule( [ 'type' => 'products', 'value' => $rule_data ] );

		$rules = $coupon->get_rules();

		$product_rule = null;
		foreach ( $rules as $rule ) {
			if ( $rule->type === 'products' ) {
				$product_rule = $rule;
				break;
			}
		}

		$this->assertNotNull( $product_rule );
		$this->assertIsArray( $product_rule->value );

		wp_delete_post( $product_id, true );
	}

	/**
	 * A date rule (start_date / end_date) is stored and retrievable.
	 */
	public function test_add_date_rule() {
		$id = $this->factory->coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $id;

		$coupon     = new Coupon( $id );
		$start_date = '2025-01-01';
		$coupon->add_rule( [ 'type' => 'start_date', 'value' => $start_date ] );

		$rules = $coupon->get_rules();

		$date_rule = null;
		foreach ( $rules as $rule ) {
			if ( $rule->type === 'start_date' ) {
				$date_rule = $rule;
				break;
			}
		}

		$this->assertNotNull( $date_rule );
		$this->assertSame( $start_date, $date_rule->value );
	}

	// ── get_products() ───────────────────────────────────────────────────────

	/**
	 * Test get_products method.
	 */
	public function test_get_products() {
		$coupon = new Coupon();
		$coupon->create( $this->valid_coupon_args() );
		$this->coupon_id = $coupon->get_id();

		$products = $coupon->get_products();

		$this->assertIsArray( $products );
	}

	// ── list() ────────────────────────────────────────────────────────────────

	/**
	 * list() returns an array with expected structural keys.
	 */
	public function test_list_returns_array() {
		$result = Coupon::list();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'coupons', $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertArrayHasKey( 'per_page', $result );
		$this->assertArrayHasKey( 'page', $result );
		$this->assertArrayHasKey( 'total_pages', $result );
		$this->assertIsArray( $result['coupons'] );
	}

	/**
	 * list() with active=1 only returns active coupons.
	 */
	public function test_list_filter_by_active() {
		// Active coupon.
		$active_id = $this->factory->coupon->create( $this->valid_coupon_args( [ 'active' => 1, 'code' => 'ACTIVE-' . uniqid() ] ) );

		// Inactive coupon created directly (factory uses active=1 default, so bypass).
		$inactive_coupon = new Coupon();
		$inactive_id     = $inactive_coupon->create( [
			'name'   => 'Inactive Coupon',
			'code'   => 'INACTIVE-' . uniqid(),
			'type'   => 'percentage',
			'offer'  => 5,
			'active' => 0,
		] );

		// Filter for active only.
		$active_result = Coupon::list( [ 'active' => 1, 'per_page' => 100 ] );
		$active_ids    = array_column( $active_result['coupons'], 'id' );
		$this->assertContains( $active_id, $active_ids );
		$this->assertNotContains( $inactive_id, $active_ids );

		// Filter for inactive only.
		$inactive_result = Coupon::list( [ 'active' => 0, 'per_page' => 100 ] );
		$inactive_ids    = array_column( $inactive_result['coupons'], 'id' );
		$this->assertContains( $inactive_id, $inactive_ids );
		$this->assertNotContains( $active_id, $inactive_ids );

		// Cleanup.
		( new Coupon( $active_id ) )->delete();
		( new Coupon( $inactive_id ) )->delete();
		$this->coupon_id = null;
	}

	/**
	 * is_applicable() is excluded from automated tests — requires Cart session.
	 */
	public function test_is_applicable() {
		$this->markTestSkipped( 'is_applicable test skipped due to Cart session issues' );
	}
}

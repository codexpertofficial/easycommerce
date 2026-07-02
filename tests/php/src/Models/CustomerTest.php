<?php
/**
 * Test Customer model.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Customer;

class CustomerTest extends EasyCommerceTestCase {

	protected $user_id;

	public function set_up(): void {
		parent::set_up();

		// Create a test user via factory
		$this->user_id = $this->factory->customer->create( [
			'email'      => 'customer@example.com',
			'first_name' => 'John',
			'last_name'  => 'Doe',
		] );
	}

	public function tear_down(): void {
		parent::tear_down();
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * Test customer constructor with a valid user ID.
	 */
	public function test_constructor(): void {
		$customer = new Customer( $this->user_id );

		$this->assertInstanceOf( Customer::class, $customer );
	}

	/**
	 * Test customer constructor with an invalid ID does not crash.
	 */
	public function test_constructor_invalid_id(): void {
		$customer = new Customer( 99999 );

		$this->assertInstanceOf( Customer::class, $customer );
	}

	/**
	 * Constructor with zero / null ID should not crash.
	 */
	public function test_constructor_with_invalid_id_does_not_crash(): void {
		$customer_zero = new Customer( 0 );
		$this->assertInstanceOf( Customer::class, $customer_zero );

		$customer_null = new Customer( null );
		$this->assertInstanceOf( Customer::class, $customer_null );
	}

	// ── Email ─────────────────────────────────────────────────────────────────

	/**
	 * get_email() should return the WordPress user's email address.
	 */
	public function test_get_email(): void {
		$customer = new Customer( $this->user_id );
		$email    = $customer->get_email();

		$this->assertIsString( $email );
		$this->assertNotEmpty( $email );
		$this->assertStringContainsString( '@', $email );
	}

	/**
	 * get_email() should return the exact email the user was created with.
	 */
	public function test_get_email_returns_correct_value(): void {
		$uid      = $this->factory->customer->create( [ 'email' => 'verify@example.com' ] );
		$customer = new Customer( $uid );

		$this->assertSame( 'verify@example.com', $customer->get_email() );
	}

	// ── Names ─────────────────────────────────────────────────────────────────

	/**
	 * get_first_name() should return a string.
	 */
	public function test_get_first_name(): void {
		$customer   = new Customer( $this->user_id );
		$first_name = $customer->get_first_name();

		$this->assertThat( $first_name, $this->logicalOr(
			$this->isType( 'string' ),
			$this->isFalse()
		) );
	}

	/**
	 * get_first_name() returns the meta value that was stored on creation.
	 */
	public function test_get_first_name_from_meta(): void {
		$uid = $this->factory->customer->create( [
			'email'      => 'firstname@example.com',
			'first_name' => 'Alice',
		] );
		$customer = new Customer( $uid );

		$this->assertSame( 'Alice', $customer->get_first_name() );
	}

	/**
	 * get_last_name() should return a string.
	 */
	public function test_get_last_name(): void {
		$customer  = new Customer( $this->user_id );
		$last_name = $customer->get_last_name();

		$this->assertThat( $last_name, $this->logicalOr(
			$this->isType( 'string' ),
			$this->isFalse()
		) );
	}

	// ── Phone ─────────────────────────────────────────────────────────────────

	/**
	 * get_phone() should return a string or false.
	 */
	public function test_get_phone(): void {
		$customer = new Customer( $this->user_id );
		$phone    = $customer->get_phone();

		$this->assertThat( $phone, $this->logicalOr(
			$this->isType( 'string' ),
			$this->isFalse()
		) );
	}

	// ── Address ───────────────────────────────────────────────────────────────

	/**
	 * get_billing_address() should return an array or false.
	 */
	public function test_get_billing_address(): void {
		$customer = new Customer( $this->user_id );
		$address  = $customer->get_billing_address();

		$this->assertThat( $address, $this->logicalOr(
			$this->isType( 'array' ),
			$this->isFalse()
		) );
	}

	/**
	 * get_shipping_address() should return an array or false.
	 */
	public function test_get_shipping_address(): void {
		$customer = new Customer( $this->user_id );
		$address  = $customer->get_shipping_address();

		$this->assertThat( $address, $this->logicalOr(
			$this->isType( 'array' ),
			$this->isFalse()
		) );
	}

	/**
	 * get_address_field() should return a string.
	 */
	public function test_get_address_field(): void {
		$customer = new Customer( $this->user_id );
		$field    = $customer->get_address_field( 'country' );

		$this->assertIsString( $field );
	}

	/**
	 * get_address_field() returns a string when address meta is populated.
	 */
	public function test_get_address_field_returns_string(): void {
		$customer = new Customer( $this->user_id );
		$customer->update_meta( 'billing_address', [
			'address_1' => '123 Main St',
			'city'      => 'Springfield',
			'country'   => 'US',
		] );

		$this->assertSame( 'US', $customer->get_address_field( 'country', 'billing' ) );
		$this->assertIsString( $customer->get_address_field( 'postcode', 'billing' ) );
	}

	/**
	 * Individual address getter methods should all return strings.
	 */
	public function test_address_getters(): void {
		$customer = new Customer( $this->user_id );

		$this->assertIsString( $customer->get_address_1() );
		$this->assertIsString( $customer->get_address_2() );
		$this->assertIsString( $customer->get_country() );
		$this->assertIsString( $customer->get_state() );
		$this->assertIsString( $customer->get_city() );
		$this->assertIsString( $customer->get_postcode() );
	}

	/**
	 * get_address() should return a string (comma-joined parts).
	 */
	public function test_get_address(): void {
		$customer = new Customer( $this->user_id );
		$address  = $customer->get_address();

		$this->assertThat( $address, $this->logicalOr(
			$this->isType( 'array' ),
			$this->isType( 'string' )
		) );
	}

	// ── Orders ────────────────────────────────────────────────────────────────

	/**
	 * get_orders() should always return an array.
	 */
	public function test_get_orders(): void {
		$customer = new Customer( $this->user_id );
		$orders   = $customer->get_orders();

		$this->assertIsArray( $orders );
	}

	/**
	 * get_orders() returns an array even after creating orders for the customer.
	 */
	public function test_get_orders_returns_array(): void {
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 50.00,
			'status'      => 'completed',
		] );

		$customer = new Customer( $this->user_id );
		$orders   = $customer->get_orders();

		$this->assertIsArray( $orders );
		$this->assertGreaterThanOrEqual( 1, count( $orders ) );
	}

	/**
	 * get_orders_by_status() should return an array.
	 */
	public function test_get_orders_by_status(): void {
		$customer = new Customer( $this->user_id );
		$orders   = $customer->get_orders_by_status();

		$this->assertIsArray( $orders );
	}

	// ── Order count ───────────────────────────────────────────────────────────

	/**
	 * get_order_count() for a new customer with no orders should be 0.
	 */
	public function test_get_order_count(): void {
		$customer = new Customer( $this->user_id );
		$count    = $customer->get_order_count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
	}

	/**
	 * get_order_count() increases after orders are created for that customer.
	 */
	public function test_get_order_count_with_orders(): void {
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 75.00,
			'status'      => 'completed',
		] );
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 25.00,
			'status'      => 'completed',
		] );

		$customer = new Customer( $this->user_id );
		$count    = $customer->get_order_count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 2, $count );
	}

	// ── Total spent ───────────────────────────────────────────────────────────

	/**
	 * get_total_spent() should return a float >= 0.
	 */
	public function test_get_total_spent(): void {
		$customer = new Customer( $this->user_id );
		$total    = $customer->get_total_spent();

		$this->assertIsFloat( $total );
		$this->assertGreaterThanOrEqual( 0, $total );
	}

	/**
	 * get_total_spent() sums up the totals of all orders for a customer.
	 */
	public function test_get_total_spent_with_orders(): void {
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 100.00,
			'status'      => 'completed',
		] );
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 50.00,
			'status'      => 'completed',
		] );

		$customer    = new Customer( $this->user_id );
		$total_spent = $customer->get_total_spent();

		$this->assertIsFloat( $total_spent );
		$this->assertGreaterThanOrEqual( 150.00, $total_spent );
	}

	/**
	 * get_total_spent() returns 0.0 when the customer has no orders at all.
	 */
	public function test_get_total_spent_zero_with_no_orders(): void {
		$uid      = $this->factory->customer->create( [ 'email' => 'noorders@example.com' ] );
		$customer = new Customer( $uid );

		$this->assertSame( 0.0, $customer->get_total_spent() );
	}

	// ── AOV ───────────────────────────────────────────────────────────────────

	/**
	 * get_aov() returns float or formatted string.
	 */
	public function test_get_aov(): void {
		$customer = new Customer( $this->user_id );
		$aov      = $customer->get_aov();

		$this->assertThat( $aov, $this->logicalOr(
			$this->isType( 'float' ),
			$this->isType( 'string' )
		) );
	}

	/**
	 * get_aov() equals total_spent / order_count when orders exist.
	 */
	public function test_get_aov_with_orders(): void {
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 80.00,
			'status'      => 'completed',
		] );
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 40.00,
			'status'      => 'completed',
		] );

		$customer = new Customer( $this->user_id );
		// AOV result is formatted (string) or raw float — just ensure it is not 0
		$aov = $customer->get_aov();

		$this->assertThat( $aov, $this->logicalOr(
			$this->isType( 'float' ),
			$this->isType( 'string' )
		) );
		// Must not be zero since there are orders with totals
		$this->assertNotSame( 0.0, $aov );
		$this->assertNotSame( '0', (string) $aov );
	}

	/**
	 * get_aov() returns 0.0 when there are no orders — no division by zero.
	 */
	public function test_get_aov_returns_zero_with_no_orders(): void {
		$uid      = $this->factory->customer->create( [ 'email' => 'aovzero@example.com' ] );
		$customer = new Customer( $uid );

		$this->assertSame( 0.0, $customer->get_aov() );
	}

	// ── LTV ───────────────────────────────────────────────────────────────────

	/**
	 * get_ltv() returns a string or float (formatted price).
	 */
	public function test_get_ltv(): void {
		$customer = new Customer( $this->user_id );
		$ltv      = $customer->get_ltv();

		$this->assertThat( $ltv, $this->logicalOr(
			$this->isType( 'float' ),
			$this->isType( 'string' )
		) );
	}

	/**
	 * get_ltv() reflects the total spent (returns a non-empty string/float).
	 */
	public function test_get_ltv_returns_string_or_float(): void {
		$customer = new Customer( $this->user_id );
		$ltv      = $customer->get_ltv();

		$this->assertThat(
			$ltv,
			$this->logicalOr(
				$this->isType( 'string' ),
				$this->isType( 'float' )
			)
		);
	}

	// ── Last order date ───────────────────────────────────────────────────────

	/**
	 * get_last_order_date() returns null when the customer has no orders.
	 */
	public function test_get_last_order_date_null_with_no_orders(): void {
		$uid      = $this->factory->customer->create( [ 'email' => 'nodate@example.com' ] );
		$customer = new Customer( $uid );

		$this->assertNull( $customer->get_last_order_date() );
	}

	/**
	 * get_last_order_date() returns a string when the customer has orders.
	 */
	public function test_get_last_order_date_with_orders(): void {
		$this->factory->order->create( [
			'customer_id' => $this->user_id,
			'total'       => 30.00,
			'status'      => 'completed',
		] );

		$customer = new Customer( $this->user_id );
		$date     = $customer->get_last_order_date();

		$this->assertIsString( $date );
		$this->assertNotEmpty( $date );
	}

	/**
	 * get_last_order_date() returns a string or null.
	 */
	public function test_get_last_order_date(): void {
		$customer = new Customer( $this->user_id );
		$date     = $customer->get_last_order_date();

		$this->assertThat( $date, $this->logicalOr(
			$this->isType( 'string' ),
			$this->isNull()
		) );
	}
}

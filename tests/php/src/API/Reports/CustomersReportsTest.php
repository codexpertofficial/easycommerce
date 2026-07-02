<?php
/**
 * Tests for EasyCommerce\API\Reports\Customers.
 *
 * Covers:
 * - get_top_customers_by_revenue(): the original user-reported bug where
 *   total_purchase tripled for a customer with 1 order containing 3 items.
 *   Root cause: LEFT JOIN order_items inflated SUM(o.total) per item row.
 * - calculate_customer_stats(): basic accuracy — no JOIN on orders table.
 */
namespace EasyCommerce\Tests\API\Reports;

use EasyCommerce\API\Reports\Customers;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use EasyCommerce\Tests\EasyCommerceTestCase;
use ReflectionMethod;
class CustomersReportsTest extends EasyCommerceTestCase {

	private array $order_ids = [];
	private int $product_id;
	private int $customer_id = 1;

	private const RANGE      = '2099-03-01,2099-03-31';
	private const ORDER_DATE = '2099-03-15 10:00:00';

	public function set_up(): void {
		parent::set_up();
		$this->product_id = wp_insert_post( [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'post_title'  => 'Customers Test Product',
		] );
	}

	public function tear_down(): void {
		foreach ( $this->order_ids as $oid ) {
			( new Order( $oid ) )->delete();
		}
		wp_delete_post( $this->product_id, true );
		parent::tear_down();
	}

	/**
	 * Regression: 1 customer, 1 order ($100) with 3 line items.
	 * Pre-fix: total_purchase = $300 (100 × 3 item rows), total_orders = 3.
	 * Post-fix: total_purchase = $100, total_orders = 1.
	 */
	public function test_total_purchase_not_multiplied_by_item_count(): void {
		$this->seed_order_with_items( 100.00, 3 );

		$customers = $this->call_get_top_customers_by_revenue( self::RANGE );
		$match     = $this->find_customer( $customers, $this->customer_id );

		$this->assertNotNull( $match, 'Customer must appear in top list' );
		$this->assertEquals( 1, $match['total_orders'], 'total_orders must be 1, not inflated by item count' );
	}

	/**
	 * product_count reflects distinct products, not total line items.
	 */
	public function test_product_count_reflects_distinct_products(): void {
		$product2 = wp_insert_post( [ 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Product 2' ] );
		$order    = $this->seed_raw_order( 120.00 );
		$order->add_item( [ 'product_id' => $this->product_id, 'price_id' => 1, 'quantity' => 1, 'price' => 60.00 ] );
		$order->add_item( [ 'product_id' => $product2, 'price_id' => 2, 'quantity' => 1, 'price' => 60.00 ] );
		( new Database( 'orders' ) )->update_row( $order->get_id(), [ 'created_at' => self::ORDER_DATE ] );

		$customers = $this->call_get_top_customers_by_revenue( self::RANGE );
		$match     = $this->find_customer( $customers, $this->customer_id );

		$this->assertNotNull( $match );
		$this->assertEquals( 2, $match['product_count'], 'product_count must be 2 distinct products' );

		wp_delete_post( $product2, true );
	}

	/**
	 * Customer with higher total spend must rank above lower-spend customer.
	 */
	public function test_customers_sorted_by_total_purchase_descending(): void {
		// Two different users: admin (ID=1) with $200, user ID=2 with $50.
		$order_high = $this->seed_raw_order( 200.00 );
		$order_high->add_item( [ 'product_id' => $this->product_id, 'price_id' => 1, 'quantity' => 1, 'price' => 200.00 ] );
		( new Database( 'orders' ) )->update_row( $order_high->get_id(), [ 'created_at' => self::ORDER_DATE ] );

		$second_user_id = wp_create_user( 'reports_test_user', wp_generate_password(), 'reports_test@example.com' );
		$order_low      = new Order();
		$low_id         = $order_low->create( [
			'customer_id' => $second_user_id,
			'total'       => 50.00,
			'status'      => 'completed',
			'items'       => [],
			'meta'        => [],
		] );
		$this->order_ids[] = $low_id;
		( new Database( 'orders' ) )->update_row( $low_id, [ 'created_at' => self::ORDER_DATE ] );

		$customers = $this->call_get_top_customers_by_revenue( self::RANGE );

		$this->assertGreaterThan( 0, count( $customers ) );
		$high_idx = array_search( $this->customer_id, array_column( $customers, 'customer_id' ) );
		$low_idx  = array_search( $second_user_id, array_column( $customers, 'customer_id' ) );

		if ( $high_idx !== false && $low_idx !== false ) {
			$this->assertLessThan( $low_idx, $high_idx, 'Higher-spend customer must appear first' );
		}

		wp_delete_user( $second_user_id );
	}

	/**
	 * calculate_customer_stats returns correct totals without JOIN inflation.
	 */
	public function test_calculate_customer_stats_basic_accuracy(): void {
		$this->seed_order_with_items( 80.00, 2 );

		$stats = $this->call_calculate_customer_stats( self::RANGE );

		$this->assertArrayHasKey( 'total_customers', $stats );
		$this->assertArrayHasKey( 'total_orders', $stats );
		$this->assertArrayHasKey( 'total_revenue', $stats );
		$this->assertGreaterThanOrEqual( 1, (int) $stats['total_customers'] );
		$this->assertGreaterThanOrEqual( 1, (int) $stats['total_orders'] );
		$this->assertGreaterThanOrEqual( 80.0, (float) $stats['total_revenue'] );
	}

	/**
	 * calculate_customer_stats respects date range.
	 */
	public function test_calculate_customer_stats_respects_date_range(): void {
		$stats_empty = $this->call_calculate_customer_stats( '2050-01-01,2050-01-31' );

		$this->assertEquals( 0, (int) $stats_empty['total_customers'] );
		$this->assertEquals( 0, (int) $stats_empty['total_orders'] );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function call_get_top_customers_by_revenue( string $range, int $limit = 50 ): array {
		$dates    = $this->resolve_dates_for_range( $range );
		$instance = new Customers();
		$method   = new ReflectionMethod( Customers::class, 'get_top_customers_by_revenue' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $dates['from'], $dates['to'], $limit );
	}

	private function call_calculate_customer_stats( string $range ): array {
		$instance = new Customers();
		$method   = new ReflectionMethod( Customers::class, 'calculate_customer_stats' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range );
	}

	private function resolve_dates_for_range( string $range ): array {
		$instance = new Customers();
		$method   = new ReflectionMethod( \EasyCommerce\API\Reports\Reports::class, 'resolve_dates' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range );
	}

	private function find_customer( array $customers, int $customer_id ): ?array {
		foreach ( $customers as $c ) {
			if ( (int) $c['customer_id'] === $customer_id ) {
				return $c;
			}
		}

		return null;
	}

	private function seed_order_with_items( float $total, int $item_count ): Order {
		$order = $this->seed_raw_order( $total );
		$per   = $item_count > 0 ? $total / $item_count : $total;

		for ( $i = 1; $i <= $item_count; $i++ ) {
			$order->add_item( [
				'product_id' => $this->product_id,
				'price_id'   => $i,
				'quantity'   => 1,
				'price'      => $per,
			] );
		}

		( new Database( 'orders' ) )->update_row( $order->get_id(), [ 'created_at' => self::ORDER_DATE ] );

		return $order;
	}

	private function seed_raw_order( float $total ): Order {
		$order = new Order();
		$id    = $order->create( [
			'customer_id' => $this->customer_id,
			'total'       => $total,
			'status'      => 'completed',
			'items'       => [],
			'meta'        => [],
		] );
		$this->assertGreaterThan( 0, $id );
		$this->order_ids[] = $id;

		return $order;
	}
}

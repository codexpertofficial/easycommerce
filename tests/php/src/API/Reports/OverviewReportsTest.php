<?php
/**
 * Tests for EasyCommerce\API\Reports\Overview.
 *
 * Covers:
 * - get_customer_metrics(): JOIN-multiplication bug — 1 order with 3 items
 *   previously inflated total_orders and total_revenue by the item count.
 * - get_top_selling_products(): revenue value rounded to 2 decimal places.
 * - get_top_customers(): ranked correctly by total spent.
 */
namespace EasyCommerce\Tests\API\Reports;

use EasyCommerce\API\Reports\Overview;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use EasyCommerce\Tests\EasyCommerceTestCase;
use ReflectionMethod;
class OverviewReportsTest extends EasyCommerceTestCase {

	private array $order_ids  = [];
	private int $product_id;
	private int $customer_id = 1;

	private const RANGE      = '2035-02-01,2035-02-28';
	private const ORDER_DATE = '2035-02-15 10:00:00';

	public function set_up(): void {
		parent::set_up();
		$this->product_id = wp_insert_post( [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'post_title'  => 'Overview Test Product',
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
	 * Bug: total_orders was counted as 3 (one per item row), total_revenue = $300.
	 * Fix: total_orders = 1, total_revenue = $100.
	 */
	public function test_get_customer_metrics_no_inflation_from_multiple_items(): void {
		$this->seed_order_with_items( 100.00, 3 );

		$metrics = $this->call_get_customer_metrics( self::RANGE );

		$this->assertEquals( 1.0, $metrics['orders_per_customer'], 'orders_per_customer must equal 1, not item count' );
		$this->assertEquals( 100.0, $metrics['aov_per_customer'], 'aov_per_customer must equal order total, not order total × item count' );
	}

	/**
	 * items_per_customer reflects the sum of quantities across all line items.
	 */
	public function test_get_customer_metrics_items_per_customer_uses_quantity_sum(): void {
		$order = $this->seed_raw_order( 90.00 );
		$order->add_item( [ 'product_id' => $this->product_id, 'price_id' => 1, 'quantity' => 2, 'price' => 40.00 ] );
		$order->add_item( [ 'product_id' => $this->product_id, 'price_id' => 2, 'quantity' => 1, 'price' => 50.00 ] );
		( new Database( 'orders' ) )->update_row( $order->get_id(), [ 'created_at' => self::ORDER_DATE ] );

		$metrics = $this->call_get_customer_metrics( self::RANGE );

		// 2 + 1 = 3 items in total for the one customer.
		$this->assertEquals( 3.0, $metrics['items_per_customer'] );
	}

	/**
	 * Two orders for the same customer → orders_per_customer = 2.
	 */
	public function test_get_customer_metrics_multiple_orders_for_same_customer(): void {
		$this->seed_order_with_items( 50.00, 1 );
		$this->seed_order_with_items( 75.00, 1 );

		$metrics = $this->call_get_customer_metrics( self::RANGE );

		$this->assertEquals( 2.0, $metrics['orders_per_customer'] );
		$this->assertEquals( 125.0 / 1, $metrics['aov_per_customer'] );
	}

	/**
	 * get_top_customers returns an array of customer objects sorted by spend.
	 */
	public function test_get_top_customers_returns_ranked_list(): void {
		$this->seed_order_with_items( 200.00, 1 );

		$customers = $this->call_get_top_customers( self::RANGE );

		$this->assertIsArray( $customers );

		if ( ! empty( $customers ) ) {
			$first = $customers[0];
			$this->assertArrayHasKey( 'id', $first );
			$this->assertArrayHasKey( 'name', $first );
			$this->assertArrayHasKey( 'orders', $first );
			$this->assertArrayHasKey( 'spent', $first );
		}
	}

	/**
	 * get_top_selling_products returns products with a numeric revenue value.
	 */
	public function test_get_top_selling_products_returns_valid_structure(): void {
		$this->seed_order_with_items( 99.99, 2 );

		$products = $this->call_get_top_selling_products( self::RANGE );

		$this->assertIsArray( $products );

		if ( ! empty( $products ) ) {
			$first = $products[0];
			$this->assertArrayHasKey( 'rank', $first );
			$this->assertArrayHasKey( 'product_id', $first );
			$this->assertArrayHasKey( 'name', $first );
			$this->assertArrayHasKey( 'unit_sold', $first );
			$this->assertArrayHasKey( 'revenue', $first );

			// Revenue must be a numeric with at most 2 decimal places.
			$revenue = (float) $first['revenue'];
			$this->assertEquals( $revenue, round( $revenue, 2 ), 'Revenue must be rounded to 2 decimal places' );
		}
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function call_get_customer_metrics( string $range ): array {
		$instance = new Overview();
		$method   = new ReflectionMethod( Overview::class, 'get_customer_metrics' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range );
	}

	private function call_get_top_customers( string $range, int $limit = 10 ): array {
		$instance = new Overview();
		$method   = new ReflectionMethod( Overview::class, 'get_top_customers' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range, $limit );
	}

	private function call_get_top_selling_products( string $range, int $limit = 5 ): array {
		$instance = new Overview();
		$method   = new ReflectionMethod( Overview::class, 'get_top_selling_products' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range, $limit );
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

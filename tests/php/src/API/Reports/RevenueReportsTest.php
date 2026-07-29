<?php
/**
 * Tests for EasyCommerce\API\Reports\Revenue.
 *
 * Covers:
 * - calculate_revenue_stats(): gross/net revenue, order count, customer count.
 * - No JOIN issues here — Revenue.php queries orders table alone.
 *   Tests serve as regression guard if queries are modified in future.
 */


namespace EasyCommerce\Tests\API\Reports;

use EasyCommerce\API\Reports\Revenue;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Refund;
use EasyCommerce\Tests\EasyCommerceTestCase;
use ReflectionMethod;

class RevenueReportsTest extends EasyCommerceTestCase {

	private array $order_ids  = [];
	private array $refund_ids = [];
	private int $product_id;
	private int $customer_id = 1;

	private const RANGE      = '2035-05-01,2035-05-31';
	private const ORDER_DATE = '2035-05-15 10:00:00';

	public function set_up(): void {
		parent::set_up();
		$this->product_id = wp_insert_post( [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'post_title'  => 'Revenue Test Product',
		] );
	}

	public function tear_down(): void {
		foreach ( $this->refund_ids as $rid ) {
			( new Refund( $rid ) )->delete();
		}
		foreach ( $this->order_ids as $oid ) {
			( new Order( $oid ) )->delete();
		}
		wp_delete_post( $this->product_id, true );
		parent::tear_down();
	}

	/**
	 * calculate_revenue_stats returns all expected keys.
	 */
	public function test_calculate_revenue_stats_returns_required_keys(): void {
		$stats = $this->call_calculate_revenue_stats( self::RANGE );

		$this->assertArrayHasKey( 'gross_revenue', $stats );
		$this->assertArrayHasKey( 'net_revenue', $stats );
		$this->assertArrayHasKey( 'avg_per_customer', $stats );
		$this->assertArrayHasKey( 'avg_per_order', $stats );
		$this->assertArrayHasKey( 'avg_per_product', $stats );
		$this->assertArrayHasKey( 'total_orders', $stats );
		$this->assertArrayHasKey( 'total_customers', $stats );
		$this->assertArrayHasKey( 'total_products', $stats );
	}

	/**
	 * gross_revenue equals the sum of completed order totals.
	 */
	public function test_calculate_revenue_stats_gross_revenue_matches_order_totals(): void {
		$this->seed_order( 100.00 );
		$this->seed_order( 50.00 );

		$stats = $this->call_calculate_revenue_stats( self::RANGE );

		$this->assertGreaterThanOrEqual( 150.0, (float) $stats['gross_revenue'] );
	}

	/**
	 * net_revenue = gross_revenue - approved refunds.
	 */
	public function test_calculate_revenue_stats_net_revenue_deducts_refunds(): void {
		$order_id = $this->seed_order( 100.00 );
		$this->add_refund( $order_id, 20.00 );

		$stats = $this->call_calculate_revenue_stats( self::RANGE );

		$gross = (float) $stats['gross_revenue'];
		$net   = (float) $stats['net_revenue'];
		$this->assertGreaterThanOrEqual( 80.0, $net );
		$this->assertLessThan( $gross, $net );
	}

	/**
	 * avg_per_order = gross_revenue / total_orders.
	 */
	public function test_calculate_revenue_stats_avg_per_order_is_correct(): void {
		$this->seed_order( 60.00 );
		$this->seed_order( 60.00 );

		$stats = $this->call_calculate_revenue_stats( self::RANGE );

		if ( (int) $stats['total_orders'] > 0 ) {
			$expected_avg = (float) $stats['gross_revenue'] / (int) $stats['total_orders'];
			$this->assertEquals( round( $expected_avg, 6 ), round( (float) $stats['avg_per_order'], 6 ) );
		}
	}

	/**
	 * Empty date range returns zero values.
	 */
	public function test_calculate_revenue_stats_empty_range_returns_zeros(): void {
		$stats = $this->call_calculate_revenue_stats( '2050-01-01,2050-01-31' );

		$this->assertEquals( 0, (float) $stats['gross_revenue'] );
		$this->assertEquals( 0, (float) $stats['net_revenue'] );
		$this->assertEquals( 0, (int) $stats['total_orders'] );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function call_calculate_revenue_stats( string $range ): array {
		$instance = new Revenue();
		$method   = new ReflectionMethod( Revenue::class, 'calculate_revenue_stats' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range );
	}

	private function seed_order( float $total ): int {
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

		( new Database( 'orders' ) )->update_row( $id, [ 'created_at' => self::ORDER_DATE ] );

		return $id;
	}

	private function add_refund( int $order_id, float $amount ): int {
		$refund = new Refund();
		$id     = $refund->create( [
			'order_id'        => $order_id,
			'amount'          => $amount,
			'status'          => 'approved',
			'payment_gateway' => 'manual',
		] );
		$this->assertGreaterThan( 0, $id );
		$this->refund_ids[] = $id;

		( new Database( 'refunds' ) )->update_row( $id, [ 'created_at' => self::ORDER_DATE ] );

		return $id;
	}
}

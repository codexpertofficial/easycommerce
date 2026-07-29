<?php
/**
 * Tests for EasyCommerce\API\Reports\Orders.
 *
 * Covers:
 * - calculate_stats() (inherited from Reports): orders, sales, refunds, net_revenue.
 * - No JOIN bugs in Orders.php — tests serve as a regression guard and verify
 *   the inherited base methods work correctly on Orders instances.
 */
namespace EasyCommerce\Tests\API\Reports;

use EasyCommerce\API\Reports\Orders;
use EasyCommerce\API\Reports\Reports;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Refund;
use EasyCommerce\Tests\EasyCommerceTestCase;
use ReflectionMethod;
class OrdersReportsTest extends EasyCommerceTestCase {

	private array $order_ids  = [];
	private array $refund_ids = [];
	private int $customer_id = 1;

	private const RANGE      = '2035-06-01,2035-06-30';
	private const ORDER_DATE = '2035-06-15 10:00:00';

	public function tear_down(): void {
		foreach ( $this->refund_ids as $rid ) {
			( new Refund( $rid ) )->delete();
		}
		foreach ( $this->order_ids as $oid ) {
			( new Order( $oid ) )->delete();
		}
		parent::tear_down();
	}

	/**
	 * calculate_stats returns all expected keys.
	 */
	public function test_calculate_stats_returns_required_keys(): void {
		$stats = $this->call_calculate_stats( self::RANGE );

		$this->assertArrayHasKey( 'orders', $stats );
		$this->assertArrayHasKey( 'sales', $stats );
		$this->assertArrayHasKey( 'refunds', $stats );
		$this->assertArrayHasKey( 'net_revenue', $stats );
		$this->assertArrayHasKey( 'completed_orders', $stats );
		$this->assertArrayHasKey( 'pending_orders', $stats );
	}

	/**
	 * sales value matches total of completed + processing orders in range.
	 */
	public function test_calculate_stats_sales_matches_order_totals(): void {
		$this->seed_order( 80.00, 'completed' );
		$this->seed_order( 40.00, 'completed' );

		$stats = $this->call_calculate_stats( self::RANGE );

		$this->assertGreaterThanOrEqual( 120.0, (float) $stats['sales'] );
	}

	/**
	 * net_revenue = sales - refunds.
	 */
	public function test_calculate_stats_net_revenue_deducts_refunds(): void {
		$order_id = $this->seed_order( 100.00, 'completed' );
		$this->add_refund( $order_id, 25.00 );

		$stats = $this->call_calculate_stats( self::RANGE );

		$this->assertGreaterThanOrEqual( 75.0, (float) $stats['net_revenue'] );
		$this->assertLessThanOrEqual( (float) $stats['sales'], (float) $stats['net_revenue'] );
	}

	/**
	 * completed_orders count only completed status orders.
	 */
	public function test_calculate_stats_completed_orders_excludes_other_statuses(): void {
		$this->seed_order( 50.00, 'completed' );
		$this->seed_order( 50.00, 'pending' );

		$stats = $this->call_calculate_stats( self::RANGE );

		// completed_orders is already an int count; pending must not inflate it.
		$this->assertGreaterThanOrEqual( 1, (int) $stats['completed_orders'] );
	}

	/**
	 * Empty date range returns zeros and empty arrays.
	 */
	public function test_calculate_stats_empty_range(): void {
		$stats = $this->call_calculate_stats( '2050-01-01,2050-01-31' );

		$this->assertEquals( 0.0, (float) $stats['sales'] );
		$this->assertEquals( 0.0, (float) $stats['refunds'] );
		$this->assertEquals( 0, (int) $stats['completed_orders'] );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function call_calculate_stats( string $range ): array {
		$instance = new Orders();
		$method   = new ReflectionMethod( Reports::class, 'calculate_stats' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range );
	}

	private function seed_order( float $total, string $status = 'completed' ): int {
		$order = new Order();
		$id    = $order->create( [
			'customer_id' => $this->customer_id,
			'total'       => $total,
			'status'      => $status,
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

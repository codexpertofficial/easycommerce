<?php
/**
 * Regression tests for EasyCommerce\API\Reports\Reports (base class).
 *
 * Covers: get_refund_stats() JOIN-multiplication bug — pre-fix, a single refund
 * was multiplied by the number of order_items rows that shared the same product_id.
 */


namespace EasyCommerce\Tests\API\Reports;

use EasyCommerce\API\Reports\Overview;
use EasyCommerce\API\Reports\Reports;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Refund;
use EasyCommerce\Tests\EasyCommerceTestCase;
use ReflectionMethod;

class ReportsBaseTest extends EasyCommerceTestCase {

	private array $order_ids  = [];
	private array $refund_ids = [];
	private int $product_id;

	private const RANGE      = '2099-01-01,2099-01-31';
	private const ORDER_DATE = '2099-01-15 10:00:00';

	public function set_up(): void {
		parent::set_up();
		$this->product_id = wp_insert_post( [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'post_title'  => 'ReportsBase Test Product',
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
	 * One order with 2 line items of the same product + 1 refund of $40.
	 * Bug: old JOIN query returned $80 (40 × 2 items). Fix: should return $40.
	 */
	public function test_get_refund_stats_does_not_multiply_when_order_has_multiple_line_items(): void {
		$order_id = $this->seed_order_with_items( 100.00, 2 );
		$this->add_refund( $order_id, 40.00 );

		$stats = $this->call_get_refund_stats( self::RANGE, $this->product_id );

		$this->assertEquals( 40.0, (float) $stats['refund_amount'], 'refund_amount must not be multiplied by line-item count' );
		$this->assertEquals( 1, (int) $stats['refund_count'] );
	}

	/**
	 * Three line items of the same product + 2 refunds. Total must be sum
	 * of both refunds once each, not × 3.
	 */
	public function test_get_refund_stats_sums_multiple_refunds_without_inflation(): void {
		$order_id = $this->seed_order_with_items( 150.00, 3 );
		$this->add_refund( $order_id, 25.00 );
		$this->add_refund( $order_id, 15.00 );

		$stats = $this->call_get_refund_stats( self::RANGE, $this->product_id );

		$this->assertEquals( 40.0, (float) $stats['refund_amount'] );
		$this->assertEquals( 2, (int) $stats['refund_count'] );
	}

	/**
	 * Refund outside the date range must not be counted.
	 */
	public function test_get_refund_stats_excludes_refunds_outside_date_range(): void {
		$order_id  = $this->seed_order_with_items( 100.00, 2 );
		$refund_id = $this->add_refund( $order_id, 40.00 );
		// Push refund outside the 2099-01 window.
		( new Database( 'refunds' ) )->update_row( $refund_id, [ 'created_at' => '2098-06-01 10:00:00' ] );

		$stats = $this->call_get_refund_stats( self::RANGE, $this->product_id );

		$this->assertEquals( 0.0, (float) $stats['refund_amount'] );
		$this->assertEquals( 0, (int) $stats['refund_count'] );
	}

	/**
	 * all_time=true bypasses the date filter.
	 */
	public function test_get_refund_stats_all_time_ignores_date_filter(): void {
		$order_id  = $this->seed_order_with_items( 100.00, 2 );
		$refund_id = $this->add_refund( $order_id, 40.00 );
		( new Database( 'refunds' ) )->update_row( $refund_id, [ 'created_at' => '2098-06-01 10:00:00' ] );

		$stats = $this->call_get_refund_stats( null, $this->product_id, true );

		$this->assertEquals( 40.0, (float) $stats['refund_amount'] );
		$this->assertEquals( 1, (int) $stats['refund_count'] );
	}

	/**
	 * Without a product_id scope, all refunds in range are summed.
	 */
	public function test_get_refund_stats_without_product_scope(): void {
		$order_id = $this->seed_order_with_items( 100.00, 1 );
		$this->add_refund( $order_id, 30.00 );

		$stats = $this->call_get_refund_stats( self::RANGE );

		$this->assertGreaterThanOrEqual( 30.0, (float) $stats['refund_amount'] );
		$this->assertGreaterThanOrEqual( 1, (int) $stats['refund_count'] );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function call_get_refund_stats( ?string $range, ?int $product_id = null, bool $all_time = false ): array {
		$instance = new Overview();
		$method   = new ReflectionMethod( Reports::class, 'get_refund_stats' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range, $product_id, $all_time );
	}

	private function seed_order_with_items( float $total, int $item_count ): int {
		$order = new Order();
		$id    = $order->create( [
			'customer_id' => 1,
			'total'       => $total,
			'status'      => 'completed',
			'items'       => [],
			'meta'        => [],
		] );
		$this->assertGreaterThan( 0, $id );
		$this->order_ids[] = $id;

		$per_item = $item_count > 0 ? $total / $item_count : $total;
		for ( $i = 1; $i <= $item_count; $i++ ) {
			$order->add_item( [
				'product_id' => $this->product_id,
				'price_id'   => $i,
				'quantity'   => 1,
				'price'      => $per_item,
			] );
		}

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

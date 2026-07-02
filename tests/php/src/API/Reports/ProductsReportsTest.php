<?php
/**
 * Tests for EasyCommerce\API\Reports\Products.
 *
 * Covers:
 * - get_sold_products(): the refunds subquery previously used INNER JOIN
 *   order_items, inflating SUM(r.amount) when an order contained multiple
 *   variations of the same product. Fix: replaced JOIN with IN subquery.
 * - get_product_sales_stats(): basic shape — sales_count, gross_sales.
 * - calculate_product_stats(): ensures stat keys are present.
 */
namespace EasyCommerce\Tests\API\Reports;

use EasyCommerce\API\Reports\Products;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Refund;
use EasyCommerce\Tests\EasyCommerceTestCase;
use ReflectionMethod;
class ProductsReportsTest extends EasyCommerceTestCase {

	private array $order_ids  = [];
	private array $refund_ids = [];
	private int $product_id;
	private int $customer_id = 1;

	private const RANGE      = '2099-04-01,2099-04-30';
	private const ORDER_DATE = '2099-04-15 10:00:00';

	public function set_up(): void {
		parent::set_up();
		$this->product_id = wp_insert_post( [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'post_title'  => 'Products Test Product',
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
	 * Regression: 1 order with 2 line items of the same product + 1 refund of $30.
	 * Pre-fix JOIN: refunds column = $60 (30 × 2 items). Fix: should be $30.
	 */
	public function test_get_sold_products_refund_not_multiplied_by_item_count(): void {
		$order_id = $this->seed_order_with_items( 100.00, 2 );
		$this->add_refund( $order_id, 30.00 );

		$products = $this->call_get_sold_products( self::RANGE );
		$match    = $this->find_product( $products, $this->product_id );

		$this->assertNotNull( $match, 'Product must appear in sold list' );
		$this->assertEquals( 30.0, (float) $match['refunds'], 'Refund amount must not be multiplied by line-item count' );
	}

	/**
	 * Two refunds on the same order — both should be summed once each.
	 */
	public function test_get_sold_products_multiple_refunds_summed_correctly(): void {
		$order_id = $this->seed_order_with_items( 100.00, 2 );
		$this->add_refund( $order_id, 20.00 );
		$this->add_refund( $order_id, 10.00 );

		$products = $this->call_get_sold_products( self::RANGE );
		$match    = $this->find_product( $products, $this->product_id );

		$this->assertNotNull( $match );
		$this->assertEquals( 30.0, (float) $match['refunds'] );
	}

	/**
	 * unit_sold reflects quantity across all line items, not inflated.
	 */
	public function test_get_sold_products_unit_sold_is_quantity_sum(): void {
		$order = $this->seed_raw_order( 100.00 );
		$order->add_item( [ 'product_id' => $this->product_id, 'price_id' => 1, 'quantity' => 3, 'price' => 60.00 ] );
		$order->add_item( [ 'product_id' => $this->product_id, 'price_id' => 2, 'quantity' => 2, 'price' => 40.00 ] );
		( new Database( 'orders' ) )->update_row( $order->get_id(), [ 'created_at' => self::ORDER_DATE ] );

		$products = $this->call_get_sold_products( self::RANGE );
		$match    = $this->find_product( $products, $this->product_id );

		$this->assertNotNull( $match );
		$this->assertEquals( 5, (int) $match['unit_sold'] );
	}

	/**
	 * get_product_sales_stats returns expected keys.
	 */
	public function test_get_product_sales_stats_returns_required_keys(): void {
		$this->seed_order_with_items( 50.00, 1 );

		$stats = $this->call_get_product_sales_stats( self::RANGE );

		$this->assertArrayHasKey( 'sales_count', $stats );
		$this->assertArrayHasKey( 'gross_sales', $stats );
		$this->assertArrayHasKey( 'refund_count', $stats );
		$this->assertArrayHasKey( 'refund_amount', $stats );
	}

	/**
	 * calculate_product_stats returns expected keys.
	 */
	public function test_calculate_product_stats_returns_required_keys(): void {
		$stats = $this->call_calculate_product_stats( self::RANGE );

		$this->assertArrayHasKey( 'live_products', $stats );
		$this->assertArrayHasKey( 'total_stock', $stats );
		$this->assertArrayHasKey( 'out_of_stock', $stats );
		$this->assertArrayHasKey( 'gross_sales', $stats );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function call_get_sold_products( string $range, int $limit = 10, string $order = 'DESC' ): array {
		$instance = new Products();
		$method   = new ReflectionMethod( Products::class, 'get_sold_products' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range, $limit, $order );
	}

	private function call_get_product_sales_stats( string $range ): array {
		$instance = new Products();
		$method   = new ReflectionMethod( Products::class, 'get_product_sales_stats' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range );
	}

	private function call_calculate_product_stats( string $range ): array {
		$instance = new Products();
		$method   = new ReflectionMethod( Products::class, 'calculate_product_stats' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $range );
	}

	private function find_product( array $products, int $product_id ): ?array {
		foreach ( $products as $p ) {
			if ( (int) $p['product_id'] === $product_id ) {
				return $p;
			}
		}

		return null;
	}

	private function seed_order_with_items( float $total, int $item_count ): int {
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

		return $order->get_id();
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

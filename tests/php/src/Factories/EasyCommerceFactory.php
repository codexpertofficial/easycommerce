<?php

namespace EasyCommerce\Tests\Factories;

use WP_UnitTest_Factory;

/**
 * Main fixture factory for EasyCommerce tests.
 *
 * Mirrors dokan-lite/tests/php/src/Factories/DokanFactory.php
 *
 * Usage inside EasyCommerceTestCase:
 *
 *   // Order with line items
 *   $order_id = $this->factory->order->create([
 *       'customer_id' => 1,
 *       'total'       => 100.00,
 *       'status'      => 'completed',
 *       'items'       => [
 *           [ 'product_id' => $pid, 'price_id' => 1, 'quantity' => 2, 'price' => 50.00 ],
 *       ],
 *   ]);
 *
 *   // Refund on an order
 *   $refund_id = $this->factory->refund->create([
 *       'order_id' => $order_id,
 *       'amount'   => 25.00,
 *   ]);
 *
 *   // Product (WordPress post + EC Product model)
 *   $product_id = $this->factory->product->create(['title' => 'My Product']);
 *
 *   // Product variation
 *   $var_id = $this->factory->variation->create([
 *       'product_id' => $product_id,
 *       'sku'        => 'SKU-001',
 *       'price'      => 29.99,
 *   ]);
 *
 *   // Customer (WordPress user)
 *   $customer_id = $this->factory->customer->create(['email' => 'john@example.com']);
 *
 *   // Coupon
 *   $coupon_id = $this->factory->coupon->create([
 *       'code'  => 'SAVE10',
 *       'type'  => 'percentage',
 *       'offer' => 10,
 *   ]);
 */
class EasyCommerceFactory extends WP_UnitTest_Factory {

	/** @var OrderFactory */
	public $order;

	/** @var RefundFactory */
	public $refund;

	/** @var ProductFactory */
	public $product;

	/** @var ProductVariationFactory */
	public $variation;

	/** @var CustomerFactory */
	public $customer;

	/** @var CouponFactory */
	public $coupon;

	public function __construct() {
		parent::__construct();

		$this->order     = new OrderFactory( $this );
		$this->refund    = new RefundFactory( $this );
		$this->product   = new ProductFactory( $this );
		$this->variation = new ProductVariationFactory( $this );
		$this->customer  = new CustomerFactory( $this );
		$this->coupon    = new CouponFactory( $this );
	}
}

<?php

namespace EasyCommerce\Tests\Factories;

use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use WP_UnitTest_Factory_For_Thing;

/**
 * Factory for EasyCommerce order fixtures.
 *
 * $order_id = $this->factory->order->create([
 *     'customer_id' => 1,
 *     'total'       => 100.00,
 *     'status'      => 'completed',
 *     'created_at'  => '2099-01-15 10:00:00',   // optional: override auto timestamp
 *     'items'       => [                          // optional: line items to add
 *         [ 'product_id' => 5, 'price_id' => 1, 'quantity' => 2, 'price' => 50.00 ],
 *     ],
 * ]);
 */
class OrderFactory extends WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		// Only scalar defaults belong here: WP_UnitTest_Factory_For_Thing::generate_args()
		// (WP 6.9+) returns a WP_Error for any non-scalar, non-generator default, which
		// then surfaces as "Cannot use object of type WP_Error as array" in create_object().
		// 'items' and 'meta' are handled (and default to []) inside create_object() instead.
		$this->default_generation_definitions = [
			'customer_id' => 1,
			'total'       => 100.00,
			'status'      => 'completed',
		];
	}

	/**
	 * @param array $args
	 * @return int Order ID.
	 */
	public function create_object( $args ): int {
		$order = new Order();

		$id = $order->create( [
			'customer_id' => $args['customer_id'] ?? 1,
			'total'       => $args['total'] ?? 100.00,
			'status'      => $args['status'] ?? 'completed',
			'items'       => [],
			'meta'        => $args['meta'] ?? [],
		] );

		if ( ! $id ) {
			return 0;
		}

		foreach ( $args['items'] ?? [] as $item ) {
			$order->add_item( $item );
		}

		if ( ! empty( $args['created_at'] ) ) {
			( new Database( 'orders' ) )->update_row( $id, [ 'created_at' => $args['created_at'] ] );
		}

		return $id;
	}

	public function update_object( $id, $fields ) {
		return ( new Order( $id ) )->update( $fields );
	}

	public function get_object_by_id( $id ): Order {
		return new Order( $id );
	}
}

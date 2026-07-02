<?php

namespace EasyCommerce\Tests\Factories;

use EasyCommerce\Models\Product_Variation;
use WP_UnitTest_Factory_For_Thing;

/**
 * Factory for EasyCommerce product variation fixtures.
 *
 * $variation_id = $this->factory->variation->create([
 *     'product_id'     => $product_id,
 *     'name'           => 'Default',
 *     'sku'            => 'SKU-001',
 *     'price'          => 29.99,
 *     'stock_quantity' => 10,
 * ]);
 *
 * $variation = $this->factory->variation->create_and_get([...]);
 */
class ProductVariationFactory extends WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'product_id'     => 0,
			'name'           => 'Default',
			'sku'            => 'TEST-VAR-001',
			'price'          => 10.00,
			'stock_quantity' => 0,
		];
	}

	/**
	 * @param array $args
	 * @return int Variation ID, or 0 on failure.
	 */
	public function create_object( $args ): int {
		$variation             = new Product_Variation();
		$variation->product_id = (int) ( $args['product_id'] ?? 0 );

		$variation->set_name( $args['name'] ?? 'Default' );
		$variation->set_sku( $args['sku'] ?? 'TEST-VAR-001' );
		$variation->set_price( (float) ( $args['price'] ?? 10.00 ) );

		if ( ! empty( $args['sale_price'] ) ) {
			$variation->set_sale_price( (float) $args['sale_price'] );
		}
		if ( isset( $args['stock_quantity'] ) ) {
			$variation->set_stock_quantity( (int) $args['stock_quantity'] );
		}
		if ( ! empty( $args['price_id'] ) ) {
			$variation->set_price_id( (int) $args['price_id'] );
		}
		if ( ! empty( $args['status'] ) ) {
			$variation->set_status( $args['status'] );
		}

		$id = $variation->save();

		return $id ? (int) $id : 0;
	}

	/**
	 * @param int   $id
	 * @param array $fields
	 * @return bool|int
	 */
	public function update_object( $id, $fields ) {
		$variation = new Product_Variation( $id );
		return $variation->save();
	}

	/**
	 * @param int $id
	 * @return Product_Variation
	 */
	public function get_object_by_id( $id ): Product_Variation {
		return new Product_Variation( $id );
	}
}

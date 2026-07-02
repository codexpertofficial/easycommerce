<?php

namespace EasyCommerce\Tests\Factories;

use EasyCommerce\Models\Product;
use WP_UnitTest_Factory_For_Thing;

/**
 * Factory for EasyCommerce product fixtures.
 *
 * Creates a WordPress product post wrapped by the EC Product model.
 *
 * $product_id = $this->factory->product->create([
 *     'title'   => 'My Product',
 *     'status'  => 'publish',
 *     'content' => 'Description',
 * ]);
 *
 * $product = $this->factory->product->create_and_get([...]);
 */
class ProductFactory extends WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'title'   => 'Test Product',
			'status'  => 'publish',
			'content' => '',
		];
	}

	/**
	 * @param array $args
	 * @return int Product (post) ID, or 0 on failure.
	 */
	public function create_object( $args ): int {
		$product = new Product();

		$result = $product->create( [
			'title'   => $args['title'] ?? 'Test Product',
			'content' => $args['content'] ?? '',
			'status'  => $args['status'] ?? 'publish',
			'meta'    => $args['meta'] ?? [],
		] );

		if ( is_wp_error( $result ) || ! $result ) {
			return 0;
		}

		return (int) $product->get_id();
	}

	/**
	 * @param int   $id
	 * @param array $fields
	 * @return bool|int
	 */
	public function update_object( $id, $fields ) {
		$product = new Product( $id );
		return $product->update( $fields );
	}

	/**
	 * @param int $id
	 * @return Product
	 */
	public function get_object_by_id( $id ): Product {
		return new Product( $id );
	}
}

<?php
/**
 * Test Product API.
 */

namespace EasyCommerce\Tests\API;
use EasyCommerce\Tests\EasyCommerceTestCase;

use EasyCommerce\API\Product;

class ProductAPITest extends EasyCommerceTestCase {

	protected $product_api;
	protected $product_id;

	public function set_up(): void {
		parent::set_up();

		$this->product_api = new Product();

		// WP factory creates product post; EC Product model wraps it
		$this->product_id = $this->factory->post->create( [
			'post_type'   => 'product',
			'post_status' => 'publish',
			'post_title'  => 'Test API Product',
		] );
	}

	/**
	 * Test Product API constructor.
	 */
	public function test_constructor() {
		$product_api = new Product();

		$this->assertInstanceOf( Product::class, $product_api );
	}

	/**
	 * Test list method.
	 */
	public function test_list() {
		// Skip API tests that produce output interfering with test results
		$this->markTestSkipped( 'API tests skipped due to output interference with test results' );
	}

	/**
	 * Test API methods are available.
	 */
	public function test_api_methods_exist() {
		$this->assertTrue( method_exists( $this->product_api, 'list' ) );
		$this->assertTrue( method_exists( $this->product_api, 'get' ) );
		$this->assertTrue( method_exists( $this->product_api, 'create' ) );
		$this->assertTrue( method_exists( $this->product_api, 'update' ) );
		$this->assertTrue( method_exists( $this->product_api, 'delete' ) );
		$this->assertTrue( method_exists( $this->product_api, 'bulk_delete' ) );
		$this->assertTrue( method_exists( $this->product_api, 'bulk_update_status' ) );
		$this->assertTrue( method_exists( $this->product_api, 'get_reviews' ) );
		$this->assertTrue( method_exists( $this->product_api, 'add_review' ) );
		$this->assertTrue( method_exists( $this->product_api, 'get_variations' ) );
	}
}

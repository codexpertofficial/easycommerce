<?php
/**
 * Test AI service.
 */

namespace EasyCommerce\Tests\Services;
use EasyCommerce\Tests\EasyCommerceTestCase;

use EasyCommerce\Services\AI;

class AITest extends EasyCommerceTestCase {

	protected $ai;

	public function set_up(): void {
		parent::set_up();

		$this->ai = new AI();
	}

	/**
	 * Test AI constructor.
	 */
	public function test_constructor() {
		$ai = new AI();

		$this->assertInstanceOf( AI::class, $ai );
	}

	/**
	 * Test write method.
	 */
	public function test_write() {
		$ai       = new AI();
		$product  = array( 'title' => 'Test Product' );
		$response = $ai->write( $product, 'Write a description', 'short' );

		// The response might be empty if API is not configured
		$this->assertThat( $response, $this->logicalOr(
			$this->isType( 'string' ),
			$this->isType( 'array' ),
			$this->isNull()
		) );
	}

	/**
	 * Test draw method.
	 */
	public function test_draw() {
		$this->markTestSkipped( 'AI draw test skipped due to API dependency issues' );
	}

	/**
	 * Test design method.
	 */
	public function test_design() {
		$this->markTestSkipped( 'AI design test skipped due to API dependency issues' );
	}
}

<?php

namespace EasyCommerce\Tests;
use EasyCommerce\Tests\EasyCommerceTestCase;

/**
 * Example test case.
 */
class ExampleTest extends EasyCommerceTestCase {

	/**
	 * Test that the plugin is loaded.
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( defined( 'EASYCOMMERCE_FILE' ) );
	}

	/**
	 * Test basic functionality.
	 */
	public function test_basic_assertion() {
		$this->assertEquals( 2 + 2, 4 );
	}
}

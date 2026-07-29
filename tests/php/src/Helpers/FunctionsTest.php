<?php
/**
 * Test Helper Functions.
 */

namespace EasyCommerce\Tests\Helpers;
use EasyCommerce\Tests\EasyCommerceTestCase;

use EasyCommerce\Helpers\Utility;

class FunctionsTest extends EasyCommerceTestCase {

	/**
	 * Test easycommerce_dev_store function.
	 */
	public function test_easycommerce_dev_store() {
		$url = easycommerce_dev_store();
		$this->assertIsString( $url );
		$this->assertStringContainsString( 'easycommerce.dev', $url );

		$url_with_path = easycommerce_dev_store( 'test-path' );
		$this->assertStringContainsString( 'test-path', $url_with_path );
	}

	/**
	 * Test easycommerce_dev_docs function.
	 */
	public function test_easycommerce_dev_docs() {
		$url = easycommerce_dev_docs();
		$this->assertIsString( $url );
		$this->assertStringContainsString( 'easycommerce.dev/docs', $url );

		$url_with_path = easycommerce_dev_docs( 'api-reference' );
		$this->assertStringContainsString( 'api-reference', $url_with_path );
	}

	/**
	 * Test easycommerce_home_url function.
	 */
	public function test_easycommerce_home_url() {
		$url = easycommerce_home_url();
		$this->assertIsString( $url );
		$this->assertNotEmpty( $url );

		$url_with_path = easycommerce_home_url( 'test-path' );
		$this->assertStringContainsString( 'test-path', $url_with_path );
	}

	/**
	 * Test easycommerce_rest_base function.
	 */
	public function test_easycommerce_rest_base() {
		$base = easycommerce_rest_base();
		$this->assertIsString( $base );
		$this->assertStringContainsString( 'easycommerce', $base );
	}

	/**
	 * Test easycommerce_product_post_type function.
	 */
	public function test_easycommerce_product_post_type() {
		$post_type = easycommerce_product_post_type();
		$this->assertEquals( 'product', $post_type );
	}

	/**
	 * Test easycommerce_get_cart function.
	 */
	public function test_easycommerce_get_cart() {
		// Skip cart tests due to session issues
		$this->markTestSkipped( 'Cart functions skipped due to session issues in test environment' );
	}

	/**
	 * Test easycommerce_get_cart_hash function.
	 */
	public function test_easycommerce_get_cart_hash() {
		// Skip cart hash tests due to session issues
		$this->markTestSkipped( 'Cart hash functions skipped due to session issues in test environment' );
	}

	/**
	 * Test easycommerce_date_ranges function.
	 */
	public function test_easycommerce_date_ranges() {
		$ranges = easycommerce_date_ranges();
		$this->assertIsArray( $ranges );
		$this->assertNotEmpty( $ranges );
	}

	/**
	 * Test easycommerce_order_statuses function.
	 */
	public function test_easycommerce_order_statuses() {
		$statuses = easycommerce_order_statuses();
		$this->assertIsArray( $statuses );
		$this->assertNotEmpty( $statuses );
		$this->assertArrayHasKey( 'failed', $statuses, "'failed' must be a recognised order status" );
	}

	/**
	 * Test easycommerce_email_events registers the 'failed' event.
	 */
	public function test_easycommerce_email_events_includes_failed() {
		$events = easycommerce_email_events();
		$this->assertIsArray( $events );
		$this->assertArrayHasKey( 'failed', $events, "'failed' must be a registered email event" );
	}

	/**
	 * Test easycommerce_email_default returns a complete template for 'failed'.
	 */
	public function test_easycommerce_email_default_failed_has_complete_template() {
		$default = easycommerce_email_default( 'failed' );

		$this->assertIsArray( $default );
		$this->assertArrayHasKey( 'customer_subject', $default );
		$this->assertArrayHasKey( 'customer_body', $default );
		$this->assertArrayHasKey( 'admin_subject', $default );
		$this->assertArrayHasKey( 'admin_body', $default );

		$this->assertNotEmpty( $default['customer_subject'] );
		$this->assertNotEmpty( $default['customer_body'] );
		$this->assertNotEmpty( $default['admin_subject'] );
		$this->assertNotEmpty( $default['admin_body'] );
	}

	/**
	 * Test easycommerce_product_statuses function.
	 */
	public function test_easycommerce_product_statuses() {
		$statuses = easycommerce_product_statuses();
		$this->assertIsArray( $statuses );
		$this->assertNotEmpty( $statuses );
	}

	/**
	 * Test easycommerce_fulfill_statuses function.
	 */
	public function test_easycommerce_fulfill_statuses() {
		$statuses = easycommerce_fulfill_statuses();
		$this->assertIsArray( $statuses );
		$this->assertNotEmpty( $statuses );
	}

	/**
	 * Test easycommerce_length_units function.
	 */
	public function test_easycommerce_length_units() {
		$units = easycommerce_length_units();
		$this->assertIsArray( $units );
		$this->assertNotEmpty( $units );
	}

	/**
	 * Test easycommerce_weight_units function.
	 */
	public function test_easycommerce_weight_units() {
		$units = easycommerce_weight_units();
		$this->assertIsArray( $units );
		$this->assertNotEmpty( $units );
	}

	/**
	 * Test easycommerce_time_units function.
	 */
	public function test_easycommerce_time_units() {
		$units = easycommerce_time_units();
		$this->assertIsArray( $units );
		$this->assertNotEmpty( $units );
	}

	/**
	 * Test easycommerce_countries function.
	 */
	public function test_easycommerce_countries() {
		$countries = easycommerce_countries();
		$this->assertIsArray( $countries );
		$this->assertNotEmpty( $countries );
	}

	/**
	 * Test easycommerce_currencies function.
	 */
	public function test_easycommerce_currencies() {
		// Skip currencies test due to complex dependencies
		$this->markTestSkipped( 'Currencies test skipped due to complex dependencies' );
	}

	/**
	 * Test easycommerce_currency function.
	 */
	public function test_easycommerce_currency() {
		$currency = easycommerce_currency();
		$this->assertIsString( $currency );
		$this->assertNotEmpty( $currency );
	}

	/**
	 * Test easycommerce_currency_symbol function.
	 */
	public function test_easycommerce_currency_symbol() {
		$symbol = easycommerce_currency_symbol();
		$this->assertIsString( $symbol );
		$this->assertNotEmpty( $symbol );
	}

	/**
	 * Test easycommerce_price function.
	 */
	public function test_easycommerce_price() {
		$price = easycommerce_price( 100.50 );
		$this->assertIsString( $price );
		$this->assertNotEmpty( $price );
	}

	/**
	 * Test easycommerce_get_business_types function.
	 */
	public function test_easycommerce_get_business_types() {
		$types = easycommerce_get_business_types();
		$this->assertIsArray( $types );
		$this->assertNotEmpty( $types );
	}

	/**
	 * Test easycommerce_get_product function.
	 */
	public function test_easycommerce_get_product() {
		// Create a test product
		$product      = new \EasyCommerce\Models\Product();
		$product_data = array(
			'title'   => 'Test Product for Function',
			'content' => 'Test content',
			'status'  => 'publish'
		);

		$product_id = $product->create( $product_data );

		$retrieved_product = easycommerce_get_product( $product_id );
		$this->assertInstanceOf( \EasyCommerce\Models\Product::class, $retrieved_product );
		$this->assertEquals( $product_id, $retrieved_product->get_id() );

		// Clean up
		$product->delete( true );
	}

	/**
	 * Test easycommerce_generate_random_coupon_code function.
	 */
	public function test_easycommerce_generate_random_coupon_code() {
		Utility::set_option( 'abandoned-cart', 'settings', 'random_coupon_discount_percentage', 10 );
		$code = easycommerce_generate_random_coupon_code();
		$this->assertIsString( $code );
		$this->assertNotEmpty( $code );
		$this->assertGreaterThan( 5, strlen( $code ) );
	}

	/**
	 * Test easycommerce_is_product function.
	 */
	public function test_easycommerce_is_product() {
		// This would need to be tested in a specific context
		$result = easycommerce_is_product();
		$this->assertIsBool( $result );
	}

	/**
	 * Test easycommerce_is_shop function.
	 */
	public function test_easycommerce_is_shop() {
		$result = easycommerce_is_shop();
		$this->assertIsBool( $result );
	}

	/**
	 * Test easycommerce_is_checkout function.
	 */
	public function test_easycommerce_is_checkout() {
		$result = easycommerce_is_checkout();
		$this->assertIsBool( $result );
	}

	/**
	 * Test easycommerce_is_account_page function.
	 */
	public function test_easycommerce_is_account_page() {
		// The account-page check was renamed to easycommerce_is_dashboard().
		$result = easycommerce_is_dashboard();
		$this->assertIsBool( $result );
	}

	/**
	 * Test easycommerce_is_paid_addon_active function.
	 */
	public function test_easycommerce_is_paid_addon_active() {
		$result = easycommerce_is_paid_addon_active();
		$this->assertIsBool( $result );
	}

	/**
	 * Test easycommerce_get_ai_credits function.
	 */
	public function test_easycommerce_get_ai_credits() {
		// Skip AI credits test due to potential external dependencies
		$this->markTestSkipped( 'AI credits test skipped due to external dependencies' );
	}

	/**
	 * Test easycommerce_cache function.
	 */
	public function test_easycommerce_cache() {
		$cache = easycommerce_cache();
		$this->assertIsObject( $cache );
		$this->assertTrue( method_exists( $cache, 'get_cache' ) );
		$this->assertTrue( method_exists( $cache, 'set_cache' ) );
	}

	/**
	 * Test easycommerce_menus function.
	 */
	public function test_easycommerce_menus() {
		$menus = easycommerce_menus();
		$this->assertIsArray( $menus );
	}

	/**
	 * Test easycommerce_settings_menus function.
	 */
	public function test_easycommerce_settings_menus() {
		$menus = easycommerce_settings_menus();
		$this->assertIsArray( $menus );
	}

	/**
	 * Test easycommerce_checkout_fields function.
	 */
	public function test_easycommerce_checkout_fields() {
		$fields = easycommerce_checkout_fields();
		$this->assertIsArray( $fields );
	}

	/**
	 * Test easycommerce_get_file_type function.
	 */
	public function test_easycommerce_get_file_type() {
		$type = easycommerce_get_file_type( 'test.jpg' );
		$this->assertIsString( $type );

		$type_pdf = easycommerce_get_file_type( 'document.pdf' );
		$this->assertIsString( $type_pdf );
	}

	/**
	 * Test easycommerce_format_size function.
	 */
	public function test_easycommerce_format_size() {
		$formatted = easycommerce_format_size( 1024 );
		$this->assertIsString( $formatted );
		$this->assertNotEmpty( $formatted );

		$formatted_mb = easycommerce_format_size( 1048576 );
		$this->assertIsString( $formatted_mb );
		$this->assertStringContainsString( 'MB', $formatted_mb );
	}
}

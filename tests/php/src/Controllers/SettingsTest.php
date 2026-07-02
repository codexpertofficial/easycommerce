<?php
/**
 * Test Settings/Options functionality.
 */

namespace EasyCommerce\Tests\Controllers;
use EasyCommerce\Tests\EasyCommerceTestCase;

use EasyCommerce\Helpers\Utility;

class SettingsTest extends EasyCommerceTestCase {

	protected $test_key = 'test_setting_key';
	protected $test_value = 'test_setting_value';

	public function tear_down(): void {
		// Clean up test options
		delete_option( 'easycommerce_' . $this->test_key );

		parent::tear_down();
	}

	/**
	 * Test get_option method.
	 */
	public function test_get_option() {
		// Test getting non-existent option
		$value = Utility::get_option( 'general', 'business', 'non_existent' );
		$this->assertEquals( '', $value );

		// Test getting existing option
		update_option( 'easycommerce-general-business', array( 'test_key' => 'test_value' ) );
		$value = Utility::get_option( 'general', 'business', 'test_key' );
		$this->assertEquals( 'test_value', $value );

		// Clean up
		delete_option( 'easycommerce-general-business' );
	}

	/**
	 * Test set_option method.
	 */
	public function test_set_option() {
		Utility::set_option( 'general', 'business', $this->test_key, $this->test_value );

		// Verify the option was set
		$value = Utility::get_option( 'general', 'business', $this->test_key );
		$this->assertEquals( $this->test_value, $value );
	}

	/**
	 * Test set_option with array value.
	 */
	public function test_set_option_array() {
		$array_value = array( 'key1' => 'value1', 'key2' => 'value2' );

		Utility::set_option( 'general', 'business', 'array_test', $array_value );

		// Verify the option was set
		$value = Utility::get_option( 'general', 'business', 'array_test' );
		$this->assertEquals( $array_value, $value );
	}

	/**
	 * Test set_option with boolean value.
	 */
	public function test_set_option_boolean() {
		Utility::set_option( 'general', 'business', 'bool_test', true );

		// Verify the option was set
		$value = Utility::get_option( 'general', 'business', 'bool_test' );
		$this->assertTrue( $value );
	}

	/**
	 * Test set_option with integer value.
	 */
	public function test_set_option_integer() {
		Utility::set_option( 'general', 'business', 'int_test', 42 );

		// Verify the option was set
		$value = Utility::get_option( 'general', 'business', 'int_test' );
		$this->assertEquals( 42, $value );
	}

	/**
	 * Test get_option with default value.
	 */
	public function test_get_option_with_default() {
		$default_value = 'default_value';
		$value         = Utility::get_option( 'general', 'business', 'non_existent', $default_value );

		$this->assertEquals( $default_value, $value );
	}

	/**
	 * Test option persistence across requests.
	 */
	public function test_option_persistence() {
		// Set an option
		Utility::set_option( 'test', 'section', 'persistent', 'value' );

		// Get the option in the same request
		$value1 = Utility::get_option( 'test', 'section', 'persistent' );
		$this->assertEquals( 'value', $value1 );

		// Simulate getting it again (in real scenarios this would be a new request)
		$value2 = Utility::get_option( 'test', 'section', 'persistent' );
		$this->assertEquals( 'value', $value2 );

		// Clean up
		delete_option( 'easycommerce_test_section_persistent' );
	}

	/**
	 * Test multiple options in same section.
	 */
	public function test_multiple_options_same_section() {
		Utility::set_option( 'test', 'section', 'option1', 'value1' );
		Utility::set_option( 'test', 'section', 'option2', 'value2' );
		Utility::set_option( 'test', 'section', 'option3', 'value3' );

		$value1 = Utility::get_option( 'test', 'section', 'option1' );
		$value2 = Utility::get_option( 'test', 'section', 'option2' );
		$value3 = Utility::get_option( 'test', 'section', 'option3' );

		$this->assertEquals( 'value1', $value1 );
		$this->assertEquals( 'value2', $value2 );
		$this->assertEquals( 'value3', $value3 );

		// Clean up
		delete_option( 'easycommerce_test_section_option1' );
		delete_option( 'easycommerce_test_section_option2' );
		delete_option( 'easycommerce_test_section_option3' );
	}

	/**
	 * Test options in different sections.
	 */
	public function test_options_different_sections() {
		Utility::set_option( 'section1', 'subsection', 'key', 'value1' );
		Utility::set_option( 'section2', 'subsection', 'key', 'value2' );

		$value1 = Utility::get_option( 'section1', 'subsection', 'key' );
		$value2 = Utility::get_option( 'section2', 'subsection', 'key' );

		$this->assertEquals( 'value1', $value1 );
		$this->assertEquals( 'value2', $value2 );

		// Clean up
		delete_option( 'easycommerce_section1_subsection_key' );
		delete_option( 'easycommerce_section2_subsection_key' );
	}

	/**
	 * Test option update.
	 */
	public function test_option_update() {
		// Set initial value
		Utility::set_option( 'test', 'update', 'key', 'initial_value' );

		$initial_value = Utility::get_option( 'test', 'update', 'key' );
		$this->assertEquals( 'initial_value', $initial_value );

		// Update value
		Utility::set_option( 'test', 'update', 'key', 'updated_value' );

		$updated_value = Utility::get_option( 'test', 'update', 'key' );
		$this->assertEquals( 'updated_value', $updated_value );

		// Clean up
		delete_option( 'easycommerce_test_update_key' );
	}

	/**
	 * Test option deletion.
	 */
	public function test_option_deletion() {
		// Set a value
		Utility::set_option( 'test', 'delete', 'key', 'value_to_delete' );

		$value = Utility::get_option( 'test', 'delete', 'key' );
		$this->assertEquals( 'value_to_delete', $value );

		// Delete the option
		delete_option( 'easycommerce-test-delete' );

		$deleted_value = Utility::get_option( 'test', 'delete', 'key' );
		$this->assertEquals( '', $deleted_value );
	}

	/**
	 * Test complex option values.
	 */
	public function test_complex_option_values() {
		$complex_value = array(
			'nested'  => array(
				'key'   => 'value',
				'array' => array( 1, 2, 3 )
			),
			'string'  => 'test string',
			'number'  => 123,
			'boolean' => true
		);

		Utility::set_option( 'test', 'complex', 'data', $complex_value );

		$retrieved_value = Utility::get_option( 'test', 'complex', 'data' );

		$this->assertEquals( $complex_value, $retrieved_value );

		// Clean up
		delete_option( 'easycommerce_test_complex_data' );
	}
}

<?php
/**
 * Test Product_Variation model.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;

class ProductVariationTest extends EasyCommerceTestCase {

	protected $product_id;
	protected $variation_id;

	public function set_up(): void {
		parent::set_up();

		// Create a test product via factory
		$this->product_id = $this->factory->product->create( [
			'title'  => 'Test Product for Variation',
			'status' => 'publish',
		] );
	}

	public function tear_down(): void {
		if ( $this->variation_id ) {
			$variation = new Product_Variation( $this->variation_id );
			$variation->delete();
		}

		if ( $this->product_id ) {
			$product = new Product( $this->product_id );
			$product->delete( true );
		}

		parent::tear_down();
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Create a variation via factory and cache its ID.
	 */
	private function make_variation( array $args = [] ): Product_Variation {
		$defaults = [
			'product_id'     => $this->product_id,
			'name'           => 'Default',
			'sku'            => 'TEST-VAR-' . wp_generate_password( 6, false ),
			'price'          => 19.99,
			'stock_quantity' => 10,
		];

		$id = $this->factory->variation->create( array_merge( $defaults, $args ) );

		$this->variation_id = $id;

		return new Product_Variation( $id );
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * Empty constructor should return an instance with exists() === false.
	 */
	public function test_constructor(): void {
		$variation = new Product_Variation();

		$this->assertInstanceOf( Product_Variation::class, $variation );
		$this->assertFalse( $variation->exists() );
	}

	/**
	 * Constructor with a valid ID should load the variation from the DB.
	 */
	public function test_constructor_with_valid_id(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Test Variation' );
		$variation->set_sku( 'TEST-VAR-001' );
		$variation->set_price( 25.00 );
		$variation->set_stock_quantity( 10 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$loaded = new Product_Variation( $id );

		$this->assertTrue( $loaded->exists() );
		$this->assertEquals( $id, $loaded->get_id() );
		$this->assertEquals( $this->product_id, $loaded->get_product_id() );
		$this->assertEquals( 'Test Variation', $loaded->get_name() );
		$this->assertEquals( 'TEST-VAR-001', $loaded->get_sku() );
	}

	// ── Save / persist ────────────────────────────────────────────────────────

	/**
	 * save() should insert a new record and return a positive integer ID.
	 */
	public function test_save_creates_variation_with_required_fields(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Save Test' );
		$variation->set_sku( 'TEST-SAVE' );
		$variation->set_price( 19.99 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$this->assertNotFalse( $id );
		$this->assertGreaterThan( 0, $id );
	}

	/**
	 * save() must return a positive integer ID.
	 */
	public function test_save_returns_positive_id(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Positive ID Test' );
		$variation->set_sku( 'TEST-POS-ID' );
		$variation->set_price( 9.99 );
		$variation->set_stock_quantity( 5 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
	}

	/**
	 * save() test via existing test_save test.
	 */
	public function test_save(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Save Test Variation' );
		$variation->set_sku( 'TEST-SAVE-2' );
		$variation->set_price( 19.99 );
		$variation->set_stock_quantity( 15 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
		$this->assertTrue( $variation->exists() );
	}

	/**
	 * After save(), update and re-load: the price change should be reflected.
	 */
	public function test_save_update_changes_price(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Update Price Test' );
		$variation->set_sku( 'TEST-UPD-PRICE' );
		$variation->set_price( 30.00 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		// Update price
		$variation->set_price( 45.00 );
		$variation->save();

		$reloaded = new Product_Variation( $id );
		$this->assertEquals( 45.00, $reloaded->get_regular_price() );
	}

	// ── exists() ──────────────────────────────────────────────────────────────

	/**
	 * exists() returns false before save, true after.
	 */
	public function test_exists(): void {
		$variation = new Product_Variation();
		$this->assertFalse( $variation->exists() );

		$variation->product_id = $this->product_id;
		$variation->set_name( 'Exists Test' );
		$variation->set_sku( 'TEST-EXISTS' );
		$variation->set_price( 30.00 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$this->assertTrue( $variation->exists() );
	}

	/**
	 * A variation loaded from DB via constructor should have exists() === true.
	 */
	public function test_exists_after_save(): void {
		$variation = $this->make_variation();
		$this->assertTrue( $variation->exists() );
	}

	// ── get_id() ──────────────────────────────────────────────────────────────

	/**
	 * get_id() returns the ID assigned after save.
	 */
	public function test_get_id_after_save(): void {
		$variation = $this->make_variation( [ 'name' => 'ID Test', 'price' => 12.00 ] );
		$id        = $variation->get_id();

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
		$this->assertSame( $this->variation_id, $id );
	}

	// ── Getters / setters ─────────────────────────────────────────────────────

	/**
	 * Comprehensive getter and setter round-trip.
	 */
	public function test_getters_and_setters(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;

		$variation->set_name( 'Premium Variation' );
		$this->assertEquals( 'Premium Variation', $variation->get_name() );

		$variation->set_sku( 'PREM-VAR-002' );
		$this->assertEquals( 'PREM-VAR-002', $variation->get_sku() );

		$variation->set_price( 49.99 );
		$this->assertEquals( 49.99, $variation->get_price() );
		$this->assertEquals( 49.99, $variation->get_regular_price() );

		$variation->set_sale_price( 39.99 );
		$this->assertEquals( 39.99, $variation->get_sale_price() );

		$variation->set_stock_quantity( 25 );
		$this->assertEquals( 25, $variation->get_stock() );

		$variation->set_type( 'physical' );
		$this->assertEquals( 'physical', $variation->get_type() );

		$variation->set_status( 'active' );
		$this->assertEquals( 'active', $variation->get_status() );

		$variation->set_price_id( 2 );
		$this->assertEquals( 2, $variation->get_price_id() );
	}

	/**
	 * get_name() returns the value that was set.
	 */
	public function test_get_name_returns_set_value(): void {
		$variation = $this->make_variation( [ 'name' => 'Unique Name XYZ' ] );
		$this->assertSame( 'Unique Name XYZ', $variation->get_name() );
	}

	/**
	 * get_sku() returns the value that was set.
	 */
	public function test_get_sku_returns_set_value(): void {
		$variation = $this->make_variation( [ 'sku' => 'SKU-EXPLICIT-001' ] );
		$this->assertSame( 'SKU-EXPLICIT-001', $variation->get_sku() );
	}

	/**
	 * get_price() returns a float.
	 */
	public function test_get_price_returns_float(): void {
		$variation = $this->make_variation( [ 'price' => 29.99 ] );
		$price     = $variation->get_price();

		$this->assertIsFloat( (float) $price );
		$this->assertEquals( 29.99, $price );
	}

	/**
	 * get_sale_price() returns false when no sale price is configured.
	 */
	public function test_get_sale_price_when_not_set(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_price( 50.00 );
		// sale_price defaults to 0.0, which is not <= price in a meaningful way
		// The model returns false when sale_price is 0 / > price

		$sale = $variation->get_sale_price();

		$this->assertThat( $sale, $this->logicalOr(
			$this->isFalse(),
			$this->isNull()
		) );
	}

	// ── get_name($full) ───────────────────────────────────────────────────────

	/**
	 * get_name(true) prepends the product title.
	 */
	public function test_get_name_full(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Large Size' );

		$name = $variation->get_name( true );
		$this->assertIsString( $name );
		$this->assertStringContainsString( 'Large Size', $name );
	}

	// ── Stock management ─────────────────────────────────────────────────────

	/**
	 * manages_stock() returns true when stock_quantity > 0.
	 */
	public function test_stock_management(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_stock_quantity( 50 );

		$this->assertTrue( $variation->manages_stock() );
		$this->assertEquals( 50, $variation->get_stock() );
	}

	/**
	 * get_stock() after set_stock_quantity() returns correct value.
	 */
	public function test_get_stock_after_set_stock_quantity(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_stock_quantity( 42 );

		$this->assertSame( 42, $variation->get_stock() );
	}

	/**
	 * manages_stock() returns true when stock is positive.
	 */
	public function test_manages_stock_true_with_stock_set(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_stock_quantity( 1 );

		$this->assertTrue( $variation->manages_stock() );
	}

	/**
	 * manages_stock() returns false when stock quantity is zero.
	 */
	public function test_manages_stock_false_with_no_stock(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_stock_quantity( 0 );

		$this->assertFalse( $variation->manages_stock() );
	}

	// ── Status ────────────────────────────────────────────────────────────────

	/**
	 * set_status / get_status round-trip for 'publish' and 'draft'.
	 */
	public function test_set_status_published_vs_draft(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;

		$variation->set_status( 'publish' );
		$this->assertSame( 'publish', $variation->get_status() );

		$variation->set_status( 'draft' );
		$this->assertSame( 'draft', $variation->get_status() );
	}

	// ── get_product() ────────────────────────────────────────────────────────

	/**
	 * get_product() should return a Product instance matching the product ID.
	 */
	public function test_get_product_id_and_product(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;

		$this->assertEquals( $this->product_id, $variation->get_product_id() );

		$product = $variation->get_product();
		$this->assertInstanceOf( Product::class, $product );
		$this->assertEquals( $this->product_id, $product->get_id() );
	}

	/**
	 * get_product() returns a Product instance.
	 */
	public function test_get_product_returns_product_instance(): void {
		$variation = $this->make_variation();
		$product   = $variation->get_product();

		$this->assertInstanceOf( Product::class, $product );
		$this->assertEquals( $this->product_id, $product->get_id() );
	}

	// ── Delete ────────────────────────────────────────────────────────────────

	/**
	 * delete() removes the variation so a subsequent load finds nothing.
	 */
	public function test_delete(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Delete Test Variation' );
		$variation->set_sku( 'TEST-DELETE' );
		$variation->set_price( 9.99 );

		$id     = $variation->save();
		$result = $variation->delete();

		$this->assertGreaterThanOrEqual( 0, $result );

		// Verify it no longer exists in the DB
		$reloaded = new Product_Variation( $id );
		$this->assertFalse( $reloaded->exists() );

		$this->variation_id = null; // Already deleted
	}

	/**
	 * delete() removes the variation from the database.
	 */
	public function test_delete_removes_variation(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Removal Test' );
		$variation->set_sku( 'TEST-REMOVAL' );
		$variation->set_price( 5.00 );

		$id = $variation->save();
		$variation->delete();

		$reloaded = new Product_Variation( $id );
		$this->assertFalse( $reloaded->exists() );

		$this->variation_id = null;
	}

	// ── Meta ─────────────────────────────────────────────────────────────────

	/**
	 * add_meta / get_meta / update_meta / delete_meta full cycle.
	 */
	public function test_meta_operations(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Meta Test Variation' );
		$variation->set_sku( 'TEST-META' );
		$variation->set_price( 29.99 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$result = $variation->add_meta( 'test_key', 'test_value' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $variation->get_meta( 'test_key' );
		$this->assertEquals( 'test_value', $value );

		$result = $variation->update_meta( 'test_key', 'updated_value' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $variation->get_meta( 'test_key' );
		$this->assertEquals( 'updated_value', $value );

		$result = $variation->delete_meta( 'test_key' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $variation->get_meta( 'test_key' );
		$this->assertNull( $value );
	}

	/**
	 * Meta set then get returns the stored value.
	 */
	public function test_meta_set_and_get(): void {
		$variation = $this->make_variation();
		$variation->add_meta( 'colour', 'blue' );

		$this->assertSame( 'blue', $variation->get_meta( 'colour' ) );
	}

	// ── Downloads ─────────────────────────────────────────────────────────────

	/**
	 * get_downloads() returns an array; add_download() returns an int.
	 */
	public function test_downloads(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Download Test Variation' );
		$variation->set_sku( 'TEST-DOWNLOAD' );
		$variation->set_price( 39.99 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$downloads = $variation->get_downloads();
		$this->assertIsArray( $downloads );

		$result = $variation->add_download( 1, 'Test File' );
		$this->assertIsInt( $result );
	}

	/**
	 * add_download() then get_downloads() should include the added download.
	 */
	public function test_add_and_get_downloads(): void {
		$variation = $this->make_variation();

		$result = $variation->add_download( 1, 'Sample PDF' );
		$this->assertIsInt( $result );
		$this->assertGreaterThan( 0, $result );

		$downloads = $variation->get_downloads();
		$this->assertIsArray( $downloads );
		$this->assertGreaterThanOrEqual( 1, count( $downloads ) );
	}

	// ── Attributes ────────────────────────────────────────────────────────────

	/**
	 * get_attributes() returns an array; add_attribute() returns an int.
	 */
	public function test_attributes(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Attribute Test Variation' );
		$variation->set_sku( 'TEST-ATTRIBUTE' );
		$variation->set_price( 49.99 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$attributes = $variation->get_attributes();
		$this->assertIsArray( $attributes );

		// Seed a real attribute + value so the FK-backed insert can succeed.
		$attribute_id = ( new Attribute() )->add( 'Color', 'Text', 'color' );
		$value_id     = ( new Attribute_Value() )->add( $attribute_id, 'Red', 'red', 'red' );

		$result = $variation->add_attribute( $attribute_id, $value_id );
		$this->assertIsInt( $result );
	}

	// ── Thumbnail ─────────────────────────────────────────────────────────────

	/**
	 * get_thumbnail() returns an array (product thumbnail fallback).
	 */
	public function test_thumbnail_methods(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Thumbnail Test' );
		$variation->set_sku( 'TEST-THUMB' );
		$variation->set_price( 10.00 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$thumbnail = $variation->get_thumbnail();
		$this->assertIsArray( $thumbnail );
	}

	// ── Formatted prices ─────────────────────────────────────────────────────

	/**
	 * Formatted price methods return strings.
	 */
	public function test_formatted_prices(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_price( 59.99 );
		$variation->set_sale_price( 49.99 );

		$regular_price = $variation->get_regular_price( true );
		$sale_price    = $variation->get_sale_price( true );

		$this->assertIsString( $regular_price );
		$this->assertIsString( $sale_price );
	}

	// ── Dimensions / weight / tax / low-stock ─────────────────────────────────

	/**
	 * get_height / get_width / get_length return false or array when not set.
	 */
	public function test_dimensions(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;

		$height = $variation->get_height();
		$width  = $variation->get_width();
		$length = $variation->get_length();

		$this->assertThat( $height, $this->logicalOr( $this->isNull(), $this->isType( 'string' ), $this->isFalse() ) );
		$this->assertThat( $width, $this->logicalOr( $this->isNull(), $this->isType( 'string' ), $this->isFalse() ) );
		$this->assertThat( $length, $this->logicalOr( $this->isNull(), $this->isType( 'string' ), $this->isFalse() ) );
	}

	/**
	 * get_tax_class / set_tax_class round-trip.
	 */
	public function test_tax_class(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Tax Class Test' );
		$variation->set_sku( 'TEST-TAX' );
		$variation->set_price( 10.00 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$tax_class = $variation->get_tax_class();
		$this->assertThat( $tax_class, $this->logicalOr( $this->isNull(), $this->isType( 'string' ) ) );

		$variation->set_tax_class( 'standard' );
		$this->assertEquals( 'standard', $variation->get_tax_class() );
	}

	/**
	 * get_low_stock_limit / set_low_stock_limit round-trip.
	 */
	public function test_low_stock_limit(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;

		$limit = $variation->get_low_stock_limit();
		$this->assertThat( $limit, $this->logicalOr( $this->isNull(), $this->isType( 'int' ) ) );

		$variation->set_low_stock_limit( 5 );
		$this->assertEquals( 5, $variation->get_low_stock_limit() );
	}

	/**
	 * get_weight / get_weight(true) return false or numeric when not set.
	 */
	public function test_weight(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;

		$weight = $variation->get_weight();
		$this->assertThat( $weight, $this->logicalOr( $this->isNull(), $this->isType( 'float' ), $this->isFalse() ) );

		$weight_kg = $variation->get_weight( true );
		$this->assertThat( $weight_kg, $this->logicalOr( $this->isNull(), $this->isType( 'float' ), $this->isFalse() ) );
	}

	// ── get_by_price() ────────────────────────────────────────────────────────

	/**
	 * get_by_price() returns the variation matching price_id + product_id.
	 */
	public function test_get_by_price(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Price Test Variation' );
		$variation->set_sku( 'TEST-PRICE' );
		$variation->set_price( 99.99 );
		$variation->set_price_id( 5 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$found = $variation->get_by_price( 5, $this->product_id );

		$this->assertInstanceOf( Product_Variation::class, $found );
		$this->assertEquals( $id, $found->get_id() );
	}

	/**
	 * get_by_price() returns the correct variation by its price_id.
	 */
	public function test_get_by_price_returns_correct_variation(): void {
		$variation = $this->make_variation( [
			'price'    => 88.00,
			'price_id' => 7,
		] );

		$finder = new Product_Variation();
		$found  = $finder->get_by_price( 7, $this->product_id );

		$this->assertInstanceOf( Product_Variation::class, $found );
		$this->assertSame( $variation->get_id(), $found->get_id() );
	}

	// ── get_by() / get_by_product_id ─────────────────────────────────────────

	/**
	 * get_by() filters variations by a given field.
	 */
	public function test_get_by(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Get By Test Variation' );
		$variation->set_sku( 'TEST-GETBY' );
		$variation->set_price( 89.99 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$variations = $variation->get_by( $this->product_id, 'product_id' );

		$this->assertIsArray( $variations );
		$this->assertGreaterThanOrEqual( 1, count( $variations ) );
	}

	/**
	 * get_by( $product_id, 'product_id' ) returns all variations for that product.
	 */
	public function test_get_by_product_id_returns_variations(): void {
		$this->make_variation( [ 'name' => 'Var A', 'price' => 10.00 ] );

		// Create a second variation for the same product
		$this->factory->variation->create( [
			'product_id' => $this->product_id,
			'name'       => 'Var B',
			'sku'        => 'TEST-VAR-B',
			'price'      => 20.00,
		] );

		$finder     = new Product_Variation();
		$variations = $finder->get_by( $this->product_id, 'product_id' );

		$this->assertIsArray( $variations );
		$this->assertGreaterThanOrEqual( 2, count( $variations ) );

		foreach ( $variations as $v ) {
			$this->assertInstanceOf( Product_Variation::class, $v );
		}
	}

	// ── get_by_attributes() ───────────────────────────────────────────────────

	/**
	 * get_by_attributes() returns false when no attributes match.
	 */
	public function test_get_by_attributes(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;
		$variation->set_name( 'Attributes Test Variation' );
		$variation->set_sku( 'TEST-ATTR' );
		$variation->set_price( 79.99 );

		$id                 = $variation->save();
		$this->variation_id = $id;

		$variations = $variation->get_by_attributes( [ '1' => 'Large' ] );

		$this->assertFalse( $variations );
	}

	// ── Stock reservation ─────────────────────────────────────────────────────

	/**
	 * reduce_stock() takes the units and persists the new level.
	 */
	public function test_reduce_stock_takes_the_units(): void {
		$variation = $this->make_variation( [ 'stock_quantity' => 10 ] );

		$this->assertTrue( $variation->reduce_stock( 3 ) );
		$this->assertSame( 7, $variation->get_stock() );
		$this->assertSame( 7, ( new Product_Variation( $variation->get_id() ) )->get_stock() );
	}

	/**
	 * reduce_stock() refuses to go below zero, so the last unit cannot be sold twice.
	 */
	public function test_reduce_stock_refuses_more_than_is_held(): void {
		$variation = $this->make_variation( [ 'stock_quantity' => 1 ] );

		$this->assertTrue( $variation->reduce_stock( 1 ) );
		$this->assertSame( 0, ( new Product_Variation( $variation->get_id() ) )->get_stock() );

		// A second checkout racing on the same unit.
		$racing = new Product_Variation( $variation->get_id() );

		$this->assertFalse( $racing->reduce_stock( 1 ) );
		$this->assertSame( 0, ( new Product_Variation( $variation->get_id() ) )->get_stock() );
	}

	/**
	 * Only as many racers as there are units get through.
	 */
	public function test_reduce_stock_lets_only_the_available_units_through(): void {
		$variation = $this->make_variation( [ 'stock_quantity' => 3 ] );
		$id        = $variation->get_id();

		$racers = array();
		for ( $i = 0; $i < 5; $i++ ) {
			// Each instance is loaded before any of them writes.
			$racers[] = new Product_Variation( $id );
		}

		$taken = 0;
		foreach ( $racers as $racer ) {
			if ( $racer->reduce_stock( 1 ) ) {
				$taken++;
			}
		}

		$this->assertSame( 3, $taken, 'three units, three winners' );
		$this->assertSame( 0, ( new Product_Variation( $id ) )->get_stock() );
	}

	/**
	 * A variation that does not track stock is not blocked by the condition.
	 */
	public function test_reduce_stock_passes_when_stock_is_not_managed(): void {
		$variation             = new Product_Variation();
		$variation->product_id = $this->product_id;

		$variation->set_name( 'Unmanaged' );
		$variation->set_sku( 'TEST-VAR-' . wp_generate_password( 6, false ) );
		$variation->set_price( 19.99 );
		$variation->set_stock_quantity( null );

		$this->variation_id = $variation->save();

		$this->assertFalse( $variation->manages_stock(), 'no stock level means no tracking' );
		$this->assertTrue( $variation->reduce_stock( 5 ) );
		$this->assertNull( ( new Product_Variation( $this->variation_id ) )->get_stock() );
	}
}

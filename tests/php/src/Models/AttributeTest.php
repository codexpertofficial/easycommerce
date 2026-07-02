<?php
/**
 * Test Attribute model.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;

class AttributeTest extends EasyCommerceTestCase {

	protected $attribute_id;

	public function set_up(): void {
		parent::set_up();
	}

	public function tear_down(): void {
		if ( $this->attribute_id ) {
			$attribute = new Attribute();
			$attribute->delete( $this->attribute_id );
		}

		parent::tear_down();
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Create a uniquely-named attribute and cache its ID.
	 */
	private function make_attribute( string $name = '', string $type = 'text', string $slug = '' ): object {
		$suffix = wp_generate_password( 6, false );
		$name   = $name ?: "Test Attribute {$suffix}";
		$slug   = $slug ?: "test-attribute-{$suffix}";

		$attribute = new Attribute();
		$id        = $attribute->add( $name, $type, $slug );

		$this->attribute_id = $id;

		return (object) [
			'id'        => $id,
			'name'      => $name,
			'slug'      => $slug,
			'type'      => $type,
			'attribute' => $attribute,
		];
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * Attribute model can be instantiated without arguments.
	 */
	public function test_constructor(): void {
		$attribute = new Attribute();

		$this->assertInstanceOf( Attribute::class, $attribute );
	}

	// ── add() ─────────────────────────────────────────────────────────────────

	/**
	 * add() inserts a new attribute and returns a positive integer.
	 */
	public function test_add(): void {
		$attribute = new Attribute();
		$result    = $attribute->add( 'Test Attribute', 'text', 'test-attribute' );

		$this->assertIsInt( $result );
		$this->assertGreaterThan( 0, $result );

		$this->attribute_id = $result;
	}

	/**
	 * add() returns a positive integer.
	 */
	public function test_add_returns_positive_id(): void {
		$attr = $this->make_attribute();

		$this->assertIsInt( $attr->id );
		$this->assertGreaterThan( 0, $attr->id );
	}

	/**
	 * add() is idempotent: adding an attribute with the same slug returns the
	 * existing ID instead of creating a duplicate.
	 */
	public function test_add_duplicate_name_returns_existing_id(): void {
		$attribute = new Attribute();

		$first_id  = $attribute->add( 'Colour', 'text', 'colour' );
		$second_id = $attribute->add( 'Colour', 'text', 'colour' );

		$this->assertSame( $first_id, $second_id );

		$this->attribute_id = $first_id;
	}

	// ── get() ─────────────────────────────────────────────────────────────────

	/**
	 * get() returns an object with the correct name and slug.
	 */
	public function test_get(): void {
		$attribute = new Attribute();
		$id        = $attribute->add( 'Test Attribute', 'text', 'test-attribute' );

		$result = $attribute->get( $id );

		$this->assertIsObject( $result );
		$this->assertEquals( 'Test Attribute', $result->name );
		$this->assertEquals( 'test-attribute', $result->slug );

		$this->attribute_id = $id;
	}

	/**
	 * get() returns an object with matching id, name, and slug.
	 */
	public function test_get_by_id_returns_correct_data(): void {
		$attr   = $this->make_attribute( 'Size', 'text', 'size-unique' );
		$result = $attr->attribute->get( $attr->id );

		$this->assertIsObject( $result );
		$this->assertSame( (string) $attr->id, (string) $result->id );
		$this->assertSame( $attr->name, $result->name );
		$this->assertSame( $attr->slug, $result->slug );
	}

	// ── get_by_slug() ─────────────────────────────────────────────────────────

	/**
	 * get_by_slug() returns an object/array with matching name and slug.
	 */
	public function test_get_by_slug(): void {
		$attribute = new Attribute();
		$id        = $attribute->add( 'Test Attribute', 'text', 'test-attribute' );

		$result = $attribute->get_by_slug( 'test-attribute' );

		$this->assertThat( $result, $this->logicalOr(
			$this->isType( 'array' ),
			$this->isType( 'object' )
		) );

		if ( is_array( $result ) ) {
			$this->assertEquals( 'Test Attribute', $result['name'] );
			$this->assertEquals( 'test-attribute', $result['slug'] );
		} else {
			$this->assertEquals( 'Test Attribute', $result->name );
			$this->assertEquals( 'test-attribute', $result->slug );
		}

		$this->attribute_id = $id;
	}

	/**
	 * get_by_slug() returns the attribute object for an existing slug.
	 */
	public function test_get_by_slug_returns_attribute(): void {
		$attr   = $this->make_attribute( 'Material', 'text', 'material-unique' );
		$result = $attr->attribute->get_by_slug( $attr->slug );

		$this->assertNotNull( $result );
		$this->assertThat( $result, $this->logicalOr(
			$this->isType( 'array' ),
			$this->isType( 'object' )
		) );

		$name = is_array( $result ) ? $result['name'] : $result->name;
		$this->assertSame( $attr->name, $name );
	}

	/**
	 * get_by_slug() returns null for a slug that does not exist.
	 */
	public function test_get_by_slug_returns_null_for_missing(): void {
		$attribute = new Attribute();
		$result    = $attribute->get_by_slug( 'this-slug-does-not-exist-xyz' );

		$this->assertNull( $result );
	}

	// ── get_all() ─────────────────────────────────────────────────────────────

	/**
	 * get_all() returns an array of results (may be empty or populated).
	 */
	public function test_get_all(): void {
		$attribute = new Attribute();
		$id        = $attribute->add( 'Test Attribute', 'text', 'test-attribute' );

		$results = $attribute->get_all();

		$this->assertIsArray( $results );
		$this->assertGreaterThanOrEqual( 1, count( $results ) );

		$this->attribute_id = $id;
	}

	/**
	 * get_all() returns an array.
	 */
	public function test_get_all_returns_array(): void {
		$this->make_attribute();

		$attribute = new Attribute();
		$results   = $attribute->get_all();

		$this->assertIsArray( $results );
		$this->assertGreaterThanOrEqual( 1, count( $results ) );
	}

	// ── count() ───────────────────────────────────────────────────────────────

	/**
	 * count() returns a non-negative integer.
	 */
	public function test_count(): void {
		$attribute = new Attribute();
		$count     = $attribute->count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
	}

	/**
	 * count() returns an integer and increases after inserting a new attribute.
	 */
	public function test_count_returns_integer(): void {
		$attribute   = new Attribute();
		$count_before = $attribute->count();

		$this->make_attribute();

		$count_after = $attribute->count();

		$this->assertIsInt( $count_after );
		$this->assertGreaterThan( $count_before, $count_after );
	}

	// ── update() ──────────────────────────────────────────────────────────────

	/**
	 * update() modifies the attribute and the change is reflected by get().
	 */
	public function test_update(): void {
		$attribute = new Attribute();
		$id        = $attribute->add( 'Test Attribute', 'text', 'test-attribute' );

		$result = $attribute->update( $id, [ 'name' => 'Updated Attribute' ] );

		$this->assertGreaterThanOrEqual( 0, $result );

		$updated = $attribute->get( $id );
		$this->assertEquals( 'Updated Attribute', $updated->name );

		$this->attribute_id = $id;
	}

	/**
	 * update() persists the new name to the database.
	 */
	public function test_update_changes_name(): void {
		$attr = $this->make_attribute( 'Original Name', 'text', 'original-name-slug' );

		$attr->attribute->update( $attr->id, [ 'name' => 'Revised Name' ] );

		$result = $attr->attribute->get( $attr->id );
		$this->assertSame( 'Revised Name', $result->name );
	}

	// ── delete() ──────────────────────────────────────────────────────────────

	/**
	 * delete() removes the attribute so get() returns null afterwards.
	 */
	public function test_delete(): void {
		$attribute = new Attribute();
		$id        = $attribute->add( 'Test Attribute', 'text', 'test-attribute' );

		$result = $attribute->delete( $id );

		$this->assertGreaterThanOrEqual( 0, $result );

		$deleted = $attribute->get( $id );
		$this->assertNull( $deleted );

		$this->attribute_id = null;
	}

	/**
	 * delete() removes the row — get() returns null afterward.
	 */
	public function test_delete_removes_attribute(): void {
		$attr = $this->make_attribute( 'Will Be Deleted', 'text', 'will-be-deleted' );

		$attr->attribute->delete( $attr->id );

		$result = $attr->attribute->get( $attr->id );
		$this->assertNull( $result );

		$this->attribute_id = null;
	}

	// ── bulk_delete() ─────────────────────────────────────────────────────────

	/**
	 * bulk_delete() removes multiple attributes and each get() returns null.
	 */
	public function test_bulk_delete_removes_multiple(): void {
		$attribute = new Attribute();
		$suffix    = wp_generate_password( 6, false );

		$id1 = $attribute->add( "Bulk A {$suffix}", 'text', "bulk-a-{$suffix}" );
		$id2 = $attribute->add( "Bulk B {$suffix}", 'text', "bulk-b-{$suffix}" );
		$id3 = $attribute->add( "Bulk C {$suffix}", 'text', "bulk-c-{$suffix}" );

		$result = $attribute->bulk_delete( [ $id1, $id2, $id3 ] );
		$this->assertTrue( $result );

		$this->assertNull( $attribute->get( $id1 ) );
		$this->assertNull( $attribute->get( $id2 ) );
		$this->assertNull( $attribute->get( $id3 ) );

		// Nothing left to clean up in tear_down
		$this->attribute_id = null;
	}

	// ── Attribute_Value: add_value / get_values / get_value_by_slug ───────────

	/**
	 * Attribute_Value::add() inserts a value linked to the attribute.
	 * Attribute_Value::get_by_attribute() returns those values.
	 */
	public function test_add_value_and_get_values(): void {
		$attr           = $this->make_attribute( 'Colour AV', 'text', 'colour-av-unique' );
		$attribute_value = new Attribute_Value();

		$value_id = $attribute_value->add( $attr->id, 'Red', 'red', 'red' );

		$this->assertIsInt( $value_id );
		$this->assertGreaterThan( 0, $value_id );

		$values = $attribute_value->get_by_attribute( $attr->id );

		$this->assertIsArray( $values );
		$this->assertGreaterThanOrEqual( 1, count( $values ) );

		$names = array_map( fn( $v ) => $v->name, $values );
		$this->assertContains( 'Red', $names );
	}

	/**
	 * Attribute_Value::get_by_slug() returns the value object for an existing slug.
	 */
	public function test_get_value_by_slug(): void {
		$attr            = $this->make_attribute( 'Size AV', 'text', 'size-av-unique' );
		$attribute_value = new Attribute_Value();

		$attribute_value->add( $attr->id, 'Large', 'large', 'large' );

		$result = $attribute_value->get_by_slug( 'large' );

		$this->assertNotNull( $result );
		$this->assertIsObject( $result );
		$this->assertSame( 'Large', $result->name );
		$this->assertSame( 'large', $result->slug );
	}
}

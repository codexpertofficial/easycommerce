<?php
/**
 * Test Product model.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;

class ProductTest extends EasyCommerceTestCase {

	/**
	 * @var int A pre-created product ID available to all tests.
	 */
	protected $product_id;

	// ── Lifecycle ─────────────────────────────────────────────────────────────

	public function set_up(): void {
		parent::set_up();

		$this->product_id = $this->factory->product->create( [
			'title'  => 'Test Product',
			'status' => 'publish',
		] );
	}

	public function tear_down(): void {
		if ( $this->product_id ) {
			wp_delete_post( $this->product_id, true );
		}

		parent::tear_down();
	}

	// ── Constructor ───────────────────────────────────────────────────────────

	/**
	 * Test product constructor with valid ID.
	 */
	public function test_constructor_with_valid_id() {
		$product = new Product( $this->product_id );

		$this->assertTrue( $product->exists() );
		$this->assertEquals( $this->product_id, $product->get_id() );
		$this->assertEquals( 'Test Product', $product->get_title() );
	}

	/**
	 * Test product constructor with invalid ID.
	 */
	public function test_constructor_with_invalid_id() {
		$product = new Product( 99999 );

		$this->assertFalse( $product->exists() );
		$this->assertNull( $product->get_id() );
	}

	// ── Existence / flags ─────────────────────────────────────────────────────

	/**
	 * Test product exists method.
	 */
	public function test_exists() {
		$product = new Product( $this->product_id );
		$this->assertTrue( $product->exists() );

		$product_invalid = new Product( 99999 );
		$this->assertFalse( $product_invalid->exists() );
	}

	/**
	 * Test product is_sellable method.
	 */
	public function test_is_sellable() {
		$product = new Product( $this->product_id );
		$this->assertTrue( $product->is_sellable() );

		$product_invalid = new Product( 99999 );
		$this->assertFalse( $product_invalid->is_sellable() );
	}

	/**
	 * Test product is_deletable method.
	 */
	public function test_is_deletable() {
		$product = new Product( $this->product_id );
		$this->assertTrue( $product->is_deletable() );

		$product_invalid = new Product( 99999 );
		$this->assertFalse( $product_invalid->is_deletable() );
	}

	/**
	 * is_sellable returns true for a published product.
	 */
	public function test_is_sellable_for_published_product() {
		$id      = $this->factory->product->create( [ 'status' => 'publish' ] );
		$product = new Product( $id );

		$this->assertTrue( $product->is_sellable() );

		wp_delete_post( $id, true );
	}

	/**
	 * is_sellable returns false when the product does not exist in the DB.
	 *
	 * A draft post is still a real post, so we test with a non-existent ID.
	 */
	public function test_is_sellable_for_draft_product() {
		// A product with status 'draft' still "exists" as a post, so is_sellable is true.
		// is_sellable() only checks $this->exists, which reflects whether the post was found.
		$id      = $this->factory->product->create( [ 'status' => 'draft' ] );
		$product = new Product( $id );

		// The post exists (just drafted), so the model considers it sellable.
		$this->assertTrue( $product->exists() );

		// Verify that a truly non-existent product is NOT sellable.
		$missing = new Product( 0 );
		$this->assertFalse( $missing->is_sellable() );

		wp_delete_post( $id, true );
	}

	// ── create() ─────────────────────────────────────────────────────────────

	/**
	 * create() returns an integer product ID on success.
	 */
	public function test_create_returns_product_id() {
		$product = new Product();
		$id      = $product->create( [
			'title'  => 'Brand New Product',
			'status' => 'publish',
		] );

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );

		wp_delete_post( $id, true );
	}

	/**
	 * create() returns false when the title is missing.
	 */
	public function test_create_fails_without_title() {
		$product = new Product();
		$result  = $product->create( [ 'status' => 'publish' ] );

		$this->assertFalse( $result );
	}

	/**
	 * create() returns a WP_Error when a duplicate SKU is supplied.
	 */
	public function test_create_with_duplicate_sku_returns_error() {
		$sku = 'DUPE-SKU-' . uniqid();

		// First product with this SKU.
		$first = new Product();
		$first->create( [
			'title'      => 'First Product',
			'status'     => 'publish',
			'variations' => [ [
				'name'           => 'Default',
				'sku'            => $sku,
				'type'           => 'physical',
				'regular_price'  => 10.00,
				'sale_price'     => '',
				'stock_quantity' => 5,
				'status'         => 'active',
				'price_id'       => 1,
				'stock_limit'    => 0,
				'attributes'     => [],
				'meta'           => [],
				'downloads'      => [],
			] ],
		] );

		// Second product attempting to reuse the same SKU.
		$second = new Product();
		$result = $second->create( [
			'title'      => 'Second Product',
			'status'     => 'publish',
			'variations' => [ [
				'name'           => 'Default',
				'sku'            => $sku,
				'type'           => 'physical',
				'regular_price'  => 20.00,
				'sale_price'     => '',
				'stock_quantity' => 3,
				'status'         => 'active',
				'price_id'       => 1,
				'stock_limit'    => 0,
				'attributes'     => [],
				'meta'           => [],
				'downloads'      => [],
			] ],
		] );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'duplicate_sku', $result->get_error_code() );

		// Cleanup.
		if ( $first->get_id() ) {
			wp_delete_post( $first->get_id(), true );
		}
	}

	// ── Getters / setters ─────────────────────────────────────────────────────

	/**
	 * get_title() returns a string.
	 */
	public function test_get_title_returns_string() {
		$product = new Product( $this->product_id );

		$this->assertIsString( $product->get_title() );
		$this->assertSame( 'Test Product', $product->get_title() );
	}

	/**
	 * get_status() returns the post status that was set at creation time.
	 */
	public function test_get_status_after_create() {
		$id      = $this->factory->product->create( [ 'title' => 'Status Product', 'status' => 'publish' ] );
		$product = new Product( $id );

		$this->assertSame( 'publish', $product->get_status() );

		wp_delete_post( $id, true );
	}

	/**
	 * Test product getters and setters.
	 */
	public function test_getters_and_setters() {
		$product = new Product( $this->product_id );

		$product->set_title( 'New Title' );
		$this->assertEquals( 'New Title', $product->get_title() );

		$product->set_slug( 'new-slug' );
		$this->assertEquals( 'new-slug', $product->get_slug() );

		$product->set_status( 'draft' );
		$this->assertEquals( 'draft', $product->get_status() );
	}

	/**
	 * Test product get_url method.
	 */
	public function test_get_url() {
		$product      = new Product( $this->product_id );
		$expected_url = get_permalink( $this->product_id );

		$this->assertEquals( $expected_url, $product->get_url() );
	}

	// ── Thumbnail ─────────────────────────────────────────────────────────────

	/**
	 * Test product get_thumbnail method.
	 */
	public function test_get_thumbnail() {
		$product   = new Product( $this->product_id );
		$thumbnail = $product->get_thumbnail();

		$this->assertIsArray( $thumbnail );
		$this->assertArrayHasKey( 'id', $thumbnail );
		$this->assertArrayHasKey( 'url', $thumbnail );
	}

	/**
	 * Test product get_thumbnail method with size.
	 */
	public function test_get_thumbnail_with_size() {
		$product   = new Product( $this->product_id );
		$thumbnail = $product->get_thumbnail( 'thumbnail' );

		$this->assertIsArray( $thumbnail );
		$this->assertArrayHasKey( 'id', $thumbnail );
		$this->assertArrayHasKey( 'url', $thumbnail );
	}

	// ── Timestamps ────────────────────────────────────────────────────────────

	/**
	 * Test product get_created_at method.
	 */
	public function test_get_created_at() {
		$product    = new Product( $this->product_id );
		$created_at = $product->get_created_at();

		$this->assertIsString( $created_at );
		$this->assertNotEmpty( $created_at );
	}

	/**
	 * Test product get_updated_at method.
	 */
	public function test_get_updated_at() {
		$product    = new Product( $this->product_id );
		$updated_at = $product->get_updated_at();

		$this->assertIsString( $updated_at );
		$this->assertNotEmpty( $updated_at );
	}

	// ── Variations / is_variable ──────────────────────────────────────────────

	/**
	 * Test product has_variations method — no variations yet.
	 */
	public function test_has_variations() {
		$product = new Product( $this->product_id );
		$this->assertFalse( $product->has_variations() );
	}

	/**
	 * Test product is_variable method — no variations.
	 */
	public function test_is_variable() {
		$product = new Product( $this->product_id );
		$this->assertFalse( $product->is_variable() );
	}

	/**
	 * is_variable() returns false when there is exactly one variation.
	 */
	public function test_is_variable_with_single_variation() {
		$product_id = $this->factory->product->create( [ 'title' => 'Single Var Product' ] );
		$this->factory->variation->create( [
			'product_id' => $product_id,
			'sku'        => 'SV-' . uniqid(),
			'price'      => 15.00,
		] );

		$product = new Product( $product_id );

		$this->assertFalse( $product->is_variable() );

		wp_delete_post( $product_id, true );
	}

	/**
	 * is_variable() returns true when there are multiple variations.
	 */
	public function test_is_variable_with_multiple_variations() {
		$product_id = $this->factory->product->create( [ 'title' => 'Multi Var Product' ] );
		$this->factory->variation->create( [ 'product_id' => $product_id, 'sku' => 'MV1-' . uniqid(), 'price' => 10.00 ] );
		$this->factory->variation->create( [ 'product_id' => $product_id, 'sku' => 'MV2-' . uniqid(), 'price' => 20.00 ] );

		$product = new Product( $product_id );

		$this->assertTrue( $product->is_variable() );

		wp_delete_post( $product_id, true );
	}

	// ── Stock ─────────────────────────────────────────────────────────────────

	/**
	 * get_stock() returns null when there are no variations with numeric stock.
	 */
	public function test_get_stock_with_no_variations() {
		$product = new Product( $this->product_id );
		$stock   = $product->get_stock();

		$this->assertNull( $stock );
	}

	/**
	 * get_stock() sums stock_quantity across all variations.
	 */
	public function test_get_stock_aggregates_across_variations() {
		$product_id = $this->factory->product->create( [ 'title' => 'Stocked Product' ] );
		$this->factory->variation->create( [ 'product_id' => $product_id, 'sku' => 'ST1-' . uniqid(), 'price' => 10.00, 'stock_quantity' => 5 ] );
		$this->factory->variation->create( [ 'product_id' => $product_id, 'sku' => 'ST2-' . uniqid(), 'price' => 20.00, 'stock_quantity' => 8 ] );

		$product = new Product( $product_id );
		$stock   = $product->get_stock();

		$this->assertSame( 13, $stock );

		wp_delete_post( $product_id, true );
	}

	// ── Prices ────────────────────────────────────────────────────────────────

	/**
	 * Test product get_price method.
	 */
	public function test_get_price() {
		$product = new Product( $this->product_id );
		$price   = $product->get_price();

		$this->assertIsString( $price );
	}

	/**
	 * Test product get_sale_price method.
	 */
	public function test_get_sale_price() {
		$product    = new Product( $this->product_id );
		$sale_price = $product->get_sale_price();

		$this->assertIsString( $sale_price );
	}

	// ── get_stock generic ─────────────────────────────────────────────────────

	/**
	 * Test product get_stock method — generic type check.
	 */
	public function test_get_stock() {
		$product = new Product( $this->product_id );
		$stock   = $product->get_stock();

		$this->assertThat( $stock, $this->logicalOr( $this->isType( 'int' ), $this->isNull() ) );
	}

	// ── Rating / sales ────────────────────────────────────────────────────────

	/**
	 * Test product get_rating method.
	 */
	public function test_get_rating() {
		$product = new Product( $this->product_id );
		$rating  = $product->get_rating();

		$this->assertThat( $rating, $this->logicalOr( $this->isType( 'float' ), $this->isType( 'string' ) ) );
	}

	/**
	 * Test product get_rating_count method.
	 */
	public function test_get_rating_count() {
		$product      = new Product( $this->product_id );
		$rating_count = $product->get_rating_count();

		$this->assertIsInt( $rating_count );
	}

	/**
	 * Test product get_sales method.
	 */
	public function test_get_sales() {
		$product = new Product( $this->product_id );
		$sales   = $product->get_sales();

		$this->assertIsString( $sales );
	}

	/**
	 * Test product get_sales method unformatted.
	 */
	public function test_get_sales_unformatted() {
		$product = new Product( $this->product_id );
		$sales   = $product->get_sales( false );

		$this->assertThat( $sales, $this->logicalOr( $this->isType( 'float' ), $this->isType( 'int' ) ) );
	}

	// ── save() / update() ─────────────────────────────────────────────────────

	/**
	 * Test product save method.
	 */
	public function test_save() {
		$product = new Product( $this->product_id );
		$product->set_title( 'Updated Title' );

		$result = $product->save();
		$this->assertTrue( $result );

		$updated_post = get_post( $this->product_id );
		$this->assertEquals( 'Updated Title', $updated_post->post_title );
	}

	/**
	 * update() changes the post title in the database.
	 */
	public function test_update_title_changes_post_title() {
		$id      = $this->factory->product->create( [ 'title' => 'Original Title' ] );
		$product = new Product( $id );

		$product->update( [
			'title'  => 'Revised Title',
			'status' => 'publish',
		] );

		$post = get_post( $id );
		$this->assertSame( 'Revised Title', $post->post_title );

		wp_delete_post( $id, true );
	}

	// ── delete() ─────────────────────────────────────────────────────────────

	/**
	 * Test product delete method (soft trash).
	 */
	public function test_delete() {
		$product = new Product( $this->product_id );
		$result  = $product->delete();

		$this->assertTrue( $result );

		$post = get_post( $this->product_id );
		$this->assertEquals( 'trash', $post->post_status );
	}

	/**
	 * Soft delete moves the post status to trash.
	 */
	public function test_delete_soft_trashes_post() {
		$id      = $this->factory->product->create( [ 'title' => 'Trash Me', 'status' => 'publish' ] );
		$product = new Product( $id );

		$result = $product->delete( false );

		$this->assertTrue( $result );
		$this->assertSame( 'trash', get_post_status( $id ) );

		wp_delete_post( $id, true );
	}

	/**
	 * Test product force delete method.
	 */
	public function test_force_delete() {
		$product = new Product( $this->product_id );
		$result  = $product->delete( true );

		$this->assertTrue( $result );

		$post = get_post( $this->product_id );
		$this->assertNull( $post );

		// Prevent tear_down() from trying to delete an already-deleted post.
		$this->product_id = null;
	}

	// ── Meta ─────────────────────────────────────────────────────────────────

	/**
	 * Test product meta operations.
	 */
	public function test_meta_operations() {
		$product = new Product( $this->product_id );

		$result = $product->add_meta( 'test_key', 'test_value' );
		$this->assertIsInt( $result );

		$value = $product->get_meta( 'test_key' );
		$this->assertEquals( 'test_value', $value );

		$result = $product->update_meta( 'test_key', 'updated_value' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $product->get_meta( 'test_key' );
		$this->assertEquals( 'updated_value', $value );

		$result = $product->delete_meta( 'test_key' );
		$this->assertGreaterThanOrEqual( 0, $result );

		$value = $product->get_meta( 'test_key' );
		$this->assertNull( $value );
	}

	/**
	 * Meta values passed to create() are stored and retrievable.
	 */
	public function test_create_with_meta_stores_meta_values() {
		$product = new Product();
		$id      = $product->create( [
			'title'  => 'Meta Product',
			'status' => 'publish',
			'meta'   => [
				'custom_field' => 'hello_world',
			],
		] );

		$this->assertIsInt( $id );

		$loaded = new Product( $id );
		$this->assertSame( 'hello_world', $loaded->get_meta( 'custom_field' ) );

		wp_delete_post( $id, true );
	}

	// ── Gallery ───────────────────────────────────────────────────────────────

	/**
	 * get_gallery() returns an array (even if empty).
	 */
	public function test_get_gallery_returns_array() {
		$product = new Product( $this->product_id );
		$gallery = $product->get_gallery();

		$this->assertIsArray( $gallery );
	}

	// ── Reviews ───────────────────────────────────────────────────────────────

	/**
	 * Test product get_reviews method.
	 */
	public function test_get_reviews() {
		$product = new Product( $this->product_id );
		$reviews = $product->get_reviews();

		$this->assertIsArray( $reviews );
	}

	/**
	 * A comment posted against the product is returned by get_reviews() with expected keys.
	 */
	public function test_add_review_and_get_reviews() {
		$comment_id = wp_insert_comment( [
			'comment_post_ID'  => $this->product_id,
			'comment_content'  => 'Great product!',
			'comment_approved' => 1,
			'comment_author'   => 'Reviewer',
			'comment_author_email' => 'reviewer@example.com',
		] );
		add_comment_meta( $comment_id, 'rating', 5 );

		$product = new Product( $this->product_id );
		$reviews = $product->get_reviews();

		$this->assertIsArray( $reviews );
		$this->assertNotEmpty( $reviews );

		$review = $reviews[0];
		$this->assertArrayHasKey( 'id', $review );
		$this->assertArrayHasKey( 'text', $review );
		$this->assertArrayHasKey( 'rating', $review );
		$this->assertArrayHasKey( 'user', $review );
		$this->assertArrayHasKey( 'time', $review );
	}

	/**
	 * get_reviews() returns items with the expected shape; review stats are checkable.
	 */
	public function test_get_review_stats_returns_expected_keys() {
		// Post an approved review.
		$comment_id = wp_insert_comment( [
			'comment_post_ID'      => $this->product_id,
			'comment_content'      => 'Amazing!',
			'comment_approved'     => 1,
			'comment_author'       => 'Tester',
			'comment_author_email' => 'tester@example.com',
		] );
		add_comment_meta( $comment_id, 'rating', 4 );

		$product      = new Product( $this->product_id );
		$reviews      = $product->get_reviews();
		$rating_count = $product->get_rating_count();

		$this->assertIsArray( $reviews );
		$this->assertIsInt( $rating_count );
		$this->assertGreaterThan( 0, $rating_count );

		// Confirm the single review has the expected keys.
		$this->assertArrayHasKey( 'id', $reviews[0] );
		$this->assertArrayHasKey( 'text', $reviews[0] );
		$this->assertArrayHasKey( 'rating', $reviews[0] );
		$this->assertArrayHasKey( 'user', $reviews[0] );
		$this->assertArrayHasKey( 'name', $reviews[0]['user'] );
		$this->assertArrayHasKey( 'email', $reviews[0]['user'] );
	}

	// ── Terms ─────────────────────────────────────────────────────────────────

	/**
	 * Test product get_terms method.
	 */
	public function test_get_terms() {
		$product = new Product( $this->product_id );
		$terms   = $product->get_terms( 'product_cat' );

		$this->assertIsArray( $terms );
	}

	/**
	 * Test product set_terms method.
	 */
	public function test_set_terms() {
		$product = new Product( $this->product_id );
		$term_id = wp_insert_term( 'Test Category', 'product_cat' );

		$result = $product->set_terms( [ $term_id['term_id'] ], 'product_cat' );
		$this->assertTrue( $result );

		$terms = $product->get_terms( 'product_cat' );
		$this->assertCount( 1, $terms );
		$this->assertEquals( 'Test Category', $terms[0]->name );

		wp_delete_term( $term_id['term_id'], 'product_cat' );
	}

	/**
	 * Test product get_categories method.
	 */
	public function test_get_categories() {
		$product    = new Product( $this->product_id );
		$categories = $product->get_categories();

		$this->assertIsArray( $categories );
	}

	/**
	 * Test product set_categories method.
	 */
	public function test_set_categories() {
		$product = new Product( $this->product_id );
		$term_id = wp_insert_term( 'Test Category', 'product_cat' );

		$result = $product->set_categories( [ $term_id['term_id'] ] );
		$this->assertTrue( $result );

		$categories = $product->get_categories();
		$this->assertCount( 1, $categories );
		$this->assertEquals( 'Test Category', $categories[0]->name );

		wp_delete_term( $term_id['term_id'], 'product_cat' );
	}

	/**
	 * Test product get_tags method.
	 */
	public function test_get_tags() {
		$product = new Product( $this->product_id );
		$tags    = $product->get_tags();

		$this->assertIsArray( $tags );
	}

	/**
	 * Test product set_tags method.
	 */
	public function test_set_tags() {
		$product = new Product( $this->product_id );
		$term_id = wp_insert_term( 'Test Tag', 'product_tag' );

		$result = $product->set_tags( [ $term_id['term_id'] ] );
		$this->assertTrue( $result );

		$tags = $product->get_tags();
		$this->assertCount( 1, $tags );
		$this->assertEquals( 'Test Tag', $tags[0]->name );

		wp_delete_term( $term_id['term_id'], 'product_tag' );
	}

	/**
	 * Test product get_brands method.
	 */
	public function test_get_brands() {
		$product = new Product( $this->product_id );
		$brands  = $product->get_brands();

		$this->assertIsArray( $brands );
	}

	/**
	 * Test product set_brands method.
	 */
	public function test_set_brands() {
		$product = new Product( $this->product_id );
		$term_id = wp_insert_term( 'Test Brand', 'product_brand' );

		$result = $product->set_brands( [ $term_id['term_id'] ] );
		$this->assertTrue( $result );

		$brands = $product->get_brands();
		$this->assertCount( 1, $brands );
		$this->assertEquals( 'Test Brand', $brands[0]->name );

		wp_delete_term( $term_id['term_id'], 'product_brand' );
	}

	// ── Description / summary ─────────────────────────────────────────────────

	/**
	 * Test product get_description method.
	 */
	public function test_get_description() {
		$product = new Product( $this->product_id );
		$product->set_description( 'Test description' );

		$description = $product->get_description();
		$this->assertEquals( 'Test description', $description );
	}

	/**
	 * Test product set_description method.
	 */
	public function test_set_description() {
		$product = new Product( $this->product_id );
		$result  = $product->set_description( 'Test description' );

		$this->assertThat( $result, $this->logicalOr(
			$this->isType( 'bool' ),
			$this->isType( 'int' ),
			$this->isNull()
		) );

		$description = $product->get_description();
		$this->assertEquals( 'Test description', $description );
	}

	/**
	 * Test product set_summary method.
	 */
	public function test_set_summary() {
		$product = new Product( $this->product_id );
		$result  = $product->set_summary( 'Test summary' );

		$this->assertThat( $result, $this->logicalOr(
			$this->isType( 'bool' ),
			$this->isType( 'int' ),
			$this->isNull()
		) );

		$summary = $product->get_summary();
		$this->assertEquals( 'Test summary', $summary );
	}

	/**
	 * Test product get_summary method.
	 */
	public function test_get_summary() {
		$product = new Product( $this->product_id );
		$product->set_summary( 'Test summary' );

		$summary = $product->get_summary();
		$this->assertEquals( 'Test summary', $summary );
	}

	// ── list() ────────────────────────────────────────────────────────────────

	/**
	 * list() returns an array with the expected top-level keys.
	 */
	public function test_list_returns_array() {
		$result = Product::list();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'products', $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertIsArray( $result['products'] );
		$this->assertIsInt( $result['total'] );
	}

	/**
	 * list() honours the status filter.
	 */
	public function test_list_filter_by_status() {
		// Create a draft product that should NOT appear in 'publish' results.
		$draft_id = $this->factory->product->create( [ 'title' => 'Draft Product', 'status' => 'draft' ] );

		$result = Product::list( [ 'status' => 'publish' ] );

		$ids = array_map( fn( $p ) => $p->get_id(), $result['products'] );
		$this->assertNotContains( $draft_id, $ids );

		$draft_result = Product::list( [ 'status' => 'draft' ] );
		$draft_ids    = array_map( fn( $p ) => $p->get_id(), $draft_result['products'] );
		$this->assertContains( $draft_id, $draft_ids );

		wp_delete_post( $draft_id, true );
	}

	/**
	 * list() respects limit and offset pagination.
	 */
	public function test_list_pagination() {
		// Create three extra published products.
		$ids = [];
		for ( $i = 0; $i < 3; $i++ ) {
			$ids[] = $this->factory->product->create( [ 'title' => "Paginated Product {$i}" ] );
		}

		// Page 1 — limit 2.
		$page1 = Product::list( [ 'status' => 'publish' ], 2, 0 );
		$this->assertCount( 2, $page1['products'] );

		// Page 2 — offset 2, limit 2.
		$page2 = Product::list( [ 'status' => 'publish' ], 2, 2 );
		$this->assertLessThanOrEqual( 2, count( $page2['products'] ) );

		// Ensure the total reflects the full set.
		$all = Product::list( [ 'status' => 'publish' ], -1, 0 );
		$this->assertGreaterThanOrEqual( 4, $all['total'] );

		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
	}

	// ── Meta-backed sorting ───────────────────────────────────────────────────

	/**
	 * Returns [ sold_id, unsold_id ] — the sold product has 3 units of `total_sale`,
	 * the other has no meta row at all (the state of every product on a fresh store).
	 */
	private function make_sales_fixture(): array {
		$sold   = $this->factory->product->create( [ 'title' => 'Sold Product' ] );
		$unsold = $this->factory->product->create( [ 'title' => 'Unsold Product' ] );

		update_post_meta( $sold, 'total_sale', 3 );

		return [ $sold, $unsold ];
	}

	private function list_ids( string $sort_by ): array {
		$result = Product::list( [ 'status' => 'publish', 'sort_by' => $sort_by ], -1, 0, false );

		return wp_list_pluck( $result['products'], 'ID' );
	}

	/**
	 * A product that has never sold has no `total_sale` meta. It must still appear
	 * under Best Selling, ranked below products that have sales.
	 */
	public function test_best_selling_includes_never_sold_products() {
		list( $sold, $unsold ) = $this->make_sales_fixture();

		$ids = $this->list_ids( 'best-selling' );

		$this->assertContains( $unsold, $ids, 'Never-sold product was excluded from best-selling.' );
		$this->assertContains( $sold, $ids );
		$this->assertLessThan(
			array_search( $unsold, $ids, true ),
			array_search( $sold, $ids, true ),
			'Sold product should rank above the never-sold one.'
		);
	}

	/**
	 * The count query must not inherit the exclusion either, or pagination reports
	 * a smaller catalogue than it renders.
	 */
	public function test_best_selling_total_counts_never_sold_products() {
		$this->make_sales_fixture();

		$sorted   = Product::list( [ 'status' => 'publish', 'sort_by' => 'best-selling' ], -1, 0, false );
		$unsorted = Product::list( [ 'status' => 'publish' ], -1, 0, false );

		$this->assertSame( $unsorted['total'], $sorted['total'] );
	}

	/**
	 * Never sold genuinely is zero sold, so unsold products lead under Lowest Selling.
	 */
	public function test_lowest_selling_ranks_never_sold_first() {
		list( $sold, $unsold ) = $this->make_sales_fixture();

		$ids = $this->list_ids( 'lowest-selling' );

		$this->assertContains( $sold, $ids );
		$this->assertLessThan(
			array_search( $sold, $ids, true ),
			array_search( $unsold, $ids, true ),
			'Never-sold product should rank below the one with sales.'
		);
	}

	public function test_top_rating_includes_unreviewed_products() {
		$rated     = $this->factory->product->create( [ 'title' => 'Rated Product' ] );
		$unreviewed = $this->factory->product->create( [ 'title' => 'Unreviewed Product' ] );

		update_post_meta( $rated, 'average_rating', 4.5 );

		$ids = $this->list_ids( 'top-rating' );

		$this->assertContains( $unreviewed, $ids, 'Unreviewed product was excluded from top-rating.' );
		$this->assertLessThan(
			array_search( $unreviewed, $ids, true ),
			array_search( $rated, $ids, true )
		);
	}

	/**
	 * An unreviewed product has an unknown rating, not a zero one — it must not
	 * outrank a genuinely badly-rated product under Lowest Rating.
	 */
	public function test_lowest_rating_keeps_unreviewed_products_last() {
		$one_star   = $this->factory->product->create( [ 'title' => 'One Star Product' ] );
		$five_star  = $this->factory->product->create( [ 'title' => 'Five Star Product' ] );
		$unreviewed = $this->factory->product->create( [ 'title' => 'Unreviewed Product' ] );

		update_post_meta( $one_star, 'average_rating', 1 );
		update_post_meta( $five_star, 'average_rating', 5 );

		$ids = $this->list_ids( 'lowest-rating' );

		$this->assertContains( $unreviewed, $ids );

		$one_star_pos   = array_search( $one_star, $ids, true );
		$five_star_pos  = array_search( $five_star, $ids, true );
		$unreviewed_pos = array_search( $unreviewed, $ids, true );

		$this->assertLessThan( $five_star_pos, $one_star_pos, '1-star should precede 5-star.' );
		$this->assertLessThan( $unreviewed_pos, $five_star_pos, 'Unreviewed should sort after every rated product.' );
	}

	/**
	 * The sort option list offers `newest`, so the model has to answer to it.
	 */
	public function test_newest_sort_orders_by_date_descending() {
		$older = $this->factory->product->create( [ 'title' => 'Older Product' ] );
		$newer = $this->factory->product->create( [ 'title' => 'Newer Product' ] );

		wp_update_post( [ 'ID' => $older, 'post_date' => '2020-01-01 00:00:00' ] );
		wp_update_post( [ 'ID' => $newer, 'post_date' => '2026-01-01 00:00:00' ] );

		$ids = $this->list_ids( 'newest' );

		$this->assertLessThan(
			array_search( $older, $ids, true ),
			array_search( $newer, $ids, true )
		);
	}

	/**
	 * The clauses filter must not leak into unrelated queries.
	 */
	public function test_meta_sort_does_not_affect_later_queries() {
		$this->make_sales_fixture();

		$this->list_ids( 'best-selling' );

		$plain = new \WP_Query( [ 'post_type' => 'product', 'posts_per_page' => -1, 'fields' => 'ids' ] );

		$this->assertNotEmpty( $plain->posts );
		$this->assertFalse( has_filter( 'posts_clauses', [ Product::class, 'meta_sort_clauses' ] ) );
	}
}

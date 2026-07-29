<?php
/**
 * Test Database model.
 *
 * Uses existing EC tables (orders, order_items, etc.) so no temp-table creation
 * is required, avoiding prefix conflicts with the WP test harness.
 * WP_UnitTestCase rolls back every DB write between tests via transactions.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Database;

class DatabaseTest extends EasyCommerceTestCase {

	/**
	 * Database instance pointed at the 'orders' EC table.
	 *
	 * @var Database
	 */
	protected $db;

	// ── Lifecycle ────────────────────────────────────────────────────────────

	public function set_up(): void {
		parent::set_up();

		// Use the real orders table (created by the installer in bootstrap.php).
		$this->db = new Database( 'orders' );
	}

	public function tear_down(): void {
		parent::tear_down();
	}

	// ── Constructor / meta ────────────────────────────────────────────────────

	/**
	 * @covers Database::__construct
	 */
	public function test_constructor() {
		$db = new Database( 'orders' );

		$this->assertInstanceOf( Database::class, $db );
		$this->assertEquals( 'ec_test_ec_orders', $db->get_table() );
		$this->assertEquals( 'id', $db->get_primary_key() );
	}

	/**
	 * @covers Database::__construct
	 */
	public function test_constructor_with_custom_primary_key() {
		$db = new Database( 'orders', 'order_id' );

		$this->assertEquals( 'ec_test_ec_orders', $db->get_table() );
		$this->assertEquals( 'order_id', $db->get_primary_key() );
	}

	/**
	 * @covers Database::__construct
	 */
	public function test_constructor_with_no_table_name() {
		$db = new Database();

		// Table name becomes prefix only when null is passed.
		$this->assertEquals( 'ec_test_ec_', $db->get_table() );
		$this->assertEquals( 'id', $db->get_primary_key() );
	}

	// ── Table / prefix helpers ─────────────────────────────────────────────────

	/**
	 * @covers Database::set_table
	 */
	public function test_set_table() {
		$this->db->set_table( 'order_items' );

		$this->assertEquals( 'ec_test_ec_order_items', $this->db->get_table() );
	}

	/**
	 * @covers Database::get_table
	 */
	public function test_get_table() {
		$this->assertEquals( 'ec_test_ec_orders', $this->db->get_table() );
	}

	/**
	 * @covers Database::get_primary_key
	 */
	public function test_get_primary_key_default() {
		$this->assertEquals( 'id', $this->db->get_primary_key() );
	}

	/**
	 * @covers Database::get_wp_prefix
	 */
	public function test_get_wp_prefix_returns_wp_prefix() {
		global $wpdb;

		$prefix = $this->db->get_wp_prefix();

		$this->assertEquals( $wpdb->prefix, $prefix );
		$this->assertEquals( 'ec_test_', $prefix );
	}

	/**
	 * @covers Database::get_prefix
	 */
	public function test_get_prefix_returns_ec_prefix() {
		$prefix = $this->db->get_prefix();

		// get_prefix() returns wp_prefix + 'ec_'
		$this->assertEquals( 'ec_test_ec_', $prefix );
		$this->assertStringContainsString( 'ec_', $prefix );
	}

	// ── Instance / prepare ────────────────────────────────────────────────────

	/**
	 * @covers Database::get_instance
	 */
	public function test_get_instance() {
		$instance = $this->db->get_instance();

		$this->assertInstanceOf( 'wpdb', $instance );
	}

	/**
	 * @covers Database::prepare
	 */
	public function test_prepare() {
		$query = $this->db->prepare( 'SELECT * FROM orders WHERE id = %d', 42 );

		$this->assertIsString( $query );
		$this->assertStringContainsString( '42', $query );
	}

	/**
	 * @covers Database::prepare
	 */
	public function test_prepare_escapes_string_values() {
		$query = $this->db->prepare( 'SELECT * FROM orders WHERE status = %s', "pending' OR '1'='1" );

		// The injected single quotes must be escaped / safe — not literally present raw.
		$this->assertIsString( $query );
		$this->assertStringNotContainsString( "OR '1'='1", $query );
	}

	// ── exec ──────────────────────────────────────────────────────────────────

	/**
	 * @covers Database::exec
	 */
	public function test_exec_returns_array_result() {
		$results = $this->db->exec( "SELECT * FROM {$this->db->get_table()} LIMIT 1" );

		$this->assertIsArray( $results );
	}

	/**
	 * @covers Database::exec
	 */
	public function test_exec() {
		// Insert a real order row so exec has something to return.
		$id      = $this->insert_minimal_order();
		$results = $this->db->exec( "SELECT * FROM {$this->db->get_table()} WHERE id = {$id}" );

		$this->assertIsArray( $results );
		$this->assertCount( 1, $results );
	}

	// ── Insert / read ─────────────────────────────────────────────────────────

	/**
	 * @covers Database::insert_row
	 */
	public function test_insert_row_returns_positive_id() {
		$id = $this->insert_minimal_order();

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
	}

	/**
	 * @covers Database::insert_row
	 * @covers Database::get_by_id
	 */
	public function test_insert_row_and_get_by_id_roundtrip() {
		$id  = $this->insert_minimal_order( 'pending', 55.00 );
		$row = $this->db->get_by_id( $id );

		$this->assertIsObject( $row );
		$this->assertEquals( $id, (int) $row->id );
		$this->assertEquals( 'pending', $row->status );
	}

	/**
	 * @covers Database::insert_rows
	 */
	public function test_insert_rows() {
		$rows = array(
			$this->minimal_order_data( 'pending', 10.00 ),
			$this->minimal_order_data( 'completed', 20.00 ),
		);

		$inserted_ids = $this->db->insert_rows( $rows );

		$this->assertIsArray( $inserted_ids );
		$this->assertCount( 2, $inserted_ids );
		$this->assertGreaterThan( 0, $inserted_ids[0] );
		$this->assertGreaterThan( 0, $inserted_ids[1] );
	}

	// ── get_by_id ─────────────────────────────────────────────────────────────

	/**
	 * @covers Database::get_by_id
	 */
	public function test_get_by_id() {
		$id  = $this->insert_minimal_order( 'completed', 99.99 );
		$row = $this->db->get_by_id( $id );

		$this->assertIsObject( $row );
		$this->assertEquals( $id, (int) $row->id );
	}

	/**
	 * @covers Database::get_by_id
	 */
	public function test_get_by_id_returns_null_for_nonexistent() {
		$row = $this->db->get_by_id( 999999 );

		$this->assertNull( $row );
	}

	// ── get_row ──────────────────────────────────────────────────────────────

	/**
	 * @covers Database::get_row
	 */
	public function test_get_row_with_condition() {
		$this->insert_minimal_order( 'refunded', 77.00 );

		$row = $this->db->get_row( array( 'status' => 'refunded' ) );

		$this->assertIsObject( $row );
		$this->assertEquals( 'refunded', $row->status );
	}

	/**
	 * @covers Database::get_row
	 */
	public function test_get_row_returns_null_when_not_found() {
		$row = $this->db->get_row( array( 'status' => 'nonexistent_status_xyz' ) );

		$this->assertNull( $row );
	}

	// ── get_rows ──────────────────────────────────────────────────────────────

	/**
	 * @covers Database::get_rows
	 */
	public function test_get_rows_returns_array() {
		$results = $this->db->get_rows();

		$this->assertIsArray( $results );
	}

	/**
	 * @covers Database::get_rows
	 */
	public function test_get_rows_with_limit_and_offset() {
		// Insert three distinct rows.
		$this->insert_minimal_order( 'pending', 1.00 );
		$this->insert_minimal_order( 'pending', 2.00 );
		$this->insert_minimal_order( 'pending', 3.00 );

		$results = $this->db->get_rows( array(), 2, 1 );

		$this->assertIsArray( $results );
		$this->assertCount( 2, $results );
	}

	/**
	 * @covers Database::get_rows
	 */
	public function test_get_rows_with_conditions() {
		// orders.status is an ENUM whose valid value is 'on_hold' (underscore);
		// 'on-hold' is coerced to '' by MySQL, so use the real enum value.
		$this->insert_minimal_order( 'on_hold', 40.00 );
		$this->insert_minimal_order( 'completed', 40.00 );

		$results = $this->db->get_rows( array( array( 'status' => 'on_hold' ) ) );

		$this->assertIsArray( $results );
		$this->assertGreaterThanOrEqual( 1, count( $results ) );

		foreach ( $results as $result ) {
			$this->assertEquals( 'on_hold', $result->status );
		}
	}

	/**
	 * @covers Database::get_rows
	 */
	public function test_get_rows_with_nested_operator_condition() {
		$this->insert_minimal_order( 'pending', 200.00 );
		$this->insert_minimal_order( 'pending', 50.00 );

		$results = $this->db->get_rows( array( array( 'total' => array( '>=', 100 ) ) ) );

		$this->assertIsArray( $results );
		foreach ( $results as $result ) {
			$this->assertGreaterThanOrEqual( 100, (float) $result->total );
		}
	}

	/**
	 * @covers Database::get_rows
	 */
	public function test_get_rows_asc_and_desc_order() {
		$id1 = $this->insert_minimal_order( 'pending', 1.00 );
		$id2 = $this->insert_minimal_order( 'pending', 2.00 );

		$asc  = $this->db->get_rows( array(), 0, 0, 'ASC', 'id' );
		$desc = $this->db->get_rows( array(), 0, 0, 'DESC', 'id' );

		$this->assertIsArray( $asc );
		$this->assertIsArray( $desc );

		// ASC: first element id <= last element id.
		$this->assertLessThanOrEqual( (int) end( $asc )->id, (int) reset( $asc )->id );

		// DESC: first element id >= last element id.
		$this->assertGreaterThanOrEqual( (int) end( $desc )->id, (int) reset( $desc )->id );
	}

	// ── get_count ─────────────────────────────────────────────────────────────

	/**
	 * @covers Database::get_count
	 */
	public function test_get_count_returns_int() {
		$count = $this->db->get_count();

		$this->assertIsInt( $count );
		$this->assertGreaterThanOrEqual( 0, $count );
	}

	/**
	 * @covers Database::get_count
	 */
	public function test_get_count_after_insert() {
		$before = $this->db->get_count();

		$this->insert_minimal_order( 'processing', 30.00 );
		$this->insert_minimal_order( 'processing', 30.00 );

		$after = $this->db->get_count();

		$this->assertEquals( $before + 2, $after );
	}

	/**
	 * @covers Database::get_count
	 */
	public function test_get_count_with_conditions() {
		$this->insert_minimal_order( 'cancelled', 0.00 );

		$count = $this->db->get_count( array( array( 'status' => 'cancelled' ) ) );

		$this->assertGreaterThanOrEqual( 1, $count );
	}

	// ── update_row ────────────────────────────────────────────────────────────

	/**
	 * @covers Database::update_row
	 */
	public function test_update_row_changes_value() {
		$id = $this->insert_minimal_order( 'pending', 10.00 );

		$this->db->update_row( $id, array( 'status' => 'completed' ) );

		$row = $this->db->get_by_id( $id );
		$this->assertEquals( 'completed', $row->status );
	}

	/**
	 * @covers Database::update_row
	 */
	public function test_update_row() {
		$id     = $this->insert_minimal_order( 'pending', 10.00 );
		$result = $this->db->update_row( $id, array( 'status' => 'completed', 'total' => 99.00 ) );

		// wpdb::update() returns int (rows affected) or false.
		$this->assertNotFalse( $result );

		$row = $this->db->get_by_id( $id );
		$this->assertEquals( 'completed', $row->status );
	}

	/**
	 * @covers Database::update_rows
	 */
	public function test_update_rows() {
		$id1 = $this->insert_minimal_order( 'pending', 10.00 );
		$id2 = $this->insert_minimal_order( 'pending', 20.00 );

		$result = $this->db->update_rows( array(
			array( 'id' => $id1, 'data' => array( 'status' => 'completed' ) ),
			array( 'id' => $id2, 'data' => array( 'status' => 'completed' ) ),
		) );

		$this->assertTrue( $result );

		$this->assertEquals( 'completed', $this->db->get_by_id( $id1 )->status );
		$this->assertEquals( 'completed', $this->db->get_by_id( $id2 )->status );
	}

	// ── delete_row ────────────────────────────────────────────────────────────

	/**
	 * @covers Database::delete_row
	 */
	public function test_delete_row_removes_record() {
		$id = $this->insert_minimal_order( 'pending', 5.00 );

		$this->db->delete_row( $id );

		$row = $this->db->get_by_id( $id );
		$this->assertNull( $row );
	}

	/**
	 * @covers Database::delete_row
	 */
	public function test_delete_row() {
		$id     = $this->insert_minimal_order();
		$result = $this->db->delete_row( $id );

		$this->assertNotFalse( $result );
		$this->assertNull( $this->db->get_by_id( $id ) );
	}

	/**
	 * @covers Database::delete_rows
	 */
	public function test_delete_rows() {
		$id1 = $this->insert_minimal_order( 'pending', 10.00 );
		$id2 = $this->insert_minimal_order( 'pending', 20.00 );

		$result = $this->db->delete_rows( array( $id1, $id2 ) );

		$this->assertTrue( $result );
		$this->assertNull( $this->db->get_by_id( $id1 ) );
		$this->assertNull( $this->db->get_by_id( $id2 ) );
	}

	// ── get_data ──────────────────────────────────────────────────────────────

	/**
	 * @covers Database::get_data
	 */
	public function test_get_data_returns_array() {
		$data = $this->db->get_data();

		$this->assertIsArray( $data );
	}

	/**
	 * @covers Database::get_data
	 */
	public function test_get_data_with_specific_columns() {
		$this->insert_minimal_order( 'pending', 10.00 );

		$data = $this->db->get_data( 'id, status' );

		$this->assertIsArray( $data );
		$this->assertGreaterThan( 0, count( $data ) );

		$first = reset( $data );
		$this->assertObjectHasProperty( 'id', $first );
		$this->assertObjectHasProperty( 'status', $first );
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Build a minimal order data array for the ec_orders table.
	 */
	private function minimal_order_data( string $status = 'pending', float $total = 10.00 ): array {
		return array(
			'customer_id' => 1,
			'status'      => $status,
			'total'       => $total,
			'created_at'  => current_time( 'mysql' ),
		);
	}

	/**
	 * Insert a minimal order row and return the inserted ID.
	 */
	private function insert_minimal_order( string $status = 'pending', float $total = 10.00 ): int {
		return (int) $this->db->insert_row( $this->minimal_order_data( $status, $total ) );
	}
}

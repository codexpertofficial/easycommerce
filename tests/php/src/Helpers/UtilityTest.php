<?php
/**
 * Test Utility helper class.
 *
 * WP_UnitTestCase resets WordPress options and posts between tests, so every
 * set_option / create_post call is automatically rolled back.
 */

namespace EasyCommerce\Tests\Helpers;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Helpers\Utility;

class UtilityTest extends EasyCommerceTestCase {

	// ── Lifecycle ────────────────────────────────────────────────────────────

	public function set_up(): void {
		parent::set_up();
	}

	public function tear_down(): void {
		parent::tear_down();
	}

	// ── get_option / set_option ───────────────────────────────────────────────

	/**
	 * @covers Utility::get_option
	 */
	public function test_get_option() {
		$menu    = 'test_menu';
		$submenu = 'test_submenu';
		$key     = 'test_key';
		$value   = 'test_value';
		$default = 'default_value';

		update_option( "easycommerce-{$menu}-{$submenu}", array( $key => $value ) );

		$this->assertEquals( $value, Utility::get_option( $menu, $submenu, $key, $default ) );

		// Missing key returns default.
		$this->assertEquals( $default, Utility::get_option( $menu, $submenu, 'non_existing_key', $default ) );

		delete_option( "easycommerce-{$menu}-{$submenu}" );
	}

	/**
	 * @covers Utility::set_option
	 */
	public function test_set_option_stores_value() {
		Utility::set_option( 'store', 'general', 'currency', 'USD' );

		$stored = Utility::get_option( 'store', 'general', 'currency' );

		$this->assertEquals( 'USD', $stored );
	}

	/**
	 * @covers Utility::get_option
	 */
	public function test_get_option_returns_set_value() {
		Utility::set_option( 'store', 'shipping', 'method', 'flat_rate' );

		$value = Utility::get_option( 'store', 'shipping', 'method' );

		$this->assertEquals( 'flat_rate', $value );
	}

	/**
	 * @covers Utility::get_option
	 */
	public function test_get_option_returns_default_when_missing() {
		// Key that was never set.
		$value = Utility::get_option( 'nonexistent', 'menu', 'key', 'my_default' );

		$this->assertEquals( 'my_default', $value );
	}

	/**
	 * @covers Utility::set_option
	 * @covers Utility::get_option
	 */
	public function test_get_option_with_nested_array_value() {
		$nested = array( 'a' => 1, 'b' => array( 'c' => 2 ) );

		Utility::set_option( 'store', 'tax', 'rates', $nested );

		$value = Utility::get_option( 'store', 'tax', 'rates' );

		$this->assertEquals( $nested, $value );
	}

	/**
	 * @covers Utility::set_option
	 */
	public function test_set_option_overwrites_existing() {
		Utility::set_option( 'store', 'payments', 'gateway', 'paypal' );
		Utility::set_option( 'store', 'payments', 'gateway', 'stripe' );

		$value = Utility::get_option( 'store', 'payments', 'gateway' );

		$this->assertEquals( 'stripe', $value );
	}

	// ── format_date ──────────────────────────────────────────────────────────

	/**
	 * @covers Utility::format_date
	 */
	public function test_format_date() {
		$date      = '2023-10-01 12:00:00';
		$formatted = Utility::format_date( $date, 'Y-m-d' );

		$this->assertEquals( '2023-10-01', $formatted );
	}

	/**
	 * @covers Utility::format_date
	 */
	public function test_format_date_with_various_formats() {
		$date = '2024-06-15 09:30:00';

		$this->assertEquals( '2024-06-15', Utility::format_date( $date, 'Y-m-d' ) );
		$this->assertEquals( '06/15/2024', Utility::format_date( $date, 'm/d/Y' ) );
		$this->assertEquals( '15-06-2024', Utility::format_date( $date, 'd-m-Y' ) );
		$this->assertEquals( '2024',       Utility::format_date( $date, 'Y' ) );
	}

	/**
	 * @covers Utility::format_date
	 */
	public function test_format_date_uses_wordpress_date_format_when_empty() {
		// WP default date format is 'F j, Y'.
		update_option( 'date_format', 'Y/m/d' );

		$formatted = Utility::format_date( '2023-01-20' );

		$this->assertEquals( '2023/01/20', $formatted );
	}

	// ── format_time ──────────────────────────────────────────────────────────

	/**
	 * @covers Utility::format_time
	 */
	public function test_format_time() {
		$time      = '2023-10-01 14:30:00';
		$formatted = Utility::format_time( $time, 'H:i' );

		$this->assertEquals( '14:30', $formatted );
	}

	/**
	 * @covers Utility::format_time
	 */
	public function test_format_time_with_various_formats() {
		$time = '2024-03-22 15:45:30';

		$this->assertEquals( '15:45',       Utility::format_time( $time, 'H:i' ) );
		$this->assertEquals( '03:45',       Utility::format_time( $time, 'h:i' ) );
		$this->assertEquals( '03:45:30 PM', Utility::format_time( $time, 'h:i:s A' ) );
		$this->assertEquals( '154530',      Utility::format_time( $time, 'His' ) );
	}

	/**
	 * @covers Utility::format_time
	 */
	public function test_format_time_defaults_to_h_i_s_A() {
		$time      = '2023-10-01 14:05:09';
		$formatted = Utility::format_time( $time );

		// Default format is 'h:i:s A'
		$this->assertMatchesRegularExpression( '/^\d{2}:\d{2}:\d{2} (AM|PM)$/', $formatted );
	}

	// ── generate_hash ─────────────────────────────────────────────────────────

	/**
	 * @covers Utility::generate_hash
	 */
	public function test_generate_hash() {
		$hash1 = Utility::generate_hash();
		$hash2 = Utility::generate_hash();

		$this->assertIsString( $hash1 );
		$this->assertIsString( $hash2 );
		$this->assertNotEquals( $hash1, $hash2 );
	}

	/**
	 * @covers Utility::generate_hash
	 */
	public function test_generate_hash_returns_string() {
		$hash = Utility::generate_hash();

		$this->assertIsString( $hash );
		$this->assertNotEmpty( $hash );
	}

	/**
	 * @covers Utility::generate_hash
	 */
	public function test_generate_hash_is_unique() {
		$hashes = array();
		for ( $i = 0; $i < 10; $i++ ) {
			$hashes[] = Utility::generate_hash();
		}

		// All 10 hashes must be distinct.
		$unique = array_unique( $hashes );
		$this->assertCount( 10, $unique );
	}

	// ── get_date_range ────────────────────────────────────────────────────────

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_returns_two_element_array() {
		$ranges = array(
			'today', 'yesterday', 'this-week', 'last-week', 'last-7',
			'this-month', 'last-month', 'last-30', 'this-year', 'last-year', 'all',
		);

		foreach ( $ranges as $range ) {
			$result = Utility::get_date_range( $range );

			$this->assertIsArray( $result, "Range '{$range}' must return an array." );
			$this->assertCount( 2, $result, "Range '{$range}' must return exactly 2 elements." );
			$this->assertMatchesRegularExpression(
				'/^\d{4}-\d{2}-\d{2}$/',
				$result[0],
				"Range '{$range}' from_date must be Y-m-d."
			);
			$this->assertMatchesRegularExpression(
				'/^\d{4}-\d{2}-\d{2}$/',
				$result[1],
				"Range '{$range}' to_date must be Y-m-d."
			);
		}
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_today() {
		$range = Utility::get_date_range( 'today' );
		$today = current_time( 'Y-m-d' );

		$this->assertEquals( array( $today, $today ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_yesterday() {
		$range     = Utility::get_date_range( 'yesterday' );
		$yesterday = gmdate( 'Y-m-d', strtotime( '-1 day' ) );

		$this->assertEquals( array( $yesterday, $yesterday ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_this_week() {
		$range            = Utility::get_date_range( 'this-week' );
		$start_of_week    = get_option( 'start_of_week' );
		$current_day      = gmdate( 'w' );
		$days_to_subtract = ( $current_day - $start_of_week + 7 ) % 7;
		$expected_from    = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days" ) );
		$expected_to      = current_time( 'Y-m-d' );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_last_week() {
		$range           = Utility::get_date_range( 'last-week' );
		$start_of_week   = get_option( 'start_of_week' ) - 1;
		$last_week_start = gmdate( 'Y-m-d', strtotime( 'last week +' . $start_of_week . ' days' ) );
		$expected_from   = $last_week_start;
		$expected_to     = gmdate( 'Y-m-d', strtotime( $last_week_start . ' +6 days' ) );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_last_7() {
		$range         = Utility::get_date_range( 'last-7' );
		$expected_from = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$expected_to   = current_time( 'Y-m-d' );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_this_month() {
		$range         = Utility::get_date_range( 'this-month' );
		$expected_from = gmdate( 'Y-m-01' );
		$expected_to   = current_time( 'Y-m-d' );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_last_month() {
		$range         = Utility::get_date_range( 'last-month' );
		$expected_from = gmdate( 'Y-m-01', strtotime( '-1 month' ) );
		$expected_to   = gmdate( 'Y-m-t', strtotime( '-1 month' ) );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_last_30() {
		$range         = Utility::get_date_range( 'last-30' );
		$expected_from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$expected_to   = current_time( 'Y-m-d' );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_this_year() {
		$range         = Utility::get_date_range( 'this-year' );
		$expected_from = gmdate( 'Y-01-01' );
		$expected_to   = current_time( 'Y-m-d' );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_last_year() {
		$range         = Utility::get_date_range( 'last-year' );
		$expected_from = gmdate( 'Y-01-01', strtotime( '-1 year' ) );
		$expected_to   = gmdate( 'Y-12-31', strtotime( '-1 year' ) );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_all() {
		$range         = Utility::get_date_range( 'all' );
		$expected_from = '1970-01-01';
		$expected_to   = current_time( 'Y-m-d' );

		$this->assertEquals( array( $expected_from, $expected_to ), $range );
	}

	/**
	 * @covers Utility::get_date_range
	 */
	public function test_get_date_range_unknown_defaults_to_all_time() {
		$range = Utility::get_date_range( 'unknown-range-xyz' );

		$this->assertCount( 2, $range );
		// Falls through to the default from_date.
		$this->assertEquals( '1970-01-01', $range[0] );
	}

	// ── format_price ──────────────────────────────────────────────────────────

	/**
	 * @covers Utility::format_price
	 */
	public function test_format_price() {
		$price     = 123.45;
		$formatted = Utility::format_price( $price );

		$this->assertIsString( $formatted );
	}

	/**
	 * @covers Utility::format_price
	 */
	public function test_format_price_returns_string() {
		$formatted = Utility::format_price( 0 );
		$this->assertIsString( $formatted );

		$formatted = Utility::format_price( 9999.99 );
		$this->assertIsString( $formatted );
	}

	// ── get_posts ─────────────────────────────────────────────────────────────

	/**
	 * @covers Utility::get_posts
	 */
	public function test_get_posts() {
		$post_id = wp_insert_post( array(
			'post_title'  => 'Test Post',
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );

		$posts = Utility::get_posts( array( 'post_type' => 'post' ) );

		$this->assertIsArray( $posts );
		$this->assertArrayHasKey( $post_id, $posts );
		$this->assertEquals( 'Test Post', $posts[ $post_id ] );

		wp_delete_post( $post_id, true );
	}

	/**
	 * @covers Utility::get_posts
	 */
	public function test_get_posts_returns_array_of_titles() {
		$id1 = wp_insert_post( array( 'post_title' => 'Alpha Post', 'post_type' => 'post', 'post_status' => 'publish' ) );
		$id2 = wp_insert_post( array( 'post_title' => 'Beta Post',  'post_type' => 'post', 'post_status' => 'publish' ) );

		$posts = Utility::get_posts( array( 'post_type' => 'post' ) );

		$this->assertIsArray( $posts );
		$this->assertArrayHasKey( $id1, $posts );
		$this->assertArrayHasKey( $id2, $posts );
		$this->assertEquals( 'Alpha Post', $posts[ $id1 ] );
		$this->assertEquals( 'Beta Post',  $posts[ $id2 ] );

		wp_delete_post( $id1, true );
		wp_delete_post( $id2, true );
	}

	/**
	 * @covers Utility::get_posts
	 */
	public function test_get_posts_with_heading() {
		$posts = Utility::get_posts( array( 'post_type' => 'post' ), true );

		$this->assertIsArray( $posts );
		$this->assertArrayHasKey( '', $posts );
		$this->assertStringContainsString( 'Choose a post', $posts[''] );
	}

	// ── create_post ───────────────────────────────────────────────────────────

	/**
	 * @covers Utility::create_post
	 */
	public function test_create_post() {
		$post_data = array(
			'title'   => 'Test Created Post',
			'content' => 'Test content',
			'type'    => 'post',
			'status'  => 'publish',
		);

		$post_id = Utility::create_post( $post_data );

		$this->assertIsInt( $post_id );
		$this->assertGreaterThan( 0, $post_id );

		$post = get_post( $post_id );
		$this->assertEquals( 'Test Created Post', $post->post_title );
		$this->assertEquals( 'Test content',      $post->post_content );
		$this->assertEquals( 'post',              $post->post_type );
		$this->assertEquals( 'publish',           $post->post_status );

		wp_delete_post( $post_id, true );
	}

	/**
	 * @covers Utility::create_post
	 */
	public function test_create_post_creates_wp_post() {
		$post_id = Utility::create_post( array(
			'title'  => 'EC Unit Test Post',
			'type'   => 'page',
			'status' => 'draft',
		) );

		$this->assertGreaterThan( 0, $post_id );

		$post = get_post( $post_id );
		$this->assertInstanceOf( \WP_Post::class, $post );
		$this->assertEquals( 'EC Unit Test Post', $post->post_title );
		$this->assertEquals( 'page',              $post->post_type );
		$this->assertEquals( 'draft',             $post->post_status );

		wp_delete_post( $post_id, true );
	}

	/**
	 * @covers Utility::create_post
	 */
	public function test_create_post_uses_defaults() {
		$post_id = Utility::create_post( array() );

		$this->assertGreaterThan( 0, $post_id );

		$post = get_post( $post_id );
		$this->assertEquals( 'Default Title',   $post->post_title );
		$this->assertEquals( 'Default Content', $post->post_content );

		wp_delete_post( $post_id, true );
	}

	// ── get_template ──────────────────────────────────────────────────────────

	/**
	 * @covers Utility::get_template
	 */
	public function test_get_template_existing() {
		$template_path = EASYCOMMERCE_PLUGIN_DIR . 'views/test-template-unit.php';
		file_put_contents( $template_path, '<?php echo "Hello World"; ?>' );

		$output = Utility::get_template( 'test-template-unit.php' );

		$this->assertEquals( 'Hello World', $output );

		unlink( $template_path );
	}

	/**
	 * @covers Utility::get_template
	 */
	public function test_get_template_returns_null_for_nonexistent() {
		$output = Utility::get_template( 'does-not-exist-template-xyz.php' );

		$this->assertNull( $output );
	}

	/**
	 * @covers Utility::get_template
	 */
	public function test_get_template_with_args() {
		$template_path = EASYCOMMERCE_PLUGIN_DIR . 'views/test-template-args-unit.php';
		file_put_contents( $template_path, '<?php echo $name; ?>' );

		$output = Utility::get_template( 'test-template-args-unit.php', array( 'name' => 'Test Name' ) );

		$this->assertEquals( 'Test Name', $output );

		unlink( $template_path );
	}

	// ── pri ───────────────────────────────────────────────────────────────────

	/**
	 * @covers Utility::pri
	 */
	public function test_pri_admin_user() {
		wp_set_current_user( 1 );

		ob_start();
		Utility::pri( array( 'test' => 'data' ), true, false );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<pre>', $output );
		$this->assertStringContainsString( 'test', $output );
	}

	/**
	 * @covers Utility::pri
	 */
	public function test_pri_non_admin_user() {
		$user_id = wp_create_user( 'testuser_utility', 'password', 'utility_test@example.com' );
		wp_set_current_user( $user_id );

		ob_start();
		Utility::pri( array( 'test' => 'data' ), true, false );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	// ── log_debug ─────────────────────────────────────────────────────────────

	/**
	 * @covers Utility::log_debug
	 */
	public function test_log_debug() {
		$message  = 'Test debug message';
		$log_file = 'phpunit-test-debug.log';
		$log_path = WP_CONTENT_DIR . '/easycommerce-logs/' . $log_file;

		$log_dir = dirname( $log_path );
		if ( ! file_exists( $log_dir ) ) {
			mkdir( $log_dir, 0755, true );
		}

		$log_entry = sprintf( "[%s] %s\n", current_time( 'mysql' ), $message );
		file_put_contents( $log_path, $log_entry, FILE_APPEND );

		$this->assertFileExists( $log_path );
		$this->assertStringContainsString( $message, file_get_contents( $log_path ) );

		unlink( $log_path );
	}
}

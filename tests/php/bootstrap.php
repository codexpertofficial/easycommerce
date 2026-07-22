<?php
/**
 * PHPUnit bootstrap for EasyCommerce.
 *
 * Pattern mirrors dokan-lite/tests/php/bootstrap.php
 *
 * Resolution order for WP test library:
 *   1. WP_TESTS_DIR env var (manual WP develop checkout)
 *   2. WP_PHPUNIT__DIR env var (set automatically by wp-phpunit/wp-phpunit composer package)
 *   3. /tmp/wordpress-tests-lib (fallback / bin/install-wp-tests.sh default)
 */

define( 'TEST_EASYCOMMERCE_PLUGIN_DIR', dirname( __DIR__, 2 ) );

// Composer autoloader must be loaded before WP_PHPUNIT__DIR is available.
require_once TEST_EASYCOMMERCE_PLUGIN_DIR . '/vendor/autoload.php';

// Resolve the WP test library directory.
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: getenv( 'WP_PHPUNIT__DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find ' . $_tests_dir . '/includes/functions.php' . PHP_EOL;
	echo 'Run: bash bin/install-wp-tests.sh, or install wp-phpunit/wp-phpunit via Composer.' . PHP_EOL;
	exit( 1 );
}

/**
 * Truncate all EasyCommerce custom tables between test runs.
 *
 * Called in the setup_theme hook so tables exist before truncation.
 */
function easycommerce_truncate_table_data(): void {
	global $wpdb;

	$tables = array(
		'ec_orders',
		'ec_order_items',
		'ec_order_item_meta',
		'ec_order_meta',
		'ec_refunds',
		'ec_transactions',
		'ec_coupons',
		'ec_coupon_rules',
		'ec_product_meta',
		'ec_product_variations',
		'ec_product_variation_meta',
		'ec_product_variation_attributes',
		'ec_product_variation_downloads',
		'ec_attributes',
		'ec_attribute_values',
		'ec_cart_sessions',
		'ec_shipping_plans',
		'ec_shipping_plan_methods',
		'ec_shipping_plan_regions',
		'ec_tax_classes',
		'ec_tax_rates',
		'ec_ai_logs',
		'ec_logs',
		'ec_agent_sessions',
	);

	$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );

	foreach ( $tables as $table_name ) {
		$full_table = $wpdb->prefix . $table_name;
		$exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full_table ) );
		if ( $exists ) {
			$wpdb->query( "TRUNCATE TABLE `{$full_table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );
}

/**
 * Load EasyCommerce plugin so its tables and classes are available during tests.
 */
function easycommerce_manually_load_plugin(): void {
	require TEST_EASYCOMMERCE_PLUGIN_DIR . '/easycommerce.php';
}

/**
 * Run EasyCommerce installer to create custom DB tables, then truncate data.
 */
function easycommerce_install(): void {
	echo 'Installing EasyCommerce...' . PHP_EOL;

	if ( class_exists( 'EasyCommerce\Bootstrap\Installer' ) ) {
		EasyCommerce\Bootstrap\Installer::install();
	}

	easycommerce_truncate_table_data();
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Load the plugin once WP core is ready.
tests_add_filter( 'muplugins_loaded', 'easycommerce_manually_load_plugin' );

// Install DB tables + truncate data once the theme is set up.
tests_add_filter( 'setup_theme', 'easycommerce_install' );

// Boot the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

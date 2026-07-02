<?php
/**
 * WordPress test configuration for EasyCommerce PHPUnit tests.
 *
 * Mirrors the pattern from wc-affiliate/tests/php/phpunit-wp-config.php
 *
 * WARNING: These tests will DROP ALL TABLES in the database with the prefix
 * defined below. DO NOT use a production database.
 *
 * Set env vars to override defaults:
 *   WP_DB_NAME, WP_DB_USER, WP_DB_PASS, WP_DB_HOST
 */

// ABSPATH — use downloaded WP core (via wp-phpunit) if present, else walk up.
// __DIR__ = tests/php/ → 5 levels up = WP root (easycommerce-development/)
$_wp_dir = dirname( __DIR__, 2 ) . '/wordpress/';
if ( ! is_dir( $_wp_dir ) ) {
	$_wp_dir = dirname( __DIR__, 5 ) . '/';
}

define( 'ABSPATH', $_wp_dir );

define( 'WP_DEFAULT_THEME', 'default' );
define( 'WP_DEBUG', true );

// ── Database ──────────────────────────────────────────────────────────────────
// WARNING: all tables with the prefix below will be dropped on every test run.
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wp_phpunit_tests' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASS' ) ?: 'password' );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// ── Auth keys (test-only, not sensitive) ─────────────────────────────────────
define( 'AUTH_KEY', 'ec-test-auth-key' );
define( 'SECURE_AUTH_KEY', 'ec-test-secure-auth-key' );
define( 'LOGGED_IN_KEY', 'ec-test-logged-in-key' );
define( 'NONCE_KEY', 'ec-test-nonce-key' );
define( 'AUTH_SALT', 'ec-test-auth-salt' );
define( 'SECURE_AUTH_SALT', 'ec-test-secure-auth-salt' );
define( 'LOGGED_IN_SALT', 'ec-test-logged-in-salt' );
define( 'NONCE_SALT', 'ec-test-nonce-salt' );

$table_prefix = 'ec_test_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'EasyCommerce Test Blog' );

define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );

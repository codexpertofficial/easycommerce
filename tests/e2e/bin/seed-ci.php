<?php
/**
 * Provision a minimal EasyCommerce store for the e2e suite on a fresh
 * WordPress (used in CI, where the plugin is freshly installed via wp-env).
 *
 * Run:  wp eval-file wp-content/plugins/easycommerce/tests/e2e/bin/seed-ci.php
 *
 * Activation normally creates the store pages and enables Cash on Delivery
 * (see app/Bootstrap/Installer.php); this script re-asserts all of that
 * idempotently so the suite does not depend on activation-hook timing, then
 * adds the two things activation does not: a purchasable priced physical
 * product and a shipping method that covers the checkout country.
 *
 * It prints `EC_PRODUCT_ID=<id>` on the last line so the workflow can forward
 * the id to Playwright (EC_PRODUCT_ID), decoupling the checkout test from the
 * shop-page markup.
 *
 * @package EasyCommerce
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

/* 1. Cash on Delivery active (mirrors Installer::seed_default_payment_methods). */
$payment = get_option( 'easycommerce-payment-methods', array() );
if ( empty( $payment['active_methods'] ) ) {
	$payment['active_methods'] = array( 'cash-on-delivery' );
	update_option( 'easycommerce-payment-methods', $payment );
}

/* 2. Store pages (mirrors Installer store-page seeding). */
$store = get_option( 'easycommerce-general-store', array() );
$pages = array(
	'shop'      => array( 'title' => 'Shop', 'content' => '<!-- wp:easycommerce/template-2 {"ProductPerPage":9,"columns":3} /-->' ),
	'checkout'  => array( 'title' => 'Checkout', 'content' => '<!-- wp:shortcode -->[easycommerce-checkout]<!-- /wp:shortcode -->' ),
	'dashboard' => array( 'title' => 'Dashboard', 'content' => '<!-- wp:shortcode -->[easycommerce-dashboard]<!-- /wp:shortcode -->' ),
);
foreach ( $pages as $key => $data ) {
	if ( ! empty( $store[ $key ] ) && get_post( $store[ $key ] ) ) {
		continue;
	}
	$page_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $data['title'],
		'post_content' => $data['content'],
	) );
	if ( $page_id && ! is_wp_error( $page_id ) ) {
		$store[ $key ] = $page_id;
	}
}
update_option( 'easycommerce-general-store', $store );

/* 3. Purchasable priced physical product with a price_id=1 variation. */
$existing   = get_posts( array(
	'post_type'   => 'product',
	'post_status' => 'publish',
	'numberposts' => 1,
	'meta_key'    => '_ec_e2e_seed',
	'fields'      => 'ids',
) );
$product_id = $existing ? (int) $existing[0] : 0;

if ( ! $product_id ) {
	$product_id = wp_insert_post( array(
		'post_type'    => 'product',
		'post_status'  => 'publish',
		'post_title'   => 'E2E Test Product',
		'post_content' => 'Seeded for e2e checkout tests.',
	) );
	add_post_meta( $product_id, '_ec_e2e_seed', '1' );
}

$variations_table = $wpdb->prefix . 'ec_product_variations';
$has_variation    = $wpdb->get_var(
	$wpdb->prepare( "SELECT id FROM {$variations_table} WHERE product_id = %d AND price_id = 1", $product_id )
);
if ( ! $has_variation ) {
	// Cart::get_by_price() matches on product_id + price_id; price_id 1 is the
	// default the add-to-cart URL uses when no variation is given.
	$wpdb->insert(
		$variations_table,
		array(
			'product_id' => $product_id,
			'price_id'   => 1,
			'name'       => 'E2E Test Product',
			'sku'        => 'E2E-' . $product_id,
			'type'       => 'physical',
			'price'      => 49.00,
			'status'     => 'in_stock',
		)
	);
}

/* 4. Global flat-rate shipping (region '--' + '{country}--'); free, full range. */
$country       = getenv( 'EC_COUNTRY' ) ?: 'US';
$plans_table   = $wpdb->prefix . 'ec_shipping_plans';
$regions_table = $wpdb->prefix . 'ec_shipping_plan_regions';
$methods_table = $wpdb->prefix . 'ec_shipping_plan_methods';

$plan_id = $wpdb->get_var( "SELECT id FROM {$plans_table} WHERE name = 'E2E Global Shipping' LIMIT 1" );
if ( ! $plan_id ) {
	$wpdb->insert(
		$plans_table,
		array(
			'name'             => 'E2E Global Shipping',
			'description'      => 'Seeded for e2e checkout tests',
			'active'           => 1,
			'taxable'          => 0,
			'calculation_base' => 'price',
		)
	);
	$plan_id = (int) $wpdb->insert_id;

	foreach ( array( '--', $country . '--' ) as $code ) {
		$wpdb->insert( $regions_table, array( 'plan_id' => $plan_id, 'region_code' => $code, 'zip_code' => '' ) );
	}
	$wpdb->insert(
		$methods_table,
		array(
			'plan_id'  => $plan_id,
			'name'     => 'Flat Rate',
			'min_unit' => 'price',
			'min'      => 0,
			'max_unit' => 'price',
			'max'      => 9999999.99,
			'cost'     => 0,
		)
	);
}

/* Flush rewrite rules so /shop/, /checkout/ and product permalinks resolve. */
flush_rewrite_rules();

echo 'EC_PRODUCT_ID=' . (int) $product_id . "\n";

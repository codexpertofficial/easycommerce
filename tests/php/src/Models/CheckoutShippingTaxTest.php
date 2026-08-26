<?php
/**
 * Test checkout shipping and tax resolution.
 */

namespace EasyCommerce\Tests\Models;

use EasyCommerce\Tests\EasyCommerceTestCase;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Shipping_Plan;
use EasyCommerce\Models\Tax;
use EasyCommerce\API\Cart as Cart_API;

class CheckoutShippingTaxTest extends EasyCommerceTestCase {

	/**
	 * Tax class id created for the fixtures.
	 *
	 * @var int
	 */
	protected $tax_class_id;

	/**
	 * Created shipping plan ids, keyed by region level.
	 *
	 * @var array
	 */
	protected $plans = array();

	/**
	 * Inserted row ids to remove after each test, keyed by table.
	 *
	 * @var array
	 */
	protected $created = array();

	/**
	 * Insert a fixture row and remember it for cleanup.
	 *
	 * @param string $table Table name without the prefix.
	 * @param array  $data  Row data.
	 * @return int Inserted row id.
	 */
	protected function insert_fixture( $table, $data ) {
		$db = new Database( $table );
		$id = $db->insert_row( $data );

		$this->created[ $table ][] = $id;

		return $id;
	}

	public function set_up(): void {
		parent::set_up();

		// The payment method template opens a PHP session, which CLI cannot do.
		remove_all_actions( 'easycommerce/views/templates/checkout/payment_methods' );

		$this->create_tax_fixtures();
		$this->create_shipping_fixtures();
	}

	public function tear_down(): void {
		$this->delete_fixtures();

		parent::tear_down();
	}

	/**
	 * Plan ids as integers, since the model returns them as strings.
	 *
	 * @param array $plans Plans returned by the lookup.
	 * @return array
	 */
	protected function plan_ids( $plans ) {
		return array_map( 'absint', wp_list_pluck( $plans, 'id' ) );
	}

	// ── Fixtures ──────────────────────────────────────────────────────────────

	/**
	 * Create one tax class with country, state and city level rates.
	 */
	protected function create_tax_fixtures() {
		$this->tax_class_id = $this->insert_fixture(
			'tax_classes',
			array(
				'name'   => 'Test Tax',
				'status' => 1,
			)
		);

		$rows = array(
			array( 'country' => 'US', 'state' => null,      'city' => null,        'postcode' => null,           'rate' => 5.0000 ),
			array( 'country' => 'US', 'state' => 'Arizona', 'city' => null,        'postcode' => null,           'rate' => 20.0000 ),
			array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park',  'postcode' => null,           'rate' => 50.0000 ),
			array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park',  'postcode' => '86351',        'rate' => 75.0000 ),
			array( 'country' => 'US', 'state' => 'Nevada',  'city' => 'Las Vegas', 'postcode' => '89101, 89102', 'rate' => 65.0000 ),
			array( 'country' => 'BD', 'state' => null,      'city' => null,        'postcode' => null,           'rate' => 10.0000 ),
		);

		foreach ( $rows as $row ) {
			$this->insert_fixture(
				'tax_rates',
				array_merge(
					$row,
					array(
						'tax_class_id' => $this->tax_class_id,
						'priority'     => 1,
						'compound'     => 0,
					)
				)
			);
		}
	}

	/**
	 * Create one shipping plan per region granularity.
	 */
	protected function create_shipping_fixtures() {
		$fixtures = array(
			'country'   => array( 'region' => 'US--', 'zip' => '', 'cost' => 10.00 ),
			'state'     => array( 'region' => 'US-California-', 'zip' => '', 'cost' => 25.00 ),
			'city'      => array( 'region' => 'US-Arizona-Big Park', 'zip' => '', 'cost' => 30.00 ),
			'postcode'  => array( 'region' => 'US-Arizona-Big Park', 'zip' => '86351', 'cost' => 40.00 ),
			'zip_list'  => array( 'region' => 'US-Nevada-Las Vegas', 'zip' => '89101, 89102', 'cost' => 55.00 ),
			'zip_range' => array( 'region' => 'US-Oregon-', 'zip' => '97000...97999', 'cost' => 60.00 ),
		);

		foreach ( $fixtures as $level => $fixture ) {
			$plan_id = $this->insert_fixture(
				'shipping_plans',
				array(
					'name'             => 'Plan ' . $level,
					'description'      => '',
					'active'           => 1,
					'taxable'          => 1,
					'calculation_base' => 'price',
				)
			);

			$this->insert_fixture(
				'shipping_plan_regions',
				array(
					'plan_id'     => $plan_id,
					'region_code' => $fixture['region'],
					'zip_code'    => $fixture['zip'],
				)
			);

			$this->insert_fixture(
				'shipping_plan_methods',
				array(
					'plan_id'  => $plan_id,
					'name'     => 'method ' . $level,
					'min'      => 0.00,
					'max'      => 10000000.00,
					'cost'     => $fixture['cost'],
					'min_unit' => 'kg',
					'max_unit' => 'kg',
				)
			);

			$this->plans[ $level ] = $plan_id;
		}
	}

	/**
	 * Create a product with a single variation of the given type.
	 *
	 * @param string $type     physical or digital.
	 * @param float  $price    Unit price.
	 * @param int    $price_id Variation price id.
	 * @return array product_id and price_id, ready for a cart line.
	 */
	protected function create_variation( $type, $price, $price_id = 1 ) {
		$product_id = $this->factory->product->create( array( 'title' => 'PHPUnit ' . $type ) );

		$variation             = new Product_Variation();
		$variation->product_id = $product_id;

		$variation->set_name( 'Default' );
		$variation->set_sku( 'PHPUNIT-' . $type . '-' . $price_id );
		$variation->set_price( $price );
		$variation->set_price_id( $price_id );
		$variation->set_type( $type );
		$variation->set_status( 'in_stock' );

		$this->created['product_variations'][] = $variation->save();

		return array( 'product_id' => $product_id, 'price_id' => $price_id );
	}

	/**
	 * Build a cart holding the given lines.
	 *
	 * @param array $lines Each as [ product, quantity, rate ].
	 * @param array $data  Extra cart data (address, shipping_method, coupons, fees).
	 * @return Cart
	 */
	protected function make_item_cart( $lines, $data = array() ) {
		$items = array();

		foreach ( $lines as $line ) {
			$items[ $line['product']['product_id'] ][ $line['product']['price_id'] ] = array(
				'quantity'      => $line['quantity'],
				'free_quantity' => 0,
				'rate'          => $line['rate'],
				'price'         => $line['rate'] * $line['quantity'],
				'is_free'       => false,
			);
		}

		$cart               = new Cart( 'phpunit-cart-amounts' );
		$cart->cart['data'] = array_merge( array( 'items' => $items ), $data );

		return $cart;
	}

	/**
	 * The first method of a plan, as the checkout would offer it.
	 *
	 * @param string $level Fixture level key.
	 * @return object
	 */
	protected function plan_method( $level ) {
		$methods = ( new Shipping_Plan( $this->plans[ $level ] ) )->get_methods();

		return is_array( $methods ) ? reset( $methods ) : $methods;
	}

	/**
	 * Remove every row created for this test.
	 */
	protected function delete_fixtures() {
		$order = array( 'coupons', 'product_variations', 'shipping_plan_methods', 'shipping_plan_regions', 'shipping_plans', 'tax_rates', 'tax_classes' );

		foreach ( $order as $table ) {
			if ( empty( $this->created[ $table ] ) ) {
				continue;
			}

			$db = new Database( $table );

			foreach ( $this->created[ $table ] as $id ) {
				$db->delete_row( $id );
			}
		}

		$this->created = array();
		$this->plans   = array();
	}

	// ── Country code normalisation ────────────────────────────────────────────

	/**
	 * A country name coming from the checkout form resolves to its iso2 code.
	 */
	public function test_country_name_resolves_to_code() {
		$this->assertEquals( 'US', easycommerce_country_code( 'United States' ) );
		$this->assertEquals( 'BD', easycommerce_country_code( 'Bangladesh' ) );
	}

	/**
	 * A value that is already a code is returned untouched.
	 */
	public function test_country_code_is_returned_unchanged() {
		$this->assertEquals( 'US', easycommerce_country_code( 'US' ) );
	}

	/**
	 * An unknown value is passed through rather than nulled out.
	 */
	public function test_unknown_country_is_passed_through() {
		$this->assertEquals( 'Neverland', easycommerce_country_code( 'Neverland' ) );
		$this->assertEquals( '', easycommerce_country_code( '' ) );
	}

	// ── Postcode expressions ──────────────────────────────────────────────────

	/**
	 * A single postcode matches only itself, ignoring case and spacing.
	 */
	public function test_exact_postcode_expression() {
		$this->assertTrue( easycommerce_zip_code_matches( '86351', '86351' ) );
		$this->assertTrue( easycommerce_zip_code_matches( ' 86351 ', '86351' ) );
		$this->assertTrue( easycommerce_zip_code_matches( 'ab1 2cd', 'AB12CD' ) );
		$this->assertFalse( easycommerce_zip_code_matches( '86351', '86336' ) );
	}

	/**
	 * Comma and new line separated lists cover every entry.
	 */
	public function test_list_postcode_expression() {
		$this->assertTrue( easycommerce_zip_code_matches( '86351, 86336', '86336' ) );
		$this->assertTrue( easycommerce_zip_code_matches( "86351\n86336", '86336' ) );
		$this->assertFalse( easycommerce_zip_code_matches( '86351, 86336', '86400' ) );
	}

	/**
	 * Ranges are inclusive of both bounds.
	 */
	public function test_range_postcode_expression() {
		$this->assertTrue( easycommerce_zip_code_matches( '86300...86399', '86300' ) );
		$this->assertTrue( easycommerce_zip_code_matches( '86300...86399', '86351' ) );
		$this->assertTrue( easycommerce_zip_code_matches( '86300...86399', '86399' ) );
		$this->assertFalse( easycommerce_zip_code_matches( '86300...86399', '86400' ) );
	}

	/**
	 * A trailing star matches everything with that prefix.
	 */
	public function test_wildcard_postcode_expression() {
		$this->assertTrue( easycommerce_zip_code_matches( '863*', '86351' ) );
		$this->assertFalse( easycommerce_zip_code_matches( '863*', '86451' ) );
	}

	/**
	 * A hyphen is part of the postcode, never a range separator.
	 */
	public function test_hyphen_is_not_a_range_separator() {
		$this->assertTrue( easycommerce_zip_code_matches( '86351-1234', '86351-1234' ) );
		$this->assertFalse( easycommerce_zip_code_matches( '86351-1234', '86351' ) );
		$this->assertFalse( easycommerce_zip_code_matches( '86351-1234', '86800' ) );
		$this->assertTrue( easycommerce_zip_code_matches( '00-001', '00-001' ) );
	}

	/**
	 * An empty expression or postcode never matches.
	 */
	public function test_empty_postcode_expression_never_matches() {
		$this->assertFalse( easycommerce_zip_code_matches( '', '86351' ) );
		$this->assertFalse( easycommerce_zip_code_matches( '86351', '' ) );
	}

	// ── Shipping region matching ──────────────────────────────────────────────

	/**
	 * Regions stored by code are found when checkout submits the country name.
	 */
	public function test_shipping_plan_found_by_country_name() {
		$by_code = Shipping_Plan::get_by_location_address( 'US', 'Arizona', 'Big Park' );
		$by_name = Shipping_Plan::get_by_location_address( 'United States', 'Arizona', 'Big Park' );

		$this->assertNotEmpty( $by_code );
		$this->assertNotEmpty( $by_name );
		$this->assertEquals( $this->plan_ids( $by_code ), $this->plan_ids( $by_name ) );
	}

	/**
	 * Country only: a country region matches when nothing else is known.
	 */
	public function test_country_level_region_matches_without_state_or_city() {
		$plans = Shipping_Plan::get_by_location_address( 'US', '', '' );

		$this->assertNotEmpty( $plans );
		$this->assertContains( $this->plans['country'], $this->plan_ids( $plans ) );
	}

	/**
	 * Country + state: the state region wins over the country one.
	 */
	public function test_state_level_region_takes_precedence_over_country() {
		$plans = Shipping_Plan::get_by_location_address( 'US', 'California', 'Los Angeles' );

		$this->assertNotEmpty( $plans );
		$this->assertContains( $this->plans['state'], $this->plan_ids( $plans ) );
		$this->assertNotContains( $this->plans['country'], $this->plan_ids( $plans ) );
	}

	/**
	 * Country + state + city: the city region wins over state and country.
	 */
	public function test_city_level_region_takes_precedence() {
		$plans = Shipping_Plan::get_by_location_address( 'US', 'Arizona', 'Big Park' );

		$this->assertNotEmpty( $plans );
		$this->assertContains( $this->plans['city'], $this->plan_ids( $plans ) );
		$this->assertNotContains( $this->plans['country'], $this->plan_ids( $plans ) );
	}

	/**
	 * Country + state + city + postcode: the postcode region is the most specific.
	 */
	public function test_postcode_region_takes_precedence_over_city() {
		$plans = Shipping_Plan::get_by_location_address( 'US', 'Arizona', 'Big Park', '86351' );

		$this->assertNotEmpty( $plans );
		$this->assertContains( $this->plans['postcode'], $this->plan_ids( $plans ) );
		$this->assertNotContains( $this->plans['city'], $this->plan_ids( $plans ) );
	}

	/**
	 * A postcode outside the configured one falls back to the city region.
	 */
	public function test_unmatched_postcode_falls_back_to_city_region() {
		$plans = Shipping_Plan::get_by_location_address( 'US', 'Arizona', 'Big Park', '99999' );

		$this->assertNotEmpty( $plans );
		$this->assertContains( $this->plans['city'], $this->plan_ids( $plans ) );
		$this->assertNotContains( $this->plans['postcode'], $this->plan_ids( $plans ) );
	}

	/**
	 * A postcode list covers every value in the list, not just the first.
	 */
	public function test_postcode_list_matches_any_listed_value() {
		foreach ( array( '89101', '89102' ) as $postcode ) {
			$plans = Shipping_Plan::get_by_location_address( 'US', 'Nevada', 'Las Vegas', $postcode );

			$this->assertContains( $this->plans['zip_list'], $this->plan_ids( $plans ), $postcode . ' should be covered' );
		}
	}

	/**
	 * A postcode outside the list does not match the region.
	 */
	public function test_postcode_outside_the_list_does_not_match() {
		$plans = Shipping_Plan::get_by_location_address( 'US', 'Nevada', 'Las Vegas', '89999' );

		$this->assertNotContains( $this->plans['zip_list'], $this->plan_ids( $plans ) );
	}

	/**
	 * A postcode range covers its bounds and everything between them.
	 */
	public function test_postcode_range_matches_within_bounds() {
		foreach ( array( '97000', '97205', '97999' ) as $postcode ) {
			$plans = Shipping_Plan::get_by_location_address( 'US', 'Oregon', 'Portland', $postcode );

			$this->assertContains( $this->plans['zip_range'], $this->plan_ids( $plans ), $postcode . ' should be in range' );
		}
	}

	/**
	 * A postcode past the end of the range falls back to a wider region.
	 */
	public function test_postcode_outside_the_range_falls_back() {
		$plans = Shipping_Plan::get_by_location_address( 'US', 'Oregon', 'Portland', '98000' );

		$this->assertNotContains( $this->plans['zip_range'], $this->plan_ids( $plans ) );
		$this->assertContains( $this->plans['country'], $this->plan_ids( $plans ) );
	}

	/**
	 * A state with no region of its own falls back to the country level plan.
	 */
	public function test_state_without_region_falls_back_to_country_plan() {
		$plans = Shipping_Plan::get_by_location_address( 'US', 'Texas', 'Houston' );

		$this->assertNotEmpty( $plans );
		$this->assertContains( $this->plans['country'], $this->plan_ids( $plans ) );
	}

	/**
	 * A country with no configured region returns no plans.
	 */
	public function test_country_without_region_returns_no_plans() {
		$this->assertEmpty( Shipping_Plan::get_by_location_address( 'BD', 'Dhaka', '' ) );
	}

	/**
	 * An empty country returns no plans, so a cleared address drops shipping.
	 */
	public function test_empty_country_returns_no_plans() {
		$this->assertEmpty( Shipping_Plan::get_by_location_address( '', '', '' ) );
	}

	// ── Tax rate resolution ───────────────────────────────────────────────────

	/**
	 * The most specific matching rate wins: city over state over country.
	 */
	public function test_tax_rate_prefers_city_over_state_and_country() {
		$tax = new Tax();

		$this->assertEquals( 50.0, $tax->get_rate_for_location( 'US', 'Arizona', 'Big Park' ) );
		$this->assertEquals( 20.0, $tax->get_rate_for_location( 'US', 'Arizona', 'Phoenix' ) );
		$this->assertEquals( 5.0, $tax->get_rate_for_location( 'US', 'Texas', 'Houston' ) );
		$this->assertEquals( 5.0, $tax->get_rate_for_location( 'US', null, null ) );
	}

	/**
	 * Each country resolves to its own rate.
	 */
	public function test_tax_rate_is_scoped_per_country() {
		$tax = new Tax();

		$this->assertEquals( 10.0, $tax->get_rate_for_location( 'BD', null, null ) );
	}

	/**
	 * A country with no configured rate is not taxed.
	 */
	public function test_country_without_rate_is_not_taxed() {
		$tax = new Tax();

		$this->assertEquals( 0.0, $tax->get_rate_for_location( 'FR', null, null ) );
	}

	/**
	 * The tax class lookup honours the same specificity ordering.
	 */
	public function test_rate_by_location_uses_specificity_for_tax_class() {
		$tax = new Tax();

		$this->assertEquals( 50.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Arizona', 'Big Park' ) );
		$this->assertEquals( 20.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Arizona', null ) );
		$this->assertEquals( 5.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', null, null ) );
		$this->assertEquals( 0.0, $tax->get_rate_by_location( $this->tax_class_id, 'FR', null, null ) );
	}

	/**
	 * A postcode scoped rate applies only inside that postcode.
	 */
	public function test_postcode_rate_does_not_leak_to_the_rest_of_the_city() {
		$tax = new Tax();

		$this->assertEquals( 75.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Arizona', 'Big Park', '86351' ) );
		$this->assertEquals( 50.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Arizona', 'Big Park', '99999' ) );
		$this->assertEquals( 50.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Arizona', 'Big Park' ) );
	}

	/**
	 * Postcode is the most specific tax level, above city.
	 */
	public function test_postcode_rate_beats_city_state_and_country() {
		$tax = new Tax();

		$this->assertEquals( 75.0, $tax->get_rate_for_location( 'US', 'Arizona', 'Big Park', '86351' ) );
		$this->assertEquals( 50.0, $tax->get_rate_for_location( 'US', 'Arizona', 'Big Park' ) );
	}

	/**
	 * A tax postcode accepts the same list syntax as a shipping region.
	 */
	public function test_tax_postcode_accepts_a_list() {
		$tax = new Tax();

		$this->assertEquals( 65.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Nevada', 'Las Vegas', '89101' ) );
		$this->assertEquals( 65.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Nevada', 'Las Vegas', '89102' ) );
		$this->assertEquals( 5.0, $tax->get_rate_by_location( $this->tax_class_id, 'US', 'Nevada', 'Las Vegas', '89999' ) );
	}

	/**
	 * A postcode set on a rate survives a read and write round trip.
	 */
	public function test_rate_postcode_is_persisted_and_returned() {
		$tax      = new Tax();
		$class_id = $tax->create_class(
			array(
				'name'  => 'Round Trip',
				'rates' => array(
					array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park', 'postcode' => '86351', 'rate' => 12.5 ),
				),
			)
		);

		$this->created['tax_classes'][] = $class_id;

		$rates = ( new Tax( $class_id ) )->get_rates();

		foreach ( $rates as $rate ) {
			$this->created['tax_rates'][] = $rate['id'];
		}

		$this->assertCount( 1, $rates );
		$this->assertEquals( '86351', $rates[0]['postcode'] );
	}

	// ── Billing vs shipping address comparison ────────────────────────────────

	/**
	 * Build a cart with the two addresses set.
	 *
	 * @param array $billing  Billing address parts.
	 * @param array $shipping Shipping address parts.
	 * @return Cart
	 */
	protected function make_cart( $billing, $shipping ) {
		$cart = new Cart( 'phpunit-shipping-tax-cart' );

		$cart->cart['data']['address']['billing']  = $billing;
		$cart->cart['data']['address']['shipping'] = $shipping;

		return $cart;
	}

	/**
	 * Identical addresses count as the same.
	 */
	public function test_identical_addresses_are_same() {
		$address = array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' );

		$this->assertTrue( $this->make_cart( $address, $address )->is_shipping_same_as_billing() );
	}

	/**
	 * A different state in the same country is not the same address.
	 */
	public function test_different_state_is_not_same() {
		$cart = $this->make_cart(
			array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' ),
			array( 'country' => 'US', 'state' => 'California', 'city' => 'Los Angeles' )
		);

		$this->assertFalse( $cart->is_shipping_same_as_billing() );
	}

	/**
	 * A different city in the same state is not the same address.
	 */
	public function test_different_city_is_not_same() {
		$cart = $this->make_cart(
			array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' ),
			array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Phoenix' )
		);

		$this->assertFalse( $cart->is_shipping_same_as_billing() );
	}

	/**
	 * A different country is not the same address.
	 */
	public function test_different_country_is_not_same() {
		$cart = $this->make_cart(
			array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' ),
			array( 'country' => 'CA', 'state' => 'Ontario', 'city' => 'Toronto' )
		);

		$this->assertFalse( $cart->is_shipping_same_as_billing() );
	}

	// ── Cart amounts ──────────────────────────────────────────────────────────

	/**
	 * A digital only cart must not be charged for a leftover shipping method.
	 */
	public function test_digital_cart_is_not_charged_shipping() {
		$digital = $this->create_variation( 'digital', 50.00 );
		$method  = $this->plan_method( 'country' );

		$cart = $this->make_item_cart(
			array( array( 'product' => $digital, 'quantity' => 1, 'rate' => 50.00 ) ),
			array(
				'shipping_method' => $method->id,
				'address'         => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 0, $amounts['shipping_fee'] );
		$this->assertEquals( 0, $amounts['shipping_tax'] ?? 0 );
		$this->assertEquals( 52.50, $amounts['total'], 'a digital cart is subtotal plus tax only' );
	}

	/**
	 * A physical cart still pays for the selected method.
	 */
	public function test_physical_cart_is_charged_shipping() {
		$physical = $this->create_variation( 'physical', 50.00 );
		$method   = $this->plan_method( 'country' );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 50.00 ) ),
			array(
				'shipping_method' => $method->id,
				'address'         => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 10.00, $amounts['shipping_fee'] );
	}

	/**
	 * A blank shipping address falls back to billing for the shipping tax.
	 */
	public function test_blank_shipping_address_still_taxes_shipping() {
		$physical = $this->create_variation( 'physical', 100.00 );
		$method   = $this->plan_method( 'country' );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'shipping_method' => $method->id,
				'address'         => array(
					'billing'  => array( 'country' => 'US', 'state' => 'Arizona' ),
					'shipping' => array( 'country' => '', 'state' => '', 'city' => '' ),
				),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 10.00, $amounts['shipping_fee'] );
		$this->assertEquals( 2.00, $amounts['shipping_tax'] ?? 0, 'Arizona charges 20% on the $10 fee' );
	}

	/**
	 * A filled shipping address still wins over billing.
	 */
	public function test_shipping_address_drives_the_shipping_tax() {
		$physical = $this->create_variation( 'physical', 100.00 );
		$method   = $this->plan_method( 'country' );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'shipping_method' => $method->id,
				'address'         => array(
					'billing'  => array( 'country' => 'US', 'state' => 'Arizona' ),
					'shipping' => array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' ),
				),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 5.00, $amounts['shipping_tax'] ?? 0, 'Big Park charges 50% on the $10 fee' );
	}

	/**
	 * A positive fee is a charge, not a discount.
	 */
	public function test_a_positive_fee_adds_to_the_total() {
		$digital = $this->create_variation( 'digital', 100.00 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $digital, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'fees'    => array( 'cod' => 5 ),
				'address' => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 5.00, $amounts['fees'] );
		$this->assertEquals( 0, $amounts['discount_amount'] );
		$this->assertEquals( 110.00, $amounts['total'], 'subtotal plus 5% tax plus the $5 fee' );
	}

	/**
	 * Negative fees are capped by what is left of the subtotal.
	 */
	public function test_negative_fees_cannot_drive_the_total_below_zero() {
		$digital = $this->create_variation( 'digital', 100.00 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $digital, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'fees'    => array( 'first' => -100, 'second' => -100 ),
				'address' => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 100.00, $amounts['discount_amount'] );
		$this->assertEquals( 0.00, $amounts['total'] );
	}

	/**
	 * A negative fee reaches the item totals, so tax follows the paid base.
	 */
	public function test_a_negative_fee_lowers_the_taxable_base() {
		$digital = $this->create_variation( 'digital', 100.00 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $digital, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'fees'    => array( 'credit' => -50 ),
				'address' => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 2.50, $amounts['tax'], '5% of the $50 actually paid' );
		$this->assertEquals( 52.50, $amounts['total'] );
	}

	/**
	 * The fees amount is always present, since other plugins add it to the total.
	 */
	public function test_fees_amount_defaults_to_zero() {
		$digital = $this->create_variation( 'digital', 100.00 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $digital, 'quantity' => 1, 'rate' => 100.00 ) )
		);

		$this->assertSame( 0.0, (float) $cart->get( true )['amounts']['fees'] );
	}

	/**
	 * A second coupon on an already zeroed cart must not divide by zero.
	 */
	public function test_a_second_coupon_on_a_zeroed_cart_does_not_divide_by_zero() {
		$first  = $this->factory->coupon->create( array( 'code' => 'PHPUNITFULL', 'type' => 'percentage', 'offer' => 100 ) );
		$second = $this->factory->coupon->create( array( 'code' => 'PHPUNITHALF', 'type' => 'percentage', 'offer' => 50 ) );

		$this->created['coupons'][] = $first;
		$this->created['coupons'][] = $second;

		$one = $this->create_variation( 'digital', 60.00, 1 );
		$two = $this->create_variation( 'digital', 40.00, 2 );

		$cart = $this->make_item_cart(
			array(
				array( 'product' => $one, 'quantity' => 1, 'rate' => 60.00 ),
				array( 'product' => $two, 'quantity' => 1, 'rate' => 40.00 ),
			),
			array(
				'coupons' => array( 'PHPUNITFULL', 'PHPUNITHALF' ),
				'address' => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 100.00, $amounts['discount_amount'], 'the cart cannot be discounted past free' );
		$this->assertEquals( 0.00, $amounts['total'] );
	}

	/**
	 * Tax is accumulated unrounded and rounded once.
	 */
	public function test_tax_is_rounded_once_not_per_line() {
		$one   = $this->create_variation( 'digital', 1.25, 1 );
		$two   = $this->create_variation( 'digital', 1.25, 2 );
		$three = $this->create_variation( 'digital', 1.25, 3 );

		$cart = $this->make_item_cart(
			array(
				array( 'product' => $one, 'quantity' => 1, 'rate' => 1.25 ),
				array( 'product' => $two, 'quantity' => 1, 'rate' => 1.25 ),
				array( 'product' => $three, 'quantity' => 1, 'rate' => 1.25 ),
			),
			array( 'address' => array( 'billing' => array( 'country' => 'US' ) ) )
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 3.75, $amounts['subtotal'] );
		$this->assertEquals( 0.19, $amounts['tax'], '5% of the $3.75 base, not the sum of three rounded lines' );
		$this->assertEquals( 3.94, $amounts['total'] );
	}

	/**
	 * The discount shown is the discount subtracted, so the summary column foots.
	 */
	public function test_the_displayed_discount_is_the_one_subtracted() {
		$coupon = $this->factory->coupon->create( array( 'code' => 'PHPUNIT33', 'type' => 'percentage', 'offer' => 33 ) );

		$this->created['coupons'][] = $coupon;

		$product = $this->create_variation( 'digital', 19.99 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $product, 'quantity' => 3, 'rate' => 19.99 ) ),
			array(
				'coupons' => array( 'PHPUNIT33' ),
				'address' => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 59.97, $amounts['subtotal'] );
		$this->assertEquals( 19.79, $amounts['discount_amount'] );
		$this->assertEquals(
			round( $amounts['subtotal'] + $amounts['tax'] - $amounts['discount_amount'], 2 ),
			$amounts['total'],
			'the total must be built from the same rounded figures the summary shows'
		);
	}

	/**
	 * A percentage offer above 100 cannot drive the cart negative.
	 */
	public function test_an_over_100_percent_coupon_stops_at_free() {
		$coupon = $this->factory->coupon->create( array( 'code' => 'PHPUNIT150', 'type' => 'percentage', 'offer' => 150 ) );

		$this->created['coupons'][] = $coupon;

		$product = $this->create_variation( 'digital', 100.00 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $product, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'coupons' => array( 'PHPUNIT150' ),
				'address' => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 100.00, $amounts['discount_amount'] );
		$this->assertEquals( 0.00, $amounts['total'] );
	}

	/**
	 * An unchanged total does not ask the payment form to re-mount.
	 */
	public function test_payment_update_is_not_required_when_the_total_is_unchanged() {
		$product = $this->create_variation( 'digital', 19.99 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $product, 'quantity' => 3, 'rate' => 19.99 ) ),
			array( 'address' => array( 'billing' => array( 'country' => 'US' ) ) )
		);

		$cart->save();

		$reloaded = new Cart( 'phpunit-cart-amounts' );
		$formatted = $reloaded->get( true );

		$reloaded->delete();

		$this->assertArrayNotHasKey( 'payment_update_required', $formatted );
	}

	/**
	 * A real price change still asks the payment form to re-mount.
	 */
	public function test_payment_update_is_required_when_the_total_changes() {
		$product = $this->create_variation( 'digital', 19.99 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $product, 'quantity' => 3, 'rate' => 19.99 ) ),
			array( 'address' => array( 'billing' => array( 'country' => 'US' ) ) )
		);

		$cart->save();

		$reloaded = new Cart( 'phpunit-cart-amounts' );

		$reloaded->update_qty( $product['product_id'], $product['price_id'], 6 );
		$formatted = $reloaded->get( true );

		$reloaded->delete();

		$this->assertTrue( $formatted['payment_update_required'] );
	}

	/**
	 * template-2 renders no shipping row, so it must not add a shipping fee.
	 */
	public function test_the_digital_only_template_does_not_charge_shipping() {
		$physical = $this->create_variation( 'physical', 100.00 );
		$method   = $this->plan_method( 'country' );

		$settings = (array) get_option( 'easycommerce-checkout-settings', array() );
		update_option( 'easycommerce-checkout-settings', array_merge( $settings, array( 'checkout_template' => 'template-2' ) ) );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'shipping_method' => $method->id,
				'address'         => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		update_option( 'easycommerce-checkout-settings', $settings );

		$this->assertEquals( 0, $amounts['shipping_fee'] );
		$this->assertEquals( 0, $amounts['shipping_tax'] ?? 0 );
		$this->assertEquals( 105.00, $amounts['total'], 'the total must match what the summary shows' );
	}

	/**
	 * The other templates still charge it.
	 */
	public function test_the_full_templates_still_charge_shipping() {
		$physical = $this->create_variation( 'physical', 100.00 );
		$method   = $this->plan_method( 'country' );

		$settings = (array) get_option( 'easycommerce-checkout-settings', array() );
		update_option( 'easycommerce-checkout-settings', array_merge( $settings, array( 'checkout_template' => 'template-3' ) ) );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'shipping_method' => $method->id,
				'address'         => array( 'billing' => array( 'country' => 'US' ) ),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		update_option( 'easycommerce-checkout-settings', $settings );

		$this->assertEquals( 10.00, $amounts['shipping_fee'] );
	}

	/**
	 * A physical item is taxed where it is delivered, not where it is billed.
	 */
	public function test_physical_product_tax_follows_the_shipping_address() {
		$physical = $this->create_variation( 'physical', 100.00 );
		$method   = $this->plan_method( 'country' );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'shipping_method' => $method->id,
				'address'         => array(
					// Arizona is 20%, Big Park is 50%.
					'billing'  => array( 'country' => 'US', 'state' => 'Arizona' ),
					'shipping' => array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' ),
				),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 50.00, $amounts['tax'], 'the delivery rate, 50% of $100' );
		$this->assertEquals( 5.00, $amounts['shipping_tax'], 'shipping tax follows the same address' );
	}

	/**
	 * A download has no delivery address, so it is taxed where the customer is billed.
	 */
	public function test_digital_product_tax_follows_the_billing_address() {
		$digital = $this->create_variation( 'digital', 100.00 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $digital, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'address' => array(
					'billing'  => array( 'country' => 'US', 'state' => 'Arizona' ),
					'shipping' => array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' ),
				),
			)
		);

		$this->assertEquals( 20.00, $cart->get( true )['amounts']['tax'], 'the billing rate, 20% of $100' );
	}

	/**
	 * A mixed cart taxes each line on its own basis.
	 */
	public function test_a_mixed_cart_taxes_each_line_on_its_own_address() {
		$physical = $this->create_variation( 'physical', 100.00, 1 );
		$digital  = $this->create_variation( 'digital', 100.00, 2 );

		$cart = $this->make_item_cart(
			array(
				array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ),
				array( 'product' => $digital, 'quantity' => 1, 'rate' => 100.00 ),
			),
			array(
				'address' => array(
					'billing'  => array( 'country' => 'US', 'state' => 'Arizona' ),
					'shipping' => array( 'country' => 'US', 'state' => 'Arizona', 'city' => 'Big Park' ),
				),
			)
		);

		// 50% on the shipped item, 20% on the download.
		$this->assertEquals( 70.00, $cart->get( true )['amounts']['tax'] );
	}

	/**
	 * An emptied shipping address means the goods rate is not known yet.
	 */
	public function test_physical_product_is_untaxed_without_a_delivery_address() {
		$physical = $this->create_variation( 'physical', 100.00 );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ) ),
			array(
				'address' => array(
					'billing'  => array( 'country' => 'US', 'state' => 'Arizona' ),
					'shipping' => array( 'country' => '', 'state' => '', 'city' => '' ),
				),
			)
		);

		$amounts = $cart->get( true )['amounts'];

		$this->assertEquals( 0, $amounts['tax'], 'no delivery address, no goods rate' );
		$this->assertEquals( 100.00, $amounts['total'] );
	}

	/**
	 * template-2 collects no shipping address, so its goods stay on billing.
	 */
	public function test_physical_product_falls_back_to_billing_on_the_digital_template() {
		$physical = $this->create_variation( 'physical', 100.00 );

		$settings = (array) get_option( 'easycommerce-checkout-settings', array() );
		update_option( 'easycommerce-checkout-settings', array_merge( $settings, array( 'checkout_template' => 'template-2' ) ) );

		$cart = $this->make_item_cart(
			array( array( 'product' => $physical, 'quantity' => 1, 'rate' => 100.00 ) ),
			array( 'address' => array( 'billing' => array( 'country' => 'US', 'state' => 'Arizona' ) ) )
		);

		$amounts = $cart->get( true )['amounts'];

		update_option( 'easycommerce-checkout-settings', $settings );

		$this->assertEquals( 20.00, $amounts['tax'], 'nowhere else to read the location from' );
	}

	// ── Shipping tier boundaries ──────────────────────────────────────────────

	/**
	 * Run the tier filter the shipping lookup applies to one plan's matches.
	 *
	 * @param array $matched Rows with id, cost, min and max.
	 * @param mixed $value   The compared subtotal, weight or quantity.
	 * @return array Ids that survive, in order.
	 */
	protected function tier_ids( $matched, $value ) {
		$method = new \ReflectionMethod( Cart_API::class, 'drop_boundary_duplicates' );
		$method->setAccessible( true );

		return wp_list_pluck( $method->invoke( new Cart_API(), $matched, $value ), 'id' );
	}

	/**
	 * Adjacent tiers touching at a boundary offer one method, not two.
	 */
	public function test_adjacent_tiers_do_not_both_match_at_the_boundary() {
		$matched = array(
			array( 'id' => 1, 'name' => 'low', 'cost' => 5, 'min' => 0.00, 'max' => 50.00 ),
			array( 'id' => 2, 'name' => 'high', 'cost' => 9, 'min' => 50.00, 'max' => 100.00 ),
		);

		$this->assertSame( array( 1 ), $this->tier_ids( $matched, 50.00 ), 'the tier that ends here wins' );
	}

	/**
	 * Away from the boundary each tier is offered on its own.
	 */
	public function test_a_value_inside_one_tier_is_unaffected() {
		$low  = array( 'id' => 1, 'name' => 'low', 'cost' => 5, 'min' => 0.00, 'max' => 50.00 );
		$high = array( 'id' => 2, 'name' => 'high', 'cost' => 9, 'min' => 50.00, 'max' => 100.00 );

		$this->assertSame( array( 1 ), $this->tier_ids( array( $low ), 25.00 ) );
		$this->assertSame( array( 2 ), $this->tier_ids( array( $high ), 75.00 ) );
	}

	/**
	 * A single tier still matches a cart worth exactly its maximum.
	 */
	public function test_the_top_of_a_single_tier_still_matches() {
		$matched = array(
			array( 'id' => 1, 'name' => 'only', 'cost' => 5, 'min' => 0.00, 'max' => 100.00 ),
		);

		$this->assertSame( array( 1 ), $this->tier_ids( $matched, 100.00 ), 'half-open bounds would offer nothing here' );
	}

	/**
	 * Two service levels sharing one range are both still offered.
	 */
	public function test_methods_sharing_a_range_are_both_kept() {
		$matched = array(
			array( 'id' => 1, 'name' => 'standard', 'cost' => 5, 'min' => 1.00, 'max' => 100000.00 ),
			array( 'id' => 2, 'name' => 'express', 'cost' => 9, 'min' => 1.00, 'max' => 100000.00 ),
		);

		$this->assertSame( array( 1, 2 ), $this->tier_ids( $matched, 500.00 ) );
	}

	/**
	 * A converted weight boundary is compared with tolerance, not float equality.
	 */
	public function test_a_converted_weight_boundary_is_matched() {
		// 10.5 kg in grams, as the weight branch computes it.
		$boundary = 10.5 * 1000;

		$matched = array(
			array( 'id' => 1, 'name' => 'light', 'cost' => 5, 'min' => 0.0, 'max' => 10.5 * 1000 ),
			array( 'id' => 2, 'name' => 'heavy', 'cost' => 9, 'min' => 10.5 * 1000, 'max' => 20.0 * 1000 ),
		);

		$this->assertSame( array( 1 ), $this->tier_ids( $matched, $boundary ) );
	}

	/**
	 * A tier that both starts and ends at the value is not dropped.
	 */
	public function test_a_single_point_tier_survives() {
		$matched = array(
			array( 'id' => 1, 'name' => 'exact', 'cost' => 5, 'min' => 50.00, 'max' => 50.00 ),
		);

		$this->assertSame( array( 1 ), $this->tier_ids( $matched, 50.00 ) );
	}
}

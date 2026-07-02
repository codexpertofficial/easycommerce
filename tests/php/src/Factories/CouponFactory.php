<?php

namespace EasyCommerce\Tests\Factories;

use EasyCommerce\Models\Coupon;
use WP_UnitTest_Factory_For_Thing;

/**
 * Factory for EasyCommerce coupon fixtures.
 *
 * $coupon_id = $this->factory->coupon->create([
 *     'name'  => '10% Off',
 *     'code'  => 'SAVE10',
 *     'type'  => 'percentage',
 *     'offer' => 10,
 * ]);
 *
 * Common types: 'percentage', 'fixed', 'free_shipping', 'products'
 *
 * $coupon = $this->factory->coupon->create_and_get([...]);
 */
class CouponFactory extends WP_UnitTest_Factory_For_Thing {

	private static int $sequence = 0;

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'type'   => 'percentage',
			'offer'  => 10,
			'active' => 1,
		];
	}

	/**
	 * @param array $args
	 * @return int Coupon ID, or 0 on failure.
	 */
	public function create_object( $args ): int {
		self::$sequence++;
		$seq = self::$sequence;

		$coupon = new Coupon();

		$result = $coupon->create( [
			'name'   => $args['name'] ?? "Test Coupon {$seq}",
			'code'   => $args['code'] ?? "TESTCODE{$seq}",
			'type'   => $args['type'] ?? 'percentage',
			'offer'  => $args['offer'] ?? 10,
			'active' => $args['active'] ?? 1,
			'rules'  => $args['rules'] ?? [],
		] );

		return $result ? (int) $coupon->get_id() : 0;
	}

	/**
	 * @param int   $id
	 * @param array $fields
	 * @return bool|int
	 */
	public function update_object( $id, $fields ) {
		$coupon = new Coupon( $id );
		return $coupon->update( $fields );
	}

	/**
	 * @param int $id
	 * @return Coupon
	 */
	public function get_object_by_id( $id ): Coupon {
		return new Coupon( $id );
	}
}

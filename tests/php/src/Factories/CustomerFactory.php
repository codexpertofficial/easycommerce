<?php

namespace EasyCommerce\Tests\Factories;

use EasyCommerce\Models\Customer;
use WP_UnitTest_Factory_For_Thing;

/**
 * Factory for EasyCommerce customer fixtures.
 *
 * Creates a WordPress user and returns their ID. The EC Customer model
 * is a view over the WP user — no separate EC table needed.
 *
 * $customer_id = $this->factory->customer->create([
 *     'username' => 'john_doe',
 *     'email'    => 'john@example.com',
 *     'password' => 'secret',
 *     'role'     => 'subscriber',
 * ]);
 *
 * $customer = $this->factory->customer->create_and_get([...]);
 */
class CustomerFactory extends WP_UnitTest_Factory_For_Thing {

	private static int $sequence = 0;

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'role' => 'subscriber',
		];
	}

	/**
	 * @param array $args
	 * @return int WP user ID, or 0 on failure.
	 */
	public function create_object( $args ): int {
		self::$sequence++;
		$seq = self::$sequence;

		$username = $args['username'] ?? "ec_test_customer_{$seq}";
		$email    = $args['email'] ?? "ec_test_{$seq}@example.com";
		$password = $args['password'] ?? wp_generate_password( 12, false );

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			return 0;
		}

		if ( ! empty( $args['role'] ) && 'subscriber' !== $args['role'] ) {
			$user = new \WP_User( $user_id );
			$user->set_role( $args['role'] );
		}

		if ( ! empty( $args['first_name'] ) ) {
			update_user_meta( $user_id, 'first_name', $args['first_name'] );
		}
		if ( ! empty( $args['last_name'] ) ) {
			update_user_meta( $user_id, 'last_name', $args['last_name'] );
		}

		return $user_id;
	}

	/**
	 * @param int   $id
	 * @param array $fields
	 * @return bool|int
	 */
	public function update_object( $id, $fields ) {
		$fields['ID'] = $id;
		return wp_update_user( $fields );
	}

	/**
	 * @param int $id
	 * @return Customer
	 */
	public function get_object_by_id( $id ): Customer {
		return new Customer( $id );
	}
}

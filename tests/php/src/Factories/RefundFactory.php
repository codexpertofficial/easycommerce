<?php

namespace EasyCommerce\Tests\Factories;

use EasyCommerce\Models\Database;
use EasyCommerce\Models\Refund;
use WP_UnitTest_Factory_For_Thing;

/**
 * Factory for EasyCommerce refund fixtures.
 *
 * $refund_id = $this->factory->refund->create([
 *     'order_id'   => $order_id,
 *     'amount'     => 25.00,
 *     'status'     => 'approved',
 *     'created_at' => '2099-01-15 11:00:00',   // optional: override auto timestamp
 * ]);
 */
class RefundFactory extends WP_UnitTest_Factory_For_Thing {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'order_id'        => 0,
			'amount'          => 10.00,
			'status'          => 'approved',
			'payment_gateway' => 'manual',
		];
	}

	/**
	 * @param array $args
	 * @return int Refund ID.
	 */
	public function create_object( $args ): int {
		$refund = new Refund();

		$id = $refund->create( [
			'order_id'        => $args['order_id'],
			'amount'          => $args['amount'] ?? 10.00,
			'status'          => $args['status'] ?? 'approved',
			'payment_gateway' => $args['payment_gateway'] ?? 'manual',
			'reason'          => $args['reason'] ?? null,
			'notes'           => $args['notes'] ?? null,
		] );

		if ( ! $id ) {
			return 0;
		}

		if ( ! empty( $args['created_at'] ) ) {
			( new Database( 'refunds' ) )->update_row( $id, [ 'created_at' => $args['created_at'] ] );
		}

		return $id;
	}

	public function update_object( $id, $fields ) {
		return ( new Refund( $id ) )->update( $fields );
	}

	public function get_object_by_id( $id ): Refund {
		return new Refund( $id );
	}
}

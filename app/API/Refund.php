<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Refund as Refund_Model;
use EasyCommerce\Models\Order;
use WP_REST_Request;
use WP_REST_Response;

class Refund extends API {

	/**
	 * List refunds.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function list( $request ) {
		$order_id         = $request->get_param( 'order_id' );
		$status           = $request->get_param( 'status' );
		$payment_gateway  = $request->get_param( 'payment_gateway' );
		$from_date        = $request->get_param( 'from_date' );
		$to_date          = $request->get_param( 'to_date' );
		$per_page         = $request->get_param( 'per_page' ) ?: 10;
		$page             = $request->get_param( 'page' ) ?: 1;
		$offset           = ( $page - 1 ) * $per_page;

		$filters = array(
			'order_id'        => $order_id,
			'status'          => $status,
			'payment_gateway' => $payment_gateway,
			'from_date'       => $from_date,
			'to_date'         => $to_date,
		);

		$result = Refund_Model::list( $filters, $per_page, $offset );

		/**
		 * Filters the refund list result.
		 *
		 * @param array $result The result.
		 * @param array $filters The filters.
		 * @param WP_REST_Request $request The request object.
		 */
		$result = apply_filters( 'easycommerce_api_refund_list', $result, $filters, $request );

		$refunds      = $result['refunds'];
		$total        = $result['total'];
		$total_pages  = ceil( $total / $per_page );

		if ( empty( $refunds ) ) {
			return $this->response_success( array( 'total' => 0, 'message' => __( 'No refunds found', 'easycommerce' ) ) );
		}

		$formatted_refunds = array_map(
			function ( $refund ) {
				$order = new Order( $refund->order_id );
				$customer_name = $order->get_customer_name();

				$user_info = get_userdata( $refund->refunded_by );

				return array(
					'id'              => $refund->id,
					'order_id'        => $refund->order_id,
					'amount'          => $refund->amount,
					'customer'        => $customer_name,
					'currency'        => $refund->currency,
					'reason'          => $refund->reason,
					'status'          => $refund->status,
					'transaction_id'  => $refund->transaction_id,
					'payment_gateway' => $refund->payment_gateway,
					'notes'           => $refund->notes,
					'refunded_by'     => $user_info ? $user_info->display_name : null,
					'created_at'      => $refund->created_at,
					'updated_at'      => $refund->updated_at,
				);
			},
			$refunds
		);

		return $this->response_success(
			array(
				'refunds'     => $formatted_refunds,
				'total_amount'=> array_sum( wp_list_pluck( $formatted_refunds, 'amount' ) ),
				'total'       => $total,
				'per_page'    => $per_page,
				'page'        => $page,
				'total_pages' => $total_pages,
			)
		);
	}

	/**
	 * Get a refund.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get( $request ) {
		$id     = $request->get_param( 'id' );
		$refund = new Refund_Model( $id );

		if ( empty( $id ) || ! $refund->exists() ) {
			return $this->response_error( [ 'message' => __( 'Refund not found.', 'easycommerce' ) ] );
		}

		$refund_data = array(
			'id'              => $refund->get_id(),
			'order_id'        => $refund->get_order_id(),
			'amount'          => $refund->get_amount(),
			'currency'        => $refund->get_currency(),
			'reason'          => $refund->get_reason(),
			'status'          => $refund->get_status(),
			'transaction_id'  => $refund->get_transaction_id(),
			'payment_gateway' => $refund->get_payment_gateway(),
			'notes'           => $refund->get_notes(),
			'refunded_by'     => $refund->get_refunded_by(),
			'created_at'      => $refund->get_created_at(),
			'updated_at'      => $refund->get_updated_at(),
		);

		/**
		 * Filters the refund data before returning the response.
		 *
		 * @param array $refund_data The refund data.
		 * @param int $id The refund ID.
		 */
		$refund_data = apply_filters( 'easycommerce_get_refund_data', $refund_data, $id );

		/**
		 * Fires after a refund is retrieved.
		 *
		 * @param array $refund_data The refund data.
		 * @param int $id The refund ID.
		 */
		do_action( 'easycommerce_get_refund', $refund_data, $id );

		$this->response_success( $refund_data );
	}

	/**
	 * Create a new refund.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create( $request ) {
		$order_id        = $request->get_param( 'order_id' );
		$amount          = $request->get_param( 'amount' );

		// Get order to check existence and get payment method if needed
		$order = new Order( $order_id );
		if ( ! $order->exists() ) {
			return $this->response_error( [ 'message' => __( 'Order not found.', 'easycommerce' ) ] );
		}

		$allowed_statuses = array( 'completed', 'processing', 'partially_refunded' );
		if ( ! in_array( $order->get_status(), $allowed_statuses ) ) {
			return $this->response_error( [ 'message' => __( 'Refunds are not allowed for this order status.', 'easycommerce' ) ] );
		}

		if ( $amount <= 0 ) {
			return $this->response_error( [ 'message' => __( 'Refund amount must be greater than zero.', 'easycommerce' ) ] );
		}

		$currency        = $request->get_param( 'currency' );
		$reason          = $request->get_param( 'reason' );
		$payment_gateway = $request->get_param( 'payment_gateway' );
		$notes           = $request->get_param( 'notes' );

		$payment_method = easycommerce_payment_method_class( $payment_gateway );

		if ( $payment_method && $payment_method->supports_refund() ) {
			$do_refund = $payment_method->refund( $order_id, $reason, $amount );

			if ( ! $do_refund ) {
				return $this->response_error( [ 'message' => __( 'Failed to process refund through payment gateway.', 'easycommerce' ) ] );
			}

			$refund_transaction_id = $payment_method->refund_transaction_id();
			$refund_status         = 'approved';
		} else {
			$refund_transaction_id = $request->get_param( 'transaction_id' );
			$refund_status         = 'approved';
		}

		if ( empty( $payment_gateway ) ) {
			$payment_gateway = $order->get_payment_method();
		}

		$args = array(
			'order_id'        => $order_id,
			'amount'          => $amount,
			'currency'        => $currency,
			'reason'          => $reason,
			'status'          => $refund_status,
			'transaction_id'  => $refund_transaction_id,
			'payment_gateway' => $payment_gateway,
			'notes'           => $notes,
			'refunded_by'     => get_current_user_id(),
		);

		$args = apply_filters( 'easycommerce_create_refund_args', $args );

		do_action( 'easycommerce_before_create_refund', $args, $request );

		// Validate refund amount does not exceed due amount
		$order_total	= $order->get_total();
		$total_refunded = $order->get_total_refunded();
		$due_amount		= $order_total - $total_refunded;

		if ( $amount > $due_amount ) {
			return $this->response_error( [ 'message' => sprintf( __( 'Refund amount cannot exceed the due amount of %s.', 'easycommerce' ), $due_amount ) ] );
		}

		$refund  = new Refund_Model();
		$created = $refund->create( $args );

		do_action( 'easycommerce_after_create_refund', $refund->get_id(), $args, $request );

		if ( ! $created ) {
			return $this->response_error( [ 'message' => __( 'Failed to create refund.', 'easycommerce' ) ] );
		}

		$refund_data = array(
			'id'       => $refund->get_id(),
			'order_id' => $refund->get_order_id(),
			'amount'   => $refund->get_amount(),
		);

		do_action( 'easycommerce_create_refund', $refund_data );

		// Update order status based on total refunded
		$updated_total_refunded = $total_refunded + $amount;
		if ( $updated_total_refunded >= $order_total ) {
			$new_order_status = 'refunded';
		} else {
			$new_order_status = 'partially_refunded';
		}

		$order->set_status( $new_order_status );

		// Trigger refund email — refund record is already saved above so
		// get_total_refunded() will return the correct amount.
		do_action( 'easycommerce_order_email', $new_order_status, $order_id );

		do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'refund', 'object_id' => $refund->get_order_id(), 'meta' => array( 'refund_id' => $refund->get_id(), 'refund_amount' => $amount ), 'note' => easycommerce_price( $amount ) . ' refunded' ) );

		return $this->response_success(
			array(
				'message' => __( 'Refund created', 'easycommerce' ),
				'refund'  => $refund_data,
			)
		);
	}

	/**
	 * Update a refund.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|\WP_Error
	 */
	public function update( $request ) {
		$args   = apply_filters( 'easycommerce_update_refund_args', $request->get_params() );
		$refund = new Refund_Model( $request->get_param( 'id' ) );

		if ( ! $refund->exists() ) {
			return $this->response_error( [ 'message' => __( 'Refund not found.', 'easycommerce' ) ] );
		}

		$updated = $refund->update( $args );

		if ( ! $updated ) {
			return $this->response_error( [ 'message' => __( 'Failed to update refund.', 'easycommerce' ) ] );
		}

		$refund_data = array(
			'id'       => $refund->get_id(),
			'order_id' => $refund->get_order_id(),
			'amount'   => $refund->get_amount(),
		);

		do_action( 'easycommerce_update_refund', $refund_data );

		$this->response_success(
			array(
				'message' => __( 'Refund updated', 'easycommerce' ),
				'refund'  => $refund_data,
			)
		);
	}

	/**
	 * Delete a refund.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$id     = $request->get_param( 'id' );
		$refund = new Refund_Model( $id );

		if ( ! $refund->exists() ) {
			return $this->response_success( array( 'message' => __( 'Refund not found.', 'easycommerce' ) ) );
		}

		do_action( 'easycommerce_before_delete_refund', $id );

		$deleted = $refund->delete();

		if ( ! $deleted ) {
			return $this->response_error( [ 'message' => __( 'Failed to delete refund.', 'easycommerce' ) ] );
		}

		do_action( 'easycommerce_delete_refund', $id );

		return $this->response_success( [ 'message' => __( 'Refund deleted successfully.', 'easycommerce' ) ] );
	}

	/**
	 * Bulk delete refunds.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function bulk_delete( $request ) {
		$ids = $request->get_param( 'ids' );

		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return $this->response_error( [ 'message' => __( 'Invalid refund IDs.', 'easycommerce' ) ] );
		}

		$deleted = true;

		foreach ( $ids as $refund_id ) {
			$refund = new Refund_Model( $refund_id );

			if ( ! $refund->exists() ) {
				$deleted = false;
				continue;
			}

			do_action( 'easycommerce_before_delete_refund', $refund_id );

			$result = $refund->delete();

			if ( ! $result ) {
				$deleted = false;
				continue;
			}

			do_action( 'easycommerce_delete_refund', $refund_id );
		}

		if ( ! $deleted ) {
			return $this->response_error( [ 'message' => __( 'Failed to delete some refunds.', 'easycommerce' ) ] );
		}

		return $this->response_success( [ 'message' => __( 'Refunds deleted successfully.', 'easycommerce' ) ] );
	}
}

<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Transaction as Transaction_Model;
use EasyCommerce\Models\Customer;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Database;

class Transaction extends API {

	/**
	 * List transactions with optional filters like order ID, status, and pagination.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function list( $request ) {
		$order_id       = $request->get_param( 'order_id' );
		$page           = $request->get_param( 'page' ) ?: 1;
		$sort           = $request->get_param( 'sort' ) ?: 'desc';
		$per_page       = $request->get_param( 'per_page' ) ?: 10;
		$customer_id    = $request->get_param( 'customer_id' );
		$customer_email = $request->get_param( 'customer_email' );
		$from_date      = $request->get_param( 'from' );
		$to_date        = $request->get_param( 'to' );
		$type           = $request->get_param( 'type' );
		$filters        = array();

		if ( ! empty( $customer_id ) ) {
			$filters['customer_id'] = $customer_id;
		}

		if ( ! empty( $customer_email ) ) {
			$filters['customer_email'] = $customer_email;
		}

		if ( ! empty( $order_id ) ) {
			$filters['order_id'] = $order_id;
		}

		if ( ! empty( $from_date ) ) {
			$filters['from_date'] = $from_date;
		}

		if ( ! empty( $to_date ) ) {
			$filters['to_date'] = $to_date;
		}

		if ( ! empty( $type ) ) {
			$filters['type'] = $type;
		}
		// @todo improve logic
		// @todo remove is_user_logged_in() check when sandbox disabled
		if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_pages' ) ) {
			$filters['customer_id'] = get_current_user_id();
		}

		$result = Transaction_Model::list( $filters, $per_page, ( $page - 1 ) * $per_page, $sort );

		if ( empty( $result['transactions'] ) ) {
			$this->response_success( array( 'message' => __( 'No transactions found.', 'easycommerce' ) ) );
		}

		$formatted_transactions = array_map(
			function ( $transaction ) {
				$customer = new Customer( $transaction->customer_id );

				return array(
					'id'              => $transaction->id,
					'order_id'        => $transaction->order_id,
					'customer'        => array(
						'id'   => $customer->get_id(),
						'name' => $customer->get_name(),
					),
					'transaction_id'  => $transaction->transaction_id,
					'payment_gateway' => $transaction->payment_gateway,
					'amount'          => easycommerce_price( $transaction->amount ),
					'currency'        => $transaction->currency,
					'status'          => $transaction->status,
					'type'            => $transaction->type,
					'created_at'      => Utility::format_date( $transaction->created_at ),
					'created_time'    => Utility::format_time( $transaction->created_at ),
				);
			},
			$result['transactions']
		);
		$total_pages 		= ceil( $result['total'] / $per_page );
		$db 				= new Database( 'transactions' );
		$all_transactions 	= $db->get_rows();
		$types_counts 		= [];

		foreach ( $all_transactions as $transaction) {
			$type = $transaction->type;

			if ( ! isset( $types_counts[ $type ] ) ) {
				$types_counts[ $type ] = 0;
			}

			$types_counts[ $type ]++;
		}

		/**
		 * Filters the transactions list.
		 *
		 * @since 1.9
		 * @param array $formatted_transactions The formatted transactions.
		 * @param WP_REST_Request $request The request object.
		 */
		$formatted_transactions = apply_filters( 'easycommerce_list_transactions', $formatted_transactions, $request );

		$this->response_success(
			array(
				'transactions' => $formatted_transactions,
				'total'        => $result['total'],
				'page'         => $page,
				'per_page'     => $per_page,
				'total_pages'  => $total_pages,
				'types_counts' => $types_counts
			),
			200
		);
	}

	public function delete( $request ) {
		$id                 = $request->get_param( 'id' );

		/**
		 * Fires before deleting a transaction.
		 *
		 * @since 1.9
		 * @param int $id The transaction ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_transaction', $id, $request );

		$transaction_model  = new Transaction_Model();
		$deleted            = $transaction_model->delete( $id );

		/**
		 * Fires after deleting a transaction.
		 *
		 * @since 1.9
		 * @param int $id The transaction ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_transaction', $id, $request );

		/**
		 * Logs the transaction deletion event.
		 */
		$transaction = $transaction_model->get_by_id( $id );
		$note        = $transaction ? '#' . $id . ' - ' . $transaction->transaction_id : '#' . $id;
		do_action( 'easycommerce_log', array( 'object' => 'transaction', 'action' => 'delete', 'object_id' => $id, 'note' => $note ) );

		if ( ! $deleted ) {
			$this->response_error( __( 'Failed to delete transaction.', 'easycommerce' ), 500 );
		}

		$this->response_success(
			array(
				'message' => __( 'Transaction deleted successfully.', 'easycommerce' ),
			)
		);
	}
}

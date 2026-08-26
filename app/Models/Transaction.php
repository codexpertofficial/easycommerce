<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Model;

/**
 * Class Transaction
 * Handles transaction records in the database.
 *
 * @package EasyCommerce\Model
 */
class Transaction extends Model {

	protected $table = 'transactions';

	/**
	 * Transaction constructor.
	 * Initializes the class with the 'transactions' table.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get all transactions based on filters like order ID, status, pagination.
	 *
	 * @param array $filters Filters for querying transactions (e.g., order_id, status).
	 * @param int   $per_page Number of records per page.
	 * @param int   $offset Offset for pagination.
	 * @return array List of transactions.
	 */
	public static function list( $args = array(), $per_page = 10, $offset = 0, $sort = 'desc' ) {

		$db             = new Database( 'transactions' );
		$customer_id    = isset( $args['customer_id'] ) ? $args['customer_id'] : null;
		$customer_email = isset( $args['customer_email'] ) ? $args['customer_email'] : null;
		$order_id       = isset( $args['order_id'] ) ? $args['order_id'] : null;
		$from_date      = isset( $args['from_date'] ) ? trim( $args['from_date'] ) : null;
		$to_date        = isset( $args['to_date'] ) ? trim( $args['to_date'] ) : null;
		$type           = isset( $args['type'] ) ? $args['type'] : null;
		$where          = array();

		if ( ! empty( $customer_id ) ) {
			$where[] = array( 'customer_id' => $customer_id );
		}

		if ( ! empty( $customer_email ) ) {
			$customer_id = get_user_by( 'email', $customer_email )->ID;
			$where[]     = array( 'customer_id' => $customer_id );
		}

		if ( ! empty( $order_id ) ) {
			$where[] = array( 'order_id' => $order_id );
		}

		if ( ! empty( $from_date ) ) {
			$from_date = date( 'Y-m-d 00:00:00', strtotime( $from_date ) );
			$where[]   = array( 'created_at' => array( '>=', $from_date ) );
		}

		if ( ! empty( $to_date ) ) {
			$to_date = date( 'Y-m-d 23:59:59', strtotime( $to_date ) );
			$where[] = array( 'created_at' => array( '<=', $to_date ) );
		}

		if ( ! empty( $args['type'] ) ) {
			$where[] = array( 'type' => $args['type'] );
		}

		$total        = $db->get_count( $where );
		$transactions = $db->get_rows( $where, $per_page, $offset, $sort );

		return array(
			'transactions' => $transactions,
			'total'        => $total,
		);
	}

	/**
	 * Get all transactions for a specific order by order ID.
	 *
	 * @param int $order_id The order ID.
	 * @return array List of transactions.
	 */
	public function get_by_order_id( $order_id ) {
		return $this->db->get_rows( array( 'order_id' => $order_id ) );
	}

	/**
	 * Add a new transaction to the database.
	 *
	 * @param int         $order_id        The order ID.
	 * @param array       $data The transaction data (transaction ID, payment gateway, amount, etc.).
	 * @param bool|string $order_status If it should update the order status.
	 *
	 * @return bool|int Transaction ID on success, false on failure.
	 */
	public function add( $order_id, $data, $order_status = false ) {
 		$data = array(
 			'order_id'        => $order_id,
 			'customer_id'     => $data['customer_id'] ?? 0,
 			'transaction_id'  => $data['transaction_id'],
 			'payment_gateway' => $data['payment_gateway'] ?? $data['payment_method'] ?? '',
 			'amount'          => $data['amount'],
 			'currency'        => $data['currency'],
 			'status'          => $data['status'],
 			'type'            => $data['type'] ?? 'payment',

 		);

		if ( false !== $order_status ) {
			$order = new Order( $order_id );
			$order->set_status( $order_status );
		}

		// A gateway transaction id is globally unique. If it is already
		// recorded — e.g. a webhook and the return URL both fire for the same
		// charge — treat it as already-recorded and return the existing row id
		// instead of inserting a duplicate.
		if ( ! empty( $data['transaction_id'] ) ) {
			$existing = $this->get_by_transaction_id( $data['transaction_id'] );
			if ( $existing ) {
				return (int) $existing->id;
			}
		}

		// insert_row() returns null on failure; normalise to the documented
		// false-on-failure contract so a strict === false check reads correctly.
		$result = $this->db->insert_row( $data );

		// Lost the check-then-insert race: the UNIQUE(transaction_id) index
		// rejected this duplicate. Re-read and treat it as already-recorded.
		if ( null === $result && ! empty( $data['transaction_id'] ) ) {
			$existing = $this->get_by_transaction_id( $data['transaction_id'] );
			if ( $existing ) {
				return (int) $existing->id;
			}
		}

		return null === $result ? false : $result;
	}

	/**
	 * Get a single transaction by its gateway transaction id.
	 *
	 * @param string $transaction_id The gateway transaction id.
	 * @return object|null The transaction object if found, otherwise null.
	 */
	public function get_by_transaction_id( $transaction_id ) {
		$rows = $this->db->get_rows( array( 'transaction_id' => $transaction_id ), 1 );

		return ! empty( $rows ) ? $rows[0] : null;
	}

	/**
	 * Update an existing transaction.
	 *
	 * @param int         $transaction_id The transaction ID.
	 * @param array       $data           Updated transaction data (amount, status, etc.).
	 * @param bool|string $order_status If it should update the order status.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function update( $transaction_id, $data, $order_status = false ) {

		if ( false !== $order_status ) {
			$transaction_data = $this->db->get_by_id( $transaction_id );
			if ( $transaction_data ) {
				$order = new Order( $transaction_data->order_id );
				$order->set_status( $order_status );
			}
		}

		return $this->db->update_row( $transaction_id, $data );
	}

	/**
	 * Delete a transaction by its transaction ID.
	 *
	 * @param int $transaction_id The transaction ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $transaction_id ) {
		return $this->db->delete_row( $transaction_id );
	}

	/**
	 * Get a specific transaction by its ID.
	 *
	 * @param int $transaction_id The transaction ID.
	 * @return object|null The transaction object if found, otherwise null.
	 */
	public function get_by_id( $transaction_id ) {
		return $this->db->get_by_id( $transaction_id );
	}
}

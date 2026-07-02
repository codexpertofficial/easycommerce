<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Model;

/**
 * Class Refund
 * Handles refund records in the database.
 *
 * @package EasyCommerce\Model
 */
class Refund extends Model {

	/**
	 * @var int Refund ID
	 */
	protected $id;

	/**
	 * @var int Order ID
	 */
	protected $order_id;

	/**
	 * @var float Refund amount
	 */
	protected $amount = 0.0;

	/**
	 * @var string Currency
	 */
	protected $currency = '';

	/**
	 * @var string Reason for refund
	 */
	protected $reason = '';

	/**
	 * @var string Refund status
	 */
	protected $status = 'pending';

	/**
	 * @var string Transaction ID from gateway
	 */
	protected $transaction_id = '';

	/**
	 * @var string Payment gateway
	 */
	protected $payment_gateway = '';

	/**
	 * @var string Notes
	 */
	protected $notes = '';

	/**
	 * @var int Refunded by user ID
	 */
	protected $refunded_by;

	/**
	 * @var string Created at
	 */
	protected $created_at = '';

	/**
	 * @var string Updated at
	 */
	protected $updated_at = '';

	/**
	 * @var bool Refund existence flag
	 */
	protected $exists = false;

	protected $table = 'refunds';

	/**
	 * Constructor for the Refund class.
	 *
	 * @param int|null $id Optional. The refund ID.
	 */
	public function __construct( $id = null ) {
		parent::__construct();

		if ( $id && $refund = $this->db->get_by_id( $id ) ) {
			$this->id              = $id;
			$this->order_id        = $refund->order_id;
			$this->amount          = $refund->amount;
			$this->currency        = $refund->currency;
			$this->reason          = $refund->reason;
			$this->status          = $refund->status;
			$this->transaction_id  = $refund->transaction_id;
			$this->payment_gateway = $refund->payment_gateway;
			$this->notes           = $refund->notes;
			$this->refunded_by     = $refund->refunded_by;
			$this->created_at      = $refund->created_at;
			$this->updated_at      = $refund->updated_at;
			$this->exists          = true;
		}
	}

	/**
	 * Get the refund ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Check if the refund exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Get the order ID.
	 *
	 * @return int
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * Get the refund amount.
	 *
	 * @return float
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Get the currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Get the refund reason.
	 *
	 * @return string
	 */
	public function get_reason() {
		return $this->reason;
	}

	/**
	 * Get the refund status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the refund status.
	 *
	 * @param string $status
	 * @return bool|int
	 */
	public function set_status( $status ) {
		return $this->update( array( 'status' => $status ) );
	}

	/**
	 * Get the transaction ID.
	 *
	 * @return string
	 */
	public function get_transaction_id() {
		return $this->transaction_id;
	}

	/**
	 * Get the payment gateway.
	 *
	 * @return string
	 */
	public function get_payment_gateway() {
		return $this->payment_gateway;
	}

	/**
	 * Get the notes.
	 *
	 * @return string
	 */
	public function get_notes() {
		return $this->notes;
	}

	/**
	 * Get the refunded by user ID.
	 *
	 * @return int
	 */
	public function get_refunded_by() {
		return $this->refunded_by;
	}

	/**
	 * Get the created at timestamp.
	 *
	 * @return string
	 */
	public function get_created_at() {
		return $this->created_at;
	}

	/**
	 * Get the updated at timestamp.
	 *
	 * @return string
	 */
	public function get_updated_at() {
		return $this->updated_at;
	}

	/**
	 * Get the associated order object.
	 *
	 * @return Order|null
	 */
	public function get_order() {
		if ( $this->order_id ) {
			return new Order( $this->order_id );
		}
		return null;
	}

	/**
	 * Create a new refund.
	 *
	 * @param array $args Refund data.
	 * @return bool|int Refund ID on success, false on failure.
	 */
	public function create( $args ) {
		if ( ! isset( $args['order_id'] ) || ! isset( $args['amount'] ) ) {
			return false;
		}

		$data = array(
			'order_id'        => $args['order_id'],
			'amount'          => $args['amount'],
			'currency'        => $args['currency'] ?? 'USD',
			'reason'          => $args['reason'] ?? null,
			'status'          => $args['status'] ?? 'pending',
			'transaction_id'  => $args['transaction_id'] ?? null,
			'payment_gateway' => $args['payment_gateway'],
			'notes'           => $args['notes'] ?? null,
			'refunded_by'     => $args['refunded_by'] ?? null,
			'created_at'      => current_time( 'mysql' ),
		);

		$refund_id = $this->db->insert_row( $data );

		if ( $refund_id ) {
			$this->id              = $refund_id;
			$this->order_id        = $data['order_id'];
			$this->amount          = $data['amount'];
			$this->currency        = $data['currency'];
			$this->reason          = $data['reason'];
			$this->status          = $data['status'];
			$this->transaction_id  = $data['transaction_id'];
			$this->payment_gateway = $data['payment_gateway'];
			$this->notes           = $data['notes'];
			$this->refunded_by     = $data['refunded_by'];
			$this->created_at      = $data['created_at'];
			$this->updated_at      = $data['created_at']; // Initially set to created_at
			$this->exists          = true;

			return $refund_id;
		}

		return false;
	}

	/**
	 * Update the refund.
	 *
	 * @param array $data Updated refund data.
	 * @return bool|int Number of affected rows on success, false on failure.
	 */
	public function update( $data ) {
		if ( ! $this->exists ) {
			return false;
		}

		$result = $this->db->update_row( $this->id, $data );
		if ( $result ) {
			// Update instance properties
			foreach ( $data as $key => $value ) {
				if ( property_exists( $this, $key ) ) {
					$this->{$key} = $value;
				}
			}
			// Update updated_at if not provided
			if ( ! isset( $data['updated_at'] ) ) {
				$this->updated_at = current_time( 'mysql' );
				$this->db->update_row( $this->id, array( 'updated_at' => $this->updated_at ) );
			}
		}
		return $result;
	}

	/**
	 * Delete the refund.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if ( ! $this->exists ) {
			return false;
		}

		return $this->db->delete_row( $this->id );
	}

	/**
	 * Get all refunds based on filters like order ID, status, pagination.
	 *
	 * @param array $filters Filters for querying refunds (e.g., order_id, status).
	 * @param int   $per_page Number of records per page.
	 * @param int   $offset Offset for pagination.
	 * @return array List of refunds.
	 */
	public static function list( $args = array(), $per_page = 10, $offset = 0, $sort = 'desc' ) {

		$db             = new Database( 'refunds' );
		$order_id       = isset( $args['order_id'] ) ? $args['order_id'] : null;
		$status         = isset( $args['status'] ) ? $args['status'] : null;
		$payment_gateway = isset( $args['payment_gateway'] ) ? $args['payment_gateway'] : null;
		$from_date      = isset( $args['from_date'] ) ? trim( $args['from_date'] ) : null;
		$to_date        = isset( $args['to_date'] ) ? trim( $args['to_date'] ) : null;
		$where          = array();

		if ( ! empty( $order_id ) ) {
			$where[] = array( 'order_id' => $order_id );
		}

		if ( ! empty( $status ) ) {
			$where[] = array( 'status' => $status );
		}

		if ( ! empty( $payment_gateway ) ) {
			$where[] = array( 'payment_gateway' => $payment_gateway );
		}

		if ( ! empty( $from_date ) ) {
			$from_date = date( 'Y-m-d 00:00:00', strtotime( $from_date ) );
			$where[]   = array( 'created_at' => array( '>=', $from_date ) );
		}

		if ( ! empty( $to_date ) ) {
			$to_date = date( 'Y-m-d 23:59:59', strtotime( $to_date ) );
			$where[] = array( 'created_at' => array( '<=', $to_date ) );
		}

		$total   = $db->get_count( $where );
		$refunds = $db->get_rows( $where, $per_page, $offset, $sort );

		return array(
			'refunds' => $refunds,
			'total'   => $total,
		);
	}

	/**
	 * Get all refunds for a specific order by order ID.
	 *
	 * @param int $order_id The order ID.
	 * @return array List of refunds.
	 */
	public function get_by_order_id( $order_id ) {
		return $this->db->get_rows( array( 'order_id' => $order_id ) );
	}

	/**
	 * Get a specific refund by its ID (static method).
	 *
	 * @param int $refund_id The refund ID.
	 * @return Refund|null The refund object if found, otherwise null.
	 */
	public static function get_by_id( $refund_id ) {
		$refund = new self( $refund_id );
		return $refund->exists() ? $refund : null;
	}
}
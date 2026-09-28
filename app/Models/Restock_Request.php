<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Model;

/**
 * Restock requests raised by shoppers on sold out products.
 */
class Restock_Request extends Model {

	protected $table = 'restock_requests';

	/**
	 * A shopper's existing request for a product.
	 *
	 * @param int    $product_id   Product the request belongs to.
	 * @param string $email        Shopper email address.
	 * @param int    $variation_id Variation the request belongs to, 0 for the whole product.
	 * @return object|null Row when the shopper already asked, null otherwise.
	 */
	public function get_request( $product_id, $email, $variation_id = 0 ) {
		$email = sanitize_email( $email );

		if ( ! (int) $product_id || ! is_email( $email ) ) {
			return null;
		}

		return $this->db->get_row(
			array(
				'product_id'   => (int) $product_id,
				'variation_id' => (int) $variation_id,
				'email'        => $email,
			)
		);
	}

	/**
	 * Register a shopper's interest in an out of stock product.
	 *
	 * @param int    $product_id   Product the request belongs to.
	 * @param string $email        Shopper email address.
	 * @param int    $variation_id Variation the request belongs to, 0 for the whole product.
	 * @param bool   $consent      Whether the shopper agreed to be emailed.
	 * @param int    $user_id      Logged in user ID, 0 for a guest.
	 * @return int|false Row ID on success, false on failure.
	 */
	public function subscribe( $product_id, $email, $variation_id = 0, $consent = false, $user_id = 0 ) {
		$product_id   = (int) $product_id;
		$variation_id = (int) $variation_id;
		$email        = sanitize_email( $email );

		if ( ! $product_id || ! is_email( $email ) ) {
			return false;
		}

		$existing = $this->db->get_row(
			array(
				'product_id'   => $product_id,
				'variation_id' => $variation_id,
				'email'        => $email,
			)
		);

		if ( $existing ) {
			$this->db->update_row(
				$existing->id,
				array(
					'status'      => 'pending',
					'consent'     => $consent ? 1 : 0,
					'user_id'     => $user_id ?: null,
					'notified_at' => null,
				)
			);

			return (int) $existing->id;
		}

		$id = $this->db->insert_row(
			array(
				'product_id'   => $product_id,
				'variation_id' => $variation_id,
				'user_id'      => $user_id ?: null,
				'email'        => $email,
				'status'       => 'pending',
				'consent'      => $consent ? 1 : 0,
			)
		);

		if ( ! $id ) {
			return false;
		}

		do_action( 'easycommerce_restock_request_subscribed', (int) $id, $product_id, $variation_id, $email );

		return (int) $id;
	}

	/**
	 * Pending requests waiting on a variation coming back in stock.
	 *
	 * Requests made against the whole product are included, since a shopper who
	 * subscribed on the product page did not pick a variation.
	 *
	 * @param int $product_id   Product ID.
	 * @param int $variation_id Variation that was restocked.
	 * @param int $limit        Rows to return, 0 for all of them.
	 * @return array Rows of pending requests.
	 */
	public function get_pending( $product_id, $variation_id = 0, $limit = 0 ) {
		$variation_ids = array_unique( array( 0, (int) $variation_id ) );

		return $this->db->get_rows(
			array(
				array( 'product_id' => (int) $product_id ),
				array( 'status' => 'pending' ),
				array( 'variation_id' => array( 'IN', array_values( $variation_ids ) ) ),
			),
			(int) $limit,
			0,
			'ASC'
		);
	}

	/**
	 * Claim a request in one conditional statement, so two overlapping batches
	 * cannot both mail the same shopper.
	 *
	 * @param int $id Request ID.
	 * @return bool True when this caller took the request, false when it was already taken.
	 */
	public function mark_notified( $id ) {
		$wpdb  = $this->db->get_instance();
		$table = $this->db->get_table();

		$claimed = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from the Database model.
				"UPDATE {$table} SET status = 'notified', notified_at = %s WHERE id = %d AND status = 'pending'",
				current_time( 'mysql' ),
				(int) $id
			)
		);

		return (bool) $claimed;
	}

	/**
	 * Delete a request.
	 *
	 * @param int $id Request ID.
	 * @return bool
	 */
	public function remove( $id ) {
		return (bool) $this->db->delete_row( (int) $id );
	}

	/**
	 * Requests for the admin table, newest first.
	 *
	 * @param array $args status, search, from, to, limit and offset.
	 * @return array Rows joined with the product title.
	 */
	public function get_requests( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'status' => '',
				'search' => '',
				'from'   => '',
				'to'     => '',
				'limit'  => 20,
				'offset' => 0,
			)
		);

		$clause = $this->build_clause( $args );
		$table  = $this->db->get_table();
		$posts  = $this->db->get_wp_prefix() . 'posts';

		$values   = $clause['values'];
		$values[] = (int) $args['limit'];
		$values[] = (int) $args['offset'];

		$query = $this->db->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names from the Database model.
			"SELECT n.*, p.post_title AS product_name
			FROM {$table} n
			LEFT JOIN {$posts} p ON p.ID = n.product_id
			{$clause['where']}
			ORDER BY n.created_at DESC
			LIMIT %d OFFSET %d",
			...$values
		);

		return $this->db->exec( $query );
	}

	/**
	 * Number of requests matching the same filters, for pagination.
	 *
	 * @param array $args status, search, from and to.
	 * @return int
	 */
	public function get_total( $args = array() ) {
		$clause = $this->build_clause( $args );
		$table  = $this->db->get_table();
		$posts  = $this->db->get_wp_prefix() . 'posts';

		$query = "SELECT COUNT(*) AS total
			FROM {$table} n
			LEFT JOIN {$posts} p ON p.ID = n.product_id
			{$clause['where']}";

		if ( ! empty( $clause['values'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names from the Database model.
			$query = $this->db->prepare( $query, ...$clause['values'] );
		}

		$total = $this->db->exec( $query );

		return isset( $total[0]->total ) ? (int) $total[0]->total : 0;
	}

	/**
	 * Request count per status, for the tab pills.
	 *
	 * @return array Status keyed counts plus an `all` total.
	 */
	public function get_status_counts() {
		$table = $this->db->get_table();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from the Database model.
		$rows = $this->db->exec( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status" );

		$counts = array(
			'all'      => 0,
			'pending'  => 0,
			'notified' => 0,
		);

		foreach ( $rows as $row ) {
			$counts[ $row->status ] = (int) $row->total;
			$counts['all']         += (int) $row->total;
		}

		return $counts;
	}

	/**
	 * Delete several requests at once.
	 *
	 * @param array $ids Request IDs.
	 * @return int Rows removed.
	 */
	public function remove_many( $ids ) {
		$ids = array_filter( array_map( 'intval', (array) $ids ) );

		if ( empty( $ids ) ) {
			return 0;
		}

		$wpdb         = $this->db->get_instance();
		$table        = $this->db->get_table();
		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );

		// delete_rows() answers true/false, and the caller reports a count to the user.
		return (int) $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from the Database model.
				"DELETE FROM {$table} WHERE id IN ({$placeholders})",
				...$ids
			)
		);
	}

	/**
	 * Shared WHERE for the request list and its count.
	 *
	 * @param array $args status, search, from and to.
	 * @return array where clause string and its values.
	 */
	private function build_clause( $args ) {
		$where  = array();
		$values = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'n.status = %s';
			$values[] = $args['status'];
		}

		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $this->db->get_instance()->esc_like( $args['search'] ) . '%';
			$where[]  = '( n.email LIKE %s OR p.post_title LIKE %s )';
			$values[] = $like;
			$values[] = $like;
		}

		if ( ! empty( $args['from'] ) ) {
			$where[]  = 'n.created_at >= %s';
			$values[] = $args['from'] . ' 00:00:00';
		}

		if ( ! empty( $args['to'] ) ) {
			$where[]  = 'n.created_at <= %s';
			$values[] = $args['to'] . ' 23:59:59';
		}

		return array(
			'where'  => $where ? 'WHERE ' . implode( ' AND ', $where ) : '',
			'values' => $values,
		);
	}
}

<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Product_Review
 * Handles product review operations using WordPress comments system.
 *
 * @package EasyCommerce\Models
 */
class Product_Review {

	/**
	 * @var int Review ID (maps to comment ID)
	 */
	protected $id;

	/**
	 * @var bool Whether the review exists
	 */
	protected $exists = false;

	/**
	 * Constructor
	 *
	 * @param int|null $id Comment ID
	 */
	public function __construct( $id = null ) {
		if ( $id ) {
			$this->id = (int) $id;
			$this->validate();
		}
	}

	/**
	 * Validate if the review exists and is a product review
	 *
	 * @return bool True if valid, false otherwise
	 */
	protected function validate() {
		$comment = get_comment( $this->id );

		if ( ! $comment ) {
			return false;
		}

		// Check if comment is for a product
		$post = get_post( $comment->comment_post_ID );
		if ( ! $post || get_post_type( $post ) !== 'product' ) {
			return false;
		}

		$this->exists = true;
		return true;
	}

	/**
	 * Delete a review
	 *
	 * @return bool True on success, false on failure
	 */
	public function delete() {
		if ( ! $this->exists || ! $this->id ) {
			return false;
		}

		$result = wp_delete_comment( $this->id, true );
		if ( $result ) {
			$this->id     = null;
			$this->exists = false;
			return true;
		}

		return false;
	}

	/**
	 * List product reviews with optional filters
	 *
	 * @param array $args Arguments for filtering reviews
	 * @param int $per_page Number of reviews per page
	 * @param int $offset Offset for pagination
	 * @return array Array of review data with total count
	 */
	public static function list( $args = array() ) {
		$per_page 	= isset( $args['per_page'] ) ? (int) $args['per_page'] : 10;
		$page 		= isset( $args['page'] ) ? (int) $args['page'] : 1;
		$status 	= isset( $args['status'] ) ? $args['status'] : null;
		$search 	= isset( $args['search'] ) ? $args['search'] : null;
		$order 		= isset( $args['order'] ) ? $args['order'] : 'DESC';
		$post_id 	= isset( $args['post_id'] ) ? $args['post_id'] : null;
		$user_id 	= isset( $args['user_id'] ) ? $args['user_id'] : null;
		$offset     = ( $page - 1 ) * $per_page;

		$where = array(
			'status'    => $status && $status !== 'all' ? $status : '',
			'order'     => $order,
			'post_id'   => $post_id,
			'user_id'   => $user_id,
			'number'    => $per_page,
			'offset'    => $offset,
			'post_type' => 'product',
		);

		if ( ! empty( $search ) ) {
			$products_result = Product::list( array( 'search' => $search ), -1, 0, true );
			if ( ! empty( $products_result['products'] ) ) {
				$product_ids = array();
				foreach ( $products_result['products'] as $product ) {
					$product_ids[] = $product->get_id();
				}
				$where['post__in'] = $product_ids;
			} else {
				$where['search'] = $args['search'];
			}
		}

		$count_where = array(
			'status'    => $where['status'],
			'order'     => $where['order'],
			'post_id'   => $where['post_id'],
			'user_id'   => $where['user_id'],
			'post_type' => $where['post_type'],
			'count'     => true,
		);

		if ( isset( $where['post__in'] ) ) {
			$count_where['post__in'] = $where['post__in'];
		}
		if ( isset( $where['search'] ) ) {
			$count_where['search'] = $where['search'];
		}

		$total    = get_comments( array_filter( $count_where ) );
		$comments = get_comments( array_filter( $where ) );
		$reviews  = array();

		foreach ( $comments as $comment ) {
			$product = get_post( $comment->comment_post_ID );
			if ( $product && $product->post_type === 'product' ) {
				$reviews[] = array(
					'id' 			=> (int) $comment->comment_ID,
					'product_id' 	=> (int) $comment->comment_post_ID,
					'product_name' 	=> $product->post_title ?: 'Unknown Product',
					'customer_id' 	=> (int) $comment->user_id,
					'customer_name' => $comment->comment_author,
					'content' 		=> $comment->comment_content,
					'rating' 		=> (int) get_comment_meta( $comment->comment_ID, 'rating', true ),
					'name' 			=> $comment->comment_author,
					'email' 		=> $comment->comment_author_email,
					'status' 		=> $comment->comment_approved,
					'created_at' 	=> $comment->comment_date,
				);
			}
		}

		return array(
			'reviews' 		=> $reviews,
			'total' 		=> (int) $total,
			'per_page' 		=> (int) $per_page,
			'page' 			=> (int) $page,
			'total_pages' 	=> ceil( $total / $per_page ),
		);
	}


	/**
	 * Check if review exists
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Get review ID
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}
}
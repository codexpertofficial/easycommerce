<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Product_Review as Review_Model;
use EasyCommerce\Models\Product;

class Product_Review extends API {

	/**
	 * Get a list of product reviews.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public function get_all( $request ) {
		$post_id 	= $request->get_param( 'product_id' );
		$user_id 	= $request->get_param( 'customer_id' );
		$status 	= $request->get_param( 'status' ) ?? null;
		$per_page 	= $request->get_param( 'per_page' ) ?: 10;
		$page 		= $request->get_param( 'page' ) ?: 1;
		$search 	= $request->get_param( 'search' );
		$order 		= $request->get_param( 'order' ) ?: 'DESC';

		$reviews = Review_Model::list( array(
			'post_id' 	=> $post_id,
			'user_id' 	=> $user_id,
			'status' 	=> $status,
			'search' 	=> $search,
			'order' 	=> $order,
			'per_page' 	=> $per_page,
			'page' 		=> $page
		) );

		/**
		 * Filters the product reviews list.
		 *
		 * @since 1.9
		 * @param array $reviews The reviews.
		 * @param WP_REST_Request $request The request object.
		 */
		$reviews = apply_filters( 'easycommerce_get_product_reviews', $reviews, $request );

		return $this->response_success( $reviews );
	}

	/**
	 * Delete a product review.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$id = $request->get_param( 'id' );

		if ( empty( $id ) ) {
			return $this->response_error( __( 'Review ID is required.', 'easycommerce' ), 400 );
		}

		$review = new Review_Model( $id );

		if ( ! $review->exists() ) {
			return $this->response_error( __( 'Review not found.', 'easycommerce' ), 404 );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->response_error( __( 'You do not have permission to delete this review.', 'easycommerce' ), 403 );
		}

		/**
		 * Fires before deleting a product review.
		 *
		 * @since 1.9
		 * @param int $id The review ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_product_review', $id, $request );

		$result = $review->delete();

		/**
		 * Fires after deleting a product review.
		 *
		 * @since 1.9
		 * @param int $id The review ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_product_review', $id, $request );

		if ( ! $result ) {
			return $this->response_error( __( 'Failed to delete review.', 'easycommerce' ), 500 );
		}

		return $this->response_success(
			array(
				'message' => __( 'Review deleted successfully.', 'easycommerce' ),
			)
		);
	}
}
<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Restock_Request as Restock_Request_Model;

class Restock_Request extends API {

	/**
	 * Capture a shopper's restock-request request.
	 *
	 * @param  \WP_REST_Request $request Request holding product_id, email and an optional variation_id.
	 * @return void
	 */
	public function subscribe( $request ) {
		$product_id   = (int) $request->get_param( 'product_id' );
		$variation_id = (int) $request->get_param( 'variation_id' );
		$email        = sanitize_email( $request->get_param( 'email' ) );

		if ( ! is_email( $email ) ) {
			$this->response_error(
				array( 'message' => __( 'Please enter a valid email address.', 'easycommerce' ) )
			);
		}

		$product = new Product( $product_id );

		if ( ! $product->get_id() ) {
			$this->response_error(
				array( 'message' => __( 'Product not found.', 'easycommerce' ) ),
				404
			);
		}

		// Handing over the address on a form that promises one email is the consent.
		$model    = new Restock_Request_Model();
		$existing = $model->get_request( $product_id, $email, $variation_id );
		$id       = $model->subscribe( $product_id, $email, $variation_id, true, get_current_user_id() );

		if ( ! $id ) {
			$this->response_error(
				array( 'message' => __( 'Unable to save your request. Please try again.', 'easycommerce' ) )
			);
		}

		// A notified row is a finished request, so only a still pending one is a repeat.
		$repeated = $existing && 'pending' === $existing->status;

		$this->response_success(
			array(
				'id'       => $id,
				'repeated' => $repeated,
				'message'  => $repeated
					? __( 'You are already on the list. We will email you the moment it returns.', 'easycommerce' )
					: __( 'Thanks! We will email you as soon as this product is back in stock.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Requests for the admin table.
	 *
	 * @param  \WP_REST_Request $request Request holding status, search, from, to, page and per_page.
	 * @return void
	 */
	public function list( $request ) {
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = (int) $request->get_param( 'per_page' ) ?: 20;
		$status   = $request->get_param( 'status' );

		$args = array(
			'status' => 'all' === $status ? '' : $status,
			'search' => $request->get_param( 'search' ),
			'from'   => $request->get_param( 'from' ),
			'to'     => $request->get_param( 'to' ),
			'limit'  => $per_page,
			'offset' => ( $page - 1 ) * $per_page,
		);

		$model    = new Restock_Request_Model();
		$requests = array();
		$stocks   = array();

		foreach ( $model->get_requests( $args ) as $row ) {
			$product_id = (int) $row->product_id;

			// One product carries many requests; only look its stock up once.
			if ( ! isset( $stocks[ $product_id ] ) ) {
				$stocks[ $product_id ] = ( new Product( $product_id ) )->get_stock();
			}

			$requests[] = array(
				'id'           => (int) $row->id,
				'email'        => $row->email,
				'product_id'   => $product_id,
				'product_name' => $row->product_name,
				'permalink'    => get_permalink( $product_id ),
				'stock'        => $stocks[ $product_id ],
				'status'       => $row->status,
				'created_at'   => $row->created_at,
				'notified_at'  => $row->notified_at,
			);
		}

		$this->response_success(
			array(
				'requests' => $requests,
				'total'    => $model->get_total( $args ),
				'statuses' => $model->get_status_counts(),
			)
		);
	}

	/**
	 * Drop a single request.
	 *
	 * @param  \WP_REST_Request $request Request holding the request id.
	 * @return void
	 */
	public function remove( $request ) {
		$model = new Restock_Request_Model();

		if ( ! $model->remove( (int) $request->get_param( 'id' ) ) ) {
			$this->response_error(
				array( 'message' => __( 'Request not removed.', 'easycommerce' ) )
			);
		}

		$this->response_success(
			array( 'message' => __( 'Request removed.', 'easycommerce' ) )
		);
	}

	/**
	 * Drop several requests at once.
	 *
	 * @param  \WP_REST_Request $request Request holding the ids.
	 * @return void
	 */
	public function bulk_remove( $request ) {
		$model   = new Restock_Request_Model();
		$removed = $model->remove_many( $request->get_param( 'ids' ) );

		if ( ! $removed ) {
			$this->response_error(
				array( 'message' => __( 'No request was removed.', 'easycommerce' ) )
			);
		}

		$this->response_success(
			array(
				'removed' => $removed,
				/* translators: %d: number of removed requests. */
				'message' => sprintf( _n( '%d request removed.', '%d requests removed.', $removed, 'easycommerce' ), $removed ),
			)
		);
	}
}

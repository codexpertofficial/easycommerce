<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Order_Item as Order_Item_Model;
use EasyCommerce\Abstracts\API;

class Order_Item extends API {

	/**
	 * Get items for a specific order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$order_item_model = new Order_Item_Model();
		$order_id = $request->get_param( 'order_id' );

		if ( empty( $order_id ) ) {
			$this->response_error( __( 'Order ID is required.', 'easycommerce' ), 400 );
		}

		// Fetch items from the order
		$items = $order_item_model->get_by_order_id( $order_id );

		if ( empty( $items ) ) {
			$this->response_success( array( 'message' => __( 'No items found for this order.', 'easycommerce' ) ) );
		}

		/**
		 * Filters the order items.
		 *
		 * @since 1.9
		 * @param array $items The items.
		 * @param int $order_id The order ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$items = apply_filters( 'easycommerce_get_order_items', $items, $order_id, $request );

		$this->response_success(
			array(
				'order_id' => $order_id,
				'items'    => $items,
			)
		);
	}

	/**
	 * Add an item to an order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function add_item( $request ) {
		$order_item_model = new Order_Item_Model();
		$order_id   = $request->get_param( 'order_id' );
		$product_id = $request->get_param( 'product_id' );
		$quantity   = $request->get_param( 'quantity' );
		$price      = $request->get_param( 'price' );
		$meta       = $request->get_param( 'meta' ) ?? array();

		if ( empty( $order_id ) || empty( $product_id ) || empty( $quantity ) || empty( $price ) ) {
			$this->response_error( __( 'Order ID, product ID, quantity, and price are required.', 'easycommerce' ), 400 );
		}

		$data = array(
			'product_id' => $product_id,
			'quantity'   => $quantity,
			'price'      => $price,
			'meta'       => $meta,
		);

		/**
		 * Filters the order item data before adding.
		 *
		 * @since 1.9
		 * @param array $data The item data.
		 * @param int $order_id The order ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_add_order_item_data', $data, $order_id, $request );

		/**
		 * Fires before adding an order item.
		 *
		 * @since 1.9
		 * @param int $order_id The order ID.
		 * @param array $data The item data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_add_order_item', $order_id, $data, $request );

		// Add the item to the order
		$item_id = $order_item_model->add( $order_id, $data );

		/**
		 * Fires after adding an order item.
		 *
		 * @since 1.9
		 * @param int $item_id The item ID.
		 * @param int $order_id The order ID.
		 * @param array $data The item data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_add_order_item', $item_id, $order_id, $data, $request );

		if ( ! $item_id ) {
			$this->response_error( __( 'Failed to add item to the order.', 'easycommerce' ), 500 );
		}

		$this->response_success(
			array(
				'message' => __( 'Item added to the order successfully.', 'easycommerce' ),
				'item_id' => $item_id,
			),
			201
		);
	}

	/**
	 * Update an existing item in an order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update_item( $request ) {
		$order_item_model = new Order_Item_Model();
		$item_id  = $request->get_param( 'item_id' );
		$quantity = $request->get_param( 'quantity' );
		$price    = $request->get_param( 'price' );
		$meta     = $request->get_param( 'meta' ) ?? array();

		if ( empty( $item_id ) || ( empty( $quantity ) && empty( $price ) ) ) {
			$this->response_error( __( 'Item ID, and either quantity or price are required.', 'easycommerce' ), 400 );
		}

		// Prepare data to update
		$data = array();
		if ( $quantity ) {
			$data['quantity'] = $quantity;
		}
		if ( $price ) {
			$data['price'] = $price;
		}

		$data = array_merge( $data, array( 'meta' => $meta ) );

		/**
		 * Filters the order item update data.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param int $item_id The item ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_update_order_item_data', $data, $item_id, $request );

		/**
		 * Fires before updating an order item.
		 *
		 * @since 1.9
		 * @param int $item_id The item ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_order_item', $item_id, $data, $request );

		// Update the item
		$updated = $order_item_model->update( $item_id, $data );

		/**
		 * Fires after updating an order item.
		 *
		 * @since 1.9
		 * @param int $item_id The item ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_order_item', $item_id, $data, $request );

		if ( ! $updated ) {
			$this->response_error( __( 'Failed to update the order item.', 'easycommerce' ), 500 );
		}

		$this->response_success(
			array(
				'message' => __( 'Item updated successfully.', 'easycommerce' ),
				'item_id' => $item_id,
			)
		);
	}

	/**
	 * Delete an item from an order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete_item( $request ) {
		$order_item_model = new Order_Item_Model();
		$item_id = $request->get_param( 'item_id' );

		if ( empty( $item_id ) ) {
			$this->response_error( __( 'Item ID is required.', 'easycommerce' ), 400 );
		}

		/**
		 * Fires before deleting an order item.
		 *
		 * @since 1.9
		 * @param int $item_id The item ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_order_item', $item_id, $request );

		// Delete the item
		$deleted = $order_item_model->delete( $item_id );

		/**
		 * Fires after deleting an order item.
		 *
		 * @since 1.9
		 * @param int $item_id The item ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_order_item', $item_id, $request );

		if ( ! $deleted ) {
			$this->response_error( __( 'Failed to delete the order item.', 'easycommerce' ), 500 );
		}

		$this->response_success(
			array(
				'message' => __( 'Item deleted successfully.', 'easycommerce' ),
			)
		);
	}
}

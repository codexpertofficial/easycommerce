<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Order as Order_Model;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Product_Variation_Download;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Order_Item_Meta;
use EasyCommerce\Models\Customer;
use EasyCommerce\Abstracts\API;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Log as Log_Model;
use EasyCommerce\Traits\Cache;
use WP_REST_Request;
use WP_REST_Response;

class Order extends API {
	use Cache;

	private function delete_order_cache() {
		$ranges = array( 'this-week', 'last-7', 'this-month', 'last-30', 'this-year' );

		$this->delete_cache( 'report_overview_stats' );
		foreach ( $ranges as $range ) {
			$this->delete_cache( 'report_overview_stats_range_' . $range );
		}
	}

	/**
	 * Create a new order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create( $request ) {
		$items               = $request->get_param( 'items' );
		$status              = $request->get_param( 'status' );
		$customer            = $request->get_param( 'customer' );
		$payment_method      = $request->get_param( 'easycommerce-payment_method' );
		$billing_address     = $request->get_param( 'billing_address' );
		$shipping_address    = $request->get_param( 'shipping_address' );
		$meta                = $request->get_param( 'meta' );
		$billing_as_shipping = $request->get_param( 'billing_as_shipping' );
		$customer_not_exits  = true;
		$coupons             = '';

		$billing_error = $this->validate_address_fields( $billing_address, 'billing' );
		if ( ! is_null( $billing_error ) ) {
			return $billing_error;
		}

		if ( is_null( $billing_as_shipping ) && ! empty( $shipping_address ) ) {
			$shipping_error = $this->validate_address_fields( $shipping_address, 'shipping' );
			if ( ! is_null( $shipping_error ) ) {
				return $shipping_error;
			}
		}

		if ( email_exists( $customer['email'] ) ) {
			$customer_not_exits = false;
		}

		if ( is_string( $items ) ) {
			$cart_hash = $items;
			$cart      = new Cart( $cart_hash );
			$cart_data = $cart->get_data( 'data' );
			$items     = $cart->get_items();
			$coupons   = $cart_data && isset( $cart_data['coupons'] ) ? $cart_data['coupons'] : '';

			if ( $cart->get_status() == 'completed' ) {
				$this->response_error( __( 'Cart is already completed.', 'easycommerce' ), 400 );
			} elseif ( empty( $items ) ) {
				$this->response_error( __( 'Cart is empty or invalid cart hash.', 'easycommerce' ), 400 );
			}
		} elseif ( ! is_array( $items ) ) {
			$this->response_error( __( 'Invalid items format.', 'easycommerce' ), 400 );
		}

		// Calculate subtotal, tax, shipping, coupon, total etc
		$total = apply_filters( 'easycommerce-calculate_cart_total', $cart->get_amount(), $cart );

		if ( isset( $cart ) && $cart->is_locked() ) {
			$locked = $cart->get_lock_amount();
			if ( null !== $locked && $locked > 0 ) {
				$total = $locked;
			}
		}

		$paypal_lock_amount = null;
		if ( 'paypal' === $payment_method && isset( $cart ) ) {
			$paypal_lock_amount = $cart->get_lock_amount();
		}

		if ( is_integer( $customer ) ) {
			$customer_id = $customer;
		} elseif ( is_user_logged_in() ) {
			$customer_id   = get_current_user_id();
			$existing_user = new \WP_User( $customer_id );
			$existing_user->add_cap( 'has_order' );
		} elseif ( isset( $customer['email'] ) && ( $existing_user = email_exists( $customer['email'] ) ) ) {
			$customer_id   = $existing_user;
			$existing_user = new \WP_User( $customer_id );
			$existing_user->add_cap( 'has_order' );
		} elseif ( is_array( $customer ) ) {
			$customer_obj = new Customer();
			$customer_obj->create( $customer );

			$customer_id = $customer_obj->get_id();
		} else {
			$customer_id = 0;
		}

		if ( ! isset( $customer_obj ) ) {
			$customer_obj = new Customer( $customer_id );
		}

		if ( ! is_null( $billing_as_shipping ) ) {
			$shipping_address = $billing_address;
		}

		$customer_obj->update_meta( 'billing_address', $billing_address );
		$customer_obj->update_meta( 'shipping_address', $shipping_address );

		/**
		 * Fires before creating an order.
		 *
		 * @since 1.9
		 * @param array $items The order items.
		 * @param array $meta The order meta.
		 * @param int $customer_id The customer ID.
		 */
		do_action( 'easycommerce_before_create_order', $items, $meta, $customer_id );

		/**
		 * Filters the new order status.
		 *
		 * @since 1.9
		 * @param string $status The order status.
		 * @param array $items The order items.
		 * @param array $meta The order meta.
		 * @param int $customer_id The customer ID.
		 */
		$status = apply_filters( 'easycommerce_new_order_status', $status, $items, $meta, $customer_id );

		$order_model  = new Order_Model();
		$cart_amounts = $cart->get_amounts();
		$order_id = $order_model->create(
			array(
				'customer_id'    => $customer_id,
				'total'          => $total,
				'payment_method' => $payment_method,
				'status'         => $status,
				'items'          => $items,
				'meta'           => array_merge(
					$cart_amounts,
					array(
						'billing_address'  => $billing_address,
						'shipping_address' => $shipping_address,
						'coupons'          => $coupons,
						'tax'         	   => $cart_amounts['tax'] ?? 0,
						'shipping_tax'     => $cart_amounts['shipping_tax'] ?? 0,
					)
				),
			)
		);

		if ( ! $order_id ) {
			$this->response_error( __( 'Failed to create order.', 'easycommerce' ), 500 );
		}

		// Persist the PayPal-intended amount in order meta so process_payment() can verify
		// the captured amount against the value locked at button-click time, not the live total.
		if ( null !== $paypal_lock_amount ) {
			$new_order = new Order_Model( $order_id );
			$new_order->add_meta( '_ec_paypal_intended_amount', number_format( $paypal_lock_amount, 2, '.', '' ) );
		}

		// Lock the cart so items cannot be added or modified while payment is being processed.
		if ( isset( $cart ) ) {
			$cart->set_status( 'payment_initiated' );
		}

		$wp_request_params   = $request->get_params();
		$request_get_params  = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
		$request_post_params = $_POST; // phpcs:ignore WordPress.Security.NonceVerification

		$params = array_merge( $wp_request_params, $request_get_params, $request_post_params );

		/**
		 * Fires after creating an order.
		 *
		 * @since 1.9
		 * @param int $order_id The order ID.
		 * @param array $params The request params.
		 * @param int $customer_id The customer ID.
		 */
		do_action( 'easycommerce_after_create_order', $order_id, $params, $customer_id );

		$default_order_status   = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
		$default_fulfill_status = Utility::get_option( 'order', 'settings', 'default_fulfill_status', 'unfulfilled' );

		/**
		 * Filters the order status.
		 *
		 * @since 1.9
		 * @param string $default_order_status The default status.
		 * @param int $order_id The order ID.
		 * @param array $params The params.
		 * @param int $customer_id The customer ID.
		 */
		$status = apply_filters( 'easycommerce_order_status', $default_order_status, $order_id, $params, $customer_id );

		/**
		 * Filters the order fulfill status.
		 *
		 * @since 1.9
		 * @param string $default_fulfill_status The default fulfill status.
		 * @param int $order_id The order ID.
		 * @param array $params The params.
		 * @param int $customer_id The customer ID.
		 */
		$fulfill_status = apply_filters( 'easycommerce_order_fulfill_status', $default_fulfill_status, $order_id, $params, $customer_id );

		$order = new Order_Model( $order_id );
		$order->set_status( $status );
		$order->set_fulfillment_status( $fulfill_status );

		// Unlock the cart if payment failed so the customer can retry.
		if ( 'failed' === $status && isset( $cart ) ) {
			$cart->set_status( 'pending' );
		}

		/**
		 * Fires to send order email.
		 *
		 * @since 1.9
		 * @param string $status The order status.
		 * @param int $order_id The order ID.
		 */
		do_action( 'easycommerce_order_email', $status, $order_id );

		/**
		 * Fires after order creation.
		 *
		 * @since 1.9
		 * @param int $order_id The order ID.
		 * @param array $params The params.
		 * @param string $status The order status.
		 */
		do_action( 'easycommerce_after_order', $order_id, $params, $status );

		// Optionally, clear the cart after order creation if cart_hash was used.
		if ( isset( $cart_hash ) ) {
			$cart->set_status( 'completed' );
			$cart->remove_flag();
		}

		$redirect = easycommerce_order_redirect( $order_id );
		if ( ! is_user_logged_in() && $customer_not_exits ) {
			$redirect = $customer_obj->get_login_link( 1, $redirect );
		}

		do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'create', 'object_id' => $order_id, 'note' => 'Order ID #' . $order_id ) );

		$this->response_success(
			array(
				'message'  => __( 'Order created successfully.', 'easycommerce' ),
				'order_id' => $order_id,
				'redirect' => $redirect,
			),
			201
		);
	}

	/**
	 * Get a specific order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get( $request ) {
		$order_id = $request->get_param( 'id' );

		$order = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			$this->response_error( array( 'message' => __( 'Order not found.', 'easycommerce' ) ) );
		}

		if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'manager' ) ) {
			if ( (int) $order->get_customer_id() !== get_current_user_id() ) {
				return $this->response_error( __( 'You are not allowed to view this order.', 'easycommerce' ), 403 );
			}
		}

		// Fetch order details
		/**
		 * Filters the order detail items.
		 *
		 * @since 1.9
		 * @param array $order_data The order data.
		 */
		$order_data = apply_filters(
			'easycommerce_order_detail_items',
			array(
				'id'                     => $order->get_id(),
				'status'                 => $order->get_status(),
				'fulfill_status'         => $order->get_fulfillment_status(),
				'total'                  => easycommerce_price( $order->get_total() ),
				'refunded_total'         => easycommerce_price( $order->get_total_refunded() ),
				'available_for_refund'   => easycommerce_price( $order->get_total() - $order->get_total_refunded() ),
				'created_at'             => Utility::format_date( $order->get_created_at() ) . ' ' . Utility::format_time( $order->get_created_at() ),
				'transactions'           => current( $order->get_transactions( true ) ) ?: null,
				'subtotal'               => easycommerce_price( $order->get_subtotal() ),
				'product_tax'            => $order->get_product_tax(),
				'shipping_tax'           => $order->get_shipping_tax(),
				'product_tax_formatted'  => easycommerce_price( $order->get_product_tax() ),
				'shipping_tax_formatted' => easycommerce_price( $order->get_shipping_tax() ),
				'tax'                    => easycommerce_price( $order->get_tax_total() ),
				'shipping_formatted'     => easycommerce_price( $order->get_shipping_total() ),
				'discount_formatted'     => easycommerce_price( $order->get_discount_total() ),
				'shipping'               => $order->get_shipping_total(),
				'discount'               => $order->get_discount_total(),
				'payment_method'         => $order->get_payment_method(),
			)
		);

		// Fetch customer data
		$customer = new Customer( $order->get_customer_id() );

		$order_data['customer'] = array(
			'id'          => $customer->get_id(),
			'name'        => $customer->get_name(),
			'email'       => $customer->get_email(),
			'phone'       => $customer->get_phone(),
			'photo'       => $customer->get_photo(),
			'since'       => $customer->get_join_date(),
			'aov'         => $customer->get_aov(),
			'ltv'         => $customer->get_ltv(),
			'order_count' => $customer->get_order_count(),
			'last_order'  => $customer->get_last_order_date(),
		);

		// Fetch order meta
		$order_data['meta'] = array(
			'billing'  => $order->get_meta( 'billing_address' ) ?: array(),
			'shipping' => $order->get_meta( 'shipping_address' ) ?: array(),
		);

		$items           = $order->get_items( true );
		$order_item_meta = new Order_Item_Meta();

		$items = array_map(
			function ( $item ) use ( $order_item_meta ) {

				$product_model            = new Product( $item->product_id );
				$variation_model          = new Product_Variation( $item->variation_id );
				$variation_download_model = new Product_Variation_Download( $item->variation_id );

				unset( $item->product_id );
				unset( $item->variation_id );

				$item->product = array(
					'id'        => $product_model->get_id(),
					'name'      => $product_model->get_title(),
					'thumbnail' => $product_model->get_thumbnail(),
				);

				$variation_data = array(
					'id'        => $variation_model->get_id(),
					'name'      => $variation_model->get_name(),
					'thumbnail' => $variation_model->get_thumbnail(),
				);

				if ( $variation_model->get_type() === 'digital' ) {
					$variation_data['downloads'] = $variation_download_model->get( $variation_model->get_id() );
				}

				$item->variation = $variation_data;

				$item->meta = $order_item_meta->get( $item->id );

				// Update price and subtotal to 0.00 if the item is marked as free in meta
				if ( isset( $item->meta['is_free'] ) && $item->meta['is_free'] ) {
					$item->price    = easycommerce_price( '0.00' );
					$item->subtotal = easycommerce_price( '0.00' );
				}

				return $item;
			},
			$items
		);

		$order_data['items'] = $items;


		/**
		 * Filters the single order data.
		 *
		 * @since 1.9
		 * @param array $order_data The order data.
		 * @param int $order_id The order ID.
		 */
		$order_data = apply_filters( 'easycommerce_get_single_order_data', $order_data, $order_id );

		$this->response_success( $order_data );
	}

	/**
	 * Update an existing order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( $request ) {
		$order_id                = $request->get_param( 'id' );
		$order                   = new Order_Model( $order_id );
		$old_status              = $order->get_status();
		$old_fulfillment_status  = $order->get_fulfillment_status();
		$new_status              = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : null;
		$new_fulfillment_status  = $request->get_param( 'fulfill_status' ) ? $request->get_param( 'fulfill_status' ) : null;
		$order_statuses          = easycommerce_order_statuses();
    	$fulfill_statuses        = easycommerce_fulfill_statuses();

		if ( ! $order->exists() ) {
			$this->response_success( array( 'message' => __( 'Order not found.', 'easycommerce' ) ) );
		}

		$data = array();
		if ( $new_status ) {
			$data['status'] = $new_status;
		}

		if ( $request->get_param( 'fulfill_status' ) ) {
			$data['fulfill_status'] = $request->get_param( 'fulfill_status' );
		}

		if ( $request->get_param( 'total' ) ) {
			$data['total'] = $request->get_param( 'total' );
		}

		$updated = $order->update( $data );

		if ( $updated ) {

			if( $new_status && $new_status !== $old_status ) {
				/**
				 * Fires when order status is updated.
				 *
				 * @since 1.9
				 * @param int $order_id The order ID.
				 * @param string $new_status The new status.
				 * @param string $old_status The old status.
				 */
				do_action( 'easycommerce_order_status_updated', $order_id, $new_status, $old_status );

				do_action(
					'easycommerce_log',
					array(
						'object'    => 'order',
						'action'    => 'order_status',
						'object_id' => $order_id,
						'type'      => 'order_status',
						'note'      => 'Order status changed from ' . ( $order_statuses[ $old_status ] ?? $old_status ) . ' to ' . ( $order_statuses[ $new_status ] ?? $new_status ),
					)
				);

				/**
				 * Fires to send order email.
				 *
				 * @since 1.9
				 * @param string $new_status The new status.
				 * @param int $order_id The order ID.
				 */
				do_action( 'easycommerce_order_email', $new_status, $order_id );
			}
			if( $new_fulfillment_status && $new_fulfillment_status !== $old_fulfillment_status ) {
				/**
				 * Fires when order fulfillment status is updated.
				 *
				 * @since 1.1
				 * @param int $order_id The order ID.
				 * @param string $new_status The new status.
				 * @param string $old_status The old status.
				 */
				do_action( 'easycommerce_order_fulfillment_status_updated', $order_id, $new_fulfillment_status, $old_fulfillment_status );

				do_action(
					'easycommerce_log',
					array(
						'object'    => 'order',
						'action'    => 'fulfillment_status',
						'object_id' => $order_id,
						'type'      => 'fulfillment_status',
						'note'      => 'Order fulfillment status changed from ' . ( $fulfill_statuses[ $old_fulfillment_status ] ?? $old_fulfillment_status ) . ' to ' . ( $fulfill_statuses[ $new_fulfillment_status ] ?? $new_fulfillment_status ),
					)
				);
			}
		}

		if ( ! $updated ) {
			$this->response_error( __( 'Failed to update order.', 'easycommerce' ), 500 );
		}

		$this->response_success(
			array(
				'message'  => __( 'Order updated successfully.', 'easycommerce' ),
				'order_id' => $order_id,
			)
		);
	}

	public function bulk_update_statuses( $request ) {
		$order_ids 		  = $request->get_param( 'order_ids' );
		$status    		  = $request->get_param( 'status' );
		$type      		  = $request->get_param( 'type' );
		$order_statuses   = easycommerce_order_statuses();
    	$fulfill_statuses = easycommerce_fulfill_statuses();

		if ( empty( $order_ids ) || ! is_array( $order_ids ) ) {
			return $this->response_error( __( 'Invalid order IDs.', 'easycommerce' ), 400 );
		}

		if ( ! in_array( $type, array( 'status', 'fulfillment' ), true ) ) {
			return $this->response_error( __( 'Invalid type parameter.', 'easycommerce' ), 400 );
		}

		foreach ( $order_ids as $order_id ) {
			$order = new Order_Model( $order_id );

			if ( ! $order->exists() ) {
				continue;
			}

			$old_status = null;

			if ( $type === 'status' ) {
				$old_status = $order->get_status();
				$order->set_status( $status );

				if ( $status !== $old_status ) {
					/**
					 * Fires when order status is updated.
					 *
					 * @since 1.1
					 * @param int $order_id The order ID.
					 * @param string $new_status The new status.
					 * @param string $old_status The old status.
					 */
					do_action( 'easycommerce_order_status_updated', $order_id, $status, $old_status );

					do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'order_update', 'object_id' => $order_id, 'type' => 'order_status', 'note'=> 'Order status changed from ' . ( $order_statuses[ $old_status ] ?? $old_status ) . ' to ' . ( $order_statuses[ $status ] ?? $status ) ) );
				}
			} elseif ( $type === 'fulfillment' ) {
				$old_status = $order->get_fulfillment_status();
				$order->set_fulfillment_status( $status );

				if ( $status !== $old_status ) {
					/**
					 * Fires when order fulfillment status is updated.
					 *
					 * @since 1.1
					 * @param int $order_id The order ID.
					 * @param string $new_status The new status.
					 * @param string $old_status The old status.
					 */
					do_action( 'easycommerce_order_fulfillment_status_updated', $order_id, $status, $old_status );

				    do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'fulfillment_status', 'object_id' => $order_id, 'type' => 'fulfillment_status', 'note'=> 'Order fulfillment status changed from ' . ( $fulfill_statuses[ $old_status ] ?? $old_status ) . ' to ' . ( $fulfill_statuses[ $status ] ?? $status ) ) );
				}
			}

			do_action( 'easycommerce_order_email', $status, $order_id );
		}

		return $this->response_success(
			array(
				'message' => __( 'Orders updated successfully.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Delete an order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete_order( $request ) {
		$order_id = $request->get_param( 'id' );
		$order    = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			$this->response_success( array( 'message' => __( 'Order not found.', 'easycommerce' ) ) );
		}

		/**
		 * Fires before deleting an order.
		 *
		 * @since 1.9
		 * @param int $order_id The order ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_order', $order_id, $request );

		$order->delete();

		/**
		 * Fires after deleting an order.
		 *
		 * @since 1.9
		 * @param int $order_id The order ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_order', $order_id, $request );

		do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'delete', 'object_id' => $order_id, 'note' => __( 'Order Deleted', 'easycommerce' ) . ' #' . $order_id ) );

		$this->delete_order_cache();

		$this->response_success(
			array(
				'message' => __( 'Order deleted successfully.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Refund an order.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function refund( $request ) {
		$order_id = $request->get_param( 'id' );
		$order    = new Order_Model( $order_id );
		$reason   = $request->get_param( 'reason' );

		if ( is_null( $amount = $request->get_param( 'amount' ) ) ) {
			$amount = $order->get_total();
		}

		if ( ! $order->exists() ) {
			$this->response_success( array( 'message' => __( 'Order not found.', 'easycommerce' ) ) );
		}

		$order->update( array( 'status' => 'refunded' ) );

		do_action( 'easycommerce_after_refund_order', $order, $reason );
		do_action( 'easycommerce_after_refund_order_' . $order->get_payment_method(), $order_id, $reason, $amount );

		// Fire after payment-gateway hooks in case they create refund records
		// (so get_total_refunded() in email placeholders returns the correct amount).
		do_action( 'easycommerce_order_email', 'refunded', $order_id );

		do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'refund', 'object_id' => $order_id, 'meta' => array( 'refund_amount' => $amount, 'reason' => $reason ), 'note' => 'Refund: ' . easycommerce_price( $amount ) ) );

		return $this->response_success(
			array(
				'message' => __( 'Order refunded successfully.', 'easycommerce' ),
			)
		);

		return $this->response_error(
			__( 'Failed to refund order.', 'easycommerce' ),
			500
		);
	}

	/**
	 * Send an email.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function email( $request ) {
		$order_id = $request->get_param( 'id' );
		$event    = $request->get_param( 'event' );
		$order    = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			$this->response_error(
				array(
					'message' => __( 'Invalid order ID', 'easycommerce' ),
				)
			);
		}

		if (
			Utility::get_option( 'email', $event, 'customer_enabled', false )
			&& ! empty( $customer_subject = Utility::get_option( 'email', $event, 'customer_subject', '' ) )
			&& ! empty( $customer_body = Utility::get_option( 'email', $event, 'customer_body', '' ) )
		) {

			$customer     = new Customer( $order->get_customer_id() );
			$customer_email = $customer->get_email();

			if ( ! empty( $customer_email ) ) {
				$placeholders = easycommerce_order_placeholders( $order_id );

				// Email to the customer
				do_action( 'easycommerce_email', $customer_email, $customer_subject, $customer_body, $placeholders );

				$mail_sent = apply_filters( 'easycommerce_mail_sent', false );

				if ( $mail_sent ) {
					do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'email_send', 'object_id' => $order_id, 'meta' => array( 'event' => $event ), 'note' => 'Email sent: ' . $event ) );

					$this->response_success(
						array(
							'message' => __( 'Email sent.', 'easycommerce' ),
						)
					);
					return;
				}
			}
		}

		do_action( 'easycommerce_log', array( 'object' => 'order', 'action' => 'email_fail', 'type' => 'error', 'object_id' => $order_id, 'meta' => array( 'event' => $event ), 'note' => 'Email failed: ' . $event ) );

		$this->response_error(
			array(
				'message' => __( 'Email not sent', 'easycommerce' ),
			)
		);
	}

	/**
	 * List orders with optional filters such as per_page, page, status, customer_id and customer_email.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_all( $request ) {
		$args = array(
			'per_page'     => $request->get_param( 'per_page' ) ?: 10,
			'page'         => $request->get_param( 'page' ) ?: 1,
			'status'       => $request->get_param( 'status' ) ?? null,
			'customer_id'  => $request->get_param( 'customer_id' ) ?? null,
			'search_query' => $request->get_param( 'search_query' ) ?? null,
			'from_date'    => $request->get_param( 'from' ) ?? null,
			'to_date'      => $request->get_param( 'to' ) ?? null,
			'product_id'   => $request->get_param( 'product_id' ) ?? null,
		);

		// @todo improve logic
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_pages' ) ) {
			$args['customer_id'] = get_current_user_id();
		}

		$result = Order_Model::list( $args );

		if ( empty( $result['orders'] ) ) {
			return $this->response_success( array( 'message' => __( 'No orders found.', 'easycommerce' ) ) );
		}

		$orders = array_map(
			function ( $order ) {
				$customer          = new Customer( $order['customer'] );
				$order['customer'] = array(
					'id'   => $customer->get_id(),
					'name' => $customer->get_name(),
				);

				return $order;
			},
			$result['orders']
		);

		return $this->response_success(
			array(
				'orders'          => $orders,
				'total'           => $result['total'],
				'per_page'        => $result['per_page'],
				'page'            => $result['page'],
				'total_pages'     => $result['total_pages'],
				'statuses_counts' => $result['statuses_counts'],
			)
		);
	}

	public function bulk_delete( $request ) {
		$order_ids = $request->get_param( 'order_ids' );

		foreach ( $order_ids as $order_id ) {
			$order = new Order_Model( $order_id );
			$order->delete();
		}

		/**
		 * Fires after bulk deleting orders.
		 *
		 * @since 1.9
		 * @param array $order_ids The order IDs.
		 */
		do_action( 'easycommerce_after_bulk_delete_order', $order_ids );

		$this->delete_order_cache();

		return $this->response_success(
			array( 'message' => __( 'Orders deleted successfully.', 'easycommerce' ) )
		);
	}

	/**
	 * Process payment for an existing pending order (agent-created orders).
	 */
	public function pay( $request ) {
		$order_id = (int) $request->get_param( 'id' );
		$order    = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			$this->response_error( __( 'Order not found.', 'easycommerce' ), 404 );
			return;
		}

		if ( $order->get_status() !== 'pending' ) {
			$this->response_error( __( 'Order is not pending payment.', 'easycommerce' ), 400 );
			return;
		}

		$params = array_merge( (array) $request->get_params(), $_POST ); // phpcs:ignore WordPress.Security.NonceVerification

		$customer_id = $order->get_customer_id();

		do_action( 'easycommerce_after_create_order', $order_id, $params, $customer_id );

		$default_status = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
		$status         = apply_filters( 'easycommerce_order_status', $default_status, $order_id, $params, $customer_id );

		$default_fulfill = Utility::get_option( 'order', 'settings', 'default_fulfill_status', 'unfulfilled' );
		$fulfill_status  = apply_filters( 'easycommerce_order_fulfill_status', $default_fulfill, $order_id, $params, $customer_id );

		$order->set_status( $status );
		$order->set_fulfillment_status( $fulfill_status );

		$payment_methods = array_keys( easycommerce_payment_methods());

		if ( ! empty( $params['easycommerce-payment_method'] ) && in_array( $params['easycommerce-payment_method'], $payment_methods, true ) ) {
			$order->update( array( 'payment_method' => sanitize_text_field( $params['easycommerce-payment_method'] ) ) );
		}

		do_action( 'easycommerce_order_email', $status, $order_id );
		do_action( 'easycommerce_after_order', $order_id, $params, $status );

		$redirect = easycommerce_order_redirect( $order_id );

		return $this->response_success(
			array(
				'message'  => __( 'Payment processed successfully.', 'easycommerce' ),
				'order_id' => $order_id,
				'status'   => $status,
				'redirect' => $redirect,
			)
		);
	}

	/**
	 * Validate address fields before creating an order.
	 *
	 * @param array  $address   Associative array of address data.
	 * @param string $type      'billing' or 'shipping'.
	 * @return WP_REST_Response|null Null on success, error response on failure.
	 */
	private function validate_address_fields( $address, $type ) {
		if ( ! is_array( $address ) ) {
			return $this->response_error(
				/* translators: %s: address type */
				sprintf( __( 'Invalid %s address data.', 'easycommerce' ), $type ),
				400
			);
		}

		$first_name = isset( $address['first_name'] ) ? trim( $address['first_name'] ) : '';
		if ( empty( $first_name ) ) {
			return $this->response_error( __( 'First name is required.', 'easycommerce' ), 400 );
		}
		if ( ! preg_match( '/^[\p{L}\s\'\-]+$/u', $first_name ) ) {
			return $this->response_error( __( 'First name may only contain letters, spaces, hyphens, and apostrophes.', 'easycommerce' ), 400 );
		}

		$last_name = isset( $address['last_name'] ) ? trim( $address['last_name'] ) : '';
		if ( ! empty( $last_name ) && ! preg_match( '/^[\p{L}\s\'\-]+$/u', $last_name ) ) {
			return $this->response_error( __( 'Last name may only contain letters, spaces, hyphens, and apostrophes.', 'easycommerce' ), 400 );
		}

		if ( 'billing' === $type ) {
			$email = isset( $address['email'] ) ? trim( $address['email'] ) : '';
			if ( empty( $email ) ) {
				return $this->response_error( __( 'Email address is required.', 'easycommerce' ), 400 );
			}
			if ( ! is_email( $email ) ) {
				return $this->response_error( __( 'Please enter a valid email address.', 'easycommerce' ), 400 );
			}
		}

		$phone = isset( $address['phone'] ) ? trim( $address['phone'] ) : '';
		if ( ! empty( $phone ) && ! preg_match( '/^[+\d\s\-(). ]{6,20}$/', $phone ) ) {
			return $this->response_error( __( 'Please enter a valid phone number.', 'easycommerce' ), 400 );
		}

		$postcode = isset( $address['postcode'] ) ? trim( $address['postcode'] ) : '';
		if ( ! empty( $postcode ) && ! preg_match( '/^[A-Za-z0-9\s\-]{3,10}$/', $postcode ) ) {
			return $this->response_error( __( 'Please enter a valid postcode.', 'easycommerce' ), 400 );
		}

		return null;
	}
}

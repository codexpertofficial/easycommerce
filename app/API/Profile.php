<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;
use EasyCommerce\Models\Customer;
use EasyCommerce\Abstracts\User;
use EasyCommerce\Helpers\Utility;

class Profile extends API {

	/**
	 * Gets current user summary
	 */
	public function get_summary( $request ) {
		$customer_id = get_current_user_id();
		$customer    = new Customer( $customer_id );

		if ( ! $customer->get_id() ) {
			$this->response_success( array( 'message' => __( 'Customer not found.', 'easycommerce' ) ) );
		}

		$orders = $customer->get_orders( 5 );

		$customer_data = array(
			'id'         => $customer->get_id(),
			'photo'      => $customer->get_photo(),
			'first_name' => $customer->get_first_name(),
			'last_name'  => $customer->get_last_name(),
			'name'       => $customer->get_first_name() . ' ' . $customer->get_last_name(),
			'email'      => $customer->get_email(),
			'since'      => $customer->get_join_date(),
			'aov'        => $customer->get_aov(),
			'ltv'        => $customer->get_ltv(),
			'count'      => $customer->get_order_count(),
			'orders'     => $orders,
			'billing'    => $customer->get_billing_address(),
			'shipping'   => $customer->get_shipping_address(),
			'last_order' => ! empty( $orders ) ? end( $orders )['created_at'] : null,
		);

		/**
		 * Filters the profile summary data.
		 *
		 * @since 1.9
		 * @param array $customer_data The customer data.
		 * @param WP_REST_Request $request The request object.
		 */
		$customer_data = apply_filters( 'easycommerce_get_profile_summary', $customer_data, $request );

		$this->response_success( array( 'customer' => $customer_data ) );
	}

	/**
	 * Gets current user data
	 */
	public function get_data( $request ) {

		$customer_id = get_current_user_id();

		if ( ! is_array( $fields = $request->get_param( 'fields' ) ) ) {
			$fields = explode( ',', $fields );
		}

		$fields    = array_map( 'trim', $fields );
		$data      = array();
		$customer  = new Customer( $customer_id );
		
		foreach ( $fields as $field ) {
			$method = "get_{$field}";

			if ( method_exists( $customer, $method ) ) {
				$data[ $field ] = $customer->$method();
			} else {
				$data[ $field ] = $customer->get_meta( $field );
			}
		}

		/**
		 * Filters the profile data.
		 *
		 * @since 1.9
		 * @param array $data The data.
		 * @param array $fields The fields.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_get_profile_data', $data, $fields, $request );

		$this->response_success( array( 'customer' => $data ) );
	}

	/**
	 * Sets current user data
	 */
	public function set_data( $request ) {

		$customer_id = get_current_user_id();
		$fields      = $request->get_param( 'fields' );
		$customer    = new Customer( $customer_id );

		$allowed_meta_keys = array(
			'first_name',
			'last_name',
			'email',
			'phone',
			'billing_address',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_country',
		);

		/**
		 * Fires before updating profile data.
		 *
		 * @since 1.9
		 * @param int $customer_id The customer ID.
		 * @param array $fields The fields.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_profile', $customer_id, $fields, $request );

		foreach ( $fields as $field => $value ) {
			$method = "set_{$field}";

			if ( method_exists( $customer, $method ) ) {
				$customer->$method( $value );
			} elseif ( in_array( $field, $allowed_meta_keys, true ) ) {
				$customer->update_meta( $field, $value );
			}
		}

		/**
		 * Fires after updating profile data.
		 *
		 * @since 1.9
		 * @param int $customer_id The customer ID.
		 * @param array $fields The fields.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_profile', $customer_id, $fields, $request );

		$this->response_success( array( 'message' => __( 'Customer data updated', 'easycommerce' ) ) );
	}

	/**
	 * List customer orders with optional filters such as `s` for search, `page`, and `per_page`.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_orders( $request ) {
		$args = array( 
			'customer_id' => get_current_user_id(),
			'per_page'    => $request->get_param( 'per_page' ) ?: 10,
			'page'        => $request->get_param( 'page' ) ?: 1,
		);

		$result = Order::list( $args );

		if ( empty( $result['orders'] ) ) {
			return $this->response_success( array( 'message' => __( 'No orders found.', 'easycommerce' ) ) );
		}

		$orders = array_map(
			function ( $order ) {
				$customer          = new Customer( $order['customer'] );
				$order['customer'] = $customer->get_name();
				return $order;
			},
			$result['orders']
		);

		/**
		 * Filters the profile orders.
		 *
		 * @since 1.9
		 * @param array $orders The orders.
		 * @param WP_REST_Request $request The request object.
		 */
		$orders = apply_filters( 'easycommerce_get_profile_orders', $orders, $request );

		return $this->response_success(
			array(
				'orders'      => $orders,
				'total'       => $result['total'],
				'per_page'    => $result['per_page'],
				'page'        => $result['page'],
				'total_pages' => $result['total_pages'],
			)
		);
	}

	/**
	 * List customer transactions with optional filters such as `s` for search, `page`, and `per_page`.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_transactions( $request ) {
		$order_id  = $request->get_param( 'order_id' );
		$status    = $request->get_param( 'status' );
		$page      = $request->get_param( 'page' ) ?: 1;
		$per_page  = $request->get_param( 'per_page' ) ?: 10;
		$status    = $request->get_param( 'status' );
		$filters   = array( 'customer_id' => get_current_user_id() );

		if ( ! is_null( $status ) ) {
			$filters['status'] = $status;
		}

		// Retrieve transactions and the total count using the static model method
		$result = Transaction::list( $filters, $per_page, ( $page - 1 ) * $per_page );

		if ( empty( $result['transactions'] ) ) {
			$this->response_success( array( 'message' => __( 'No transactions found.', 'easycommerce' ) ) );
		}

		// Format the response data
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

		$total_pages = ceil( $result['total'] / $per_page );

		$this->response_success(
			array(
				'transactions' => $formatted_transactions,
				'total'        => $result['total'],
				'page'         => $page,
				'per_page'     => $per_page,
				'total_pages'  => $total_pages,
			),
			200
		);
	}

	/**
	 * List customer downloads with optional filters such as `s` for search, `page`, and `per_page`.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_downloads( $request ) {
		$id       = get_current_user_id();
		$search   = $request->get_param( 's' );
		$page     = $request->get_param( 'page' ) ?: 1;
		$per_page = $request->get_param( 'per_page' ) ?: 10;
		$customer = new Customer( $id );

		if ( ! $customer->get_id() ) {
			return $this->response_success( array( 'message' => __( 'Customer not found.', 'easycommerce' ) ) );
		}

		// Shared entitlement: paid orders, digital variations, keyed by media_id.
		$downloads_map = $customer->get_downloads();

		foreach ( $downloads_map as $download ) {
			$download->type = easycommerce_get_file_type( $download->filename );
			// Expose only the gated URL; never leak the raw public attachment URL.
			$download->url = easycommerce_secure_download( $download->media_id );
			unset( $download->secure_url );
		}

		$all_downloads 	 	 = array_values( $downloads_map );
		$total 				 = count( $all_downloads );
		$offset 			 = ( $page - 1 ) * $per_page;
		$paginated_downloads = array_slice( $all_downloads, $offset, $per_page );
		$total_pages 		 = ceil( $total / $per_page );

		return $this->response_success( array(
			'downloads'    => $paginated_downloads,
			'total'        => $total,
			'total_pages'  => $total_pages,
			'per_page'     => $per_page,
			'current_page' => $page,
		) );
	}
}

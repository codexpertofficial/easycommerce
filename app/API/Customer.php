<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Abstracts\User;
use EasyCommerce\Models\Customer as Customer_Model;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Product_Variation;

class Customer extends API {

	/**
	 * Create a new customer.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create( $request ) {
		$first_name     = $request->get_param( 'first_name' );
		$last_name      = $request->get_param( 'last_name' );
		$photo          = $request->get_param( 'photo' );
		$email          = $request->get_param( 'email' );
		$password       = $request->get_param( 'password' );
		$password_again = $request->get_param( 'password_again' );
		$meta           = $request->get_param( 'meta' ) ?? array();

		// Validate passwords match
		if ( $password !== $password_again ) {
			$this->response_error( __( 'Passwords do not match.', 'easycommerce' ), 400 );
		}

		// Create the customer
		$customer = new Customer_Model();
		$created  = $customer->create(
			array(
				'email'      => $email,
				'name'       => $first_name . ' ' . $last_name,
				'password'   => $password,
				'first_name' => $first_name,
				'last_name'  => $last_name,
			)
		);

		if ( ! $created ) {
			$this->response_error( __( 'Failed to create customer.', 'easycommerce' ), 500 );
		}

		if ( $created && ! is_null( $photo ) ) {
			$customer->set_photo( $photo );
		}

		foreach ( $meta as $key => $value ) {
			$customer->add_meta( $key, $value );
		}

		/**
		 * Logs the customer creation event.
		 */
		do_action( 'easycommerce_log', array( 'object' => 'customer', 'action' => 'create', 'object_id' => $customer->get_id(), 'note' => $first_name . ' ' . $last_name ) );

		$this->response_success(
			array(
				'message'     => __( 'Customer created successfully.', 'easycommerce' ),
				'customer_id' => $customer->get_id(),
			),
			201
		);
	}

	/**
	 * Get a specific customer.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get( $request ) {
		$customer_id = $request->get_param( 'id' );

		// Load the customer
		$customer = new Customer_Model( $customer_id );

		if ( ! $customer->get_id() ) {
			$this->response_success( array( 'message' => __( 'Customer not found.', 'easycommerce' ) ) );
		}

		$orders = $customer->get_orders();

		// Fetch customer data
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
			'last_order' => $customer->get_last_order_date(),
		);

		/**
		 * Filters the customer data before sending the response.
		 *
		 * @since 1.9
		 * @param array $customer_data The customer data.
		 * @param int $customer_id The customer ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$customer_data = apply_filters( 'easycommerce_get_customer_data', $customer_data, $customer_id, $request );

		$this->response_success( array( 'customer' => $customer_data ) );
	}

	/**
	 * List customers with optional filters such as `s` for search, `page`, and `per_page`.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function list( $request ) {
		$search      	= $request->get_param( 's' );
		$customer_id 	= $request->get_param( 'customer_id' );
		$page        	= (int) ( $request->get_param( 'page' ) ?: 1 );
		$per_page    	= (int) ( $request->get_param( 'per_page' ) ?: 10 );
		$type        	= $request->get_param( 'type' ) ?: 'all';
		$all_customers 	= Customer_Model::customer_list( $search, 1, -1 );
		$all_users     	= ! empty( $all_customers['users'] ) ? $all_customers['users'] : [];
		$statuses 		= [
			'all'       => count( $all_customers['users'] ),
			'recurring' => 0,
			'one_time'  => 0,
		];
	
		foreach ( $all_customers['users'] as $user ) {
			$customer    = new Customer_Model( $user['id'] );
			$order_count = (int) $customer->get_order_count();

			if ( $order_count > 1 ) {
				$statuses['recurring']++;
			} else {
				$statuses['one_time']++;
			}
		}
		
		// Apply the type filter to get the correct list of customers and total count
		$all_customers_filtered = array_filter( $all_users, function( $user ) use ( $type ) {
			$customer    = new Customer_Model( $user['id'] );
			$order_count = (int) $customer->get_order_count();

			if ( $type === 'recurring' ) {
				return $order_count > 1;
			}

			if ( $type === 'one_time' ) {
				return $order_count <= 1;
			}

			return true;
		} );
		

		$total_customers 	= count( $all_customers_filtered );
		$total_pages 		= $per_page > 0 ? ceil( $total_customers / $per_page ) : 1;
		$page				= max( 1, min( $page, $total_pages ) );
		$customers_for_page = array_slice( $all_customers_filtered, ( $page - 1 ) * $per_page, $per_page );
		
		// Format the customers for the response
		$formatted_customers = array_map(
			function ( $user ) {
				$customer = new Customer_Model( $user['id'] );

				return [
					'id'         => $customer->get_id(),
					'name'       => $customer->get_name(),
					'email'      => $customer->get_email(),
					'photo'      => $customer->get_photo(),
					'since'      => $customer->get_join_date(),
					'orders'     => $customer->get_order_count(),
					'ltv'        => $customer->get_ltv(),
					'aov'        => $customer->get_aov(),
					'last_order' => $customer->get_last_order_date(),
				];
			},
			$customers_for_page
		);

		/**
		 * Filters the list of customers before sending the response.
		 *
		 * @since 1.9
		 * @param array $formatted_customers The formatted customers.
		 * @param WP_REST_Request $request The request object.
		 */
		$formatted_customers = apply_filters( 'easycommerce_list_customers', $formatted_customers, $request );

		return $this->response_success( [
			'customers'   => array_values( $formatted_customers ),
			'total'       => $total_customers,
			'page'        => (int) $page,
			'per_page'    => (int) $per_page,
			'total_pages' => (int) $total_pages,
			'statuses'    => $statuses
		] );
	}

	/**
	 * Update an existing customer.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( $request ) {
		$customer_id = $request->get_param( 'id' );
		$first_name  = $request->get_param( 'first_name' );
		$last_name   = $request->get_param( 'last_name' );
		$email       = $request->get_param( 'email' );

		// Load the customer
		$customer = new Customer_Model( $customer_id );

		if ( ! $customer->get_id() ) {
			$this->response_success( array( 'message' => __( 'Customer not found.', 'easycommerce' ) ) );
		}

		/**
		 * Fires before updating a customer.
		 *
		 * @since 1.9
		 * @param int $customer_id The customer ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_customer', $customer_id, $request );

		// Update customer details
		$customer->set_name( $first_name . ' ' . $last_name );
		$customer->set_email( $email );

		if ( ! $customer->save() ) {
			$this->response_error( __( 'Failed to update customer.', 'easycommerce' ), 500 );
		}

		/**
		 * Fires after updating a customer.
		 *
		 * @since 1.9
		 * @param int $customer_id The customer ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_customer', $customer_id, $request );

		$this->response_success(
			array(
				'message' => __( 'Customer updated successfully.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Delete a customer.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$customer_id = $request->get_param( 'id' );

		// Load the customer
		$customer = new Customer_Model( $customer_id );

		if ( ! $customer->get_id() ) {
			$this->response_success( array( 'message' => __( 'Customer not found.', 'easycommerce' ) ) );
		}

		/**
		 * Fires before deleting a customer.
		 *
		 * @since 1.9
		 * @param int $customer_id The customer ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_customer', $customer_id, $request );

		// Delete the customer
		if ( ! $customer->delete() ) {
			$this->response_error( __( 'Failed to delete customer.', 'easycommerce' ), 500 );
		}

		/**
		 * Fires after deleting a customer.
		 *
		 * @since 1.9
		 * @param int $customer_id The customer ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_customer', $customer_id, $request );

		/**
		 * Logs the customer deletion event.
		 */
		do_action( 'easycommerce_log', array( 'object' => 'customer', 'action' => 'delete', 'object_id' => $customer_id, 'note' => $customer->get_name() ) );

		$this->response_success(
			array(
				'message' => __( 'Customer deleted successfully.', 'easycommerce' ),
			)
		);
	}

	/**
	 * List customer orders with optional filters such as `s` for search, `page`, and `per_page`.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function list_orders( $request ) {
		$id       = $request->get_param( 'id' );
		$search   = $request->get_param( 's' );
		$page     = $request->get_param( 'page' ) ?: 1;
		$per_page = $request->get_param( 'per_page' ) ?: 10;

		// @todo implement s, page etc param

		// Load the customer
		$customer = new Customer_Model( $id );

		if ( ! $customer->get_id() ) {
			$this->response_success( array( 'message' => __( 'Customer not found.', 'easycommerce' ) ) );
		}

		$orders = $customer->get_orders();

		/**
		 * Filters the customer orders before sending the response.
		 *
		 * @since 1.9
		 * @param array $orders The orders.
		 * @param int $id The customer ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$orders = apply_filters( 'easycommerce_list_customer_orders', $orders, $id, $request );

		$this->response_success( array( 'orders' => $orders ) );
	}
}

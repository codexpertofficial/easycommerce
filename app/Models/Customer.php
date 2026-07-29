<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\User;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Product_Variation;
use WP_User_Query as User_Query;

/**
 * Concrete Customer Class
 */
class Customer extends User {

	protected $role = 'customer';

	public function __construct( $id = null ) {
		parent::__construct( $id );
	}
	/**
	 * Get order count
	 *
	 * @return int
	 */
	public function get_order_count( $range = '' ) {
		$args = array(
			'customer_id' => $this->id,
			'per_page'    => -1,
		);

		if ( ! empty( $range ) ) {
			$date_range        = Utility::get_date_range( $range );
			$args['from_date'] = $date_range[0];
			$args['to_date']   = $date_range[1];
		}

		$orders = Order::list( $args );

		return count( $orders['orders'] );
	}
	/**
	 * Get total spent
	 *
	 * @return int
	 */
	public function get_ltv() {

		$total_spent = $this->get_total_spent();

		return easycommerce_price( $total_spent );
	}

	/**
	 * Get average order value
	 *
	 * @return int
	 */
	public function get_aov() {
		$total_spent = $this->get_total_spent();
		$orders      = $this->get_order_count();

		if ( empty( $orders ) || empty( $total_spent ) ) {
			return 0.0;
		}

		return easycommerce_price( $total_spent / $orders );
	}

	/**
	 * Get last order date
	 *
	 * @return string
	 */
	public function get_last_order_date() {
		$result = Order::list(
			array(
				'customer_id' => $this->id,
				'per_page'    => 1,
				'orderby'     => 'created_at',
				'order'       => 'DESC',
			)
		);

		return ! empty( $result['orders'] ) ? $result['orders'][0]['created_at'] : null;
	}

	/**
	 * Get phone number
	 *
	 * @return string
	 */
	public function get_phone( $type = '' ) {
		if ( in_array( $type, ['billing', 'shipping'], true ) ) {
			$value = $this->get_address_field( 'phone', $type );
			if ( ! empty( $value ) ) {
				return $value;
			}
		}
		return $this->get_meta( 'phone' );
	}

	/**
	 * Get billing address
	 *
	 * @return array The billing address fields, or an empty array when unset.
	 */
	public function get_billing_address() {
		return $this->get_meta( 'billing_address' ) ?: array();
	}

	/**
	 * Get shipping address
	 *
	 * @return array The shipping address fields, or an empty array when unset.
	 */
	public function get_shipping_address() {
		return $this->get_meta( 'shipping_address' ) ?: array();
	}

	/**
	 * Retrieve a specific field from the billing or shipping address.
	 *
	 * @param string $field The field name to retrieve.
	 * @param string $type  The address type ('billing' or 'shipping'). Default is 'billing'.
	 * @return string The value of the requested field or an empty string if not set.
	 */
	public function get_address_field( $field, $type = 'billing' ) {
		if ( in_array( $type, [ 'billing', 'shipping' ], true ) ) {
			$address = ( $type === 'billing' ) ? $this->get_billing_address() : $this->get_shipping_address();
			if ( isset( $address[ $field ] ) && ! empty( $address[ $field ] ) ) {
				return $address[ $field ];
			}
		}
		return '';
	}
	
	public function get_first_name( $type = '' ) {
		if ( in_array( $type, ['billing', 'shipping'], true ) ) {
			$value = $this->get_address_field( 'first_name', $type );
			if ( ! empty( $value ) ) {
				return $value;
			}
		}
		return $this->get_meta( 'first_name' );
	}
	
	public function get_last_name( $type = '' ) {
		if ( in_array( $type, ['billing', 'shipping'], true ) ) {
			$value = $this->get_address_field( 'last_name', $type );
			if ( ! empty( $value ) ) {
				return $value;
			}
		}
		return $this->get_meta( 'last_name' );
	}

	public function get_name( $type = '' ) {
		$first_name = $this->get_first_name( $type );
		$last_name  = $this->get_last_name( $type );
		$full_name  = trim( $first_name . ' ' . $last_name );
		if ( ! empty( $full_name ) ) {
			return $full_name;
		}

		// Fall back to WordPress display_name when address/meta names are not set.
		return parent::get_name();
	}
	
	public function get_email( $type = '' ) {
		if ( in_array( $type, ['billing', 'shipping'], true ) ) {
			$value = $this->get_address_field( 'email', $type );
			if ( ! empty( $value ) ) {
				return $value;
			}
		}
		$user_data = get_userdata( $this->id );
		return $user_data ? $user_data->user_email : '';
	}

	public function get_address_1( $type = 'billing' ) {
		return $this->get_address_field( 'address_1', $type );
	}

	public function get_address_2( $type = 'billing' ) {
		return $this->get_address_field( 'address_2', $type );
	}

	public function get_country( $type = 'billing' ) {
		return $this->get_address_field( 'country', $type );
	}

	public function get_state( $type = 'billing' ) {
		return $this->get_address_field( 'state', $type );
	}

	public function get_city( $type = 'billing' ) {
		return $this->get_address_field( 'city', $type );
	}

	public function get_postcode( $type = 'billing' ) {
		return $this->get_address_field( 'postcode', $type );
	}

	/**
	 * @since 1.2.3
	 */
	public function get_address( $type = 'billing' ) {
		$address = [];
		$address[] = $this->get_address_1( $type );
		$address[] = $this->get_address_2( $type );
		$address[] = $this->get_city( $type );
		$address[] = $this->get_state( $type );
		$address[] = $this->get_country( $type );

		return implode( ', ', array_filter( $address, function( $part ) {
			return ! empty( trim( $part ) );
		} ) );
	}

	/**
	 * Get orders
	 *
	 * @param int $count
	 * @return array
	 */
	public function get_orders( $count = -1 ) {
		$result = Order::list(
			array(
				'customer_id' => $this->id,
				'per_page'    => $count,
			)
		);

		return $result['orders'];
	}

	/**
	 * Get the customer's orders filtered by one or more statuses.
	 *
	 * @param string|array $status One status or a list of statuses.
	 * @param int          $count  Number of orders to return (-1 for all).
	 * @return array
	 */
	public function get_orders_by_status( $status, $count = -1 ) {
		$statuses = (array) $status;

		$result = Order::list(
			array(
				'customer_id' => $this->id,
				'per_page'    => $count,
			)
		);

		return array_values(
			array_filter(
				$result['orders'],
				function ( $order ) use ( $statuses ) {
					return in_array( $order['status'], $statuses, true );
				}
			)
		);
	}

	/**
	 * Order statuses that entitle a customer to access digital downloads.
	 *
	 * Excludes unpaid (`pending`), aborted (`cancelled`, `on_hold`) and
	 * fully-refunded (`refunded`) orders. A paid order that is still
	 * `processing` counts, so buyers are not blocked before completion.
	 *
	 * @since 1.32
	 * @return array
	 */
	public static function downloadable_order_statuses() {
		/**
		 * Filters the order statuses that grant digital-download access.
		 *
		 * @since 1.32
		 * @param array $statuses The entitled order statuses.
		 */
		return apply_filters(
			'easycommerce_downloadable_order_statuses',
			array( 'processing', 'completed', 'partially_refunded' )
		);
	}

	/**
	 * Get the customer's downloadable files, keyed by media ID.
	 *
	 * Single source of truth for download entitlement: only orders whose
	 * status is in {@see self::downloadable_order_statuses()} are considered,
	 * and only `digital` variations contribute files. Used by both the
	 * dashboard list (`/me/downloads`) and the secure-download handler so the
	 * two can never disagree.
	 *
	 * @since 1.32
	 * @return array<int,object> media_id => decorated download object.
	 */
	public function get_downloads() {
		$statuses      = self::downloadable_order_statuses();
		$downloads_map = array();

		foreach ( $this->get_orders() as $order ) {
			if ( ! in_array( $order['status'], $statuses, true ) ) {
				continue;
			}

			$order_obj = new Order( $order['id'] );

			foreach ( $order_obj->get_items() as $order_item ) {
				$variation = new Product_Variation( $order_item->variation_id );

				if ( 'digital' !== $variation->get_type() ) {
					continue;
				}

				$result = $variation->get_downloads();

				if ( empty( $result['downloads'] ) ) {
					continue;
				}

				foreach ( $result['downloads'] as $download ) {
					$media_id = (int) $download->media_id;

					if ( isset( $downloads_map[ $media_id ] ) ) {
						continue;
					}

					$download->order_id         = $order['id'];
					$downloads_map[ $media_id ] = $download;
				}
			}
		}

		return $downloads_map;
	}

	/**
	 * Get total spent
	 *
	 * @return float
	 */
	public function get_total_spent() {
		$orders      = $this->get_orders();
		$total_spent = 0.0;
		if ( empty( $orders ) ) {
			return $total_spent;
		}

		foreach ( $orders as $order ) {
			$order_instance = new Order( $order['id'] );
			$total_spent   += $order_instance->get_total();
		}
		return (float) $total_spent;
	}

	/**
	 * List customers with optional filters such as search, page, and per_page.
	 *
	 * @param string|null $search
	 * @param int         $page
	 * @param int         $per_page
	 * @return array List of customers with pagination info
	 */
	public static function customer_list( $search = null, $page = 1, $per_page = 10 ) {

		$args = array(
			'number'         => $per_page,
			'paged'          => $page,
			'search'         => $search ? '*' . esc_attr( $search ) . '*' : '',
			'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
			'fields'         => 'all', // get full user objects
		);

		$query = new User_Query( $args );
		$users = $query->get_results();

		// Filter users who have the 'has_order' capability
		$users_with_cap = array_filter( $users, function ( $user ) {
			return user_can( $user, 'has_order' );
		} );

		$total_users = count( $users_with_cap );

		if ( empty( $users_with_cap ) ) {
			return array(
				'users'       => array(),
				'total'       => 0,
				'per_page'    => $per_page,
				'page'        => $page,
				'total_pages' => 0,
			);
		}

		// Pagination for filtered users
		// $offset = ( $page - 1 ) * $per_page;
		// $paged_users = array_slice( $users_with_cap, $offset, $per_page );
		
		if ( $per_page == -1 ) {
        	$paged_users = $users_with_cap;
		} else {
			$offset = ( $page - 1 ) * $per_page;
			$paged_users = array_slice( $users_with_cap, $offset, $per_page );
		}

		$formatted_users = array_map(
			function ( $user ) {
				return array(
					'id'    => $user->ID,
					'name'  => $user->display_name,
					'email' => $user->user_email,
					'role'  => implode( ', ', $user->roles ),
				);
			},
			$paged_users
		);

		return array(
			'users'       => $formatted_users,
			'total'       => $total_users,
			'per_page'    => $per_page,
			'page'        => $page,
			'total_pages' => ceil( $total_users / $per_page ),
		);
	}
}
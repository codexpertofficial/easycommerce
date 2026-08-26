<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Order_Item;
use EasyCommerce\Models\Tax;
use EasyCommerce\Models\Order_Meta;
use EasyCommerce\Abstracts\Model;
use EasyCommerce\Models\Customer;

/**
 * Concrete Order Class
 * Handles order-related operations in the database.
 */
class Order extends Model {

	/**
	 * @var int Order ID
	 */
	protected $id;

	/**
	 * @var int Customer ID
	 */
	protected $customer_id = 0;

	/**
	 * @var float Order total amount
	 */
	protected $total = 0.0;
	/**
	 * @var float Order subtotal amount
	 */
	protected $subtotal = 0.0;

	/**
	 * @var string Order status
	 */
	protected $status = 'pending';

	/**
	 * @var string Fulfillment status
	 */
	protected $fulfill_status = '';

	/**
	 * @var string Payment method
	 */
	protected $payment_method = '';

	/**
	 * @var string Order creation date
	 */
	protected $created_at = '';

	/**
	 * @var string Last modified date
	 */
	protected $updated_at = '';

	/**
	 * @var bool Order existence flag
	 */
	protected $exists = false;

	/**
	 * @var Order_Meta Instance for handling order meta data
	 */
	protected $meta;

	/**
	 * @var Order_Item Instance for handling order items
	 */
	protected $items;

	protected $table = 'orders';

	/**
	 * Constructor for the Order class.
	 *
	 * @param int|null $id Optional. The order ID.
	 */
	public function __construct( $id = null ) {
		parent::__construct();
		$this->meta  = new Order_Meta();
		$this->items = new Order_Item();

		if ( $id && $order = $this->db->get_by_id( $id ) ) {
			$this->id             = $id;
			$this->customer_id    = $order->customer_id;
			$this->total          = $order->total;
			$this->status         = $order->status;
			$this->fulfill_status = $order->fulfill_status;
			$this->payment_method = $order->payment_method;
			$this->created_at     = $order->created_at;
			$this->updated_at     = $order->updated_at;
			$this->exists         = true;
		}
	}

	/**
	 * Get the order ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Check if the order exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Get the customer ID for the order.
	 *
	 * @return int
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	public function get_customer_name() {
		$customer_id = $this->get_customer_id();
		$customer        = new Customer( $customer_id );
		$customer_name = $customer->get_name();
		return $customer_name;
	}

	/**
	 * Get the total amount of the order.
	 *
	 * @return float
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Get the subtotal amount of the order.
	 *
	 * @return float
	 */
	public function get_subtotal() {
		$items    = $this->get_items();
		$subtotal = 0;

		$order_item_meta = new Order_Item_Meta();

		foreach ( $items as $item ) {
			if ( ! $order_item_meta->get( $item->id, 'is_free' ) ) {
				$subtotal += $item->price;
			}
		}
		return $subtotal;
	}

	/**
	 * Get the product tax amount of the order.
	 *
	 * @return float
	 */
	public function get_product_tax() {
		return (float) $this->get_meta( 'tax' );
	}

	/**
	 * Get the shipping tax amount of the order.
	 *
	 * @return float
	 */
	public function get_shipping_tax() {
		return (float) $this->get_meta( 'shipping_tax' );
	}

	/**
	 * Get the tax amount of the order.
	 *
	 * @return float
	 */
	public function get_tax_total() {
		return (float) $this->get_meta( 'tax' ) + (float) $this->get_meta( 'shipping_tax' );
	}

	/**
	 * Get the shipping amount of the order.
	 *
	 * @return float
	 */
	public function get_shipping_total() {
		return (float) $this->get_meta( 'shipping_fee' );
	}

	/**
	 * Get the coupon discount amount of the order.
	 *
	 * @return float
	 */
	public function get_discount_total() {
		return (float) $this->get_meta( 'discount_amount' );
	}

	/**
	 * Get the status of the order.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the status of the order.
	 *
	 * @return bool|int
	 */
	public function set_status( $status ) {

		do_action( 'easycommerce-set_order_status', $status, $this );

		return $this->update( array( 'status' => $status ) );
	}

	/**
	 * Get the fulfillment status of the order.
	 *
	 * @return string
	 */
	public function get_fulfillment_status() {
		return $this->fulfill_status;
	}

	/**
	 * Set the fulfillment status of the order.
	 *
	 * @return bool|int
	 */
	public function set_fulfillment_status( $fulfill_status ) {
		do_action( 'easycommerce-set_order_fulfillment_status', $fulfill_status, $this );
		return $this->update( array( 'fulfill_status' => $fulfill_status ) );
	}

	/**
	 * Get the payment method for the order.
	 *
	 * @return string
	 */
	public function get_payment_method() {
		return $this->payment_method;
	}

	public function get_created_at() {
		return $this->created_at;
	}

	public function get_updated_at() {
		return $this->updated_at;
	}

	public function get_transactions( $formatted = false ) {
		$transaction_model = new Transaction();

		$transactions = array_map(
			function ( $transaction ) use ( $formatted ) {

				unset( $transaction->order_id );
				unset( $transaction->currency );
				unset( $transaction->created_at );

				if ( $formatted == true ) {
						$transaction->amount = easycommerce_price( $transaction->amount );
				}

				return $transaction;
			},
			$transaction_model->get_by_order_id( $this->id )
		);

		return $transactions;
	}

	public function get_refunds( $formatted = false ) {
		$refund_model = new Refund();

		$refunds = array_map(
			function ( $refund ) use ( $formatted ) {

				unset( $refund->order_id );

				if ( $formatted == true ) {
						$refund->amount = easycommerce_price( $refund->amount );
				}

				return $refund;
			},
			$refund_model->get_by_order_id( $this->id )
		);

		return $refunds;
	}

	/**
	 * Get the total refunded amount for the order.
	 *
	 * @return float
	 */
	public function get_total_refunded() {
		$refunds = $this->get_refunds();
		$total = 0.0;
		foreach ( $refunds as $refund ) {
			$total += $refund->amount;
		}
		return $total;
	}

	/**
	 * Create a new order.
	 *
	 * @param array $args Order data.
	 * @return bool|int Order ID on success, false on failure.
	 */
	public function create( $args ) {
		if ( ! isset( $args['customer_id'] ) || ! isset( $args['total'] ) ) {
			return false;
		}

		$data = array(
			'customer_id'     => $args['customer_id'],
			'total'           => $args['total'],
			'status'          => $args['status'] ?? 'pending',
			'fulfill_status'  => $args['fulfill_status'] ?? '',
			'payment_method'  => $args['payment_method'] ?? '',
			'created_at'      => current_time( 'mysql' ),
		);

		$order_id = $this->db->insert_row( $data );

		if ( $order_id ) {
			$this->id             = $order_id;
			$this->customer_id    = $data['customer_id'];
			$this->total          = $data['total'];
			$this->status         = $data['status'];
			$this->fulfill_status = $data['fulfill_status'];
			$this->payment_method = $data['payment_method'];
			$this->created_at     = $data['created_at'];
			$this->updated_at     = $data['created_at']; // Initially set to created_at
			$this->exists         = true;

			$product_variation_model = new Product_Variation();

			// Add items to the order
			foreach ( $args['items'] as $product_id => $variations ) {
				$product_total_quantity = 0;
				foreach ( $variations as $price_id => $item ) {

					$product_variation    = $product_variation_model->get_by_price( $price_id, $product_id );

					if( ! $product_variation ) continue;

					$free_quantity = isset( $item['free_quantity'] )
						? min( (int) $item['free_quantity'], (int) $item['quantity'] )
						: ( empty( $item['is_free'] ) ? 0 : (int) $item['quantity'] );

					if ( $product_variation->manages_stock() && ! is_null( $current_stock = $product_variation->get_stock() ) && $item['quantity'] > $current_stock ) {
						$item['quantity'] = $current_stock;
						$free_quantity    = min( $free_quantity, $item['quantity'] );
					}

					$paid_quantity = max( 0, $item['quantity'] - $free_quantity );
					$is_free       = 0 === $paid_quantity && $item['quantity'] > 0;

					$item['product_id']    = $product_id;
					$item['price_id']      = $price_id;
					$item['variation_id']  = $product_variation->get_id();
					$item['tax_class_id']  = $product_variation->get_tax_class();
					$item['free_quantity'] = $free_quantity;
					$item['subtotal']      = $paid_quantity * $item['rate'];
					$item['meta']          = array(
						'name'          => $product_variation->get_product()->get_title(),
						'price'         => $product_variation->get_price( true ),
						'attributes'    => $product_variation->get_name( false ),
						'is_free'       => $is_free,
						'free_quantity' => $free_quantity,
					);

					$this->add_item( $item );

					// Every unit leaves the warehouse, including the gifted ones.
					$product_variation->reduce_stock( $item['quantity'] );

					$product_total_quantity += $paid_quantity;
				}

				// Only update total sales if there were non-free products
				if ( $product_total_quantity > 0 ) {
					$current_total_sale = intval( get_post_meta( $product_id, 'total_sale', true ) );
					$new_total_sale     = $current_total_sale + $product_total_quantity;
					update_post_meta( $product_id, 'total_sale', $new_total_sale );
				}
			}

			// Add meta to the order
			foreach ( $args['meta'] as $key => $value ) {
				$this->update_meta( $key, $value );
			}

			return $order_id;
		}

		return false;
	}

	/**
	 * Update the order.
	 *
	 * @param array $data Updated order data.
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
	 * Delete the order.
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
	 * Add order meta data using the Order_Meta class.
	 *
	 * @param string $key Meta key.
	 * @param mixed  $value Meta value.
	 * @return bool True on success, false on failure.
	 */
	public function add_meta( $key, $value ) {
		return $this->meta->add( $this->id, $key, $value );
	}

	/**
	 * Get order meta data using the Order_Meta class.
	 *
	 * @param string $key Meta key.
	 * @param bool   $single Whether to return a single value.
	 * @return mixed Meta value or null if not found.
	 */
	public function get_meta( $key, $single = true ) {
		return $this->meta->get( $this->id, $key, $single );
	}

	/**
	 * Update order meta data using the Order_Meta class.
	 *
	 * @param string $key Meta key.
	 * @param mixed  $value Meta value.
	 * @return bool True on success, false on failure.
	 */
	public function update_meta( $key, $value ) {
		return $this->meta->update( $this->id, $key, $value );
	}

	/**
	 * Delete order meta data using the Order_Meta class.
	 *
	 * @param string $key Meta key.
	 * @return bool True on success, false on failure.
	 */
	public function delete_meta( $key ) {
		return $this->meta->delete( $this->id, $key );
	}

	/**
	 * Get all items for the order using the Order_Item class.
	 *
	 * @return array List of order items.
	 */
	public function get_items( $formatted = false ) {
		$items = array_map(
			function ( $item ) use ( $formatted ) {

				unset( $item->order_id );

				if ( $formatted == true ) {
						$item->rate  = easycommerce_price( $item->rate );
						$item->price = easycommerce_price( $item->price );
				}

				return $item;
			},
			$this->items->get_by_order_id( $this->id )
		);

		return $items;
	}

	/**
	 * Add an item to the order using the Order_Item class.
	 *
	 * @param array $item_data Item data (product ID, quantity, price, etc.).
	 * @return bool True on success, false on failure.
	 */
	public function add_item( $item_data ) {
		return $this->items->add( $this->id, $item_data );
	}

	/**
	 * Remove an item from the order using the Order_Item class.
	 *
	 * @param int $item_id The order item ID.
	 * @return bool True on success, false on failure.
	 */
 	public function remove_item( $item_id ) {
 		$item = new Order_Item( $item_id );
 		return $item->delete();
 	}

	/**
	 * Update an item in the order using the Order_Item class.
	 *
	 * @param int   $item_id The order item ID.
	 * @param array $data    Updated item data.
	 * @return bool True on success, false on failure.
	 */
 	public function update_item( $item_id, $data ) {
 		$item = new Order_Item( $item_id );
 		return $item->update( $data );
 	}

	/**
	 * Static method to list orders with optional filters such as per_page, page, status, and search_query.
	 *
	 * @param array $args Arguments that may include 'per_page', 'page', 'status', and 'search_query'.
	 * @return array List of orders and pagination info.
	 */
	public static function list( $args = array(), $formatted = false ) {

		$per_page       = isset( $args['per_page'] ) ? (int) $args['per_page'] : 1;
		$page           = isset( $args['page'] ) ? (int) $args['page'] : 1;
		$search_query   = isset( $args['search_query'] ) ? $args['search_query'] : null;
		$customer_id    = isset( $args['customer_id'] ) ? $args['customer_id'] : null;
		$customer_email	= isset( $args['customer_email'] ) ? $args['customer_email'] : null;
		$status         = isset( $args['status'] ) ? $args['status'] : null;
		$fulfill_status = isset( $args['fulfill_status'] ) ? $args['fulfill_status'] : null;
		$from_date      = isset( $args['from_date'] ) ? trim( $args['from_date'] ) : null;
		$to_date        = isset( $args['to_date'] ) ? trim( $args['to_date'] ) : null;
		$product_id     = isset( $args['product_id'] ) ? $args['product_id'] : null;
		$meta_query    	= isset( $args['meta_query'] ) ? $args['meta_query'] : array();
		$db             = new Database( 'orders' );
		$where          = array();

		if ( ! empty( $search_query ) && is_email( $search_query ) ) {
			$customer = get_user_by( 'email', $search_query );
			$id       = '';
			if( ! empty( $customer ) ) {
				$id = $customer->ID;
			}
			$where[]     = array( 'customer_id' => $id );
		}
		
		if( is_numeric( $search_query ) ) {
			$where[] = array( 'id' => $search_query );
		}

		if ( ! empty( $customer_email ) && is_email( $customer_email ) ) {
			$customer = get_user_by( 'email', $customer_email );
			$id       = '';
			if( ! empty( $customer ) ) {
				$id = $customer->ID;
			}
			$where[]     = array( 'customer_id' => $id );
		}

		if( ! empty( $customer_id ) ) {
			$where[] = array( 'customer_id' => $customer_id );
		}

		if ( ! empty( $status ) ) {
			$where[] = array( 'status' => $status );
		}

		if ( ! empty( $fulfill_status ) ) {
			$where[] = array( 'fulfill_status' => $fulfill_status );
		}

		if ( ! empty( $from_date ) ) {
			$from_date = date( 'Y-m-d 00:00:00', strtotime( $from_date ) );
			$where[]   = array( 'created_at' => array( '>=', $from_date ) );
		}

		if ( ! empty( $to_date ) ) {
			$to_date = date( 'Y-m-d 23:59:59', strtotime( $to_date ) );
			$where[] = array( 'created_at' => array( '<=', $to_date ) );
		}
		if ( ! empty( $product_id ) ) {
			global $wpdb;
			$orders_table      = $db->get_prefix() . 'orders';
			$order_items_table = $db->get_prefix() . 'order_items';

			$orders_by_product_id  = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT o.id 
					FROM {$orders_table} o
					INNER JOIN {$order_items_table} oi ON o.id = oi.order_id
					WHERE oi.product_id = %d",
					$product_id
				)
			);
			$orders_by_product_ids = array_column( $orders_by_product_id, 'id' );
			$where[]               = array( 'id' => array( 'IN', $orders_by_product_ids ) );

		}

		if ( ! empty( $meta_query ) && is_array( $meta_query ) ) {
			global $wpdb;
			$meta_db             	= new Database( 'order_meta' );
			$order_meta_table 		= $meta_db->get_table();
	
			foreach ( $meta_query as $meta ) {
				if ( empty( $meta['key'] ) || ! isset( $meta['value'] ) ) continue;
	
				$order_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT DISTINCT order_id FROM {$order_meta_table}
					WHERE meta_key = %s AND meta_value = %s",
					$meta['key'],
					$meta['value']
				) );
	
				if ( empty( $order_ids ) ) {
					return array(
						'orders' 			=> array(),
						'total' 			=> 0,
						'per_page' 			=> $per_page,
						'page' 				=> $page,
						'total_pages' 		=> 0
					);
				}
	
				$where[] = array( 'id' => array( 'IN', $order_ids ) );
			}
		}
		
		$orders       = $db->get_rows( $where, $per_page, ( $page - 1 ) * $per_page );

		if ( empty( $orders ) ) {
			return array(
				'orders'   => array(),
				'total'    => 0,
				'per_page' => $per_page,
				'page'     => $page,
			);
		}
		$total_orders = $db->get_count( $where );
 		// Process the orders and return relevant data.
 		$formatted_orders = array_map(
 			function ( $order_data ) use ( $args, $formatted ) {
 				$order         = new self( $order_data->id );
 				$customer_id   = $order->get_customer_id();
 				$user          = get_user_by( 'id', $customer_id );
 				$customer_name = $user ? $user->display_name : 'Guest';
		
				$order_array = array(
					'id'             => $order->get_id(),
					'status'         => $order->get_status(),
					'fulfill_status' => $order->get_fulfillment_status(),
					'items'          => array_sum( wp_list_pluck( $order->get_items(), 'quantity' ) ),
					'total'          => isset( $formatted ) && $formatted ? easycommerce_price( $order->get_total() ) : $order->get_total(),
					'subtotal'       => isset( $formatted ) && $formatted ? easycommerce_price( $order->get_subtotal() ) : $order->get_subtotal(),
					'refunded_total' => isset( $formatted ) && $formatted ? easycommerce_price( $order->get_total_refunded() ) : $order->get_total_refunded(),
					'available_for_refund' => isset( $formatted ) && $formatted ? easycommerce_price( $order->get_total() - $order->get_total_refunded() ) : $order->get_total() - $order->get_total_refunded(),
					'customer'       => $customer_id,
					'transactions'   => current( $order->get_transactions( true ) ),
					'created_at'     => $order->get_created_at(),
					'created_at_formatted' => Utility::format_date( $order->get_created_at() ),
					'created_time'   => Utility::format_time( $order->get_created_at() ),
					'updated_at'     => $order->get_updated_at(),
					'customer_name'  => $customer_name,
				);
		
				$order_array = apply_filters( 'easycommerce_order_list_item', $order_array, $order, $args );
		
				return $order_array;
			},
			$orders
		);

		//Get each status count
		$orders 			= $db->get_rows(); //all orders
		$statuses_counts 	= [];

		foreach ( $orders as $order ) {
			$status = $order->status;

			if ( ! isset( $statuses_counts[ $status ] ) ) {
				$statuses_counts[ $status ] = 0;
			}

			$statuses_counts[ $status ]++;
		}

		return array(
			'orders'      		=> $formatted_orders,
			'total'       		=> $total_orders,
			'per_page'    		=> $per_page,
			'page'        		=> $page,
			'total_pages' 		=> ceil( $total_orders / $per_page ),
			'statuses_counts' 	=> $statuses_counts,
		);
	}
}
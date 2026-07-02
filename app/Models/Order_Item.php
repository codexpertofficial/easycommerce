<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Model;

/**
 * Class Order_Item
 * Handles order items and their metadata operations in the database.
 *
 * @package EasyCommerce\Model
 */
class Order_Item extends Model {

	/**
	 * @var Order Item ID
	 */
	protected $id;

	protected $table = 'order_items';

	/**
	 * @var Order_Item_Meta Instance for handling order item metadata
	 */
	protected $meta;

	/**
	 * Order_Item constructor.
	 * Initializes the class with the 'order_items' table and metadata handler.
	 */
	public function __construct( $id = null ) {
		parent::__construct();
		$this->meta = new Order_Item_Meta();

		if ( $id && ! empty( $this->get_by_id( $id ) ) ) {
			$this->id = $id;
		}
	}

	/**
	 * Get all items for a specific order by order ID.
	 *
	 * @param int $order_id The order ID.
	 * @return array List of order items.
	 */
	public function get_by_order_id( $order_id ) {
		return $this->db->get_rows( array( 'order_id' => $order_id ) );
	}

	/**
	 * Add a new item to the order.
	 *
	 * @param int   $order_id The order ID.
	 * @param array $item_data The item data (product ID, quantity, price, etc.).
	 * @return bool|int Item ID on success, false on failure.
	 */
	public function add( $order_id, $item_data ) {
 		$data = array(
 			'order_id'     => $order_id,
 			'product_id'   => $item_data['product_id'],
 			'variation_id' => $item_data['variation_id'] ?? null,
 			'price_id'     => $item_data['price_id'] ?? null,
 			'quantity'     => $item_data['quantity'],
 			'rate'         => $item_data['rate'] ?? 0,
 			'price'        => $item_data['price'],
 			'tax_class_id' => $item_data['tax_class_id'] ?? 0,
 			'tax_rate'     => $item_data['tax_rate'] ?? 0.0000,
 			'subtotal'     => $item_data['subtotal'] ?? 0.00,
 		);

 		$item_id = $this->db->insert_row( $data );

 		if ( $item_id ) {
 			$this->id = (int) $item_id;

 			// Add metadata for the item
 			if ( isset( $item_data['meta'] ) && ! empty( $item_data['meta'] ) ) {
 				foreach ( $item_data['meta'] as $key => $value ) {
 					$this->add_meta( $key, $value );
 				}
 			}
 		}

		return $item_id;
	}

	/**
	 * Update an existing order item.
	 *
	 * @param int   $item_id The item ID.
	 * @param array $data    Updated item data (quantity, price, etc.).
	 * @return bool True on success, false on failure.
	 */
	public function update( $data ) {
		$updated = $this->db->update_row( $this->id, $data );

 		if ( $updated && isset( $data['meta'] ) && ! empty( $data['meta'] ) ) {

 			// Update metadata for the item
 			foreach ( $data['meta'] as $key => $value ) {
 				$this->update_meta( $key, $value );
 			}
 		}

		return $updated;
	}

	/**
	 * Delete an order item by its item ID.
	 * The associated metadata will be automatically deleted by the database.
	 *
	 * @param int $item_id The item ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		return $this->db->delete_row( $this->id );
	}

	/**
	 * Get a specific order item by its ID.
	 *
	 * @param int $item_id The item ID.
	 * @return object|null The order item object if found, otherwise null.
	 */
	public function get_by_id( $item_id ) {
		return $this->db->get_by_id( $item_id );
	}

	/**
	 * Get a specific order item by its ID.
	 *
	 * @param int $item_id The item ID.
	 * @return object|null The order item object if found, otherwise null.
	 */
	public function get_by( $value, $key = 'order_id' ) {
		return $this->db->get_rows( array( $key => $value ) );
	}

	/**
	 * Add metadata to a specific order item using Order_Item_Meta.
	 *
	 * @param int    $item_id The item ID.
	 * @param string $key     The meta key.
	 * @param mixed  $value   The meta value.
	 * @return bool True on success, false on failure.
	 */
	public function add_meta( $key, $value ) {
		return $this->meta->add( $this->id, $key, $value );
	}

	/**
	 * Get metadata for a specific order item using Order_Item_Meta.
	 *
	 * @param int    $item_id The item ID.
	 * @param string $key     The meta key.
	 * @param bool   $single  Whether to return a single value.
	 * @return mixed The meta value or null if not found.
	 */
	public function get_meta( $key, $single = true ) {
		return $this->meta->get( $this->id, $key, $single );
	}

	/**
	 * Update metadata for a specific order item using Order_Item_Meta.
	 *
	 * @param int    $item_id The item ID.
	 * @param string $key     The meta key.
	 * @param mixed  $value   The meta value.
	 * @return bool True on success, false on failure.
	 */
	public function update_meta( $key, $value ) {
		return $this->meta->update( $this->id, $key, $value );
	}

	/**
	 * Delete metadata for a specific order item using Order_Item_Meta.
	 *
	 * @param int    $item_id The item ID.
	 * @param string $key     The meta key.
	 * @return bool True on success, false on failure.
	 */
	public function delete_meta( $key ) {
		return $this->meta->delete( $this->id, $key );
	}
}

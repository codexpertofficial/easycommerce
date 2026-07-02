<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Abstracts\Model;

/**
 * Coupon Class
 * Handles coupon-related operations in the database.
 */
class Coupon extends Model {

	protected $id;
	protected $name;
	protected $code;
	protected $type          = 'percentage';
	protected $offer         = 0.0;
	protected $active        = 1;
	protected $exists        = false;

	protected $table = 'coupons';

	/**
	 * Constructor for the Coupon class.
	 *
	 * @param int|string|null $identifier Optional. The coupon ID or code.
	 */
	public function __construct( $identifier = null ) {
		parent::__construct();

		if ( is_numeric( $identifier ) ) {
			$this->load_by_id( $identifier );

			if ( ! $this->exists ) {
				$this->load_by_code( $identifier );
			}
		} elseif ( is_string( $identifier ) ) {
			$this->load_by_code( $identifier );
		}
	}

	protected function load_by_id( $id ) {
		$coupon = $this->db->get_by_id( $id );

		if ( $coupon ) {
			$this->initialize_coupon( $coupon );
			$this->exists = true;
		}
	}

	protected function load_by_code( $code ) {
		$coupon = $this->db->get_row( array( 'code' => $code ) );

		if ( $coupon ) {
			$this->initialize_coupon( $coupon );
			$this->exists = true;
		}
	}

	protected function initialize_coupon( $coupon ) {
		$this->id            = $coupon->id;
		$this->name          = $coupon->name;
		$this->code          = $coupon->code;
		$this->type          = $coupon->type;
		$this->offer         = $coupon->offer;
		$this->active        = $coupon->active;
	}

	public function exists() {
		return $this->exists;
	}

	public static function get( $identifier ) {
		$coupon = new self( $identifier );

		if ( ! $coupon->exists() ) {
			return null;
		}

		// Format offer if it's a serialized array
		$offer = $coupon->get_offer();
		if ( $coupon->get_type() === 'products' ) {
			$offer = maybe_unserialize( $coupon->get_offer() );
		}

		return array(
			'id'        => $coupon->get_id(),
			'name'      => $coupon->get_name(),
			'code'      => $coupon->get_code(),
			'type'      => $coupon->get_type(),
			'offer'     => $offer,
			'active'    => $coupon->is_active(),
			'rules'     => $coupon->get_rules( true ),
		);
	}

	public function get_id() {
		return $this->id;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_code() {
		return $this->code;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_offer() {
		return $this->offer;
	}

	// @todo: remove exec and use get_rows
	public function get_usage_count() {

		$order_meta = new Database( 'order_meta' );
		$code       = $this->code;
		$sql        = $order_meta->prepare(
			"SELECT * FROM {$order_meta->get_table()}
			 WHERE meta_key = %s 
			 AND meta_value LIKE %s",
			'coupons',
			'%"' . $code . '"%'
		);

		$orders = $order_meta->exec( $sql );

		return count( $orders );
	}

	public function is_active() {
		return (bool) $this->active;
	}

	public function set_status( $status ) {
		$this->update( array( 'active' => $status ) );
	}

	public function create( $args ) {
		if ( ! isset( $args['name'] ) || ! isset( $args['code'] ) || ! isset( $args['type'] ) ) {
			return false;
		}

		if ( $args['type'] !== 'free_shipping' && ! isset( $args['offer'] ) ) {
			return false;
		}

		$data = array(
			'name'          => $args['name'],
			'code'          => $args['code'],
			'type'          => $args['type'],
			'offer'         => $args['offer'] ?? '',
			'active'        => $args['active'] ?? 1,
		);

		// Serialize offer if it's a percentage
		if ( $data['type'] === 'products' ) {
			$data['offer'] = maybe_serialize( $data['offer'] );
		}

		$coupon_id = $this->db->insert_row( $data );

		if ( $coupon_id ) {
			$this->id = $coupon_id;

			if ( ! empty( $args['rules'] ) && is_array( $args['rules'] ) ) {
				foreach ( $args['rules'] as $rule ) {
					if ( isset( $rule['type'], $rule['value'] ) ) {
						$this->add_rule(
							array(
								'type'  => $rule['type'],
								'value' => $rule['value'],
							)
						);
					}
				}
			}

			return $coupon_id;
		}

		return false;
	}

	public function update( $data ) {
		if ( ! $this->exists ) {
			return false;
		}
		// Check if 'rules' key exists in $data
		$rules_provided = array_key_exists( 'rules', $data );
	
		// Extract rules only if present
		if ( $rules_provided ) {
			$rules = $data['rules'];
			unset( $data['rules'] );
		}

		// Serialize offer if it's a percentage
		if ( isset( $data['offer'] ) && $data['type'] === 'products' ) {
			$data['offer'] = maybe_serialize( $data['offer'] );
		}

		// Update main coupon data in the `coupons` table
		if ( ! empty( $data ) ) {
			$this->db->update_row( $this->id, $data );
		}
	
		// Update rules only if provided
		if ( $rules_provided ) {
			$this->update_rules( $rules );
		}
		return true;
	}
	/**
	 * Update the coupon's associated rules.
	 *
	 * @param array $rules Array of rules to update (each with 'type' and 'value').
	 */
	protected function update_rules( $rules ) {
		$rules_db = new Database( 'coupon_rules' );

		// Get existing rules for the coupon
		$existing_rules     = $this->get_rules();
		$existing_rules_map = array();

		// Map existing rules by their type for easier lookup
		foreach ( $existing_rules as $rule ) {
			$existing_rules_map[ $rule->type ] = $rule;
		}

		// Process each rule in the provided data
		foreach ( $rules as $rule_data ) {
			$type  = $rule_data['type'];
			$value = maybe_serialize( $rule_data['value'] );

			if ( isset( $existing_rules_map[ $type ] ) ) {
				// Rule exists, update it if value has changed
				if ( $existing_rules_map[ $type ]->value !== $value ) {
					$rules_db->update_row(
						$existing_rules_map[ $type ]->id,
						array( 'value' => $value )
					);
				}
				// Remove from map to keep track of rules that should remain
				unset( $existing_rules_map[ $type ] );
			} else {
				// Rule does not exist, add it
				$this->add_rule(
					array(
						'type'  => $type,
						'value' => $rule_data['value'],
					)
				);
			}
		}

		// Any remaining rules in $existing_rules_map should be removed
		foreach ( $existing_rules_map as $rule ) {
			$rules_db->delete_row( $rule->id );
		}
	}

	public function delete() {
		if ( ! $this->exists ) {
			return false;
		}

		return $this->db->delete_row( $this->id );
	}

	public function get_rules( $format = false ) {
		$rules_db = new Database( 'coupon_rules' );
		$rows     = $rules_db->get_rows( array( 'coupon_id' => $this->id ) );

		foreach ( $rows as $row ) {
			if ( $format ) {
				$row->value = json_encode( maybe_unserialize( $row->value ) );
			} else {
				$row->value = maybe_unserialize( $row->value );
			}
		}

		return $rows;
	}

	public function add_rule( $rule_data ) {
		$rules_db = new Database( 'coupon_rules' );

		$data = array(
			'coupon_id' => $this->id,
			'type'      => $rule_data['type'],
			'value'     => maybe_serialize( $rule_data['value'] ),
		);

		return $rules_db->insert_row( $data );
	}

	public static function list( $args = array() ) {
		$per_page 	= isset( $args['per_page'] ) ? (int) $args['per_page'] : 10;
		$page     	= isset( $args['page'] ) ? (int) $args['page'] : 1;
		$active   	= isset( $args['active'] ) ? $args['active'] : null;
		$from_date 	= isset( $args['from_date'] ) ? $args['from_date'] : null;
		$to_date  	= isset( $args['to_date'] ) ? $args['to_date'] : null;
		$search    	= isset( $args['search'] ) ? $args['search'] : null;
		$db 		= new Database( 'coupons' );
		$where 		= [];

		if ( $active !== null ) {
			$where['active'] = $active;
		}

		if ( ! empty( $from_date ) ) {
			$from_date = date( 'Y-m-d 00:00:00', strtotime( $from_date ) );
			$where[]   = array( 'created_at' => array( '>=', $from_date ) );
		}

		if ( ! empty( $to_date ) ) {
			$to_date = date( 'Y-m-d 23:59:59', strtotime( $to_date ) );
			$where[] = array( 'created_at' => array( '<=', $to_date ) );
		}

		if ( ! empty( $args['search'] ) ) {
			$where['code'] = $args['search'];
		}

		$total_coupons = $db->get_count( $where );
		$coupons       = $db->get_rows( $where, $per_page, ( $page - 1 ) * $per_page );

		$formatted_coupons = array_map(
			function ( $coupon_data ) {
				$coupon = new self( $coupon_data->id );

				// Format offer if it's a serialized array
				$offer = $coupon->get_offer();
				if ( $coupon->get_type() === 'products' ) {
					$offer = maybe_unserialize( $coupon->get_offer() );
				}

				return array(
					'id'            => $coupon->get_id(),
					'name'          => $coupon->get_name(),
					'code'          => $coupon->get_code(),
					'type'          => $coupon->get_type(),
					'offer'         => $offer,
					'usage'         => $coupon->get_usage_count(),
					'active'        => $coupon->is_active(),
				);
			},
			$coupons
		);

		$coupons          = $db->get_rows();
		$statuses_counts  = [
			'all'      => 0,
			'active'   => 0,
			'inactive' => 0,
		];
		
		foreach ( $coupons as $coupon ) {
			$statuses_counts['all']++;
		
			if ( intval( $coupon->active ) === 1 ) {
				$statuses_counts['active']++;
			} else {
				$statuses_counts['inactive']++;
			}
		}
		

		return array(
			'coupons'     => $formatted_coupons,
			'statuses'    => $statuses_counts,
			'total'       => $total_coupons,
			'per_page'    => $per_page,
			'page'        => $page,
			'total_pages' => ceil( $total_coupons / $per_page ),
		);
	}

	public function is_applicable( $cart = null ) {

		if( is_null( $cart ) ) {
			$cart = new Cart();
		}

		$amount       = $cart->get_amount( 'subtotal' );
		$rules        = $this->get_rules();
		$_items       = $cart->get_items();
		$current_date = wp_date( 'Y-m-d' );
		$items        = array_filter(
			$_items,
			function ( $_items ) {
				return ! empty( $_items );
			}
		);

		foreach ( $rules as $rule ) {
			if ( empty( $rule->value ) ) {
				continue;
			}
			$type = $rule->type;
			if ( $type == 'min_spend' && intval( $rule->value ) > $amount ) {
				return false;
			}
			if ( $type == 'max_spend' && intval( $rule->value ) < $amount ) {
				return false;
			}
			if ( $type == 'start_date' && $current_date < $rule->value ) {
				return false;
			}
			if ( $type == 'end_date' && $current_date > $rule->value ) {
				return false;
			}

			if ( $type == 'products' ) {
				$products  = $rule->value;
				$has_match = false;
				foreach ( $products as $product ) {
					$product_id = $product['id'];

					foreach ( $items as $cart_product_id => $value ) {
						if ( $product_id == $cart_product_id ) {
							$has_match = true;
						}
					}
				}
				if ( ! $has_match ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Get all product IDs associated with the coupon rules.
	 *
	 * @return array Array of unique product IDs.
	 */
	public function get_products() {
		$rules = $this->get_rules();
		$products = [];
		foreach ( $rules as $rule ) {
			if ( $rule->type == 'products' ) {
				$products  = $rule->value;
				foreach ( $rule->value ?? array() as $product ) {
					$products[] = $product['id'];
				}
			}
		}
		return array_unique( $products );
	}
}

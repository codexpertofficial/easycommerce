<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Shipping_Plan;
use EasyCommerce\Models\Coupon;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Tax;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Abstracts\Model;

/**
 * Cart Model
 *
 * @package EasyCommerce
 */
class Cart extends Model {

	use Cleaner;

	/**
	 * Cart data
	 *
	 * @var array
	 */
	public $cart;

	/**
	 * Cart hash
	 *
	 * @var string
	 */
	protected $hash;

	/**
	 * User ID
	 *
	 * @var int
	 */
	protected $user_id = null;

	/**
	 * Formatted cart cache
	 *
	 * @var array|null
	 */
	protected $formatted_cart = null;

	protected $table = 'cart_sessions';

	/**
	 * Constructor
	 *
	 * @param string $hash The cart hash.
	 */
	public function __construct( $hash = null ) {

		parent::__construct();

		// Assign the provided hash or fetch the user's cart hash.
		$this->hash = $hash ?: $this->get_user_hash();

		// Initialize the cart structure with default values.
		$this->cart = array(
			'data'       => array(),
			'total'      => 0,
			'status'     => 'pending',
			'reminders'  => 0,
			'updated_at' => null,
			'created_at' => null,
		);

		// Load the cart data from the database using the hash.
		$this->load_cart_by_hash( $this->hash );
	}

	/**
	 * Get the user's cart hash.
	 *
	 * @return string The user's cart hash.
	 */
	private function get_user_hash() {
		$hash = null;

		// Check if the user is logged in and fetch the cart hash from user meta.
		if ( is_user_logged_in() ) {
			$hash = get_user_meta( get_current_user_id(), '_easycommerce_cart_hash', true );
		}

		// If not logged in, fetch the cart hash from the cookie.
		elseif ( isset( $_COOKIE['easycommerce_cart_hash'] ) ) {
			$hash = $this->sanitize( $_COOKIE['easycommerce_cart_hash'] );
		}

		// If no hash is found, generate and set a new one.
		if ( empty( $hash ) ) {
			$hash = $this->set_user_hash();
		}

		return $hash;
	}

	/**
	 * Set the user's cart hash.
	 *
	 * @param string $hash The cart hash.
	 * @return string The cart hash.
	 */
	public function set_user_hash( $hash = '' ) {

		if ( '' === $hash ) {
			$hash = Utility::generate_hash();
		}

		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), '_easycommerce_cart_hash', $hash );
			return $hash;
		}

		if ( ! session_id() ) {
			session_start();

			setcookie( 'easycommerce_cart_hash', $hash, time() + WEEK_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
		}

		return $hash;
	}

	/**
	 * Clear the formatted cart cache.
	 */
	protected function clear_cache() {
		$this->formatted_cart = null;
	}

	/**
	 * Save the cart data to the database.
	 */
	public function save() {

		$this->clear_cache();
		
		$cart_data = array(
			'data'       	 => maybe_serialize( $this->cart['data'] ),
			'total'          => $this->get_amount('total'),
			'status'     	 => $this->cart['status'],
			'reminders'  	 => $this->cart['reminders'],
			'updated_at' 	 => current_time( 'mysql' ),
			'customer_name'  => $this->get_customer_name(),
			'customer_email' => $this->get_customer_email(),
		);

		$existing_cart = $this->db->get_row( array( 'hash' => $this->hash ) );

		if ( $existing_cart ) {
			$this->db->update_row( $existing_cart->id, $cart_data );
		} else {
			$cart_data['user_id']    = $this->user_id ?? get_current_user_id();
			$cart_data['hash']       = $this->hash;
			$cart_data['created_at'] = current_time( 'mysql' );

			$this->db->insert_row( $cart_data );
		}
	}

	/**
	 * Load the cart data from the database using the hash.
	 *
	 * @param string $hash The cart hash.
	 */
	public function load_cart_by_hash( $hash = null ) {

		$hash_to_use = $hash ?: $this->hash;

		$result = $this->db->get_row( array( 'hash' => $hash_to_use ) );

		if ( $result ) {
			$this->cart = array(
				'data'       => maybe_unserialize( $result->data ),
				'total'      => $result->total,
				'status'     => $result->status,
				'hash'       => $result->hash,
				'user_id'    => $result->user_id,
				'reminders'  => $result->reminders,
				'updated_at' => $result->updated_at,
				'created_at' => $result->created_at,
			);
		} else {
			$this->cart = array(
				'data'       => array(),
				'total'      => 0,
				'status'     => 'pending',
				'reminders'  => 0,
				'updated_at' => null,
				'created_at' => null,
			);
		}
		return $this->cart;
	}

	/**
	 * Get the items in the cart.
	 *
	 * @return array The items in the cart.
	 */
	public function get_items() {
		return $this->cart['data']['items'] ?? array();
	}

	/**
	 * Get the item count in the cart.
	 *
	 * @return int The item count in the cart.
	 */
	public function get_item_count() {
		$count = 0;

		foreach ( $this->get_items() as $product_id => $items ) {
			foreach ( $items as $price_id => $config ) {
				$count += $config['quantity'];
			}
		}

		return $count;
	}

	/**
	 * Get the selected shipping method ID
	 *
	 * @return int|bool
	 */
	public function get_shipping_method_id() {
		return $this->cart['data']['shipping_method'] ?? false;
	}

	/**
	 * Get the selected shipping method
	 *
	 * @return array|null
	 */
	public function get_shipping_method() {
		$methods = $this->get_shipping_methods();
		$id = $this->get_shipping_method_id();

		foreach ( $methods as $method ) {
			if( $method['id'] == $id ) {
				return $method;
			}
		}

		return null;
	}

	/**
	 * Get the selected shipping cost
	 *
	 * @return array|null
	 */
	public function get_shipping_cost() {
		$method = $this->get_shipping_method();

		return $method['cost'] ?? 0;
	}

	/**
	 * Get the shipping methods in the cart.
	 *
	 * @return array The shipping methods in the cart.
	 */
	public function get_shipping_methods() {
		return $this->cart['data']['shipping_methods'] ?? array();
	}

	/**
	 * Get the tax applicable on the cart items
	 *
	 * @return float The tax ammount
	 */
	public function get_item_tax() {
		if ( null === $this->formatted_cart ) {
			$this->formatted_cart = $this->get( true );
		}

		return $this->formatted_cart['amounts']['tax'] ?? 0;
	}

	/**
	 * Get the tax applicable on shipping
	 *
	 * @return float The tax ammount
	 */
	public function get_shipping_tax() {
		if ( null === $this->formatted_cart ) {
			$this->formatted_cart = $this->get( true );
		}

		return $this->formatted_cart['amounts']['shipping_tax'] ?? 0;
	}

	/**
	 * Get the tax applicable on shipping
	 *
	 * @return float The tax ammount
	 */
	public function get_tax() {
		return $this->get_item_tax() + $this->get_shipping_tax();
	}

	/**
	 * Get the data from the cart.
	 *
	 * @param string $column The column to get the data from.
	 * @return mixed The data from the cart.
	 */
	public function get_data( $column ) {

		if ( array_key_exists( $column, $this->cart ) ) {
			return $this->cart[ $column ];
		}

		return '';
	}

	/**
	 * If it's from a logged in user/customer
	 * 
	 * @return Customer object | false
	 */
	public function get_customer() {
		if( ! isset( $this->cart['user_id'] ) || $this->cart['user_id'] == 0 ) return false;

		$customer = new Customer( $this->cart['user_id'] );

		return $customer;
	}

	/**
	 * Get the cart data.
	 *
	 * @param bool $formatted Whether to format the cart data.
	 * @param bool $formatted_price Whether to format the price.
	 * @return array The cart data.
	 */
	public function get( $formatted = false, $formatted_price = true ) {
		$old_total = $this->get_total();
		if ( $formatted ) {
			$formatted_cart          = array();
			$product_variation_model = new Product_Variation();
			$total_discount          = 0;

			if ( ! empty( $this->get_items() ) ) {
				$tax_model = new Tax();
				$address   = $this->get_billing_address();
				$country   = $address['country'] ?? null;
				$state     = $address['state'] ?? null;
				$city      = $address['city'] ?? null;

				$formatted_cart['items']                      = array();
				$formatted_cart['amounts']['subtotal']        = 0;
				$formatted_cart['amounts']['discount_amount'] = 0;

				// Process items
				foreach ( $this->get_items() as $product_id => $items ) {
					foreach ( $items as $price_id => $config ) {
						$variation = $product_variation_model->get_by_price( $price_id, $product_id );
						
						if ( false === $variation ) {
							continue;
						}

						$is_free = $config['is_free'] ?? false;
						$tax = 0;

						$price = $config['rate'] * $config['quantity'];

						$formatted_cart['items'][] = array(
							'title'              => $variation->get_name( true ),
							'product_id'         => $product_id,
							'price_id'           => $price_id,
							'thumbnail'          => $variation->get_thumbnail(),
							'attributes'         => wp_list_pluck( $variation->get_attributes(), 'value_slug', 'attribute_slug' ),
							'quantity'           => $config['quantity'],
							'unit_price'         => $formatted_price ? easycommerce_price( $config['rate'] ) : $config['rate'],
							'subtotal'           => $is_free ? 0 : $price,
							'tax'                => $tax,
							'total'				 => $is_free ? 0 : $price,
							'formatted_total' => $is_free ? easycommerce_price( 0 ) : easycommerce_price( $price ),
							'discount'           => 0,
							'is_free'            => $is_free,
						);

						// Only add to subtotal if it's not a free product
						if ( ! $is_free ) {
							$formatted_cart['amounts']['subtotal'] += $price;
						}
					}
				}

				// Process coupons
				if ( ! empty( $this->cart['data']['coupons'] ) ) {
					$formatted_cart['fragments']['coupons'] = array();
					$applied_to                             = array();
					$total_applicable_subtotal              = 0;

					foreach ( $this->cart['data']['coupons'] as $code ) {
						$coupon = new Coupon( $code );
						if ( ! $coupon->is_active() ) {
							continue;
						}

						$coupon_rules = $coupon->get_rules();
						$apply_to_all = true;

						// Check if coupon is product-specific
						foreach ( $coupon_rules as $rule ) {
							if ( $rule->type === 'products' && ! empty( $rule->value ) ) {
								$apply_to_all = false;
								break;
							}
						}

						$applicable_items    = array();
						$applicable_subtotal = 0;

						// Identify applicable items and their subtotal
						foreach ( $formatted_cart['items'] as $index => $item ) {
							$product_id    = $item['product_id'];
							$is_applicable = $apply_to_all && ! $item['is_free']; // Don't apply discounts to free products

							if ( ! $apply_to_all && ! $item['is_free'] ) {
								foreach ( $coupon_rules as $rule ) {
									if ( $rule->type === 'products' && in_array( $product_id, wp_list_pluck( $rule->value, 'id' ) ) ) {
										$is_applicable             = true;
										$applied_to[ $product_id ] = $item['total'];
										break;
									}
								}
							}

							if ( $is_applicable ) {
								$applicable_items[]  = $index;
								$applicable_subtotal += $item['total'];
							}
						}

						// Validate min/max spend rules
						foreach ( $coupon_rules as $rule ) {
							if ( $rule->type === 'min_spend' && $rule->value != '' && $applicable_subtotal < intval( $rule->value ) ) {
								continue 2;
							}
							if ( $rule->type === 'max_spend' && $rule->value != '' && $applicable_subtotal > intval( $rule->value ) ) {
								continue 2;
							}
						}

						$coupon_discount = 0;
						if ( $coupon->get_type() === 'fixed' ) {
							$coupon_discount = min( $coupon->get_offer(), $applicable_subtotal );
						} elseif ( $coupon->get_type() === 'percentage' ) {
							$coupon_discount = $applicable_subtotal * $coupon->get_offer() / 100;
						} elseif ( $coupon->get_type() === 'products' ) {
							$coupon_discount = $this->handle_free_products( $coupon, $formatted_cart['items'], $applicable_items );
						}

						// Distribute discount proportionally across applicable items
						if ( ! empty( $applicable_items ) ) {
							$remaining_discount = $coupon_discount;
							$last_item_index    = end( $applicable_items );

							foreach ( $applicable_items as $index ) {
								$item          = $formatted_cart['items'][ $index ];
								$item_subtotal = $item['total'];

								if ( $index === $last_item_index ) {
									// Assign remaining discount to the last item to avoid rounding issues
									$item_discount = $remaining_discount;
								} else {
									// Calculate proportional discount
									$item_discount      = ( $item_subtotal / $applicable_subtotal ) * $coupon_discount;
									$item_discount      = round( $item_discount, 2 );
									$remaining_discount -= $item_discount;
								}

								$final_subtotal = $item_subtotal - $item_discount;
								$formatted_cart['items'][ $index ]['discount']           = $item_discount;
								$formatted_cart['items'][ $index ]['total']           = $final_subtotal;
								$formatted_cart['items'][ $index ]['formatted_total'] = easycommerce_price( $final_subtotal );
							}

							$total_discount            += $coupon_discount;
							$total_applicable_subtotal += $applicable_subtotal;

							$formatted_cart['fragments']['coupons'][ $code ] = array(
								'id'         => $coupon->get_id(),
								'code'       => $coupon->get_code(),
								'type'       => $coupon->get_type(),
								'value'      => $coupon->get_offer(),
								'amount'     => easycommerce_price( $coupon_discount ),
								'applies_to' => array_unique( array_keys( $applied_to ), SORT_NUMERIC ),
							);
						}
					}

					$total_discount                               = min( $total_discount, $total_applicable_subtotal );
					$formatted_cart['amounts']['discount_amount'] = $total_discount;
				}

				// Process fees
				if ( ! empty( $this->cart['data']['fees'] ) ) {
					$total_fee_discount  = 0;

					foreach ( $this->cart['data']['fees'] as $key => $value ) {
						$amount = $value;

						if ( $amount == 0 ) {
							continue;
						}
						$subtotal             = $formatted_cart['amounts']['subtotal'] ?? $this->get_amount();
						$max_discount_allowed = $subtotal - $total_discount;
						$discount             = min( abs( $amount ), $max_discount_allowed );
						$total_fee_discount   += $discount;
						if ( ! isset( $formatted_cart['fragments']['discount_details'] ) ) {
							$formatted_cart['fragments']['discount_details'] = [];
						}
						$formatted_cart['fragments']['discount_details'][ $key ] = $discount;
					}
					$formatted_cart['amounts']['discount_amount'] = $total_discount + $total_fee_discount;
				}

				// Calculate tax on final post-discount item totals (after coupons and fees)
				if ( ! empty( $country ) ) {
					foreach ( $formatted_cart['items'] as $index => $item ) {
						$variation = $product_variation_model->get_by_price( $item['price_id'], $item['product_id'] );

						if ( $variation ) {
							$product_tax_class = $variation->get_tax_class();

							if ( ! empty( $product_tax_class ) ) {
								$tax_rate = $tax_model->get_rate_by_location( $product_tax_class, $country, $state, $city );
							} else {
								$tax_rate = $tax_model->get_rate_for_location( $country, $state, $city );
							}

							$formatted_cart['items'][ $index ]['tax'] = round( ( $item['total'] * $tax_rate ) / 100, 2 );
						}
					}
				}

				// Calculate shipping and tax
				$formatted_cart['amounts']['shipping_fee'] = 0;
				$formatted_cart['amounts']['tax']          = round( array_sum( wp_list_pluck( $formatted_cart['items'], 'tax' ) ), 2 );
				$physical_subtotal                         = 0;
				$has_free_shipping_coupon                  = false;

				// Check if any applied coupon provides free shipping
				if ( ! empty( $this->cart['data']['coupons'] ) ) {
					foreach ( $this->cart['data']['coupons'] as $code ) {
						$coupon = new Coupon( $code );
						if ( $coupon->is_active() && $coupon->get_type() === 'free_shipping' ) {
							$has_free_shipping_coupon = true;
							break;
						}
					}
				}

				foreach ( $this->get_items() as $product_id => $items ) {
					foreach ( $items as $price_id => $item ) {
						$variation = $product_variation_model->get_by_price( $price_id, $product_id );
						$is_free = $item['is_free'] ?? false;

						if ( $variation && $variation->get_type() === 'physical' && ! $is_free ) {
							$physical_subtotal += (float) $item['price'];
						}
					}
				}

				// Only calculate shipping fee if no free shipping coupon is applied
				if ( isset( $this->cart['data']['shipping_method'] ) ) {
					$method = Shipping_Plan::get_shipping_plan_by_id( $this->cart['data']['shipping_method'] );

					if ( ! is_null( $method ) ) {
						$taxable_shipping = $method->taxable ?? false;
						$shipping_cost    = $method->cost;
						$formatted_cart['amounts']['shipping_fee'] = round( $shipping_cost, 2 );
						
						if ( $taxable_shipping && ! empty( $country ) ) {
							if( ! $this->is_shipping_same_as_billing() ) {
								$address = $this->get_shipping_address();
								$country = $address['country'] ?? null;
								$state   = $address['state'] ?? null;
								$city    = $address['city'] ?? null;
							}
							$shipping_tax_rate = $tax_model->get_rate_for_location( $country, $state, $city );
							
							if ( $shipping_tax_rate > 0 ) {
								$shipping_tax = ( $shipping_cost * $shipping_tax_rate ) / 100;
								$formatted_cart['amounts']['shipping_tax'] = round( $shipping_tax, 2 );
							}
						}
					}
				}

				if ( $this->has_item_type( 'physical' ) ) {
					$formatted_cart['fragments']['shipping_fee'] = easycommerce_price( $formatted_cart['amounts']['shipping_fee'] );

					if ( $has_free_shipping_coupon ) {
						$formatted_cart['fragments']['shipping_fee_discount'] = $formatted_cart['amounts']['shipping_fee'] ;
						$formatted_cart['amounts']['shipping_fee']            = 0;
						$formatted_cart['amounts']['shipping_tax']            = 0;
        				$formatted_cart['fragments']['shipping_tax']          = easycommerce_price( 0 );
					}
				}

				// Finalize amounts and fragments
				$formatted_cart['amounts']                        = apply_filters( 'easycommerce_cart_amounts', $formatted_cart['amounts'], $this->cart );
				$shipping_tax                                     = $formatted_cart['amounts']['shipping_tax'] ?? 0;
				$total_tax                                        = $formatted_cart['amounts']['tax'] + $shipping_tax;
				$formatted_cart['amounts']['total']               = $formatted_cart['amounts']['subtotal'] + $formatted_cart['amounts']['shipping_fee'] + $formatted_cart['amounts']['tax'] + $shipping_tax - $formatted_cart['amounts']['discount_amount'];
				$formatted_cart['amounts']['discount_amount']     = round( $formatted_cart['amounts']['discount_amount'], 2 );
				$formatted_cart['fragments']['subtotal']          = easycommerce_price( $formatted_cart['amounts']['subtotal'] ?? 0 );
				$formatted_cart['fragments']['tax']               = easycommerce_price( $total_tax );
				$formatted_cart['fragments']['product_tax']       = easycommerce_price( $formatted_cart['amounts']['tax'] );
				$formatted_cart['fragments']['shipping_tax']      = easycommerce_price( $shipping_tax );
				$formatted_cart['fragments']['total']             = easycommerce_price( $formatted_cart['amounts']['total'] );
				$formatted_cart['fragments']['payment_methods']   = '';
				$formatted_cart['fragments']['physical_subtotal'] = $physical_subtotal;

				ob_start();

				do_action( 'easycommerce/views/templates/checkout/items', $formatted_cart );

				$formatted_cart['fragments']['items'] = ob_get_clean();

				ob_start();

				do_action( 'easycommerce/views/templates/checkout/summary', $formatted_cart, $this );

				$formatted_cart['fragments']['summary'] = ob_get_clean();

				if( $formatted_cart['amounts']['total'] > 0 ) {
					ob_start();

					do_action( 'easycommerce/views/templates/checkout/payment_methods', $this );

					$formatted_cart['fragments']['payment_methods'] = ob_get_clean();
				}
			}
			$total           = $formatted_cart['amounts']['total'] ?? 0;
			$formatted_total = strpos( $total, '.' ) === false ? $total . '.00' : $total;
			$old_total       = $this->get_total();

			if( $formatted_total > 0 &&  $old_total > 0 && $old_total != $formatted_total ) {
				$formatted_cart['payment_update_required'] = true;
			}

			return $formatted_cart;
		}

		return $this->cart;
	}
	/**
	 * Check if the shipping address is the same as the billing address.
	 *
	 * @return bool
	 */
	public function is_shipping_same_as_billing() {
		$billing_address   = $this->get_billing_address();
		$billing_country   = $billing_address['country'] ?? null;
		$shipping_address  = $this->get_shipping_address();
		$shipping_country  = $shipping_address['country'] ?? null;

		return $billing_country === $shipping_country;
	}

	/**
	 * Get the payment intent ID for a specific payment method.
	 *
	 * @param string $method The payment method.
	 * @return string|null The payment intent ID or null if not found.
	 */
	public function get_payment_intent( $method ) {
		return $this->cart['data']['payment_intents'][ $method ] ?? null;
	}

	public function get_payment_method() {
		return $this->cart['data']['payment_method'] ?? null;
	}

	/**
	 * Set the payment intent ID for a specific payment method.
	 *
	 * @param string $method The payment method.
	 * @param string $payment_intent_id The payment intent ID.
	 */
	public function set_payment_intent( $method, $payment_intent_id ) {
		$this->cart['data']['payment_intents'][ $method ] = $payment_intent_id;
		$this->save();
	}

	public function clear_payment_intent( $method ) {
		if ( isset( $this->cart['data']['payment_intents'][ $method ] ) ) {
			unset( $this->cart['data']['payment_intents'][ $method ] );
			$this->cart['data']['items'] = array(); 
			
			$this->save();
		}
	}

	/**
	 * Get the customer name from the cart.
	 *
	 * @return string The customer name.
	 */
	public function get_customer_name() {
		if ( ! empty( $billing = $this->get_billing_address() ) && ! empty( $billing['first_name'] ) ) {
			return $billing['first_name'];
		}
		elseif ( ! empty( $shipping = $this->get_shipping_address() ) && ! empty( $shipping['first_name'] ) ) {
			return $shipping['first_name'];
		}
		elseif( $customer = $this->get_customer() ) {
			return $customer->get_first_name();
		}

		return '';
	}

	/**
	 * Get the customer email from the cart.
	 *
	 * @return string The customer email.
	 */
	public function get_customer_email() {
		if ( ! empty( $billing = $this->get_billing_address() ) && ! empty( $billing['email'] ) ) {
			return $billing['email'];
		}
		elseif ( ! empty( $shipping = $this->get_shipping_address() ) && ! empty( $shipping['email'] ) ) {
			return $shipping['email'];
		}
		elseif( $customer = $this->get_customer() ) {
			return $customer->get_email();
		}

		return '';
	}

	/**
	 * Get the amounts from the cart.
	 *
	 * @return array The amounts from the cart.
	 */
	public function get_amounts() {
		if ( null === $this->formatted_cart ) {
			$this->formatted_cart = $this->get( true );
		}

		return $this->formatted_cart['amounts'] ?? array();
	}

	/**
	 * Get the amount from the cart.
	 *
	 * @param string $segment The segment to get the amount from.
	 * @return mixed The amount from the cart.
	 */
	public function get_amount( $segment = 'total' ) {
		$amounts = $this->get_amounts();
		return $amounts[ $segment ] ?? false;
	}

	/**
	 * Get total amount from the cart.
	 *
	 * @return float The total amount from the cart.
	 */
	public function get_total() {
		return $this->cart['total'] ?? 0;
	}

	/**
	 * Get the quantity from the cart.
	 *
	 * @return int The quantity from the cart.
	 */
	public function get_quantity() {
		$quantity = 0;

		foreach ( $this->get_items() as $product_id => $variations ) {
			foreach ( $variations as $variation_id => $config ) {
				$quantity += $config['quantity'];
			}
		}

		return $quantity;
	}

	/**
	 * Get the weight from the cart.
	 *
	 * @return float The weight from the cart.
	 */
	public function get_weight() {
		$product_variation_model = new Product_Variation();
		$weight 				 = 0;

		foreach ( $this->get_items() as $product_id => $variations ) {
			foreach ( $variations as $price_id => $config ) {
				$product_variation = $product_variation_model->get_by_price( $price_id, $product_id );
				if ( $product_variation ) {
					$weight += $product_variation->get_weight( true ) * $config['quantity'];
				}
			}
		}

		return $weight; // Total weight in kilograms
	}

	/**
	 * Adds product data to the cart, now storing more detailed information.
	 *
	 * @param int       $product_id The ID of the product.
	 * @param int|array $price_id The price_id or an associative array of attributes.
	 * @param int       $quantity The quantity to add.
	 * @param int       $user_id The user ID.
	 * @param bool      $is_free Whether this is a free product from coupon.
	 */
	public function add( $product_id, $price_id = 1, $quantity = 1, $user_id = null, $is_free = false ) {

		if ( ! is_null( $user_id ) ) {
			$this->user_id = $user_id;
		}

		$product_variation_model = new Product_Variation();

		if ( is_integer( $price_id ) ) {
			$variation = $product_variation_model->get_by_price( $price_id, $product_id );
		} elseif ( is_array( $price_id ) ) {
			$variation = $product_variation_model->get_by_attributes( $price_id );
		}

		if ( ! $variation ) {
			return;
		}

		if ( get_post_status( $product_id ) !== 'publish' ) {
			return;
		}

		$type          = $variation->get_type();
		$stock         = $variation->get_stock();
		$manages_stock = $variation->manages_stock();

		if ( $manages_stock && $stock == 0 ) {
			return;
		}

		// If it's already in the cart, increase the quantity
		if ( isset( $this->cart['data']['items'][ $product_id ][ $price_id ] ) ) {
			$quantity += $this->cart['data']['items'][ $product_id ][ $price_id ]['quantity'];
		}

		// If it's a digital variation, limit the quantity to 1
		if ( $type === 'digital' && $quantity > 1 ) {
			$quantity = 1;
		}

		$price = $variation->get_price( false );
		$this->cart['data']['items'][ $product_id ][ $price_id ] = array(
			'quantity' => $quantity,
			'rate'     => $price,
			'price'    => $quantity * $price,
			'is_free'  => $is_free,
		);

		$this->save();
	}

	/**
	 * Updates a product quantity in the cart.
	 *
	 * @param int $product_id The ID of the product.
	 * @param int $quantity The new quantity to set.
	 */
	public function update_qty( $product_id, $price_id = 0, $quantity = 1 ) {
		$product_variation_model = new Product_Variation();

		if ( is_integer( $price_id ) ) {
			$variation = $product_variation_model->get_by_price( $price_id, $product_id );
		} elseif ( is_array( $price_id ) ) {
			$variation = $product_variation_model->get_by_attributes( $price_id );
		}

		$type = $variation->get_type();

		// If it's a digital variation, limit the quantity to 1
		if ( $type === 'digital' && $quantity > 1 ) {
			$quantity = 1;
		}

		if ( isset( $this->cart['data']['items'][ $product_id ][ $price_id ] ) && $variation ) {

			$price = $variation->get_price( false );

			$this->cart['data']['items'][ $product_id ][ $price_id ] = array(
				'quantity' => $quantity,
				'rate'     => $price,
				'price'    => $quantity * $price,
			);

			$this->save();
		}
	}

	/**
	 * Checks if the cart contains a given product_id and price_id
	 *
	 * @return bool
	 */
	public function has_product( $product_id, $price_id = 1 ) {
		$items = $this->get_items();

		if ( isset( $items[ $product_id ] ) && isset( $items[ $product_id ][ $price_id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the current status of the cart.
	 *
	 * @return string|null The cart status if found, null if the cart doesn't exist.
	 */
	public function get_status() {
		return $this->cart['status'] ?? null;
	}

	/**
	 * Check whether the cart is locked for payment processing.
	 *
	 * @return bool
	 */
	public function is_locked() {
		if ( 'payment_initiated' !== ( $this->cart['status'] ?? '' ) ) {
			return false;
		}

		/**
		 * Filters how long (in seconds) a cart stays locked for payment before
		 * the lock is considered stale and auto-released.
		 *
		 * Guards against a cart being stuck in `payment_initiated` forever when a
		 * payment is abandoned mid-flow (redirect gateway, browser closed, etc.).
		 *
		 * @param int  $timeout Lock lifetime in seconds. Default 900 (15 minutes).
		 * @param Cart $cart    The cart instance.
		 */
		$timeout = (int) apply_filters( 'easycommerce_cart_lock_timeout', 15 * MINUTE_IN_SECONDS, $this );

		// Both timestamps are parsed from `current_time( 'mysql' )` strings, so any
		// site/UTC offset cancels out in the subtraction.
		$locked_at = ! empty( $this->cart['updated_at'] ) ? strtotime( $this->cart['updated_at'] ) : 0;
		$now       = strtotime( current_time( 'mysql' ) );

		// Stale lock: auto-release so the customer can shop/retry.
		if ( $locked_at && ( $now - $locked_at ) > $timeout ) {
			$this->set_status( 'pending' );
			unset( $this->cart['data']['lock_amount'] );
			$this->save();
			return false;
		}

		return true;
	}

	/**
	 * Get the cart total snapshotted at payment initiation time.
	 *
	 * @return float|null Null if no snapshot exists.
	 */
	public function get_lock_amount() {
		return isset( $this->cart['data']['lock_amount'] )
			? (float) $this->cart['data']['lock_amount']
			: null;
	}

	/**
	 * Lock the cart for payment and snapshot the current total in one DB write.
	 *
	 * @return float The snapshotted amount.
	 */
	public function lock_for_payment(): float {
		$amount                            = (float) ( $this->get_amount() ?: 0 );
		$this->cart['data']['lock_amount'] = $amount;
		$this->cart['status']              = 'payment_initiated';
		$this->cart['updated_at']          = current_time( 'mysql' );
		$this->save();
		return $amount;
	}

	/**
	 * Set the status of the cart.
	 *
	 * @param string $status The new status for the cart.
	 * @return bool True if status is updated, False if failure.
	 */
	public function set_status( $status ) {

		$valid_statuses = array( 'pending', 'abandoned', 'cancelled', 'completed', 'payment_initiated' );

		// Validate status
		if ( ! in_array( $status, $valid_statuses, true ) ) {
			return false;
		}

		// Update the status in the cart data
		$this->cart['status']     = $status;
		$this->cart['updated_at'] = current_time( 'mysql' );

		// Save the changes to the database
		$this->save();

		return true;
	}

	/**
	 * Check if the cart contains at least 1 item of the given type.
	 *
	 * @param string $type The type of item to check for (e.g., 'physical').
	 * @return bool True if the cart contains an item of the given type, false otherwise.
	 */
	public function has_item_type( $type = 'physical' ) {
		$product_variation_model = new Product_Variation();

		foreach ( $this->get_items() as $product_id => $items ) {
			foreach ( $items as $price_id => $config ) {
				$product_variation = $product_variation_model->get_by_price( $price_id, $product_id );

				if ( $product_variation && $product_variation->get_type() === $type ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Removes a product from the cart.
	 *
	 * @param int $product_id The ID of the product to remove.
	 * @param int $price_id The price ID. Default 1.
	 * @param bool $is_free Whether this is a free product removal.
	 */
	public function remove( $product_id, $price_id = 1, $is_free = false ) {
		if ( isset( $this->cart['data']['items'][ $product_id ][ $price_id ] ) ) {

			unset( $this->cart['data']['items'][ $product_id ][ $price_id ] );

			// If no variations are left, remove the product entirely
			if ( empty( $this->cart['data']['items'][ $product_id ] ) ) {
				unset( $this->cart['data']['items'][ $product_id ] );
			}

			if ( empty( $this->cart['data']['items'] ) ) {
				$this->cart['data']['coupons'] = array();
			}

			$this->save();
		}
	}

	public function add_coupon( $code = null ) {

		if( is_null( $code ) ) return;

		foreach ( explode( ',', $code ) as $code ) {

			$coupon = new Coupon( trim( $code ) );

			if ( $coupon->exists() && $coupon->is_applicable( $this ) ) {

				if ( ! isset( $this->cart['data']['coupons'] ) ) {
					$this->cart['data']['coupons'] = array();
				}

				$this->cart['data']['coupons'][] = $coupon->get_code();

				/**
				 * Fires after a coupon is applied to the cart.
				 *
				 * @param string $coupon The coupon instance.
				 */
				do_action( 'easycommerce_apply_coupon', $coupon );
			}
		}

		// remove duplicate coupons

		if( isset( $this->cart['data']['coupons'] ) ) {
			$this->cart['data']['coupons'] = array_unique( $this->cart['data']['coupons'] );
		}

		$this->save();
	}

	/**
	 * Adds fee to the cart
	 * 
	 * @param array $fee An associative array of `name => amount` pair
	 * 
	 * @since 0.9.20
	 */
	public function add_fee( $fee = [] ) {
	    if ( ! is_array( $fee ) || empty( $fee ) ) {
	        return;
	    }

	    $name = $this->sanitize( array_key_first( $fee ), 'key' );
	    $amount = $this->sanitize( reset( $fee ) );

	    if ( ! isset( $this->cart['data']['fees'] ) ) {
	        $this->cart['data']['fees'] = [];
	    }

	    $this->cart['data']['fees'][ $name ] = $amount;

	    $this->save();
	}

	/**
	 * Empties the cart.
	 */
	public function empty() {
		$this->cart['data']       = array();
		$this->cart['status']     = 'pending';
		$this->cart['reminders']  = 0;
		$this->cart['updated_at'] = current_time( 'mysql' );

		$this->save();
	}

	/**
	 * Remove reference from the user meta or session.
	 */
	public function remove_flag() {

		// Remove the cart hash from user meta if a user ID is set.
		if ( isset( $this->user_id ) ) {
			delete_user_meta( $this->user_id, '_easycommerce_cart_hash' );
		}

		// Remove the cart hash from logged-in user's meta.
		if ( is_user_logged_in() ) {
			delete_user_meta( get_current_user_id(), '_easycommerce_cart_hash' );
		}

		// Remove the cart hash from cookies.
		if ( isset( $_COOKIE['easycommerce_cart_hash'] ) ) {
			if ( ! session_id() ) {
				session_start();
			}
			setcookie( 'easycommerce_cart_hash', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	/**
	 * Reset the cart hash and store the new hash in the database.
	 *
	 * @return string The new cart hash.
	 */
	public function reset() {
		$this->remove_flag();

		// Generate a new hash for the cart.
		$this->hash = Utility::generate_hash();

		// Reset the cart data.
		$this->cart = array(
			'data'       => array(),
			'total'      => 0,
			'status'     => 'pending',
			'reminders'  => 0,
			'updated_at' => current_time( 'mysql' ),
			'created_at' => current_time( 'mysql' ),
		);

		$this->save();

		return $this->hash;
	}

	/**
	 * Completely erase the cart session.
	 *
	 * @return bool True if the cart session is successfully deleted, false otherwise.
	 */
	public function delete() {
		$this->cart = array(
			'data'       => array(),
			'total'      => 0,
			'status'     => 'pending',
			'reminders'  => 0,
			'updated_at' => null,
			'created_at' => null,
		);

		return $this->db->get_instance()->delete( $this->db->get_table(), array( 'hash' => $this->hash ) );
	}

	/**
	 * Get the billing address from the cart.
	 *
	 * @return array The billing address from the cart.
	 */
	public function get_billing_address() {
		return $this->cart['data']['address']['billing'] ?? false;
	}

	/**
	 * Get the shipping address from the cart.
	 *
	 * @return array The shipping address from the cart.
	 */
	public function get_shipping_address() {
		return $this->cart['data']['address']['shipping'] ?? false;
	}

	/**
	 * Retrieve a specific field from the billing or shipping address.
	 *
	 * @param string $field The field name to retrieve.
	 * @param string $type  The address type ('billing' or 'shipping'). Default is 'billing'.
	 * @return string The value of the requested field or an empty string if not set.
	 */
	public function get_address_field( $field, $type = 'billing' ) {
		$address_type = ( $type === 'billing' ) ? 'billing' : 'shipping';
		$address      = isset( $this->cart['data']['address'][ $address_type ] ) ? $this->cart['data']['address'][ $address_type ] : '';

		return isset( $address[ $field ] ) ? $address[ $field ] : '';
	}

	public function get_phone( $type = 'billing' ) {
		return $this->get_address_field( 'phone', $type );
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
	 * Get abandoned carts
	 *
	 * @param int $period The time delay to consider as abandoned, in minutes. Default is 30 minutes.
	 * @return array Array of abandoned carts.
	 */
	public function get_abandoned( $period, $range = null, $from = null, $to = null ) {

		$threshold_time = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) . " - $period minutes" ) );

		$conditions = [
			['updated_at' => ['<', $threshold_time]],
			['status' => 'pending'],
		];

		if ( $range ) {
			$dates = easycommerce_get_range_dates( $range, $from, $to );
			if ( $dates ) {
				$conditions[] = ['created_at' => ['>=', $dates['from']]];
				$conditions[] = ['created_at' => ['<=', $dates['to']]];
			}
		}

		$abandoned_carts = $this->db->get_rows( $conditions );

		return array_map(
			function ( $cart ) {

				foreach ( array( 'payment_attempts', 'customer_name', 'customer_email', 'status', 'reminders_sent', 'user_id', 'created_at' ) as $key ) {
					if ( isset( $cart->$key ) ) {
						unset( $cart->$key );
					}
				}

				$cart->data = maybe_unserialize( $cart->data );

				return $cart;
			},
			$abandoned_carts
		);
	}

	/**
	 * Retrieves the current cart hash.
	 *
	 * @return string The cart hash.
	 */
	public function get_hash() {
		return $this->hash;
	}

	/**
	 * Retrieves the current cart link.
	 *
	 * @return string The cart link.
	 */
	public function get_link() {
		return add_query_arg( 'hash', $this->hash, easycommerce_checkout_page( true ) );
	}

	/**
	 * Pre-fill cart data (items + addresses) from external source such as the AI agent.
	 * Computes and stores the total from item prices so the cart is ready for checkout.
	 *
	 * @param array  $items        Items keyed as [ product_id => [ price_id => [ quantity, rate, price ] ] ].
	 * @param array  $billing      Billing address fields.
	 * @param array  $shipping     Shipping address fields.
	 * @param string $coupon_code  Optional coupon code.
	 */
	public function prefill( array $items, array $billing, array $shipping, string $coupon_code = '' ): void {
		$this->cart['data']['items']               = $items;
		$this->cart['data']['address']['billing']  = $billing;
		$this->cart['data']['address']['shipping'] = $shipping;

		if ( ! empty( $coupon_code ) ) {
			$this->cart['data']['coupons'] = array( $coupon_code );
		}

		$subtotal = 0.0;
		foreach ( $items as $prices ) {
			foreach ( $prices as $config ) {
				$subtotal += (float) ( $config['price'] ?? 0 );
			}
		}
		$this->cart['data']['amounts']['subtotal'] = $subtotal;
		$this->cart['data']['amounts']['total']    = $subtotal;

		$this->save();
	}

	/**
	 * Handle free products discount (BXGY functionality)
	 *
	 * @param Coupon $coupon The coupon object
	 * @param array $cart_items All cart items
	 * @param array $applicable_items Applicable item indices
	 * @return float The discount amount
	 */
	private function handle_free_products( $coupon, $cart_items, $applicable_items ) {
		$free_products = maybe_unserialize( $coupon->get_offer() );

		if ( empty( $free_products ) || ! is_array( $free_products ) ) {
			return 0;
		}

		$total_discount = 0;

		// Get free product IDs from coupon offer
		$free_product_ids = wp_list_pluck( $free_products, 'id' );

		foreach ( $applicable_items as $index ) {
			$item = $cart_items[ $index ];
			$product_id = $item['product_id'];

			// If this cart item is one of the free products
			if ( in_array( $product_id, $free_product_ids ) ) {
				// Give it for free (100% discount)
				$total_discount += $item['total'];
			}
		}

		return $total_discount;
	}
}

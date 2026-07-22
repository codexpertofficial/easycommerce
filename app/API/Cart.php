<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Shipping_Plan;
use EasyCommerce\Models\Coupon;
use EasyCommerce\Models\Cart as Cart_Model;

class Cart extends API {

	/**
	 * List the cart contents and hash.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function list( $request ) {
		$cart = new Cart_Model();

		$cart_data = array(
			'cart' => $cart->get( true ),
			'hash' => $cart->get_hash(),
		);

		/**
		 * Filter the cart data before sending the response.
		 *
		 * @since 1.9
		 * @param array $cart_data The cart data including contents and hash.
		 * @param WP_REST_Request $request The request object.
		 */
		$cart_data = apply_filters( 'easycommerce_list_cart_data', $cart_data, $request );

		/**
		 * Fires after retrieving the cart contents.
		 *
		 * @since 1.9
		 * @param array $cart_data The cart data including contents and hash.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_list_cart', $cart_data, $request );

		$this->response_success( $cart_data );
	}

	/**
	 * Add items to the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function add_items( $request ) {
		$cart = new Cart_Model();

		if ( $cart->is_locked() ) {
			$this->response_error( __( 'Cart is locked for payment. Complete or cancel the current payment before making changes.', 'easycommerce' ), 403 );
		}

		$products = $request->get_param( 'products' );
		$user_id  = $request->get_param( 'user_id' );

		if ( ! is_array( $products ) || empty( $products ) ) {
			$this->response_error( __( 'Invalid product data.', 'easycommerce' ) );
		}

		$cart_items = $cart->get_items();

		foreach ( $products as $product ) {

			$product_id = isset( $product['id'] ) ? absint( $product['id'] ) : 0;
			$price_id   = isset( $product['price_id'] ) ? absint( $product['price_id'] ) : 1;
			$quantity   = isset( $product['quantity'] ) ? absint( $product['quantity'] ) : 1;

			$product_obj = new Product( $product_id );

			if ( ! $product_obj->exists() ) {
				/* Translators: %d is the product ID. */
				$this->response_error( sprintf( __( 'Invalid product ID: %d', 'easycommerce' ), $product_id ) );
			}

			// Check stock.
			$has_stock         = true;
			$existing_quantity = $cart_items[ $product_id ][ $price_id ]['quantity'] ?? 0;

			foreach ( $product_obj->get_variations() as $variation ) {
				if ( $variation->get_price_id() == $price_id && $variation->manages_stock() && ! is_null( $stock = $variation->get_stock() ) && ( $quantity + $existing_quantity ) > $stock ) {
					$has_stock = false;
				}
			}

			if ( $has_stock ) {
				$cart->add( $product_id, $price_id, $quantity, $user_id );
			} else {
				$this->response_error( __( 'Product is out of stock!', 'easycommerce' ) );
			}
		}

		/**
		 * Fires after products are added to the cart.
		 *
		 * @since 1.9
		 * @param array $products The products added to the cart.
		 */
		do_action( 'easycommerce_add_to_cart', $products );

		$response_data = array(
			'message'  => __( 'Products added to cart.', 'easycommerce' ),
			'hash'     => $cart->get_hash(),
			'cart'     => $cart->get( true ),
			'redirect' => get_permalink( easycommerce_cart_redirect() ),
		);

		// translators: %d: number of items added to the cart.
		do_action( 'easycommerce_log', array( 'object' => 'cart', 'action' => 'add', 'meta' => array( 'hash' => $cart->get_hash(), 'item' => $cart->get_items() ), 'note' => sprintf( _n( '%d item added to cart', '%d items added to cart', count( $products ), 'easycommerce' ), count( $products ) ) ) );
		/**
		 * Filter the response data before sending.
		 *
		 * @since 1.9
		 * @param array $response_data The response data including cart and hash.
		 * @param array $products The products added to the cart.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_add_to_cart_response', $response_data, $products, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Update a product quantity in the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function update_item( $request ) {
		$cart = new Cart_Model();

		if ( $cart->is_locked() ) {
			$this->response_error( __( 'Cart is locked for payment. Complete or cancel the current payment before making changes.', 'easycommerce' ), 403 );
		}

		$product_id = absint( $request->get_param( 'id' ) );
		$price_id   = absint( $request->get_param( 'price_id' ) );
		$quantity   = absint( $request->get_param( 'quantity' ) );

		if ( $product_id <= 0 || $quantity < 0 ) {
			$this->response_error( __( 'Invalid product ID or quantity.', 'easycommerce' ) );
		}

		$product_obj = new Product( $product_id );

		if ( ! $product_obj->is_sellable() ) {
			$this->response_success( array( 'message' => __( 'Product not found.', 'easycommerce' ) ) );
		}

		$cart->update_qty( $product_id, $price_id, $quantity );

		/**
		 * Fires after a product quantity is updated in the cart.
		 *
		 * @param int $product_id The product ID.
		 * @param int $price_id The price ID.
		 * @param int $quantity The new quantity.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_update_cart', $product_id, $price_id, $quantity, $request );

		$response_data = array(
			'message' => __( 'Cart updated.', 'easycommerce' ),
			'hash'    => $cart->get_hash(),
			'cart'    => $cart->get( true ),
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including cart and hash.
		 * @param int $product_id The product ID.
		 * @param int $price_id The price ID.
		 * @param int $quantity The updated quantity.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_update_cart_response', $response_data, $product_id, $price_id, $quantity, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Remove a product from the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function remove_item( $request ) {
		$cart = new Cart_Model();

		if ( $cart->is_locked() ) {
			$this->response_error( __( 'Cart is locked for payment. Complete or cancel the current payment before making changes.', 'easycommerce' ), 403 );
		}

		$product_id = absint( $request->get_param( 'id' ) );
		$price_id   = absint( $request->get_param( 'price_id' ) );

		if ( $product_id <= 0 ) {
			$this->response_error( __( 'Invalid product ID.', 'easycommerce' ) );
		}

		// Remove associated free products if any.
		$coupons = $cart->cart['data']['coupons'] ?? array();
		foreach ( $coupons as $code ) {
			$coupon = new Coupon( $code );
			if ( $coupon->get_type() === 'products' ) {
			if ( in_array( $product_id, $coupon->get_products() ) ) {
				$free_products = maybe_unserialize( $coupon->get_offer() );
				foreach ( $free_products as $free_product ) {
					$cart->remove( $free_product['id'], 1, true );
				}
			}
			}
		}

		$cart->remove( $product_id, $price_id );

		/**
		 * Fires after a product is removed from the cart.
		 *
		 * @param int $product_id The product ID.
		 * @param int $price_id The price ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_remove_from_cart', $product_id, $price_id, $request );

		$response_data = array(
			'message' => __( 'Product removed from cart.', 'easycommerce' ),
			'hash'    => $cart->get_hash(),
			'cart'    => $cart->get( true ),
			'reload'  => true, // @todo check
		);

		/**
		 * Check if the cart is empty after removing an item.
		 *
		 * @var array $items The current cart items.
		 */
		$items      = $cart->get_items();
		$empty_cart = empty( $items ) || empty( array_pop( $items ) );

		if ( $empty_cart ) {
			do_action( 'easycommerce_cart_emptied' );
		}

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including cart and hash.
		 * @param int $product_id The product ID.
		 * @param int $price_id The price ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_remove_from_cart_response', $response_data, $product_id, $price_id, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Lock the cart for payment processing.
	 * Once locked, add/update/remove operations are rejected until the payment completes or fails.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function lock( $request ) {
		$cart = new Cart_Model();

		if ( $cart->is_locked() ) {
			$this->response_success( array(
				'message' => __( 'Cart already locked.', 'easycommerce' ),
				'locked'  => true,
				'total'   => $cart->get_lock_amount(),
			) );
		}

		$intended_amount = $cart->lock_for_payment();
		$this->response_success( array(
			'message' => __( 'Cart locked for payment.', 'easycommerce' ),
			'locked'  => true,
			'total'   => $intended_amount,
		) );
	}

	/**
	 * Get the shipping options for the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_shipping_options( $request ) {
		$hash             = $request->get_param( 'hash' );
		$cart             = new Cart_Model( $hash );
		$shipping_address = $request->get_param( 'shipping_address' );
		$billing_address  = $request->get_param( 'billing_address' );

		// remove previous selected shipping method
		$cart->cart['data']['shipping_method'] = null;
		if ( ! is_null( $billing_address ) ) {
			$billing_address                          = $this->sanitize_address( $billing_address );
			$cart->cart['data']['address']['billing'] = $billing_address;
			$cart->save();

			if ( is_user_logged_in() ) {
				$customer = new Customer( get_current_user_id() );
				$customer->update_meta( 'billing_address', wp_parse_args( $billing_address, $customer->get_meta( 'billing_address' ) ) );
			}
		}

		$methods = apply_filters( 'easycommerce_shipping_methods', array(), $cart, $shipping_address, $billing_address, $request );

		if ( ! is_null( $shipping_address ) ) {
			$shipping_address                          = $this->sanitize_address( $shipping_address );
			$cart->cart['data']['address']['shipping'] = $shipping_address;

			if ( is_user_logged_in() ) {
				$customer = new Customer( get_current_user_id() );
				$customer->update_meta( 'shipping_address', wp_parse_args( $shipping_address, $customer->get_meta( 'shipping_address' ) ) );
			}

			$plans = Shipping_Plan::get_by_location_address( 
				$shipping_address['country'] ?? '', 
				$shipping_address['state'] ?? '', 
				$shipping_address['city'] ?? '', 
				$shipping_address['postcode'] ?? ''
			);

			if ( empty( $plans ) ) {
				$this->cart['data']['shipping_methods'] = array();
			}

			foreach ( $plans as $plan ) {

				if ( $plan['active'] != 1 ) {
					continue;
				}

				$base = $plan['calculation_base'];
				if ( $base == 'price' ) {
					$cart_total = $cart->get_amount();

					$formatted_cart = $cart->get(true);
					$physical_subtotal = 0;

					if ( isset( $formatted_cart['fragments']['physical_subtotal'] ) ) {
						$physical_subtotal = $formatted_cart['fragments']['physical_subtotal'];
					}
					foreach ( $plan['methods'] as $method ) {
						$min = $method->min;
						$max = $method->max !== null ? $method->max : PHP_INT_MAX;

						if ( $min <= $physical_subtotal && $max >= $physical_subtotal ) {
							$methods[] = array(
								'id'   => $method->id,
								'name' => $method->name,
								'cost' => $method->cost,
							);
						}
					}
				} elseif ( $base == 'weight' ) {
					$unit_conversions  = easycommerce_weight_unit_conversion( 'g' );

					$cart_weight 	   = $cart->get_weight(); // Total cart weight in kilograms
					$cart_weight_grams = $cart_weight * $unit_conversions['kg']; // Convert cart weight to grams

					foreach ( $plan['methods'] as $method ) {
						// Convert min and max to grams based on their units
						$min_grams = $method->min * ( $unit_conversions[$method->min_unit] ?? 1 );
						$max_grams = $method->max !== null ? $method->max * ( $unit_conversions[$method->max_unit] ?? 1 ) : PHP_INT_MAX;

						// Compare cart weight (in grams) with method range (in grams)
						if ( $min_grams <= $cart_weight_grams && $cart_weight_grams <= $max_grams ) {
							$methods[] = [
								'id'   => $method->id,
								'name' => $method->name,
								'cost' => $method->cost,
							];
						}
					}
				} elseif ( $base == 'quantity' ) {
					$cart_quantity = $cart->get_quantity();
					foreach ( $plan['methods'] as $method ) {

						$min = $method->min;
						$max = $method->max !== null ? $method->max : PHP_INT_MAX;

						if ( $min <= $cart_quantity && $max >= $cart_quantity ) {
							$methods[] = array(
								'id'   => $method->id,
								'name' => $method->name,
								'cost' => $method->cost,
							);
						}
					}
				}
			}
			usort( $methods, fn( $a, $b ) => $a['cost'] <=> $b['cost'] );
			$cart->cart['data']['shipping_methods'] = $methods;
			$cart->cart['data']['shipping_method'] = $methods[0]['id'] ?? null;

			$cart->save();
		}

		$response_data = array(
			'message' => ! empty( $methods ) ? __( 'Shipping methods found', 'easycommerce' ) : __( 'No shipping methods found', 'easycommerce' ),
			'methods' => ! empty( $methods ) ? $methods : false,
			'cart'    => $cart->get( true ),
		);		

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including shipping methods.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_shipping_options_response', $response_data, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Set the shipping method for the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function set_shipping_method( $request ) {
		$cart   = new Cart_Model();
		$id     = $request->get_param( 'id' );
		$method = Shipping_Plan::get_method_by_id( $id );

		if ( ! $method ) {
			$this->response_error( __( 'Shipping method not found.', 'easycommerce' ), 400 );
		}

		$cart->cart['data']['shipping_method'] = $id;

		$cart->save();

		/**
		 * Fires after a shipping method is set in the cart.
		 *
		 * @param int $id The shipping method ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_set_shipping_method', $id, $request );

		$response_data = array(
			'message' => __( 'Shipping method applied', 'easycommerce' ),
			'cart'    => $cart->get( true ),
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including cart details.
		 * @param int $id The shipping method ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_set_shipping_method_response', $response_data, $id, $request );

		$this->response_success( $response_data );
	}

	public function set_payment_method( $request ) {
		$cart            = new Cart_Model();
		$payment_method  = $request->get_param( 'payment_method' );

		$cart->cart['data']['payment_method'] = $payment_method;

		$cart->save();

		/**
		 * Fires after a payment method is set in the cart.
		 *
		 * @param int $payment_method The payment method ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_set_payment_method', $payment_method, $request );

		$response_data = array(
			'message' => __( 'Payment method applied', 'easycommerce' ),
			'cart'    => $cart->get( true ),
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including cart details.
		 * @param int $payment_method The payment method ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_set_payment_method_response', $response_data, $payment_method, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Apply a coupon to the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function apply_coupon( $request ) {
		$hash = $request->get_param( 'hash' );
		$cart = new Cart_Model( $hash );
		$code = $request->get_param( 'code' );

		$coupon = new Coupon( $code );

		if ( ! $coupon->exists() || ! $coupon->is_active() ) {
			$this->response_error(
				array(
					'message' => __( 'Invalid coupon code.', 'easycommerce' ),
				)
			);
		}

		if ( ! isset( $cart->cart['data']['coupons'] ) ) {
			$cart->cart['data']['coupons'] = array();
		}

		if ( ! $coupon->is_applicable() ) {
			$this->response_error(
				array(
					'message' => __( 'Coupon is not applicable.', 'easycommerce' ),
				)
			);
		}

		if ( ! in_array( $code, $cart->cart['data']['coupons'] ) ) {
			$cart->cart['data']['coupons'][] = $coupon->get_code();

			// Add free products if Buy X Get Y offer.
			if ( $coupon->get_type() === 'products' ) {
				$free_products = maybe_unserialize( $coupon->get_offer() );
				foreach ( $free_products as $free_product ) {
					$cart->add( $free_product['id'], 1, 1, null, true );
				}
			}

			/**
			 * Fires after a coupon is applied to the cart.
			 *
			 * @param string $coupon The coupon instance.
			 */
			do_action( 'easycommerce_apply_coupon', $coupon );
		}

		$cart->save();

		// Trigger cart updated event
		do_action( 'easycommerce_cart_updated' );

		$response_data = array(
			'message' => __( 'Coupon applied.', 'easycommerce' ),
			'cart'    => $cart->get( true ),
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including cart details.
		 * @param string $code The coupon code.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_apply_coupon_response', $response_data, $code, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Remove a coupon from the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function remove_coupon( $request ) {
		$hash = $request->get_param( 'hash' );
		$cart = new Cart_Model( $hash );
		$code = $request->get_param( 'code' );

		if ( empty( $cart->cart['data']['coupons'] ) || ! in_array( $code, $cart->cart['data']['coupons'] ) ) {
			$this->response_error(
				array(
					'message' => __( 'Cart doesn\'t contain this coupon!', 'easycommerce' ),
				)
			);
		}

		$cart->cart['data']['coupons'] = array_values( array_filter( $cart->cart['data']['coupons'], fn( $value ) => $value !== $code ) );

		// Remove free products if Buy X Get Y offer.
		$coupon = new Coupon( $code );
		if ( $coupon->get_type() === 'products' ) {
			$free_products = maybe_unserialize( $coupon->get_offer() );
			foreach ( $free_products as $free_product ) {
				$cart->remove( $free_product['id'], 1, true );
			}
		}

		$cart->save();

		/**
		 * Fires after a coupon is removed from the cart.
		 *
		 * @param string $code The coupon code.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_remove_coupon', $code, $request );

		$response_data = array(
			'message' => __( 'Coupon removed.', 'easycommerce' ),
			'cart'    => $cart->get( true ),
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including cart details.
		 * @param string $code The coupon code.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_remove_coupon_response', $response_data, $code, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Clear the cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function clear( $request ) {
		$cart = new Cart_Model();

		$cart->empty();

		/**
		 * Fires after the cart is cleared.
		 *
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_clear_cart', $request );

		/**
		 * Logs the cart clear event.
		 */
		do_action( 'easycommerce_log', array( 'object' => 'cart', 'action' => 'clear', 'object_id' => $cart->get_hash(), 'note' => __( 'Cart cleared', 'easycommerce' ) ) );

		$response_data = array(
			'message' => __( 'Cart cleared.', 'easycommerce' ),
			'hash'    => $cart->get_hash(),
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including cart hash.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_clear_cart_response', $response_data, $request );

		$this->response_success( $response_data );
	}

	/**
	 * Remind abandoned cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function remind_abandoned( $request ) {
		$hash = $request->get_param( 'hash' );
		$cart = new Cart_Model( $hash );

		/**
		 * Fires before sending an abandoned cart reminder.
		 *
		 * @param string $hash The cart hash.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_send_abandoned_reminder', $hash, $request );

		do_action( 'easycommerce_send_abandoned_reminder', $hash );

		$mail_sent = apply_filters( 'easycommerce_mail_sent', false, $hash, $request );

		if ( $mail_sent ) {
			// Update reminders count.
			$cart->cart['reminders'] = $cart->get_data( 'reminders' ) + 1;
			$cart->save();
		}

		$response_data = array(
			'mail_sent' => $mail_sent,
			'message'   => $mail_sent ? __( 'Reminder sent', 'easycommerce' ) : __( 'Reminder not sent', 'easycommerce' ),
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @param array $response_data The response data including mail status.
		 * @param string $hash The cart hash.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_remind_abandoned_response', $response_data, $hash, $request );

		$this->response_success( $response_data );
	}

	private function sanitize_address( $address ) {
		if ( ! is_array( $address ) ) {
			return $address;
		}

		$text_fields = array( 'first_name', 'last_name', 'address_1', 'address_2', 'city', 'state', 'country', 'postcode' );

		foreach ( $text_fields as $field ) {
			if ( isset( $address[ $field ] ) ) {
				$address[ $field ] = sanitize_text_field( $address[ $field ] );
			}
		}

		if ( isset( $address['email'] ) ) {
			$address['email'] = sanitize_email( $address['email'] );
		}

		if ( isset( $address['phone'] ) ) {
			$address['phone'] = sanitize_text_field( $address['phone'] );
		}

		return $address;
	}
}

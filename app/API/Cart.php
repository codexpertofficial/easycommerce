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

		$this->guard_locked( $cart );

		$products = $request->get_param( 'products' );

		if ( ! is_array( $products ) || empty( $products ) ) {
			$this->response_error( __( 'Invalid product data.', 'easycommerce' ) );
		}

		foreach ( $products as $product ) {

			$product_id = isset( $product['id'] ) ? absint( $product['id'] ) : 0;
			$price_id   = isset( $product['price_id'] ) ? absint( $product['price_id'] ) : 1;
			$quantity   = isset( $product['quantity'] ) ? absint( $product['quantity'] ) : 1;

			$product_obj = new Product( $product_id );

			if ( ! $product_obj->exists() ) {
				/* Translators: %d is the product ID. */
				$this->response_error( sprintf( __( 'Invalid product ID: %d', 'easycommerce' ), $product_id ) );
			}

			// Read the lines fresh: add() saves on every iteration.
			$has_stock         = true;
			$cart_items        = $cart->get_items();
			$existing_quantity = $cart_items[ $product_id ][ $price_id ]['quantity'] ?? 0;

			$variation_exists = false;

			foreach ( $product_obj->get_variations() as $variation ) {
				if ( $variation->get_price_id() != $price_id ) {
					continue;
				}

				$variation_exists = true;

				if ( $variation->manages_stock() && ! is_null( $stock = $variation->get_stock() ) && ( $quantity + $existing_quantity ) > $stock ) {
					$has_stock = false;
				}
			}

			// add() returns silently for an unknown variation.
			if ( ! $variation_exists ) {
				/* Translators: %d is the price ID. */
				$this->response_error( sprintf( __( 'Invalid variation: %d', 'easycommerce' ), $price_id ) );
			}

			if ( $has_stock ) {
				$cart->add( $product_id, $price_id, $quantity );
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

		$this->guard_locked( $cart );

		$product_id = absint( $request->get_param( 'id' ) );
		$price_id   = absint( $request->get_param( 'price_id' ) );
		$quantity   = absint( $request->get_param( 'quantity' ) );

		// Zero is not an update: update_qty() would leave a 0-quantity line behind.
		if ( $product_id <= 0 || $quantity < 1 ) {
			$this->response_error( __( 'Invalid product ID or quantity.', 'easycommerce' ) );
		}

		$product_obj = new Product( $product_id );

		if ( ! $product_obj->is_sellable() ) {
			$this->response_error( __( 'Product not found.', 'easycommerce' ), 404 );
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

		$this->guard_locked( $cart );

		$product_id = absint( $request->get_param( 'id' ) );
		$price_id   = absint( $request->get_param( 'price_id' ) );

		if ( $product_id <= 0 ) {
			$this->response_error( __( 'Invalid product ID.', 'easycommerce' ) );
		}

		// Remove associated free products if any.
		$coupons = $cart->cart['data']['coupons'] ?? array();
		foreach ( $coupons as $code ) {
			$coupon = new Coupon( $code );

			if ( $coupon->get_type() !== 'products' || ! in_array( $product_id, $coupon->get_products() ) ) {
				continue;
			}

			foreach ( $this->get_free_products( $coupon ) as $free_product ) {
				$cart->remove( $free_product['id'], 1, true );
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
			// Only drop the stored method when this request recomputes the list.
			$cart->cart['data']['shipping_method']     = null;
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

			// get( true ) is not memoised: read the tier figures once, not per plan.
			$formatted_cart    = $cart->get( true );
			$physical_subtotal = $formatted_cart['fragments']['physical_subtotal'] ?? 0;
			$cart_weight_grams = $cart->get_weight() * easycommerce_weight_unit_conversion( 'g' )['kg'];
			$cart_quantity     = $cart->get_quantity();

			foreach ( $plans as $plan ) {

				if ( $plan['active'] != 1 ) {
					continue;
				}

				$base    = $plan['calculation_base'];
				$matched = array();
				$value   = null;

				if ( $base == 'price' ) {
					$value = $physical_subtotal;

					foreach ( $plan['methods'] as $method ) {
						$min = $method->min;
						$max = $this->tier_max( $method->max );

						if ( $min <= $value && $max >= $value ) {
							$matched[] = array(
								'id'   => $method->id,
								'name' => $method->name,
								'cost' => $method->cost,
								'min'  => $min,
								'max'  => $max,
							);
						}
					}
				} elseif ( $base == 'weight' ) {
					$unit_conversions = easycommerce_weight_unit_conversion( 'g' );
					$value            = $cart_weight_grams;

					foreach ( $plan['methods'] as $method ) {
						// Convert min and max to grams based on their units
						$min_grams = $method->min * ( $unit_conversions[$method->min_unit] ?? 1 );
						$max       = $this->tier_max( $method->max );
						$max_grams = PHP_INT_MAX === $max ? PHP_INT_MAX : $max * ( $unit_conversions[$method->max_unit] ?? 1 );

						// Compare cart weight (in grams) with method range (in grams)
						if ( $min_grams <= $value && $value <= $max_grams ) {
							$matched[] = array(
								'id'   => $method->id,
								'name' => $method->name,
								'cost' => $method->cost,
								'min'  => $min_grams,
								'max'  => $max_grams,
							);
						}
					}
				} elseif ( $base == 'quantity' ) {
					$value = $cart_quantity;

					foreach ( $plan['methods'] as $method ) {
						$min = $method->min;
						$max = $this->tier_max( $method->max );

						if ( $min <= $value && $max >= $value ) {
							$matched[] = array(
								'id'   => $method->id,
								'name' => $method->name,
								'cost' => $method->cost,
								'min'  => $min,
								'max'  => $max,
							);
						}
					}
				}

				$methods = array_merge( $methods, $this->drop_boundary_duplicates( $matched, $value ) );
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

		$this->guard_locked( $cart );

		if ( ! $method ) {
			$this->response_error( __( 'Shipping method not found.', 'easycommerce' ), 400 );
		}

		// Only the methods this cart's own address/tier lookup produced are selectable.
		$available = array_map( 'absint', wp_list_pluck( $cart->get_shipping_methods(), 'id' ) );

		if ( ! in_array( absint( $id ), $available, true ) ) {
			$this->response_error( __( 'Shipping method is not available for this cart.', 'easycommerce' ), 400 );
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
		$payment_method  = sanitize_key( (string) $request->get_param( 'payment_method' ) );

		$this->guard_locked( $cart );

		// Only a gateway the store has switched on may be stored.
		if ( ! in_array( $payment_method, easycommerce_active_payment_methods(), true ) ) {
			$this->response_error( __( 'Payment method is not available.', 'easycommerce' ), 400 );
		}

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

		$this->guard_locked( $cart );

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

		// Without the cart it validates against the requester's own session.
		if ( ! $coupon->is_applicable( $cart ) ) {
			$this->response_error(
				array(
					'message' => __( 'Coupon is not applicable.', 'easycommerce' ),
				)
			);
		}

		// Coupon lookups are case insensitive, so compare the resolved code.
		$canonical_code = $coupon->get_code();
		$applied_codes  = array_map( 'strtolower', array_map( 'strval', $cart->cart['data']['coupons'] ) );

		if ( ! in_array( strtolower( (string) $canonical_code ), $applied_codes, true ) ) {
			$cart->cart['data']['coupons'][] = $canonical_code;

			// Add free products if Buy X Get Y offer.
			if ( $coupon->get_type() === 'products' ) {
				foreach ( $this->get_free_products( $coupon ) as $free_product ) {
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

		$this->guard_locked( $cart );

		$normalized_code = strtolower( (string) $code );
		$applied_codes   = array_map( 'strtolower', array_map( 'strval', (array) ( $cart->cart['data']['coupons'] ?? array() ) ) );

		if ( empty( $cart->cart['data']['coupons'] ) || ! in_array( $normalized_code, $applied_codes, true ) ) {
			$this->response_error(
				array(
					'message' => __( 'Cart doesn\'t contain this coupon!', 'easycommerce' ),
				)
			);
		}

		$cart->cart['data']['coupons'] = array_values(
			array_filter(
				$cart->cart['data']['coupons'],
				function ( $value ) use ( $normalized_code ) {
					return strtolower( (string) $value ) !== $normalized_code;
				}
			)
		);

		// Remove free products if Buy X Get Y offer.
		$coupon = new Coupon( $code );
		if ( $coupon->get_type() === 'products' ) {
			foreach ( $this->get_free_products( $coupon ) as $free_product ) {
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

		$this->guard_locked( $cart );

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

	/**
	 * Gift lines of a Buy X Get Y coupon, empty when the offer is not a list.
	 *
	 * @param Coupon $coupon The coupon.
	 * @return array
	 */
	private function get_free_products( $coupon ) {
		$free_products = maybe_unserialize( $coupon->get_offer() );

		if ( empty( $free_products ) || ! is_array( $free_products ) ) {
			return array();
		}

		return array_filter(
			$free_products,
			function ( $free_product ) {
				return is_array( $free_product ) && ! empty( $free_product['id'] );
			}
		);
	}

	/**
	 * Drop a tier that only starts where another one of the same plan ends.
	 *
	 * @param array $matched Methods whose range covers the value, with min and max.
	 * @param mixed $value   The compared subtotal, weight or quantity.
	 * @return array Methods in the response shape, without the bounds.
	 */
	private function drop_boundary_duplicates( $matched, $value ) {
		// DECIMAL columns arrive as floats and weights are unit converted.
		$is = function ( $bound ) use ( $value ) {
			return abs( (float) $bound - (float) $value ) < 0.0001;
		};

		$ends_here = false;

		foreach ( $matched as $row ) {
			if ( $is( $row['max'] ) ) {
				$ends_here = true;
				break;
			}
		}

		$kept = array();

		foreach ( $matched as $row ) {
			if ( $ends_here && $is( $row['min'] ) && ! $is( $row['max'] ) ) {
				continue;
			}

			unset( $row['min'], $row['max'] );

			$kept[] = $row;
		}

		return $kept;
	}

	/**
	 * Upper bound of a shipping tier, where empty and zero mean no maximum.
	 *
	 * @param mixed $max The stored maximum.
	 * @return float|int
	 */
	private function tier_max( $max ) {
		if ( null === $max || '' === $max || 0.0 === (float) $max ) {
			return PHP_INT_MAX;
		}

		return $max;
	}

	/**
	 * Reject a change when the cart is locked for payment.
	 *
	 * @param Cart_Model $cart The cart being changed.
	 */
	private function guard_locked( $cart ) {
		if ( $cart->is_locked() ) {
			$this->response_error( __( 'Cart is locked for payment. Complete or cancel the current payment before making changes.', 'easycommerce' ), 403 );
		}
	}

	private function sanitize_address( $address ) {
		// Consumers expect an array, and unknown keys must not reach the stored cart.
		if ( ! is_array( $address ) ) {
			return array();
		}

		$text_fields = array( 'first_name', 'last_name', 'address_1', 'address_2', 'city', 'state', 'country', 'postcode' );
		$sanitized   = array();

		foreach ( $text_fields as $field ) {
			if ( isset( $address[ $field ] ) && is_scalar( $address[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $address[ $field ] );
			}
		}

		if ( isset( $address['email'] ) && is_scalar( $address['email'] ) ) {
			$sanitized['email'] = sanitize_email( $address['email'] );
		}

		if ( isset( $address['phone'] ) && is_scalar( $address['phone'] ) ) {
			$sanitized['phone'] = sanitize_text_field( $address['phone'] );
		}

		/**
		 * Filter the sanitized checkout address, to allow extra address fields.
		 *
		 * @param array $sanitized The keys kept from the request.
		 * @param array $address   The raw address from the request.
		 */
		return apply_filters( 'easycommerce_sanitize_cart_address', $sanitized, $address );
	}
}

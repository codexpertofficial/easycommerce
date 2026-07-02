<?php
namespace EasyCommerce\API\Agent;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Agent;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Order as Order_Model;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Coupon;
use EasyCommerce\Models\Product_Review;
use EasyCommerce\Models\Shipping_Plan;

class Assistant extends Agent {

	protected $log_type = 'shopping_agent';

	private $order_url = null;

	// -------------------------------------------------------------------------
	// Overrides
	// -------------------------------------------------------------------------

	public function run_message( string $session_id, string $message ): array {
		$this->order_url = null;
		$result          = parent::run_message( $session_id, $message );
		$result['order_url'] = $this->order_url;
		return $result;
	}

	protected function build_response( array $result, string $session_id ): array {
		return array(
			'session_id' => $session_id,
			'reply'      => $result['reply'],
			'order_url'  => $result['order_url'] ?? null,
		);
	}

	// -------------------------------------------------------------------------
	// Tool dispatch
	// -------------------------------------------------------------------------

	protected function dispatch_tool( string $name, array $args ): array {
		switch ( $name ) {
			case 'search_products':
				return $this->tool_search_products( $args );
			case 'get_product_details':
				return $this->tool_get_product_details( $args );
			case 'check_stock':
				return $this->tool_check_stock( $args );
			case 'calculate_shipping':
				return $this->tool_calculate_shipping( $args );
			case 'apply_coupon':
				return $this->tool_apply_coupon( $args );
			case 'create_order':
				return $this->tool_create_order( $args );
			case 'generate_payment_link':
				return $this->tool_generate_payment_link( $args );
			case 'get_order_status':
				return $this->tool_get_order_status( $args );
			case 'update_order_contact':
				return $this->tool_update_order_contact( $args );
			case 'update_order_address':
				return $this->tool_update_order_address( $args );
			case 'add_order_note':
				return $this->tool_add_order_note( $args );
			case 'get_order_breakdown':
				return $this->tool_get_order_breakdown( $args );
			case 'get_order_items':
				return $this->tool_get_order_items( $args );
			case 'estimate_order_total':
				return $this->tool_estimate_order_total( $args );
			case 'apply_coupon_to_order':
				return $this->tool_apply_coupon_to_order( $args );
			case 'cancel_order':
				return $this->tool_cancel_order( $args );
			case 'resend_order_email':
				return $this->tool_resend_order_email( $args );
			case 'track_order':
				return $this->tool_track_order( $args );
			case 'get_refund_info':
				return $this->tool_get_refund_info( $args );
			case 'get_customer_profile':
				return $this->tool_get_customer_profile( $args );
			case 'list_products_by_category':
				return $this->tool_list_products_by_category( $args );
			case 'get_product_reviews':
				return $this->tool_get_product_reviews( $args );
			case 'validate_coupon':
				return $this->tool_validate_coupon( $args );
			default:
				return array( 'error' => "Unknown tool: {$name}" );
		}
	}

	// -------------------------------------------------------------------------
	// Tool implementations
	// -------------------------------------------------------------------------

	private function tool_search_products( array $args ): array {
		$query       = $args['query'] ?? '';
		$color       = strtolower( $args['color'] ?? '' );
		$size        = strtolower( $args['size'] ?? '' );
		$price_range = $args['price_range'] ?? '';

		$filters = array( 'search' => $query );

		if ( ! empty( $price_range ) ) {
			$parts = array_map( 'floatval', explode( '-', $price_range, 2 ) );
			if ( count( $parts ) === 2 ) {
				$filters['min_price'] = $parts[0];
				$filters['max_price'] = $parts[1];
			}
		}

		$result   = Product::list( $filters, 5 );
		$products = $result['products'] ?? array();

		if ( empty( $products ) ) {
			return array( 'found' => false, 'message' => 'No products found matching your search.' );
		}

		$out = array();
		foreach ( $products as $product ) {
			$row = array(
				'id'          => $product->get_id(),
				'name'        => $product->get_title(),
				'url'         => $product->get_url(),
				'is_variable' => $product->is_variable(),
				'variants'    => array(),
			);

			$vm       = new Product_Variation();
			$variants = $vm->get_by( $product->get_id(), 'product_id' );

			foreach ( $variants as $v ) {
				$v_name = strtolower( $v->get_name( true ) );

				if ( ! empty( $color ) && strpos( $v_name, $color ) === false ) {
					continue;
				}
				if ( ! empty( $size ) && strpos( $v_name, $size ) === false ) {
					continue;
				}

				$row['variants'][] = array(
					'variation_id' => $v->get_id(),
					'price_id'     => $v->get_price_id(),
					'name'         => $v->get_name( true ),
					'price'        => $v->get_price( false ),
					'stock'        => $v->get_stock(),
				);
			}

			// If color/size filters removed all variants, skip this product
			if ( ( ! empty( $color ) || ! empty( $size ) ) && empty( $row['variants'] ) ) {
				continue;
			}

			// Include a price summary when no variant filter applied
			if ( empty( $row['variants'] ) ) {
				foreach ( $variants as $v ) {
					$row['variants'][] = array(
						'variation_id' => $v->get_id(),
						'price_id'     => $v->get_price_id(),
						'name'         => $v->get_name( true ),
						'price'        => $v->get_price( false ),
						'stock'        => $v->get_stock(),
					);
				}
			}

			$out[] = $row;
		}

		if ( empty( $out ) ) {
			return array( 'found' => false, 'message' => 'No products matched the specified color/size.' );
		}

		return array( 'found' => true, 'products' => $out );
	}

	private function tool_get_product_details( array $args ): array {
		$product_id = (int) ( $args['product_id'] ?? 0 );
		$product    = new Product( $product_id );

		if ( ! $product->exists() ) {
			return array( 'error' => 'Product not found.' );
		}

		$vm       = new Product_Variation();
		$variants = $vm->get_by( $product_id, 'product_id' );
		$out      = array();

		foreach ( $variants as $v ) {
			$out[] = array(
				'variation_id' => $v->get_id(),
				'price_id'     => $v->get_price_id(),
				'name'         => $v->get_name( true ),
				'price'        => $v->get_price( false ),
				'stock'        => $v->get_stock(),
				'sku'          => $v->get_sku(),
			);
		}

		return array(
			'id'          => $product->get_id(),
			'name'        => $product->get_title(),
			'description' => wp_strip_all_tags( $product->get_summary() ),
			'url'         => $product->get_url(),
			'is_variable' => $product->is_variable(),
			'variants'    => $out,
		);
	}

	private function tool_check_stock( array $args ): array {
		$product_id   = (int) ( $args['product_id'] ?? 0 );
		$variation_id = (int) ( $args['variation_id'] ?? 0 );
		$quantity     = max( 1, (int) ( $args['quantity'] ?? 1 ) );

		if ( $variation_id ) {
			$variation = new Product_Variation( $variation_id );
			if ( ! $variation->exists() ) {
				return array( 'error' => 'Variation not found.' );
			}
		} else {
			$vm   = new Product_Variation();
			$vars = $vm->get_by( $product_id, 'product_id' );
			if ( empty( $vars ) ) {
				return array( 'error' => 'No variations found for this product.' );
			}
			$variation = $vars[0];
		}

		$stock     = (int) $variation->get_stock();
		$available = $variation->manages_stock() ? $stock >= $quantity : true;

		return array(
			'in_stock'  => $available,
			'stock'     => $stock,
			'requested' => $quantity,
			'message'   => $available
				? "{$quantity} unit(s) available."
				: "Only {$stock} unit(s) in stock.",
		);
	}

	private function tool_calculate_shipping( array $args ): array {
		$address  = sanitize_text_field( $args['address'] ?? '' );
		$subtotal = (float) ( $args['subtotal'] ?? 0 );

		$result = Shipping_Plan::list( array( 'per_page' => 5, 'active' => 1 ) );
		$plans  = $result['plans'] ?? array();

		if ( empty( $plans ) ) {
			return array( 'shipping_cost' => 0, 'message' => 'Free shipping available.' );
		}

		$plan    = $plans[0];
		$methods = $plan['methods'] ?? array();

		if ( empty( $methods ) ) {
			return array( 'shipping_cost' => 0, 'message' => 'Free shipping available.' );
		}

		$cheapest = null;
		foreach ( $methods as $method ) {
			$rate = (float) ( $method['price'] ?? 0 );
			if ( $cheapest === null || $rate < (float) $cheapest['price'] ) {
				$cheapest = $method;
			}
		}

		$cost = (float) ( $cheapest['price'] ?? 0 );

		return array(
			'shipping_cost' => $cost,
			'method'        => $cheapest['name'] ?? 'Standard Shipping',
			'message'       => $cost > 0
				? 'Shipping: ' . easycommerce_price( $cost )
				: 'Free shipping available.',
		);
	}

	private function tool_apply_coupon( array $args ): array {
		$code     = sanitize_text_field( $args['coupon_code'] ?? '' );
		$subtotal = (float) ( $args['subtotal'] ?? 0 );

		if ( empty( $code ) ) {
			return array( 'valid' => false, 'message' => 'No coupon code provided.' );
		}

		$coupon = Coupon::get( $code );

		if ( ! $coupon || empty( $coupon['active'] ) ) {
			return array( 'valid' => false, 'message' => 'Coupon not found or inactive.' );
		}

		$type     = $coupon['type'];
		$offer    = $coupon['offer'];
		$discount = 0;

		if ( $type === 'percentage' ) {
			$discount = $subtotal * ( (float) $offer / 100 );
		} elseif ( $type === 'fixed' ) {
			$discount = (float) $offer;
		}

		$discount  = min( $discount, $subtotal );
		$new_total = $subtotal - $discount;

		return array(
			'valid'     => true,
			'discount'  => round( $discount, 2 ),
			'new_total' => round( $new_total, 2 ),
			'message'   => 'Coupon applied! Discount: ' . easycommerce_price( $discount ) . '. New total: ' . easycommerce_price( $new_total ) . '.',
		);
	}

	private function tool_create_order( array $args ): array {
		$customer_name  = sanitize_text_field( $args['customer_name'] ?? '' );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );
		$address_1      = sanitize_text_field( $args['address_1'] ?? '' );
		$address_2      = sanitize_text_field( $args['address_2'] ?? '' );
		$city           = sanitize_text_field( $args['city'] ?? '' );
		$state          = sanitize_text_field( $args['state'] ?? '' );
		$postcode       = sanitize_text_field( $args['postcode'] ?? '' );
		$country        = sanitize_text_field( $args['country'] ?? '' );
		$phone          = sanitize_text_field( $args['phone'] ?? '' );
		$coupon_code    = sanitize_text_field( $args['coupon_code'] ?? '' );

		if ( ! $customer_name || ! $customer_email || ! $address_1 ) {
			return array( 'error' => 'Missing required fields: customer_name, customer_email, address_1.' );
		}

		// Normalise to items array. Accept either `items` array or legacy single-item params.
		$raw_items = $args['items'] ?? null;
		if ( empty( $raw_items ) || ! is_array( $raw_items ) ) {
			$product_id = (int) ( $args['product_id'] ?? 0 );
			$price_id   = (int) ( $args['price_id'] ?? 0 );
			if ( ! $product_id || ! $price_id ) {
				return array( 'error' => 'Provide either an `items` array or `product_id` + `price_id`.' );
			}
			$raw_items = array(
				array(
					'product_id'   => $product_id,
					'variation_id' => (int) ( $args['variation_id'] ?? 0 ),
					'price_id'     => $price_id,
					'quantity'     => max( 1, (int) ( $args['quantity'] ?? 1 ) ),
				),
			);
		}

		$vm       = new Product_Variation();
		$items    = array();
		$subtotal = 0.0;

		foreach ( $raw_items as $idx => $item ) {
			$pid   = (int) ( $item['product_id'] ?? 0 );
			$vid   = (int) ( $item['variation_id'] ?? 0 );
			$prid  = (int) ( $item['price_id'] ?? 0 );
			$qty   = max( 1, (int) ( $item['quantity'] ?? 1 ) );

			if ( ! $pid || ! $prid ) {
				return array( 'error' => "Item #" . ( $idx + 1 ) . " missing product_id or price_id." );
			}

			$variation = $vid ? new Product_Variation( $vid ) : $vm->get_by_price( $prid, $pid );
			if ( ! $variation || ! $variation->exists() ) {
				return array( 'error' => "Item #" . ( $idx + 1 ) . ": variation not found (product_id={$pid}, price_id={$prid})." );
			}

			$rate = (float) $variation->get_price( false );

			if ( isset( $items[ $pid ][ $prid ] ) ) {
				// Aggregate quantity for duplicate product+price combos.
				$qty += $items[ $pid ][ $prid ]['quantity'];
				$subtotal -= $items[ $pid ][ $prid ]['price'];
			}

			$items[ $pid ][ $prid ] = array(
				'quantity' => $qty,
				'rate'     => $rate,
				'price'    => round( $rate * $qty, 2 ),
				'is_free'  => false,
			);

			$subtotal += $rate * $qty;
		}

		$total    = $subtotal;
		$discount = 0.0;
		$coupons  = array();

		if ( ! empty( $coupon_code ) ) {
			$coupon = Coupon::get( $coupon_code );
			if ( $coupon && ! empty( $coupon['active'] ) ) {
				if ( $coupon['type'] === 'percentage' ) {
					$discount = $subtotal * ( (float) $coupon['offer'] / 100 );
				} elseif ( $coupon['type'] === 'fixed' ) {
					$discount = (float) $coupon['offer'];
				}
				$discount  = min( $discount, $subtotal );
				$total    -= $discount;
				$coupons[] = $coupon_code;
			}
		}

		$names   = explode( ' ', $customer_name, 2 );
		$address = array(
			'first_name' => $names[0],
			'last_name'  => $names[1] ?? '',
			'email'      => $customer_email,
			'phone'      => $phone,
			'address_1'  => $address_1,
			'address_2'  => $address_2,
			'city'       => $city,
			'state'      => $state,
			'postcode'   => $postcode,
			'country'    => $country,
		);

		$wp_user = get_user_by( 'email', $customer_email );
		if ( $wp_user ) {
			$customer_id = $wp_user->ID;
		} else {
			$username    = sanitize_user( strtolower( $names[0] ) . ( isset( $names[1] ) ? '.' . strtolower( $names[1] ) : '' ) . '.' . time(), true );
			$customer_id = wp_create_user( $username, wp_generate_password(), $customer_email );
			if ( is_wp_error( $customer_id ) ) {
				return array( 'error' => 'Failed to create customer account: ' . $customer_id->get_error_message() );
			}
			wp_update_user( array(
				'ID'         => $customer_id,
				'first_name' => $names[0],
				'last_name'  => $names[1] ?? '',
			) );
		}

		$order_model = new Order_Model();
		$order_id    = $order_model->create(
			array(
				'customer_id' => $customer_id,
				'total'       => round( $total, 2 ),
				'status'      => 'pending',
				'items'       => $items,
				'meta'        => array(
					'billing_address'  => $address,
					'shipping_address' => $address,
					'coupons'          => $coupons,
					'subtotal'         => round( $subtotal, 2 ),
					'discount'         => round( $discount, 2 ),
					'tax'              => 0,
					'shipping_tax'     => 0,
					'shipping'         => 0,
				),
			)
		);

		if ( ! $order_id ) {
			return array( 'error' => 'Failed to create order.' );
		}

		return array(
			'success'     => true,
			'order_id'    => $order_id,
			'item_count'  => count( $raw_items ),
			'total'       => round( $total, 2 ),
			'message'     => 'Order #' . $order_id . ' created (pending) with ' . count( $raw_items ) . ' item(s). Total: ' . easycommerce_price( $total ) . '. Call generate_payment_link with this order_id.',
		);
	}

	private function tool_generate_payment_link( array $args ): array {
		$order_id = (int) ( $args['order_id'] ?? 0 );

		if ( ! $order_id ) {
			return array( 'error' => 'order_id is required.' );
		}

		$order = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			return array( 'error' => 'Order not found.' );
		}

		$pay_url         = easycommerce_payment_page( true ) . '?order_id=' . $order_id;
		$this->order_url = $pay_url;

		return array(
			'success'     => true,
			'payment_url' => $pay_url,
			'message'     => "Payment link ready. Share this with the customer: {$pay_url}",
		);
	}

	private function tool_get_order_status( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $customer_email ) {
			return array( 'error' => 'customer_email is required.' );
		}

		if ( $order_id ) {
			$order = new Order_Model( $order_id );
			if ( ! $order->get_id() ) {
				return array( 'error' => 'Order not found.' );
			}
			$billing = $order->get_meta( 'billing_address' ) ?: array();
			if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
				return array( 'error' => 'Email does not match this order.' );
			}
			return array(
				'order_id' => $order_id,
				'status'   => $order->get_status(),
				'total'    => $order->get_total(),
				'message'  => "Order #{$order_id} is {$order->get_status()}.",
			);
		}

		$wp_user = get_user_by( 'email', $customer_email );
		if ( ! $wp_user ) {
			return array( 'error' => 'No customer found with this email.' );
		}
		$customer = new Customer( $wp_user->ID );
		$orders   = $customer->get_orders( 5 );

		if ( empty( $orders ) ) {
			return array( 'message' => 'No orders found for this customer.' );
		}

		$out = array();
		foreach ( $orders as $o ) {
			$out[] = array(
				'order_id' => $o->get_id(),
				'status'   => $o->get_status(),
				'total'    => $o->get_total(),
			);
		}
		return array( 'orders' => $out );
	}

	private function tool_update_order_contact( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );
		$phone          = sanitize_text_field( $args['phone'] ?? '' );
		$customer_name  = sanitize_text_field( $args['customer_name'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}
		if ( empty( $phone ) && empty( $customer_name ) ) {
			return array( 'error' => 'Provide at least one field to update: phone or customer_name.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match the order. Cannot update.' );
		}

		$updates = array();
		if ( ! empty( $phone ) ) {
			$updates['phone'] = $phone;
		}
		if ( ! empty( $customer_name ) ) {
			$names                 = explode( ' ', $customer_name, 2 );
			$updates['first_name'] = $names[0];
			$updates['last_name']  = $names[1] ?? '';
		}

		$updated_billing  = array_merge( $billing, $updates );
		$updated_shipping = array_merge( $order->get_meta( 'shipping_address' ) ?: array(), $updates );

		$order->update_meta( 'billing_address', $updated_billing );
		$order->update_meta( 'shipping_address', $updated_shipping );

		return array(
			'success'  => true,
			'order_id' => $order_id,
			'updated'  => array_keys( $updates ),
			'message'  => "Order #{$order_id} contact info updated.",
		);
	}

	private function tool_update_order_address( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match the order. Cannot update.' );
		}

		$addr_fields = array( 'address_1', 'address_2', 'city', 'state', 'postcode', 'country' );
		$updates     = array();
		foreach ( $addr_fields as $field ) {
			if ( ! empty( $args[ $field ] ) ) {
				$updates[ $field ] = sanitize_text_field( $args[ $field ] );
			}
		}

		if ( empty( $updates ) ) {
			return array( 'error' => 'No address fields provided to update.' );
		}

		$order->update_meta( 'shipping_address', array_merge( $order->get_meta( 'shipping_address' ) ?: array(), $updates ) );
		$order->update_meta( 'billing_address', array_merge( $billing, $updates ) );

		return array(
			'success'  => true,
			'order_id' => $order_id,
			'updated'  => array_keys( $updates ),
			'message'  => "Order #{$order_id} delivery address updated.",
		);
	}

	private function tool_add_order_note( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );
		$note           = sanitize_textarea_field( $args['note'] ?? '' );

		if ( ! $order_id || ! $customer_email || ! $note ) {
			return array( 'error' => 'order_id, customer_email, and note are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match the order. Cannot update.' );
		}

		$order->update_meta( 'customer_note', $note );

		return array(
			'success'  => true,
			'order_id' => $order_id,
			'message'  => "Note added to order #{$order_id}.",
		);
	}

	private function tool_get_order_breakdown( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match this order.' );
		}

		$total    = (float) $order->get_total();
		$refunded = (float) $order->get_total_refunded();

		return array(
			'order_id'       => $order_id,
			'subtotal'       => (float) $order->get_subtotal(),
			'discount'       => (float) $order->get_discount_total(),
			'shipping'       => (float) $order->get_shipping_total(),
			'tax'            => (float) $order->get_tax_total(),
			'total'          => $total,
			'total_refunded' => $refunded,
			'balance_due'    => max( 0.0, $total - $refunded ),
			'payment_status' => $order->get_status(),
		);
	}

	private function tool_get_order_items( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match this order.' );
		}

		$out = array();
		foreach ( $order->get_items() as $item ) {
			$out[] = array(
				'name'       => $item->name ?? '',
				'quantity'   => (int) ( $item->quantity ?? 1 ),
				'unit_price' => (float) ( $item->rate ?? 0 ),
				'line_total' => (float) ( $item->price ?? 0 ),
			);
		}

		return array(
			'order_id' => $order_id,
			'items'    => $out,
		);
	}

	private function tool_estimate_order_total( array $args ): array {
		$coupon_code = sanitize_text_field( $args['coupon_code'] ?? '' );
		$address     = sanitize_text_field( $args['address'] ?? '' );

		// Normalise to items array. Accept either `items` array or legacy single-item params.
		$raw_items = $args['items'] ?? null;
		if ( empty( $raw_items ) || ! is_array( $raw_items ) ) {
			$price_id   = (int) ( $args['price_id'] ?? 0 );
			$product_id = (int) ( $args['product_id'] ?? 0 );
			if ( ! $price_id ) {
				return array( 'error' => 'Provide either an `items` array or a `price_id`.' );
			}
			$raw_items = array(
				array(
					'product_id' => $product_id,
					'price_id'   => $price_id,
					'quantity'   => max( 1, (int) ( $args['quantity'] ?? 1 ) ),
				),
			);
		}

		$vm       = new Product_Variation();
		$lines    = array();
		$subtotal = 0.0;

		foreach ( $raw_items as $idx => $item ) {
			$pid  = (int) ( $item['product_id'] ?? 0 );
			$prid = (int) ( $item['price_id'] ?? 0 );
			$qty  = max( 1, (int) ( $item['quantity'] ?? 1 ) );

			if ( ! $prid ) {
				return array( 'error' => "Item #" . ( $idx + 1 ) . " missing price_id." );
			}

			$variation = $vm->get_by_price( $prid, $pid );
			if ( ! $variation || ! $variation->exists() ) {
				return array( 'error' => "Item #" . ( $idx + 1 ) . ": variation not found (price_id={$prid})." );
			}

			$unit_price = (float) $variation->get_price( false );
			$line_total = $unit_price * $qty;
			$subtotal  += $line_total;

			$lines[] = array(
				'price_id'   => $prid,
				'unit_price' => $unit_price,
				'quantity'   => $qty,
				'line_total' => round( $line_total, 2 ),
			);
		}

		$discount    = 0.0;
		$coupon_note = '';

		if ( ! empty( $coupon_code ) ) {
			$coupon = Coupon::get( $coupon_code );
			if ( $coupon && ! empty( $coupon['active'] ) ) {
				if ( $coupon['type'] === 'percentage' ) {
					$discount = $subtotal * ( (float) $coupon['offer'] / 100 );
				} elseif ( $coupon['type'] === 'fixed' ) {
					$discount = (float) $coupon['offer'];
				}
				$discount    = min( $discount, $subtotal );
				$coupon_note = 'Coupon "' . $coupon_code . '" applied: -' . easycommerce_price( $discount ) . '.';
			} else {
				$coupon_note = 'Coupon "' . $coupon_code . '" is not valid.';
			}
		}

		$shipping_cost   = 0.0;
		$shipping_method = 'Free shipping';

		if ( ! empty( $address ) ) {
			$s               = $this->tool_calculate_shipping( array( 'address' => $address, 'subtotal' => $subtotal ) );
			$shipping_cost   = (float) ( $s['shipping_cost'] ?? 0 );
			$shipping_method = $s['method'] ?? 'Standard Shipping';
		}

		$total = ( $subtotal - $discount ) + $shipping_cost;

		return array(
			'lines'           => $lines,
			'subtotal'        => round( $subtotal, 2 ),
			'discount'        => round( $discount, 2 ),
			'shipping'        => round( $shipping_cost, 2 ),
			'shipping_method' => $shipping_method,
			'estimated_total' => round( $total, 2 ),
			'coupon_note'     => $coupon_note,
			'message'         => 'Estimated total: ' . easycommerce_price( $total ) . ' (subtotal ' . easycommerce_price( $subtotal ) . ', shipping ' . easycommerce_price( $shipping_cost ) . ', discount -' . easycommerce_price( $discount ) . ').',
		);
	}

	private function tool_apply_coupon_to_order( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );
		$coupon_code    = sanitize_text_field( $args['coupon_code'] ?? '' );

		if ( ! $order_id || ! $customer_email || ! $coupon_code ) {
			return array( 'error' => 'order_id, customer_email, and coupon_code are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match this order.' );
		}

		if ( $order->get_status() !== 'pending' ) {
			return array( 'error' => 'Coupon can only be applied to unpaid (pending) orders. This order status is "' . $order->get_status() . '".' );
		}

		foreach ( $order->get_transactions() as $txn ) {
			if ( isset( $txn->status ) && $txn->status === 'completed' ) {
				return array( 'error' => 'Order already has a completed payment. Cannot apply coupon.' );
			}
		}

		$coupon = Coupon::get( $coupon_code );
		if ( ! $coupon || empty( $coupon['active'] ) ) {
			return array( 'valid' => false, 'message' => 'Coupon not found or inactive.' );
		}

		$subtotal = (float) $order->get_subtotal();
		$discount = 0.0;

		if ( $coupon['type'] === 'percentage' ) {
			$discount = $subtotal * ( (float) $coupon['offer'] / 100 );
		} elseif ( $coupon['type'] === 'fixed' ) {
			$discount = (float) $coupon['offer'];
		}
		$discount  = min( $discount, $subtotal );
		$new_total = $subtotal - $discount + (float) $order->get_shipping_total() + (float) $order->get_tax_total();

		$order->update( array( 'total' => $new_total ) );
		$order->update_meta( 'coupon_code', $coupon_code );
		$order->update_meta( 'discount_amount', $discount );

		return array(
			'success'   => true,
			'order_id'  => $order_id,
			'discount'  => round( $discount, 2 ),
			'new_total' => round( $new_total, 2 ),
			'message'   => 'Coupon applied to order #' . $order_id . '. New total: ' . easycommerce_price( $new_total ) . '.',
		);
	}

	private function tool_cancel_order( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match this order.' );
		}

		$current = $order->get_status();
		if ( ! in_array( $current, array( 'pending', 'on-hold' ), true ) ) {
			return array( 'error' => 'Cannot cancel order. Status is "' . $current . '". Only pending or on-hold orders can be cancelled.' );
		}

		$order->set_status( 'cancelled' );

		return array(
			'success'  => true,
			'order_id' => $order_id,
			'message'  => "Order #{$order_id} has been cancelled.",
		);
	}

	private function tool_resend_order_email( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match this order.' );
		}

		do_action( 'easycommerce_order_email', $order->get_status(), $order_id );

		return array(
			'success'  => true,
			'order_id' => $order_id,
			'message'  => "Order confirmation email for order #{$order_id} has been resent.",
		);
	}

	private function tool_track_order( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match this order.' );
		}

		$fulfillment = $order->get_fulfillment_status();

		return array(
			'order_id'           => $order_id,
			'payment_status'     => $order->get_status(),
			'fulfillment_status' => $fulfillment,
			'message'            => "Order #{$order_id} — payment: {$order->get_status()}, fulfillment: {$fulfillment}.",
		);
	}

	private function tool_get_refund_info( array $args ): array {
		$order_id       = (int) ( $args['order_id'] ?? 0 );
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $order_id || ! $customer_email ) {
			return array( 'error' => 'order_id and customer_email are required.' );
		}

		$order = new Order_Model( $order_id );
		if ( ! $order->get_id() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		if ( strtolower( $billing['email'] ?? '' ) !== strtolower( $customer_email ) ) {
			return array( 'error' => 'Email does not match this order.' );
		}

		$refunds        = $order->get_refunds();
		$total_refunded = (float) $order->get_total_refunded();

		if ( empty( $refunds ) ) {
			return array(
				'order_id'       => $order_id,
				'has_refunds'    => false,
				'total_refunded' => 0,
				'message'        => "No refunds found for order #{$order_id}.",
			);
		}

		$out = array();
		foreach ( $refunds as $refund ) {
			$out[] = array(
				'amount' => (float) $refund->amount,
				'reason' => $refund->reason ?? '',
				'status' => $refund->status ?? '',
			);
		}

		return array(
			'order_id'       => $order_id,
			'has_refunds'    => true,
			'total_refunded' => $total_refunded,
			'refunds'        => $out,
			'message'        => "Order #{$order_id} refunded total: " . easycommerce_price( $total_refunded ) . '.',
		);
	}

	private function tool_get_customer_profile( array $args ): array {
		$customer_email = sanitize_email( $args['customer_email'] ?? '' );

		if ( ! $customer_email ) {
			return array( 'error' => 'customer_email is required.' );
		}

		$wp_user = get_user_by( 'email', $customer_email );
		if ( ! $wp_user ) {
			return array( 'error' => 'No customer found with this email.' );
		}

		$customer = new Customer( $wp_user->ID );

		return array(
			'name'        => $customer->get_name(),
			'email'       => $customer_email,
			'phone'       => $customer->get_phone(),
			'address'     => $customer->get_address(),
			'order_count' => (int) $customer->get_order_count(),
			'total_spent' => (float) $customer->get_total_spent(),
		);
	}

	private function tool_list_products_by_category( array $args ): array {
		$category = sanitize_text_field( $args['category'] ?? '' );
		$brand    = sanitize_text_field( $args['brand'] ?? '' );

		if ( empty( $category ) && empty( $brand ) ) {
			return array( 'error' => 'Provide at least one of: category or brand.' );
		}

		$filters = array();

		if ( ! empty( $category ) ) {
			$filters['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'name',
				'terms'    => $category,
			);
		}

		if ( ! empty( $brand ) ) {
			$filters['tax_query'][] = array(
				'taxonomy' => 'product_brand',
				'field'    => 'name',
				'terms'    => $brand,
			);
		}

		$result   = Product::list( $filters, 10 );
		$products = $result['products'] ?? array();

		if ( empty( $products ) ) {
			return array( 'found' => false, 'message' => 'No products found in this category.' );
		}

		$out = array();
		foreach ( $products as $product ) {
			$out[] = array(
				'id'          => $product->get_id(),
				'name'        => $product->get_title(),
				'price'       => $product->get_price( false ),
				'url'         => $product->get_url(),
				'is_variable' => $product->is_variable(),
			);
		}

		return array( 'found' => true, 'count' => count( $out ), 'products' => $out );
	}

	private function tool_get_product_reviews( array $args ): array {
		$product_id = (int) ( $args['product_id'] ?? 0 );

		if ( ! $product_id ) {
			return array( 'error' => 'product_id is required.' );
		}

		$product = new Product( $product_id );
		if ( ! $product->exists() ) {
			return array( 'error' => 'Product not found.' );
		}

		$result = Product_Review::list( array( 'post_id' => $product_id, 'per_page' => 5, 'status' => 1 ) );
		$items  = $result['reviews'] ?? array();

		if ( empty( $items ) ) {
			return array(
				'product_id'   => $product_id,
				'product_name' => $product->get_title(),
				'found'        => false,
				'message'      => 'No approved reviews yet for this product.',
			);
		}

		$out = array();
		foreach ( $items as $review ) {
			$out[] = array(
				'rating'  => (int) ( $review['rating'] ?? 0 ),
				'content' => $review['content'] ?? '',
				'author'  => $review['customer_name'] ?? '',
				'date'    => $review['created_at'] ?? '',
			);
		}

		return array(
			'product_id'    => $product_id,
			'product_name'  => $product->get_title(),
			'found'         => true,
			'avg_rating'    => $product->get_rating(),
			'total_reviews' => (int) $product->get_rating_count(),
			'reviews'       => $out,
		);
	}

	private function tool_validate_coupon( array $args ): array {
		$code        = sanitize_text_field( $args['coupon_code'] ?? '' );
		$subtotal    = (float) ( $args['subtotal'] ?? 0 );
		$product_ids = array_map( 'intval', (array) ( $args['product_ids'] ?? array() ) );

		if ( empty( $code ) ) {
			return array( 'valid' => false, 'message' => 'No coupon code provided.' );
		}

		$coupon_model = new Coupon( $code );

		if ( ! $coupon_model->exists() || ! $coupon_model->is_active() ) {
			return array( 'valid' => false, 'message' => 'Coupon not found or inactive.' );
		}

		$rules        = $coupon_model->get_rules();
		$current_date = wp_date( 'Y-m-d' );
		$errors       = array();

		foreach ( $rules as $rule ) {
			if ( empty( $rule->value ) ) {
				continue;
			}
			$type = $rule->type;

			if ( $type === 'min_spend' && $subtotal < (float) $rule->value ) {
				$errors[] = 'Minimum spend of ' . easycommerce_price( $rule->value ) . ' required.';
			}
			if ( $type === 'max_spend' && $subtotal > (float) $rule->value ) {
				$errors[] = 'Order total exceeds maximum spend of ' . easycommerce_price( $rule->value ) . ' for this coupon.';
			}
			if ( $type === 'start_date' && $current_date < $rule->value ) {
				$errors[] = 'Coupon is not active yet. Valid from ' . $rule->value . '.';
			}
			if ( $type === 'end_date' && $current_date > $rule->value ) {
				$errors[] = 'Coupon expired on ' . $rule->value . '.';
			}
			if ( $type === 'products' && ! empty( $product_ids ) ) {
				$allowed = array_column( (array) $rule->value, 'id' );
				if ( empty( array_intersect( $product_ids, array_map( 'intval', $allowed ) ) ) ) {
					$errors[] = 'Coupon is not valid for the selected products.';
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return array( 'valid' => false, 'message' => implode( ' ', $errors ) );
		}

		$type     = $coupon_model->get_type();
		$offer    = (float) $coupon_model->get_offer();
		$discount = 0.0;

		if ( $type === 'percentage' ) {
			$discount = $subtotal * ( $offer / 100 );
		} elseif ( $type === 'fixed' ) {
			$discount = $offer;
		}
		$discount = min( $discount, $subtotal );

		return array(
			'valid'    => true,
			'type'     => $type,
			'offer'    => $offer,
			'discount' => round( $discount, 2 ),
			'message'  => 'Coupon valid! You save ' . easycommerce_price( $discount ) . '.',
		);
	}

	// -------------------------------------------------------------------------
	// Tool definitions & system prompt
	// -------------------------------------------------------------------------

	protected function get_tools(): array {
		return array(
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'search_products',
					'description' => 'Search for products in the store by name, color, size, or price range. Returns matching products with their variants.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'query'       => array( 'type' => 'string', 'description' => 'Product name or keyword to search' ),
							'color'       => array( 'type' => 'string', 'description' => 'Color to filter by (e.g. red, blue)' ),
							'size'        => array( 'type' => 'string', 'description' => 'Size to filter by (e.g. 10, XL, 42)' ),
							'price_range' => array( 'type' => 'string', 'description' => 'Price range formatted as "min-max" (e.g. "50-200")' ),
						),
						'required'   => array( 'query' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_product_details',
					'description' => 'Get full details and all available variants for a specific product by ID.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'product_id' => array( 'type' => 'integer', 'description' => 'The product ID' ),
						),
						'required'   => array( 'product_id' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'check_stock',
					'description' => 'Check whether a product or specific variant has sufficient stock for the requested quantity.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'product_id'   => array( 'type' => 'integer', 'description' => 'The product ID' ),
							'variation_id' => array( 'type' => 'integer', 'description' => 'Specific variation ID (optional)' ),
							'quantity'     => array( 'type' => 'integer', 'description' => 'Quantity requested', 'default' => 1 ),
						),
						'required'   => array( 'product_id' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'calculate_shipping',
					'description' => 'Calculate the shipping cost for a delivery address and order subtotal.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'address'  => array( 'type' => 'string', 'description' => 'Full delivery address' ),
							'subtotal' => array( 'type' => 'number', 'description' => 'Order subtotal before shipping' ),
						),
						'required'   => array( 'address', 'subtotal' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'apply_coupon',
					'description' => 'Validate a coupon/promo code and calculate the resulting discount.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'coupon_code' => array( 'type' => 'string', 'description' => 'The coupon or promo code' ),
							'subtotal'    => array( 'type' => 'number', 'description' => 'Order subtotal to apply the discount on' ),
						),
						'required'   => array( 'coupon_code', 'subtotal' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'create_order',
					'description' => 'Create a pending order after receiving explicit customer confirmation. Supports one or more line items via the `items` array. Returns an order_id — immediately pass it to generate_payment_link.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'items'          => array(
								'type'        => 'array',
								'description' => 'Line items to order. Each item needs product_id, price_id, and quantity.',
								'items'       => array(
									'type'       => 'object',
									'properties' => array(
										'product_id'   => array( 'type' => 'integer', 'description' => 'Product ID' ),
										'price_id'     => array( 'type' => 'integer', 'description' => 'Variation price ID from search or product details' ),
										'variation_id' => array( 'type' => 'integer', 'description' => 'Variation ID (optional)' ),
										'quantity'     => array( 'type' => 'integer', 'description' => 'Number of units', 'default' => 1 ),
									),
									'required'   => array( 'product_id', 'price_id' ),
								),
							),
							'customer_name'  => array( 'type' => 'string', 'description' => 'Customer full name' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Customer email address' ),
							'phone'          => array( 'type' => 'string', 'description' => 'Customer phone number (optional)' ),
							'address_1'      => array( 'type' => 'string', 'description' => 'Street address line 1 (house/flat number and street name)' ),
							'address_2'      => array( 'type' => 'string', 'description' => 'Street address line 2 (apartment, suite, area — optional)' ),
							'city'           => array( 'type' => 'string', 'description' => 'City or district' ),
							'state'          => array( 'type' => 'string', 'description' => 'State, province, or division' ),
							'postcode'       => array( 'type' => 'string', 'description' => 'Postal or ZIP code' ),
							'country'        => array( 'type' => 'string', 'description' => 'Country name or 2-letter ISO code' ),
							'coupon_code'    => array( 'type' => 'string', 'description' => 'Coupon code to apply (optional)' ),
						),
						'required'   => array( 'items', 'customer_name', 'customer_email', 'address_1', 'city', 'country' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'generate_payment_link',
					'description' => 'Generate a payment URL from the order_id returned by create_order. Share the resulting payment_url with the customer.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id' => array( 'type' => 'integer', 'description' => 'The order_id returned by create_order' ),
						),
						'required'   => array( 'order_id' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_order_status',
					'description' => 'Look up the payment status of an order. customer_email is always required. Provide order_id to check a specific order (email is verified against it); omit order_id to list the customer\'s recent orders.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'Specific order ID to look up (optional — omit to list all recent orders by email)' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Customer email address — always required, used to verify order ownership' ),
						),
						'required'   => array( 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'update_order_contact',
					'description' => 'Update the phone number or customer name on an existing order. Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID to update' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
							'phone'          => array( 'type' => 'string', 'description' => 'New phone number (optional)' ),
							'customer_name'  => array( 'type' => 'string', 'description' => 'New full name (optional)' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'update_order_address',
					'description' => 'Update the delivery/shipping address on an existing order. Requires order ID and the email address on the order to verify ownership. Only updates fields you provide.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID to update' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
							'address_1'      => array( 'type' => 'string', 'description' => 'Street address line 1 (optional)' ),
							'address_2'      => array( 'type' => 'string', 'description' => 'Street address line 2 (optional)' ),
							'city'           => array( 'type' => 'string', 'description' => 'City (optional)' ),
							'state'          => array( 'type' => 'string', 'description' => 'State or province (optional)' ),
							'postcode'       => array( 'type' => 'string', 'description' => 'Postcode or ZIP (optional)' ),
							'country'        => array( 'type' => 'string', 'description' => 'Country name or 2-letter ISO code (optional)' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'add_order_note',
					'description' => 'Add a customer note or delivery instruction to an existing order (e.g. "leave at the door", "call before delivery"). Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
							'note'           => array( 'type' => 'string', 'description' => 'The note or delivery instruction' ),
						),
						'required'   => array( 'order_id', 'customer_email', 'note' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_order_breakdown',
					'description' => 'Get a full price breakdown for an order: subtotal, discount, shipping, tax, total, refunded amount, and balance due. Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_order_items',
					'description' => 'Get the line items (products) inside an order: product name, quantity, unit price, and line total. Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'estimate_order_total',
					'description' => 'Calculate an estimated total before placing an order: sum of line items, optional coupon discount, and optional shipping. Supports one or more items. Use this to give the customer a price quote.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'items'       => array(
								'type'        => 'array',
								'description' => 'Line items to estimate. Each item needs price_id and quantity.',
								'items'       => array(
									'type'       => 'object',
									'properties' => array(
										'product_id' => array( 'type' => 'integer', 'description' => 'Product ID (optional but recommended)' ),
										'price_id'   => array( 'type' => 'integer', 'description' => 'Variation price ID from search or product details' ),
										'quantity'   => array( 'type' => 'integer', 'description' => 'Number of units', 'default' => 1 ),
									),
									'required'   => array( 'price_id' ),
								),
							),
							'coupon_code' => array( 'type' => 'string', 'description' => 'Optional coupon code to apply' ),
							'address'     => array( 'type' => 'string', 'description' => 'Optional delivery address to calculate shipping' ),
						),
						'required'   => array( 'items' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'apply_coupon_to_order',
					'description' => 'Apply a coupon code to an existing unpaid (pending) order and recalculate the total. Only works before payment. Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
							'coupon_code'    => array( 'type' => 'string', 'description' => 'The coupon code to apply' ),
						),
						'required'   => array( 'order_id', 'customer_email', 'coupon_code' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'cancel_order',
					'description' => 'Cancel an order. Only pending or on-hold orders can be cancelled. Requires order ID and the email address on the order to verify ownership. Always confirm with the customer before cancelling.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID to cancel' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'resend_order_email',
					'description' => 'Resend the order confirmation email to the customer. Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'track_order',
					'description' => 'Get the fulfillment/shipping status of an order. Returns payment status and fulfillment status. Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_refund_info',
					'description' => 'Check if an order has been refunded and get refund amount and reason. Requires order ID and the email address on the order to verify ownership.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id'       => array( 'type' => 'integer', 'description' => 'The order ID' ),
							'customer_email' => array( 'type' => 'string', 'description' => 'Email address on the order — used to verify ownership' ),
						),
						'required'   => array( 'order_id', 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_customer_profile',
					'description' => "Look up a customer's own profile: name, address, phone, order count, and total amount spent. Only use this with the customer's own email address.",
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'customer_email' => array( 'type' => 'string', 'description' => 'The customer email address' ),
						),
						'required'   => array( 'customer_email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'list_products_by_category',
					'description' => 'Browse products by category or brand name. Use when a customer wants to explore a product type rather than search by keyword (e.g. "show me shoes", "what Nike products do you have?").',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'category' => array( 'type' => 'string', 'description' => 'Product category name (e.g. "Shoes", "Electronics")' ),
							'brand'    => array( 'type' => 'string', 'description' => 'Brand name (e.g. "Nike", "Apple")' ),
						),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_product_reviews',
					'description' => 'Get customer reviews and average rating for a product. Use when a customer asks about product quality, reviews, or ratings.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'product_id' => array( 'type' => 'integer', 'description' => 'The product ID' ),
						),
						'required'   => array( 'product_id' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'validate_coupon',
					'description' => 'Validate a coupon code against all its rules: minimum spend, maximum spend, expiry date, and product restrictions. More thorough than apply_coupon — prefer this when a customer asks if a coupon is valid.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'coupon_code' => array( 'type' => 'string', 'description' => 'The coupon code to validate' ),
							'subtotal'    => array( 'type' => 'number', 'description' => 'Order subtotal to check spend rules against' ),
							'product_ids' => array(
								'type'        => 'array',
								'items'       => array( 'type' => 'integer' ),
								'description' => 'Product IDs in the order, to check product-restricted coupons',
							),
						),
						'required'   => array( 'coupon_code' ),
					),
				),
			),
		);
	}

	protected function get_system_prompt(): string {
		$store_name = get_bloginfo( 'name' );
		return "You are a friendly shopping assistant for {$store_name}. " .
			"Help customers find products and place orders through natural conversation. " .

			"PRODUCT DISCOVERY: Use search_products for keyword searches. Use list_products_by_category to browse by category or brand. Use get_product_reviews when customers ask about quality or ratings. Use get_product_details for full variant details. " .

			"BEFORE PLACING AN ORDER: " .
			"(1) Collect all desired products with their variants (size/color), quantities, customer full name, email, street address (address_1), city, and country. A single order can contain multiple products — gather all items before proceeding. " .
			"(2) Collect address fields separately — never accept a single freeform string. Ask for street address, city, state/division, postcode, and country as separate pieces. " .
			"(3) Optionally use estimate_order_total with the full `items` array to quote the total before confirming. " .
			"(4) If the customer has a promo code, use validate_coupon to check all rules (expiry, min spend, product restrictions) before quoting the discount. " .
			"(5) Never call create_order without explicit customer confirmation (e.g. 'yes', 'confirm', 'go ahead', 'place the order'). " .
			"(6) Pass all line items in the `items` array of create_order — one call creates the entire order regardless of how many products. " .
			"(7) After creating an order, always call generate_payment_link with the returned order_id and share the payment link with the customer. " .

			"AFTER AN ORDER IS PLACED: " .
			"Help customers with: order status (get_order_status), full price breakdown (get_order_breakdown), line items (get_order_items), shipment/fulfillment tracking (track_order), refund details (get_refund_info), applying a coupon to an unpaid order (apply_coupon_to_order), cancelling a pending order (cancel_order — always confirm first), updating contact info (update_order_contact), updating delivery address (update_order_address), adding a delivery note (add_order_note), resending the confirmation email (resend_order_email). " .

			"OWNERSHIP VERIFICATION: Every order tool requires the customer's email address to verify they own the order. Always ask for the order ID and their email before accessing or modifying any order. Never reveal or modify order data if the email does not match. " .

			"CUSTOMER PROFILE: Use get_customer_profile only with the customer's own email address. " .

			"RESTRICTIONS: Never update payment method, order pricing directly, or payment/fulfillment status — those are admin-only actions. " .

			"Keep responses concise and conversational.";
	}
}

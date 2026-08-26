<?php

namespace EasyCommerce\Controllers\Payment\Stripe\API;

use EasyCommerce\Controllers\Payment\Stripe\Helpers\Payment_Methods;
use EasyCommerce\Models\Cart;
use EasyCommerce\Traits\Rest;
use WP_Error;
use WP_REST_Request;

class Payment_Intent {

	use Rest;

	protected $api_client;

	protected $payment_methods_helper;

	public function __construct() {
		if ( ! empty( easycommerce_stripe_get_secret_key() ) ) {
			$this->api_client = easycommerce_stripe_get_api_client();
			if ( $this->api_client ) {
				$this->payment_methods_helper = new Payment_Methods();
			}
		}
	}

	/**
	 * Get or create a Stripe customer for the current user.
	 *
	 * @return string|WP_Error Customer ID or error.
	 */
	protected function get_or_create_customer() {
		if ( ! $this->api_client ) {
			return new WP_Error( 'stripe_not_configured', 'Stripe is not properly configured.' );
		}

		try {
			$user_id        = get_current_user_id();
			$customer_email = '';

			if ( $user_id > 0 ) {
				$user           = wp_get_current_user();
				$customer_email = $user->user_email;
				$customer_id    = get_user_meta( $user_id, 'stripe_customer_id', true );

				if ( $customer_id ) {
					try {
						$customer = $this->api_client->customers->retrieve( $customer_id );
						if ( ! empty( $customer->deleted ) ) {
							delete_user_meta( $user_id, 'stripe_customer_id' );
							$customer_id = false;
						} else {
							return $customer_id;
						}
					} catch ( \Exception $e ) {
						delete_user_meta( $user_id, 'stripe_customer_id' );
						$customer_id = false;
					}
				}
			}

			// For logged-out users, get email from cart
			if ( $user_id <= 0 ) {
				$cart           = new Cart();
				$customer_email = $cart->get_customer_email();
			}

			// Check for existing customer by email
			if ( ! empty( $customer_email ) ) {
				$existing_customers = $this->api_client->customers->all(
					array(
						'email' => $customer_email,
						'limit' => 1,
					)
				);

				if ( ! empty( $existing_customers->data ) ) {
					$customer_id = $existing_customers->data[0]->id;
					if ( $user_id > 0 ) {
						update_user_meta( $user_id, 'stripe_customer_id', $customer_id );
					}
					return $customer_id;
				}
			}
			// Create new customer
			$customer_data = array(
				'email' => $customer_email,
			);

			if ( $user_id > 0 ) {
				$customer_data['metadata'] = array(
					'easycommerce_user_id' => $user_id,
				);
			}

			$customer = $this->api_client->customers->create( $customer_data );

			if ( $user_id > 0 ) {
				update_user_meta( $user_id, 'stripe_customer_id', $customer->id );
			}

			return $customer->id;

		} catch ( \Throwable $e ) {
			return new WP_Error( 'customer_creation_error', $e->getMessage() );
		}
	}

	/**
	 * Prepare metadata for Stripe.
	 *
	 * @param array  $cart Cart data.
	 * @param string $customer_email Customer email.
	 *
	 * @return array Metadata array.
	 */
	protected function prepare_metadata( $cart, $customer_email ) {

		$item_tax     = (float) ( $cart['amounts']['tax'] ?? 0 );
		$shipping_tax = (float) ( $cart['amounts']['shipping_tax'] ?? 0 );
		$total_tax    = $item_tax + $shipping_tax;

		$metadata = array(
			'easycommerce_cart_id'         => isset( $cart['id'] ) ? (string) $cart['id'] : '',
			'easycommerce_subtotal'        => isset( $cart['amounts']['subtotal'] ) ? (string) $cart['amounts']['subtotal'] : '0',
			'easycommerce_discount_amount' => isset( $cart['amounts']['discount_amount'] ) ? (string) $cart['amounts']['discount_amount'] : '0',
			'easycommerce_shipping_fee'    => isset( $cart['amounts']['shipping_fee'] ) ? (string) $cart['amounts']['shipping_fee'] : '0',
			'easycommerce_tax_amount'      => (string) $total_tax,
			'easycommerce_item_tax'        => (string) $item_tax,
			'easycommerce_shipping_tax'    => (string) $shipping_tax,
			'easycommerce_fees_amount'     => isset( $cart['amounts']['fees'] ) ? (string) $cart['amounts']['fees'] : '0',
			'easycommerce_total_amount'    => (string) $cart['amounts']['total'],
			'easycommerce_currency'        => easycommerce_currency(),
			'easycommerce_item_count'      => ! empty( $cart['items'] ) ? (string) count( $cart['items'] ) : '0',
			'easycommerce_customer_email'  => $customer_email,
		);

		// Add item details (limited to avoid metadata limits)
		if ( ! empty( $cart['items'] ) ) {
			$item_index = 0;
			foreach ( $cart['items'] as $item ) {
				if ( $item_index >= 5 ) {
					break;
				}
				$metadata[ 'easycommerce_item_' . $item_index . '_id' ]       = (string) ( $item['product_id'] ?? '' );
				$metadata[ 'easycommerce_item_' . $item_index . '_name' ]     = substr( (string) ( $item['title'] ?? '' ), 0, 50 );
				$metadata[ 'easycommerce_item_' . $item_index . '_quantity' ] = (string) ( $item['quantity'] ?? 0 );
				$metadata[ 'easycommerce_item_' . $item_index . '_price' ]    = (string) ( $item['unit_price'] ?? 0 );
				++$item_index;
			}
		}

		return $metadata;
	}

	/**
	 * Create a Payment Intent OR Setup Intent based on cart contents.
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return array|WP_Error The intent data or error.
	 */
	public function create( WP_REST_Request $request ) {
		if ( ! $this->api_client ) {
			return new WP_Error( 'stripe_not_configured', 'Stripe is not properly configured.' );
		}

		try {
			$intent_for = $request->get_param( 'intent_for' ) ?: 'payment';
			$order_id   = (int) $request->get_param( 'order_id' );
			$currency   = strtolower( easycommerce_currency() );

			if ( $order_id ) {
				$order = new \EasyCommerce\Models\Order( $order_id );

				if ( ! $order->exists() ) {
					return new WP_Error( 'invalid_order', __( 'Order not found.', 'easycommerce' ), array( 'status' => 400 ) );
				}

				// Guard against payment intent amount manipulation via arbitrary order IDs.
				// Logged-in users may only pay their own orders. Guests may pay guest
				// orders (no registered owner) so guest checkout keeps working, but must
				// not be able to reference an order that belongs to a registered user.
				$order_customer_id = (int) $order->get_customer_id();
				$current_user_id   = get_current_user_id();

				if ( $current_user_id > 0 ) {
					if ( $order_customer_id !== $current_user_id ) {
						return new WP_Error( 'order_access_denied', __( 'Permission denied.', 'easycommerce' ), array( 'status' => 403 ) );
					}
				}

				$total_amount = (float) $order->get_total();
				$cart         = array(
					'amounts' => array( 'total' => $total_amount ),
					'items'   => array(),
				);
			} else {
				$cart         = ( new Cart() )->get( true, false );
				$total_amount = (float) $cart['amounts']['total'];
			}

			if ( is_user_logged_in() ) {
				$user           = wp_get_current_user();
				$customer_email = $user->user_email;
			} else {
				$_cart          = new Cart();
				$customer_email = $_cart->get_customer_email();
			}

			$customer_id = $this->get_or_create_customer();
			if ( is_wp_error( $customer_id ) ) {
				return $customer_id;
			}

			$metadata = $this->prepare_metadata( $cart, $customer_email );

			if ( $intent_for === 'setup' ) {

				$enabled_payment_methods = $this->payment_methods_helper->get_enabled_payment_methods();
				$enabled_payment_methods = easycommerce_stripe_filter_payment_methods_by_currency( $enabled_payment_methods, $currency );
				$enabled_payment_methods = easycommerce_stripe_filter_wallet_payment_methods( $enabled_payment_methods );
				$enabled_payment_methods = apply_filters( 'easycommerce_stripe_filter_payment_methods', $enabled_payment_methods, $cart, $currency );

				// Ensure at least 'card' (always reusable) so the SetupIntent stays valid.
				if ( empty( $enabled_payment_methods ) ) {
					$enabled_payment_methods = array( 'card' );
				}

				$setup_intent_data = array(
					'customer'             => $customer_id,
					'usage'                => 'off_session',
					'metadata'             => $metadata,
					'payment_method_types' => array_values( $enabled_payment_methods ),
				);

				$setup_intent = $this->api_client->setupIntents->create( $setup_intent_data );

				return array(
					'client_secret'   => $setup_intent->client_secret,
					'setup_intent_id' => $setup_intent->id,
					'customer_id'     => $customer_id,
					'type'            => 'setup_intent',
				);
			} else {
				$amount_in_cents = max( (int) round( $total_amount * 100 ), 50 );

				// Non-recurring path. Let Stripe decide which of the account's enabled
				// methods are eligible for this currency, country and amount — its rules
				// are authoritative and self-updating, so no static method-to-currency
				// map can go stale. This is what made Klarna misbehave: its allowed
				// currency depends on the Stripe account country, not a fixed list
				// (https://docs.stripe.com/payments/klarna). Recurring carts never reach
				// here; they run as SetupIntents in the branch above. See Stripe's
				// dynamic payment methods docs for the mechanism used here:
				// https://docs.stripe.com/payments/payment-methods/dynamic-payment-methods .
				$all_methods = $this->payment_methods_helper->get_enabled_payment_methods();
				$all_methods = easycommerce_stripe_filter_wallet_payment_methods( $all_methods );

				// Preserve the existing extension contract: a filter (e.g. the
				// subscriptions add-on) may narrow the set. Automatic payment methods
				// takes no allow-list, so whatever the filter removes is passed on as an
				// explicit exclusion instead.
				$kept     = apply_filters( 'easycommerce_stripe_payment_intent_methods', $all_methods, $cart, $currency );
				$excluded = array_values( array_diff( $all_methods, $kept ) );

				$payment_intent_data = array(
					'amount'                    => $amount_in_cents,
					'currency'                  => $currency,
					'customer'                  => $customer_id,
					'metadata'                  => $metadata,
					'automatic_payment_methods' => array( 'enabled' => true ),
				);

				// Pin to the account's default payment method configuration when known so
				// the server intent and the client Payment Element resolve the same set;
				// Stripe falls back to the account default when it is omitted.
				$pmc_id = $this->payment_methods_helper->get_default_pmc_id();
				if ( ! empty( $pmc_id ) ) {
					$payment_intent_data['payment_method_configuration'] = $pmc_id;
				}

				if ( ! empty( $excluded ) ) {
					$payment_intent_data['excluded_payment_method_types'] = $excluded;
				}

				$payment_intent = $this->api_client->paymentIntents->create( $payment_intent_data );

				// Fix 4: Bind the intent to the order server-side so Order::pay() can
				// verify it without trusting the client-supplied intent ID.
				if ( $order_id ) {
					$order->add_meta( '_ec_pending_stripe_intent_id', $payment_intent->id );
				}

				return array(
					'client_secret'     => $payment_intent->client_secret,
					'payment_intent_id' => $payment_intent->id,
					'customer_id'       => $customer_id,
					'amount'            => $amount_in_cents,
					'currency'          => $currency,
					'type'              => 'payment_intent',
				);
			}
		} catch ( \Throwable $e ) {
			return new WP_Error( 'intent_creation_error', $e->getMessage() );
		}
	}

	/**
	 * Update Payment Intent or SetupIntent with current cart amount.
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return array|WP_Error The update result or error.
	 */
	public function update( WP_REST_Request $request ) {
		if ( ! $this->api_client ) {
			return new WP_Error( 'stripe_not_configured', 'Stripe is not properly configured.' );
		}

		try {
			$payment_intent_id = $request->get_param( 'payment_intent_id' );
			$setup_intent_id   = $request->get_param( 'setup_intent_id' );
			$intent_type       = $request->get_param( 'intent_type' );
			$cart              = ( new Cart() )->get( true, false );
			$total_amount      = (float) $cart['amounts']['total'];
			$amount_in_cents   = max( (int) round( $total_amount * 100 ), 50 );

			if ( is_user_logged_in() ) {
				$user           = wp_get_current_user();
				$customer_email = $user->user_email;
			} else {
				$_cart          = new Cart();
				$customer_email = $_cart->get_customer_email();
			}

			if ( $intent_type === 'setup_intent' && $setup_intent_id ) {

				// For SetupIntent, just update metadata (no amount)
				$this->api_client->setupIntents->update(
					$setup_intent_id,
					array(
						'metadata' => array(
							'easycommerce_total_amount'   => (string) $total_amount,
							'easycommerce_customer_email' => $customer_email,
						),
					)
				);

				return array(
					'success' => true,
					'type'    => 'setup_intent',
				);
			} elseif ( $payment_intent_id ) {

				$update_data = array(
					'amount'   => $amount_in_cents,
					'metadata' => array(
						'easycommerce_total_amount'   => (string) $total_amount,
						'easycommerce_customer_email' => $customer_email,
					),
				);

				$this->api_client->paymentIntents->update( $payment_intent_id, $update_data );

				return array(
					'success' => true,
					'amount'  => $amount_in_cents,
					'type'    => 'payment_intent',
				);
			}

			return new WP_Error( 'missing_intent_id', 'Intent ID is required.' );

		} catch ( \Throwable $e ) {
			return new WP_Error( 'intent_update_error', $e->getMessage() );
		}
	}
}

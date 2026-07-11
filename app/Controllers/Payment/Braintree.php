<?php
namespace EasyCommerce\Controllers\Payment;

use EasyCommerce\Models\Cart;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;
use Braintree\Gateway;
use EasyCommerce\Abstracts\Payment_Method;
use Exception;

add_action(
	'init',
	function () {
		class Braintree extends Payment_Method {
			protected $id;
			protected $refund_transaction_id;

			/**
			 * Constructor
			 */
			public function __construct() {
				$this->id = 'braintree';

				parent::__construct(
					$this->id,
					__( 'Braintree', 'easycommerce' ),
					__( 'Pay via Braintree', 'easycommerce' )
				);

				add_filter( 'easycommerce_payment_method_braintree_icon', array( $this, 'braintree_logo_url' ) );
			}

			public function braintree_logo_url() {
				$icon_id  = Utility::get_option( 'payment', 'braintree', 'braintree_logo', '' );
				$icon_url = '';
				if ( wp_attachment_is_image( $icon_id ) ) {
					$icon_url = wp_get_attachment_url( $icon_id );
				}

				return $icon_url ?: EASYCOMMERCE_ASSETS_URL . 'payment/img/braintree.svg';
			}

			public function settings(): array {
				if ( ! $this->is_enabled() ) {
					return array();
				}

				return array(
					array(
						'label'  => __( 'Basic', 'easycommerce' ),
						'fields' => array(
							'test_mode'            => array(
								'id'          => 'test_mode',
								'type'        => 'switch',
								'label'       => __( 'Sandbox Mode', 'easycommerce' ),
								'description' => __( 'Enable to process test transactions without real charges.', 'easycommerce' ),
							),
							'braintree_logo'       => array(
								'id'          => 'braintree_logo',
								'type'        => 'image',
								'label'       => __( 'Payment Method Logo', 'easycommerce' ),
								'description' => __( 'Upload your payment method logo. Recommended dimensions: 512x512 px.', 'easycommerce' ),
								'placeholder' => __( 'Choose logo file', 'easycommerce' ),
							),
							'payment_method_name'  => array(
								'id'          => 'payment_method_name',
								'type'        => 'text',
								'label'       => __( 'Name on Checkout', 'easycommerce' ),
								'description' => __( 'The name displayed to customers on the checkout page.', 'easycommerce' ),
								'placeholder' => __( 'Credit Card', 'easycommerce' ),
								'default'     => __( 'Credit Card', 'easycommerce' ),
							),
							'default_order_status' => array(
								'id'          => 'default_order_status',
								'type'        => 'select',
								'label'       => __( 'Default Order Status', 'easycommerce' ),
								'description' => __( 'Initial status assigned to new orders placed through this payment method.', 'easycommerce' ),
								'options'     => array(
									'pending'    => __( 'Pending', 'easycommerce' ),
									'completed'  => __( 'Completed', 'easycommerce' ),
									'processing' => __( 'Processing', 'easycommerce' ),
								),
								'default'     => easycommerce_global_default_status(),
								'placeholder' => __( 'Select Default Order Status', 'easycommerce' ),
							),
						),
					),
					array(
						'label'  => __( 'Braintree Credentials', 'easycommerce' ),
						'fields' => array(
							'merchant_id'      => array(
								'id'          => 'merchant_id',
								'type'        => 'text',
								'label'       => __( 'Merchant ID', 'easycommerce' ),
								'placeholder' => __( 't6b2r9xxxx', 'easycommerce' ),
								'description' => __( 'Your Braintree merchant ID. Get this from your <a href="https://www.braintreegateway.com/login" target="_blank">Braintree Control Panel</a> under Account > API Keys.', 'easycommerce' ),
							),
							'public_key'       => array(
								'id'          => 'public_key',
								'type'        => 'text',
								'label'       => __( 'Public Key', 'easycommerce' ),
								'placeholder' => __( 't6b2r9xxxx', 'easycommerce' ),
								'description' => __( 'Your Braintree public key for API authentication. Found in the <a href="https://www.braintreegateway.com/login" target="_blank">Braintree Control Panel</a> under Account > API Keys.', 'easycommerce' ),
							),
							'private_key'      => array(
								'id'          => 'private_key',
								'type'        => 'text',
								'label'       => __( 'Private Key', 'easycommerce' ),
								'placeholder' => __( '4f8a2c6exxxx', 'easycommerce' ),
								'description' => __( 'Your Braintree private key for server-side API calls. Located in the <a href="https://www.braintreegateway.com/login" target="_blank">Braintree Control Panel</a> under Account > API Keys.', 'easycommerce' ),
							),
							'tokenization_key' => array(
								'id'          => 'tokenization_key',
								'type'        => 'text',
								'label'       => __( 'Tokenization Key', 'easycommerce' ),
								'placeholder' => __( 'production_t6b2r9h5_4f8axxxx', 'easycommerce' ),
								'description' => __( 'Your Braintree tokenization key for client-side payment processing. Generate this in the <a href="https://www.braintreegateway.com/login" target="_blank">Braintree Control Panel</a> under Account > API > Tokenization Keys.', 'easycommerce' ),
							),
						),
					),
				);
			}

			public function is_available() {
				$merchant_id      = Utility::get_option( 'payment', 'braintree', 'merchant_id', '' );
				$public_key       = Utility::get_option( 'payment', 'braintree', 'public_key', '' );
				$private_key      = Utility::get_option( 'payment', 'braintree', 'private_key', '' );
				$tokenization_key = Utility::get_option( 'payment', 'braintree', 'tokenization_key', '' );

				return ! empty( $tokenization_key ) && ! empty( $merchant_id ) && ! empty( $public_key ) && ! empty( $private_key );
			}

			public function enqueue_scripts() {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return;
				}

				if( ! easycommerce_is_checkout() ) return;

				wp_enqueue_script(
					'braintree-js',
					'https://js.braintreegateway.com/web/dropin/1.45.1/js/dropin.js',
					array(),
					'1.45.1',
					true
				);

				wp_enqueue_script(
					'easycommerce-braintree',
					EASYCOMMERCE_ASSETS_URL . 'payment/js/braintree.js',
					array( 'braintree-js', 'jquery' ),
					EASYCOMMERCE_VERSION,
					true
				);
			}

			public function localized( $vars ) {
				if ( ! $this->is_enabled() ) {
					return $vars;
				}

				if( ! easycommerce_is_checkout() ) return $vars;

				$vars['braintree'] = array(
					'tokenization_key' => Utility::get_option( 'payment', 'braintree', 'tokenization_key', '' ),
				);

				return $vars;
			}

			public function payment_form(): string {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return '';
				}

				return '<div id="easycommerce_braintree_payment_form"></div><div id="easycommerce_braintree_payment_errors"></div>';
			}

			public function process_payment( $status, $order_id, $params, $customer_id ) {
				if ( $this->get_id() !== $params['easycommerce-payment_method'] ) {
					return $status;
				}

				$order = new Order( $order_id );
				if ( ! empty( $order->get_meta( 'braintree_transaction_id' ) ) ) {
					return $status;
				}

				if ( Utility::get_option( 'payment', 'braintree', 'default_order_status' ) ) {
					$default_order_status = Utility::get_option( 'payment', 'braintree', 'default_order_status' );
				} else {
					$default_order_status = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
				}

				try {
					$merchant_id = Utility::get_option( 'payment', 'braintree', 'merchant_id', '' );
					$public_key  = Utility::get_option( 'payment', 'braintree', 'public_key', '' );
					$private_key = Utility::get_option( 'payment', 'braintree', 'private_key', '' );
					$test_mode   = Utility::get_option( 'payment', 'braintree', 'test_mode', '0' );

					// Validate required credentials
					if ( empty( $merchant_id ) || empty( $public_key ) || empty( $private_key ) ) {
						throw new Exception( 'Braintree payment gateway is not properly configured. Please check your API credentials.' );
					}

					$gateway = new Gateway(
						array(
							'environment' => $test_mode ? 'sandbox' : 'production',
							'merchantId'  => $merchant_id,
							'publicKey'   => $public_key,
							'privateKey'  => $private_key,
						)
					);

					$nonce = $params['meta']['braintreeNonce'] ?? null;
					if ( ! $nonce ) {
						throw new Exception( 'Payment nonce is missing.' );
					}

					// Validate billing address exists and has required fields
					if ( ! isset( $params['billing_address'] ) || ! is_array( $params['billing_address'] ) ) {
						throw new Exception( 'Billing address is required for payment processing.' );
					}

					$billing_address = $params['billing_address'];

					// Build customer data with validation
					$customer_data = array();

					if ( ! empty( $billing_address['first_name'] ) ) {
						$customer_data['firstName'] = $billing_address['first_name'];
					}

					if ( ! empty( $billing_address['last_name'] ) ) {
						$customer_data['lastName'] = $billing_address['last_name'];
					}

					// Add email if available (important for fraud prevention and customer management)
					if ( ! empty( $billing_address['email'] ) ) {
						$customer_data['email'] = $billing_address['email'];
					}

					// Add phone if available
					if ( ! empty( $billing_address['phone'] ) ) {
						$customer_data['phone'] = $billing_address['phone'];
					}

					// Ensure we have at least basic customer information
					if ( empty( $customer_data ) ) {
						throw new Exception( 'Customer information is required for payment processing.' );
					}

					$customer_result = $gateway->customer()->create( $customer_data );

					if ( ! $customer_result->success ) {
						throw new Exception( 'Failed to create customer in Braintree: ' . $customer_result->message );
					}

					$braintree_customer_id = $customer_result->customer->id;

					$cart = ( new Cart() )->get( true, false );
					if ( empty( $cart ) || ! isset( $cart['items'] ) ) {
						throw new Exception( 'Cart is empty or invalid.' );
					}

					$total_amount = $order->get_total();
					$metadata     = array();

					foreach ( $cart['items'] as $index => $item ) {
						if ( ! isset( $item['title'], $item['quantity'], $item['unit_price'], $item['total'] ) ) {
							throw new Exception( 'Invalid cart item data.' );
						}
						$metadata[ "item_{$index}_name" ]       = $item['title'];
						$metadata[ "item_{$index}_quantity" ]   = (int) $item['quantity'];
						$metadata[ "item_{$index}_unit_price" ] = (float) $item['unit_price'];
						$metadata[ "item_{$index}_subtotal" ]   = (float) $item['subtotal'];
					}

					if ( $total_amount <= 0 ) {
						throw new Exception( 'Order total must be greater than zero.' );
					}

					$amount = number_format( $total_amount, 2, '.', '' );

					$result = $gateway->transaction()->sale(
						array(
							'amount'             => $amount,
							'paymentMethodNonce' => $nonce,
							'customerId'         => $braintree_customer_id,
							'options'            => array(
								'submitForSettlement' => true,
							),

						)
					);

					if ( $result->success && ! is_null( $result->transaction ) ) {
						$order       = new Order( $order_id );
						$transaction = $result->transaction;

						$order->add_meta( 'braintree_transaction_id', $transaction->id );
						$order->add_meta( 'braintree_payment_status', $transaction->status );
						$order->add_meta( 'payment_method_name', 'Braintree' );
						$order->add_meta( 'payment_method', $this->get_id() );

						foreach ( $metadata as $meta_key => $meta_value ) {
							$order->add_meta( $meta_key, $meta_value );
						}

						do_action( "easycommerce-{$this->id}_payment_complete", $transaction->id, $order_id, $total_amount, $customer_id, $params );
						return $default_order_status;
					} else {
						$error_message = 'Braintree transaction failed';
						if ( isset( $result->errors ) && method_exists( $result->errors, 'deepAll' ) ) {
							$errors = array();
							foreach ( $result->errors->deepAll() as $error ) {
								$errors[] = $error->code . ': ' . $error->message;
							}
							if ( ! empty( $errors ) ) {
								$error_message .= ' - ' . implode( '; ', $errors );
							}
						} elseif ( isset( $result->message ) ) {
							$error_message .= ': ' . $result->message;
						}

						throw new Exception( $error_message );
					}
				} catch ( \Throwable $e ) {
					error_log( 'Braintree payment error: ' . $e->getMessage() );
					return $status;
				}
			}

			public function insert_transaction( $transaction_id, $order_id, $total_amount, $customer_id, $params ) {
				if ( empty( $transaction_id ) ) {
					throw new Exception( 'Invalid parameters provided for transaction record insertion.' );
				}

				$currency             = easycommerce_currency();
				$txn                  = new Transaction();
				$existing_transaction = $txn->get_by_order_id( $order_id );

				if ( $existing_transaction ) {
					return;
				}

				$txn->add(
					$order_id,
					array(
						'transaction_id'  => $transaction_id,
						'customer_id'     => $customer_id,
						'payment_gateway' => 'braintree',
						'amount'          => $total_amount,
						'currency'        => $currency,
						'type'            => 'payment',
						'status'          => 'completed',
					)
				);
			}

			public function refund( $order_id, $reason, $amount ) {
				require_once EASYCOMMERCE_PLUGIN_DIR . '/vendor/autoload.php';

				$test_mode      = Utility::get_option( 'payment', 'braintree', 'test_mode', '0' );
				$order          = new Order( $order_id );
				$transaction_id = $order->get_meta( 'braintree_transaction_id' );

				$braintree = new Gateway(
					array(
						'environment' => $test_mode ? 'sandbox' : 'production',
						'merchantId'  => Utility::get_option( 'payment', 'braintree', 'merchant_id', '' ),
						'publicKey'   => Utility::get_option( 'payment', 'braintree', 'public_key', '' ),
						'privateKey'  => Utility::get_option( 'payment', 'braintree', 'private_key', '' ),
					)
				);

				$refund_statuses = array( 'settled', 'settling' );
				$void_statuses   = array( 'submitted_for_settlement' );

				try {
					if ( ! $transaction_id ) {
						return false;
					}

					$transaction = $braintree->transaction()->find( $transaction_id );

					if ( in_array( $transaction->status, $void_statuses ) ) {
						$result = $braintree->transaction()->void( $transaction_id );
					} elseif ( in_array( $transaction->status, $refund_statuses ) ) {
						$result = $braintree->transaction()->refund( $transaction_id, $amount );
					} elseif ( $transaction->status === 'voided' ) {
						return true;
					} else {
						return false;
					}

					if ( $result->success ) {
						$this->refund_transaction_id = $result->transaction->id;
						do_action( 'easycommerce_braintree_refund_complete', $result->transaction->id, $order_id );
						return true;
					}
				} catch ( Exception $e ) {
					return false;
				}

				return false;
			}

			public function refund_transaction_id() {
				return $this->refund_transaction_id;
			}

			public function supports_refund() {
				return true;
			}
		}

		new Braintree();
	},
	9
);

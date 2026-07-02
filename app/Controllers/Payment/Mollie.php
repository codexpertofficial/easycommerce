<?php
namespace EasyCommerce\Controllers\Payment;

use EasyCommerce\Models\Cart;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;
use EasyCommerce\Abstracts\Payment_Method;
use Exception;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Exceptions\ApiException;

add_action(
	'init',
	function () {
		class Mollie extends Payment_Method {

			/**
			 * Constructor
			 */
			public function __construct() {
				$this->id = 'mollie';

				parent::__construct(
					$this->id,
					__( 'Mollie', 'easycommerce' ),
					__( 'Pay via Mollie', 'easycommerce' )
				);

				add_filter( 'easycommerce_payment_method_mollie_icon', array( $this, 'mollie_logo_url' ) );
			}

			public function mollie_logo_url() {
				$icon_id  = Utility::get_option( 'payment', 'mollie', 'mollie_logo', '' );
				$icon_url = '';
				if ( wp_attachment_is_image( $icon_id ) ) {
					$icon_url = wp_get_attachment_url( $icon_id );
				}

				return $icon_url ?: EASYCOMMERCE_ASSETS_URL . 'payment/img/mollie.svg';
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
							'mollie_logo'          => array(
								'id'          => 'mollie_logo',
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
								'placeholder' => __( 'Mollie', 'easycommerce' ),
								'default'     => __( 'Mollie', 'easycommerce' ),
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
						'label'  => __( 'Mollie Credentials', 'easycommerce' ),
						'fields' => array(
							'api_key'    => array(
								'id'          => 'api_key',
								'type'        => 'text',
								'label'       => __( 'API Key', 'easycommerce' ),
								'placeholder' => __( 'live_dHar4XY7xxxx', 'easycommerce' ),
								'description' => __( 'Your Mollie API key for authentication. Get this from your <a href="https://my.mollie.com/dashboard/developers/api-keys" target="_blank">Mollie Dashboard API Keys</a> section.', 'easycommerce' ),
							),
							'profile_id' => array(
								'id'          => 'profile_id',
								'type'        => 'text',
								'label'       => __( 'Profile ID', 'easycommerce' ),
								'placeholder' => __( 'pfl_aP3bxxxx', 'easycommerce' ),
								'description' => __( 'Your Mollie website profile ID. View this in your <a href="https://www.mollie.com/dashboard/settings/profiles" target="_blank">Mollie Dashboard Profiles</a> section.', 'easycommerce' ),
							),
						),
					),
				);
			}

			public function is_available() {
				$api_key    = Utility::get_option( 'payment', 'mollie', 'api_key', '' );
				$profile_id = Utility::get_option( 'payment', 'mollie', 'profile_id', '' );

				return ! empty( $api_key ) && ! empty( $profile_id );
			}

			public function enqueue_scripts() {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return;
				}

				if( ! easycommerce_is_checkout() ) return;

				wp_enqueue_script(
					'mollie-js',
					'https://js.mollie.com/v1/mollie.js',
					array(),
					'1.0.0',
					true
				);

				wp_enqueue_script(
					'easycommerce-mollie',
					EASYCOMMERCE_ASSETS_URL . 'payment/js/mollie.js',
					array( 'mollie-js', 'jquery' ),
					EASYCOMMERCE_VERSION,
					true
				);
				wp_enqueue_style(
					'easycommerce-mollie-styles',
					EASYCOMMERCE_ASSETS_URL . 'payment/css/mollie.css',
					array(),
					time(),
					'all'
				);
			}

			public function localized( $vars ) {
				if ( ! $this->is_enabled() ) {
					return $vars;
				}

				if( ! easycommerce_is_checkout() ) return $vars;

				$test_mode      = Utility::get_option( 'payment', 'mollie', 'test_mode', '0' );
				$cart         = ( new Cart() )->get( true, false );
				$amounts      = $cart['amounts'] ?? array();

				$vars['mollie'] = array(
					'profile_id'   => Utility::get_option( 'payment', 'mollie', 'profile_id', '' ),
					'test_mode'    => $test_mode,
					'subtotal'     => $amounts['subtotal'] ?? 0,
					'discount'     => $amounts['discount_amount'] ?? 0,
					'shipping'     => $amounts['shipping_fee'] ?? 0,
					'product_tax'  => $amounts['tax'] ?? 0,
					'shipping_tax' => $amounts['shipping_tax'] ?? 0,
					'total'        => $amounts['total'] ?? 0,
				);

				return $vars;
			}

			public function payment_form(): string {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return '';
				}

				return '
                    <div id="easycommerce_mollie_payment_form">
                        <div id="easycommerce_cardHolder">
                            <span class="mollie-placeholder">Cardholder Name</span>
                        </div>
                        <div id="easycommerce_cardNumber">
                            <span class="mollie-placeholder">Card Number</span>
                        </div>
                        <div id="easycommerce_cvc">
                            <div id="easycommerce_expiryDate">
                                <span class="mollie-placeholder">MM/YY</span>
                            </div>
                            <div id="easycommerce_verificationCode">
                                <span class="mollie-placeholder">CVC</span>
                            </div>
                        </div>
                    </div>
                    <div id="easycommerce_mollie_payment_errors"></div>
                    ';
			}

			public function process_payment( $status, $order_id, $params, $customer_id ) {
				if ( $this->get_id() !== $params['easycommerce-payment_method'] ) {
					return $status;
				}

				$order = new Order( $order_id );
				if ( ! empty( $order->get_meta( 'mollie_payment_id' ) ) ) {
					return $status;
				}

				require_once EASYCOMMERCE_PLUGIN_DIR . '/vendor/autoload.php';

				if ( Utility::get_option( 'payment', 'mollie', 'default_order_status' ) ) {
					$default_order_status = Utility::get_option( 'payment', 'mollie', 'default_order_status' );
				} else {
					$default_order_status = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
				}
				$mollie                = new MollieApiClient();
				$easycommerce_currency = easycommerce_currency();
				$test_mode             = Utility::get_option( 'payment', 'mollie', 'test_mode', '0' );
				$api_key               = Utility::get_option( 'payment', 'mollie', 'api_key', '' );

				$mollie->setApiKey( $api_key );
				$cart         = ( new Cart() )->get( true, false );
				$total_amount = $cart['amounts']['total'] ?? 0;
				$metadata     = array();

				if ( ! empty( $cart['items'] ) ) {
					foreach ( $cart['items'] as $index => $item ) {
						$metadata[ "item_{$index}_name" ]       = $item['title'];
						$metadata[ "item_{$index}_quantity" ]   = (int) $item['quantity'];
						$metadata[ "item_{$index}_unit_price" ] = (float) $item['unit_price'];
						$metadata[ "item_{$index}_subtotal" ]   = (float) $item['subtotal'];
					}
				}

				$amounts = $cart['amounts'] ?? array();

				$metadata['subtotal']      = isset( $params['meta']['mollie_subtotal'] ) ? (float) $params['meta']['mollie_subtotal'] : ( $amounts['subtotal'] ?? 0 );
				$metadata['discount']      = isset( $params['meta']['mollie_discount'] ) ? (float) $params['meta']['mollie_discount'] : ( $amounts['discount_amount'] ?? 0 );
				$metadata['shipping']      = isset( $params['meta']['mollie_shipping'] ) ? (float) $params['meta']['mollie_shipping'] : ( $amounts['shipping_fee'] ?? 0 );
				$metadata['product_tax']   = isset( $params['meta']['mollie_product_tax'] ) ? (float) $params['meta']['mollie_product_tax'] : ( $amounts['tax'] ?? 0 );
				$metadata['shipping_tax']   = isset( $params['meta']['mollie_shipping_tax'] ) ? (float) $params['meta']['mollie_shipping_tax'] : ( $amounts['shipping_tax'] ?? 0 );
				$metadata['total']         = isset( $params['meta']['mollie_total'] ) ? (float) $params['meta']['mollie_total'] : ( $amounts['total'] ?? 0 );

				$payment = $mollie->payments->create(
					array(
						'method'      => 'creditcard',
						'amount'      => array(
							'currency' => $easycommerce_currency,
							'value'    => number_format( $total_amount, 2, '.', '' ),
						),
						'description' => "Order #$order_id",
						'redirectUrl' => easycommerce_order_redirect( $order_id ),
						'metadata'    => $metadata,
						'cardToken'   => $params['meta']['mollieToken'],
					)
				);

				$mollie_token = $params['meta']['mollieToken'];

				if ( $test_mode && 'open' === $payment->status ) {
					$order = new Order( $order_id );
					$order->add_meta( 'mollie_payment_id', $payment->id );
					$order->add_meta( 'payment_method_name', 'Mollie' );
					$order->add_meta( 'payment_method', $this->get_id() );

					do_action( "easycommerce-{$this->id}_payment_complete", $mollie_token, $order_id, $total_amount, $customer_id, $params );

					return $default_order_status;
				} elseif ( ! $test_mode && 'paid' === $payment->status ) {
					$order = new Order( $order_id );
					$order->add_meta( 'mollie_payment_id', $payment->id );
					$order->add_meta( 'payment_method_name', 'Mollie' );
					$order->add_meta( 'payment_method', $this->get_id() );

					do_action( "easycommerce-{$this->id}_payment_complete", $mollie_token, $order_id, $total_amount, $customer_id, $params );

					return $default_order_status;
				} else {
					throw new Exception( 'Mollie Payment failed: ' . $payment->status );
				}
			}

			public function insert_transaction( $transaction_id, $order_id, $total_amount, $customer_id, $params ) {
				$easycommerce_currency = easycommerce_currency();
				$txn                   = new Transaction();
				$existing_transaction  = $txn->get_by_order_id( $order_id );

				if ( $existing_transaction ) {
					return;
				}

				$txn->add(
					$order_id,
					array(
						'transaction_id'  => $transaction_id,
						'customer_id'     => $customer_id,
						'payment_gateway' => 'mollie',
						'amount'          => $total_amount,
						'currency'        => $easycommerce_currency,
						'type'            => 'payment',
						'status'          => 'completed',
					)
				);
			}

			public function refund( $order_id, $reason, $amount ) {
				require_once EASYCOMMERCE_PLUGIN_DIR . '/vendor/autoload.php';

				$order = new Order( $order_id );

				$mollie_payment_id = $order->get_meta( 'mollie_payment_id' );

				if ( empty( $mollie_payment_id ) ) {
					return false;
				}

				$api_key = Utility::get_option( 'payment', 'mollie', 'api_key', '' );

				if ( empty( $api_key ) ) {
					return false;
				}

				$mollie = new MollieApiClient();
				$mollie->setApiKey( $api_key );

				try {
					$payment = $mollie->payments->get( $mollie_payment_id );

					// if ( ! $payment->isPaid() ) {
					// 	return false;
					// }

					$currency       = $payment->amount->currency;
					$payment_amount = (float) $payment->amount->value;
					$refund_amount  = (float) $amount;

					if ( $refund_amount <= 0 || $refund_amount > $payment_amount ) {
						return false;
					}

					$refund = $payment->refund(
						[
							'amount' => [
								'currency' => $currency,
								'value'    => number_format( $refund_amount, 2, '.', '' ),
							],
							'metadata' => [
								'order_id' => $order_id,
								'reason'   => $reason,
							],
						]
					);

					$order->add_meta( 'mollie_refund_id', $refund->id );
					$order->add_meta( 'mollie_refund_status', $refund->status );

					do_action(
						'easycommerce_mollie_refund_complete',
						$refund->id,
						$order_id,
						$refund_amount,
						$reason
					);

					return true;

				} catch ( ApiException $e ) {
					return false;
				}
			}

			public function refund_transaction_id( $order_id ) {
				$order = new Order( $order_id );
				return $order->get_meta( 'mollie_refund_id' );
			}
		}

		new Mollie();
	},
	9
);

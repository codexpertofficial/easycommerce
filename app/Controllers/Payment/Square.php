<?php
namespace EasyCommerce\Controllers\Payment;

use EasyCommerce\Models\Cart;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;
use Exception;
use Square\Environment;
use EasyCommerce\Abstracts\Payment_Method;
use Square\Models\Address;
use Square\Models\CreateCustomerRequest;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;
use Square\Models\RefundPaymentRequest;
use Square\SquareClient;
use Square\Models\CreateOrderRequest;
use Square\Models\Order as SquareOrder;
use Square\Models\OrderLineItem;
use Square\Models\OrderLineItemDiscount;
use Square\Models\OrderServiceCharge;
use Square\Models\OrderFulfillment;
use Square\Models\OrderFulfillmentShipmentDetails;
use Square\Models\OrderFulfillmentRecipient;

add_action(
	'init',
	function () {
		class Square extends Payment_Method {
			protected $refund_transaction_id;

			/**
			 * Constructor
			 */
			public function __construct() {
				parent::__construct(
					'square',
					__( 'Square', 'easycommerce' ),
					__( 'Pay via Square', 'easycommerce' )
				);

				add_filter( 'easycommerce_payment_method_square_icon', array( $this, 'square_logo_url' ) );
				add_action( 'update_option', function( $option_name ) {
					if ( strpos( $option_name, 'square' ) !== false ) {
						delete_transient( 'easycommerce_square_location_currency' );
					}
				} );
			}

			public function square_logo_url() {
				$icon_id  = Utility::get_option( 'payment', 'square', 'square_logo', '' );
				$icon_url = '';
				if ( wp_attachment_is_image( $icon_id ) ) {
					$icon_url = wp_get_attachment_url( $icon_id );
				}

				return $icon_url ?: EASYCOMMERCE_ASSETS_URL . 'payment/img/square.svg';
			}

			public function settings(): array {
				if ( ! $this->is_enabled() ) {
					return array();
				}

				return array(
					array(
						'label'  => __( 'Basic', 'easycommerce' ),
						'fields' => array(
							'sandbox'              => array(
								'id'          => 'sandbox',
								'type'        => 'switch',
								'label'       => __( 'Sandbox Mode', 'easycommerce' ),
								'description' => __( 'Enable to process test transactions without real charges.', 'easycommerce' ),
							),
							'square_logo'          => array(
								'id'          => 'square_logo',
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
						'label'  => __( 'Credentials', 'easycommerce' ),
						'fields' => array(
							'application_id' => array(
								'id'          => 'application_id',
								'type'        => 'text',
								'label'       => __( 'Application ID', 'easycommerce' ),
								'placeholder' => __( 'sq0idp-RmT8xxxx', 'easycommerce' ),
								'description' => __( 'Your Square application ID. Get this from your <a href="https://developer.squareup.com/apps" target="_blank">Developer Console</a> under the Credentials section.', 'easycommerce' ),
							),
							'access_token'   => array(
								'id'          => 'access_token',
								'type'        => 'text',
								'label'       => __( 'Access Token', 'easycommerce' ),
								'placeholder' => __( 'EAAAEPjXxxxx', 'easycommerce' ),
								'description' => __( 'Your Square access token for API authentication. Found in the <a href="https://developer.squareup.com/apps" target="_blank">Developer Console</a> Credentials section.', 'easycommerce' ),
							),
							'location_id'    => array(
								'id'          => 'location_id',
								'type'        => 'text',
								'label'       => __( 'Location ID', 'easycommerce' ),
								'placeholder' => __( 'L1ABC2xxxx', 'easycommerce' ),
								'description' => __( 'Your Square location ID. View this in the <a href="https://developer.squareup.com/apps" target="_blank">Developer Console</a> under the Locations section.', 'easycommerce' ),
							),
						),
					),
				);
			}

			public function is_available() {
				$application_id = Utility::get_option( 'payment', 'square', 'application_id', '' );
				$access_token   = Utility::get_option( 'payment', 'square', 'access_token', '' );
				$location_id    = Utility::get_option( 'payment', 'square', 'location_id', '' );

				if ( empty( $application_id ) || empty( $access_token ) || empty( $location_id ) ) {
					return false;
				}

				$square_currency = get_transient( 'easycommerce_square_location_currency' );

				if ( ! $square_currency ) {
					$sandbox = Utility::get_option( 'payment', 'square', 'sandbox', '1' );
					$client  = new SquareClient(
						array(
							'accessToken' => Utility::get_option( 'payment', 'square', 'access_token', '' ),
							'environment' => '1' === $sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
						)
					);

					$response = $client->getLocationsApi()->listLocations();
					if ( $response->isSuccess() ) {
						foreach ( $response->getResult()->getLocations() as $location ) {
							if ( $location->getId() === $location_id ) {
								$square_currency = $location->getCurrency();
								set_transient( 'easycommerce_square_location_currency', $square_currency, HOUR_IN_SECONDS );
								break;
							}
						}
					}
				}

				return $square_currency && easycommerce_currency() === $square_currency;
			}

			public function enqueue_scripts() {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return;
				}

				if( ! easycommerce_is_checkout() ) return;

				$sandbox       = Utility::get_option( 'payment', 'square', 'sandbox', '1' );
				$square_js_url = '1' === $sandbox ? 'https://sandbox.web.squarecdn.com/v1/square.js' : 'https://web.squarecdn.com/v1/square.js';

				if ( easycommerce_is_checkout() ) {
					wp_enqueue_script(
						'square-js',
						$square_js_url,
						array(),
						'1.0.0',
						true
					);

					wp_enqueue_script(
						'easycommerce-square',
						EASYCOMMERCE_ASSETS_URL . 'payment/js/square.js',
						array( 'square-js', 'jquery' ),
						EASYCOMMERCE_VERSION,
						true
					);
				}
			}

			public function localized( $vars ) {
				if ( ! $this->is_enabled() ) {
					return $vars;
				}

				if( ! easycommerce_is_checkout() ) return $vars;

				$vars['square'] = array(
					'application_id' => Utility::get_option( 'payment', 'square', 'application_id', '' ),
					'location_id'    => Utility::get_option( 'payment', 'square', 'location_id', '' ),
				);

				return $vars;
			}

			public function payment_form(): string {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return '';
				}

				return '<div id="easycommerce_square_payment_form"></div><div id="easycommerce_square_payment_errors"></div>';
			}

			private function to_square_amount( float $amount, string $currency ): int {
				$zero_decimal = array( 'JPY' );
				if ( in_array( strtoupper( $currency ), $zero_decimal, true ) ) {
					return (int) round( $amount );
				}
				return (int) round( $amount * 100 );
			}

			public function process_payment( $status, $order_id, $params, $customer_id ) {
				if ( $this->get_id() !== $params['easycommerce-payment_method'] ) {
					return $status;
				}

				$order = new Order( $order_id );
				if ( ! empty( $order->get_meta( 'square_payment_id' ) ) ) {
					return $status;
				}

				// Prevent re-processing if order is already completed or processing
				$current_status = $order->get_status();
				if ( in_array( $current_status, array( 'completed', 'processing' ), true ) ) {
					throw new Exception( 'This order has already been processed.' );
				}

				if ( Utility::get_option( 'payment', 'square', 'default_order_status' ) ) {
					$default_order_status = Utility::get_option( 'payment', 'square', 'default_order_status' );
				} else {
					$default_order_status = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
				}

				$access_token = Utility::get_option( 'payment', 'square', 'access_token', '' );
				$sandbox      = Utility::get_option( 'payment', 'square', 'sandbox', '1' );
				$currency     = easycommerce_currency();
				$client       = new SquareClient(
					array(
						'accessToken' => $access_token,
						'environment' => '1' === $sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
					)
				);

				$locations = $client->getLocationsApi()->listLocations();

				if ( $locations->isSuccess() ) {
					$cart         = ( new Cart() )->get( true, false );
					$total_amount = $cart['amounts']['total'];
					$metadata     = array();

					// Calculate total amount and set metadata
					foreach ( $cart['items'] as $index => $item ) {
						$metadata[ "item_{$index}_name" ]       = $item['title'];
						$metadata[ "item_{$index}_quantity" ]   = (int) $item['quantity'];
						$metadata[ "item_{$index}_unit_price" ] = (float) $item['unit_price'];
						$metadata[ "item_{$index}_subtotal" ]   = (float) $item['subtotal'];
					}

					$amounts = $cart['amounts'] ?? array();

					$metadata['subtotal']      = isset( $params['meta']['square_subtotal'] ) ? (float) $params['meta']['square_subtotal'] : ( $amounts['subtotal'] ?? 0 );
					$metadata['discount']      = isset( $params['meta']['square_discount'] ) ? (float) $params['meta']['square_discount'] : ( $amounts['discount_amount'] ?? 0 );
					$metadata['shipping']      = isset( $params['meta']['square_shipping'] ) ? (float) $params['meta']['square_shipping'] : ( $amounts['shipping_fee'] ?? 0 );
					$metadata['product_tax']   = isset( $params['meta']['square_product_tax'] ) ? (float) $params['meta']['square_product_tax'] : ( $amounts['tax'] ?? 0 );
					$metadata['shipping_tax']  = isset( $params['meta']['square_shipping_tax'] ) ? (float) $params['meta']['square_shipping_tax'] : ( $amounts['shipping_tax'] ?? 0 );
					$metadata['total']         = isset( $params['meta']['square_total'] ) ? (float) $params['meta']['square_total'] : ( $amounts['total'] ?? 0 );

					$billing_address = $params['billing_address'];

					// Create customer
					$address = new Address();
					$address->setAddressLine1( $billing_address['address_1'] );
					$address->setAddressLine2( $billing_address['address_2'] );

					$body = new CreateCustomerRequest();
					$body->setGivenName( $billing_address['first_name'] );
					$body->setFamilyName( $billing_address['last_name'] );
					$body->setAddress( $address );

					$customer_response = $client->getCustomersApi()->createCustomer( $body );

					if ( $customer_response->isSuccess() ) {
						$result             = $customer_response->getResult();
						$square_customer_id = $result->getCustomer()->getId();

						$square_token = $params['meta']['squareToken'];
						$location_id  = Utility::get_option( 'payment', 'square', 'location_id', '' );

						$line_items = array();
						foreach ( $cart['items'] as $item ) {
							$base_price = new Money();
							$base_price->setAmount( $this->to_square_amount( $item['unit_price'], $currency ) );
							$base_price->setCurrency( $currency );

							$line_item = new OrderLineItem( (string) $item['quantity'] );
							$line_item->setName( $item['title'] );
							$line_item->setBasePriceMoney( $base_price );
							$line_items[] = $line_item;
						}

						$square_order = new SquareOrder( $location_id );
						$square_order->setLineItems( $line_items );
						$square_order->setReferenceId( (string) $order_id );

						if ( ! empty( $metadata['discount'] ) && $metadata['discount'] > 0 ) {
							$discount_money = new Money();
							$discount_money->setAmount( $this->to_square_amount( $metadata['discount'], $currency ) );
							$discount_money->setCurrency( $currency );

							$discount = new OrderLineItemDiscount();
							$discount->setName( 'Discount' );
							$discount->setAmountMoney( $discount_money );
							$discount->setScope( 'ORDER' );
							$square_order->setDiscounts( array( $discount ) );
						}

						$service_charges = array();

						if ( ! empty( $metadata['shipping'] ) && $metadata['shipping'] > 0 ) {
							$shipping_money = new Money();
							$shipping_money->setAmount( $this->to_square_amount( $metadata['shipping'], $currency ) );
							$shipping_money->setCurrency( $currency );

							$shipping_charge = new OrderServiceCharge();
							$shipping_charge->setName( 'Shipping' );
							$shipping_charge->setAmountMoney( $shipping_money );
							$shipping_charge->setCalculationPhase( 'TOTAL_PHASE' );
							$service_charges[] = $shipping_charge;
						}

						if ( ! empty( $metadata['product_tax'] ) && $metadata['product_tax'] > 0 ) {
							$product_tax_money = new Money();
							$product_tax_money->setAmount( $this->to_square_amount( $metadata['product_tax'], $currency ) );
							$product_tax_money->setCurrency( $currency );

							$product_tax_charge = new OrderServiceCharge();
							$product_tax_charge->setName( 'Product Tax' );
							$product_tax_charge->setAmountMoney( $product_tax_money );
							$product_tax_charge->setCalculationPhase( 'TOTAL_PHASE' );
							$service_charges[] = $product_tax_charge;
						}

						if ( ! empty( $metadata['shipping_tax'] ) && $metadata['shipping_tax'] > 0 ) {
							$shipping_tax_money = new Money();
							$shipping_tax_money->setAmount( $this->to_square_amount( $metadata['shipping_tax'], $currency ) );
							$shipping_tax_money->setCurrency( $currency );

							$shipping_tax_charge = new OrderServiceCharge();
							$shipping_tax_charge->setName( 'Shipping Tax' );
							$shipping_tax_charge->setAmountMoney( $shipping_tax_money );
							$shipping_tax_charge->setCalculationPhase( 'TOTAL_PHASE' );
							$service_charges[] = $shipping_tax_charge;
						}

						if ( ! empty( $service_charges ) ) {
							$square_order->setServiceCharges( $service_charges );
						}

						$recipient = new OrderFulfillmentRecipient();
						$recipient->setDisplayName( trim( $billing_address['first_name'] . ' ' . $billing_address['last_name'] ) );

						if ( ! empty( $billing_address['email'] ) ) {
							$recipient->setEmailAddress( $billing_address['email'] );
						}
						if ( ! empty( $billing_address['phone'] ) ) {
							$recipient->setPhoneNumber( $billing_address['phone'] );
						}

						$shipment_details = new OrderFulfillmentShipmentDetails();
						$shipment_details->setRecipient( $recipient );

						$fulfillment = new OrderFulfillment();
						$fulfillment->setType( 'SHIPMENT' );
						$fulfillment->setState( 'PROPOSED' );
						$fulfillment->setShipmentDetails( $shipment_details );

						$square_order->setFulfillments( array( $fulfillment ) );

						$order_request = new CreateOrderRequest();
						$order_request->setOrder( $square_order );
						$order_request->setIdempotencyKey( uniqid() );

						$square_order_id     = null;
						$square_order_total  = $this->to_square_amount( $total_amount, $currency );
						$order_response      = $client->getOrdersApi()->createOrder( $order_request );

						if ( $order_response->isSuccess() ) {
							$square_order_result = $order_response->getResult()->getOrder();
							$square_order_id     = $square_order_result->getId();

							$net_amounts = $square_order_result->getNetAmountDueMoney();
							if ( $net_amounts ) {
								$square_order_total = $net_amounts->getAmount();
							} else {
								$total_money = $square_order_result->getTotalMoney();
								if ( $total_money ) {
									$square_order_total = $total_money->getAmount();
								}
							}
						}

						$amount_money = new Money();
						$amount_money->setAmount( $square_order_total );
						$amount_money->setCurrency( $currency );

						$body = new CreatePaymentRequest( (string) $square_token, uniqid() );
						$body->setAmountMoney( $amount_money );
						$body->setAutocomplete( true );
						$body->setCustomerId( $square_customer_id );
						$body->setReferenceId( (string) $order_id );
						if ( $square_order_id ) {
							$body->setOrderId( $square_order_id );
						}

						$api_response = $client->getPaymentsApi()->createPayment( $body );

						if ( $api_response->isSuccess() ) {
							$result      = $api_response->getResult();
							$payment     = $result->getPayment();
							$payment_id  = $payment->getId();
							$status      = $payment->getStatus();
							$receipt_url = $payment->getReceiptUrl();

							// Update order and add meta
							$order = new Order( $order_id );
							$order->add_meta( 'square_payment_id', $payment_id );
							$order->add_meta( 'square_payment_status', $status );
							$order->add_meta( 'square_receipt_url', $receipt_url );
							$order->add_meta( 'payment_method_name', 'Square' );
							$order->add_meta( 'payment_method', $this->get_id() );

							foreach ( $metadata as $meta_key => $meta_value ) {
								$order->add_meta( $meta_key, $meta_value );
							}

							$approved_money = $payment->getApprovedMoney();
							$currency       = $approved_money->getCurrency() ?? easycommerce_currency();

							$params['meta']['money_currency'] = $currency;

							do_action( "easycommerce-{$this->id}_payment_complete", $result, $order_id, $total_amount, $customer_id, $params );

							return $default_order_status;
						}
					}
				}

				return $status;
			}

			public function insert_transaction( $transaction_id, $order_id, $total_amount, $customer_id, $params ) {
				$txn = new Transaction();
				if ( $txn->get_by_order_id( $order_id ) ) {
					return;
				}

				$currency     = $params['meta']['money_currency'];
				$square_token = $params['meta']['squareToken'];

				$txn->add(
					$order_id,
					array(
						'transaction_id'  => $square_token,
						'customer_id'     => $customer_id,
						'payment_gateway' => 'square',
						'amount'          => $total_amount,
						'currency'        => $currency,
						'type'            => 'payment',
						'status'          => 'completed',
					)
				);
			}

			public function refund( $order_id, $reason, $amount ) {
				$order          = new Order( $order_id );
				$payment_method = $order->get_payment_method();

				if ( $this->get_id() !== $payment_method ) {
					return false;
				}

				$payment_id = $order->get_meta( 'square_payment_id' );
				$sandbox    = Utility::get_option( 'payment', 'square', 'sandbox', '1' );
				try {
					$access_token = Utility::get_option( 'payment', 'square', 'access_token', '' );
					$client       = new SquareClient(
						array(
							'accessToken' => $access_token,
							'environment' => '1' === $sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
						)
					);

					$payment_response = $client->getPaymentsApi()->getPayment( $payment_id );
					if ( $payment_response->isSuccess() ) {
						$payment  = $payment_response->getResult()->getPayment();
						$currency = $payment->getApprovedMoney()->getCurrency();
						$status   = $payment->getStatus();

						if ( strtoupper( $status ) !== 'COMPLETED' ) {
							return false;
						}
					} else {
						return false;
					}

					$amount_money = new Money();
					$amount_money->setAmount( $this->to_square_amount( (float) $amount, $currency ) );
					$amount_money->setCurrency( $currency );

					$body = new RefundPaymentRequest( uniqid(), $amount_money );
					$body->setPaymentId( $payment_id );
					$body->setReason( $reason );

					$api_response = $client->getRefundsApi()->refundPayment( $body );
					if ( $api_response->isSuccess() ) {
						$result    = $api_response->getResult();
						$refund_id = $result->getRefund()->getId();

						$this->refund_transaction_id = $refund_id;

						do_action( 'easycommerce_square_refund_complete', $refund_id, $order_id, $amount, $reason );
						return true;
					} else {
						return false;
					}
				} catch ( \Throwable $e ) {
					return false;
				}
			}

			public function refund_transaction_id() {
				return $this->refund_transaction_id;
			}

			public function supports_refund() {
				return true;
			}
		}

		new Square();
	},
	9
);

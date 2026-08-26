<?php
namespace EasyCommerce\Controllers\Payment;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;
use EasyCommerce\Abstracts\Payment_Method;
use Exception;

add_action(
	'init',
	function () {
		class PayPal extends Payment_Method {
			protected $refund_transaction_id;

			public function __construct() {
				parent::__construct(
					'paypal',
					__( 'PayPal', 'easycommerce' ),
					__( 'Pay via paypal', 'easycommerce' )
				);

				add_filter( 'easycommerce_payment_method_paypal_icon', array( $this, 'paypal_logo_url' ) );
			}

			public function paypal_logo_url() {
				$icon_id  = Utility::get_option( 'payment', 'paypal', 'paypal_logo', '' );
				$icon_url = '';
				if ( wp_attachment_is_image( $icon_id ) ) {
					$icon_url = wp_get_attachment_url( $icon_id );
				}

				return $icon_url ?: EASYCOMMERCE_ASSETS_URL . 'payment/img/paypal.svg';
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
							'paypal_logo'          => array(
								'id'          => 'paypal_logo',
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
								'placeholder' => __( 'PayPal', 'easycommerce' ),
								'default'     => __( 'PayPal', 'easycommerce' ),
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
						'label'  => __( 'PayPal Credentials', 'easycommerce' ),
						'fields' => array(
							'client_id'          => array(
								'id'          => 'client_id',
								'type'        => 'text',
								'label'       => __( 'Client ID', 'easycommerce' ),
								'placeholder' => __( 'AZDt2lKXsxxxx', 'easycommerce' ),
								'description' => __( 'Your PayPal client ID for API authentication. Get this from your <a href="https://developer.paypal.com/dashboard/applications/sandbox" target="_blank">PayPal Developer Dashboard</a> under Apps & Credentials.', 'easycommerce' ),
							),
							'client_secret'      => array(
								'id'          => 'client_secret',
								'type'        => 'text',
								'label'       => __( 'Client Secret Key', 'easycommerce' ),
								'placeholder' => __( 'EL5G3xxxx', 'easycommerce' ),
								'description' => __( 'Your PayPal client secret for API authentication. Found in the <a href="https://developer.paypal.com/dashboard/applications/sandbox" target="_blank">PayPal Developer Dashboard</a> under Apps & Credentials.', 'easycommerce' ),
							),
							'paypal_merchant_id' => array(
								'id'          => 'paypal_merchant_id',
								'type'        => 'text',
								'label'       => __( 'PayPal Merchant ID', 'easycommerce' ),
								'placeholder' => __( 'QBKKG4xxxx', 'easycommerce' ),
								'description' => __( 'Your PayPal merchant account ID. View this in your <a href="https://www.paypal.com/businessprofile/settings" target="_blank">PayPal Business Profile</a> settings.', 'easycommerce' ),
							),
						),
					),
				);
			}

			public function is_available() {
				$client_id     = Utility::get_option( 'payment', 'paypal', 'client_id', '' );
				$client_secret = Utility::get_option( 'payment', 'paypal', 'client_secret', '' );

				return ! empty( $client_id ) && ! empty( $client_secret );
			}

			public function enqueue_scripts() {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return;
				}

				if( ! easycommerce_is_checkout() ) return;

				$paypal_client_id = Utility::get_option( 'payment', 'paypal', 'client_id', '' );

				wp_enqueue_script(
					'paypal-sdk',
					add_query_arg(
						array( 'client-id' => $paypal_client_id, 'currency' => easycommerce_currency() ),
						'https://www.paypal.com/sdk/js',
					),
					array(),
					null,
					true
				);

				wp_enqueue_script(
					'easycommerce-paypal',
					EASYCOMMERCE_ASSETS_URL . 'payment/js/paypal.js',
					array( 'paypal-sdk', 'jquery', 'wp-i18n', 'easycommerce_checkout' ),
					EASYCOMMERCE_VERSION,
					true
				);

				wp_set_script_translations( 'easycommerce-paypal', 'easycommerce', EASYCOMMERCE_PLUGIN_DIR . 'languages' );
			}

			public function payment_form(): string {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return '';
				}

				return '<div id="easycommerce_paypal_payment_form"></div><div id="easycommerce_paypal_payment_errors"></div>';
			}

			public function process_payment( $status, $order_id, $params, $customer_id ) {
				if ( $this->id !== $params['easycommerce-payment_method'] ) {
					return $status;
				}

				$paypal_payment_id = $params['meta']['paypalPaymentID'] ?? '';

				if ( empty( $paypal_payment_id ) ) {
					return 'failed';
				}

				$order = new Order( $order_id );

				if ( ! empty( $order->get_meta( 'paypalPaymentID' ) ) ) {
					return $status;
				}

				if ( Utility::get_option( 'payment', 'paypal', 'default_order_status' ) ) {
					$default_order_status = Utility::get_option( 'payment', 'paypal', 'default_order_status' );
				} else {
					$default_order_status = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
				}

				$total_amount = $order->get_total();

				// Verify payment with PayPal before processing
				$access_token = $this->get_paypal_access_token();
				if ( ! $access_token ) {
					return 'failed';
				}

				$paypal_url  = $this->get_paypal_url();
				$order_response = wp_remote_get(
					"$paypal_url/v2/checkout/orders/{$paypal_payment_id}",
					[ 'headers' => [ 'Authorization' => "Bearer {$access_token}" ] ]
				);

				$order_body = json_decode( wp_remote_retrieve_body( $order_response ), true );

				if ( ( $order_body['status'] ?? '' ) !== 'COMPLETED' ) {
					return 'failed';
				}

				$captured_amount = $order_body['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;

				$intended_raw    = $order->get_meta( '_ec_paypal_intended_amount' );
				$compare_against = ( '' !== (string) $intended_raw )
					? (float) $intended_raw
					: (float) $total_amount;

				if ( number_format( $compare_against, 2, '.', '' ) !== number_format( (float) $captured_amount, 2, '.', '' ) ) {
					return 'failed';
				}

				$order->add_meta( 'payment_method', $this->get_id() );
				$order->add_meta( 'payment_method_name', 'PayPal' );
				$order->add_meta( 'paypalPaymentID', $paypal_payment_id );

				do_action( "easycommerce-{$this->id}_payment_complete", $paypal_payment_id, $order_id, $total_amount, $customer_id, $params );
				return $default_order_status;
			}

			private function get_paypal_url() {
				$sandbox = Utility::get_option( 'payment', 'paypal', 'sandbox', '1' );
				return ( '1' === $sandbox )
					? 'https://api-m.sandbox.paypal.com'
					: 'https://api-m.paypal.com';
			}

			private function get_paypal_access_token() {
				$client_id     = trim( Utility::get_option( 'payment', 'paypal', 'client_id', '' ) );
				$client_secret = trim( Utility::get_option( 'payment', 'paypal', 'client_secret', '' ) );
				$paypal_url    = $this->get_paypal_url();

				$auth_response = wp_remote_post(
					"$paypal_url/v1/oauth2/token",
					[
						'headers' => [
							'Authorization' => 'Basic ' . base64_encode( "{$client_id}:{$client_secret}" ),
							'Content-Type'  => 'application/x-www-form-urlencoded',
						],
						'body' => 'grant_type=client_credentials',
					]
				);

				if ( is_wp_error( $auth_response ) ) {
					return null;
				}

				$auth_body = json_decode( wp_remote_retrieve_body( $auth_response ), true );

				return $auth_body['access_token'] ?? null;
			}

			public function insert_transaction( $transaction_id, $order_id, $total_amount, $customer_id, $params ) {
				$txn = new Transaction();
				if ( $txn->get_by_order_id( $order_id ) ) {
					return;
				}

				$txn->add(
					$order_id,
					array(
						'transaction_id'  => $transaction_id,
						'customer_id'     => $customer_id,
						'payment_gateway' => 'paypal',
						'amount'          => $total_amount,
						'currency'        => easycommerce_currency(),
						'type'            => 'payment',
						'status'          => 'completed',
					)
				);
			}

			public function refund( $order_id, $reason, $amount ) {
				$order          = new Order( $order_id );
				$payment_id     = $order->get_meta( 'paypalPaymentID' );
				$payment_method = $order->get_meta( 'payment_method_name' );

				if ( 'PayPal' !== $payment_method || empty( $payment_id ) ) {
					return false;
				}

				$access_token = $this->get_paypal_access_token();
				if ( ! $access_token ) {
					throw new Exception( 'PayPal authentication failed.' );
				}

				$paypal_url = $this->get_paypal_url();

				try {
					$order_respose = wp_remote_get(
						"$paypal_url/v2/checkout/orders/{$payment_id}",
						[
							'headers' => [
								'Authorization' => "Bearer {$access_token}",
							],
						]
					);

					$order_body = json_decode( wp_remote_retrieve_body( $order_respose ), true );

					if ( empty( $order_body['purchase_units'][0]['payments']['captures'][0]['id'] ) ) {
						throw new Exception( 'PayPal capture ID not found.' );
					}

					$capture_id = $order_body['purchase_units'][0]['payments']['captures'][0]['id'];

					$refund_response = wp_remote_post(
						"$paypal_url/v2/payments/captures/{$capture_id}/refund",
						[
							'headers' => [
								'Authorization' => "Bearer {$access_token}",
								'Content-Type'  => 'application/json',
							],
							'body' => wp_json_encode(
								[
									'amount' => [
										'value'         => number_format( (float) $amount, 2, '.', '' ),
										'currency_code' => easycommerce_currency(),
									],
									'note_to_payer' => $reason,
								]
							),
						]
					);

					if ( is_wp_error( $refund_response ) ) {
						throw new Exception( 'PayPal refund request failed.' );
					}

					$refund_body = json_decode( wp_remote_retrieve_body( $refund_response ), true );

					if ( empty( $refund_body['id'] ) ) {
						throw new Exception(
							$refund_body['message'] ?? 'PayPal refund failed without ID.'
						);
					}

					$this->refund_transaction_id = $refund_body['id'];

					do_action(
						'easycommerce_paypal_refund_complete',
						$refund_body['id'],
						$order_id,
						$amount,
						$reason
					);

					return true;

				} catch ( Exception $e ) {
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

		new PayPal();
	},
	9
);

<?php

namespace EasyCommerce\Controllers\Payment;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Payment_Method;
use EasyCommerce\Controllers\Payment\Stripe\Helpers\Webhook;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Transaction;
use EasyCommerce\Controllers\Payment\Stripe\API\Webhook as StripeWebhook;
use EasyCommerce\Controllers\Payment\Stripe\API\Payment_Intent as StripePaymentIntent;
use EasyCommerce\Controllers\Payment\Stripe\Helpers\Payment_Methods;
use EasyCommerce\Traits\Rest;
use WP_REST_Server;
use Exception;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\StripeClient;
use Throwable;

add_action(
	'init',
	function () {
		class Stripe extends Payment_Method {
			use Rest;

			protected $id;

			protected $api_client;

			protected $payment_methods_helper;

			protected $refund_transaction_id;

			public function __construct() {
				$this->id = 'stripe';

				parent::__construct(
					$this->id,
					__( 'Stripe', 'easycommerce' ),
					__( 'Pay via stripe', 'easycommerce' )
				);

				if ( ! empty( easycommerce_stripe_get_secret_key() ) ) {
					$this->api_client = easycommerce_stripe_get_api_client();
					if ( $this->api_client ) {
						$this->payment_methods_helper = new Payment_Methods();
					}
				}
				add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
				add_filter( 'easycommerce_payment_method_stripe_icon', array( $this, 'stripe_logo_url' ) );
				add_action( 'easycommerce_option_updated', array( $this, 'manage_webhook' ) );
				add_action( 'admin_init', array( $this, 'ensure_payment_methods_sync' ) );
			}

			/**
			 * Registers all REST API endpoints for the Stripe payment gateway.
			 *
			 * @return void
			 */
			public function register_endpoints() {
				$this->register_webhook_endpoint_routes();
				$this->register_payment_intent_endpoint_routes();
			}

			/**
			 * Registers REST API routes for handling webhooks.
			 * This method defines a single route for handling incoming webhooks.
			 *
			 * @see https://docs.stripe.com/webhooks
			 * @see https://docs.stripe.com/webhooks#webhooks-summary:~:text=Create%20a%20webhook%20endpoint%20handler%20to%20receive%20event%20data%20POST%20requests.
			 *
			 * @return void
			 */
			public function register_webhook_endpoint_routes() {
				$webhook = new StripeWebhook();

				register_rest_route(
					$this->namespace,
					'/stripe/webhook',
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $webhook, 'listen' ),
						'permission_callback' => '__return_true',
						'args'                => array(),
					)
				);
			}

			/**
			 * Registers REST API routes for handling Payment Intents.
			 * This method defines routes for creating and updating Payment Intents.
			 *
			 * @return void
			 */
			public function register_payment_intent_endpoint_routes() {
				$payment_intent      = new StripePaymentIntent();
				$permission_callback = array( $this, 'payment_intent_permission_callback' );

				register_rest_route(
					$this->namespace,
					'/stripe/payment-intent',
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $payment_intent, 'create' ),
						'permission_callback' => $permission_callback,
						'args'                => array(),
					)
				);

				register_rest_route(
					$this->namespace,
					'/stripe/payment-intent/update',
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $payment_intent, 'update' ),
						'permission_callback' => $permission_callback,
						'args'                => array(),
					)
				);
			}

			/**
			 * Permission callback for payment intent endpoints.
			 *
			 * Logged-in users are authenticated by WordPress REST API cookie auth.
			 * Guest users must supply a valid wp_rest nonce via the X-WP-Nonce header
			 * to prove the request originates from the checkout page.
			 *
			 * @param \WP_REST_Request $request
			 * @return bool
			 */
			public function payment_intent_permission_callback( \WP_REST_Request $request ): bool {
				if ( is_user_logged_in() ) {
					return true;
				}

				$nonce = $request->get_header( 'x-wp-nonce' ) ?: $request->get_param( '_wpnonce' );

				return ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wp_rest' ) !== false;
			}

			/**
			 * Retrieves the URL of the Stripe payment method logo.
			 *
			 * @return string The URL of the logo image, or empty string if not found or not an image.
			 */
			public function stripe_logo_url() {
				$icon_id = Utility::get_option( 'payment', 'stripe', 'stripe_logo', '' );

				$icon_url = '';
				if ( wp_attachment_is_image( $icon_id ) ) {
					$icon_url = wp_get_attachment_url( $icon_id );
				}

				return $icon_url ?: EASYCOMMERCE_ASSETS_URL . 'payment/img/stripe.svg';
			}

			/**
			 * Manages webhook registration when Stripe payment settings are updated.
			 *
			 * @param string $option The option name that was updated.
			 *
			 * @return void
			 */
			public function manage_webhook( $option ) {
				if ( 'easycommerce-payment-stripe' !== $option ) {
					return;
				}

				if ( empty( easycommerce_stripe_get_webhook_secret() ) ) {
					$webhook = new Webhook();
					$webhook->register();
				}
			}

			/**
			 * Ensures payment methods are in sync when admin loads
			 *
			 * @return void
			 */
			public function ensure_payment_methods_sync() {

				if ( ! is_admin() ) {
					return;
				}

				if ( empty( easycommerce_stripe_get_secret_key() ) || ! easycommerce_stripe_get_api_client() ) {
					return;
				}

				$page    = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
				$menu    = isset( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';
				$submenu = isset( $_GET['submenu'] ) ? sanitize_text_field( wp_unslash( $_GET['submenu'] ) ) : '';

				if ( 'easycommerce-settings' === $page && 'payment' === $menu && 'stripe' === $submenu ) {
					$payment_methods_helper = new Payment_Methods();
					$payment_methods_helper->force_sync_payment_methods();
				}
			}

			/**
			 * Returns the settings configuration for the Stripe payment method.
			 *
			 * @return array Array of settings fields organized by sections.
			 */
			public function settings(): array {
				if ( ! $this->is_enabled() ) {
					return array();
				}

				$settings = array(
					array(
						'label'  => __( 'Basic', 'easycommerce' ),
						'fields' => array(
							'is_sandbox_mode'        => array(
								'id'          => 'is_sandbox_mode',
								'type'        => 'switch',
								'label'       => __( 'Sandbox Mode', 'easycommerce' ),
								'description' => __( 'Enable to process test transactions without real charges.', 'easycommerce' ),
							),
							'stripe_logo'            => array(
								'id'          => 'stripe_logo',
								'type'        => 'image',
								'label'       => __( 'Payment Method Logo', 'easycommerce' ),
								'description' => __( 'Upload your payment method logo. Recommended dimensions: 512x512 px.', 'easycommerce' ),
								'placeholder' => __( 'Choose logo file', 'easycommerce' ),
							),
							'payment_method_name'    => array(
								'id'          => 'payment_method_name',
								'type'        => 'text',
								'label'       => __( 'Name on Checkout', 'easycommerce' ),
								'description' => __( 'The name displayed to customers on the checkout page.', 'easycommerce' ),
								'placeholder' => __( 'Credit Card', 'easycommerce' ),
								'default'     => __( 'Credit Card', 'easycommerce' ),
							),
							'payment_element_layout' => array(
								'id'          => 'payment_element_layout',
								'type'        => 'select',
								'label'       => __( 'Payment Element Layout', 'easycommerce' ),
								'description' => __( 'Control how payment methods are organized in the checkout form. Tabs show methods in a compact tabbed interface, while accordion expands each method.', 'easycommerce' ),
								'options'     => array(
									'tabs'      => __( 'Tabs (Default)', 'easycommerce' ),
									'accordion' => __( 'Accordion', 'easycommerce' ),
								),
								'default'     => 'tabs',
							),
							'payment_element_theme'  => array(
								'id'          => 'payment_element_theme',
								'type'        => 'select',
								'label'       => __( 'Payment Element Theme', 'easycommerce' ),
								'description' => __( 'Select the visual appearance of the payment form. Choose from Stripe"s default styling or alternative themes.', 'easycommerce' ),
								'options'     => array(
									'stripe' => __( 'Stripe (Default)', 'easycommerce' ),
									'night'  => __( 'Night', 'easycommerce' ),
									'flat'   => __( 'Flat', 'easycommerce' ),
								),
								'default'     => 'stripe',
							),
							'default_order_status'   => array(
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
							'publishable_key'         => array(
								'id'          => 'publishable_key',
								'type'        => 'text',
								'label'       => __( 'Publishable Key', 'easycommerce' ),
								'placeholder' => __( 'pk_live_51Abxxxx', 'easycommerce' ),
								'description' => __( 'Your Stripe publishable key used for client-side operations like displaying payment forms. Available in your <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard API Keys</a> section.', 'easycommerce' ),
							),
							'secret_key'              => array(
								'id'          => 'secret_key',
								'type'        => 'text',
								'label'       => __( 'Secret Key', 'easycommerce' ),
								'placeholder' => __( 'sk_live_51Abxxxx', 'easycommerce' ),
								'description' => __( 'Your Stripe secret key used for server-side operations like processing payments. Keep this secure and never share it. Found in your <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard API Keys</a> section.', 'easycommerce' ),
							),
							'webhook_secret'          => array(
								'id'          => 'webhook_secret',
								'type'        => 'text',
								'label'       => __( 'Webhook Secret', 'easycommerce' ),
								'placeholder' => __( 'whsec_xxxx', 'easycommerce' ),
								'description' => sprintf(
								// translators: %s: Link to Stripe Dashboard.
									__( 'Secret key for verifying webhook authenticity. The plugin automatically registers webhooks, but you can manually enter the webhook signing secret from your <a href="%s" target="_blank">Stripe Dashboard Webhooks</a> section for additional security.', 'easycommerce' ),
									esc_url( 'https://dashboard.stripe.com/webhooks' ),
								),
							),
							'sandbox_publishable_key' => array(
								'id'          => 'sandbox_publishable_key',
								'type'        => 'text',
								'label'       => __( 'Sandbox Publishable Key', 'easycommerce' ),
								'placeholder' => __( 'pk_test_51Abxxxx', 'easycommerce' ),
								'description' => __( 'Your Stripe test publishable key for client-side operations during development. Available in your <a href="https://dashboard.stripe.com/test/apikeys" target="_blank">Stripe Test Dashboard API Keys</a> section.', 'easycommerce' ),
							),
							'sandbox_secret_key'      => array(
								'id'          => 'sandbox_secret_key',
								'type'        => 'text',
								'label'       => __( 'Sandbox Secret Key', 'easycommerce' ),
								'placeholder' => __( 'sk_test_51Abxxxx', 'easycommerce' ),
								'description' => __( 'Your Stripe test secret key for server-side operations during development. Keep this secure. Found in your <a href="https://dashboard.stripe.com/test/apikeys" target="_blank">Stripe Test Dashboard API Keys</a> section.', 'easycommerce' ),
							),
							'sandbox_webhook_secret'  => array(
								'id'          => 'sandbox_webhook_secret',
								'type'        => 'text',
								'label'       => __( 'Sandbox Webhook Secret', 'easycommerce' ),
								'placeholder' => __( 'whsec_xxxx', 'easycommerce' ),
								'description' => sprintf(
								// translators: %s: Webhook URL.
									__( 'For testing in sandbox mode, use Stripe CLI: <code>stripe listen --skip-verify --forward-to %s</code>. Copy the webhook signing secret shown in the CLI output here. The plugin handles webhook registration automatically.', 'easycommerce' ),
									esc_url( easycommerce_stripe_get_webhook_url() )
								),
							),
						),
					),
				);

				if ( isset( $this->payment_methods_helper ) ) {

					$enabled_payment_methods = $this->payment_methods_helper->get_enabled_payment_methods();
					$all_payment_methods     = $this->payment_methods_helper->get_all_available_payment_methods();
					$enabled_list            = array();
					foreach ( $enabled_payment_methods as $method_key ) {
						if ( isset( $all_payment_methods[ $method_key ] ) ) {
							$enabled_list[] = $all_payment_methods[ $method_key ]['label'];
						}
					}

					$base_url       = easycommerce_stripe_is_sandbox() ? 'https://dashboard.stripe.com/test/settings/payment_methods' : 'https://dashboard.stripe.com/settings/payment_methods';
					$default_pmc_id = $this->payment_methods_helper->get_default_pmc_id();
					$dashboard_url  = empty( $default_pmc_id ) ? $base_url : trailingslashit( $base_url ) . rawurlencode( $default_pmc_id );

					$content  = '<div class="mb-4">';
					$content .= '<h4 class="text-sm font-medium text-gray-900 mb-2">' . esc_html__( 'Enabled Payment Methods', 'easycommerce' ) . '</h4>';
					if ( ! empty( $enabled_list ) ) {
						$content .= '<ul class="text-sm text-gray-600 list-disc list-inside space-y-1">';
						foreach ( $enabled_list as $method ) {
							$content .= '<li>' . esc_html( $method ) . '</li>';
						}
						$content .= '</ul>';
					} else {
						$content .= '<p class="text-sm text-gray-600">' . esc_html__( 'No payment methods are currently enabled.', 'easycommerce' ) . '</p>';
					}
					$content .= '</div>';

					$content .= '<div>';
					$content .= '<h4 class="text-sm font-medium text-gray-900 mb-2">' . esc_html__( 'Manage Payment Methods', 'easycommerce' ) . '</h4>';
					$content .= '<p class="text-sm text-gray-600">';
					$content .= sprintf(
						wp_kses(
							/* translators: %s: URL of the Stripe dashboard payment methods page. The <a> markup must be kept. */
							__( 'Configure which payment methods are available in your Stripe account. <a href="%s" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Manage Payment Methods</a>', 'easycommerce' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
									'class'  => array(),
								),
							)
						),
						esc_url( $dashboard_url )
					);
					$content .= '</p>';
					$content .= '</div>';

					$settings[] = array(
						'label'   => __( 'Payment Methods', 'easycommerce' ),
						'content' => $content,
					);
				}

				return $settings;
			}

			public function is_available() {
				$api_key    = easycommerce_stripe_get_publishable_key();
				$secret_key = easycommerce_stripe_get_secret_key();

				return ! empty( $api_key ) && ! empty( $secret_key );
			}

			/**
			 * Enqueues the necessary JavaScript files for Stripe payment processing.
			 *
			 * @return void
			 */
			public function enqueue_scripts() {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return;
				}

				if ( ! easycommerce_is_checkout() ) {
					return;
				}

				wp_enqueue_script(
					'stripe.js',
					'https://js.stripe.com/v3/',
					array(),
					3,
					array( 'in_footer' => true )
				);

				wp_enqueue_script(
					'easycommerce-stripe',
					EASYCOMMERCE_ASSETS_URL . 'payment/js/stripe-payment.js',
					array( 'stripe.js', 'jquery', 'wp-i18n' ),
					EASYCOMMERCE_VERSION,
					array( 'in_footer' => true )
				);

				wp_set_script_translations( 'easycommerce-stripe', 'easycommerce', EASYCOMMERCE_PLUGIN_DIR . 'languages' );
			}

			/**
			 * Adds localized variables for the Stripe payment method.
			 *
			 * @param array $vars Existing localized variables.
			 *
			 * @return array Updated localized variables including Stripe configuration.
			 */
			public function localized( $vars ) {
				if ( ! $this->is_enabled() ) {
					return $vars;
				}

				if ( ! easycommerce_is_checkout() ) {
					return $vars;
				}

				$vars['stripe'] = array(
					'publishable_key'        => easycommerce_stripe_get_publishable_key(),
					'payment_element_layout' => Utility::get_option( 'payment', 'stripe', 'payment_element_layout', 'tabs' ),
					'payment_element_theme'  => Utility::get_option( 'payment', 'stripe', 'payment_element_theme', 'stripe' ),
				);

				if ( isset( $this->payment_methods_helper ) ) {
					$currency                = strtolower( easycommerce_currency() );
					$enabled_payment_methods = $this->payment_methods_helper->get_enabled_payment_methods();
					$cart                    = ( new Cart() )->get( true, false );
					$enabled_payment_methods = easycommerce_stripe_filter_payment_methods_by_currency( $enabled_payment_methods, $currency );
					$enabled_payment_methods = easycommerce_stripe_filter_wallet_payment_methods( $enabled_payment_methods );
					$enabled_payment_methods = apply_filters( 'easycommerce_stripe_filter_payment_methods', $enabled_payment_methods, $cart, $currency );

					$vars['stripe']['enabled_payment_methods'] = $enabled_payment_methods;
					$vars['stripe']['payment_method_minimums'] = easycommerce_stripe_payment_method_minimums();
					$vars['stripe']['payment_method_maximums'] = easycommerce_stripe_payment_method_maximums();
				}

				return $vars;
			}

			/**
			 * Returns the HTML for the Stripe payment form.
			 *
			 * @return string The HTML markup for the payment form.
			 */
			public function payment_form(): string {
				if ( ! $this->is_enabled() || ! $this->is_available() ) {
					return '';
				}

				return '<div id="easycommerce_stripe_payment_form"></div><div id="easycommerce_stripe_payment_errors"></div>';
			}

			/**
			 * Processes a payment through the Stripe payment gateway.
			 *
			 * @param string $status Current order status.
			 * @param int    $order_id The order ID being processed.
			 * @param array  $params Payment parameters including payment method and metadata.
			 * @param int    $customer_id The customer ID making the payment.
			 *
			 * @return string The updated order status after payment processing.
			 */
			public function process_payment( $status, $order_id, $params, $customer_id ) {
				if ( $this->get_id() !== $params['easycommerce-payment_method'] ) {
					return $status;
				}

				if ( ! $this->api_client ) {
					return $status;
				}

				$order = new Order( $order_id );

				if ( ! empty( $order->get_meta( 'stripe_payment_intent_id' ) ) ) {
					return $status;
				}

				$processed_status = apply_filters( 'easycommerce_stripe_process_payment', null, $status, $order_id, $params, $customer_id, $this );

				if ( $processed_status !== null ) {
					return $processed_status;
				}

				if ( Utility::get_option( 'payment', 'stripe', 'default_order_status' ) ) {
					$default_order_status = Utility::get_option( 'payment', 'stripe', 'default_order_status' );
				} else {
					$default_order_status = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
				}

				$payment_intent_id = $params['meta']['stripePaymentIntentId'] ?? null;
				$payment_method_id = $params['meta']['stripePaymentMethodId'] ?? null;

				if ( ! $payment_intent_id ) {
					return $status;
				}

				return $this->process_one_time_payment_with_intent(
					$order,
					$order_id,
					$payment_intent_id,
					$payment_method_id,
					$customer_id,
					$default_order_status
				);
			}

			/**
			 * Process one-time payment with existing PaymentIntent
			 */
			private function process_one_time_payment_with_intent( $order, $order_id, $payment_intent_id, $payment_method_id, $customer_id, $default_order_status ) {

				if ( ! $this->api_client ) {
					return 'failed';
				}

				try {
					$payment_intent = $this->api_client->paymentIntents->retrieve( $payment_intent_id );

					if ( $payment_intent->status === 'succeeded' ) {
						$order->add_meta( 'stripe_payment_intent_id', $payment_intent_id );
						$order->add_meta( 'stripe_payment_method_id', $payment_method_id );
						$order->add_meta( 'payment_method', $this->get_id() );

						$txn = new Transaction();
						$txn->add(
							$order_id,
							array(
								'transaction_id'  => $payment_intent->latest_charge,
								'customer_id'     => $customer_id,
								'payment_gateway' => 'stripe',
								'amount'          => ( (float) $payment_intent->amount ) / 100,
								'currency'        => $payment_intent->currency,
								'type'            => 'payment',
								'status'          => 'completed',
							)
						);

						do_action( "easycommerce-{$this->get_id()}_payment_complete", $payment_intent, $order_id, ( (float) $payment_intent->amount ) / 100, $customer_id, array() );

						return $default_order_status;
					}

					return 'pending';

				} catch ( \Throwable $e ) {
					if ( false === get_transient( 'ec_stripe_payment_error_log' ) ) {
						error_log( 'Stripe payment error: ' . $e->getMessage() );
						set_transient( 'ec_stripe_payment_error_log', 1, MINUTE_IN_SECONDS );
					}
					return 'failed';
				}
			}

			/**
			 * Handle payment element redirect flow (3D Secure, etc.)
			 *
			 * @param Order  $order The order object.
			 * @param int    $order_id The order ID.
			 * @param string $payment_intent_id The Stripe payment intent ID from URL.
			 * @param int    $customer_id The customer ID.
			 *
			 * @return string The order status.
			 */
			private function handle_payment_element_redirect( $order, $order_id, $payment_intent_id, $customer_id, $default_order_status ): string {

				if ( ! $this->api_client ) {
					return 'failed';
				}

				try {
					// Retrieve the payment intent
					$payment_intent = $this->api_client->paymentIntents->retrieve( $payment_intent_id, array( 'expand' => array( 'payment_method' ) ) );

					// Payment was successful
					if ( 'succeeded' === $payment_intent->status ) {
						$order->add_meta( 'stripe_payment_intent_id', $payment_intent_id );
						$order->add_meta( 'stripe_payment_method_id', $payment_intent->payment_method->id );
						$order->add_meta( 'stripe_payment_method_type', $payment_intent->payment_method->type );
						$order->add_meta( 'payment_method', $this->get_id() );

						// Create transaction record
						$txn = new Transaction();
						$txn->add(
							$order_id,
							array(
								'transaction_id'  => $payment_intent->latest_charge,
								'customer_id'     => $customer_id,
								'payment_gateway' => 'stripe',
								'amount'          => $order->get_total(),
								'currency'        => $payment_intent->currency,
								'type'            => 'payment',
								'status'          => 'completed',
							)
						);

						// Trigger success action
						do_action( "easycommerce-{$this->id}_payment_complete", $payment_intent, $order_id, $order->get_total(), $customer_id, array() );

						return $default_order_status ?? 'completed';
					} elseif ( 'requires_payment_method' === $payment_intent->status ) {
						// Payment failed
						return 'failed';
					} else {
						// Payment is still processing or requires action
						return 'pending';
					}
				} catch ( Throwable $e ) {
					return 'failed';
				}
			}

			/**
			 * Creates a new Stripe customer or retrieves an existing one.
			 *
			 * @param int    $customer_id The EasyCommerce customer ID.
			 * @param string $payment_intent_id The Stripe payment token.
			 * @param array  $params Additional customer parameters.
			 *
			 * @return Customer The Stripe customer object.
			 * @throws ApiErrorException
			 * @throws Exception
			 */
			private function create_or_get_stripe_customer( $customer_id, $payment_intent_id, $params ): Customer {

				if ( ! $this->api_client ) {
					throw new Exception( 'Stripe is not properly configured.' );
				}

				$existing_stripe_customer_id = get_user_meta( $customer_id, '_stripe_customer_id', true );

				if ( ! $existing_stripe_customer_id ) {
					$customer = $this->create_and_assign_customer( $payment_intent_id, $customer_id, $params );
				} else {
					try {
						$customer = $this->api_client->customers->retrieve( $existing_stripe_customer_id );
					} catch ( Throwable $e ) {
						$customer = $this->create_and_assign_customer( $payment_intent_id, $customer_id, $params );
					}
				}

				$payment_method = $this->retrieve_payment_method_from_token( $payment_intent_id );
				if ( $payment_method instanceof PaymentMethod ) {
					$this->attach_payment_method_to_customer( $customer->id, $payment_method->id );

					$this->api_client->customers->update(
						$customer->id,
						array(
							'invoice_settings' => array(
								'default_payment_method' => $payment_method->id,
							),
						)
					);

				}

				return $customer;
			}

			/**
			 * Retrieves a Stripe payment method from a token.
			 *
			 * @param string $payment_intent_id The Stripe token to retrieve payment method from.
			 *
			 * @return PaymentMethod|null The payment method object or null if not found.
			 */
			private function retrieve_payment_method_from_token( $payment_intent_id ): ?PaymentMethod {

				if ( ! $this->api_client ) {
					return null;
				}

				try {
					$token = $this->api_client->tokens->retrieve( $payment_intent_id );
					if ( 'card' === $token->type ) {
						return $this->api_client->paymentMethods->retrieve( $token->card->id );
					}
				} catch ( Exception $e ) {
					return null;
				}

				return null;
			}

			/**
			 * Attaches a payment method to a Stripe customer.
			 *
			 * @param string $customer_id The Stripe customer ID.
			 * @param string $payment_method_id The Stripe payment method ID.
			 *
			 * @return void
			 */
			private function attach_payment_method_to_customer( $customer_id, $payment_method_id ): void {

				if ( ! $this->api_client ) {
					return;
				}

				try {
					$this->api_client->paymentMethods->retrieve( $payment_method_id )->attach(
						array(
							'customer' => $customer_id,
						)
					);

					return;
				} catch ( Throwable $e ) {
					return;
				}
			}

			/**
			 * Inserts a transaction record for a successful payment.
			 *
			 * @param PaymentIntent $intent The Stripe payment intent object.
			 * @param int           $order_id The order ID.
			 * @param float         $total_amount The total payment amount.
			 * @param int           $customer_id The customer ID.
			 * @param array         $params Additional payment parameters.
			 *
			 * @return void
			 */
			public function insert_transaction( $intent, $order_id, $total_amount, $customer_id, $params ) {
				$txn                  = new Transaction();
				$existing_transaction = $txn->get_by_order_id( $order_id );

				if ( $existing_transaction ) {
					return;
				}

				$txn->add(
					$order_id,
					array(
						'transaction_id'  => $intent->latest_charge,
						'customer_id'     => $customer_id,
						'payment_gateway' => 'stripe',
						'amount'          => ( (float) $intent->amount ) / 100,
						'currency'        => $intent->currency,
						'type'            => 'payment',
						'status'          => 'completed',
					)
				);
			}

			/**
			 * Processes a refund through the Stripe payment gateway.
			 *
			 * @param int    $order_id The order ID to refund.
			 * @param string $reason The reason for the refund.
			 * @param float  $amount The amount to refund.
			 *
			 * @return bool True on successful refund, false otherwise.
			 */
			public function refund( $order_id, $reason, $amount ) {

				if ( ! $this->api_client ) {
					return false;
				}

				$order             = new Order( $order_id );
				$payment_intent_id = $order->get_meta( 'stripe_payment_intent_id' );
				$refund_handled    = apply_filters( 'easycommerce_stripe_handle_refund', false, $order_id, $reason, $amount, $this );

				if ( $refund_handled ) {
					return true;
				}

				if ( ! $payment_intent_id ) {
					return false;
				}

				try {
					$config = array(
						'payment_intent' => $payment_intent_id,
						'amount'         => $amount * 100,
					);

					if ( in_array( $reason, array( 'requested_by_customer', 'fraudulent', 'duplicate' ), true ) ) {
						$config['reason'] = $reason;
					}

					$refund = $this->api_client->refunds->create( $config );

					$this->refund_transaction_id = $refund->id;

					do_action( 'easycommerce_stripe_refund_complete', $refund, $order_id, $amount, $reason );

					return true;

				} catch ( Throwable $e ) {
					return false;
				}
			}

			public function refund_transaction_id() {
				return $this->refund_transaction_id;
			}

			/**
			 * Creates and returns a Customer object based on the provided details.
			 *
			 * @param string     $payment_intent_id A token generated by Stripe to associate the customer with a payment source.
			 * @param int|string $customer_id The ID of the customer in the EasyCommerce system.
			 * @param array      $params Additional parameters including billing address and other metadata.
			 *
			 * @return Customer The created Customer object.
			 * @throws Exception If an error occurs during the customer creation process.
			 */
			public function create_new_customer( string $payment_intent_id, $customer_id, array $params ): Customer {

				if ( ! $this->api_client ) {
					throw new Exception( 'Stripe is not properly configured.' );
				}

				$customer_data = array(
					'source'   => $payment_intent_id,
					'metadata' => array(
						'easycommerce_customer_id' => $customer_id,
					),
				);

				if ( isset( $params['billing_address']['email'] ) ) {
					$customer_data['email'] = $params['billing_address']['email'];
				}

				if ( isset( $params['billing_address']['first_name'] ) || isset( $params['billing_address']['last_name'] ) ) {
					$customer_data['name'] = trim( ( $params['billing_address']['first_name'] ?? '' ) . ' ' . ( $params['billing_address']['last_name'] ?? '' ) );
				}

				if ( isset( $params['billing_address'] ) ) {
					$address = array();
					if ( ! empty( $params['billing_address']['address_1'] ) ) {
						$address['line1'] = $params['billing_address']['address_1'];
					}
					if ( ! empty( $params['billing_address']['address_2'] ) ) {
						$address['line2'] = $params['billing_address']['address_2'];
					}
					if ( ! empty( $params['billing_address']['city'] ) ) {
						$address['city'] = $params['billing_address']['city'];
					}
					if ( ! empty( $params['billing_address']['state'] ) ) {
						$address['state'] = $params['billing_address']['state'];
					}
					if ( ! empty( $params['billing_address']['postcode'] ) ) {
						$address['postal_code'] = $params['billing_address']['postcode'];
					}
					if ( ! empty( $params['billing_address']['country'] ) ) {
						$address['country'] = $params['billing_address']['country'];
					}

					if ( ! empty( $address ) ) {
						$customer_data['address'] = $address;
					}
				}

				return $this->api_client->customers->create( $customer_data );
			}

			/**
			 * Creates a new Stripe customer and assigns it to the EasyCommerce customer.
			 *
			 * @param string $payment_intent_id The Stripe payment token.
			 * @param int    $customer_id The EasyCommerce customer ID.
			 * @param array  $params Additional customer parameters.
			 *
			 * @return Customer The created Stripe customer object.
			 * @throws Exception
			 */
			public function create_and_assign_customer( $payment_intent_id, $customer_id, $params ): Customer {
				$customer = $this->create_new_customer( $payment_intent_id, $customer_id, $params );

				update_user_meta( $customer_id, '_stripe_customer_id', $customer->id );

				return $customer;
			}

			/**
			 * Get Stripe API client
			 *
			 * @return StripeClient
			 */
			public function get_api_client() {
				return $this->api_client;
			}

			public function supports_refund() {
				return true;
			}
		}
		new Stripe();
	},
	9
);

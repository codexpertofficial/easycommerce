<?php
namespace EasyCommerce\Controllers\Payment;

use EasyCommerce\Models\Cart;
use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Order;
use EasyCommerce\Abstracts\Payment_Method;

add_action(
	'init',
	function () {
		class Cash_On_Delivery extends Payment_Method {

			protected $id;

			/**
			 * Constructor
			 */
			public function __construct() {
				$this->id = 'cash-on-delivery';

				parent::__construct(
					$this->id,
					__( 'Cash on Delivery', 'easycommerce' ),
					__( 'Pay via Cash on Delivery', 'easycommerce' )
				);
			}

			public function get_icon() {
				return EASYCOMMERCE_ASSETS_URL . 'payment/img/cash-on-delivery.svg';
			}

			public function settings(): array {
				if ( ! $this->is_enabled() ) {
					return array();
				}

				return array(
					'label'  => __( 'General', 'easycommerce' ),
					'fields' => array(
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
						),
						'instruction' => array(
							'id'          => 'instruction',
							'type'        => 'textarea',
							'label'       => __( 'Instruction', 'easycommerce' ),
							'description' => __( 'Instructions shown to the customer on the checkout page.', 'easycommerce' ),
							'placeholder' => __( 'e.g. Please have the exact amount ready upon delivery.', 'easycommerce' ),
						),
					),
				);
			}

			public function is_offline(): bool {
				return true;
			}

			public function payment_form(): string {
				$instruction = Utility::get_option( 'payment', $this->id, 'instruction' );
				$html        = '<div id="easycommerce_cod_payment_form">';

				if ( ! empty( $instruction ) ) {
					$html .= '<p class="easycommerce-cod-instruction text-ec-body font-inter text-sm leading-[26px] mt-2">' . wp_kses_post( nl2br( $instruction ) ) . '</p>';
				}

				$html .= '</div><div id="easycommerce_cod_payment_errors"></div>';

				return $html;
			}

			public function validate_availability( $unset, $has_physical ) {
				if ( ! $has_physical ) {
					return true;
				}

				$cart = easycommerce_get_cart();
				return $cart && $cart->has_item_type( 'digital' ) ? true : $unset;
			}

			public function process_payment( $status, $order_id, $params, $customer_id ) {
				if ( $this->get_id() !== $params['easycommerce-payment_method'] ) {
					return $status;
				}

				$cart     = ( new Cart() )->get( true, false );
				$metadata = array();

				if ( Utility::get_option( 'payment', $this->id, 'default_order_status' ) ) {
					$default_order_status = Utility::get_option( 'payment', $this->id, 'default_order_status' );
				} else {
					$default_order_status = Utility::get_option( 'order', 'settings', 'default_order_status', 'pending' );
				}

				if ( ! empty( $cart['items'] ) ) {
					foreach ( $cart['items'] as $index => $item ) {
						// Add itemized details to metadata for each product
						$metadata[ "item_{$index}_name" ]       = $item['title'];
						$metadata[ "item_{$index}_quantity" ]   = (int) $item['quantity'];
						$metadata[ "item_{$index}_unit_price" ] = (float) $item['unit_price'];
						$metadata[ "item_{$index}_subtotal" ]   = (float) $item['subtotal'];
						$metadata[ "item_{$index}_attributes" ] = implode( ', ', $item['attributes'] );
					}
				}

				$metadata['discount_amount'] = $cart['amounts']['discount_amount'] ?? 0;
				$metadata['tax']      		 = $cart['amounts']['tax'] ?? 0;
				$metadata['shipping_fee']    = $cart['amounts']['shipping_fee'] ?? 0;
				$metadata['total']           = $cart['amounts']['total'] ?? 0;

				$order = new Order( $order_id );
				$order->update_meta( 'payment_method_name', 'Cash-on-delivery' );
				$order->update_meta( 'payment_method', $this->get_id() );

				foreach ( $metadata as $meta_key => $meta_value ) {
					$order->update_meta( $meta_key, $meta_value );
				}

				return $default_order_status;
			}

			public function refund( $order_id, $amount, $reason ) {
				$order          = new Order( $order_id );
				$payment_method = $order->get_meta( 'payment_method_name' );

				return 'cash-on-delivery' === $payment_method;
			}
		}

		new Cash_On_Delivery();
	},
	9
);

<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Cleaner;
use Exception;

class Email_Placeholder extends API {

	use Hook;
	use Cleaner;

	/**
	 * Get general email placeholders (available for all emails)
	 */
	public function get_general_placeholders(): array {
		return [
			'##site_name##'      => __( 'Site Name', 'easycommerce' ),
			'##shop_name##'      => __( 'Shop Name', 'easycommerce' ),
			'##year##'           => __( 'Current Year', 'easycommerce' ),
			'##shop_page##'      => __( 'Shop Page URL', 'easycommerce' ),
			'##checkout_page##'  => __( 'Checkout Page URL', 'easycommerce' ),
			'##dashboard_page##' => __( 'Dashboard Page URL', 'easycommerce' ),
		];
	}

	/**
	 * Get order-specific email placeholders
	 */
	public function get_order_placeholders(): array {
		// Use EasyCommerce's existing function if available.
		if ( function_exists( 'easycommerce_order_placeholders' ) ) {
			// Get a sample with dummy order ID to show available placeholders.
			$sample_placeholders    = easycommerce_order_placeholders( 0 );
			$organized_placeholders = [];

			foreach ( $sample_placeholders as $placeholder => $description ) {
				$organized_placeholders[ $placeholder ] = $this->get_placeholder_description( $placeholder );
			}

			return $organized_placeholders;
		}

		return [];
	}

	/**
	 * Get cart-specific email placeholders
	 */
	public function get_cart_placeholders(): array {
		// Use EasyCommerce's existing function if available.
		if ( function_exists( 'easycommerce_cart_placeholders' ) && function_exists( 'easycommerce_get_cart' ) ) {
			// Get a sample cart to show available placeholders.
			$cart                   = easycommerce_get_cart();
			$sample_placeholders    = easycommerce_cart_placeholders( $cart );
			$organized_placeholders = [];

			foreach ( $sample_placeholders as $placeholder => $description ) {
				$organized_placeholders[ $placeholder ] = $this->get_placeholder_description( $placeholder );
			}

			return $organized_placeholders;
		}

		return [];
	}

	/**
	 * Get placeholder description based on key
	 *
	 * @param string $placeholder The placeholder key.
	 *
	 * @return string The placeholder description.
	 */
	private function get_placeholder_description( string $placeholder ): string {
		$descriptions = [
			'##order_id##'                     => __( 'Order ID', 'easycommerce' ),
			'##customer_name##'                => __( 'Customer Full Name', 'easycommerce' ),
			'##customer_email##'               => __( 'Customer Email Address', 'easycommerce' ),
			'##customer_phone##'               => __( 'Customer Phone Number', 'easycommerce' ),
			'##billing_first_name##'           => __( 'Billing First Name', 'easycommerce' ),
			'##billing_last_name##'            => __( 'Billing Last Name', 'easycommerce' ),
			'##billing_email##'                => __( 'Billing Email Address', 'easycommerce' ),
			'##billing_phone##'                => __( 'Billing Phone Number', 'easycommerce' ),
			'##billing_address_1##'            => __( 'Billing Address Line 1', 'easycommerce' ),
			'##billing_address_2##'            => __( 'Billing Address Line 2', 'easycommerce' ),
			'##billing_country##'              => __( 'Billing Country', 'easycommerce' ),
			'##billing_state##'                => __( 'Billing State/Province', 'easycommerce' ),
			'##billing_city##'                 => __( 'Billing City', 'easycommerce' ),
			'##billing_postcode##'             => __( 'Billing Postal Code', 'easycommerce' ),
			'##billing_address##'              => __( 'Full Billing Address', 'easycommerce' ),
			'##shipping_first_name##'          => __( 'Shipping First Name', 'easycommerce' ),
			'##shipping_last_name##'           => __( 'Shipping Last Name', 'easycommerce' ),
			'##shipping_email##'               => __( 'Shipping Email Address', 'easycommerce' ),
			'##shipping_phone##'               => __( 'Shipping Phone Number', 'easycommerce' ),
			'##shipping_address_1##'           => __( 'Shipping Address Line 1', 'easycommerce' ),
			'##shipping_address_2##'           => __( 'Shipping Address Line 2', 'easycommerce' ),
			'##shipping_country##'             => __( 'Shipping Country', 'easycommerce' ),
			'##shipping_state##'               => __( 'Shipping State/Province', 'easycommerce' ),
			'##shipping_city##'                => __( 'Shipping City', 'easycommerce' ),
			'##shipping_postcode##'            => __( 'Shipping Postal Code', 'easycommerce' ),
			'##shipping_address##'             => __( 'Full Shipping Address', 'easycommerce' ),
			'##customer_total_spent##'         => __( 'Customer Total Spent', 'easycommerce' ),
			'##customer_total_order_count##'   => __( 'Customer Total Orders', 'easycommerce' ),
			'##customer_average_order_value##' => __( 'Customer Average Order Value', 'easycommerce' ),
			'##product_list##'                 => __( 'Product List (HTML)', 'easycommerce' ),
			'##product_table##'                => __( 'Product Table (HTML)', 'easycommerce' ),
			'##number_of_items##'              => __( 'Number of Items', 'easycommerce' ),
			'##order_total##'                  => __( 'Order Total Amount', 'easycommerce' ),
			'##hash##'                         => __( 'Cart Hash/ID', 'easycommerce' ),
			'##name##'                         => __( 'Customer Name', 'easycommerce' ),
			'##email##'                        => __( 'Customer Email', 'easycommerce' ),
			'##cart_link##'                    => __( 'Cart Recovery Link', 'easycommerce' ),
			'##cart_total##'                   => __( 'Cart Total Amount', 'easycommerce' ),
			'##amount##'                       => __( 'Total Amount', 'easycommerce' ),
			'##order_status##'                 => __( 'Order Status', 'easycommerce' ),
		];

		return $descriptions[ $placeholder ] ?? ucwords( str_replace( [ '##', '_' ], [ '', ' ' ], $placeholder ) );
	}

	/**
	 * REST API handler to get placeholders for Select2
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return void Uses Rest trait response methods.
	 */
	public function get_placeholders( WP_REST_Request $request ) {
		try {
			$response = [];
			$search   = $this->sanitize( $request->get_param( 'search' ) ?? '', 'text' );

			// Validate search parameter length to prevent abuse
			if ( strlen( $search ) > 200 ) {
				$this->response_error(
					[
						'message' => __( 'Search term is too long', 'easycommerce' ),
					],
					400
				);
				return;
			}

			// Get all placeholder categories.
			$categories = [
				'General' => $this->get_general_placeholders(),
				'Order'   => $this->get_order_placeholders(),
				'Cart'    => $this->get_cart_placeholders(),
			];

			// Organize response with categories.
			foreach ( $categories as $category => $placeholders ) {
				if ( ! empty( $placeholders ) ) {
					// Add category header.
					$response[] = [
						'id'       => '',
						'text'     => $this->escape( sprintf( '--- %s Placeholders ---', $category ) ),
						'disabled' => true,
					];

					// Add placeholders in this category.
					foreach ( $placeholders as $placeholder => $description ) {
						$placeholder_text = $placeholder . ' - ' . $description;

						// Filter by search term if provided
						if ( ! empty( $search ) ) {
							if ( stripos( $placeholder_text, $search ) === false ) {
								continue;
							}
						}

						$response[] = [
							'id'   => $this->escape( $placeholder, 'attr' ),
							'text' => $this->escape( $placeholder_text ),
						];
					}
				}
			}

			/**
			 * Filters the placeholders before sending the response.
			 *
			 * @since 1.9
			 * @param array $response The placeholders.
			 * @param WP_REST_Request $request The request object.
			 */
			$response = apply_filters( 'easycommerce_get_placeholders', $response, $request );

			$this->response_success( $response );

		} catch ( Exception $e ) {
			$this->response_error(
				[
					'message' => __( 'Failed to retrieve email placeholders', 'easycommerce' ),
					'error'   => $e->getMessage(),
				],
				500
			);
		}
	}
}

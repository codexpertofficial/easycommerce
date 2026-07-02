<?php
namespace EasyCommerce\Controllers\Front;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Notice;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Customer;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Services\AI as AI_Service;

class Init {

	use Hook;
	use Cleaner;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->filter( 'body_class', array( $this, 'add_body_class' ) );
		$this->action( 'wp', array( $this, 'add_to_cart' ) );
		$this->action( 'template_redirect', array( $this, 'empty_cart_redirect' ) );
		$this->action( 'easycommerce_before_empty_cart_redirect', array( $this, 'parse_shortcode_params' ) );
		$this->action( 'template_redirect', array( $this, 'restore_cart' ) );
		$this->action( 'wp_head', array( $this, 'product_head' ) );
		$this->filter( 'easycommerce_api_product_list', array( $this, 'smart_search' ), 10, 3 );
		$this->filter( 'the_content', array( $this, 'check_required_elements' ), 10, 1 );
		$this->action( 'wp_body_open', array( $this, 'show_notices' ) );
	}

	public function add_body_class( $classes ) {
		$classes[] = 'easycommerce';
		$classes[] = 'easycommerce-' . get_template();

		return $classes;
	}

	public function add_to_cart() {
		if ( ! isset( $_GET['add-to-cart'] ) ) {
			return;
		}

		$product_id = (int) $this->sanitize( $_GET['add-to-cart'] );
		$price_id   = isset( $_GET['variation'] ) ? (int) $this->sanitize( $_GET['variation'] ) : 1;
		$quantity   = isset( $_GET['quantity'] ) ? (int) $this->sanitize( $_GET['quantity'] ) : 1;
		$coupon     = isset( $_GET['coupon'] ) ? $this->sanitize( $_GET['coupon'] ) : null;

		$cart = new Cart();
		$cart->add( $product_id, $price_id, $quantity );
		$cart->add_coupon( $coupon );

		wp_safe_redirect( easycommerce_checkout_page( true ) );
	}

	/**
	 * Processes the [easycommerce-checkout] shortcode in the current post.
	 *
	 * If the shortcode includes a "products" attribute, parses its comma-separated list
	 * of products. Each product should be in the format: product_id|price_id|quantity.
	 * - product_id is required.
	 * - price_id is optional (default: 1).
	 * - quantity is optional (default: 1).
	 *
	 * Examples:
	 *   products="9"
	 *       -> Adds product ID 9 with default price_id (1) and quantity (1).
	 *   products="9|2|4"
	 *       -> Adds product ID 9 with price ID 2 and quantity 4.
	 *   products="9,10|3,11|2|5"
	 *       -> Processes multiple products:
	 *           - Product ID 9 with defaults,
	 *           - Product ID 10 with price ID 3 and default quantity,
	 *           - Product ID 11 with price ID 2 and quantity 5.
	 *
	 * For each product, if it already exists in the cart, its quantity is updated;
	 * otherwise, the product is added to the cart.
	 *
	 * @param object $cart The cart instance to update.
	 *
	 * @return void
	 */
	public function parse_shortcode_params( $cart ) {
		global $post;

		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'easycommerce-checkout' ) ) {
			$pattern = get_shortcode_regex( array( 'easycommerce-checkout' ) );
			if ( preg_match( '/' . $pattern . '/s', $post->post_content, $matches ) ) {
				$atts = shortcode_parse_atts( $matches[3] );
				if ( isset( $atts['products'] ) ) {
					foreach ( explode( ',', $atts['products'] ) as $product ) {
						$product = explode( '|', $product );

						$product_id = (int) $product[0];
						$price_id   = isset( $product[1] ) ? (int) $product[1] : 1;
						$quantity   = isset( $product[2] ) ? (int) $product[2] : 1;

						if ( $cart->has_product( $product_id, $price_id ) ) {
							$cart->update_qty( $product_id, $price_id, $quantity );
						} else {
							$cart->add( $product_id, $price_id, $quantity );
						}
					}
				}
			}
		}
	}

	/**
	 * If the cart is empty, return to the shop page
	 */
	public function empty_cart_redirect() {

		if ( isset( $_GET['add-to-cart'] ) || ( is_page( easycommerce_checkout_page() ) && isset( $_GET['hash'] ) ) || easycommerce_checkout_page() == easycommerce_shop_page() ) {
			return;
		}

		$cart = easycommerce_get_cart();

		do_action( 'easycommerce_before_empty_cart_redirect', $cart );

		$items      = $cart->get_items();
		$empty_cart = empty( $items ) || empty( array_pop( $items ) );

		if ( is_page( easycommerce_checkout_page() ) && $empty_cart ) {
			wp_safe_redirect( easycommerce_shop_page( true ) );
			// exit();
		}
	}

	/**
	 * Restore the cart from a hash
	 */
	public function restore_cart() {
		if ( ! isset( $_GET['hash'] ) ) {
			return;
		}

		$hash = $this->sanitize( $_GET['hash'] );

		$cart_model = new Cart( $hash );
		$cart_model->set_user_hash( $hash );

		wp_safe_redirect( easycommerce_checkout_page( true ) );
		exit();
	}

	public function product_head() {

		if ( ! is_singular( 'product' ) ) {
			return;
		}

		$product = new Product( get_the_ID() );

		if ( get_option( 'blog_public' ) == 1 && $product->get_meta( 'noindex' ) == 1 ) {
			echo '<meta name="robots" content="noindex, nofollow">';
		}
	}

	public function smart_search( $result, $filters, $request ) {

		if ( Utility::get_option( 'ai', 'agentic-ai', 'smart_search' ) == '1' && empty( $result['total'] ) && ! empty( $filters['search'] ) ) {

			$search_term = $filters['search'];
			$per_page    = $request->get_param( 'per_page' ) ?: 10;
			$page        = $request->get_param( 'page' ) ?: 1;
			$offset      = ( $page - 1 ) * $per_page;

			// Tier 1: local fuzzy match on significant words (stop words stripped).
			// Handles single-word typos ("walett") and multi-word queries ("walett for men").
			// Longest word tried first — more specific words produce better matches.
			$stop_words   = [ 'for', 'the', 'and', 'or', 'a', 'an', 'of', 'in', 'on', 'at', 'to', 'by', 'with', 'from' ];
			$sig_words    = array_filter(
				explode( ' ', mb_strtolower( $search_term ) ),
				fn( $w ) => strlen( $w ) > 2 && ! in_array( $w, $stop_words, true )
			);
			usort( $sig_words, fn( $a, $b ) => strlen( $b ) - strlen( $a ) );

			foreach ( $sig_words as $sig_word ) {
				$fuzzy_match = Utility::fuzzy_search( $sig_word );
				if ( $fuzzy_match ) {
					$filters['search'] = $fuzzy_match;
					return Product::list( $filters, $per_page, $offset, true, true );
				}
			}

			// Tier 2: AI intent extraction with full product catalog context.
			// Fixes typos AND understands natural language ("keep money" → "wallet").
			// Product titles already cached by fuzzy_search() above — no extra DB hit.
			$ai_service     = new AI_Service();
			$product_titles = Utility::get_product_titles();
			$ai_filters     = $ai_service->search_intent( $search_term, $product_titles );
			$ai_search      = $ai_filters['search'] ?? '';

			if ( $ai_search && $ai_search !== $search_term ) {
				$ai_result = Product::list(
					array_merge( $filters, array( 'search' => $ai_search ) ),
					$per_page,
					$offset,
					true,
					true
				);

				if ( ! empty( $ai_result['total'] ) ) {
					return $ai_result;
				}
			}

			return $result;
		}

		return $result;
	}

	/**
	 * Check if required elements are present on setup wizard pages
	 */
	public function check_required_elements( $content ) {
		global $post;

		if ( ! is_page() || ! isset( $post ) || ! current_user_can( 'administrator' ) ) {
			return $content;
		}

		$page_id = $post->ID;
		$notice = '';

		// Check shop page
		if ( $page_id == easycommerce_shop_page() ) {
			$has_block = false;
			for ( $i = 1; $i <= 3; $i++ ) {
				if ( has_block( 'easycommerce/template-' . $i, $post ) ) {
					$has_block = true;
					break;
				}
			}

			$show_shop_page_notice = apply_filters( 'easycommerce_show_shop_page_missing_shortcode_notice', true, $post );
			if ( ! $has_block && $show_shop_page_notice ) {
				$notice = sprintf(
					__( '<div class="easycommerce-notice easycommerce-warning" style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px; margin-bottom: 20px; border-radius: 4px;"><p style="margin: 0; font-weight: 500;">%s</p></div>', 'easycommerce' ),
					__( 'This page requires an EasyCommerce shop block to function properly. Please add a shop template block to this page.', 'easycommerce' )
				);
			}
		}

		// Check checkout page
		if ( $page_id == easycommerce_checkout_page() ) {
			$has_checkout_shortcode = has_shortcode( $post->post_content, 'easycommerce-checkout' );
			$show_checkout_page_notice = apply_filters( 'easycommerce_show_checkout_page_missing_shortcode_notice', true, $post );

			if ( ! $has_checkout_shortcode && $show_checkout_page_notice ) {
				$notice = sprintf(
					__( '<div class="easycommerce-notice easycommerce-warning" style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px; margin-bottom: 20px; border-radius: 4px;"><p style="margin: 0; font-weight: 500;">%s</p></div>', 'easycommerce' ),
					__( 'This page is missing the EasyCommerce checkout shortcode. Please add the <span style="color: #000;">&#91;easycommerce-checkout&#93;</span> shortcode to this page to function properly.', 'easycommerce' )
				);
			}
		}

		// Check dashboard page
		if ( $page_id == easycommerce_dashboard_page() ) {
			$has_dashboard_shortcode = has_shortcode( $post->post_content, 'easycommerce-dashboard' );
			$show_dashboard_page_notice = apply_filters( 'easycommerce_show_dashboard_page_missing_shortcode_notice', true, $post );
			
			if ( ! $has_dashboard_shortcode && $show_dashboard_page_notice ) {
				$notice = sprintf(
					__( '<div class="easycommerce-notice easycommerce-warning" style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px; margin-bottom: 20px; border-radius: 4px;"><p style="margin: 0; font-weight: 500;">%s</p></div>', 'easycommerce' ),
					__( 'This page is missing the EasyCommerce dashboard shortcode. Please add the <span style="color: #000;">&#91;easycommerce-dashboard&#93;</span> shortcode to this page to function properly.', 'easycommerce' )
				);
			}
		}

		if ( $notice ) {
			$content = $notice . $content;
		}

		return $content;
	}

	/**
	 * Show notices
	 */
	public function show_notices() {

		$notices = Notice::list( null, null, 2 );

		foreach ( $notices as $notice ) {
			printf( "
				<div class='easycommerce-notice easycommerce-notice-{$notice['type']}'>
					<div class='easycommerce-notice-content'>
						<h3 class='easycommerce-notice-title'>{$notice['title']}</h3>
						<p>{$notice['message']}</p>
					</div>
					<div class='easycommerce-notice-cta'>
						<a href='{$notice['url']}'>{$notice['button']}</a>
					</div>
				</div>
			" );
		}
	}
}

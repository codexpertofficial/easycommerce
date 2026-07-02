<?php
namespace EasyCommerce\Controllers\Front;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Traits\Hook;

class Template {

	use Hook;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		// template override
		$this->filter( 'template_include', array( $this, 'override_template' ) );

		// archive-related templates
		$this->action( 'easycommerce/views/templates/archive-product', array( $this, 'show_product_archive' ) );
		$this->action( 'easycommerce/views/templates/archive-product/loop', array( $this, 'show_product_archive_loop' ) );
		$this->action( 'easycommerce/views/templates/archive-product/pagination', array( $this, 'show_product_archive_pagination' ) );

		// single product-related templates
		$this->action( 'easycommerce/views/templates/single-product', array( $this, 'show_single_product' ) );
		$this->action( 'easycommerce/views/templates/single-product/thumbnail', array( $this, 'show_thumbnail' ) );
		$this->action( 'easycommerce/views/templates/single-product/gallery', array( $this, 'show_gallery' ) );
		$this->action( 'easycommerce/views/templates/single-product/stock', array( $this, 'show_stock' ) );
		$this->action( 'easycommerce/views/templates/single-product/title', array( $this, 'show_title' ) );
		$this->action( 'easycommerce/views/templates/single-product/rating', array( $this, 'show_rating' ) );
		$this->action( 'easycommerce/views/templates/single-product/price', array( $this, 'show_price' ) );
		$this->action( 'easycommerce/views/templates/single-product/attributes', array( $this, 'show_attributes' ) );
		$this->action( 'easycommerce/views/templates/single-product/sale_price', array( $this, 'show_sale_price' ) );
		$this->action( 'easycommerce/views/templates/single-product/add-to-cart', array( $this, 'show_add_to_cart' ) );
		$this->action( 'easycommerce/views/templates/single-product/product-tab', array( $this, 'show_product_tab' ) );
		$this->action( 'easycommerce/views/templates/single-product/description', array( $this, 'show_product_description' ) );
		$this->action( 'easycommerce/views/templates/single-product/summary', array( $this, 'show_product_summary' ) );
		$this->action( 'easycommerce/views/templates/single-product/review', array( $this, 'show_product_review' ) );
		$this->action( 'easycommerce/views/templates/single-product/excerpt', array( $this, 'show_excerpt' ) );

		// block templates
		$this->action( 'easycommerce/views/blocks/shop', array( $this, 'show_shop_products' ), 10, 4 );

		// checkout template
		$this->action( 'easycommerce/views/templates/checkout/billing', array( $this, 'show_billing' ) );
		$this->action( 'easycommerce/views/templates/checkout/shipping', array( $this, 'show_shipping' ) );
		$this->action( 'easycommerce/views/templates/checkout/items', array( $this, 'show_items' ) );
		$this->action( 'easycommerce/views/templates/checkout/summary', array( $this, 'show_summary' ), 10, 2 );
		$this->action( 'easycommerce/views/templates/checkout/payment_methods', array( $this, 'show_payment_methods' ) );
	}

	public function override_template( $template ) {
		if ( is_post_type_archive( 'product' ) ) {
			$should_override = apply_filters( 'easycommerce_override_product_archive_template', true );
			$plugin_template = EASYCOMMERCE_PLUGIN_DIR . 'views/templates/archive-product.php';

			if ( $should_override && file_exists( $plugin_template ) ) {
				$template = $plugin_template;
			}

			return apply_filters( 'easycommerce-product_archive_template_path', $template );
		}

		if ( is_singular( 'product' ) ) {
			$should_override = apply_filters( 'easycommerce_override_product_single_template', true );
			$plugin_template = EASYCOMMERCE_PLUGIN_DIR . 'views/templates/single-product.php';

			if ( $should_override && file_exists( $plugin_template ) ) {
				$template = $plugin_template;
			}

			return apply_filters( 'easycommerce-single_product_template_path', $template );
		}

		return $template;
	}

	public function show_product_archive() {
		echo Utility::get_template( 'templates/archive-product/layout.php' );
	}

	public function show_product_archive_loop() {
		echo Utility::get_template( 'templates/archive-product/loop.php' );
	}

	public function show_product_archive_pagination() {
		echo Utility::get_template( 'templates/archive-product/pagination.php' );
	}

	public function show_single_product() {
		echo Utility::get_template( 'templates/single-product/layout.php' );
	}

	public function show_thumbnail() {
		echo Utility::get_template( 'templates/single-product/thumbnail.php' );
	}

	public function show_gallery() {
		echo Utility::get_template( 'templates/single-product/gallery.php' );
	}

	public function show_stock() {
		echo Utility::get_template( 'templates/single-product/stock.php' );
	}

	public function show_title() {
		echo Utility::get_template( 'templates/single-product/title.php' );
	}

	public function show_rating() {
		echo Utility::get_template( 'templates/single-product/rating.php' );
	}

	public function show_price() {
		echo Utility::get_template( 'templates/single-product/price.php' );
	}

	public function show_attributes() {
		echo Utility::get_template( 'templates/single-product/attributes.php' );
	}

	public function show_sale_price() {
		echo Utility::get_template( 'templates/single-product/sale-price.php' );
	}

	public function show_add_to_cart() {
		echo Utility::get_template( 'templates/single-product/add-to-cart.php' );
	}

	public function show_product_tab() {
		echo Utility::get_template( 'templates/single-product/product-tab.php' );
	}

	public function show_product_description() {
		echo Utility::get_template( 'templates/single-product/product-description.php' );
	}
	public function show_product_summary() {
		echo Utility::get_template( 'templates/single-product/summary.php' );
	}

	public function show_product_review() {
		echo Utility::get_template( 'templates/single-product/product-reivew.php' );
	}

	public function show_excerpt() {
		echo Utility::get_template( 'templates/single-product/excerpt.php' );
	}

	public function show_shop_products( $settings, $products, $shop_name, $view ) {
		echo Utility::get_template(
			"blocks/shops-page/{$shop_name}/shop.php",
			array(
				'settings' 	=> $settings,
				'products' 	=> $products,
				'view'  	=> $view,
			)
		);
	}

	private function render_checkout_template( string $path, array $args = [] ) {
		$template = easycommerce_checkout_template();
		echo Utility::get_template( "templates/checkout/{$template}/{$path}.php", $args );
	}

	public function show_billing( $cart_obj ) {
		$this->render_checkout_template( 'billing', [ 'cart_obj' => $cart_obj ] );
	}

	public function show_shipping( $cart_obj ) {
		$this->render_checkout_template( 'shipping', [ 'cart_obj' => $cart_obj ] );
	}

	public function show_items( $cart ) {
		$this->render_checkout_template( 'items', [ 'cart' => $cart ] );
	}

	public function show_summary( $cart, $cart_obj ) {
		$this->render_checkout_template(
			'summary',
			[
				'cart'     => $cart,
				'cart_obj' => $cart_obj,
			]
		);
	}

	public function show_payment_methods( $cart_obj ) {
		$this->render_checkout_template( 'payment-methods', [ 'cart_obj' => $cart_obj ] );
	}
}

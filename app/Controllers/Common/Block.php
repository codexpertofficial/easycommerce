<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Traits\Asset;
use ParagonIE\Sodium\Core\Util;

class Block {

	use Hook;
	use Asset;

	public $categories = array();

	public $pattern_categories = array();

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {

		$this->categories = array(
			'shop'      => __( 'EasyCommerce - Shop', 'easycommerce' ),
			'product'   => __( 'EasyCommerce - Product', 'easycommerce' ),
			'checkout'  => __( 'EasyCommerce - Checkout', 'easycommerce' ),
			'dashboard' => __( 'EasyCommerce - Dashboard', 'easycommerce' ),
		);

		$this->filter( 'init', array( $this, 'register' ) );
		$this->filter( 'block_categories_all', array( $this, 'register_category' ) );
		$this->action( 'init', array( $this, 'register_patterns' ) );
		$this->action( 'init', array( $this, 'register_pattern_categories' ) );
	}

	public function register() {
		$blocks_dir = EASYCOMMERCE_PLUGIN_DIR . 'build/blocks/';
		$categories = glob( $blocks_dir . '*', GLOB_ONLYDIR );

		foreach ( $categories as $category ) {
			$blocks = glob( $category . '/*', GLOB_ONLYDIR );

			foreach ( $blocks as $block ) {
				$view_file = str_replace( 'build/blocks/', 'views/blocks/', $block ) . '/render.php';

				// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
				register_block_type(
					$block,
					array(
						'render_callback' => function ( $attributes, $content ) use ( $view_file ) {
							ob_start();

							if ( file_exists( $view_file ) ) {
								include $view_file;
							}

							return ob_get_clean();
						},
					)
				);
				// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			}
		}
	}

	/**
	 * Register custom block categories.
	 *
	 * @param array $categories Existing block categories.
	 * @return array Updated block categories.
	 */
	public function register_category( $categories ) {
		$new_categories = array();

		foreach ( $this->categories as $id => $label ) {
			$new_categories[] = array(
				'slug'  => "easycommerce-{$id}",
				'title' => $label,
			);
		}

		return array_merge( $new_categories, $categories );
	}

	/**
	 * Registers patterns
	 */
	public function register_patterns() {

		register_block_pattern(
			'easycommerce/single-product-1',
			array(
				'title'       => __( 'Single Product 1', 'easycommerce' ),
				'description' => _x( 'A pattern that includes all checkout blocks', 'Block pattern description', 'easycommerce' ),
				'categories'  => array( 'easycommerce' ),
				'content'     => Utility::get_template( 'patterns/single-product/template-1.php' ),
			)
		);

		register_block_pattern(
			'easycommerce/single-product-2',
			array(
				'title'       => __( 'Single Product 2', 'easycommerce' ),
				'description' => _x( 'A pattern that includes all checkout blocks', 'Block pattern description', 'easycommerce' ),
				'categories'  => array( 'easycommerce' ),
				'content'     => Utility::get_template( 'patterns/single-product/template-2.php' ),
			)
		);

		$this->register_store_patterns();
	}

	/**
	 * Register the store pattern library (section + page patterns).
	 *
	 * Each file under views/patterns/store/ defines a $pattern_meta array
	 * (title/description/categories/keywords/blockTypes/viewportWidth) and
	 * echoes its block markup. The markup is captured via output buffering,
	 * so pattern copy can be translated inline.
	 */
	public function register_store_patterns() {
		$dir = EASYCOMMERCE_PLUGIN_DIR . 'views/patterns/store/';

		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = glob( $dir . '*.php' );

		if ( empty( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			$pattern_meta = array();

			ob_start();
			include $file;
			$content = ob_get_clean();

			if ( '' === trim( $content ) ) {
				continue;
			}

			$slug = isset( $pattern_meta['slug'] )
				? $pattern_meta['slug']
				: 'easycommerce/' . sanitize_title( basename( $file, '.php' ) );

			$args = wp_parse_args(
				$pattern_meta,
				array(
					'title'      => ucwords( str_replace( '-', ' ', basename( $file, '.php' ) ) ),
					'categories' => array( 'easycommerce' ),
				)
			);

			unset( $args['slug'] );
			$args['content'] = $content;

			register_block_pattern( $slug, $args );
		}
	}

	/**
	 * Registers pattern categories
	 */
	public function register_pattern_categories() {
		register_block_pattern_category(
			'easycommerce',
			array( 'label' => __( 'EasyCommerce', 'easycommerce' ) )
		);
	}
}

<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Thumbnail {

	/**
	 * Registers thumbnail sizes for the plugin.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Fires before registering thumbnail sizes.
		 */
		do_action( 'easycommerce_before_register_thumbnails' );

		// Add theme support for Post Thumbnails
		add_theme_support( 'post-thumbnails' );

		// Define image sizes
		$image_sizes = apply_filters(
			'easycommerce_thumbnail_sizes',
			array(
				'easycommerce-shop-thumbnail'    => array( 600, 600, true ),
				'easycommerce-gallery-image'     => array( 700, 470, true ),
				'easycommerce-gallery-thumbnail' => array( 180, 120, true ),
			)
		);

		// Register image sizes
		foreach ( $image_sizes as $name => $size ) {
			add_image_size( $name, $size[0], $size[1], $size[2] );
		}

		/**
		 * Fires after registering thumbnail sizes.
		 */
		do_action( 'easycommerce_after_register_thumbnails' );
	}
}

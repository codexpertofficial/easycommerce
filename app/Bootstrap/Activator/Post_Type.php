<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Post_Type {

	/**
	 * Registers the custom post type for products.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Filters the labels for the product post type.
		 *
		 * @param array $labels The default labels.
		 */
		$labels = apply_filters(
			'easycommerce_product_post_type_labels',
			array(
				'name'               => _x( 'Products', 'post type general name', 'easycommerce' ),
				'singular_name'      => _x( 'Product', 'post type singular name', 'easycommerce' ),
				'menu_name'          => _x( 'Products', 'admin menu', 'easycommerce' ),
				'name_admin_bar'     => _x( 'Product', 'add new on admin bar', 'easycommerce' ),
				'add_new'            => _x( 'Add New', 'product', 'easycommerce' ),
				'add_new_item'       => __( 'Add New Product', 'easycommerce' ),
				'new_item'           => __( 'New Product', 'easycommerce' ),
				'edit_item'          => __( 'Edit Product', 'easycommerce' ),
				'view_item'          => __( 'View Product', 'easycommerce' ),
				'all_items'          => __( 'Products', 'easycommerce' ),
				'search_items'       => __( 'Search Products', 'easycommerce' ),
				'parent_item_colon'  => __( 'Parent Products:', 'easycommerce' ),
				'not_found'          => __( 'No products found.', 'easycommerce' ),
				'not_found_in_trash' => __( 'No products found in Trash.', 'easycommerce' ),
			)
		);

		/**
		 * Filters the arguments for the product post type.
		 *
		 * @param array $args The default arguments.
		 */
		$args = apply_filters(
			'easycommerce_product_post_type_args',
			array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'show_in_admin_bar'  => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'products' ),
				'capability_type'    => 'page',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
				'show_in_rest'       => true,
				'template'           => array(
					array(
						'core/columns',
						array(
							'style' => array(
								'spacing' => array(
									'padding' => array(
										'top'    => 'var:preset|spacing|30',
										'bottom' => 'var:preset|spacing|30',
										'left'   => 'var:preset|spacing|30',
										'right'  => 'var:preset|spacing|30',
									),
								),
							),
						),
						array(
							array(
								'core/column',
								array(
									'width' => '60%',
									'style' => array(
										'spacing' => array(
											'blockGap' => 'var:preset|spacing|10',
										),
										'border' => array(
											'width' => '0px',
											'style' => 'none',
										),
									),
								),
								array(
									array( 'easycommerce/single-product-gallery', array(), array() ),
								),
							),
							array(
								'core/column',
								array(
									'width' => '40%',
									'style' => array(
										'spacing' => array(
											'padding' => array(
												'left' => '0',
											),
										),
									),
								),
								array(
									array( 'easycommerce/single-product-title', array(), array() ),
									array( 'easycommerce/single-product-rating', array(), array() ),
									array( 'easycommerce/single-product-price', array(), array() ),
									array( 'easycommerce/single-product-summary', array(), array() ),
									array( 'easycommerce/single-product-stock', array(), array() ),
									array( 'easycommerce/single-product-attributes', array(), array() ),
									array( 'easycommerce/single-product-add-to-cart', array(), array() ),
								),
							),
						),
					),
					array(
						'core/columns',
						array(
							'style' => array(
								'spacing' => array(
									'padding' => array(
										'right' => 'var:preset|spacing|30',
										'left'  => 'var:preset|spacing|30',
										'top'   => 'var:preset|spacing|20',
										'bottom' => 'var:preset|spacing|20',
									),
								),
							),
						),
						array(
							array(
								'core/column',
								array(),
								array(
									array( 'easycommerce/single-product-product-tab', array(), array() ),
								),
							),
						),
					),
				),
			)
		);

		/**
		 * Fires before registering the product post type.
		 */
		do_action( 'easycommerce_before_register_product_post_type', $args );

		if ( ! post_type_exists( easycommerce_product_post_type() ) ) {
			register_post_type( easycommerce_product_post_type(), $args );
		}

		/**
		 * Fires after registering the product post type.
		 */
		do_action( 'easycommerce_after_register_product_post_type', $args );
	}

	// Set default content for new products
	public function insert_default_content ( $post_id, $post, $update ) {
		if ( $post->post_type === easycommerce_product_post_type() && ! $update && empty( $post->post_content ) ) {
			wp_update_post( array(
				'ID'           => $post_id,
				'post_content' => \EasyCommerce\Helpers\Utility::get_template( 'patterns/single-product/template-1.php' ),
			) );
		}
	}
}

<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Taxonomy {

	/**
	 * Registers custom taxonomies for the plugin.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Filters the labels for the product category taxonomy.
		 *
		 * @param array $category_labels The default labels.
		 */
		$category_labels = apply_filters(
			'easycommerce_product_category_labels',
			array(
				'name'              => _x( 'Categories', 'taxonomy general name', 'easycommerce' ),
				'singular_name'     => _x( 'Category', 'taxonomy singular name', 'easycommerce' ),
				'search_items'      => __( 'Search Categories', 'easycommerce' ),
				'all_items'         => __( 'All Categories', 'easycommerce' ),
				'parent_item'       => __( 'Parent Category', 'easycommerce' ),
				'parent_item_colon' => __( 'Parent Category:', 'easycommerce' ),
				'edit_item'         => __( 'Edit Category', 'easycommerce' ),
				'update_item'       => __( 'Update Category', 'easycommerce' ),
				'add_new_item'      => __( 'Add New Category', 'easycommerce' ),
				'new_item_name'     => __( 'New Category Name', 'easycommerce' ),
				'menu_name'         => __( 'Categories', 'easycommerce' ),
			)
		);

		/**
		 * Filters the arguments for the product category taxonomy.
		 *
		 * @param array $category_args The default arguments.
		 */
		$category_args = apply_filters(
			'easycommerce_product_category_args',
			array(
				'hierarchical'      => true,
				'labels'            => $category_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'product-cat' ),
				'show_in_rest'      => true,
			)
		);

		/**
		 * Fires before registering the product category taxonomy.
		 */
		do_action( 'easycommerce_before_register_product_category', $category_args );

		register_taxonomy( 'product_cat', apply_filters( 'easycommerce_product_cat_post_types', array( 'product' ) ), $category_args );

		/**
		 * Fires after registering the product category taxonomy.
		 */
		do_action( 'easycommerce_after_register_product_category', $category_args );

		/**
		 * Filters the labels for the product brand taxonomy.
		 *
		 * @param array $brand_labels The default labels.
		 */
		$brand_labels = apply_filters(
			'easycommerce_product_brand_labels',
			array(
				'name'              => _x( 'Brands', 'taxonomy general name', 'easycommerce' ),
				'singular_name'     => _x( 'Brand', 'taxonomy singular name', 'easycommerce' ),
				'search_items'      => __( 'Search Brands', 'easycommerce' ),
				'all_items'         => __( 'All Brands', 'easycommerce' ),
				'parent_item'       => __( 'Parent Brand', 'easycommerce' ),
				'parent_item_colon' => __( 'Parent Brand:', 'easycommerce' ),
				'edit_item'         => __( 'Edit Brand', 'easycommerce' ),
				'update_item'       => __( 'Update Brand', 'easycommerce' ),
				'add_new_item'      => __( 'Add New Brand', 'easycommerce' ),
				'new_item_name'     => __( 'New Brand Name', 'easycommerce' ),
				'menu_name'         => __( 'Brands', 'easycommerce' ),
			)
		);

		/**
		 * Filters the arguments for the product brand taxonomy.
		 *
		 * @param array $brand_args The default arguments.
		 */
		$brand_args = apply_filters(
			'easycommerce_product_brand_args',
			array(
				'hierarchical'      => true,
				'labels'            => $brand_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'product-brand' ),
				'show_in_rest'      => true,
			)
		);

		/**
		 * Fires before registering the product brand taxonomy.
		 */
		do_action( 'easycommerce_before_register_product_brand', $brand_args );

		register_taxonomy( 'product_brand', apply_filters( 'easycommerce_product_brand_post_types', array( 'product' ) ), $brand_args );

		/**
		 * Fires after registering the product brand taxonomy.
		 */
		do_action( 'easycommerce_after_register_product_brand', $brand_args );


		/**
		 * Filters the labels for the product tag taxonomy.
		 *
		 * @param array $tag_labels The default labels.
		 */

		$tag_labels = apply_filters(
			'easycommerce_product_tag_labels',
			array(
				'name'              => _x( 'Tags', 'taxonomy general name', 'easycommerce' ),
				'singular_name'     => _x( 'Tag', 'taxonomy singular name', 'easycommerce' ),
				'search_items'      => __( 'Search Tags', 'easycommerce' ),
				'all_items'         => __( 'All Tags', 'easycommerce' ),
				'parent_item'       => __( 'Parent Tag', 'easycommerce' ),
				'parent_item_colon' => __( 'Parent Tag:', 'easycommerce' ),
				'edit_item'         => __( 'Edit Tag', 'easycommerce' ),
				'update_item'       => __( 'Update Tag', 'easycommerce' ),
				'add_new_item'      => __( 'Add New Tag', 'easycommerce' ),
				'new_item_name'     => __( 'New Tag Name', 'easycommerce' ),
				'menu_name'         => __( 'Tags', 'easycommerce' ),
			)
		);

		/**
		 * Filters the arguments for the product tag taxonomy.
		 *
		 * @param array $tag_args The default arguments.
		 */
		$tag_args = apply_filters(
			'easycommerce_product_tag_args',
			array(
				'hierarchical'      => true,
				'labels'            => $tag_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'product-tag' ),
				'show_in_rest'      => true,
			)
		);

		/**
		 * Fires before registering the product tag taxonomy.
		 */
		do_action( 'easycommerce_before_register_product_tag', $tag_args );

		register_taxonomy( 'product_tag', apply_filters( 'easycommerce_product_tag_post_types', array( 'product' ) ), $tag_args );

		/**
		 * Fires after registering the product tag taxonomy.
		 */
		do_action( 'easycommerce_after_register_product_tag', $tag_args );
	}
}

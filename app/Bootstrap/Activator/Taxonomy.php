<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Taxonomy {

	public function register() {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			$this->registerProductCategory();
		}

		if ( ! taxonomy_exists( 'product_brand' ) ) {
			$this->registerProductBrand();
		}

		if ( ! taxonomy_exists( 'product_tag' ) ) {
			$this->registerProductTag();
		}
	}

	private function registerProductCategory() {
		$labels = apply_filters(
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

		$args = apply_filters(
			'easycommerce_product_category_args',
			array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'product-cat' ),
				'show_in_rest'      => true,
			)
		);

		do_action( 'easycommerce_before_register_product_category', $args );
		register_taxonomy( 'product_cat', apply_filters( 'easycommerce_product_cat_post_types', array( 'product' ) ), $args );
		do_action( 'easycommerce_after_register_product_category', $args );
	}

	private function registerProductBrand() {
		$labels = apply_filters(
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

		$args = apply_filters(
			'easycommerce_product_brand_args',
			array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'product-brand' ),
				'show_in_rest'      => true,
			)
		);

		do_action( 'easycommerce_before_register_product_brand', $args );
		register_taxonomy( 'product_brand', apply_filters( 'easycommerce_product_brand_post_types', array( 'product' ) ), $args );
		do_action( 'easycommerce_after_register_product_brand', $args );
	}

	private function registerProductTag() {
		$labels = apply_filters(
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

		$args = apply_filters(
			'easycommerce_product_tag_args',
			array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'product-tag' ),
				'show_in_rest'      => true,
			)
		);

		do_action( 'easycommerce_before_register_product_tag', $args );
		register_taxonomy( 'product_tag', apply_filters( 'easycommerce_product_tag_post_types', array( 'product' ) ), $args );
		do_action( 'easycommerce_after_register_product_tag', $args );
	}
}

<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Models\Taxonomy as Taxonomy_Model;

class Taxonomy extends API {

	use Cleaner;

	public function get_categories( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$per_page		= (int) ( $request->get_param( 'per_page' ) ?? 10 );
		$page			= (int) ( $request->get_param( 'page' ) ?? 1 );
		$offset			= ( $page - 1 ) * $per_page;
		$taxonomy		= 'product_cat';
		$total_terms 	= wp_count_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$categories		= $taxonomy_model->list_terms( 0, $taxonomy, $per_page, $offset );

		/**
		 * Filters the categories.
		 *
		 * @since 1.9
		 * @param array $categories The categories.
		 * @param WP_REST_Request $request The request object.
		 */
		$categories = apply_filters( 'easycommerce_get_categories', $categories, $request );

		$this->response_success(
			array(
				'message'    => __( 'Categories found.', 'easycommerce' ),
				'categories' => $categories,
				'pagination' => array(
					'total'        => (int) $total_terms,
					'per_page'     => $per_page,
					'current_page' => $page,
					'total_pages'  => ceil( $total_terms / $per_page ),
				),
			)
		);
	}

	public function add_category( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$name   = $request->get_param( 'name' );
		$slug   = $request->get_param( 'slug' );
		$parent = $request->get_param( 'parent' );

		$data = array(
			'name'   => $name,
			'slug'   => $slug,
			'parent' => $parent,
		);

		/**
		 * Filters the category data before adding.
		 *
		 * @since 1.9
		 * @param array $data The category data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_add_category_data', $data, $request );

		/**
		 * Fires before adding a category.
		 *
		 * @since 1.9
		 * @param array $data The category data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_add_category', $data, $request );

		$result = $taxonomy_model->add_term( $name, 'product_cat', $slug, $parent );

		/**
		 * Fires after adding a category.
		 *
		 * @since 1.9
		 * @param int $term_id The term ID.
		 * @param array $data The category data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_add_category', $result, $data, $request );

		if ( is_wp_error( $result ) ) {
			$this->response_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		$this->response_success(
			array(
				'message'  => __( 'Category created.', 'easycommerce' ),
				'category' => $result,
			),
			201
		);
	}

	public function update_category( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$id     = $request->get_param( 'id' );
		$name   = $request->get_param( 'name' );
		$slug   = $request->get_param( 'slug' );
		$parent = $request->get_param( 'parent' );

		$data = array(
			'id'     => $id,
			'name'   => $name,
			'slug'   => $slug,
			'parent' => $parent,
		);

		/**
		 * Filters the category update data.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_update_category_data', $data, $request );

		/**
		 * Fires before updating a category.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_category', $data, $request );

		$result = $taxonomy_model->update_term( $id, $name, 'product_cat', $slug, $parent );

		/**
		 * Fires after updating a category.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_category', $data, $request );
	
		if ( is_wp_error( $result ) ) {
			$this->response_error(
				[ 'message' => $result->get_error_message() ]
			);
		}
	
		$this->response_success(
			[
				'message'  => __( 'Category updated.', 'easycommerce' ),
				'category' => $result,
			]
		);
	}

	public function delete_category( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$id       = $request->get_param( 'id' );

		/**
		 * Fires before deleting a category.
		 *
		 * @since 1.9
		 * @param int $id The category ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_category', $id, $request );

		$deleted  = $taxonomy_model->delete_term( $id, 'product_cat' );

		/**
		 * Fires after deleting a category.
		 *
		 * @since 1.9
		 * @param int $id The category ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_category', $id, $request );

		if ( ! $deleted ) {
			$this->response_error(
				array(
					'message' => __( 'Category not deleted', 'easycommerce' ),
				)
			);
		}

		$this->response_success(
			array(
				'message' => __( 'Category deleted', 'easycommerce' ),
			),
			201
		);
	}

	public function bulk_delete_categories( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$ids = $request->get_param( 'ids' );

		/**
		 * Fires before deleting a category in bulk.
		 *
		 * @since 1.9
		 * @param int $id The category ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_bulk_delete_category', $ids, $request );

		$taxonomy_model->bulk_delete( $ids, 'product_cat' );

		/**
		 * Fires after deleting a category in bulk.
		 *
		 * @since 1.9
		 * @param int $id The category ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_bulk_delete_category', $ids, $request );

		$this->response_success(
			array(
				'message' => __( 'Categories deleted', 'easycommerce' ),
			),
			201
		);
	}

	public function get_brands( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$per_page		= (int) ( $request->get_param( 'per_page' ) ?? 10 );
		$page			= (int) ( $request->get_param( 'page' ) ?? 1 );
		$offset			= ( $page - 1 ) * $per_page;
		$taxonomy		= 'product_brand';
		$total_terms	= wp_count_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$brands			= $taxonomy_model->list_terms( 0, $taxonomy, $per_page, $offset );

		/**
		 * Filters the brands.
		 *
		 * @since 1.9
		 * @param array $brands The brands.
		 * @param WP_REST_Request $request The request object.
		 */
		$brands = apply_filters( 'easycommerce_get_brands', $brands, $request );

		$this->response_success(
			array(
				'message' => __( 'Brands found.', 'easycommerce' ),
				'brands'  => $brands,
				'pagination' => array(
					'total'        => (int) $total_terms,
					'per_page'     => $per_page,
					'current_page' => $page,
					'total_pages'  => ceil( $total_terms / $per_page ),
				),
			)
		);
	}

	public function add_brand( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$name   = $request->get_param( 'name' );
		$parent = $request->get_param( 'parent' );
		$slug   = $request->get_param( 'slug' );

		$data = array(
			'name'   => $name,
			'parent' => $parent,
			'slug'   => $slug,
		);

		/**
		 * Filters the brand data before adding.
		 *
		 * @since 1.9
		 * @param array $data The brand data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_add_brand_data', $data, $request );

		/**
		 * Fires before adding a brand.
		 *
		 * @since 1.9
		 * @param array $data The brand data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_add_brand', $data, $request );

		$result = $taxonomy_model->add_term( $name, 'product_brand', $slug, $parent );

		/**
		 * Fires after adding a brand.
		 *
		 * @since 1.9
		 * @param int $term_id The term ID.
		 * @param array $data The brand data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_add_brand', $result, $data, $request );

		if ( is_wp_error( $result ) ) {
			$this->response_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		$this->response_success(
			array(
				'message' => __( 'Brand created.', 'easycommerce' ),
				'brand'   => $result,
			),
			201
		);
	}

	public function update_brand( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$id     = $request->get_param( 'id' );
		$name   = $request->get_param( 'name' );
		$slug   = $request->get_param( 'slug' );
		$parent = $request->get_param( 'parent' );

		$data = array(
			'id'     => $id,
			'name'   => $name,
			'slug'   => $slug,
			'parent' => $parent,
		);

		/**
		 * Filters the brand update data.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_update_brand_data', $data, $request );

		/**
		 * Fires before updating a brand.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_brand', $data, $request );

		$result = $taxonomy_model->update_term( $id, $name, 'product_brand', $slug, $parent );

		/**
		 * Fires after updating a brand.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_brand', $data, $request );
	
		if ( is_wp_error( $result ) ) {
			$this->response_error(
				[ 'message' => $result->get_error_message() ]
			);
		}
	
		$this->response_success(
			[
				'message'  => __( 'brand updated.', 'easycommerce' ),
				'brand' => $result,
			]
		);
	}


	public function delete_brand( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$id       = $request->get_param( 'id' );

		/**
		 * Fires before deleting a brand.
		 *
		 * @since 1.9
		 * @param int $id The brand ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_brand', $id, $request );

		$deleted  = $taxonomy_model->delete_term( $id, 'product_brand' );

		/**
		 * Fires after deleting a brand.
		 *
		 * @since 1.9
		 * @param int $id The brand ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_brand', $id, $request );
		
		if ( ! $deleted ) {
			$this->response_error(
				array(
					'message' => __( 'Brand not deleted', 'easycommerce' ),
				)
			);
		}

		$this->response_success(
			array(
				'message' => __( 'Brand deleted', 'easycommerce' ),
			),
			201
		);
	}

	public function bulk_delete_brands( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$ids = $request->get_param( 'ids' );

		/**
		 * Fires before deleting a brand in bulk.
		 *
		 * @since 1.9
		 * @param int $id The brand ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_bulk_delete_brand', $ids, $request );

		$taxonomy_model->bulk_delete( $ids, 'product_brand' );

		/**
		 * Fires after deleting a brand in bulk.
		 *
		 * @since 1.9
		 * @param int $id The brand ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_bulk_delete_brand', $ids, $request );

		$this->response_success(
			array(
				'message' => __( 'Brands deleted', 'easycommerce' ),
			),
			201
		);
	}
	
	public function get_tags( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$per_page		= (int) ( $request->get_param( 'per_page' ) ?? 10 );
		$page			= (int) ( $request->get_param( 'page' ) ?? 1 );
		$offset			= ( $page - 1 ) * $per_page;
		$taxonomy		= 'product_tag';
		$total_terms	= wp_count_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$tags			= $taxonomy_model->list_terms( 0, $taxonomy, $per_page, $offset );

		/**
		 * Filters the tags.
		 *
		 * @since 1.9
		 * @param array $tags The tags.
		 * @param WP_REST_Request $request The request object.
		 */
		$tags = apply_filters( 'easycommerce_get_tags', $tags, $request );

		$this->response_success(
			array(
				'message' => __( 'tags found.', 'easycommerce' ),
				'tags'  => $tags,
				'pagination' => array(
					'total'        => (int) $total_terms,
					'per_page'     => $per_page,
					'current_page' => $page,
					'total_pages'  => ceil( $total_terms / $per_page ),
				),
			)
		);
	}

	public function add_tag( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$name   = $request->get_param( 'name' );
		$slug   = $request->get_param( 'slug' );

		$data = array(
			'name' => $name,
			'slug' => $slug,
		);

		/**
		 * Filters the tag data before adding.
		 *
		 * @since 1.9
		 * @param array $data The tag data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_add_tag_data', $data, $request );

		/**
		 * Fires before adding a tag.
		 *
		 * @since 1.9
		 * @param array $data The tag data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_add_tag', $data, $request );

		$result = $taxonomy_model->add_term( $name, 'product_tag', $slug );

		/**
		 * Fires after adding a tag.
		 *
		 * @since 1.9
		 * @param int $term_id The term ID.
		 * @param array $data The tag data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_add_tag', $result, $data, $request );

		if ( is_wp_error( $result ) ) {
			$this->response_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		$this->response_success(
			array(
				'message' => __( 'tag created.', 'easycommerce' ),
				'tag'   => $result,
			),
			201
		);
	}

	public function update_tag( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$id     = $request->get_param( 'id' );
		$name   = $request->get_param( 'name' );
		$slug   = $request->get_param( 'slug' );

		$data = array(
			'id'   => $id,
			'name' => $name,
			'slug' => $slug,
		);

		/**
		 * Filters the tag update data.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_update_tag_data', $data, $request );

		/**
		 * Fires before updating a tag.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_tag', $data, $request );

		$result = $taxonomy_model->update_term( $id, $name, 'product_tag', $slug );

		/**
		 * Fires after updating a tag.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_tag', $data, $request );
	
		if ( is_wp_error( $result ) ) {
			$this->response_error(
				[ 'message' => $result->get_error_message() ]
			);
		}
	
		$this->response_success(
			[
				'message'  	=> __( 'tag updated.', 'easycommerce' ),
				'tag' 		=> $result,
			]
		);
	}

	public function delete_tag( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$id 		= $request->get_param( 'id' );

		/**
		 * Fires before deleting a tag.
		 *
		 * @since 1.9
		 * @param int $id The tag ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_tag', $id, $request );

		$deleted 	= $taxonomy_model->delete_term( $id, 'product_tag' );

		/**
		 * Fires after deleting a tag.
		 *
		 * @since 1.9
		 * @param int $id The tag ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_tag', $id, $request );

		if ( ! $deleted ) {
			$this->response_error(
				array(
					'message' => __( 'tag not deleted', 'easycommerce' ),
				)
			);
		}

		$this->response_success(
			array(
				'message' => __( 'tag deleted', 'easycommerce' ),
			),
			201
		);
	}

	public function bulk_delete_tags( $request ) {
		$taxonomy_model = new Taxonomy_Model();
		$ids = $request->get_param( 'ids' );

		/**
		 * Fires before deleting a tag in bulk.
		 *
		 * @since 1.9
		 * @param int $id The tag ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_bulk_delete_tag', $ids, $request );

		$taxonomy_model->bulk_delete( $ids, 'product_tag' );

		/**
		 * Fires after deleting a tag in bulk.
		 *
		 * @since 1.9
		 * @param int $id The tag ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_bulk_delete_tag', $ids, $request );

		$this->response_success(
			array(
				'message' => __( 'tags deleted', 'easycommerce' ),
			),
			201
		);
	}
}

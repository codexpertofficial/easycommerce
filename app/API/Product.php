<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Product as Product_Model;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Helpers\Utility;

class Product extends API {

	public function list( $request ) {

		$search_query = $request->get_param( 's' );
		$sku          = $request->get_param( 'sku' );
		$categories   = $request->get_param( 'categories' );
		$sort_by      = $request->get_param( 'sort_by' ) ?: '';
		$brands       = $request->get_param( 'brands' );
		$attributes   = $request->get_param( 'attributes' );
		$min_price    = $request->get_param( 'min_price' ) ?: '';
		$max_price    = $request->get_param( 'max_price' ) ?: '';
		$status       = $request->get_param( 'status' ) ?: 'publish';
		$per_page     = $request->get_param( 'per_page' ) ?: 10;
		$page         = $request->get_param( 'page' ) ?: 1;
		$show_prices  = $request->get_param( 'show_prices' ) == true;
		$return_html  = $request->get_param( 'return_html' ) == true;
		$settings     = $request->get_param( 'settings' );
		$is_shop      = $request->get_param( 'is_shop' ) == 1;
		$shop_name    = $request->get_param( 'shop_name' ) ?: 'template-1';
		$view 		  = $request->get_param('view') ?: 'easycommerce-st-grid';

		$filters = array(
			'search'     => $search_query,
			'sku'        => $sku,
			'status'     => $status,
			'categories' => $categories,
			'sort_by'    => $sort_by,
			'brands'     => $brands,
			'attributes' => $attributes,
			'min_price'  => $min_price,
			'max_price'  => $max_price,
			'is_shop'    => $is_shop,
		);

		$result          = Product_Model::list( $filters, $per_page, ( $page - 1 ) * $per_page, true, true );

		/**
		 * Filters the product list result.
		 *
		 * @since 1.9
		 * @param array $result The result.
		 * @param array $filters The filters.
		 * @param WP_REST_Request $request The request object.
		 */
		$result = apply_filters( 'easycommerce_api_product_list', $result, $filters, $request );

		$products        = $result['products'];
		$total_products  = $result['total'];

		$pagination_html = '';
		$total_pages     = ceil( $total_products / $per_page );

		if ( empty( $products ) ) {
			return $this->response_success( array( 'total' => 0, 'message' => __( 'No products found', 'easycommerce' ) ) );
		}

		$formatted_products = array_map(
			function ( $product ) use ( $show_prices ) {
				$list = array(
					'id'                   => $product->get_id(),
					'title'                => $product->get_title(),
					'slug'                 => $product->get_slug(),
					'description' 		   => $product->get_description(),
					'summary'  	  		   => $product->get_summary(),
					'status'               => $product->get_status(),
					'link'                 => $product->get_url(),
					'thumbnail'            => $product->get_thumbnail( 'easycommerce-shop-thumbnail' ),
					'rating'               => $product->get_rating(),
					'rating_count'         => $product->get_rating_count(),
					'price'                => $product->get_price(),
					'sale_price'           => $product->get_sale_price(),
					'formatted_price'      => $product->get_price( false ),
					'formatted_sale_price' => $product->get_sale_price( false ),
					'stock'                => $product->get_stock(),
					'categories'           => $product->get_categories(),
					'tags'                 => $product->get_tags(),
					'brands'               => $product->get_brands(),
					'sales'                => $product->get_sales(),
					'attributes'           => $product->get_attributes(),
					'is_variable'          => $product->is_variable(),
					'badges' => $product->get_badges(),

				);

				if ( $show_prices ) {
					$list['prices'] = $product->get_prices( false );
				}

				return $list;
			},
			$products
		);

		if ( $return_html ) {
			$formatted_products = Utility::get_template(
				"blocks/shops-page/{$shop_name}/shop.php",
				array(
					'products' => $formatted_products,
					'settings' => $request,
					'view'     => $view,
				)
			);
			$pagination_html = Utility::get_template(
				"blocks/shops-page/{$shop_name}/inc/pagination.php",
				array(
					'total_pages' => $total_pages,
					'current_page' => $page,
				)
			);
		}

		return $this->response_success(
			array(
				'products'        => $formatted_products,
				'pagination'      => $pagination_html,
				'total'           => $total_products,
				'per_page'        => $per_page,
				'page'            => $page,
				'total_pages'     => $total_pages,
				'statuses_counts' => $result['statuses_counts'],
			)
		);
	}

	/**
	 * Get a product.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get( $request ) {
		$id      = $request->get_param( 'id' );
		$product = new Product_Model( $id );

		if ( empty( $id ) || ! $product->exists() ) {
			return $this->response_error( __( 'Product not found.', 'easycommerce' ) );
		}

		// Prepare the product data for the response
		$product_data = array(
			'id'          => $product->get_id(),
			'attributes'  => $product->get_attributes(),
			'title'       => $product->get_title(),
			'slug'        => $product->get_slug(),
			'description' => $product->get_description(),
			'summary'  	  => $product->get_summary(),
			'url'         => $product->get_url(),
			'thumbnail'   => $product->get_thumbnail(),
			'variations'  => $product->get_prices( false ),
			'price'       => $product->get_price(),
			'sale_price'  => $product->get_sale_price(),
			'rating'      => $product->get_rating(),
			'categories'  => $product->get_categories(),
			'tags'        => $product->get_tags(),
			'badges'      => $product->get_badges(),
			'brands'      => $product->get_brands(),
			'sales'       => $product->get_sales(),
			'stock'       => $product->get_stock(),
			'status'      => $product->get_status(),
			'gallery'     => $product->get_gallery(),
			'meta'        => $product->get_meta(),
			'created_at'  => $product->get_created_at(),
			'updated_at'  => $product->get_updated_at(),
		);

		/**
		 * Filters the product data before returning the response.
		 *
		 * @since 1.9
		 * @param array $product_data The product data.
		 * @param int $id The product ID.
		 */
		$product_data = apply_filters( 'easycommerce_get_product_data', $product_data, $id );

		/**
		 * Fires after a product is retrieved.
		 *
		 * @param array $product_data The product data.
		 * @param int $id The product ID.
		 */
		do_action( 'easycommerce_get_product', $product_data, $id );

		$this->response_success( $product_data );
	}

	/**
	 * Create a new product.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create( $request ) {

		/**
		 * Filters the arguments for creating a new product.
		 *
		 * @since 1.9
		 * @param array $args The product arguments.
		 */
		$args     = apply_filters( 'easycommerce_create_product_args', $request->get_params() );

		/**
		 * Fires before creating a product.
		 *
		 * @since 1.9
		 * @param array $args The product arguments.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_create_product', $args, $request );

		$product  = new Product_Model();
		$created  = $product->create( $args );

		/**
		 * Fires after creating a product.
		 *
		 * @since 1.9
		 * @param int $product_id The product ID.
		 * @param array $args The product arguments.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_create_product', $product->get_id(), $args, $request );

		if ( is_wp_error( $created ) ) {
			$this->response_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		if ( ! $created ) {
			$this->response_error( __( 'Failed to create product.', 'easycommerce' ) );
		}

		$product_data = array(
			'id'    => $product->get_id(),
			'title' => $product->get_title(),
		);

		/**
		 * Fires after a product is created.
		 *
		 * @param array $product_data The product data.
		 */
		do_action( 'easycommerce_create_product', $product_data );
		
		do_action( 'easycommerce_log', array( 'object' => 'product', 'action' => 'create', 'object_id' => $product->get_id(), 'note' => $product_data['title'] ) );

		$this->response_success(
			array(
				'message' => __( 'Product created', 'easycommerce' ),
				'product' => $product_data,
			)
		);
	}

	/**
	 * Update a product.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( $request ) {

		/**
		 * Filters the arguments for creating a new product.
		 *
		 * @param array $args The product arguments.
		 */
		$args     = apply_filters( 'easycommerce_update_product_args', $request->get_params() );
		$product  = new Product_Model( $request->get_param( 'id' ) );

		if ( ! $product->exists() ) {
			$this->response_error( __( 'Product not found.', 'easycommerce' ) );
		}

		if ( isset( $args['meta']['template'] ) ) {
			$args['content'] = Utility::get_template( "patterns/single-product/{$args['meta']['template']}.php" );
		}

		$updated = $product->update( $args );

		if ( ! $updated ) {
			$this->response_error( __( 'Failed to update product.', 'easycommerce' ) );
		}

		$product_data = array(
			'id'    => $product->get_id(),
			'title' => $product->get_title(),
		);

		/**
		 * Fires after a product is updated.
		 *
		 * @param array $product_data The product data.
		 */
		do_action( 'easycommerce_update_product', $product_data );

		$this->response_success(
			array(
				'message' => __( 'Product updated', 'easycommerce' ),
				'product' => $product_data,
			)
		);
	}

	/**
	 * Delete a product.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$id      = $request->get_param( 'id' );
		$force   = $request->get_param( 'force' ) == true;
		$product = new Product_Model( $id );

		if ( ! $product->exists() ) {
			$this->response_success( array( 'message' => __( 'Product not found.', 'easycommerce' ) ) );
		}

		/**
		 * Fires before a product is deleted.
		 *
		 * @param int $id The product ID.
		 */
		do_action( 'easycommerce_before_delete_product', $id );

		if ( ! $product->is_deletable() ) {
			$this->response_error( __( 'Product can\'t be deleted.', 'easycommerce' ) );
		}

		$deleted = $product->delete( $force );

		if ( ! $deleted ) {
			$this->response_error( __( 'Failed to delete product.', 'easycommerce' ) );
		}

		/**
		 * Fires after a product is deleted.
		 *
		 * @param int $id The product ID.
		 */
		do_action( 'easycommerce_delete_product', $id );

		do_action( 'easycommerce_log', array( 'object' => 'product', 'action' => 'delete', 'object_id' => $product->get_id(), 'note' => $product->get_title() ) );

		$this->response_success( __( 'Product deleted successfully.', 'easycommerce' ) );
	}

	/**
	 * Bulk delete products.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function bulk_delete( $request ) {
		$product_ids = $request->get_param( 'product_ids' );

		if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
			return $this->response_error( __( 'Invalid product IDs.', 'easycommerce' ) );
		}

		$deleted = true;

		foreach ( $product_ids as $id ) {
			$product = new Product_Model( $id );

			if ( ! $product->exists() || ! $product->is_deletable() ) {
				$deleted = false;
				continue;
			}

			/**
			 * Fires before a product is deleted.
			 *
			 * @param int $id The product ID.
			 */
			do_action( 'easycommerce_before_delete_product', $id );

			$result = $product->delete( true ); //always force

			if ( ! $result ) {
				$deleted = false;
				continue;
			}

			/**
			 * Fires after a product is deleted.
			 *
			 * @param int $id The product ID.
			 */
			do_action( 'easycommerce_delete_product', $id );
		}

		if ( ! $deleted ) {
			return $this->response_error( __( 'Failed to delete some products.', 'easycommerce' ) );
		}

		return $this->response_success( __( 'Products deleted successfully.', 'easycommerce' ) );
	}

	// bulk status update
	public function bulk_update_status( $request ) {
		$product_ids = $request->get_param( 'product_ids' );
		$status      = $request->get_param( 'status' );

		if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
			return $this->response_error( __( 'Invalid product IDs.', 'easycommerce' ) );
		}

		foreach ( $product_ids as $id ) {
			$product = new Product_Model( $id );

			if ( ! $product->exists() ) {
				continue;
			}

			/**
			 * Fires before a product's status is updated.
			 *
			 * @param int $id The product ID.
			 */
			do_action( 'easycommerce_before_update_product_status', $id );

			$result = $product->set_status( $status );
			$result = $product->save();

			if ( ! $result ) {
				continue;
			}

			/**
			 * Fires after a product's status is updated.
			 *
			 * @param int $id The product ID.
			 */
			do_action( 'easycommerce_update_product_email', $status, $id );
		}

		return $this->response_success(
			array(
				'message' => __( 'Products updated successfully.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Get review to a product.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_reviews( $request ) {
		$id        = $request->get_param( 'id' );
		$reviews   = array();
		$comments  = get_comments(
			array(
				'post_id' => $id,
				'status'  => 'approve',
			)
		);

		foreach ( $comments as $comment ) {
			$reviews[] = array(
				'id'     => $comment->comment_ID,
				'text'   => $comment->comment_content,
				'rating' => get_comment_meta( $comment->comment_ID, 'rating', true ),
				'user'   => array(
					'id'    => $comment->user_id,
					'email' => $comment->comment_author_email,
					'name'  => $comment->comment_author,
					'photo' => get_avatar_url( $comment->comment_author_email ),
				),
				'time'   => $comment->comment_date,
			);
		}

		$this->response_success(
			array(
				'message' => __( 'Review added', 'easycommerce' ),
				'reviews' => $reviews,
			)
		);
	}

	/**
	 * Add review to a product.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function add_review( $request ) {
		$id             = $request->get_param( 'id' );
		$product        = new Product_Model( $id );
		$text           = $request->get_param( 'text' );
		$rating         = $request->get_param( 'rating' );
		$user           = get_userdata( get_current_user_id() );
		$reviews        = $product->get_reviews();
		$ratings        = array_column( $reviews, 'rating' );
		$ratings[]      = $rating;
		$average_rating = count( $ratings ) > 0 ? array_sum( $ratings ) / count( $ratings ) : 0;

		$data = array(
			'comment_post_ID'      => $id,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_author_url'   => '',
			'comment_content'      => $text,
			'comment_type'         => '',
			'comment_parent'       => 0,
			'user_id'              => $user->ID,
			'comment_approved'     => 1,
		);

		if ( $comment_id = wp_insert_comment( $data ) ) {
			add_comment_meta( $comment_id, 'rating', $rating );

			update_post_meta( $id, 'average_rating', $average_rating );

			do_action(
				'easycommerce_log',
				array(
					'object'    => 'review',
					'action'    => 'create',
					'object_id' => $comment_id,
					// translators: %s: product title.
					'note'      => sprintf( __( 'New review added for %s', 'easycommerce' ), $product->get_title() ),
				)
			);

			do_action( 'easycommerce_review_added', $comment_id, $id, $rating, $text );

			$this->response_success(
				array(
					'message'   => __( 'Review added', 'easycommerce' ),
					'review_id' => $comment_id,
				)
			);
		}

		$this->response_error( __( 'Failed to add review.', 'easycommerce' ) );
	}

	/**
	 * Get a product's variations
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 *
	 * @todo optimize
	 */
	public function get_variations( $request ) {
		$product_id           = $request->get_param( 'id' );
		$requested_attributes = $request->get_param( 'attributes' );
		$product_model        = new Product_Model( $product_id );
		$variations           = $product_model->get_variations();
		$matching_variations  = array();

		foreach ( $variations as $variation ) {
			$variation_model      = new Product_Variation( $variation->id );
			$variation_attributes = $variation_model->get_attributes();
			$is_match             = true;

			foreach ( $requested_attributes as $key => $value ) {
				$attribute_found = false;

				foreach ( $variation_attributes as $attribute ) {
					if ( $attribute->attribute_slug === $key && $attribute->value_slug === $value ) {
						$attribute_found = true;
						break;
					}
				}

				if ( ! $attribute_found ) {
					$is_match = false;
					break;
				}
			}

			if ( $is_match ) {
				$matching_variations[] = $variation;
			}
		}

		if ( empty( $matching_variations ) ) {
			$this->response_success( array( 'message' => __( 'No variations found', 'easycommerce' ) ) );
		} else {
			$this->response_success(
				array(
					'message'    => __( 'Variations found', 'easycommerce' ),
					'variations' => $matching_variations,
				)
			);
		}
	}
}

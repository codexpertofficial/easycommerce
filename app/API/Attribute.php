<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Attribute as Attribute_Model;
use EasyCommerce\Models\Attribute_Value;

class Attribute extends API {

	/**
	 * List all attributes with optional search and details.
	 *
	 * @param \WP_REST_Request $request The request object.
	 */
	public function list( $request ) {
		$attribute_model = new Attribute_Model();

		$s        = $request->get_param( 's' );
		$page     = (int) ( $request->get_param( 'page' ) ?? 1 );
		$per_page = (int) ( $request->get_param( 'per_page' ) ?? 10 );
		$offset   = ( $page - 1 ) * $per_page;
		$args 	= [];
		if ( ! is_null( $s ) ) {
			$args[] = array( 'name' => array( 'LIKE', '%' . $s . '%' ) );
		}
		/**
		 * Filters the arguments used to retrieve attributes.
		 *
		 * @since 1.9
		 * @param array $args The arguments used to retrieve attributes.
		 */
		$args = apply_filters( 'easycommerce_attribute_list_args', $args );
		$total = $attribute_model->db->get_count( $args );

		// Get paginated results
		$rows = $attribute_model->get_all( $args, $per_page, $offset );
	
		$attributes = array_filter(
			array_map(
				function ( $attribute ) {
	
					if ( empty( $attribute->name ) ) {
						return null;
					}

					$attribute_options_model = new Attribute_Value();
					$options                 = $attribute_options_model->get_by( $attribute->id );
					$attribute->options 	 = $options;
					$attribute->count        = count( $options );
	
					unset( $attribute->created_at );
	
					/**
					 * Filters the attribute object before returning it.
					 *
					 * @since 1.9
					 * @param object $attribute The attribute object.
					 */
					return apply_filters( 'easycommerce_attribute_object', $attribute );
				},
				$rows
			)
		);
		/**
		 * Filters the list of attributes before returning the response.
		 *
		 * @since 1.9
		 * @param array $attributes The list of attributes.
		 */
		$attributes = apply_filters( 'easycommerce_attribute_list', array_values( $attributes ) );

		$this->response_success(
			array(
				'attributes' => $attributes,
				'pagination' => array(
					'total'        => (int) $total,
					'per_page'     => $per_page,
					'current_page' => $page,
					'total_pages'  => ceil( $total / $per_page ),
				),
			)
		);
	}

	/**
	 * Add new attributes and their values.
	 *
	 * @param \WP_REST_Request $request The request object.
	 */
	public function add_item( $request ) {
		$attribute_model = new Attribute_Model();
		$name       			= $request->get_param( 'name' );
		$slug       			= $request->get_param( 'slug' );
		$type       			= $request->get_param( 'type' );
		$options    			= $request->get_param( 'options' );
		$attribute_values_model = new Attribute_Value();
		$attribute_id 			= $attribute_model->add( $name, $type, $slug );

		if ( $attribute_id ) {
			foreach ( $options as $option ) {
				if ( ! isset( $option['name'] ) ) continue;
		
				$name  	= $option['name'];
				$slug  	= $option['slug'] ? $option['slug'] : $option['name'];
				$value 	= $option['value'] ? $option['value'] : $option['name'];
		
				$attribute_values_model->add( $attribute_id, $name, $value , $slug );
			}
		}

		/**
		 * Fires after attribute are added.
		 *
		 * @since 1.9
		 * @param int attribute_id The ID of the attribute that were added.
		 */
		do_action( 'easycommerce_attribute_added', $attribute_id );

		$this->response_success(
			array(
				'message' => __( 'Attribute updated.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Get values for a specific attribute.
	 *
	 * @param \WP_REST_Request $request The request object.
	 */
	public function get_values( $request ) {
		$attribute_model = new Attribute_Model();
		$attribute = $request->get_param( 'attribute' );
		$s         = $request->get_param( 's' );

		$attribute_values_model = new Attribute_Value();

		if ( is_numeric( $attribute ) ) {
			$attribute_obj = $attribute_model->get( (int) $attribute );
		} else {
			$attribute_obj = $attribute_model->get_by_slug( $attribute );
		}

		if ( ! isset( $attribute_obj->id ) ) {
			$this->response_error( __( 'Invalid attribute data.', 'easycommerce' ) );
		}

		$values = array_map(
			function ( $value ) {
				unset( $value->attribute_id );

				/**
				 * Filters the attribute value object before returning it.
				 *
				 * @since 1.9
				 * @param object $value The attribute value object.
				 */
				return apply_filters( 'easycommerce_attribute_value_object', $value );
			},
			$attribute_values_model->get_by( $attribute_obj->id, 'attribute_id', $s )
		);

		/**
		 * Filters the list of attribute values before returning the response.
		 *
		 * @since 1.9
		 * @param array $values The list of attribute values.
		 */
		$values = apply_filters( 'easycommerce_attribute_values', $values );

		$this->response_success(
			array(
				'attribute' => $attribute_obj->name,
				'values'    => $values,
			)
		);
	}

	/**
	 * Get a specific attribute.
	 *
	 * @param \WP_REST_Request $request The request object.
	 */
	public function get_attribute( $request ) {
		$attribute_model = new Attribute_Model();
		$attribute_id 				= $request->get_param( 'id' );
		$attribute 					= $attribute_model->get( $attribute_id );
		$attribute_options_model 	= new Attribute_Value();

		if ( ! is_object( $attribute ) || ! isset( $attribute->id ) ) {
			$this->response_error( __( 'Invalid attribute data.', 'easycommerce' ) );
			return;
		}

		$options            = $attribute_options_model->get_by_attribute( $attribute_id );
		$attribute->options = $options;

		if ( isset( $attribute->type ) && $attribute->type === 'Image' ) {
			$attribute->options = array_map(
				function ( $option ) {
					$option->url = wp_get_attachment_url( $option->value );
					return $option;
				},
				$attribute->options
			);
		}

		/**
		 * Filters the attribute data before sending the response.
		 *
		 * @since 1.9
		 * @param object $attribute The attribute object.
		 * @param WP_REST_Request $request The request object.
		 */
		$attribute = apply_filters( 'easycommerce_get_attribute', $attribute, $request );

		$this->response_success( $attribute );
	}

	/**
	 * 	Delete an attribute.
	 *
	 * @param \WP_REST_Request $request The request object.
	 */

	public function delete_attribute( $request ) {
		$attribute_model = new Attribute_Model();
		$attribute_id = $request->get_param( 'id' );

		/**
		 * Fires before an attribute is deleted.
		 *
		 * @since 1.9
		 * @param int $attribute_id The attribute ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_attribute', $attribute_id, $request );

		$attribute_model->delete( $attribute_id );

		/**
		 * Fires after an attribute is deleted.
		 *
		 * @since 1.9
		 * @param int $attribute_id The attribute ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_attribute', $attribute_id, $request );

		$this->response_success(
			array(
				'message' => __( 'Attribute deleted.', 'easycommerce' ),
			)
		);
	}

	public function bulk_delete_attributes( $request ) {
		$attribute_model = new Attribute_Model();
		$attribute_ids = $request->get_param( 'ids' );

		if ( ! is_array( $attribute_ids ) ) {
			$this->response_error( __( 'Invalid attribute IDs.', 'easycommerce' ) );
			return;
		}

		/**
		 * Fires before an attribute is deleted.
		 *
		 * @since 1.9
		 * @param int $attribute_id The attribute ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_attribute', $attribute_ids, $request );

		$attribute_model->bulk_delete( $attribute_ids );

		/**
		 * Fires after an attribute is deleted.
		 *
		 * @since 1.9
		 * @param int $attribute_id The attribute ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_attribute', $attribute_ids, $request );

		$this->response_success(
			array(
				'message' => __( 'Attributes deleted.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Update an attribute.
	 *
	 * @param \WP_REST_Request $request The request object.
	 */
	public function update_attribute( $request ) {
		$attribute_model = new Attribute_Model();
		$attribute_id 				= $request->get_param( 'id' );
		$name         				= $request->get_param( 'name' );
		$slug         				= $request->get_param( 'slug' );
		$type         				= $request->get_param( 'type' );
		$attribute_values     		= $request->get_param( 'options' );
		$attribute_value_model 		= new Attribute_Value();

		$attribute = [
			'name'  => $name,
			'slug'  => $slug,
			'type'  => $type,
		];

		/**
		 * Filters the attribute data before updating.
		 *
		 * @since 1.9
		 * @param array $attribute The attribute data.
		 * @param int $attribute_id The attribute ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$attribute = apply_filters( 'easycommerce_update_attribute_data', $attribute, $attribute_id, $request );
	
		/**
		 * Fires before an attribute is updated.
		 *
		 * @since 1.9
		 * @param int $attribute_id The attribute ID.
		 * @param array $attribute The attribute data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_attribute', $attribute_id, $attribute, $request );

		$attribute_model->update( $attribute_id, $attribute );

		/**
		 * Fires after an attribute is updated.
		 *
		 * @since 1.9
		 * @param int $attribute_id The attribute ID.
		 * @param array $attribute The attribute data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_attribute', $attribute_id, $attribute, $request );
	
		$existing_values 	= $attribute_value_model->get_by_attribute( $attribute_id );
		$existing_ids    	= wp_list_pluck( $existing_values, 'id' );
		$updated_ids 		= array_filter( wp_list_pluck( $attribute_values, 'id' ) );
		$ids_to_delete 		= array_diff( $existing_ids, $updated_ids );
	
		foreach ( $attribute_values as $key => $attribute_value ) {
			if ( isset( $attribute_value['id'] ) && $attribute_value['id'] ) {
				$attribute_value_model->update( $attribute_value['id'], $attribute_value );
			} else {
				$attribute_value_model->add( $attribute_id, $attribute_value['name'], $attribute_value['value'] );
			}
		}
	
		foreach ( $ids_to_delete as $id ) {
			$attribute_value_model->delete( $id );
		}
	
		$this->response_success(
			array(
				'message' => __( 'Attribute updated.', 'easycommerce' ),
			)
		);
	}
}

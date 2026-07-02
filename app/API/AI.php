<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Services\AI as AI_Service;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;
use EasyCommerce\Helpers\Utility;

class AI extends API {

	public $ai_service;

	public function __construct() {
		$this->ai_service = new AI_Service;
	}

	/**
	 * Stop the request with a 403 if the given Generative AI feature is disabled.
	 *
	 * response_error() sends the JSON response and exits, so when the feature is
	 * off this never returns and the calling endpoint short-circuits.
	 *
	 * @param string $feature Feature key (see easycommerce_is_ai_feature_enabled()).
	 * @param string $label   Human-readable feature name for the error message.
	 * @return void
	 */
	private function require_feature( $feature, $label ) {
		if ( easycommerce_is_ai_feature_enabled( $feature ) ) {
			return;
		}

		$this->response_error(
			array(
				/* translators: %s: AI feature name. */
				'message' => sprintf( __( '%s is disabled. Enable it from Settings → AI → Generative AI.', 'easycommerce' ), $label ),
			),
			403
		);
	}

	/**
	 * Write product description or summary
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function write_copy( $request ) {
		$this->require_feature( 'text_generator', __( 'AI Text Generator', 'easycommerce' ) );

		$product 		= $request->get_param( 'product' );
		$prompt  		= $request->get_param( 'prompt' );
		$length  		= $request->get_param( 'length' ) === 'long' ? 'long' : 'short';
		
		$response 		= $this->ai_service->write( $product, $prompt, $length );

		if ( $response->success == true && ! empty( $response->data->message ) ) {
			$data = array(
				'message' => $response->data->message
			);

			/**
			 * Filters the AI write response data.
			 *
			 * @since 1.9
			 * @param array $data The response data.
			 * @param string $product The product data.
			 * @param string $prompt The prompt used.
			 * @param string $length The length.
			 * @param WP_REST_Request $request The request object.
			 */
			$data = apply_filters( 'easycommerce_ai_write_response', $data, $product, $prompt, $length, $request );

			/**
			 * Logs the AI write copy event.
			 */
			do_action( 'easycommerce_log', array( 'object' => 'ai', 'action' => 'write', 'object_id' => null, 'meta' => $request->get_params(), 'note' => 'AI content writer' ) );

			$this->response_success( $data, 201 );
		} else {
			$response = (object) $response;
			
			$this->response_error(
				array(
					'message'   => $response->data->message ?? $response->message ?? __( 'AI service error', 'easycommerce' )
				),
				400
			);
		}
	}

	/**
	 * Generate product attributes
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function generate_attributes( $request ) {
		$this->require_feature( 'attribute_generator', __( 'AI Attribute Generator', 'easycommerce' ) );

		$product 			= $request->get_param( 'product' );
		$num_attributes 	= $request->get_param( 'num_attributes' );
		$num_values			= $request->get_param( 'num_values' );

		$response 			= $this->ai_service->generate_attributes( $product, $num_attributes, $num_values );


		if ( $response->success == true && ! empty( $response->data->message ) ) {

			$response->data->message = json_decode( $response->data->message );

			$formatted_attributes    = [];
			$attributes              = maybe_unserialize( $response->data->message );
			$attribute_model         = new Attribute();
			$attribute_values_model  = new Attribute_Value();
			
			foreach ( $attributes as $attribute => $values ) {
				$attribute_id = $attribute_model->add( $attribute );
				
				if ( $attribute_id ) {
					$attribute_value_ids = [];
					
					foreach ( $values as $value ) {
						$value_id = $attribute_values_model->add( $attribute_id, $value );
						
						if ( $value_id ) {
							$attribute_value_ids[] = $value_id;
						}
					}
					
					$formatted_attributes[] = array(
						'attribute_id'   => $attribute_id,
						'attribute_name' => $attribute,
						'value_ids'      => $attribute_value_ids
					);
				}
			}
		
			$data = array(
				'message' 		=> $response->data->message,
				'attributes' 	=> $formatted_attributes
			);
			
			/**
			 * Filters the AI write response data.
			 *
			 * @since 1.9
			 * @param array $data The response data.
			 * @param string $product The product data.
			 * @param string $prompt The prompt used.
			 * @param string $length The length.
			 * @param WP_REST_Request $request The request object.
			 */
			$data = apply_filters( 'easycommerce_ai_write_response', $data, $product, $prompt, $length, $request );

			/**
			 * Logs the AI generate attributes event.
			 */
			do_action( 'easycommerce_log', array( 'object' => 'ai', 'action' => 'get_attr', 'object_id' => null, 'meta' => $request->get_params(), 'note' => 'AI attribute generator' ) );

			$this->response_success( $data, 201 );
		} else {
			$response = (object) $response;
			
			$this->response_error(
				array(
					'message'   => $response->data->message ?? $response->message ?? __( 'AI service error', 'easycommerce' )
				),
				400
			);
		}
	}

	/**
	 * Design page template using blocks
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function design_template( $request ) {
		$this->require_feature( 'template_generator', __( 'AI Template Generator', 'easycommerce' ) );

		$prompt 	= $request->get_param( 'prompt' );
		$product 	= $request->get_param( 'product' );

		if ( is_integer( $product_id = $product ) ) {
			$product_obj = new Product( $product_id );
			$product 	 = $product_obj->get_title();
		}

		$response 		= $this->ai_service->design( $prompt, $product );

		if ( $response->success == true && ! empty( $response->data->message ) ) {
			$data = array(
				'message' => $response->data->message
			);

			/**
			 * Filters the AI design response data.
			 *
			 * @since 1.9
			 * @param array $data The response data.
			 * @param string $prompt The prompt used.
			 * @param string $product The product data.
			 * @param WP_REST_Request $request The request object.
			 */
			$data = apply_filters( 'easycommerce_ai_design_response', $data, $prompt, $product, $request );

			/**
			 * Logs the AI design template event.
			 */
			do_action( 'easycommerce_log', array( 'object' => 'ai', 'action' => 'build', 'object_id' => null, 'meta' => $request->get_params(), 'note' => 'AI product builder' ) );

			$this->response_success( $data, 201 );
		} else {
			$response = (object) $response;
			
			$this->response_error(
				array(
					'message'   => $response->data->message ?? $response->message ?? __( 'AI service error', 'easycommerce' )
				),
				400
			);
		}
	}

	/**
	 * Draw an image
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function draw_image( $request ) {
		$this->require_feature( 'image_generator', __( 'AI Image Generator', 'easycommerce' ) );

		$prompt    		= $request->get_param( 'prompt' );
		$size     		= $request->get_param( 'size' );
		$quality    	= $request->get_param( 'quality' );
		$product   		= $request->get_param( 'product' );

		$response 		= $this->ai_service->draw( $prompt, $size, $quality, $product );

		if ( $response->success == true && ! empty( $response->data->url ) ) {
			$data = array(
				'message' => __( 'Image generated', 'easycommerce' ),
				'url'	  => $response->data->url
			);

			/**
			 * Filters the AI draw response data.
			 *
			 * @since 1.9
			 * @param array $data The response data.
			 * @param string $prompt The prompt used.
			 * @param string $ratio The ratio.
			 * @param string $format The format.
			 * @param string $product The product data.
			 * @param WP_REST_Request $request The request object.
			 */
			$data = apply_filters( 'easycommerce_ai_draw_response', $data, $prompt, $size, $quality, $product, $request );

			/**
			 * Logs the AI draw image event.
			 */
			do_action( 'easycommerce_log', array( 'object' => 'ai', 'action' => 'draw', 'object_id' => null, 'meta' => $request->get_params(), 'note' => 'AI image generator' ) );

			$this->response_success( $data, 201 );
		} else {
			$response = (object) $response;
			
			$this->response_error(
				array(
					'message'   => $response->data->message ?? $response->message ?? __( 'AI service error', 'easycommerce' )
				),
				400
			);
		}
	}

	/**
	 * Edit an image
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function edit_image( $request ) {
		$this->require_feature( 'image_generator', __( 'AI Image Generator', 'easycommerce' ) );

		$image   		= $request->get_param( 'image' );
		$prompt    		= $request->get_param( 'prompt' );
		$size     		= $request->get_param( 'size' );
		$quality    	= $request->get_param( 'quality' );

		if( is_numeric( $image ) ) {
			$image = wp_get_attachment_url( $image );
		}

		$response = $this->ai_service->edit_image( $image, $prompt, $size, $quality );

		if ( $response->success == true && ! empty( $response->data ) ) {

			$data = array(
				'message'		=> __( 'Image edited', 'easycommerce' ),
				'image_data'	=> $response->data->b64,
			);

			/**
			 * Filters the AI draw response data.
			 *
			 * @since 1.9
			 * @param array $data The response data.
			 * @param string $prompt The prompt used.
			 * @param string $ratio The ratio.
			 * @param string $format The format.
			 * @param string $product The product data.
			 * @param WP_REST_Request $request The request object.
			 */
			$data = apply_filters( 'easycommerce_ai_draw_response', $data, $image, $prompt, $size, $quality, $request );

			/**
			 * Logs the AI edit image event.
			 */
			do_action( 'easycommerce_log', array( 'object' => 'ai', 'action' => 'enhance', 'object_id' => null, 'meta' => $request->get_params(), 'note' => 'AI image enhancer' ) );

			$this->response_success( $data, 201 );
		} else {
			$response = (object) $response;
			
			$this->response_error(
				array(
					'message'   => $response->data->message ?? $response->message ?? __( 'AI service error', 'easycommerce' )
				),
				400
			);
		}
	}
}
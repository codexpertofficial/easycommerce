<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

use EasyCommerce\Abstracts\API;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;
use EasyCommerce\Traits\Queue;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Controllers\Common\Process;

class Importer extends API {

	use Cleaner;
	use Queue;

	public function upload_csv( $request ) {
		$files = $request->get_file_params();
		if ( empty( $files['csv_file'] ) ) {
			return $this->response_error( 'No file uploaded', 400 );
		}

		$file = $files['csv_file'];
		$file_path = $file['tmp_name'];

		if ( empty( $file_path ) || ! is_uploaded_file( $file_path ) ) {
			return $this->response_error( 'Invalid file', 400 );
		}

		// Process CSV
		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			$item = 0;
			$rows = array();
			while ( ( $row = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
				if ( 0 === $item ) {
					$sanitized_headers = $this->sanitize( $row, 'array' );
					update_option( 'easycommerce_importer_headers', $sanitized_headers );
					++$item;
					continue;
				}
				$sanitized_row = $this->sanitize( $row, 'array' );
				array_push( $rows, $sanitized_row );
			}
			fclose( $handle );

			update_option( 'easycommerce_importer_rows', $rows );
		} else {
			return $this->response_error( 'Failed to read CSV file', 400 );
		}

		$headers = get_option( 'easycommerce_importer_headers', array() );

		$data = array(
			'headers' => $headers,
			'row_count' => count( $rows ),
			'message' => 'CSV uploaded successfully'
		);

		/**
		 * Filters the CSV upload response data.
		 *
		 * @since 1.9
		 * @param array $data The response data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_upload_csv_response', $data, $request );

		return $this->response_success( $data );
	}

	public function map_columns( $request ) {
		$mapping = $request->get_param( 'mapping' );
		if ( ! is_array( $mapping ) ) {
			return $this->response_error( 'Invalid mapping data', 400 );
		}

		$sanitized_mapping = $this->sanitize( $mapping, 'array' );
		update_option( 'easycommerce_importer_mapping', $sanitized_mapping );

		$data = array(
			'message' => 'Column mapping saved successfully'
		);

		/**
		 * Filters the column mapping response data.
		 *
		 * @since 1.9
		 * @param array $data The response data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_map_columns_response', $data, $request );

		return $this->response_success( $data );
	}

	public function import_products( $request ) {
		$rows 	= get_option( 'easycommerce_importer_rows', array() );
		$errors = array();

		/**
		 * Fires before importing products.
		 *
		 * @since 1.9
		 * @param array $rows The rows to import.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_import_products', $rows, $request );

		$import_id = $request->get_param( 'import_id' );
		$offset    = (int) $request->get_param( 'offset' );
		if ( ! $import_id ) {
			$import_id = 'import_' . wp_generate_uuid4();

			$all_imports = get_option( 'easycommerce_import_statuses', array() );
			$all_imports[ $import_id ] = array(
				'total'      => count( $rows ),
				'processed'  => 0,
				'imported'   => 0,
				'errors'     => array(),
				'status'     => 'running',
				'started_at' => current_time( 'mysql' ),
			);
			update_option( 'easycommerce_import_statuses', $all_imports );

			$this->schedule( 'easycommerce_process_import_batch', array( $import_id, 0 ) );

			/**
			 * Fires after importing products.
			 *
			 * @since 1.9
			 * @param array $errors The errors.
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'easycommerce_after_import_products', $errors, $request );

			$data = array(
				'import_id' => $import_id,
				'total'     => count( $rows ),
				'message'   => ( __( 'Import started in background via WP-Cron', 'easycommerce' ) ),
				'status'    => 'running',
			);

			/**
			 * Filters the import products response data.
			 *
			 * @since 1.9
			 * @param array $data The response data.
			 * @param WP_REST_Request $request The request object.
			 */

			$data = apply_filters( 'easycommerce_import_products_response', $data, $request );

			return $this->response_success( $data );
		}

		$all_imports = get_option( 'easycommerce_import_statuses', array() );

		if ( ! isset( $all_imports[ $import_id ] ) ) {
			return $this->response_error( 'Import session not found', 404 );
		}

		$status = $all_imports[ $import_id ];

		// Add progress percentage
		$progress = $status['total'] > 0 ? round( ($status['processed'] / $status['total']) * 100, 1 ) : 0;
		$status['progress'] = $progress;

		return $this->response_success( $status );
	}

	public function import_sample_products( $request ) {

		$this->schedule( 'easycommerce_sample_product_import' );

		return $this->response_success( array(
			'message'  => __( 'Your demo products are being generated', 'easycommerce' ),
		), );
	}

	public function create_product( $row ) {
		$product_headers = get_option( 'easycommerce_importer_headers', array() );
		$product_mapping = get_option( 'easycommerce_importer_mapping', array() );

		foreach ( $product_headers as $key => $value ) {
			$header = str_replace( ' ', '_', $value );
			$$header = isset( $row[ $key ] ) ? $this->sanitize( $row[ $key ] ) : '';
		}

		// Process product-level attributes
		$attributes = $this->process_product_attributes( $attribute_names ?? '', $attribute_values ?? '' );

		if ( ! empty( $attribute_names ) && empty( $attribute_values ) && ! empty( $attribute_values_name ) && ! empty( $attribute_values_value ) ) {
			$attributes = $this->extract_attributes_from_variations( 
				$attribute_names ?? '', 
				$attribute_values_name ?? '', 
				$attribute_values_value ?? '' 
			);
		}

		$variation_data = array(
			'names' => $this->sanitize( explode( ',', $variation_names ?? '' ), 'array' ),
			'types' => $this->sanitize( explode( ',', $variation_types ?? '' ), 'array' ),
			'statuses' => $this->sanitize( explode( ',', $variation_status ?? '' ), 'array' ),
			'regular_prices' => $this->sanitize( explode( ',', $regular_prices ?? '' ), 'array' ),
			'sale_prices' => $this->sanitize( explode( ',', $sale_prices ?? '' ), 'array' ),
			'skus' => $this->sanitize( explode( ',', $skus ?? '' ), 'array' ),
			'stock_quantities' => $this->sanitize( explode( ',', $stock_quantities ?? '' ), 'array' ),
			'stock_limits' => $this->sanitize( explode( ',', $stock_limits ?? '' ), 'array' ),
			'attributes_keys' => $variation_attribute_names ?? '', // Keep as string
			'attributes_values_name' => $this->sanitize( explode( ',', $variation_attribute_values_name ?? '' ), 'array' ),
			'attributes_values_value' => $this->sanitize( explode( ',', $variation_attribute_values_value ?? '' ), 'array' ),
			'is_managed_stocks' => $this->sanitize( explode( ',', $managed_stocks ?? '' ), 'array' ),
			'tax_classes' => $this->sanitize( explode( ',', $tax_classes ?? '' ), 'array' ),
			'thumbnail_ids' => $this->get_img_ids( $thumbnail_urls ?? '' ),
			'thumbnail_urls' => $this->sanitize( explode( ',', $thumbnail_urls ?? '' ), 'array' ),
			'width_values' => $this->sanitize( explode( ',', $width_values ?? '' ), 'array' ),
			'width_units' => $this->sanitize( explode( ',', $width_units ?? '' ), 'array' ),
			'height_values' => $this->sanitize( explode( ',', $height_values ?? '' ), 'array' ),
			'height_units' => $this->sanitize( explode( ',', $height_units ?? '' ), 'array' ),
			'weight_values' => $this->sanitize( explode( ',', $weight_values ?? '' ), 'array' ),
			'weight_units' => $this->sanitize( explode( ',', $weight_units ?? '' ), 'array' ),
			'length_values' => $this->sanitize( explode( ',', $length_values ?? '' ), 'array' ),
			'length_units' => $this->sanitize( explode( ',', $length_units ?? '' ), 'array' ),
		);

		$has_explicit_variations = ! empty( $variation_data['regular_prices'] ) && count( array_filter( $variation_data['regular_prices'] ) ) > 0;

		// Build variations BEFORE converting attributes to minimal format
		if ( $has_explicit_variations ) {
			$variations = array();
			foreach ( $variation_data['regular_prices'] as $key => $price ) {
				$variations[] = $this->build_variation( $variation_data, $key, $attributes );
			}
		} else {
			$variations = $this->generate_variations_from_attributes( $variation_data, $attributes );
		}

		$meta = array();
		$meta['gallery'] = array();
		$gallery_ids = $this->get_img_ids( $meta_gallery_urls ?? '' );
		$gallery_urls = array_map( 'sanitize_text_field', explode( ',', $meta_gallery_urls ?? '' ) );
		$gallery_titles = array_map( 'sanitize_text_field', explode( ',', $meta_gallery_titles ?? '' ) );

		if ( ! empty( $gallery_ids ) && is_array( $gallery_ids ) ) {
			$meta['gallery'] = array();
			foreach ( $gallery_ids as $key => $gallery_id ) {
				$url = isset( $gallery_urls[ $key ] ) ? $this->sanitize( $gallery_urls[ $key ] ) : '';
				$title = isset( $gallery_titles[ $key ] ) ? $this->sanitize( $gallery_titles[ $key ] ) : '';
				if ( ! $gallery_id || $url === '' || $title === '' ) {
					continue;
				}
				$meta['gallery'][] = array(
					'id' => $gallery_id,
					'url' => $url,
					'title' => $title,
				);
			}
			$meta['gallery'] = array_values( $meta['gallery'] );
		}

		$meta['template'] = $this->sanitize( $meta_templates ?? '' );
		$meta['show_review'] = (bool) ( $show_reviews ?? false );
		$meta['review_text_mandatory'] = (bool) ( $review_text_mandatory ?? false );
		$meta['hide_from_shop'] = (bool) ( $hide_from_shop ?? false );
		$meta['noindex'] = (bool) ( $noindex ?? false );
		$meta['publish_date'] = gmdate( 'Y-m-d' );

		// Convert attributes to minimal format for product storage
		$minimal_attributes = array();
		foreach ( $attributes as $attr ) {
			if ( empty( $attr['id'] ) || empty( $attr['values'] ) ) {
				continue;
			}
			$values_minimal = array();
			foreach ( $attr['values'] as $val ) {
				if ( ! empty( $val['id'] ) ) {
					$values_minimal[] = array(
						'id'           => $val['id'],
						'attribute_id' => (string) $attr['id']
					);
				}
			}
			if ( ! empty( $values_minimal ) ) {
				$minimal_attributes[] = array(
					'id'     => $attr['id'],
					'values' => $values_minimal
				);
			}
		}

		$args = array();
		$args['status'] = $this->sanitize( $status ?? 'publish' );
		$args['title'] = $this->sanitize( $title ?? '' );
		$args['tags'] = $this->sanitize( explode( ',', $tags ?? '' ), 'array' );
		$args['summary'] = $this->sanitize( $summary ?? '' );
		$args['description'] = $this->sanitize( $description ?? '' );
		$args['brands'] = $this->sanitize( explode( ',', $brands ?? '' ), 'array' );
		$args['slug'] = $this->sanitize( $slug ?? '' );
		$args['thumbnail'] = $this->get_img_ids( $thumbnail_url ?? '' );
		$args['categories'] = $this->sanitize( explode( ',', $categories ?? '' ), 'array' );
		$args['attributes'] = $minimal_attributes;
		$args['variations'] = $variations;
		$args['meta'] = $meta;

		$product = new Product();
		$id = $product->update( $args );

		/**
		 * Fires after a product is imported.
		 *
		 * @since 1.9
		 * @param int $id The product ID.
		 * @param array $args The product args.
		 */
		do_action( 'easycommerce_product_imported', $id, $args );

		return $id;
	}

	/**
	 * Extract unique attribute values from variation data when product-level values are empty
	 * 
	 * @param string $attribute_names Product-level attribute names (e.g., "Color, Size")
	 * @param string $variation_attr_names Variation attribute names (e.g., "Color,Color,Size,Size")
	 * @param string $variation_attr_values Variation attribute values (e.g., "Blue,Black,M,L")
	 * @return array Structured attributes array with IDs
	 */
	private function extract_attributes_from_variations( $attribute_names, $variation_attr_names, $variation_attr_values ) {
		$attribute_model        = new Attribute();
		$attribute_values_model = new Attribute_Value();
		$attr_names  			= array_map( 'trim', explode( ',', $attribute_names ) );
		$value_names 			= array_map( 'trim', explode( ',', $variation_attr_names ) );
		$value_vals  			= array_map( 'trim', explode( ',', $variation_attr_values ) );
		$grouped_values         = array();

		foreach ( $attr_names as $index => $attr_name ) {
			if (
				empty( $attr_name ) ||
				empty( $value_names[ $index ] ) ||
				empty( $value_vals[ $index ] )
			) {
				continue;
			}

			$clean_attr_name = $this->sanitize( $attr_name );

			$names  = array_map( 'trim', explode( '|', $value_names[ $index ] ) );
			$values = array_map( 'trim', explode( '|', $value_vals[ $index ] ) );

			foreach ( $names as $i => $name ) {
				$value = $values[ $i ] ?? '';

				if ( empty( $name ) || empty( $value ) ) {
					continue;
				}

				$grouped_values[ $clean_attr_name ][] = array(
					'name'  => $this->sanitize( $name ),
					'value' => $this->sanitize( $value ),
				);
			}
		}
		// Now create attributes and values
		$attributes = array();
		foreach ( $attr_names as $attr_name ) {
			if ( empty( $attr_name ) ) {
				continue;
			}
			
			$clean_attr_name = $this->sanitize( $attr_name );
			$attr_slug       = strtolower( $this->sanitize( $clean_attr_name ) );
			$attr_type 		 = strtolower( $clean_attr_name ) === 'color' ? 'Color' : 'Text';
			$existing_attr 	 = $attribute_model->get_by_slug( $attr_slug );

			if ( $existing_attr ) {
				$attribute_id = $existing_attr->id;
				if ( $existing_attr->type !== $attr_type ) {
					$attribute_model->update( $attribute_id, array( 'type' => $attr_type ) );
				}
			} else {
				$attribute_id = $attribute_model->add( $clean_attr_name, $attr_type, $attr_slug );
			}
			
			if ( ! $attribute_id ) {
				continue;
			}
			
			// Get unique values for this attribute from grouped data
			$attribute_values_array = $grouped_values[ $clean_attr_name ] ?? array();
			$processed_values       = array();
			
			foreach ( $attribute_values_array as $value_data ) {
				$name        = $value_data['name'];
				$clean_value = $this->sanitize( $value_data['value'] );

				if ( empty( $name ) ) {
					continue;
				}
				
				$value_slug = $this->sanitize( $name );
				
				$existing_value = $attribute_values_model->get_by_slug( $attribute_id, $value_slug );
				if ( $existing_value ) {
					$value_id = $existing_value->id;
				} else {
					$value_id = $attribute_values_model->add( $attribute_id, $name, $clean_value, $value_slug );
				}
				
				if ( $value_id ) {
					$processed_values[] = array(
						'id' 	=> (string) $value_id,
						'slug' 	=> $value_slug,
						'name' 	=> $clean_value,
					);
				}
			}
			
			$attributes[] = array(
				'id' 		=> (string) $attribute_id,
				'slug' 		=> $attr_slug,
				'name' 		=> $clean_attr_name,
				'values' 	=> $processed_values,
			);
		}
		
		return $attributes;
	}

	/**
	 * Process product attributes and create them in database if they don't exist
	 * 
	 * @param string $attribute_names Comma-separated attribute names
	 * @param string $attribute_values Pipe-separated groups of comma-separated values
	 * @return array Structured attributes array with IDs
	 */
	private function process_product_attributes( $attribute_names, $attribute_values ) {
		$attribute_model        = new Attribute();
		$attribute_values_model = new Attribute_Value();
		
		$attributes 			= array();
		$keys 					= array_map( 'trim', explode( ',', $attribute_names ) );
		$value_groups 			= explode( '|', $attribute_values );

		foreach ( $keys as $key => $attribute_name ) {
			if ( empty( $attribute_name ) ) {
				continue;
			}

			$clean_attr_name 	= $this->sanitize( $attribute_name );
			$attr_slug 			= strtolower( $this->sanitize( $clean_attr_name ) );
			$attr_type 			=  $clean_attr_name === 'Color' ? 'Color' : 'Text';
			$existing_attr 		= $attribute_model->get_by_slug( $attr_slug );
			if ( $existing_attr ) {
				$attribute_id = $existing_attr->id;
				// Update type if different
				if ( $existing_attr->type !== $attr_type ) {
					$attribute_model->update( $attribute_id, array( 'type' => $attr_type ) );
				}
			} else {
				$attribute_id = $attribute_model->add( $clean_attr_name, $attr_type, $attr_slug );
			}

			if ( ! $attribute_id ) {
				continue;
			}

			// Process attribute values
			$values_string 			= isset( $value_groups[ $key ] ) ? $value_groups[ $key ] : '';
			$attribute_values_array = array_map( 'trim', explode( ',', $values_string ) );
			$attribute_values_array = array_filter( $attribute_values_array );

			$processed_values = array();

			if ( ! empty( $attribute_values_array ) ) {
				foreach ( $attribute_values_array as $value ) {
					$clean_value = $this->sanitize( $value );
					if ( empty( $clean_value ) ) {
						continue;
					}

					$value_slug = $this->sanitize( $clean_value );

					// Get or create attribute value
					$existing_value = $attribute_values_model->get_by_slug( $attribute_id, $value_slug );
					if ( $existing_value ) {
						$value_id = $existing_value->id;
					} else {
						$value_id = $attribute_values_model->add( $attribute_id, $clean_value );
					}

					if ( $value_id ) {
						$processed_values[] = array(
							'id' => $value_id,
							'slug' => $value_slug,
							'name' => $clean_value,
						);
					}
				}
			}

			$attributes[] = array(
				'id' 	 => $attribute_id,
				'slug' 	 => $attr_slug,
				'name' 	 => $clean_attr_name,
				'values' => $processed_values,
			);
		}

		return $attributes;
	}

	/**
	 * Generate all possible variations from product attributes
	 * 
	 * @param array $attributes Product attributes array
	 * @return array Generated variations
	 */
	private function generate_variations_from_attributes( $variation_data, $attributes ) {
		if ( empty( $attributes ) ) {
			return array();
		}

		// Generate all combinations of attribute values
		$combinations 	= $this->generate_attribute_combinations( $attributes );
		$variations 	= array();
		$price_id 		= 1;

		foreach ( $combinations as $combination ) {
			$sku_parts 	= array();
			$name_parts = array();
			
			foreach ( $combination as $attr ) {
				$name_parts[] 	= $attr['value_name'];
				$sku_parts[] 	= strtoupper( substr( $attr['value_slug'], 0, 3 ) );
			}

			$sku 	= $variation_data['skus'] ? implode( '-', $sku_parts ) : '';
			$name 	= implode( ' - ', $name_parts );

			// Build variation attributes structure
			$variation_attributes = array();
			foreach ( $combination as $attr ) {
				$variation_attributes[] = array(
					'attribute_id'    => (string) $attr['attribute_id'],
        			'attribute_slug'  => $attr['attribute_slug'],
					'name' 	 		  => $attr['attribute_name'],
					'value_id'        => (string) $attr['value_id'],
        			'value_slug'      => $attr['value_slug'],
					'values' 		  => array(
						array(
							'id'   => $attr['value_id'],
							'slug' => $attr['value_slug'],
							'name' => $attr['value_name'],
						)
					)
				);
			}

			$variations[] = array(
				'name' 				=> $name,
				'type' 				=> 'physical',
				'status' 			=> 'in_stock',
				'regular_price' 	=> 0,
				'sale_price' 		=> '',
				'sku' 				=> $sku,
				'stock_quantity' 	=> '',
				'stock_limit' 		=> '',
				'attributes' 		=> $variation_attributes,
				'meta' 				=> array(
					'is_managed_stock' => false,
					'tax_class' => '',
					'thumbnail' => array(
						'id'  => 0,
						'url' => '',
					),
					'width' 	=> array( 'value' => '', 'unit' => 'cm' ),
					'height' 	=> array( 'value' => '', 'unit' => 'cm' ),
					'weight' 	=> array( 'value' => '', 'unit' => 'kg' ),
					'length' 	=> array( 'value' => '', 'unit' => 'cm' ),
				),
				'downloads' 		=> array(),
				'price_id' 			=> $price_id++,
			);
		}

		return $variations;
	}

	private function generate_attribute_combinations( $attributes ) {
		$combinations = array( array() );

		foreach ( $attributes as $attribute ) {
			$temp_combinations = array();
			
			foreach ( $combinations as $combination ) {
				foreach ( $attribute['values'] as $value ) {
					$new_combination = $combination;
					$new_combination[] = array(
						'attribute_id' 		=> $attribute['id'],
						'attribute_slug' 	=> $attribute['slug'],
						'attribute_name' 	=> $attribute['name'],
						'value_id' 			=> $value['id'],
						'value_slug' 		=> $value['slug'],
						'value_name' 		=> $value['name'],
					);
					$temp_combinations[] = $new_combination;
				}
			}
			
			$combinations = $temp_combinations;
		}

		return $combinations;
	}

	private function get_img_ids( $urls ) {
		$thumb_urls = $this->sanitize( explode( ',', $urls ), 'array' );
		$thumb_ids = array();
		foreach ( $thumb_urls as $url ) {
			$url = trim( $url );
			if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
				continue;
			}
			$media_id = $this->sideload_remote_image( $url );
			if ( is_wp_error( $media_id ) ) {
				error_log( sprintf(
					'EasyCommerce image import failed — url=%s code=%s message=%s',
					$url,
					$media_id->get_error_code(),
					$media_id->get_error_message()
				) );
				continue;
			}
			array_push( $thumb_ids, $media_id );
		}

		if ( count( $thumb_ids ) === 1 ) {
			return $thumb_ids[0];
		}

		return $thumb_ids;
	}

	/**
	 * Download a remote image and attach it to the media library.
	 *
	 * Uses an unsafe wp_remote_get() instead of media_sideload_image()/download_url()
	 * on purpose: those route through wp_safe_remote_get(), which calls
	 * wp_http_validate_url(). On WP 7.0 that helper does an IPv4-only gethostbyname()
	 * and returns false when it can't resolve an A record — so CDN hosts that only
	 * resolve over IPv6 (e.g. Cloudflare-fronted cdn.easycommerce.dev) get rejected
	 * pre-flight with "A valid URL was not provided." on live/IPv6 servers, while
	 * working on IPv4 localhost. The URLs handled here are plugin-controlled sample
	 * assets and operator-supplied CSV image URLs, not arbitrary user input, so
	 * bypassing the safe-URL SSRF guard is acceptable.
	 *
	 * @param string $url Remote image URL.
	 * @return int|\WP_Error Attachment ID on success, WP_Error on failure.
	 */
	private function sideload_remote_image( $url ) {
		$response = wp_remote_get( $url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return new \WP_Error( 'http_bad_status', sprintf( 'HTTP %d for %s', $code, $url ) );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( '' === $body ) {
			return new \WP_Error( 'http_empty_body', 'Empty response body for ' . $url );
		}

		$filename = sanitize_file_name( wp_basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
		if ( '' === $filename ) {
			$filename = 'easycommerce-image-' . md5( $url ) . '.jpg';
		}

		$tmp = wp_tempnam( $filename );
		if ( ! $tmp ) {
			return new \WP_Error( 'http_no_file', 'Could not create temporary file.' );
		}

		if ( false === file_put_contents( $tmp, $body ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@unlink( $tmp );
			return new \WP_Error( 'write_failed', 'Could not write temporary file for ' . $url );
		}

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		);

		$media_id = media_handle_sideload( $file_array, 0, null, array( 'test_form' => false ) );
		if ( is_wp_error( $media_id ) ) {
			@unlink( $tmp );
			return $media_id;
		}

		return (int) $media_id;
	}

	/**
	 * Build explicit variation from CSV data with proper attribute structure
	 */
	private function build_variation( $variation_data, $key, $product_attributes ) {

		$thumb_id   			= $variation_data['thumbnail_ids'][ $key ] ?? 0;
		$thumb_url  			= $variation_data['thumbnail_urls'][ $key ] ?? '';
		$variation_attributes 	= array();
		$variation_name 		= $variation_data['names'][ $key ] ?? '';
		$variation_value_parts 	= array_map( 'trim', explode( '/', $variation_name ) );

		// Get attribute names - this is a single string like "Color, Size"
		$attr_names_string = $variation_data['attributes_keys'];
		$attr_names 	   = array_map( 'trim', explode( ',', $attr_names_string ) );

		// Match each attribute name with its value from the variation name
		foreach ( $attr_names as $idx => $attr_name ) {
			if ( empty( $attr_name ) || ! isset( $variation_value_parts[ $idx ] ) ) {
				continue;
			}

			$clean_attr_name  = $this->sanitize( $attr_name );
			$clean_attr_value = $this->sanitize( $variation_value_parts[ $idx ] );
			$attr_slug        = $this->sanitize( $clean_attr_name );
			$value_slug       = $this->sanitize( $clean_attr_value );

			$attribute_id = 0;
			$value_id     = 0;

			// Find matching attribute and value from product attributes
			foreach ( $product_attributes as $pa ) {
				$pa_slug = isset( $pa['slug'] ) ? $pa['slug'] : $this->sanitize( $pa['name'] );
				
				if ( $pa_slug === $attr_slug || $this->sanitize( $pa['name'] ) === $attr_slug ) {
					$attribute_id = $pa['id'];
					
					// Find matching value
					foreach ( $pa['values'] as $val ) {
						$val_slug = isset( $val['slug'] ) ? $val['slug'] : $this->sanitize( $val['name'] );
						
						if ( $val_slug === $value_slug || $this->sanitize( $val['name'] ) === $value_slug ) {
							$value_id = $val['id'];
							break;
						}
					}
					break;
				}
			}

			if ( $attribute_id && $value_id ) {
				$variation_attributes[] = array(
					'attribute_id'    => (string) $attribute_id,
        			'attribute_slug'  => $attr_slug,
					'name' 	 		  => $clean_attr_name,
					'value_id'        => (string) $value_id,
        			'value_slug'      => $value_slug,
					'values' 		  => array(
						array(
							'id' 	=> (string) $value_id,
							'slug' 	=> $value_slug,
							'name' 	=> $clean_attr_value,
						)
					)
				);
			}
		}

		return array(
			"id"              => "",
			"name"            => $variation_data['names'][ $key ] ?? '',
			"sku"             => $variation_data['skus'][ $key ] ?? '',
			"type"            => $variation_data['types'][ $key ] ?? 'physical',
			"thumbnail"       => array(
				"id"  => $thumb_id,
				"url" => $this->sanitize( $thumb_url )
			),
			"status"          => $variation_data['statuses'][ $key ] ?? 'in_stock',
			"stock_quantity"  => $variation_data['stock_quantities'][ $key ] ?? null,
			"stock_limit"     => $variation_data['stock_limits'][ $key ] ?? null,
			"price_id"        => (string) ( $key + 1 ),
			"regular_price"   => $variation_data['regular_prices'][ $key ] ?? '0.00',
			"sale_price"      => $variation_data['sale_prices'][ $key ] ?? false,
			"price"           => $variation_data['sale_prices'][ $key ] ?? $variation_data['regular_prices'][ $key ] ?? '0.00',
			"attributes"      => $variation_attributes,
			"downloads"       => array( "downloads" => array() ),
			"meta"            => array(
				"null"            => "on",
				"length"          => array( "value" => $variation_data['length_values'][ $key ] ?? null, "unit" => $variation_data['length_units'][ $key ] ?? null ),
				"weight"          => array( "value" => $variation_data['weight_values'][ $key ] ?? null, "unit" => $variation_data['weight_units'][ $key ] ?? 'kg' ),
				"height"          => array( "value" => $variation_data['height_values'][ $key ] ?? null, "unit" => $variation_data['height_units'][ $key ] ?? null ),
				"width"           => array( "value" => $variation_data['width_values'][ $key ] ?? null,  "unit" => $variation_data['width_units'][ $key ] ?? null ),
				"thumbnail"       => array( "id" => $thumb_id, "url" => $this->sanitize( $thumb_url ) ),
				"tax_class"       => $variation_data['tax_classes'][ $key ] ?? '',
				"is_managed_stock" => ! empty( $variation_data['is_managed_stocks'][ $key ] ) ? "1" : ""
			)
		);
	}

	public function is_base64_data_url( string $value ): bool {
		return (bool) preg_match(
			'#^data:[a-z0-9]+/[a-z0-9.+-]+;base64,#i',
			$value
		);
	}

	public function sideload_image( $request ) {
		$url			= $request->get_param( 'url' );
		$post_id		= $request->get_param( 'post_id' );
		$description	= $request->get_param( 'description' );

		if ( $this->is_base64_data_url( $url ) ) {
			$imported = easycommerce_import_encoded_image( $url, $post_id, $description );
		} else {
			$imported = easycommerce_import_image( $url, $post_id, $description );
		}

		if( is_wp_error( $imported ) ) {
			$this->response_error(
				array(
					'message' => __( 'Failed to import image', 'easycommerce' )
				)
			);
		}

		$this->response_success(
			array(
				'message'	=> __( 'Image imported', 'easycommerce' ),
				'imported'	=> $imported
			)
		);
	}
}
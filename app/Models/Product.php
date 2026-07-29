<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;
use EasyCommerce\Abstracts\Model;

/**
 * Concrete Product Class
 */
class Product extends Model {

	use Cleaner;

	/**
	 * Sort keys backed by a post meta value.
	 *
	 * @var array
	 */
	protected static $meta_sorts = array(
		'top-rating'     => array(
			'key'        => 'average_rating',
			'order'      => 'DESC',
			'nulls_last' => true,
		),
		'lowest-rating'  => array(
			'key'        => 'average_rating',
			'order'      => 'ASC',
			'nulls_last' => true,
		),
		'best-selling'   => array(
			'key'        => 'total_sale',
			'order'      => 'DESC',
			'nulls_last' => false,
		),
		'lowest-selling' => array(
			'key'        => 'total_sale',
			'order'      => 'ASC',
			'nulls_last' => false,
		),
	);

	/**
	 * @var int Product ID
	 */
	protected $id;

	/**
	 * @var string Product title
	 */
	protected $title = '';

	/**
	 * @var string Product slug
	 */
	protected $slug = '';

	/**
	 * @var string Product description
	 */
	protected $description = '';

	/**
	 * @var string Product post_content
	 */
	protected $content = '';

	/**
	 * @var string Product status
	 */
	protected $status = '';

	/**
	 * @var timestamp Product created_at
	 */
	protected $created_at = '';

	/**
	 * @var timestamp Product updated_at
	 */
	protected $updated_at = '';

	/**
	 * @var string Product thumbnail
	 */
	protected $thumbnail = '';

	/**
	 * @var bool Product existence flag
	 */
	protected $exists = false;

	/**
	 * @var Product_Meta Product_Meta instance
	 */
	protected $meta;

	protected $table = 'products';

	public function __construct( $id = null ) {
		parent::__construct();
		$this->meta = new Product_Meta();

		if ( $id && ! is_null( $post = get_post( $id ) ) && $post->post_type === easycommerce_product_post_type() ) {
			$this->id         = $id;
			$this->title      = $post->post_title;
			$this->slug       = $post->post_name;
			$this->content    = $post->post_content;
			$this->created_at = $post->post_date;
			$this->updated_at = $post->post_modified;
			$this->status     = $post->post_status;
			$this->thumbnail  = array(
				'id'  => get_post_thumbnail_id( $id ),
				'url' => get_the_post_thumbnail_url( $id, 'easycommerce-gallery-image' ),
			);
			$this->exists     = true;
		}
	}

	/**
	 * Get product ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Check if the product exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Check if the product is sellable.
	 *
	 * @return bool
	 */
	public function is_sellable() {
		return $this->exists();
	}

	/**
	 * Check if the product is deletable.
	 *
	 * @return bool
	 */
	public function is_deletable() {
		return $this->exists();
	}

	/**
	 * Get product title.
	 *
	 * @return string
	 */
	public function get_title() {
		return html_entity_decode( $this->title );
	}

	/**
	 * Get product slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Set product title.
	 *
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Set product slug.
	 *
	 * @param string $slug
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Set product thumbnail.
	 *
	 * @param int $thumbnail The thumbnail ID
	 */
	public function set_thumbnail( $thumbnail ) {

		set_post_thumbnail( $this->id, $thumbnail ); // @todo improve

		$this->thumbnail = $thumbnail;
	}

	/**
	 * Get product description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->get_meta( 'description' );
	}

	/**
	 * Set product description.
	 *
	 * @param string $description
	 */
	public function set_description( $description ) {
		$this->update_meta( 'description', $description );
	}

	/**
	 * Get product summary.
	 *
	 * @return string
	 */
	public function get_summary() {
		return $this->get_meta( 'summary' );
	}

	/**
	 * Set product summary.
	 *
	 * @param string $summary
	 */
	public function set_summary( $summary ) {
		$this->update_meta( 'summary', $summary );
	}

	/**
	 * Get product status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set product status.
	 *
	 * @param string $status
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Get product thumbnail.
	 *
	 * @return string
	 */
	public function get_thumbnail( $size = null ) {
		if ( is_null( $size ) ) {
			return $this->thumbnail;
		}

		return array(
			'id'  => get_post_thumbnail_id( $this->get_id() ),
			'url' => get_the_post_thumbnail_url( $this->get_id(), $size ),
		);
	}

	/**
	 * Get product url.
	 *
	 * @return string
	 */
	public function get_url() {
		return get_permalink( $this->get_id() );
	}

	/**
	 * Get product gallery.
	 *
	 * @return string
	 */
	public function get_gallery() {
		$gallery = maybe_unserialize( $this->get_meta( 'gallery' ) );

		if ( ! is_array( $gallery ) ) {
			$gallery = array();
		}

		$variations = $this->get_variations();
		foreach ( $variations as $variation ) {
			$thumbnail = $variation->get_thumbnail();
			if ( is_array( $thumbnail ) && isset( $thumbnail['id'] ) ) {
				$thumbnail['variation_id'] = $variation->get_id();
				$gallery[]                 = $thumbnail;
			}
		}

		return array_map(
			function ( $item ) {
				return array(
					'parent_id' => isset( $item['variation_id'] ) ? $item['variation_id'] : $this->get_id(),
					'url'       => wp_get_attachment_image_url( $item['id'], 'easycommerce-gallery-image' ),
					'thumbnail' => wp_get_attachment_image_url( $item['id'], 'easycommerce-gallery-thumbnail' ),
				);
			},
			$gallery
		);
	}

	/**
	 * Get product attributes.
	 *
	 * @return string
	 */
	public function get_attributes() {
		return maybe_unserialize( $this->get_meta( 'attributes' ) );
	}

	/**
	 * Get product rating
	 *
	 * @return float
	 */
	public function get_rating() {
		return get_post_meta( $this->get_id(), 'average_rating', true );
	}

	/**
	 * Get product rating
	 *
	 * @return float
	 */
	public function get_rating_count() {
		return count( $this->get_reviews() );
	}

	/**
	 * Get product total sales
	 *
	 * @return float
	 */
	public function get_sales( $formatted = true ) {
		$order_items_model = new Order_Item();
		$order_items       = $order_items_model->get_by( $this->get_id(), 'product_id' );
		$sales             = array_sum( wp_list_pluck( $order_items, 'price' ) );

		if ( $formatted !== false ) {
			return easycommerce_price( $sales );
		}

		return $sales;
	}

	/**
	 * Get product prices.
	 *
	 * @param bool $formatted Whether to return formatted prices.
	 * @return array
	 */
	public function get_prices( $formatted = true ) {

		$prices = array_map(
			function ( $variation ) use ( $formatted ) {
				$attributes = array();
				foreach ( $variation->get_attributes() as $attr ) {
					$attribute_model = new Attribute();
					$attribute_value_model = new Attribute_Value();
					$attr_info = $attribute_model->get( $attr->attribute_id );
					$attr_value = $attribute_value_model->get( $attr->value_id );
					$actual_value = $attr_value ? $attr_value->value : '';
					if ($attr_info && $attr_info->type === 'Image' && is_numeric($actual_value)) {
						$actual_value = wp_get_attachment_image_url($actual_value, 'full');
					}
					$attributes[] = (object) array(
						'id'             => $attr->id,
						'variation_id'   => $attr->variation_id,
						'attribute_id'   => $attr->attribute_id,
						'attribute_slug' => $attr_info ? $attr_info->slug : '',
						'value_id'       => $attr->value_id,
						'value_slug'     => $attr_value ? $attr_value->slug : '',
						'type'           => $attr_info ? $attr_info->type : 'text',
						'value'          => $actual_value,
					);
				}

				return array(
					'id'             => $variation->get_id(),
					'name'           => $variation->get_name(),
					'sku'            => $variation->get_sku(),
					'type'           => $variation->get_type(),
					'thumbnail'      => $variation->get_thumbnail(),
					'status'         => $variation->get_status(),
					'stock_quantity' => $variation->get_stock(),
					'price_id'       => $variation->get_price_id(),
					'regular_price'  => $variation->get_regular_price(),
					'sale_price'     => $variation->get_sale_price(),
					'price'          => $variation->get_price(),
					'attributes'     => $attributes,
					'downloads'      => $variation->get_downloads(),
					'meta'           => $variation->get_meta(),
					'stock_limit'    => $variation->get_low_stock_limit(),
				);
			},
			$this->get_variations()
		);

		if ( true !== $formatted ) {
			return $prices;
		}

		return array_map(
			function ( $price ) {
				$price['price']      = easycommerce_price( $price['price'] );
				$price['sale_price'] = easycommerce_price( $price['sale_price'] );

				return $price;
			},
			$prices
		);
	}

	public function is_variable() {
		$prices = $this->get_prices( false );
		return count( $prices ) > 1;
	}

	public function get_price( $formatted = true, $type = 'regular_price' ) {

		if ( ! in_array( $type, array( 'regular_price', 'sale_price' ) ) ) {
			return '';
		}

		$prices = wp_list_pluck( $this->get_prices( false ), $type );

		if ( count( $prices ) < 1 ) {
			return '';
		} elseif ( count( $prices ) == 1 ) {
			return $formatted ? easycommerce_price( $prices[0] ) : $prices[0];
		} else {
			$prices = array_map(
				'floatval',
				array_filter(
					$prices,
					function ( $value ) {
						return is_numeric( $value );
					}
				)
			);

			if ( empty( $prices ) ) {
				return false;
			}

			/**
			 * If a product has 2 (or more) prices, one with them with a sale_price, adjust
			 *
			 * @see issue #951
			 * @link https://github.com/easycommercedev/easycommerce/issues/651
			 */
			if ( $type == 'sale_price' && count( $prices ) == 1 && count( $regular_prices = wp_list_pluck( $this->get_prices( false ), 'regular_price' ) ) > 1 ) {
				$prices = array_merge( $prices, $regular_prices );
			}

			$min = min( $prices );
			$max = max( $prices );

			if ( $formatted ) {
				$min = easycommerce_price( $min );
				$max = easycommerce_price( $max );
			}

			if ( $min == $max ) {
				return $min;
			}

			return sprintf( '%1$s-%2$s', $min, $max );
		}
	}

	public function get_sale_price( $formatted = true ) {
		return $this->get_price( $formatted, 'sale_price' );
	}

	/**
	 * Get product stock.
	 *
	 * @return integer|bool The stock of the product or false if not found
	 */
	public function get_stock() {
		$variations 	= $this->get_variations();
		$stock 			= 0;
		$has_numeric 	= false;

		foreach ( $variations as $variation ) {
			if ( $variation->get_type() === 'digital' ) {
				continue;
			}

			$quantity = $variation->stock_quantity ?? null;

			if ( is_numeric( $quantity ) ) {
				$stock 			+= (int) $quantity;
				$has_numeric 	= true;
			}
		}

		if ( $has_numeric ) {
			return $stock;
		}

		return null;
	}

	/**
	 * Get product created_at.
	 *
	 * @return integer
	 */
	public function get_created_at() {
		return $this->created_at;
	}

	/**
	 * Get product updated_at.
	 *
	 * @return integer
	 */
	public function get_updated_at() {
		return $this->updated_at;
	}

	/**
	 * Check if the product has variations.
	 *
	 * @return bool
	 */
	public function has_variations() {
		return ! empty( $this->get_variations() );
	}

	/**
	 * Get variations of the product.
	 *
	 * @return array
	 */
	public function get_variations() {
		$variation = new Product_Variation();

		return $variation->get_by( $this->id );
	}

	/**
	 * An unorganized list of all downloads from all variations of this product.
	 */
	public function get_downloads() {
		return array_merge(
			...array_map(
				fn( $variation ) => $variation->get_downloads(),
				$this->get_variations()
			)
		);
	}

	/**
	 * Set product categories.
	 *
	 * @param array $categories Array of category IDs, slugs, or names.
	 * @return bool
	 */
	public function set_categories( $categories ) {
		return $this->set_terms( $categories, 'product_cat' );
	}

	/**
	 * Get product categories.
	 *
	 * @return array List of category objects.
	 */
	public function get_categories() {
		return $this->get_terms( 'product_cat' );
	}

	/**
	 * Set product tags.
	 *
	 * @param array $tags Array of tag IDs, slugs, or names.
	 * @return bool
	 */
	public function set_tags( $tags ) {
		return $this->set_terms( $tags, 'product_tag' );
	}

	/**
	 * Get product tags.
	 *
	 * @return array List of tag objects.
	 */
	public function get_tags() {
		return $this->get_terms( 'product_tag' );
	}

	/**
	 * Set product brands.
	 *
	 * @param array $brands Array of brand IDs, slugs, or names.
	 * @return bool
	 */
	public function set_brands( $brands ) {
		return $this->set_terms( $brands, 'product_brand' );
	}

	/**
	 * Get product brands.
	 *
	 * @return array List of brand objects.
	 */
	public function get_brands() {
		return $this->get_terms( 'product_brand' );
	}

	/**
	 * Get terms of a product.
	 *
	 * Retrieves the terms assigned to a product for a specified taxonomy (e.g., 'product_cat' or 'product_brand').
	 *
	 * @param string $taxonomy The taxonomy name, e.g., 'product_cat' or 'product_brand'.
	 * @return array List of terms with filtered data.
	 */
	public function get_terms( $taxonomy = 'product_cat' ) {

		$terms = array_map(
			function ( $term ) {

				$term->id = $term->term_id;
				$term->name = wp_specialchars_decode( $term->name );

				unset( $term->term_id );
				unset( $term->term_group );
				unset( $term->term_taxonomy_id );
				unset( $term->taxonomy );
				unset( $term->description );
				unset( $term->count );
				unset( $term->filter );

				return $term;
			},
			wp_get_post_terms( $this->id, $taxonomy )
		);

		return $terms;
	}

	/**
	 * Create a new product.
	 *
	 * @param array $args Arguments for creating the product, including title, slug, description, status, categories, attributes, and variations.
	 * @return int|bool The product ID on success, false on failure.
	 */
	public function create( $args ) {

		if ( empty( $args['title'] ) ) {
			return false;
		}
		$variations_db = new Database( 'product_variations' );
		if ( ! empty( $args['variations'] ) && is_array( $args['variations'] ) ) {
			foreach ( $args['variations'] as $variation_data ) {
				if ( ! empty( $variation_data['sku'] ) ) {
					$variation_data['sku'] = substr( $variation_data['sku'], 0, 100 );

					$existing_variation = $variations_db->get_row( array( 'sku' => $variation_data['sku'] ) );
					if ( $existing_variation ) {
						return new \WP_Error(
							'duplicate_sku',
							/* translators: %s: the duplicate SKU value. */
							sprintf( __( 'The SKU "%s" is already in use.', 'easycommerce' ), $variation_data['sku'] ),
							array( 'status' => 400 )
						);
					}
				}
			}
		}

		// Set product data
		$this->title   = isset( $args['title'] ) ? $args['title'] : '';
		$this->slug    = isset( $args['slug'] ) ? $args['slug'] : '';
		$this->content = isset( $args['content'] ) ? $args['content'] : '';
		$this->status  = isset( $args['status'] ) ? $args['status'] : 'publish';

		// Set template
		if ( isset( $args['meta']['template'] ) ) {
			$args['content'] = Utility::get_template( "patterns/single-product/{$args['meta']['template']}.php" );
		}
		// Save the product to the database
		$this->save();

		// Set description
		if ( isset( $args['description'] ) ) {
			$this->set_description( $args['description'] );
		}

		// Set summary
		if ( isset( $args['summary'] ) ) {
			$this->set_summary( $args['summary'] );
		}

		// Set thumbnail
		if ( isset( $args['thumbnail'] ) ) {
			$this->set_thumbnail( $args['thumbnail'] );
		}

		// Set categories
		if ( isset( $args['categories'] ) ) {
			$this->set_categories( $args['categories'] );
		}

		// Set tags
		if ( isset( $args['tags'] ) ) {
			$this->set_tags( $args['tags'] );
		}

		// Set brands
		if ( isset( $args['brands'] ) ) {
			$this->set_brands( $args['brands'] );
		}

		// Set attributes
		$attribute_model        = new Attribute();
		$attribute_values_model = new Attribute_Value();

		if ( isset( $args['attributes'] ) && is_array( $args['attributes'] ) ) {
			// foreach ( $args['attributes'] as $attribute => $values ) {
			// 	// Add or get the attribute and its values
			// 	$attribute_id = $attribute_model->add( $attribute );

			// 	if ( $attribute_id ) {
			// 		foreach ( $values as $value ) {
			// 			$attribute_values_model->add( $attribute_id, $value );
			// 		}
			// 	}
			// }

			// additionally, we'll store these attributes as a product meta
			$this->update_meta( 'attributes', $args['attributes'] );
		}

		// Set variations, if provided
		$variations = $args['variations'] ?? array();
		if ( ! empty( $variations ) ) {

			$product_variation_meta       = new Product_Variation_Meta();
			$product_variation_downloads  = new Product_Variation_Download();
			$product_variation_attributes = new Product_Variation_Attribute();

			$price_id = 1;
			foreach ( $variations as $variation_data ) {
				$variation = new Product_Variation();
				$variation->set_name( $variation_data['name'] );
				$variation->set_sku( substr( $variation_data['sku'], 0, 100 ) );
				$variation->set_type( $variation_data['type'] ?? 'physical' );
				$variation->set_price( $variation_data['regular_price'] );
				$variation->set_sale_price( $variation_data['sale_price'] == '' ? null : (float) $variation_data['sale_price'] );
				$variation->set_stock_quantity( $variation_data['stock_quantity'] );
				$variation->set_status( $variation_data['status'] );
				$variation->set_price_id( $price_id );
				$variation->set_low_stock_limit( $variation_data['stock_limit'] );
				$variation->product_id = $this->id;

				// Save the variation and process its attributes, meta, and downloads
				if ( $variation->save() ) {
					$variation_id = $variation->get_id();

					// Add variation attributes
					foreach ( $variation_data['attributes'] as $key => $attribute ) {
						$attribute_id         = $attribute['attribute_id'];
						$attribute_slug       = $attribute['attribute_slug'];
						$attribute_value_slug = $attribute['value_slug'];
						$attribute_value_id   = $attribute['value_id'];

						if ( $attribute_id && $attribute_value_id ) {
							$product_variation_attributes->add(
								$variation_id,
								$attribute_slug,
								$attribute_value_slug,
								$attribute_id,
								$attribute_value_id
							);
						}
					}

					// $variation_data['meta']['thumbnail'] = $variation_data['thumbnail'] ?? '';

					// Add variation meta data
					foreach ( $variation_data['meta'] as $meta_key => $meta_value ) {
						$product_variation_meta->add( $variation_id, $meta_key, $meta_value );
					}

					// Add variation downloads
					if ( ! empty( $variation_data['downloads'] ) && is_array( $variation_data['downloads'] ) ) {
						foreach ( $variation_data['downloads'] as $download ) {

							$media_id = $download['media_id'] ?? null;
        					$name     = $download['name'] ?? '';

							if ( ! $media_id ) {
								continue; 
							}
							
							$product_variation_downloads->add(
								$variation_id,
								$media_id,
								$name
							);
						}
					}
				}

				++$price_id;
			}
		}

		// Set product meta data
		if ( isset( $args['meta'] ) && is_array( $args['meta'] ) ) {
			foreach ( $args['meta'] as $key => $value ) {
				$this->update_meta( $key, $value );
			}
		}
		// Return the product ID after creation
		return $this->get_id();
	}

	public function update( $args ) {

		if ( empty( $args['title'] ) ) {
			return false;
		}
		$previous_status 	= $this->get_status();
		$this->title   		= isset( $args['title'] ) ? $args['title'] : '';
		$this->slug    		= isset( $args['slug'] ) ? $args['slug'] : '';
		$this->content 		= isset( $args['content'] ) ? $args['content'] : '';
		$this->status  		= isset( $args['status'] ) ? $args['status'] : 'publish';

		if ( isset( $args['meta']['template'] ) ) {
			$args['content'] = Utility::get_template( "patterns/single-product/{$args['meta']['template']}.php" );
		}

		// Save the product to the database
		$this->save();

		// Set description
		if ( isset( $args['description'] ) ) {
			$this->set_description( $args['description'] );
		}

		// Set summary
		if ( isset( $args['summary'] ) ) {
			$this->set_summary( $args['summary'] );
		}

		// Set thumbnail
		if ( isset( $args['thumbnail'] ) ) {
			$this->set_thumbnail( $args['thumbnail'] );
		}

		// Set categories
		if ( isset( $args['categories'] ) ) {
			$this->set_categories( $args['categories'] );
		}

		// Set Tags
		if ( isset( $args['tags'] ) ) {
			$this->set_tags( $args['tags'], 'product_tag' );
		}

		// Set brands
		if ( isset( $args['brands'] ) ) {
			$this->set_brands( $args['brands'] );
		}

		// Set attributes
		$attribute_model        = new Attribute();
		$attribute_values_model = new Attribute_Value();

		if ( isset( $args['attributes'] ) && is_array( $args['attributes'] ) ) {
			// Store attributes as product meta
			$this->update_meta( 'attributes', $args['attributes'] );
		}

		// Handle variations
		if ( isset( $args['variations'] ) ) {
			$variations = $args['variations'] ?? array();

			// Delete non-matching existing variations if the product is not restored from the trash
			if ( 'trash' !== $previous_status ) {
				foreach ( $this->get_variations() as $variation ) {
					// if an existing variation is not "posted", remove it
					if( ! in_array( $variation->get_id(), wp_list_pluck( $args['variations'], 'id' ) ) ) {
						$variation->delete();
					}
				}
			}
		}
		// Create new variations or update existing ones
		if ( ! empty( $variations ) ) {

			$product_variation_meta       = new Product_Variation_Meta();
			$product_variation_downloads  = new Product_Variation_Download();
			$product_variation_attributes = new Product_Variation_Attribute();

			$price_id = 1;
			foreach ( $variations as $variation_data ) {

				$variation = new Product_Variation( $variation_data['id'] ?? null );
				$variation->set_name( $variation_data['name'] );
				$variation->set_sku( substr( $variation_data['sku'], 0, 100 ) );
				$variation->set_type( $variation_data['type'] ?? 'physical' );
				$variation->set_price( $variation_data['regular_price'] );
				$variation->set_sale_price( $variation_data['sale_price'] == '' ? null : (float) $variation_data['sale_price'] );
				$variation->set_stock_quantity( $variation_data['stock_quantity'] == '' ? null : $variation_data['stock_quantity'] );
				$variation->set_status( $variation_data['status'] );
				$variation->set_price_id( $price_id );
				$variation->set_low_stock_limit( $variation_data['stock_limit'] );
				$variation->product_id = $this->id;

				// Save the variation and process its attributes, meta, and downloads
				if ( $variation->save() ) {
					$variation_id = $variation->get_id();

					if ( ! empty( $variation_data['attributes'] ) ) {

						$existing_attrs = $product_variation_attributes->get( $variation_id );
						$existing_ids   = array_column( $existing_attrs, 'attribute_id' );

						foreach ( $variation_data['attributes'] as $attribute ) {
							if ( is_string( $attribute ) ) {
								$attribute = $attribute_model->get_by_slug( $attribute );
							}

							$attribute_id         = $attribute['attribute_id'] ?? null;
							$attribute_slug       = $attribute['attribute_slug'] ?? '';
							$attribute_value_slug = $attribute['value_slug'] ?? '';
							$attribute_value_id   = $attribute['value_id'] ?? null;

							if ( ! $attribute_id || ! $attribute_value_id ) {
								continue;
							}

							if ( ! in_array( $attribute_id, $existing_ids ) ) {
								$product_variation_attributes->add(
									$variation_id,
									$attribute_slug,
									$attribute_value_slug,
									$attribute_id,
									$attribute_value_id
								);
							}
						}
					}
					// Replace variation downloads: clear existing rows first so removed
					// files don't persist and re-sent files don't duplicate on save.
					$product_variation_downloads->delete_by_variation( $variation_id );

					// Add variation downloads
					if ( ! empty( $variation_data['downloads'] ) && is_array( $variation_data['downloads'] ) ) {
						foreach ( $variation_data['downloads'] as $download ) {

							$media_id = $download['media_id'] ?? null;
        					$name     = $download['name'] ?? '';

							if ( ! $media_id ) {
								continue; 
							}
							
							$product_variation_downloads->add(
								$variation_id,
								$media_id,
								$name
							);
						}
					}
					
					// Update variation meta data
					foreach ( $variation_data['meta'] as $meta_key => $meta_value ) {
						$product_variation_meta->update( $variation_id, $meta_key, $meta_value );
					}
				}

				++$price_id;
			}
		}

		// Set product meta data
		if ( isset( $args['meta'] ) && is_array( $args['meta'] ) ) {
			foreach ( $args['meta'] as $key => $value ) {
				$this->update_meta( $key, $value );
			}
		}

		// Return the product ID after update
		return $this->get_id();
	}

	/**
	 * Save product data.
	 *
	 * @return bool
	 */
	public function save() {
		// Set default content for new products
		if ( ! $this->id && empty( $this->content ) ) {
			$this->content = Utility::get_template( 'patterns/single-product/template-1.php' );
		}

		$post_data = array(
			'ID'           => $this->id,
			'post_title'   => $this->title,
			'post_name'    => $this->slug,
			'post_content' => $this->content ? $this->content : '',
			'post_status'  => $this->status ? $this->status : 'publish',
			'post_type'    => 'product',
		);

		if ( $this->id ) {
			$post_data['ID'] = $this->id;
			$post_id         = wp_update_post( $post_data );
		} else {
			$post_id  = wp_insert_post( $post_data );
			$this->id = $post_id;
		}

		return ! is_wp_error( $post_id );
	}

	/**
	 * Delete product.
	 *
	 * @return bool
	 */
	public function delete( $force = false ) {
		$result = $force ? wp_delete_post( $this->id ) : wp_trash_post( $this->id );
		return ( $result !== false );
	}

	/**
	 * Static method to get products by filters using WP_Query and return total count.
	 *
	 * @param array $filters Filters like search query, status, etc.
	 * @param int   $limit   Number of products to return.
	 * @param int   $offset  Offset for pagination.
	 * @return array List of Product objects and total count.
	 *
	 * @todo improve logic for params of different sets. It should work like this-
	 *              ( cat1 OR cat2 ) AND ( tag1 OR tag2 ) AND ( color1 OR color2 )
	 */
	public static function list( $filters = array(), $limit = 10, $offset = 0, $detailed = true, $product_status = false ) {
		// Prepare WP_Query arguments for the main query (paginated results)
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'post_status'    => isset( $filters['status'] ) ? $filters['status'] : 'publish',
		);

		$variations_meta_db = new Database( 'product_variations' );
		$product_meta_db	= new Database( 'product_meta' );

		// Add search filter if provided
		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = $filters['search'];
		}

		// Add tax_query if provided
		if ( ! empty( $filters['tax_query'] ) ) {
			$args['tax_query'] = $filters['tax_query'];
		}

		// Add post__not_in if provided
		if ( ! empty( $filters['post__not_in'] ) ) {
			$args['post__not_in'] = $filters['post__not_in'];
		}

		$post__in = array();

		// Add min_price and max_price filter
		if ( ! empty( $filters['min_price'] ) || ! empty( $filters['max_price'] ) ) {
			$min_price       = isset( $filters['min_price'] ) ? $filters['min_price'] : 0;
			$max_price       = isset( $filters['max_price'] ) ? $filters['max_price'] : PHP_INT_MAX;
			$priced_products = $variations_meta_db->get_rows(
				array(
					array( 'price' => array( '>=', $min_price ) ),
					array( 'price' => array( '<=', $max_price ) ),
				)
			);

			if ( ! empty( $priced_products ) ) {
				$post__in = array_merge( $post__in, wp_list_pluck( $priced_products, 'product_id' ) );
			} else {
				$post__in = array( 0 );
			}
		}

		if ( ! empty( $filters['sku'] ) ) {
			$sku_products = $variations_meta_db->get_rows(
				array( array( 'sku' => $filters['sku'] ) )
			);
			if ( ! empty( $sku_products ) ) {
				$post__in = array_merge( $post__in, wp_list_pluck( $sku_products, 'product_id' ) );
			} else {
				$post__in = array( 0 );
			}
		}

		// Add sort_by filter for price,date,rating,sales sorting
		if ( ! empty( $filters['sort_by'] ) ) {
			if ( in_array( $filters['sort_by'], array( 'low-to-high', 'high-to-low' ), true ) ) {
				$order           = $filters['sort_by'] === 'high-to-low' ? 'DESC' : 'ASC';
				$min_price       = 0;
				$max_price       = PHP_INT_MAX;
				$sorted_products = $variations_meta_db->get_rows(
					array(
						array( 'price' => array( '>=', $min_price ) ),
						array( 'price' => array( '<=', $max_price ) ),
					),
					0,
					0,
					$order,
					'price'
				);

				if ( ! empty( $sorted_products ) ) {
					$post__in         = wp_list_pluck( $sorted_products, 'product_id' );
					$args['post__in'] = $post__in;
					$args['orderby']  = 'post__in';
				} else {
					$post__in = array( 0 );
				}
			} elseif ( in_array( $filters['sort_by'], array( 'latest', 'newest', 'oldest' ), true ) ) {
				$args['orderby'] = 'date';
				$args['order']   = $filters['sort_by'] === 'oldest' ? 'ASC' : 'DESC';
			} elseif ( isset( self::$meta_sorts[ $filters['sort_by'] ] ) ) {
				$args['easycommerce_meta_sort'] = self::$meta_sorts[ $filters['sort_by'] ];
			}
		}

		// Add brands filter if provided
		if ( ! empty( $filters['brands'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_brand',
				'field'    => 'slug',
				'terms'    => $filters['brands'],
			);
		}

		// Add categories filter if provided
		if ( ! empty( $filters['categories'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $filters['categories'],
			);
		}

		// Add attributes filter if provided
		if ( ! empty( $filters['attributes'] ) ) {
			$variation   = new Product_Variation();
			$result      = $variation->get_by_attributes( $filters['attributes'], true );

			if ( ! empty( $result ) ) {
				$matched_ids 	= is_array( $result ) ? wp_list_pluck( $result, 'product_id' ) : [ $result->product_id ];
				$post__in 		= ! empty( $post__in ) ? array_intersect( $post__in, $matched_ids ) : $matched_ids;
			} else {
				$post__in 		= [ 0 ];
			}
		}

		if ( ! empty( $filters['is_shop'] ) ) {
			// hidden products
			$hidden_products = $product_meta_db->get_rows(
				array(
					array( 'meta_key' => 'hide_from_shop' ),
					array( 'meta_value' => '1' )
				)
			);

			if ( ! empty( $hidden_products ) ) {
				$hidden_ids = wp_list_pluck( $hidden_products, 'product_id' );

				if ( ! empty( $post__in ) ) {
					$post__in = array_diff( $post__in, $hidden_ids );
				} else {
					$args['post__not_in'] = array_merge(
						$args['post__not_in'] ?? [],
						$hidden_ids
					);
				}
			}
		}

		if ( ! empty( $post__in ) ) {
			$args['post__in'] = $post__in;
		}

		// Registered only for the meta-backed sorts, and only around the queries below.
		$meta_sorted = isset( $args['easycommerce_meta_sort'] );

		if ( $meta_sorted ) {
			add_filter( 'posts_clauses', array( __CLASS__, 'meta_sort_clauses' ), 10, 2 );
		}

		// Query to get the paginated products
		$query = new \WP_Query( $args );

		// Total number of products (without pagination)
		$total_products = ( new \WP_Query(
			array_merge(
				$args,
				array(
					'posts_per_page' => -1,
					'offset'         => 0,
				)
			)
		) )->found_posts;

		if ( $meta_sorted ) {
			remove_filter( 'posts_clauses', array( __CLASS__, 'meta_sort_clauses' ), 10 );
		}

		$products = $query->posts;

		if ( $detailed === true ) {
			// Return array of Product objects and total count
			$products = array_map(
				function ( $post ) {
					return new self( $post->ID );
				},
				$query->posts
			);
		}
		$statuses_counts = [];

		if ( $product_status ) {
			$status_query = new \WP_Query( array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'draft', 'trash' ),
				'fields'         => 'ids',
			) );

			if ( $status_query->have_posts() ) {
				foreach ( $status_query->posts as $product_id ) {
					$status = get_post_status( $product_id );
					if ( ! isset( $statuses_counts[ $status ] ) ) {
						$statuses_counts[ $status ] = 0;
					}
					$statuses_counts[ $status ]++;
				}
			}
		}
		return array(
			'products' 			=> $products,
			'total'    			=> $total_products,
			'statuses_counts' 	=> $statuses_counts,
		);
	}

	/**
	 * Order a product query by a post meta value without excluding products that lack it.
	 *
	 * @param array     $clauses SQL clauses for the query.
	 * @param \WP_Query $query   The query being run.
	 * @return array
	 */
	public static function meta_sort_clauses( $clauses, $query ) {
		global $wpdb;

		$sort = $query->get( 'easycommerce_meta_sort' );

		if ( empty( $sort['key'] ) ) {
			return $clauses;
		}

		$order = ( isset( $sort['order'] ) && strtoupper( $sort['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';

		$clauses['join'] .= $wpdb->prepare(
			" LEFT JOIN {$wpdb->postmeta} AS ec_sort_meta
				ON ( {$wpdb->posts}.ID = ec_sort_meta.post_id AND ec_sort_meta.meta_key = %s )",
			$sort['key']
		);

		$value   = empty( $sort['nulls_last'] )
			? 'COALESCE( ec_sort_meta.meta_value + 0, 0 )'
			: 'ec_sort_meta.meta_value + 0';
		$orderby = empty( $sort['nulls_last'] )
			? "{$value} {$order}"
			: "( ec_sort_meta.meta_value IS NOT NULL ) DESC, {$value} {$order}";

		$clauses['orderby'] = "{$orderby}, {$wpdb->posts}.ID DESC";

		if ( false === strpos( $clauses['groupby'], "{$wpdb->posts}.ID" ) ) {
			$clauses['groupby'] = "{$wpdb->posts}.ID";
		}

		return $clauses;
	}

	/**
	 * @var string $type The range type- min or max
	 */
	public static function get_store_price( $type, $formatted = false ) {

		if ( ! in_array( $type, array( 'min', 'max' ), true ) ) {
			return null;
		}

		$db    = ( new self() )->db;
		$query = $db->exec( "SELECT {$type}(`price`) AS `price` FROM `{$db->get_prefix()}product_variations`" );

		if ( empty( $query ) ) {
			return $formatted ? easycommerce_price( 0 ) : 0;
		}

		$price = reset( $query );

		return $formatted ? easycommerce_price( $price->price ) : $price->price;
	}

	/**
	 * Set product terms.
	 *
	 * @param array  $terms Array of category/brand IDs, slugs, or names.
	 * @param string $taxonomy The taxonomy name
	 *
	 * @return bool
	 */
	public function set_terms( $terms = array(), $taxonomy = 'product_cat' ) {
		if ( empty( $terms ) ) {
			return false;
		}

		$term_ids = array();

		foreach ( $terms as $_term ) {
			if ( is_numeric( $_term ) ) {
				$term_ids[] = intval( $_term );
			} elseif ( is_string( $_term ) ) {
				$term = get_term_by( 'slug', $this->sanitize( $_term, 'title' ), $taxonomy );

				if ( ! $term ) {
					$term = get_term_by( 'name', $_term, $taxonomy );
				}

				if ( ! $term ) {
					$new_term = wp_insert_term( $_term, $taxonomy );

					if ( is_wp_error( $new_term ) ) {
						return false;
					}

					$term_ids[] = $new_term['term_id'];
				} else {
					$term_ids[] = $term->term_id;
				}
			}
		}

		// Assign the category IDs to the product
		$result = wp_set_object_terms( $this->id, $term_ids, $taxonomy );

		return ! is_wp_error( $result );
	}

	/**
	 * Add product meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function add_meta( $key, $value ) {
		return $this->meta->add( $this->id, $key, $value );
	}

	/**
	 * Get product meta data.
	 *
	 * @param string $key
	 * @param bool   $single
	 * @return mixed
	 */
	public function get_meta( $key = '', $single = true ) {
		return $this->meta->get( $this->id, $key, $single );
	}

	/**
	 * Update product meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function update_meta( $key, $value ) {
		return $this->meta->update( $this->id, $key, $value );
	}

	/**
	 * Delete product meta data.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function delete_meta( $key ) {
		return $this->meta->delete( $this->id, $key );
	}

	/**
	 * Get product reviews.
	 */
	public function get_reviews( $limit = '' ) {

		$comments = get_comments(
			array(
				'post_id' => $this->id,
				'status'  => 'approve',
				'number'  => $limit,
			)
		);

		$reviews = array();
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

		return $reviews;
	}

	/**
	 * Get related products.
	 */
	public function get_related( $number = 5, $detailed = false ) {
		$categories = $this->get_categories();
		$tags       = $this->get_tags();

		$term_ids = array();

		foreach ( $categories as $cat ) {
			$term_ids[] = $cat->id;
		}

		foreach ( $tags as $tag ) {
			$term_ids[] = $tag->id;
		}

		if ( empty( $term_ids ) ) {
			return array();
		}

		$tax_query = array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'IN',
			),
			array(
				'taxonomy' => 'product_tag',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'IN',
			),
		);

		$filters = array(
			'tax_query'   => $tax_query,
			'post__not_in' => array( $this->id ),
			'sort_by'     => 'latest',
		);

		$result = self::list( $filters, $number, 0, $detailed );

		$products = $result['products'];

		if ( ! $detailed ) {
			$related_products = array();
			
			foreach ( $products as $post ) {
				$related_products[ $post->ID ] = $post->post_title;
			}

			$products = $related_products;
		}

		return $products;
	}


	protected static $badge_definitions = array(
		'sale'        => array(
			'label' => 'Sale',
			'color' => '#EF4444',
			'text'  => '#FFFFFF',
		),
		'new'         => array(
			'label' => 'New Arrival',
			'color' => '#22C55E',
			'text'  => '#FFFFFF',
		),
		'best_seller' => array(
			'label' => 'Best Seller',
			'color' => '#F59E0B',
			'text'  => '#FFFFFF',
		),
		'featured'    => array(
			'label' => 'Featured',
			'color' => '#3B82F6',
			'text'  => '#FFFFFF',
		),
	);

	// Precedence, highest first. "out_of_stock" is derived from stock, not a
	// manual/meta badge, so it's handled separately in get_badges().
	protected static $badge_precedence = array( 'sale', 'best_seller', 'new', 'featured' );

	

	// -----------------------------------------------------------------------
	// 2) Badge methods
	// -----------------------------------------------------------------------

	/**
	 * Get the active badges for this product, ordered by display precedence
	 * (Sale > Best Seller > New > Featured > Out of Stock).
	 *
	 * Each entry: array( 'type' => string, 'label' => string, 'color' => string, 'text_color' => string, 'position' => string )
	 *
	 * @return array
	 */
	public function get_badges() {
		$badges = array();

		// Out of stock is reported here too so callers don't need a second lookup;
		// the storefront renderer treats it as an override, per spec.
		$stock = $this->get_stock();
		$is_out_of_stock = ( $stock !== null && $stock <= 0 );

		// Sale — auto-computed from price. A manual `_badge_sale` meta override
		// is also honored (e.g. a promo the merchant wants to force even if
		// prices don't strictly reflect it).
		$forced_sale = $this->get_meta( '_badge_sale' );
		if ( $forced_sale === '1' || $forced_sale === true || $this->has_active_sale_price() ) {
			$badges[] = $this->build_badge( 'sale' );
		}

		// Best seller — manual toggle.
		if ( $this->get_meta( '_badge_best_seller' ) ) {
			$badges[] = $this->build_badge( 'best_seller' );
		}

		// New arrival — manual toggle that auto-expires after 30 days.
		if ( $this->has_active_new_badge() ) {
			$badges[] = $this->build_badge( 'new' );
		}

		// Featured — manual toggle.
		if ( $this->get_meta( '_badge_featured' ) ) {
			$badges[] = $this->build_badge( 'featured' );
		}

		if ( $is_out_of_stock ) {
			$badges[] = array(
				'type'       => 'out_of_stock',
				'label'      => __( 'Out of Stock', 'easycommerce' ),
				'color'      => '#374151', // gray-700
				'text_color' => '#FFFFFF',
				'position'   => 'top-center',
			);
		}

		// Sort by declared precedence; out_of_stock always renders last in the
		// array but the storefront renderer treats it as an override.
		$order = array_flip( array_merge( self::$badge_precedence, array( 'out_of_stock' ) ) );
		usort(
			$badges,
			function ( $a, $b ) use ( $order ) {
				return ( $order[ $a['type'] ] ?? 999 ) <=> ( $order[ $b['type'] ] ?? 999 );
			}
		);

		return $badges;
	}

	/**
	 * Build a badge array from the static definitions.
	 *
	 * @param string $type One of self::$badge_definitions keys.
	 * @return array
	 */
	protected function build_badge( $type ) {
		$definition = self::$badge_definitions[ $type ] ?? array();

		return array(
			'type'       => $type,
			'label'      => $definition['label'] ?? ucfirst( $type ),
			'color'      => $definition['color'] ?? '#6B7280',
			'text_color' => $definition['text'] ?? '#FFFFFF',
			'position'   => 'top-left',
		);
	}

	/**
	 * Whether the product currently has a genuine sale (sale_price < regular_price)
	 * on at least one variation.
	 *
	 * @return bool
	 */
	public function has_active_sale_price() {
		foreach ( $this->get_prices( false ) as $price ) {
			$regular = $price['regular_price'];
			$sale    = $price['sale_price'];

			if (
				is_numeric( $regular )
				&& is_numeric( $sale )
				&& (float) $sale > 0
				&& (float) $sale < (float) $regular
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Whether the manually-set "New" badge is still within its 30-day window.
	 *
	 * @return bool
	 */
	public function has_active_new_badge() {
		if ( ! $this->get_meta( '_badge_new' ) ) {
			return false;
		}

		$set_at = $this->get_meta( '_badge_new_set_at' );

		if ( empty( $set_at ) ) {
			// Backfill: treat as just-set so it doesn't immediately vanish for
			// pre-existing toggles saved before this field existed.
			$this->update_meta( '_badge_new_set_at', current_time( 'mysql' ) );
			return true;
		}

		$expires = strtotime( $set_at ) + ( 30 * DAY_IN_SECONDS );

		if ( time() > $expires ) {
			// Auto-clear expired badge so subsequent reads/writes stay consistent.
			$this->update_meta( '_badge_new', false );
			$this->delete_meta( '_badge_new_set_at' );
			return false;
		}

		return true;
	}

	/**
	 * Set (or clear) a manual badge on this product.
	 *
	 * Supported $badge_type values: 'new', 'best_seller', 'featured', 'sale'.
	 *
	 * @param string $badge_type
	 * @param bool   $value
	 * @return bool
	 */
	public function set_badge( $badge_type, $value ) {
		switch ( $badge_type ) {
			case 'new':
				$enabled = (bool) $value;
				$this->update_meta( '_badge_new', $enabled );

				if ( $enabled ) {
					$this->update_meta( '_badge_new_set_at', current_time( 'mysql' ) );
				} else {
					$this->delete_meta( '_badge_new_set_at' );
				}

				return true;

			case 'best_seller':
				return (bool) $this->update_meta( '_badge_best_seller', (bool) $value );

			case 'featured':
				return (bool) $this->update_meta( '_badge_featured', (bool) $value );

			case 'sale':
				// Manual override; auto_set_sale_badge() handles the computed case.
				return (bool) $this->update_meta( '_badge_sale', (bool) $value );

			default:
				return false;
		}
	}

	/**
	 * Recompute the auto sale badge from current variation prices. Call this
	 * whenever a variation's regular_price/sale_price changes (e.g. from
	 * Product::update() after variations are saved).
	 *
	 * @return bool Whether the product is currently on sale.
	 */
	public function auto_set_sale_badge() {
		$on_sale = $this->has_active_sale_price();

		// Only meaningful as a cache; get_badges() recomputes has_active_sale_price()
		// directly, so this is mainly useful for list views / bulk queries that
		// want to filter by "_badge_sale" meta without loading every variation.
		$this->update_meta( '_badge_sale', $on_sale );

		return $on_sale;
	}
}

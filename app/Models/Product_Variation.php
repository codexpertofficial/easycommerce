<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Abstracts\Model;

/**
 * Concrete Variation Class
 */
class Product_Variation extends Model {

	/**
	 * @var int Variation ID
	 */
	public $id;

	/**
	 * @var int Product ID
	 */
	public $product_id;

	/**
	 * @var string Variation name
	 */
	public $name = '';

	/**
	 * @var string Variation SKU
	 */
	public $sku = '';

	/**
	 * @var float Variation price
	 */
	public $price = 0.0;

	/**
	 * @var int Variation price_id
	 */
	public $price_id = 0.0;

	/**
	 * @var float Variation sale price
	 */
	public $sale_price = 0.0;

	/**
	 * @var int Variation type
	 */
	public $type = 'digital';

	/**
	 * @var int|null Variation stock quantity. Null means stock is not managed.
	 */
	public $stock_quantity = null;

	/**
	 * @var int Variation stock limit
	 */
	public $stock_limit = 0;

	/**
	 * @var string Variation status
	 */
	public $status = '';

	/**
	 * @var bool Variation existence flag
	 */
	public $exists = false;

	/**
	 * @var Product_Variation_Meta Variation meta instance
	 */
	public $meta;

	/**
	 * @var Product_Variation_Download Variation downloads instance
	 */
	public $downloads;

	/**
	 * @var Product_Variation_Attribute Variation attributes instance
	 */
	public $attributes;

	protected $table = 'product_variations';

	public function __construct( $id = null ) {
		parent::__construct();
		$this->meta       = new Product_Variation_Meta();
		$this->downloads  = new Product_Variation_Download();
		$this->attributes = new Product_Variation_Attribute();

		if ( $id ) {
			$variation = $this->db->get_by_id( $id );

			if ( $variation ) {
				$this->id             = (int) $variation->id;
				$this->product_id     = (int) $variation->product_id;
				$this->price_id       = (int) $variation->price_id;
				$this->name           = $variation->name;
				$this->sku            = $variation->sku;
				$this->type           = $variation->type;
				$this->price          = (float) $variation->price;
				$this->sale_price     = (float) $variation->sale_price;
				$this->stock_quantity = is_null( $variation->stock_quantity ) ? null : (int) $variation->stock_quantity;
				$this->stock_limit    = $variation->stock_limit;
				$this->status         = $variation->status;
				$this->exists         = true;
			}
		}
	}

	/**
	 * Get variation ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get product ID.
	 *
	 * @return int|null
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Get product.
	 *
	 * @return int|null
	 */
	public function get_product() {
		$product = new Product( $this->product_id );

		return $product;
	}

	/**
	 * Check if the variation exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Get variation name.
	 *
	 * @param bool $full Should it prepend the product name
	 *
	 * @return string
	 */
	public function get_name( $full = false ) {

		if ( $full ) {
			return $this->get_product()->get_title() . ' - ' . $this->name;
		}

		return $this->name;
	}

	/**
	 * Get variation SKU.
	 *
	 * @return string
	 */
	public function get_sku() {
		return $this->sku;
	}

	/**
	 * Set variation name.
	 *
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Set variation SKU.
	 *
	 * @param string $sku
	 */
	public function set_sku( $sku ) {
		$this->sku = $sku;
	}

	/**
	 * Get variation thumbnail
	 *
	 * If no thumbnail is set for this variation, fallback to the product thumbnail
	 *
	 * @return string The URL
	 */
	public function get_thumbnail() {
		if ( '' == ( $thumbnail = $this->get_meta( 'thumbnail' ) ) ) {
			return $this->get_product()->get_thumbnail();
		}

		return $thumbnail;
	}

	/**
	 * Get variation thumbnail
	 *
	 * @param string $url The URL
	 */
	public function set_thumbnail( $url = '' ) {
		$this->update_meta( 'thumbnail', $url );
	}

	/**
	 * Get variation price.
	 *
	 * If the variation has a "valid" sale price set, return it. Otherwise return the regular price.
	 *
	 * @return float
	 */
	public function get_price( $formatted = false ) {
		if ( false !== ( $sale_price = $this->get_sale_price() ) && $sale_price <= $this->get_regular_price() ) {
			$price = $sale_price;
		} else {
			$price = $this->get_regular_price();
		}

		if ( $formatted ) {
			return easycommerce_price( $price );
		}

		return $price;
	}

	/**
	 * Get variation regular (non-sale) price.
	 *
	 * @return float
	 */
	public function get_regular_price( $formatted = false ) {

		if ( $formatted ) {
			return easycommerce_price( $this->price );
		}

		return $this->price;
	}

	/**
	 * Set variation price.
	 *
	 * @param float $price
	 */
	public function set_price( $price ) {
		$this->price = $price;
	}

	/**
	 * Get variation price_id.
	 *
	 * @return float
	 */
	public function get_price_id() {
		return $this->price_id;
	}

	/**
	 * Set variation price_id.
	 *
	 * @param float $price_id
	 */
	public function set_price_id( $price_id ) {
		$this->price_id = $price_id;
	}

	/**
	 * Get variation sale price.
	 *
	 * @return float
	 */
	public function get_sale_price( $formatted = false ) {
		$sale_price = $this->sale_price && $this->sale_price <= $this->price ? $this->sale_price : false;

		if ( $sale_price === false ) {
			return $sale_price;
		}

		if ( $formatted ) {
			return easycommerce_price( $sale_price );
		}

		return $sale_price;
	}

	/**
	 * Set variation sale price.
	 *
	 * @param float $sale_price
	 */
	public function set_sale_price( $sale_price ) {
		$this->sale_price = $sale_price;
	}

 	/**
 	 * If this variation manages stock
 	 *
 	 * @return bool
 	 */
 	public function manages_stock() {
 		return null !== $this->stock_quantity;
 	}

	/**
	 * Get variation stock quantity.
	 *
	 * @return int|null
	 */
	public function get_stock() {
		return $this->stock_quantity;
	}

	/**
	 * Set variation stock quantity.
	 *
	 * @param int|null $stock_quantity
	 */
	public function set_stock_quantity( $stock_quantity ) {
		$this->stock_quantity = ( null === $stock_quantity || '' === $stock_quantity ) ? null : (int) $stock_quantity;
	}

	/**
	 * Set variation type.
	 *
	 * @param string $type
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}

	/**
	 * Get variation type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get variation status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set variation status.
	 *
	 * @param string $status
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

 	/**
 	 * Save variation data.
 	 *
 	 * @return int|bool Variation ID on success, false on failure.
 	 */
 	public function save() {
 		$data = array(
 			'product_id'     => $this->product_id,
 			'price_id'       => $this->price_id,
 			'name'           => $this->name,
 			'sku'            => $this->sku,
 			'type'           => $this->type,
 			'price'          => $this->price,
 			'sale_price'     => $this->sale_price,
 			'stock_quantity' => $this->stock_quantity,
 			'stock_limit'    => $this->stock_limit,
 			'status'         => $this->status,
 		);

  		if ( $this->id ) {
  			$this->db->update_row( $this->id, $data );
  			return $this->id;
  		} else {
  			$this->id = (int) $this->db->insert_row( $data );
  			if ( $this->id ) {
  				$this->exists = true;
  				return $this->id;
  			}
  			return false;
  		}
 	}

	/**
	 * Delete variation.
	 *
	 * @return bool
	 */
	public function delete() {
		return $this->db->delete_row( $this->id );
	}

	/**
	 * Get variation meta data.
	 *
	 * @param string $key
	 * @param bool   $single
	 * @return mixed
	 */
	public function get_meta( $key = '', $single = true ) {
		return $this->meta->get( $this->id, $key, $single );
	}

	/**
	 * Add variation meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function add_meta( $key, $value ) {
		return $this->meta->add( $this->id, $key, $value );
	}

	/**
	 * Update variation meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function update_meta( $key, $value ) {
		return $this->meta->update( $this->id, $key, $value );
	}

	/**
	 * Delete variation meta data.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function delete_meta( $key ) {
		return $this->meta->delete( $this->id, $key );
	}

	/**
	 * Get variation downloads.
	 *
	 * @return array
	 */
	public function get_downloads() {
		return $this->downloads->get( $this->id );
	}

	/**
	 * Add variation download.
	 *
	 * @param string $name
	 * @param string $filename
	 * @param int    $filesize
	 * @param int    $download_limit
	 * @param string $expiry
	 * @return bool
	 */
	public function add_download( $media_id, $name ) {
		return $this->downloads->add( $this->id, $media_id, $name );
	}

	/**
	 * Delete variation download.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete_download( $id ) {
		return $this->downloads->delete( $id );
	}

	/**
	 * Get variation attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {
		return $this->attributes->get( $this->id );
	}

	/**
	 * Get height
	 *
	 * @return array|bool An array of value and unit, if found. False otherwise.
	 */
	public function get_height() {
		$height = $this->get_meta( 'height', true );

		if ( ! isset( $height['value'] ) || is_null( $height['value'] ) ) {
			return false;
		}

		return $height;
	}

	/**
	 * Get width
	 *
	 * @return array|bool An array of value and unit, if found. False otherwise.
	 */
	public function get_width() {
		$width = $this->get_meta( 'width', true );

		if ( ! isset( $width['value'] ) || is_null( $width['value'] ) ) {
			return false;
		}

		return $width;
	}

	/**
	 * Get length
	 *
	 * @return array|bool An array of value and unit, if found. False otherwise.
	 */
	public function get_length() {
		$length = $this->get_meta( 'length', true );

		if ( ! isset( $length['value'] ) || is_null( $length['value'] ) ) {
			return false;
		}

		return $length;
	}

	/**
	 * Get tax class
	 *
	 * @return integer The Tax Class ID
	 */
	public function get_tax_class() {
		return $this->get_meta( 'tax_class' );
	}

	/**
	 * Set tax class
	 *
	 * @param int $class_id The Tax Class ID
	 */
	public function set_tax_class( $class_id ) {
		if ( ! empty( $class_id ) ) {
			$this->update_meta( 'tax_class', $class_id );
		}
	}

	/**
	 * Get stock limit
	 *
	 * @return integer get stock limit
	 */
	public function get_low_stock_limit() {
		return $this->stock_limit;
	}

	/**
	 * Set stock limit
	 *
	 * @return integer set stock limit
	 */
	public function set_low_stock_limit( $limit ) {
		return $this->stock_limit = $limit;
	}

	/**
	 * Get weight
	 *
	 * @return array|bool An array of value and unit, if found. False otherwise.
	 */
	public function get_weight( $in_kg = false ) {
		$weight = $this->get_meta( 'weight', true );

		if ( ! isset( $weight['value'] ) || is_null( $weight['value'] ) ) {
			return false;
		}

		// Define unit conversion factors to kilograms
		$unit_conversions_to_kg = easycommerce_weight_unit_conversion( 'kg' );

		if ( $in_kg ) {
			// Convert weight to kilograms based on stored unit
			$unit 				= isset( $weight['unit'] ) ? $weight['unit'] : 'kg'; // Default to kg if unit is missing
			$conversion_factor 	= isset( $unit_conversions_to_kg[$unit] ) ? $unit_conversions_to_kg[$unit] : 1;
			
			return $weight['value'] * $conversion_factor;
		}

		return $weight; // Return raw weight array with value and unit
	}

	/**
	 * Add variation attribute.
	 *
	 * @param int $attribute_id
	 * @param int $value_id
	 * @return bool
	 */
	public function add_attribute( $attribute_id, $value_id ) {
		$attribute_id = (int) $attribute_id;
		$value_id     = (int) $value_id;

		// The FK columns are attribute_id/value_id; resolve their slugs (the
		// row also stores them) so ids are not misrouted into the slug params.
		$attribute      = ( new Attribute() )->get( $attribute_id );
		$value          = ( new Attribute_Value() )->get( $value_id );
		$attribute_slug = $attribute->slug ?? '';
		$value_slug     = $value->slug ?? '';

		return $this->attributes->add( $this->id, $attribute_slug, $value_slug, $attribute_id, $value_id );
	}

	/**
	 * Delete variation attribute.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete_attribute( $id ) {
		return $this->attributes->delete( $id );
	}

	/**
	 * Get a variation by price_id and product_id.
	 *
	 * @param int $price_id The price_id
	 * @param int $product_id The product_id
	 * @return bool|object
	 */
	public function get_by_price( $price_id, $product_id ) {
		$variation = $this->db->get_row(
			array(
				'product_id' => $product_id,
				'price_id'   => $price_id,
			)
		);

		if ( is_null( $variation ) ) {
			return false;
		}

		return new self( $variation->id );
	}

	/**
	 * Get variation by attributes.
	 *
	 * @param array $attributes Associative array of attribute_slug => value_slug pairs.
	 *                          Example:
	 *                          [
	 *                              'color' => 'midnight',
	 *                              'size' => '41mm',
	 *                              'connectivity' => 'gps'
	 *                          ]
	 * @return Product_Variation|null The matching variation instance or null if not found.
	 *
	 * @todo improve as it's not considering product_id
	 */
	public function get_by_attributes( $attributes = array(), $show_all = false ) {

		$database = new Database( 'product_variation_attributes' );

		$where_clause = array();
		$values       = array();

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute_slug => $value_slugs ) {
				// Handle both single values and arrays
				if ( ! is_array( $value_slugs ) ) {
					$value_slugs = array( $value_slugs );
				}
				
				foreach ( $value_slugs as $value_slug ) {
					$where_clause[] = '(attribute_slug = %s AND value_slug = %s)';
					$values[]       = $attribute_slug;
					$values[]       = $value_slug;
				}
			}
		}

		// Build the query using OR logic to match any of the selected attributes
		$where_clause_str = implode( ' OR ', $where_clause );
		$query            = "SELECT DISTINCT variation_id
                  FROM {$database->get_table()}
                  WHERE $where_clause_str";

		$prepared_query = $database->get_instance()->prepare( $query, ...$values );
		$results        = $database->get_instance()->get_results( $prepared_query );

		if ( ! empty( $results ) ) {
			if ( $show_all ) {
				return array_map( function( $row ) {
					return new self( $row->variation_id );
				}, $results );
			} else {
				return new self( $results[0]->variation_id );
			}
		}

		return false;
	}

	/**
	 * Get all variations by a specific field and value.
	 *
	 * @param mixed  $value The value to search for (e.g., product ID).
	 * @param string $key The field to search by (defaults to 'product_id').
	 * @return array An array of Product_Variation instances.
	 */
	public function get_by( $value, $key = 'product_id' ) {
		$variation_rows = $this->db->get_rows( array( $key => $value ), 0, 0, 'ASC' );

		return array_map(
			function ( $row ) {
				return new self( $row->id );
			},
			$variation_rows
		);
	}
}

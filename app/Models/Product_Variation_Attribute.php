<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Model;

/**
 * Class Product_Variation_Attribute
 * Handles product variation attributes operations.
 *
 * @package EasyCommerce\Model
 */
class Product_Variation_Attribute extends Model {

	protected $table = 'product_variation_attributes';

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add variation attribute.
	 *
	 * @param int    $variation_id
	 * @param string $attribute_slug
	 * @param string $value_slug
	 * @param int    $attribute_id
	 * @param int    $value_id
	 * @return bool
	 */
	public function add( $variation_id, $attribute_slug, $value_slug, $attribute_id = 0, $value_id = 0 ) {
		$data = array(
			'variation_id'   => $variation_id,
			'attribute_slug' => $attribute_slug,
			'value_slug'     => $value_slug,
			'attribute_id'   => $attribute_id,
			'value_id'       => $value_id,
		);

		return $this->db->insert_row( $data );
	}

	/**
	 * Get variation attributes.
	 *
	 * @param int $variation_id
	 * @return array
	 */
	public function get( $variation_id ) {
		return $this->db->get_rows( array( 'variation_id' => $variation_id ) );
	}

	/**
	 * Update variation attribute.
	 *
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update( $id, $data ) {
		return $this->db->update_row( $id, $data );
	}

	/**
	 * Delete variation attribute.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete( $id ) {
		return $this->db->delete_row( $id );
	}
}

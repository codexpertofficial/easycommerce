<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Abstracts\Model;

/**
 * Class Attribute_Value
 * Handles attribute values operations.
 *
 * @package EasyCommerce\Model
 */
class Attribute_Value extends Model {

	use Cleaner;

	protected $table = 'attribute_values';

	/**
	 * Add an attribute value.
	 *
	 * @param int    $attribute_id
	 * @param string $name
	 * @param string $slug
	 * @return bool
	 */
	public function add( $attribute_id, $name, $value = null, $slug = null ) {

		if ( is_null( $slug ) ) {
			$slug = $this->sanitize( $name );
		}

		if ( is_null( $value ) ) {
			$value = $this->sanitize( $name );
		}

		$existing_attribute_value = $this->get_by_slug( $slug );

		if ( $existing_attribute_value && $existing_attribute_value->attribute_id == $attribute_id ) {
			return $existing_attribute_value->id;
		}

		$data = array(
			'attribute_id' => $attribute_id,
			'name'         => $name,
			'slug'         => $slug,
			'value'        => $value,
		);

		return $this->db->insert_row( $data );
	}

	/**
	 * Get an attribute value.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $id );
	}

	/**
	 * Get values by attribute ID
	 */
	public function get_by( $id, $key = 'attribute_id', $search = null ) {
		$args = array( $key => $id );

		if ( ! is_null( $search ) ) {
			$args['slug'] = array( 'like', '%' . $search . '%' );
		}

		return $this->db->get_rows( $args );
	}

	/**
	 * Get an attribute by slug.
	 *
	 * @param string $slug
	 * @return object|null
	 */
	public function get_by_slug( $slug ) {
		return $this->db->get_row( array( 'slug' => $slug ) );
	}

	/**
	 * Get all attribute values.
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->db->get_rows( array() );
	}

	/**
	 * Get all values for a specific attribute.
	 *
	 * @param int $attribute_id
	 * @return array
	 */
	public function get_by_attribute( $attribute_id ) {
		return $this->db->get_rows( array( 'attribute_id' => $attribute_id ) );
	}

	/**
	 * Update an attribute value.
	 *
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update( $attribute_value_id, $data ) {
		return $this->db->update_row( $attribute_value_id, $data );
	}

	/**
	 * Delete an attribute value.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete( $id ) {
		return $this->db->delete_row( $id );
	}
}

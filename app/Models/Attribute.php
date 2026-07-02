<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Abstracts\Model;

/**
 * Class Attribute
 * Handles product attributes operations.
 *
 * @package EasyCommerce\Model
 */
class Attribute extends Model {

	use Cleaner;

	protected $table = 'attributes';

	/**
	 * Add an attribute.
	 *
	 * @param string $name
	 * @param string $slug
	 * @return bool
	 */
	public function add( $name, $type = 'Text', $slug = null ) {

		if ( is_null( $slug ) ) {
			$slug = $this->sanitize( $name, 'title' );
		}

		$existing_attribute = $this->get_by_slug( $slug );
		if ( $existing_attribute ) {
			return $existing_attribute->id;
		}

		$data = array(
			'name'       => $name,
			'slug'       => $slug,
			'type'       => $type,
			'created_at' => current_time( 'mysql', 1 ),
		);

		return $this->db->insert_row( $data );
	}

	/**
	 * Get an attribute.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $id );
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
	 * Get all attributes.
	 *
	 * @return array
	 */
	public function get_all( $args = array(), $limit = 0, $offset = 0, $order = 'DESC', $orderby = 'id' ) {
		return $this->db->get_rows( $args, $limit, $offset, $order, $orderby );
	}

	public function count() {
		return count( $this->get_all() );
	}

	/**
	 * Update an attribute.
	 *
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update( $id, $data ) {
		return $this->db->update_row( $id, $data );
	}

	/**
	 * Delete an attribute.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete( $id ) {
		return $this->db->delete_row( $id );
	}

	/**
	 * Bulk delete attributes.
	 *
	 * @param array $ids
	 * @return bool
	 */
	public function bulk_delete( $ids ) {
		foreach ( $ids as $id ) {
			$this->delete( $id );
		}
		return true;
	}
}

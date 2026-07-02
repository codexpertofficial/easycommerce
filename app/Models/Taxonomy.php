<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Taxonomy
 * Handles product taxonomy operations.
 *
 * @package EasyCommerce\Model
 */
class Taxonomy {

	/**
	 * Lists terms and their children
	 *
	 * @param int    $parent_id The parent term ID
	 * @param string $taxonomy The taxonomy name
	 *
	 * @return array
	 */
	public function list_terms( $parent_id = 0, $taxonomy = 'product_cat', $limit = 10, $offset = 0 ) {
		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'parent'     => $parent_id,
			'number'     => $limit,
			'offset'     => $offset,
		);

		$terms      = get_terms( $args );
		$categories = array();

		foreach ( $terms as $term ) {
			$categories[] = array(
				'id'       => $term->term_id,
				'slug'     => $term->slug,
				'name'     => wp_specialchars_decode( $term->name ),
				'count'    => $term->count,
				'children' => $this->list_terms( $term->term_id, $taxonomy ),
			);
		}

		return $categories;
	}


	/**
	 * Adds a new term to the specified taxonomy
	 *
	 * @param string   $name The name of the term
	 * @param string   $slug The slug of the term (optional)
	 * @param string   $taxonomy The taxonomy name
	 * @param int|null $parent The parent term ID (optional)
	 *
	 * @return array
	 */
	public function add_term( $name, $taxonomy, $slug = null,  $parent = null ) {

		$term = wp_insert_term( $name, $taxonomy, [ 'parent' => $parent, 'slug' => $slug ] );

		if ( is_wp_error( $term ) ) {
			return $term;
		}

		return array(
			'id'   => $term['term_id'],
			'name' => $name,
		);
	}

	/**
	* Updates a term in the specified taxonomy
	* @param int      $term_id The ID of the term to update
	* @param string   $name The new name of the term ( optional )
	* @param string   $taxonomy The taxonomy name
	* @param int|null $parent The new parent term ID (optional)
	*
	* @return array
	*/
	public function update_term( $term_id, $name, $taxonomy, $slug = null, $parent = null ) {
		$args = [
			'name' 		=> $name,
			'slug' 		=> $slug,
			'parent' 	=> $parent,
		];
	
		$term = wp_update_term( $term_id, $taxonomy, $args );
	
		if ( is_wp_error( $term ) ) {
			return $term;
		}
	
		return [
			'id'   => $term['term_id'],
			'name' => $name,
		];
	}

	/**
	 * Deletes a term from the specified taxonomy
	 *
	 * @param int    $term_id  The ID of the term to delete
	 * @param string $taxonomy The taxonomy name
	 *
	 * @return bool True on success, false on failure
	 */
	public function delete_term( $term_id, $taxonomy ) {
		return wp_delete_term( $term_id, $taxonomy ) === true;
	}

	/**
	 * Bulk deletes multiple terms from the specified taxonomy
	 *
	 * @param array  $term_ids Array of term IDs to delete
	 * @param string $taxonomy The taxonomy name
	 *
	 * @return bool True on completion
	 */
	public function bulk_delete( $term_ids, $taxonomy ) {
		foreach ( $term_ids as $term_id ) {
			$this->delete_term( $term_id, $taxonomy );
		}
		
		return true;
	}
}

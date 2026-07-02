<?php
namespace EasyCommerce\Abstracts;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;
use EasyCommerce\Traits\Cleaner;

/**
 * Class Meta
 * Handles generic meta operations.
 */
abstract class Meta extends Database {

	use Cleaner;

	/**
	 * The key used to identify unique IDs in the meta table.
	 *
	 * @var string
	 */
	protected $unique_id_key;

	/**
	 * Constructor.
	 *
	 * @param string $table_name    The name of the table.
	 * @param string $unique_id_key The key used to identify unique IDs.
	 */
	public function __construct( $table_name, $unique_id_key ) {
		$this->unique_id_key = $unique_id_key;
		parent::__construct( $table_name );
	}

	/**
	 * Add meta data.
	 *
	 * @param int    $unique_id The unique ID to associate the meta data with.
	 * @param string $key       The meta key.
	 * @param mixed  $value     The meta value.
	 * @return int|null The last inserted ID or null on failure.
	 */
	public function add( $unique_id, $key, $value ) {
		/**
		 * Filters the meta data before it is added.
		 *
		 * @param array $meta_data The meta data to be added.
		 * @param int   $unique_id The unique ID.
		 * @param string $key      The meta key.
		 * @param mixed  $value    The meta value.
		 */
		$meta_data = apply_filters(
			'easycommerce_pre_add_meta',
			array(
				$this->unique_id_key => $unique_id,
				'meta_key'           => $key,
				'meta_value'         => maybe_serialize( $value ),
			),
			$unique_id,
			$key,
			$value
		);

		$result = $this->insert_row( $meta_data );

		/**
		 * Fires after meta data is added.
		 *
		 * @param int    $unique_id The unique ID.
		 * @param string $key       The meta key.
		 * @param mixed  $value     The meta value.
		 * @param int|null $result  The last inserted ID or null on failure.
		 */
		do_action( 'easycommerce_after_add_meta', $unique_id, $key, $value, $result );

		return $result;
	}

	/**
	 * Get meta data.
	 *
	 * @param int         $unique_id The unique ID to retrieve meta data for.
	 * @param string|null $key       The meta key to retrieve. If null, retrieves all meta data.
	 * @param bool        $single    Whether to return a single value or an array of values.
	 * @return mixed
	 */
	public function get( $unique_id, $key = null, $single = true ) {

		if ( $key ) {
			// If a specific key is provided, return either a single or multiple values.
			if ( $single ) {
				$result = $this->get_row(
					array(
						$this->unique_id_key => $unique_id,
						'meta_key'           => $key,
					)
				);

				$value = $result ? $this->unserialize( $result->meta_value ) : null;

				/**
				 * Filters the retrieved meta value.
				 *
				 * @since 1.9
				 * @param mixed  $value     The meta value.
				 * @param int    $unique_id The unique ID.
				 * @param string $key       The meta key.
				 */
				return apply_filters( 'easycommerce_get_meta', $value, $unique_id, $key );
			} else {
				$results = $this->get_rows(
					array(
						$this->unique_id_key => $unique_id,
						'meta_key'           => $key,
					)
				);

				$values = $results ? array_map(
					function ( $result ) {
						return $this->unserialize( $result->meta_value );
					},
					$results
				) : array();

				/**
				 * Filters the retrieved meta values.
				 *
				 * @since 1.9
				 * @param array  $values    The meta values.
				 * @param int    $unique_id The unique ID.
				 * @param string $key       The meta key.
				 */
				return apply_filters( 'easycommerce_get_metas', $values, $unique_id, $key );
			}
		} else {
			// If no key is provided, return all meta values for the unique ID.
			$results = $this->get_rows(
				array(
					$this->unique_id_key => $unique_id,
				)
			);

			$all_meta = $results ? array_merge(
				...array_map(
					function ( $result ) {
						return array( $result->meta_key => $this->unserialize( $result->meta_value ) );
					},
					$results
				)
			) : array();

			/**
			 * Filters all retrieved meta values for a unique ID.
			 *
			 * @since 1.9
			 * @param array $all_meta  The meta values.
			 * @param int   $unique_id The unique ID.
			 */
			return apply_filters( 'easycommerce_get_all_meta', $all_meta, $unique_id );
		}
	}

	/**
	 * Update meta data.
	 *
	 * @param int    $unique_id The unique ID to update meta data for.
	 * @param string $key       The meta key.
	 * @param mixed  $value     The meta value.
	 * @return bool|int If updating an existing entry, returns true on success, false on failure.
	 *                  If creating a new entry, returns the last inserted ID or null on failure.
	 */
	public function update( $unique_id, $key, $value ) {
		$entry = $this->get_row(
			array(
				$this->unique_id_key => $unique_id,
				'meta_key'           => $key,
			)
		);

		if ( $entry ) {
			/**
			 * Filters the meta data before it is updated.
			 *
			 * @param array $meta_data The meta data to be updated.
			 * @param int   $unique_id The unique ID.
			 * @param string $key      The meta key.
			 * @param mixed  $value    The meta value.
			 */
			$meta_data = apply_filters(
				'easycommerce_pre_update_meta',
				array( 'meta_value' => maybe_serialize( $value ) ),
				$unique_id,
				$key,
				$value
			);

			$result = parent::update_row( $entry->id, $meta_data );

			/**
			 * Fires after meta data is updated.
			 *
			 * @param int    $unique_id The unique ID.
			 * @param string $key       The meta key.
			 * @param mixed  $value     The meta value.
			 * @param bool   $result    Whether the update was successful.
			 */
			do_action( 'easycommerce_after_update_meta', $unique_id, $key, $value, $result );

			return $result;
		} else {
			return $this->add( $unique_id, $key, maybe_serialize( $value ) );
		}
	}

	/**
	 * Delete meta data.
	 *
	 * @param int    $unique_id The unique ID to delete meta data for.
	 * @param string $key       The meta key.
	 * @return bool
	 */
	public function delete( $unique_id, $key ) {
		$entry = $this->get_row(
			array(
				$this->unique_id_key => $unique_id,
				'meta_key'           => $key,
			)
		);

		if ( $entry ) {
			/**
			 * Fires before meta data is deleted.
			 *
			 * @param int    $unique_id The unique ID.
			 * @param string $key       The meta key.
			 */
			do_action( 'easycommerce_before_delete_meta', $unique_id, $key );

			$result = parent::delete_row( $entry->id );

			/**
			 * Fires after meta data is deleted.
			 *
			 * @param int    $unique_id The unique ID.
			 * @param string $key       The meta key.
			 * @param bool   $result    Whether the deletion was successful.
			 */
			do_action( 'easycommerce_after_delete_meta', $unique_id, $key, $result );

			return $result;
		} else {
			return false;
		}
	}
}

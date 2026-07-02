<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Model;

/**
 * Class Product_Variation_Download
 * Handles product variation downloads operations.
 *
 * @package EasyCommerce\Model
 */
class Product_Variation_Download extends Model {

	protected $table = 'product_variation_downloads';

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add variation download.
	 *
	 * @param int    $variation_id
	 * @param string $name
	 * @param string $filename
	 * @param int    $filesize
	 *
	 * @return bool
	 */
	public function add( $variation_id, $media_id, $name = '' ) {
		$data = array(
			'variation_id' => $variation_id,
			'media_id'     => $media_id,
			'name'         => $name,
		);

		return $this->db->insert_row( $data );
	}

	/**
	 * Get variation downloads.
	 *
	 * @param int $variation_id
	 * @return array
	 */
	public function get( $variation_id ) {
		$total = $this->db->get_count( array( 'variation_id' => $variation_id ) );

		$rows = $this->db->get_rows( array( 'variation_id' => $variation_id ) );

		$downloads = array_map(
			function ( $download ) {
				$file_path = get_attached_file( $download->media_id );

				$download->filename   = basename( $file_path );
				$download->url        = wp_get_attachment_url( $download->media_id );
				$download->secure_url = easycommerce_secure_download( $download->media_id );
				$download->type       = easycommerce_get_file_type( $file_path );
				$download->size       = file_exists( $file_path ) ? easycommerce_format_size( filesize( $file_path ) ) : 0;

				return $download;
			},
			$rows
		);

		return array(
			'downloads' => $downloads
		);
	}

	/**
	 * Update variation download.
	 *
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update( $id, $data ) {
		return $this->db->update_row( $id, $data );
	}

	/**
	 * Delete variation download.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete( $id ) {
		return $this->db->delete_row( $id );
	}
}

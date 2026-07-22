<?php
namespace EasyCommerce\Models;

defined('ABSPATH') || exit;

use EasyCommerce\Abstracts\User;
use EasyCommerce\Helpers\Utility;

/**
 * Concrete Notice Class
 */
class Notice {

	/**
	 * Get a list of notices, optionally filtered by type and screen.
	 *
	 * @param  string|null $type   Filter notices by type (e.g., 'error', 'success').
	 * @param  string|null $screen Filter notices by screen context (e.g., '#/orders').
	 * @param  int|null $limit Number of notices to return
	 * 
	 * @return array Array of notice arrays, each with structure:
	 * 	array(
	 * 		'id'			=> 'my-notice',
	 * 		'type'			=> 'error',
	 * 		'screen'		=> '#/orders',
	 * 		'title'			=> 'This is notice',
	 * 		'message'		=> 'Please notice this notice',
	 * 		'dismissible'	=> true,
	 * 		'button'		=> 'Click here',
	 * 		'url'			=> 'https://easycommerce.dev'
	 * 	)
	 */
	public static function list( $type = null, $screen = null, $limit = null ) {
		
		$dismissed	= get_option( 'easycommerce_dismissed_notices', [] );
		$notices	= array_diff_key( apply_filters( 'easycommerce_notices', [] ), array_flip( $dismissed ) );

		if( ! is_null( $type ) ) {
			$notices = array_filter( $notices, function ( $notice ) use ( $type ) {
				return isset( $notice['type'] ) && $notice['type'] === $type;
			} );
		}

		if( ! is_null( $screen ) ) {
			$notices = array_filter( $notices, function ( $notice ) use ( $screen ) {
				return isset( $notice['screen'] ) && $notice['screen'] === $screen;
			} );
		}

		$notices = is_null( $limit ) ? $notices : array_slice( array_values( $notices ), 0, $limit );

		return $notices;
	}

	/**
	* @param array Array of notice arrays, each with structure:
	* 	array(
	* 		'id'			=> 'my-notice',
	* 		'type'			=> 'error',
	* 		'screen'		=> '#/orders',
	* 		'title'			=> 'This is notice',
	* 		'message'		=> 'Please notice this notice',
	* 		'dismissible'	=> true,
	* 		'button'		=> 'Click here',
	* 		'url'			=> 'https://easycommerce.dev'
	* 	)														   
	*/
	public function add( $data ) {

		if( empty( $data['title'] ) || empty( $data['id'] ) ) {
			return;
		}

		$data = [
			'id'			=> $data['id'],
			'title'			=> $data['title'],
			'type'			=> $data['type'] ?? 'info',
			'screen'		=> $data['screen'] ?? null,
			'message'		=> $data['message'] ?? null,
			'button'		=> $data['button'] ?? null,
			'url'			=> $data['url'] ?? null,
			'target'		=> $data['target'] ?? null,
			'dismissible'	=> $data['dismissible'] ?? true,
		];

		add_filter( 'easycommerce_notices', function ( $notices ) use ( $data ) {
			$notices[ $data['id'] ] = $data;

			return $notices;
		} );
	}

	public function remove( $id ) {
		$dismissed = get_option( 'easycommerce_dismissed_notices', [] );
		$dismissed[] = $id;

		update_option( 'easycommerce_dismissed_notices', array_unique( $dismissed ) );

		return true;
	}
}
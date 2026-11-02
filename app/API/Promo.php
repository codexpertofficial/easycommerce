<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;

/**
 * The BFCM promotion's one write: remembering that the Pro page banner was dismissed.
 */
class Promo extends API {

	/**
	 * Hide the Pro page banner for seven days.
	 *
	 * The server stamps the time itself, so the browser cannot store a value or
	 * skew the clock. The option is site-wide and not autoloaded.
	 *
	 * @param \WP_REST_Request $request
	 */
	public function dismiss_banner( $request ) {
		update_option( 'easycommerce_promo_banner_dismissed_at', time(), false );

		$this->response_success( array( 'dismissed' => true ), 200 );
	}
}

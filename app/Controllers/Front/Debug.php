<?php
namespace EasyCommerce\Controllers\Front;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Coupon;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Product;
use EasyCommerce\Helpers\Email as Emailer;

/**
 * A test class to debug things
 *
 * @todo remove before release
 */
class Debug {

	use Hook;
	use Cache;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		if ( ! isset( $_GET['debug'] ) ) {
			return;
		}

		$this->action( 'init', array( $this, 'test' ) );
	}

	public function test() {
		Utility::pri( Utility::fuzzy_search( 'watarpruf' ) );
	}
}

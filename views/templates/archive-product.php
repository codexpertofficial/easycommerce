<?php
/**
 * Product archive template - renders through the shared storefront shell.
 */
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

echo Utility::get_template( 'templates/layout-header.php' );

do_action( 'easycommerce/views/templates/archive-product' );

echo Utility::get_template( 'templates/layout-footer.php' );

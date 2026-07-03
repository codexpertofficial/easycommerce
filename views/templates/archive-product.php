<?php
/**
 * Product archive template - renders through the shared storefront shell.
 */
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

echo Utility::get_template( 'templates/layout-header.php' );

$store_mode = Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test';

if ( $store_mode === 'test' && ! current_user_can( 'manage_options' ) ) {
	echo Utility::get_template( 'templates/store-mode.php' );
} else {
	do_action( 'easycommerce/views/templates/archive-product' );
}

echo Utility::get_template( 'templates/layout-footer.php' );

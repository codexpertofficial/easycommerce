<?php
/**
 * Single Product Template - renders through the shared storefront shell.
 */
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

echo Utility::get_template( 'templates/layout-header.php' );
?>
<div class="easycommerce-single-product-wrapper w-full overflow-hidden">
	<?php do_action( 'easycommerce/views/templates/single-product' ); ?>
</div>
<?php
echo Utility::get_template( 'templates/layout-footer.php' );

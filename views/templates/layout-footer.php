<?php
/**
 * Storefront shell - closing. Pair with layout-header.php.
 *
 * Hooks:
 * - action easycommerce_storefront_end   ( inside the container, after content )
 * - action easycommerce_after_storefront ( after the wrapper, before get_footer )
 */

defined( 'ABSPATH' ) || exit;
?>
		<?php do_action( 'easycommerce_storefront_end' ); ?>
	</div>
</div>
<?php
do_action( 'easycommerce_after_storefront' );

get_footer();

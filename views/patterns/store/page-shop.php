<?php
/**
 * Page pattern: Shop.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-shop',
	'title'       => __( 'Store shop', 'easycommerce' ),
	'description' => _x( 'A shop page with a full product grid and filters.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'shop', 'products', 'catalog' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-shop","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-shop" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Shop', 'easycommerce' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:easycommerce/template-2 {"align":"wide"} /--></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"easycommerce/store-trust-badges"} /-->

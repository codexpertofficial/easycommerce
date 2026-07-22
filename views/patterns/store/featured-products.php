<?php
/**
 * Section pattern: Featured products.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-featured-products',
	'title'       => __( 'Featured products', 'easycommerce' ),
	'description' => _x( 'A four-up featured product grid with a heading and a "View all" link.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'products', 'featured', 'grid' ),
);

$shop_url = easycommerce_shop_page( true );
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-featured","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-featured" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Featured products', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"featured","count":4,"columns":4,"align":"wide"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'View all', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

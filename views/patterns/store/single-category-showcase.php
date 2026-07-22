<?php
/**
 * Section pattern: Single category showcase.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-single-category-showcase',
	'title'       => __( 'Single category showcase', 'easycommerce' ),
	'description' => _x( 'A heading and a product grid filtered to one category. Set the category on the Product Collection block.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'products', 'category', 'showcase' ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-category-showcase","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-category-showcase" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Shop the collection', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"all","count":8,"columns":4,"align":"wide"} /--></div>
<!-- /wp:group -->

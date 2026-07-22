<?php
/**
 * Section pattern: Product categories.
 *
 * Renders up to four category cards. Cards are static so users can point each
 * one at their own category page and swap the image.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-product-categories',
	'title'       => __( 'Product categories', 'easycommerce' ),
	'description' => _x( 'A row of category cards with images and links.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'categories', 'cards' ),
);

$shop_url = easycommerce_shop_page( true );

$cards = array(
	array( __( 'New in', 'easycommerce' ), easycommerce_pattern_image( 'category-1.jpg' ) ),
	array( __( 'Best sellers', 'easycommerce' ), easycommerce_pattern_image( 'category-2.jpg' ) ),
	array( __( 'On sale', 'easycommerce' ), easycommerce_pattern_image( 'category-3.jpg' ) ),
	array( __( 'All products', 'easycommerce' ), easycommerce_pattern_image( 'category-4.jpg' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-categories","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-categories" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Shop by category', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><?php foreach ( $cards as $card ) : $label = $card[0]; $image = $card[1]; ?>
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"<?php echo esc_url( $image ); ?>","dimRatio":30,"minHeight":220,"style":{"border":{"radius":"10px"}},"className":"ec-pattern"} -->
<div class="wp-block-cover ec-pattern" style="border-radius:10px;min-height:220px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $image ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":3,"textColor":"white"} -->
<h3 class="wp-block-heading has-text-align-center has-white-color has-text-color"><?php echo esc_html( $label ); ?></h3>
<!-- /wp:heading -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->
<?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

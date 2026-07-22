<?php
/**
 * Section pattern: Promo banner.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-promo-banner',
	'title'       => __( 'Promo banner', 'easycommerce' ),
	'description' => _x( 'A full-width promotional banner with a heading and CTA.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'promo', 'banner', 'sale', 'cta' ),
);

$shop_url = easycommerce_shop_page( true );
$promo    = easycommerce_pattern_image( 'promo.jpg' );
?>
<!-- wp:cover {"url":"<?php echo esc_url( $promo ); ?>","dimRatio":50,"minHeight":320,"align":"full","className":"ec-pattern ec-pattern-promo","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"backgroundColor":"black"} -->
<div class="wp-block-cover alignfull ec-pattern ec-pattern-promo" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);min-height:320px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $promo ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-50 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","textColor":"white","style":{"typography":{"fontSize":"36px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:36px;font-weight:700"><?php echo esc_html__( 'Season sale — up to 40% off', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color"><?php echo esc_html__( 'Limited time only. Grab your favourites before they are gone.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop the sale', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->

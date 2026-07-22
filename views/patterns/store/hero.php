<?php
/**
 * Section pattern: Hero with CTA.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-hero',
	'title'       => __( 'Hero with CTA', 'easycommerce' ),
	'description' => _x( 'A full-width hero banner with heading, sub-copy and a call-to-action button.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'hero', 'banner', 'cta' ),
);

$shop_url = easycommerce_shop_page( true );
?>
<!-- wp:cover {"dimRatio":40,"minHeight":480,"align":"full","className":"ec-pattern ec-pattern-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} -->
<div class="wp-block-cover alignfull ec-pattern ec-pattern-hero" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);min-height:480px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"48px","fontWeight":"700"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:48px;font-weight:700"><?php echo esc_html__( 'Everything your store needs, in one place', 'easycommerce' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white","style":{"typography":{"fontSize":"18px"}}} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:18px"><?php echo esc_html__( 'Shop curated products with fast checkout and secure payments.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->

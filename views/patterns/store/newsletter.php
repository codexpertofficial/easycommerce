<?php
/**
 * Section pattern: Newsletter signup.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-newsletter',
	'title'       => __( 'Newsletter signup', 'easycommerce' ),
	'description' => _x( 'A heading and an email capture prompt.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'newsletter', 'email', 'subscribe' ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-newsletter","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained","contentSize":"620px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-newsletter has-base-color has-contrast-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Join our newsletter', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php echo esc_html__( 'Get new arrivals and exclusive offers straight to your inbox.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#"><?php echo esc_html__( 'Subscribe', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

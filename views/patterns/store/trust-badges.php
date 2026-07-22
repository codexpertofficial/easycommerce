<?php
/**
 * Section pattern: Trust badges.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-trust-badges',
	'title'       => __( 'Trust badges', 'easycommerce' ),
	'description' => _x( 'A row of reassurance badges: shipping, returns, secure payment and support.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'trust', 'badges', 'shipping', 'returns', 'support' ),
);

$badges = array(
	array( '🚚', __( 'Free shipping', 'easycommerce' ), __( 'On orders over $50', 'easycommerce' ) ),
	array( '↩️', __( 'Easy returns', 'easycommerce' ), __( '30-day money back', 'easycommerce' ) ),
	array( '🔒', __( 'Secure payment', 'easycommerce' ), __( 'Encrypted checkout', 'easycommerce' ) ),
	array( '💬', __( '24/7 support', 'easycommerce' ), __( 'We are here to help', 'easycommerce' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-trust","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-trust" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><?php foreach ( $badges as $badge ) : ?>
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"32px"}}} -->
<p class="has-text-align-center" style="font-size:32px"><?php echo esc_html( $badge[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":4} -->
<h4 class="wp-block-heading has-text-align-center"><?php echo esc_html( $badge[1] ); ?></h4>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php echo esc_html( $badge[2] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->
<?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

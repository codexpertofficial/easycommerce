<?php
/**
 * Section pattern: Testimonials.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-testimonials',
	'title'       => __( 'Testimonials', 'easycommerce' ),
	'description' => _x( 'Three customer quote cards.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'testimonials', 'reviews', 'quotes' ),
);

$quotes = array(
	array( __( 'Fast shipping and the quality is exactly as described. Will buy again.', 'easycommerce' ), __( 'Alex M.', 'easycommerce' ) ),
	array( __( 'Checkout was effortless and support answered within minutes.', 'easycommerce' ), __( 'Priya S.', 'easycommerce' ) ),
	array( __( 'My go-to store now. Great prices and everything arrives on time.', 'easycommerce' ), __( 'Jordan T.', 'easycommerce' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-testimonials","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-testimonials" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'What our customers say', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><?php foreach ( $quotes as $quote ) : ?>
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"border":{"radius":"10px","width":"1px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="border-width:1px;border-radius:10px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30)"><!-- wp:paragraph -->
<p><?php echo esc_html( $quote[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}}} -->
<p style="font-weight:600"><?php echo esc_html( $quote[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->
<?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

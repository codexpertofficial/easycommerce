<?php
/**
 * Section pattern: FAQ.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-faq',
	'title'       => __( 'FAQ', 'easycommerce' ),
	'description' => _x( 'A list of frequently asked questions using accordion (details) blocks.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'faq', 'accordion', 'questions' ),
);

$faqs = array(
	array( __( 'How long does shipping take?', 'easycommerce' ), __( 'Most orders ship within 1–2 business days and arrive within a week.', 'easycommerce' ) ),
	array( __( 'What is your return policy?', 'easycommerce' ), __( 'You can return any item within 30 days for a full refund.', 'easycommerce' ) ),
	array( __( 'Which payment methods do you accept?', 'easycommerce' ), __( 'We accept all major cards and secure online payment methods.', 'easycommerce' ) ),
	array( __( 'Do you ship internationally?', 'easycommerce' ), __( 'Yes, we ship to most countries. Rates are shown at checkout.', 'easycommerce' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-faq","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-faq" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Frequently asked questions', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->
<?php foreach ( $faqs as $faq ) : ?>
<!-- wp:details {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"bottom":{"width":"1px"}}}} -->
<details class="wp-block-details" style="border-bottom-width:1px;padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)"><summary><?php echo esc_html( $faq[0] ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $faq[1] ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
<?php endforeach; ?></div>
<!-- /wp:group -->

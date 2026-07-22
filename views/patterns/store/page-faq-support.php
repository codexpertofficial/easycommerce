<?php
/**
 * Page pattern: FAQ / Support.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-faq-support',
	'title'       => __( 'Store FAQ / Support', 'easycommerce' ),
	'description' => _x( 'A support page combining an FAQ accordion with a newsletter prompt.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'faq', 'support', 'help' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-faq-support","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-faq-support" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30)"><!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center"><?php echo esc_html__( 'Help & support', 'easycommerce' ); ?></h1>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"easycommerce/store-faq"} /-->

<!-- wp:pattern {"slug":"easycommerce/store-newsletter"} /-->

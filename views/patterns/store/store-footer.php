<?php
/**
 * Section pattern: Store footer.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-footer',
	'title'       => __( 'Store footer', 'easycommerce' ),
	'description' => _x( 'A footer with link columns and a copyright line.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'keywords'    => array( 'footer', 'links', 'copyright' ),
);

$shop_url      = easycommerce_shop_page( true );
$dashboard_url = easycommerce_dashboard_page( true );
$year          = gmdate( 'Y' );
$site_name     = get_bloginfo( 'name' );
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-footer has-base-color has-contrast-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading"><?php echo esc_html( $site_name ); ?></h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'Quality products, fast delivery and friendly support.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading"><?php echo esc_html__( 'Shop', 'easycommerce' ); ?></h4>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'All products', 'easycommerce' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $dashboard_url ); ?>"><?php echo esc_html__( 'My account', 'easycommerce' ); ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading"><?php echo esc_html__( 'Help', 'easycommerce' ); ?></h4>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><?php echo esc_html__( 'Shipping & returns', 'easycommerce' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php echo esc_html__( 'Contact us', 'easycommerce' ); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"}}} -->
<p class="has-text-align-center" style="font-size:14px"><?php
	/* translators: 1: year, 2: site name. */
	echo esc_html( sprintf( __( '© %1$s %2$s. All rights reserved.', 'easycommerce' ), $year, $site_name ) );
?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<?php
/**
 * Page pattern: Grocery store home — catalog / department-store layout.
 *
 * Boxed, dense, category-forward and LEFT-aligned. Every section shares one
 * discipline: an `alignfull` group with a `constrained` 1200px content column
 * and matching horizontal padding, and NO `alignwide` children — so every
 * heading, grid and row lines up on the same left gutter at every width.
 * Structurally distinct from the other homes: utility strip, split
 * (text-left / image-right) hero, prominent category tiles, row-header product
 * grids, a horizontal promo strip and an inline trust bar. Natively editable.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-homepage-grocery',
	'title'       => __( 'Grocery Store Home', 'easycommerce' ),
	'description' => _x( 'Boxed, category-led catalog homepage.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);

$shop_url = easycommerce_shop_page( true );
$hero     = easycommerce_pattern_image( 'grocery-hero.jpg' );
$promo    = easycommerce_pattern_image( 'grocery-promo.jpg' );

$cats = array(
	array( __( 'New In', 'easycommerce' ), easycommerce_pattern_image( 'grocery-category-1.jpg' ) ),
	array( __( 'Best Sellers', 'easycommerce' ), easycommerce_pattern_image( 'grocery-category-2.jpg' ) ),
	array( __( 'On Sale', 'easycommerce' ), easycommerce_pattern_image( 'grocery-category-3.jpg' ) ),
	array( __( 'All Products', 'easycommerce' ), easycommerce_pattern_image( 'grocery-category-4.jpg' ) ),
);

$trust = array(
	array( __( 'Free Shipping', 'easycommerce' ), __( 'On orders over $50', 'easycommerce' ) ),
	array( __( '30-Day Returns', 'easycommerce' ), __( 'No questions asked', 'easycommerce' ) ),
	array( __( 'Secure Checkout', 'easycommerce' ), __( 'Encrypted payments', 'easycommerce' ) ),
	array( __( 'Friendly Support', 'easycommerce' ), __( 'Here to help, 7 days', 'easycommerce' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-utility","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"base","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-utility has-base-background-color has-background" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"13px","letterSpacing":"0.04em"}}} -->
<p class="has-text-align-center" style="font-size:13px;letter-spacing:0.04em"><?php echo esc_html__( 'Free shipping over $50   ·   30-day returns   ·   Secure checkout', 'easycommerce' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-hero-split","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-hero-split" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%"><!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"44px","fontWeight":"700","lineHeight":"1.1"}}} -->
<h1 class="wp-block-heading" style="font-size:44px;font-weight:700;line-height:1.1"><?php echo esc_html__( 'Everything for the Season', 'easycommerce' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"17px"},"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|30"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--30);font-size:17px"><?php echo esc_html__( 'Thousands of quality products across every category, shipped fast and backed by an easy returns promise.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop the catalog', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%"><!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"4px"}}} -->
<figure class="wp-block-image size-large" style="border-radius:4px"><img src="<?php echo esc_url( $hero ); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-categories","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-categories" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"22px"},"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--30);font-size:22px"><?php echo esc_html__( 'Shop by Category', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><?php foreach ( $cats as $c ) : ?>
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"<?php echo esc_url( $c[1] ); ?>","dimRatio":30,"minHeight":200,"className":"ec-pattern","style":{"border":{"radius":"4px"}}} -->
<div class="wp-block-cover ec-pattern" style="border-radius:4px;min-height:200px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $c[1] ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":3,"textColor":"white","style":{"typography":{"fontSize":"24px"}}} -->
<h3 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:24px"><?php echo esc_html( $c[0] ); ?></h3>
<!-- /wp:heading --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->
<?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-featured","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-featured" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}},"layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--30)"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"22px"}}} -->
<h2 class="wp-block-heading" style="font-size:22px"><?php echo esc_html__( 'Featured Products', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"}}} -->
<p style="font-size:14px"><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'View all →', 'easycommerce' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:easycommerce/product-collection {"source":"featured","count":3,"columns":3} /--></div>
<!-- /wp:group -->

<!-- wp:cover {"url":"<?php echo esc_url( $promo ); ?>","dimRatio":60,"minHeight":240,"align":"full","className":"ec-pattern ec-pattern-promo","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-cover alignfull ec-pattern ec-pattern-promo" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40);min-height:240px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $promo ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-60 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap","verticalAlignment":"center"}} -->
<div class="wp-block-group"><!-- wp:heading {"textColor":"white","style":{"typography":{"fontSize":"28px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-white-color has-text-color" style="font-size:28px;font-weight:700"><?php echo esc_html__( 'Season Sale — Up to 40% Off', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop the sale', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-best-sellers","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-best-sellers" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"22px"},"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--30);font-size:22px"><?php echo esc_html__( 'Best Sellers', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"best-selling","count":3,"columns":3} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-trust","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-trust" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns -->
<div class="wp-block-columns"><?php foreach ( $trust as $t ) : ?>
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":4,"style":{"typography":{"fontSize":"15px","fontWeight":"700"}}} -->
<h4 class="wp-block-heading" style="font-size:15px;font-weight:700"><?php echo esc_html( $t[0] ); ?></h4>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px"}}} -->
<p style="font-size:13px"><?php echo esc_html( $t[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->
<?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

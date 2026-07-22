<?php
/**
 * Page pattern: Fashion store home — bold youth / streetwear layout.
 *
 * Loud and typographic: a rounded warm-grey hero card with a chunky uppercase
 * headline (yellow-highlighted keyword) and a pill CTA, a saturated yellow
 * utility strip, three editorial category cards, a full-width yellow payday
 * sale band, two product rows and a yellow community/newsletter band. All
 * yellow is inline (#f2d22e) so the design's black accent keeps buttons and
 * card CTAs legible; the chunky uppercase heading language comes from the
 * design tokens. Natively editable.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-homepage-fashion',
	'title'       => __( 'Fashion Store Home', 'easycommerce' ),
	'description' => _x( 'Bold, yellow-and-black streetwear homepage.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);

$shop_url = easycommerce_shop_page( true );
$hero     = easycommerce_pattern_image( 'fashion-hero.jpg' );
$sale     = easycommerce_pattern_image( 'fashion-sale.jpg' );

$cats = array(
	array( __( 'Hoodies & Sweatshirts', 'easycommerce' ), easycommerce_pattern_image( 'fashion-category-1.jpg' ) ),
	array( __( 'Coats & Parkas', 'easycommerce' ), easycommerce_pattern_image( 'fashion-category-2.jpg' ) ),
	array( __( 'Tees & T-Shirts', 'easycommerce' ), easycommerce_pattern_image( 'fashion-category-3.jpg' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-hero" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)"><!-- wp:group {"style":{"border":{"radius":"24px"},"color":{"background":"#f4f1ec"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-radius:24px;background-color:#f4f1ec;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%"><!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"56px","fontWeight":"800","lineHeight":"1.05","textTransform":"uppercase","letterSpacing":"0.01em"}}} -->
<h1 class="wp-block-heading" style="font-size:56px;font-weight:800;letter-spacing:0.01em;line-height:1.05;text-transform:uppercase"><?php echo wp_kses( sprintf( /* translators: %s: highlighted word. */ __( 'Let’s Explore %s Clothes.', 'easycommerce' ), '<mark style="background-color:#f2d22e" class="has-inline-color">' . esc_html__( 'Unique', 'easycommerce' ) . '</mark>' ), array( 'mark' => array( 'style' => array(), 'class' => array() ) ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"16px"},"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|30"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--30);font-size:16px"><?php echo esc_html__( 'Live for influential and innovative fashion.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%"><!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"16px"}}} -->
<figure class="wp-block-image size-large has-custom-border"><img src="<?php echo esc_url( $hero ); ?>" alt="" style="border-radius:16px"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-utility","style":{"color":{"background":"#f2d22e"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-utility has-background" style="background-color:#f2d22e;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px","fontWeight":"700","letterSpacing":"0.12em","textTransform":"uppercase"},"color":{"text":"#141414"}}} -->
<p class="has-text-align-center has-text-color" style="color:#141414;font-size:14px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase"><?php echo esc_html__( 'Free shipping over $50 · New drops weekly · Easy 30-day returns', 'easycommerce' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-categories","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-categories" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"28px","fontWeight":"800","textTransform":"uppercase"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;text-transform:uppercase"><?php echo esc_html__( 'New Arrivals', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|30"}}}} -->
<div class="wp-block-columns"><?php foreach ( $cats as $cat ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"12px"}}} -->
<figure class="wp-block-image size-large has-custom-border"><img src="<?php echo esc_url( $cat[1] ); ?>" alt="" style="border-radius:12px"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"18px","fontWeight":"700"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
<h3 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--20);font-size:18px;font-weight:700"><?php echo esc_html( $cat[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","fontWeight":"600","letterSpacing":"0.06em","textTransform":"uppercase"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--10);font-size:13px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase"><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Explore now →', 'easycommerce' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-promo","style":{"color":{"background":"#f2d22e"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-promo has-background" style="background-color:#f2d22e;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%"><!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"16px"}}} -->
<figure class="wp-block-image size-large has-custom-border"><img src="<?php echo esc_url( $sale ); ?>" alt="" style="border-radius:16px"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%"><!-- wp:heading {"style":{"typography":{"fontSize":"48px","fontWeight":"800","lineHeight":"1.05","textTransform":"uppercase"},"color":{"text":"#141414"}}} -->
<h2 class="wp-block-heading has-text-color" style="color:#141414;font-size:48px;font-weight:800;line-height:1.05;text-transform:uppercase"><?php echo esc_html__( 'Payday Sale Now', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"17px"},"color":{"text":"#141414"},"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|10"}}}} -->
<p class="has-text-color" style="color:#141414;margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--10);font-size:17px"><?php echo esc_html__( 'Spend a minimum of $100 and get a 30% off voucher for your next purchase.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px"},"color":{"text":"#141414"},"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-color" style="color:#141414;margin-bottom:var(--wp--preset--spacing--30);font-size:13px"><?php echo esc_html__( 'Limited time only. *Terms & conditions apply.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-best-sellers","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-best-sellers" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"28px","fontWeight":"800","textTransform":"uppercase"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;text-transform:uppercase"><?php echo esc_html__( 'Young’s Favourite', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"best-selling","count":4,"columns":4} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-new-arrivals","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-new-arrivals" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"28px","fontWeight":"800","textTransform":"uppercase"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;text-transform:uppercase"><?php echo esc_html__( 'Just Dropped', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"newest","count":4,"columns":4} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-newsletter","style":{"color":{"background":"#f2d22e"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-newsletter has-background" style="background-color:#f2d22e;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"30px","fontWeight":"800","textTransform":"uppercase","lineHeight":"1.2"},"color":{"text":"#141414"}}} -->
<h2 class="wp-block-heading has-text-align-center has-text-color" style="color:#141414;font-size:30px;font-weight:800;line-height:1.2;text-transform:uppercase"><?php echo esc_html__( 'Join the Community, Get Monthly Promos', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px"},"color":{"text":"#141414"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center has-text-color" style="color:#141414;margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:15px"><?php echo esc_html__( 'Sign up and be the first to hear about drops, deals and events.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Sign me up', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

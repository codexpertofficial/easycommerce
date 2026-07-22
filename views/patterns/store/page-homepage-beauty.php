<?php
/**
 * Page pattern: Beauty store home — contemporary DTC layout.
 *
 * Airy and rounded, built on contained feature cards rather than harsh full-
 * bleed bands. A cover hero with a bottom-left, width-capped headline; a clean
 * 3-up featured row; a single calm image/copy spotlight; a rounded dark promo
 * card and a rounded light newsletter card (both gutter-safe); a 3-up new-
 * arrivals row; and one restrained centered testimonial. Natively editable.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-homepage-beauty',
	'title'       => __( 'Beauty Store Home', 'easycommerce' ),
	'description' => _x( 'Airy, rounded, feature-card DTC homepage.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);

$shop_url = easycommerce_shop_page( true );
$hero     = easycommerce_pattern_image( 'beauty-hero.jpg' );
$spot     = easycommerce_pattern_image( 'beauty-spotlight.jpg' );
$promo    = easycommerce_pattern_image( 'beauty-promo.jpg' );
?>
<!-- wp:cover {"url":"<?php echo esc_url( $hero ); ?>","dimRatio":45,"minHeight":600,"align":"full","className":"ec-pattern ec-pattern-hero","contentPosition":"bottom center","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} -->
<div class="wp-block-cover alignfull ec-pattern ec-pattern-hero has-custom-content-position is-position-bottom-center" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50);min-height:600px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $hero ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-45 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"62%"} -->
<div class="wp-block-column" style="flex-basis:62%"><!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"54px","fontWeight":"800","lineHeight":"1.03","letterSpacing":"-0.01em"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-white-color has-text-color" style="font-size:54px;font-weight:800;line-height:1.03;letter-spacing:-0.01em"><?php echo esc_html__( 'Designed to Stand Out', 'easycommerce' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"white","style":{"typography":{"fontSize":"19px"},"spacing":{"margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|40"}}}} -->
<p class="has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--30);margin-bottom:var(--wp--preset--spacing--40);font-size:19px"><?php echo esc_html__( 'A bold storefront built around your best products.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"spacing":{"padding":{"left":"32px","right":"32px","top":"15px","bottom":"15px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>" style="padding-top:15px;padding-right:32px;padding-bottom:15px;padding-left:32px"><?php echo esc_html__( 'Explore the collection', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"38%"} -->
<div class="wp-block-column" style="flex-basis:38%"></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-featured","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-featured" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"34px","fontWeight":"800"},"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--50);font-size:34px;font-weight:800"><?php echo esc_html__( 'This Season’s Edit', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"featured","count":3,"columns":3} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-spotlight","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-spotlight" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo esc_url( $spot ); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","letterSpacing":"0.12em","textTransform":"uppercase"}}} -->
<p style="font-size:13px;letter-spacing:0.12em;text-transform:uppercase"><?php echo esc_html__( 'Spotlight', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"36px","fontWeight":"800","lineHeight":"1.1"}}} -->
<h2 class="wp-block-heading" style="font-size:36px;font-weight:800;line-height:1.1"><?php echo esc_html__( 'The Piece Everyone’s Asking About', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"17px"},"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|30"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--30);font-size:17px"><?php echo esc_html__( 'Put your hero product front and centre — swap this image and copy for the thing you most want customers to see first.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop the spotlight', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-promo","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-promo" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)"><!-- wp:group {"backgroundColor":"contrast","textColor":"white","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-white-color has-contrast-background-color has-text-color has-background" style="border-radius:16px;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"center","textColor":"white","style":{"typography":{"fontSize":"40px","fontWeight":"800"}}} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:40px;font-weight:800"><?php echo esc_html__( 'Up to 40% Off', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white","style":{"typography":{"fontSize":"17px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:17px"><?php echo esc_html__( 'The season sale is on. Limited stock — don’t wait.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop the sale', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-new-arrivals","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-new-arrivals" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"34px","fontWeight":"800"},"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--50);font-size:34px;font-weight:800"><?php echo esc_html__( 'Just Landed', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"newest","count":3,"columns":3} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-testimonials","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-testimonials" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"26px","fontWeight":"600","lineHeight":"1.35"}}} -->
<p class="has-text-align-center" style="font-size:26px;font-weight:600;line-height:1.35"><?php echo esc_html__( '“Easily the smoothest online shop experience I’ve had this year — fast, clear, and beautiful.”', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px","letterSpacing":"0.06em","textTransform":"uppercase"},"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center" style="margin-top:var(--wp--preset--spacing--30);font-size:14px;letter-spacing:0.06em;text-transform:uppercase"><?php echo esc_html__( 'Jamie R. — verified buyer', 'easycommerce' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-newsletter","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1180px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-newsletter" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)"><!-- wp:cover {"url":"<?php echo esc_url( $promo ); ?>","dimRatio":70,"minHeight":320,"className":"ec-newsletter-cover","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover ec-newsletter-cover" style="border-radius:16px;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50);min-height:320px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $promo ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-70 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","textColor":"white","style":{"typography":{"fontSize":"32px","fontWeight":"800"}}} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:32px;font-weight:800"><?php echo esc_html__( 'Get First Access', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white","style":{"typography":{"fontSize":"17px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:17px"><?php echo esc_html__( 'New drops, restocks and members-only offers — straight to your inbox.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Sign me up', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->

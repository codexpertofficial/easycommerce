<?php
/**
 * Page pattern: Gadget store home — dark tech / electronics layout.
 *
 * Deal-led and mosaic-driven: a two-up dark hero with a three-tile promo row
 * beneath it, rounded category tiles, a best-sellers grid on a cool grey band,
 * a full-width dark promo cover, a flash-deals split (image + tight 2-up grid)
 * and a new-arrivals row closed by a single testimonial. Every banner shares
 * one ink-navy overlay so the page reads as a tech storefront even with
 * neutral placeholder imagery. Natively editable.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-homepage-gadget',
	'title'       => __( 'Gadget Store Home', 'easycommerce' ),
	'description' => _x( 'Dark, deal-led electronics homepage with a banner mosaic.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);

$shop_url = easycommerce_shop_page( true );
$hero_a   = easycommerce_pattern_image( 'gadget-hero-1.jpg' );
$hero_b   = easycommerce_pattern_image( 'gadget-hero-2.jpg' );
$promo    = easycommerce_pattern_image( 'gadget-promo.jpg' );
$flash    = easycommerce_pattern_image( 'gadget-flash.jpg' );

$cats = array(
	array( __( 'New Tech', 'easycommerce' ), easycommerce_pattern_image( 'gadget-category-1.jpg' ) ),
	array( __( 'Top Rated', 'easycommerce' ), easycommerce_pattern_image( 'gadget-category-2.jpg' ) ),
	array( __( 'Deals', 'easycommerce' ), easycommerce_pattern_image( 'gadget-category-3.jpg' ) ),
	array( __( 'All Gear', 'easycommerce' ), easycommerce_pattern_image( 'gadget-category-4.jpg' ) ),
);

$tiles = array(
	array( __( 'Best Deals', 'easycommerce' ), __( 'Sleek Ultra-Thin Laptops', 'easycommerce' ), easycommerce_pattern_image( 'gadget-tile-1.jpg' ) ),
	array( __( 'Grab 50% Off', 'easycommerce' ), __( 'True Wireless Earbuds', 'easycommerce' ), easycommerce_pattern_image( 'gadget-tile-2.jpg' ) ),
	array( __( 'New Arrival', 'easycommerce' ), __( 'Smartwatch Series', 'easycommerce' ), easycommerce_pattern_image( 'gadget-tile-3.jpg' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|20","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-hero" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:cover {"url":"<?php echo esc_url( $hero_a ); ?>","dimRatio":70,"customOverlayColor":"#0b1120","minHeight":420,"contentPosition":"center left","style":{"border":{"radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} -->
<div class="wp-block-cover has-custom-content-position is-position-center-left" style="border-radius:10px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:420px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $hero_a ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-70 has-background-dim" style="background-color:#0b1120"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontSize":"12px","letterSpacing":"0.1em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#facc15"}}} -->
<p class="has-text-color" style="color:#facc15;font-size:12px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase"><?php echo esc_html__( 'Starting at only $99', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textColor":"white","style":{"typography":{"fontSize":"34px","fontWeight":"700","lineHeight":"1.15"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);font-size:34px;font-weight:700;line-height:1.15"><?php echo esc_html__( 'Smart Home Essentials Bundle', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"white","style":{"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:15px"><?php echo esc_html__( 'Seamless connectivity, premium sound and effortless charging.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Explore now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:cover {"url":"<?php echo esc_url( $hero_b ); ?>","dimRatio":70,"customOverlayColor":"#0b1120","minHeight":420,"contentPosition":"center left","style":{"border":{"radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} -->
<div class="wp-block-cover has-custom-content-position is-position-center-left" style="border-radius:10px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:420px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $hero_b ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-70 has-background-dim" style="background-color:#0b1120"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontSize":"12px","letterSpacing":"0.1em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#facc15"}}} -->
<p class="has-text-color" style="color:#facc15;font-size:12px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase"><?php echo esc_html__( 'This week only', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textColor":"white","style":{"typography":{"fontSize":"34px","fontWeight":"700","lineHeight":"1.15"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);font-size:34px;font-weight:700;line-height:1.15"><?php echo esc_html__( 'Wireless Headphones', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"white","style":{"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:15px"><?php echo esc_html__( 'Stay connected with Bluetooth technology and crystal-clear sound.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Explore now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"},"margin":{"top":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--20)"><?php foreach ( $tiles as $tile ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"<?php echo esc_url( $tile[2] ); ?>","dimRatio":70,"customOverlayColor":"#0b1120","minHeight":220,"contentPosition":"bottom left","style":{"border":{"radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}}} -->
<div class="wp-block-cover has-custom-content-position is-position-bottom-left" style="border-radius:10px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30);min-height:220px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $tile[2] ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-70 has-background-dim" style="background-color:#0b1120"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.1em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#facc15"}}} -->
<p class="has-text-color" style="color:#facc15;font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase"><?php echo esc_html( $tile[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"textColor":"white","style":{"typography":{"fontSize":"20px","fontWeight":"700","lineHeight":"1.2"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h3 class="wp-block-heading has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);font-size:20px;font-weight:700;line-height:1.2"><?php echo esc_html( $tile[1] ); ?></h3>
<!-- /wp:heading --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-categories","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-categories" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"26px","fontWeight":"700"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--40);font-size:26px;font-weight:700"><?php echo esc_html__( 'Featured Categories', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><?php foreach ( $cats as $cat ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"<?php echo esc_url( $cat[1] ); ?>","dimRatio":40,"customOverlayColor":"#0b1120","minHeight":200,"contentPosition":"bottom left","style":{"border":{"radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}}} -->
<div class="wp-block-cover has-custom-content-position is-position-bottom-left" style="border-radius:10px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20);min-height:200px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $cat[1] ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#0b1120"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":3,"textColor":"white","style":{"typography":{"fontSize":"18px","fontWeight":"700"}}} -->
<h3 class="wp-block-heading has-white-color has-text-color" style="font-size:18px;font-weight:700"><?php echo esc_html( $cat[0] ); ?></h3>
<!-- /wp:heading --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-best-sellers","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-best-sellers" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"26px","fontWeight":"700"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--40);font-size:26px;font-weight:700"><?php echo esc_html__( 'Most Viewed Products', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"best-selling","count":4,"columns":4} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-promo","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-promo" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:cover {"url":"<?php echo esc_url( $promo ); ?>","dimRatio":80,"customOverlayColor":"#0b1120","minHeight":300,"style":{"border":{"radius":"10px"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover" style="border-radius:10px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:300px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $promo ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-80 has-background-dim" style="background-color:#0b1120"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"12px","letterSpacing":"0.1em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#facc15"}}} -->
<p class="has-text-align-center has-text-color" style="color:#facc15;font-size:12px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase"><?php echo esc_html__( 'Limited time', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","textColor":"white","style":{"typography":{"fontSize":"36px","fontWeight":"700"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);font-size:36px;font-weight:700"><?php echo esc_html__( 'Power Up Your Setup', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white","style":{"typography":{"fontSize":"16px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:16px"><?php echo esc_html__( 'Chargers, speakers, headphones and accessories — the time for a super setup is now.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop the sale', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-flash","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-flash" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"verticalAlignment":"top","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns are-vertically-aligned-top"><!-- wp:column {"verticalAlignment":"top","width":"42%"} -->
<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:42%"><!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"10px"}}} -->
<figure class="wp-block-image size-large has-custom-border"><img src="<?php echo esc_url( $flash ); ?>" alt="" style="border-radius:10px"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top","width":"58%"} -->
<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:58%"><!-- wp:heading {"style":{"typography":{"fontSize":"26px","fontWeight":"700"},"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--30);font-size:26px;font-weight:700"><?php echo esc_html__( 'Flash Deals', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"featured","count":2,"columns":2} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-new-arrivals","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-new-arrivals" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"26px","fontWeight":"700"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--40);font-size:26px;font-weight:700"><?php echo esc_html__( 'Just Landed', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"newest","count":4,"columns":4} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-testimonials","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-testimonials" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"24px","fontWeight":"600","lineHeight":"1.4"}}} -->
<p class="has-text-align-center" style="font-size:24px;font-weight:600;line-height:1.4"><?php echo esc_html__( '“Unbeatable quality and service. The sound is top-notch and support was exceptional — highly recommended for tech enthusiasts.”', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"13px","letterSpacing":"0.08em","textTransform":"uppercase"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
<p class="has-text-align-center" style="margin-top:var(--wp--preset--spacing--20);font-size:13px;letter-spacing:0.08em;text-transform:uppercase"><?php echo esc_html__( 'Emily J. — verified buyer', 'easycommerce' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

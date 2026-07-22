<?php
/**
 * Page pattern: Boutique store home — ink-and-amber storefront layout.
 *
 * Built from the "Threadline" design handoff: an ink-gradient hero with two
 * stacked feature cards, a hairline trust strip, category tiles under a mono
 * kicker + "view all" header, a deal-of-the-day gradient band with (static)
 * countdown tiles, two product rows, a three-tile gradient promo mosaic,
 * testimonial cards, latest posts and a soft newsletter band. Amber (#f5a623)
 * is inline on dark surfaces so the design's ink accent keeps default buttons
 * and card CTAs legible; mono kickers and the tight-tracked 800 headings come
 * from the design tokens. Natively editable.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-homepage-boutique',
	'title'       => __( 'Boutique Store Home', 'easycommerce' ),
	'description' => _x( 'Ink-and-amber storefront homepage with a deal band.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);

$shop_url = easycommerce_shop_page( true );
$hero     = easycommerce_pattern_image( 'boutique-hero.jpg' );

$cats = array(
	array( __( 'Dresses', 'easycommerce' ), easycommerce_pattern_image( 'boutique-category-1.jpg' ) ),
	array( __( 'Denim', 'easycommerce' ), easycommerce_pattern_image( 'boutique-category-2.jpg' ) ),
	array( __( 'Knitwear', 'easycommerce' ), easycommerce_pattern_image( 'boutique-category-3.jpg' ) ),
	array( __( 'Accessories', 'easycommerce' ), easycommerce_pattern_image( 'boutique-category-4.jpg' ) ),
);

$trust = array(
	array( '⚑', __( 'Free shipping', 'easycommerce' ), __( 'On orders over $99', 'easycommerce' ) ),
	array( '↺', __( '30-day returns', 'easycommerce' ), __( 'No questions asked', 'easycommerce' ) ),
	array( '⛨', __( 'Secure checkout', 'easycommerce' ), __( '256-bit encryption', 'easycommerce' ) ),
	array( '☎', __( '24/7 support', 'easycommerce' ), __( 'Real humans, always', 'easycommerce' ) ),
);

$countdown = array(
	array( '02', __( 'Days', 'easycommerce' ) ),
	array( '08', __( 'Hours', 'easycommerce' ) ),
	array( '41', __( 'Mins', 'easycommerce' ) ),
	array( '00', __( 'Secs', 'easycommerce' ) ),
);

$mosaic = array(
	array( __( 'Layer up', 'easycommerce' ), __( 'Coats & Outerwear', 'easycommerce' ), 'linear-gradient(120deg,#101820 0%,#1c2b3a 100%)' ),
	array( __( 'Everyday', 'easycommerce' ), __( 'Essentials for Every Day', 'easycommerce' ), 'linear-gradient(120deg,#1f2a37 0%,#2c3d4d 100%)' ),
	array( __( 'Finishing touch', 'easycommerce' ), __( 'Bags & Accessories', 'easycommerce' ), 'linear-gradient(120deg,#16202a 0%,#243b4f 100%)' ),
);

$quotes = array(
	array( __( '“The quality genuinely surprised me — the fit is perfect and it washes beautifully. My whole autumn order was spot on.”', 'easycommerce' ), __( 'Emily Johnson', 'easycommerce' ) ),
	array( __( '“Fast shipping, beautiful packaging, and the jeans fit exactly like the size guide promised. Rare these days.”', 'easycommerce' ), __( 'Marcus Reid', 'easycommerce' ) ),
	array( __( '“Customer service replied within minutes and sorted my exchange the same day. I shop here first now.”', 'easycommerce' ), __( 'Sofia Alvarez', 'easycommerce' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|20","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-hero" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|30"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"61%"} -->
<div class="wp-block-column" style="flex-basis:61%"><!-- wp:cover {"url":"<?php echo esc_url( $hero ); ?>","dimRatio":80,"customGradient":"linear-gradient(120deg,#101820 0%,#1c2b3a 60%,#243b4f 100%)","minHeight":420,"contentPosition":"center left","style":{"border":{"radius":"20px"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} -->
<div class="wp-block-cover has-custom-content-position is-position-center-left" style="border-radius:20px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:420px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $hero ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-80 has-background-dim has-background-gradient" style="background:linear-gradient(120deg,#101820 0%,#1c2b3a 60%,#243b4f 100%)"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( 'New · Up to 40% off', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"textColor":"white","style":{"typography":{"fontSize":"44px","fontWeight":"800","lineHeight":"1.05","letterSpacing":"-0.02em"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
<h1 class="wp-block-heading has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--20);font-size:44px;font-weight:800;letter-spacing:-0.02em;line-height:1.05"><?php echo esc_html__( 'Style That Moves With You.', 'easycommerce' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"15px","lineHeight":"1.55"},"color":{"text":"#aeb8c2"},"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-color" style="color:#aeb8c2;margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--30);font-size:15px;line-height:1.55"><?php echo esc_html__( 'Elevated everyday pieces in responsible fabrics — made to wear on repeat, season after season.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"color":{"background":"#f5a623","text":"#101820"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="<?php echo esc_url( $shop_url ); ?>" style="color:#101820;background-color:#f5a623"><?php echo esc_html__( 'Shop the collection →', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"39%"} -->
<div class="wp-block-column" style="flex-basis:39%"><!-- wp:group {"style":{"border":{"radius":"20px"},"color":{"gradient":"linear-gradient(120deg,#1f2a37 0%,#2c3d4d 100%)"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-radius:20px;background:linear-gradient(120deg,#1f2a37 0%,#2c3d4d 100%);padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( 'New arrival', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"textColor":"white","style":{"typography":{"fontSize":"23px","fontWeight":"800"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h3 class="wp-block-heading has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);font-size:23px;font-weight:800"><?php echo esc_html__( 'The Autumn Edit', 'easycommerce' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"},"color":{"text":"#aeb8c2"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<p class="has-text-color" style="color:#aeb8c2;margin-top:var(--wp--preset--spacing--10);font-size:14px"><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'From $49 · Explore →', 'easycommerce' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"border":{"radius":"20px"},"color":{"background":"#f4f5f6"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"margin":{"top":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-radius:20px;background-color:#f4f5f6;margin-top:var(--wp--preset--spacing--30);padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#e4572e"}}} -->
<p class="has-text-color" style="color:#e4572e;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( 'Grab 50% off', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"23px","fontWeight":"800"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h3 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);font-size:23px;font-weight:800"><?php echo esc_html__( 'Denim Drop', 'easycommerce' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"},"color":{"text":"#737d87"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<p class="has-text-color" style="color:#737d87;margin-top:var(--wp--preset--spacing--10);font-size:14px"><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Limited stock · Explore →', 'easycommerce' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-trust","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-trust" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><?php foreach ( $trust as $item ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"16px","width":"1px","color":"#eef0f2"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:#eef0f2;border-width:1px;border-radius:16px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"24px"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:24px"><?php echo esc_html( $item[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":4,"style":{"typography":{"fontSize":"14px","fontWeight":"700"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h4 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);font-size:14px;font-weight:700"><?php echo esc_html( $item[1] ); ?></h4>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"12px"},"color":{"text":"#737d87"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<p class="has-text-color" style="color:#737d87;margin-top:var(--wp--preset--spacing--10);font-size:12px"><?php echo esc_html( $item[2] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-categories","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-categories" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( 'Browse the racks', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"28px","fontWeight":"800","letterSpacing":"-0.02em"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;letter-spacing:-0.02em"><?php echo esc_html__( 'Shop by Category', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><?php foreach ( $cats as $cat ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"16px","width":"1px","color":"#eef0f2"},"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|30","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:#eef0f2;border-width:1px;border-radius:16px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--20)"><!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"12px"}}} -->
<figure class="wp-block-image size-large has-custom-border"><img src="<?php echo esc_url( $cat[1] ); ?>" alt="" style="border-radius:12px"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"14px","fontWeight":"700"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
<h3 class="wp-block-heading has-text-align-center" style="margin-top:var(--wp--preset--spacing--20);font-size:14px;font-weight:700"><?php echo esc_html( $cat[0] ); ?></h3>
<!-- /wp:heading --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-deal","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-deal" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:group {"style":{"border":{"radius":"20px"},"color":{"gradient":"linear-gradient(120deg,#101820 0%,#22323f 100%)"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-radius:20px;background:linear-gradient(120deg,#101820 0%,#22323f 100%);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( '⚡ Deal of the day', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textColor":"white","style":{"typography":{"fontSize":"32px","fontWeight":"800","lineHeight":"1.1","letterSpacing":"-0.02em"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);font-size:32px;font-weight:800;letter-spacing:-0.02em;line-height:1.1"><?php echo esc_html__( 'The Knitwear Edit — 45% Off Today Only', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"15px"},"color":{"text":"#aeb8c2"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-color" style="color:#aeb8c2;margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:15px"><?php echo esc_html__( 'Every sweater, cardigan and knit dress — one day, one price drop.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"color":{"background":"#f5a623","text":"#101820"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="<?php echo esc_url( $shop_url ); ?>" style="color:#101820;background-color:#f5a623"><?php echo esc_html__( 'Grab the deal →', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|10"}}}} -->
<div class="wp-block-columns"><?php foreach ( $countdown as $tile ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"12px"},"color":{"background":"#2a3844"},"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|10","right":"var:preset|spacing|10"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-radius:12px;background-color:#2a3844;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--10);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--10)"><!-- wp:paragraph {"align":"center","textColor":"white","style":{"typography":{"fontSize":"30px","fontWeight":"800"}}} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:30px;font-weight:800"><?php echo esc_html( $tile[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase"},"color":{"text":"#aeb8c2"}}} -->
<p class="has-text-align-center has-text-color" style="color:#aeb8c2;font-size:11px;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html( $tile[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-best-sellers","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-best-sellers" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( 'Customer favourites', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"28px","fontWeight":"800","letterSpacing":"-0.02em"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;letter-spacing:-0.02em"><?php echo esc_html__( 'Most Popular', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"best-selling","count":8,"columns":4} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-mosaic","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-mosaic" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><?php foreach ( $mosaic as $tile ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"18px"},"color":{"gradient":"<?php echo esc_attr( $tile[2] ); ?>"},"dimensions":{"minHeight":"240px"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"flex","orientation":"vertical","verticalAlignment":"bottom"}} -->
<div class="wp-block-group has-background" style="border-radius:18px;background:<?php echo esc_attr( $tile[2] ); ?>;min-height:240px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html( $tile[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"textColor":"white","style":{"typography":{"fontSize":"24px","fontWeight":"800"}}} -->
<h3 class="wp-block-heading has-white-color has-text-color" style="font-size:24px;font-weight:800"><?php echo esc_html( $tile[1] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontWeight":"600"},"color":{"text":"#c8ced5"}}} -->
<p class="has-text-color" style="color:#c8ced5;font-size:14px;font-weight:600"><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Explore now →', 'easycommerce' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-featured","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-featured" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( 'Tried and loved', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"28px","fontWeight":"800","letterSpacing":"-0.02em"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;letter-spacing:-0.02em"><?php echo esc_html__( 'Best Sellers', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"featured","count":3,"columns":3} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-testimonials","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-testimonials" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"28px","fontWeight":"800","letterSpacing":"-0.02em"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;letter-spacing:-0.02em"><?php echo esc_html__( 'Loved by Thousands', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><?php foreach ( $quotes as $quote ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"16px","width":"1px","color":"#eef0f2"},"color":{"background":"#fbfbfc"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-border-color has-background" style="border-color:#eef0f2;border-width:1px;border-radius:16px;background-color:#fbfbfc;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--30)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:14px"><?php echo esc_html__( '★★★★★', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","lineHeight":"1.6"},"color":{"text":"#3a444e"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
<p class="has-text-color" style="color:#3a444e;margin-top:var(--wp--preset--spacing--20);font-size:14px;line-height:1.6"><?php echo esc_html( $quote[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontWeight":"700"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--20);font-size:14px;font-weight:700"><?php echo esc_html( $quote[1] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"12px"},"color":{"text":"#737d87"}}} -->
<p class="has-text-color" style="color:#737d87;font-size:12px"><?php echo esc_html__( 'Verified buyer', 'easycommerce' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-posts","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-posts" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"11px","letterSpacing":"0.14em","textTransform":"uppercase","fontWeight":"700"},"color":{"text":"#f5a623"}}} -->
<p class="has-text-color" style="color:#f5a623;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php echo esc_html__( 'From the journal', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"28px","fontWeight":"800","letterSpacing":"-0.02em"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--40);font-size:28px;font-weight:800;letter-spacing:-0.02em"><?php echo esc_html__( 'Latest Posts', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:latest-posts {"postsToShow":3,"displayPostContent":true,"displayPostContentRadio":"excerpt","excerptLength":18,"displayPostDate":true,"postLayout":"grid","columns":3,"displayFeaturedImage":true,"featuredImageSizeSlug":"large","className":"ec-latest-posts"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-newsletter","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-newsletter" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:group {"style":{"border":{"radius":"20px"},"color":{"background":"#f4f5f6"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-radius:20px;background-color:#f4f5f6;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"28px","fontWeight":"800","letterSpacing":"-0.02em"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:28px;font-weight:800;letter-spacing:-0.02em"><?php echo esc_html__( 'Get $20 Off Your First Order', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px"},"color":{"text":"#737d87"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center has-text-color" style="color:#737d87;margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:15px"><?php echo esc_html__( 'Join the list for new drops, restocks and subscriber-only offers.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Subscribe', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

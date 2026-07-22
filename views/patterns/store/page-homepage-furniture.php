<?php
/**
 * Page pattern: Furniture store home — warm banner-led showroom layout.
 *
 * Bright and banner-driven: a tall light hero with two banner columns under
 * it, an 8-up new-arrivals grid, a pair of wide room-deal covers, a trending
 * row, a "deal of the day" split, three testimonial cards, a first-order
 * newsletter cover and a closing trust bar. Room banners share one warm dark
 * overlay; everything else stays airy so the page reads as a showroom even
 * with neutral placeholder imagery. Natively editable.
 *
 * @var array $pattern_meta Populated for the registrar.
 */

defined( 'ABSPATH' ) || exit;

$pattern_meta = array(
	'slug'        => 'easycommerce/store-page-homepage-furniture',
	'title'       => __( 'Furniture Store Home', 'easycommerce' ),
	'description' => _x( 'Warm, banner-led showroom homepage.', 'Block pattern description', 'easycommerce' ),
	'categories'  => array( 'easycommerce' ),
	'blockTypes'  => array( 'core/post-content' ),
	'postTypes'   => array( 'page' ),
);

$shop_url = easycommerce_shop_page( true );
$hero     = easycommerce_pattern_image( 'furniture-hero.jpg' );
$deal     = easycommerce_pattern_image( 'furniture-deal.jpg' );
$promo    = easycommerce_pattern_image( 'furniture-promo.jpg' );

$banners = array(
	array( __( 'Modern Furniture', 'easycommerce' ), __( 'Start from $40.45', 'easycommerce' ), easycommerce_pattern_image( 'furniture-banner-1.jpg' ) ),
	array( __( 'New Lighting', 'easycommerce' ), __( 'Fresh looks for every room', 'easycommerce' ), easycommerce_pattern_image( 'furniture-banner-2.jpg' ) ),
);

$rooms = array(
	array( __( 'Bed Room', 'easycommerce' ), easycommerce_pattern_image( 'furniture-room-1.jpg' ) ),
	array( __( 'Dining Deals', 'easycommerce' ), easycommerce_pattern_image( 'furniture-room-2.jpg' ) ),
);

$quotes = array(
	array( __( '“Beautiful pieces and the delivery was faster than promised. Our living room finally feels finished.”', 'easycommerce' ), __( 'Jonathon D.', 'easycommerce' ) ),
	array( __( '“The quality for the price is unreal. The armchair looks even better in person.”', 'easycommerce' ), __( 'Priya S.', 'easycommerce' ) ),
	array( __( '“Easy ordering, careful packaging, zero scratches. Will absolutely shop here again.”', 'easycommerce' ), __( 'Marcus T.', 'easycommerce' ) ),
);

$trust = array(
	array( __( 'Worldwide Delivery', 'easycommerce' ), __( 'Fast and tracked to your door', 'easycommerce' ) ),
	array( __( 'Safe Payment', 'easycommerce' ), __( 'Encrypted, secure checkout', 'easycommerce' ) ),
	array( __( 'Shop With Confidence', 'easycommerce' ), __( '30-day easy returns', 'easycommerce' ) ),
	array( __( '24/7 Support', 'easycommerce' ), __( 'Real people, here to help', 'easycommerce' ) ),
);
?>
<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|20","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-hero" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--40)"><!-- wp:cover {"url":"<?php echo esc_url( $hero ); ?>","dimRatio":50,"customOverlayColor":"#ffffff","isDark":false,"minHeight":480,"style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover is-light" style="border-radius:6px;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50);min-height:480px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $hero ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-50 has-background-dim" style="background-color:#ffffff"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","letterSpacing":"0.28em","textTransform":"uppercase"}}} -->
<p class="has-text-align-center" style="font-size:15px;letter-spacing:0.28em;text-transform:uppercase"><?php echo esc_html__( 'Spring', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"52px","fontWeight":"700","lineHeight":"1.05","letterSpacing":"0.04em","textTransform":"uppercase"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:52px;font-weight:700;letter-spacing:0.04em;line-height:1.05;text-transform:uppercase"><?php echo esc_html__( 'Collection', 'easycommerce' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:16px"><?php echo esc_html__( 'Start from $40.45', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"},"margin":{"top":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--20)"><?php foreach ( $banners as $banner ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"<?php echo esc_url( $banner[2] ); ?>","dimRatio":40,"customOverlayColor":"#ffffff","isDark":false,"minHeight":240,"contentPosition":"center right","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}}} -->
<div class="wp-block-cover is-light has-custom-content-position is-position-center-right" style="border-radius:6px;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30);min-height:240px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $banner[2] ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#ffffff"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"right","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
<h3 class="wp-block-heading has-text-align-right" style="font-size:24px;font-weight:700"><?php echo esc_html( $banner[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"right","style":{"typography":{"fontSize":"14px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|20"}}}} -->
<p class="has-text-align-right" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--20);font-size:14px"><?php echo esc_html( $banner[1] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"typography":{"fontSize":"13px"},"spacing":{"padding":{"top":"9px","bottom":"9px","left":"20px","right":"20px"}}}} -->
<div class="wp-block-button has-custom-font-size" style="font-size:13px"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>" style="padding-top:9px;padding-right:20px;padding-bottom:9px;padding-left:20px"><?php echo esc_html__( 'Shop now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-new-arrivals","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-new-arrivals" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"30px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:30px;font-weight:700"><?php echo esc_html__( 'New Arrivals', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|40"}}}} -->
<p class="has-text-align-center" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--40);font-size:15px"><?php echo esc_html__( 'Fresh pieces for every corner of your home.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:easycommerce/product-collection {"source":"newest","count":8,"columns":4} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-rooms","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-rooms" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><?php foreach ( $rooms as $room ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"<?php echo esc_url( $room[1] ); ?>","dimRatio":50,"customOverlayColor":"#2b2119","minHeight":260,"style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover" style="border-radius:6px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40);min-height:260px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $room[1] ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-50 has-background-dim" style="background-color:#2b2119"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":3,"textColor":"white","style":{"typography":{"fontSize":"30px","fontWeight":"700","letterSpacing":"0.06em","textTransform":"uppercase"}}} -->
<h3 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:30px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase"><?php echo esc_html( $room[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white","style":{"typography":{"fontSize":"14px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|20"}}}} -->
<p class="has-text-align-center has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--20);font-size:14px"><?php echo esc_html__( 'Up to 20% off all furniture in store', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"typography":{"fontSize":"13px"},"spacing":{"padding":{"top":"9px","bottom":"9px","left":"20px","right":"20px"}}}} -->
<div class="wp-block-button has-custom-font-size" style="font-size:13px"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>" style="padding-top:9px;padding-right:20px;padding-bottom:9px;padding-left:20px"><?php echo esc_html__( 'Shop now', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-featured","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-featured" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"style":{"typography":{"fontSize":"26px","fontWeight":"700"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--40);font-size:26px;font-weight:700"><?php echo esc_html__( 'Trending Items', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:easycommerce/product-collection {"source":"featured","count":4,"columns":4} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-deal","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-deal" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%"><!-- wp:group {"style":{"border":{"radius":"6px"},"color":{"background":"#f7f6f3"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<div class="wp-block-group has-background" style="border-radius:6px;background-color:#f7f6f3;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","letterSpacing":"0.1em","textTransform":"uppercase","fontWeight":"600"}}} -->
<p style="font-size:13px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase"><?php echo esc_html__( 'Deal of the day', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"34px","fontWeight":"700","lineHeight":"1.1"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<h3 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--10);font-size:34px;font-weight:700;line-height:1.1"><?php echo esc_html__( 'Ends Tonight — Extra 15% Off', 'easycommerce' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:15px"><?php echo esc_html__( 'One standout piece at its lowest price of the season. When it’s gone, it’s gone.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Shop the deal', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%"><!-- wp:cover {"url":"<?php echo esc_url( $deal ); ?>","dimRatio":30,"customOverlayColor":"#ffffff","isDark":false,"minHeight":300,"contentPosition":"top center","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}}} -->
<div class="wp-block-cover is-light has-custom-content-position is-position-top-center" style="border-radius:6px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--30);min-height:300px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $deal ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#ffffff"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"12px","letterSpacing":"0.1em","textTransform":"uppercase"}}} -->
<p class="has-text-align-center" style="font-size:12px;letter-spacing:0.1em;text-transform:uppercase"><?php echo esc_html__( 'New arrivals', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"24px","fontWeight":"700"}}} -->
<h3 class="wp-block-heading has-text-align-center" style="font-size:24px;font-weight:700"><?php echo esc_html__( 'Office Tables', 'easycommerce' ); ?></h3>
<!-- /wp:heading --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-testimonials","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-testimonials" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"26px","fontWeight":"700"},"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--40);font-size:26px;font-weight:700"><?php echo esc_html__( 'What Our Customers Say', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|20"}}}} -->
<div class="wp-block-columns"><?php foreach ( $quotes as $quote ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"6px","width":"1px","color":"#efece6"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:#efece6;border-width:1px;border-radius:6px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--30)"><!-- wp:paragraph {"style":{"typography":{"fontSize":"15px","lineHeight":"1.6"}}} -->
<p style="font-size:15px;line-height:1.6"><?php echo esc_html( $quote[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","fontWeight":"600","letterSpacing":"0.06em","textTransform":"uppercase"},"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--20);font-size:13px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase"><?php echo esc_html( $quote[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-newsletter","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-newsletter" style="padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)"><!-- wp:cover {"url":"<?php echo esc_url( $promo ); ?>","dimRatio":60,"customOverlayColor":"#2b2119","minHeight":300,"style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover" style="border-radius:6px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:300px"><img class="wp-block-cover__image-background" src="<?php echo esc_url( $promo ); ?>" data-object-fit="cover" alt=""/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-60 has-background-dim" style="background-color:#2b2119"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","textColor":"white","style":{"typography":{"fontSize":"32px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:32px;font-weight:700"><?php echo esc_html__( 'Get $20 Off Your First Order', 'easycommerce' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white","style":{"typography":{"fontSize":"16px"},"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|30"}}}} -->
<p class="has-text-align-center has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--10);margin-bottom:var(--wp--preset--spacing--30);font-size:16px"><?php echo esc_html__( 'Join our list for early access to sales, new collections and members-only offers.', 'easycommerce' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Subscribe', 'easycommerce' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"ec-pattern ec-pattern-trust","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull ec-pattern ec-pattern-trust" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|30"}}}} -->
<div class="wp-block-columns"><?php foreach ( $trust as $item ) : ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"textAlign":"center","level":4,"style":{"typography":{"fontSize":"16px","fontWeight":"700"}}} -->
<h4 class="wp-block-heading has-text-align-center" style="font-size:16px;font-weight:700"><?php echo esc_html( $item[0] ); ?></h4>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"13px"},"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} -->
<p class="has-text-align-center" style="margin-top:var(--wp--preset--spacing--10);font-size:13px"><?php echo esc_html( $item[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

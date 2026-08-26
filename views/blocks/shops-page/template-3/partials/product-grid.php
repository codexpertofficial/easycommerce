<?php
/**
 * Product grid view partial for Shop Template 3.
 *
 * Displays products in a grid layout with styling for Template 3.
 *
 * @var array $args Template arguments.
 * @var array $args['products'] Array of formatted product data.
 * @var array $args['settings'] Block settings for styling.
 */
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product as Product_Model;

$full_star                  		  = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/full-start-template-3.png';
$half_star                  		  = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/half-start-template-3.png';
$empty_star                 		  = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/empty-start-template-3.png';
$products                   		  = $args['products'];
$settings                   		  = $args['settings'];
$category_color             		  = isset( $settings['categoryColor'] ) ? $settings['categoryColor'] : 'var(--color-ec-secondary)';
$category_fontsize          		  = isset( $settings['categoryFontSize'] ) ? intval( $settings['categoryFontSize'] ) : 12;
$category_fontweight        		  = isset( $settings['categoryFontWeight'] ) ? $settings['categoryFontWeight'] : '500';
$category_text_transform    		  = isset( $settings['categoryTextTransform'] ) ? $settings['categoryTextTransform'] : 'none';
$category_text_style        		  = isset( $settings['categoryStyle'] ) ? $settings['categoryStyle'] : 'none';
$category_decoration        		  = isset( $settings['categoryDecoration'] ) ? $settings['categoryDecoration'] : 'none';
$category_line_height       		  = isset( $settings['categoryLineHeight'] ) ? intval( $settings['categoryLineHeight'] ) : 20;
$category_letter_spacing    		  = isset( $settings['categorySpacing'] ) ? intval( $settings['categorySpacing'] ) : 0;
$title_color                		  = isset( $settings['titleColor'] ) ? $settings['titleColor'] : '#121216';
$title_hover_color                	  = isset( $settings['titleHoverColor'] ) ? $settings['titleHoverColor'] : 'var(--color-ec-primary)';
$title_fontsize             		  = isset( $settings['titleFontSize'] ) ? intval( $settings['titleFontSize'] ) : 20;
$title_fontweight           		  = isset( $settings['titleFontWeight'] ) ? $settings['titleFontWeight'] : '600';
$title_text_transform       		  = isset( $settings['titleTextTransform'] ) ? $settings['titleTextTransform'] : 'none';
$title_text_style       		  	  = isset( $settings['titleTextStyle'] ) ? $settings['titleTextStyle'] : 'none';
$title_decoration           		  = isset( $settings['titleDecoration'] ) ? $settings['titleDecoration'] : 'none';
$title_line_height          		  = isset( $settings['titleLineHeight'] ) ? intval( $settings['titleLineHeight'] ) : 20;
$title_letter_spacing       		  = isset( $settings['titleSpacing'] ) ? intval( $settings['titleSpacing'] ) : 0;
$rating_color               		  = isset( $settings['ratingColor'] ) ? $settings['ratingColor'] : 'var(--color-ec-body)';
$star_size                  		  = isset( $settings['starSize'] ) ? intval( $settings['starSize'] ) : 13;
$rating_font_size           		  = isset( $settings['ratingFontSize'] ) ? intval( $settings['ratingFontSize'] ) : 16;
$rating_font_weight         		  = isset( $settings['ratingFontWeight'] ) ? $settings['ratingFontWeight'] : '500';
$rating_text_transform      		  = isset( $settings['ratingTextTransform'] ) ? $settings['ratingTextTransform'] : 'none';
$rating_text_style          		  = isset( $settings['ratingStyle'] ) ? $settings['ratingStyle'] : 'none';
$rating_decoration          		  = isset( $settings['ratingDecoration'] ) ? $settings['ratingDecoration'] : 'none';
$rating_line_height         		  = isset( $settings['ratingLineHeight'] ) ? intval( $settings['ratingLineHeight'] ) : 20;
$rating_letter_spacing      		  = isset( $settings['ratingSpacing'] ) ? intval( $settings['ratingSpacing'] ) : 0;
$price_color                		  = isset( $settings['priceColor'] ) ? $settings['priceColor'] : 'var(--color-ec-body)';
$price_font_size            		  = isset( $settings['priceFontSize'] ) ? intval( $settings['priceFontSize'] ) : 16;
$price_font_weight          		  = isset( $settings['priceFontWeight'] ) ? $settings['priceFontWeight'] : '500';
$price_text_transform       		  = isset( $settings['priceTextTransform'] ) ? $settings['priceTextTransform'] : 'none';
$price_style	   		  	          = isset( $settings['priceStyle'] ) ? $settings['priceStyle'] : 'none';
$price_decoration           		  = isset( $settings['priceDecoration'] ) ? $settings['priceDecoration'] : 'none';
$price_line_height          		  = isset( $settings['priceLineHeight'] ) ? intval( $settings['priceLineHeight'] ) : 20;
$price_letter_spacing       		  = isset( $settings['priceSpacing'] ) ? intval( $settings['priceSpacing'] ) : 0;
$sale_price_color                	  = isset( $settings['salePriceColor'] ) ? $settings['salePriceColor'] : 'var(--color-ec-body)';
$sale_price_font_size            	  = isset( $settings['salePriceFontSize'] ) ? intval( $settings['salePriceFontSize'] ) : 16;
$sale_price_font_weight          	  = isset( $settings['salePriceFontWeight'] ) ? $settings['salePriceFontWeight'] : '500';
$sale_price_text_transform       	  = isset( $settings['salePriceTextTransform'] ) ? $settings['salePriceTextTransform'] : 'none';
$sale_price_style       		  	  = isset( $settings['salePriceStyle'] ) ? $settings['salePriceStyle'] : 'none';
$sale_price_decoration           	  = isset( $settings['salePriceDecoration'] ) ? $settings['salePriceDecoration'] : 'line-through';
$sale_price_line_height          	  = isset( $settings['salePriceLineHeight'] ) ? intval( $settings['salePriceLineHeight'] ) : 20;
$sale_price_letter_spacing       	  = isset( $settings['salePriceSpacing'] ) ? intval( $settings['salePriceSpacing'] ) : 0;
$cart_button_color          		  = isset( $settings['cartButtonColor'] ) ? $settings['cartButtonColor'] : 'var(--color-ec-body)';
$cart_button_bg_Color       		  = isset( $settings['cartButtonBgColor'] ) ? $settings['cartButtonBgColor'] : '#F8F8F8';
$cart_button_fontsize       		  = isset( $settings['cartButtonFontSize'] ) ? intval( $settings['cartButtonFontSize'] ) : 16;
$cart_button_fontweight     		  = isset( $settings['cartButtonFontWeight'] ) ? $settings['cartButtonFontWeight'] : '500';
$cart_button_text_transform 		  = isset( $settings['cartButtonTextTransform'] ) ? $settings['cartButtonTextTransform'] : 'none';
$cart_button_style       		  	  = isset( $settings['cartButtonStyle'] ) ? $settings['cartButtonStyle'] : 'none';	
$cart_button_decoration     		  = isset( $settings['cartButtonDecoration'] ) ? $settings['cartButtonDecoration'] : 'none';
$cart_button_line_height    		  = isset( $settings['cartButtonLineHeight'] ) ? intval( $settings['cartButtonLineHeight'] ) : 20;
$cart_button_letter_spacing 		  = isset( $settings['cartButtonSpacing'] ) ? intval( $settings['cartButtonSpacing'] ) : 0;
$cart_button_hover_color          	  = isset( $settings['cartButtonHoverColor'] ) ? $settings['cartButtonHoverColor'] : $cart_button_color;
$cart_button_hover_bg_Color       	  = isset( $settings['cartButtonHoverBgColor'] ) ? $settings['cartButtonHoverBgColor'] : $cart_button_bg_Color;
$cart_button_hover_fontsize       	  = isset( $settings['cartButtonHoverFontSize'] ) ? intval( $settings['cartButtonHoverFontSize'] ) : 16;
$cart_button_hover_fontweight     	  = isset( $settings['cartButtonHoverFontWeight'] ) ? $settings['cartButtonHoverFontWeight'] : '500';
$cart_button_hover_text_transform 	  = isset( $settings['cartButtonHoverTextTransform'] ) ? $settings['cartButtonHoverTextTransform'] : 'none';
$cart_button_hover_style       	  	  = isset( $settings['cartButtonHoverStyle'] ) ? $settings['cartButtonHoverStyle'] : 'none';
$cart_button_hover_decoration     	  = isset( $settings['cartButtonHoverDecoration'] ) ? $settings['cartButtonHoverDecoration'] : 'none';
$cart_button_hover_line_height    	  = isset( $settings['cartButtonHoverLineHeight'] ) ? intval( $settings['cartButtonHoverLineHeight'] ) : 20;
$cart_button_hover_letter_spacing 	  = isset( $settings['cartButtonHoverSpacing'] ) ? intval( $settings['cartButtonHoverSpacing'] ) : 0;
$cart_button_focus_color         	  = isset( $settings['cartButtonFocusColor'] ) ? $settings['cartButtonFocusColor'] : '#ffffff';
$cart_button_focus_bg_Color      	  = isset( $settings['cartButtonFocusBgColor'] ) ? $settings['cartButtonFocusBgColor'] : 'var(--color-ec-primary)';
$checkout_button_color          	  = isset( $settings['checkoutButtonColor'] ) ? $settings['checkoutButtonColor'] : 'var(--color-ec-body)';
$checkout_button_bg_Color       	  = isset( $settings['checkoutButtonBgColor'] ) ? $settings['checkoutButtonBgColor'] : '#F8F8F8';
$checkout_button_fontsize         	  = isset( $settings['checkoutButtonFontSize'] ) ? intval( $settings['checkoutButtonFontSize'] ) : 16;
$checkout_button_fontweight     	  = isset( $settings['checkoutButtonFontWeight'] ) ? $settings['checkoutButtonFontWeight'] : '500';
$checkout_button_text_transform 	  = isset( $settings['checkoutButtonTextTransform'] ) ? $settings['checkoutButtonTextTransform'] : 'none';
$checkout_button_style       		  = isset( $settings['checkoutButtonStyle'] ) ? $settings['checkoutButtonStyle'] : 'none';
$checkout_button_decoration     	  = isset( $settings['checkoutButtonDecoration'] ) ? $settings['checkoutButtonDecoration'] : 'none';
$checkout_button_line_height    	  = isset( $settings['checkoutButtonLineHeight'] ) ? intval( $settings['checkoutButtonLineHeight'] ) : 20;
$checkout_button_letter_spacing 	  = isset( $settings['checkoutButtonSpacing'] ) ? intval( $settings['checkoutButtonSpacing'] ) : 0;
$checkout_button_hover_color          = isset( $settings['checkoutButtonHoverColor'] ) ? $settings['checkoutButtonHoverColor'] : '#FFFFFF';
$checkout_button_hover_bg_Color       = isset( $settings['checkoutButtonHoverBgColor'] ) ? $settings['checkoutButtonHoverBgColor'] : 'var(--color-ec-body)';
$checkout_button_hover_fontsize       = isset( $settings['checkoutButtonHoverFontSize'] ) ? intval( $settings['checkoutButtonHoverFontSize'] ) : 16;
$checkout_button_hover_fontweight     = isset( $settings['checkoutButtonHoverFontWeight'] ) ? $settings['checkoutButtonHoverFontWeight'] : '500';
$checkout_button_hover_text_transform = isset( $settings['checkoutButtonHoverTextTransform'] ) ? $settings['checkoutButtonHoverTextTransform'] : 'none';
$checkout_button_hover_style          = isset( $settings['checkoutButtonHoverStyle'] ) ? $settings['checkoutButtonHoverStyle'] : 'none';
$checkout_button_hover_decoration     = isset( $settings['checkoutButtonHoverDecoration'] ) ? $settings['checkoutButtonHoverDecoration'] : 'none';
$checkout_button_hover_line_height    = isset( $settings['checkoutButtonHoverLineHeight'] ) ? intval( $settings['checkoutButtonHoverLineHeight'] ) : 20;
$checkout_button_hover_letter_spacing = isset( $settings['checkoutButtonHoverSpacing'] ) ? intval( $settings['checkoutButtonHoverSpacing'] ) : 0;

$ec_checkout_btn_class = 'ec-checkout-btn-' . uniqid();
?>
<style>
.<?php echo esc_attr( $ec_checkout_btn_class ); ?> {
	color: <?php echo esc_attr( $checkout_button_color ); ?>;
	background-color: <?php echo esc_attr( $checkout_button_bg_Color ); ?>;
	font-size: <?php echo intval( $checkout_button_fontsize ); ?>px;
	font-weight: <?php echo esc_attr( $checkout_button_fontweight ); ?>;
	text-transform: <?php echo esc_attr( $checkout_button_text_transform ); ?>;
	font-style: <?php echo esc_attr( $checkout_button_style ); ?>;
	text-decoration: <?php echo esc_attr( $checkout_button_decoration ); ?>;
	line-height: <?php echo intval( $checkout_button_line_height ); ?>px;
	letter-spacing: <?php echo intval( $checkout_button_letter_spacing ); ?>px;
	transition: all 0.3s ease;
	/* display: inline-flex; */
	gap: 8px;
	align-items: center;
	padding: 5px 16px;
	border: 1px solid #ebebeb;
    border-radius: 100px;
}
.<?php echo esc_attr( $ec_checkout_btn_class ); ?>:hover {
	color: <?php echo esc_attr( $checkout_button_hover_color ); ?>;
	background-color: <?php echo esc_attr( $checkout_button_hover_bg_Color ); ?>;
	font-size: <?php echo intval( $checkout_button_hover_fontsize ); ?>px;
    font-weight: <?php echo esc_attr( $checkout_button_hover_fontweight ); ?>;
    text-transform: <?php echo esc_attr( $checkout_button_hover_text_transform ); ?>;
	font-style: <?php echo esc_attr( $checkout_button_hover_style ); ?>;
    text-decoration: <?php echo esc_attr( $checkout_button_hover_decoration ); ?>;
    line-height: <?php echo intval( $checkout_button_hover_line_height ); ?>px;
    letter-spacing: <?php echo intval( $checkout_button_hover_letter_spacing ); ?>px;
}
</style>
<?php

// Print CSS for the class just once
static $ec_cart_btn_css_printed = false;
$ec_cart_btn_class = 'ec-cart-btn-' . uniqid();


if ( ! $ec_cart_btn_css_printed ) :
    $ec_cart_btn_css_printed = true;
    ?>
    <style>
        .<?php echo esc_attr( $ec_cart_btn_class ); ?> {
            color: <?php echo esc_attr( $cart_button_color ); ?>;
            background-color: <?php echo esc_attr( $cart_button_bg_Color ); ?>;
            font-size: <?php echo intval( $cart_button_fontsize ); ?>px;
            font-weight: <?php echo esc_attr( $cart_button_fontweight ); ?>;
            text-transform: <?php echo esc_attr( $cart_button_text_transform ); ?>;
			font-style: <?php echo esc_attr( $cart_button_style ); ?>;
            text-decoration: <?php echo esc_attr( $cart_button_decoration ); ?>;
            line-height: <?php echo intval( $cart_button_line_height ); ?>px;
            letter-spacing: <?php echo intval( $cart_button_letter_spacing ); ?>px;
            transition: all 0.3s ease;
            /* display: inline-flex; */
            gap: 8px;
            align-items: center;
            padding: 5px 17px;
            border-radius: 100px;
        }
        .<?php echo esc_attr( $ec_cart_btn_class ); ?>:hover {
            color: <?php echo esc_attr( $cart_button_hover_color ); ?>;
            background-color: <?php echo esc_attr( $cart_button_hover_bg_Color ); ?>;
			font-size: <?php echo intval( $cart_button_hover_fontsize ); ?>px;
            font-weight: <?php echo esc_attr( $cart_button_hover_fontweight ); ?>;
            text-transform: <?php echo esc_attr( $cart_button_hover_text_transform ); ?>;
			font-style: <?php echo esc_attr( $cart_button_hover_style ); ?>;
            text-decoration: <?php echo esc_attr( $cart_button_hover_decoration ); ?>;
            line-height: <?php echo intval( $cart_button_hover_line_height ); ?>px;
            letter-spacing: <?php echo intval( $cart_button_hover_letter_spacing ); ?>px;
			/* border: 1px solid #ebebeb; */
        }
		.<?php echo esc_attr( $ec_cart_btn_class ); ?>:focus {
            color: <?php echo esc_attr( $cart_button_focus_color ); ?>;
            background-color: <?php echo esc_attr( $cart_button_focus_bg_Color ); ?>;
        }
    </style>
    <?php
endif;


foreach ( $products as $product ) :

	$modal_product 	  = new Product_Model( $product['id'] );
	$categories    	  = $modal_product->get_categories();
	$image_url     	  = ! empty( $product['thumbnail']['url'] ) ? $product['thumbnail']['url'] : null;	
	$unique_class     = 'easycommerce-title-' . esc_attr( $product['id'] );	
	$show_stock_badge = easycommerce_is_stock_badge_enabled();
	$is_out_of_stock  = ( $product['stock'] !== false && $product['stock'] !== null && $product['stock'] <= 0 );

	?>
		<div class="col-span-1 rounded-lg easycommerce-single-product p-4 list-st easycommerce-st-product-card border">
			<div class="w-full rounded-t-xl">
				<?php if ( $product['thumbnail'] || $product['title'] ) : ?>
				<a class="w-full inline-block overflow-hidden" href="<?php echo esc_attr( $product['link'] ); ?>"
					style="text-decoration: none; outline: none;">
					<div class="rounded-md overflow-hidden relative">
						<?php
						$product_badges = $product['badges'] ?? array();

						$is_out_of_stock = (
							$product['stock'] !== false &&
							$product['stock'] !== null &&
							(int) $product['stock'] <= 0
						);

						$display_badges = array_filter(
							$product_badges,
							function ( $badge ) {
								return $badge['type'] !== 'out_of_stock';
							}
						);

						if ( ! $is_out_of_stock && $show_stock_badge ) {
							$display_badges[] = array(
								'label'      => __( 'In Stock', 'easycommerce' ),
								'color'      => '#10B981',
								'text_color' => '#FFFFFF',
							);
						}
						?>

						<?php if ( ! empty( $display_badges ) ) : ?>
							<div class="absolute top-3 left-3 z-10 flex flex-wrap items-center gap-1.5 max-w-[calc(100%-1.5rem)]">
								<?php foreach ( $display_badges as $badge ) : ?>
									<span
										class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded shadow-md"
										style="
											background-color: <?php echo esc_attr( $badge['color'] ); ?>;
											color: <?php echo esc_attr( $badge['text_color'] ?? '#FFFFFF' ); ?>;
										"
									>
										<?php echo esc_html( $badge['label'] ); ?>
									</span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ( $is_out_of_stock && $show_stock_badge ) : ?>
							<div class="absolute inset-0 z-20 flex items-center justify-center bg-black/40">
								<span class="inline-flex items-center text-xs font-semibold px-3 py-1.5 shadow-md rounded bg-gray-700 text-white">
									<?php esc_html_e( 'Out of Stock', 'easycommerce' ); ?>
								</span>
							</div>
						<?php endif; ?>
						<?php
						if ( $image_url ) {
							?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product['title'] ); ?>"
							class="w-full transition-transform duration-500 hover:scale-110 easycommerce-thumbnail-img" />
							<?php
						} else {
							?>
						<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/product/shop-product-placeholder.png' ); ?>"
							alt="<?php echo esc_attr( $product['title'] ); ?>"
							class="w-full transition-transform duration-500 hover:scale-110 easycommerce-thumbnail-img" />
							<?php
						}
						?>
					</div>
				</a>
				<?php endif; ?>
			</div>
			<div class="pt-4">
				<div class="mb-[2px]" style="
							color: <?php echo esc_attr( $title_color ); ?>;
							font-size: <?php echo esc_attr( $title_fontsize ); ?>px;
							font-weight: <?php echo esc_attr( $title_fontweight ); ?>;
							text-transform: <?php echo esc_attr( $title_text_transform ); ?>;
							font-style: <?php echo esc_attr( $title_text_style ); ?>;
							text-decoration: <?php echo esc_attr( $title_decoration ); ?>;
							line-height: <?php echo esc_attr( $title_line_height ); ?>px;
							letter-spacing: <?php echo esc_attr( $title_letter_spacing ); ?>px;
						">
					<a class="easycommerce-product-title-shop inline-block <?php echo $unique_class; ?> <?php echo esc_attr($title_text_transform); ?>  <?php echo esc_attr( $title_decoration ); ?>"
						title="<?php esc_attr( $product['title'] ); ?>" href="<?php echo esc_attr( $product['link'] ); ?>"
						style="text-decoration: none ; outline: none;"
						>
						<?php
						$title = $product['title'];

						if ( strlen( $title ) > 50 ) {
							echo esc_html( substr( $title, 0, 30 ) . '...' );
						} else {
							echo esc_html( $title );
						}
						?>
					</a>
				</div>
				<style>
					<?php if ( $title_hover_color ) : ?>
						.<?php echo $unique_class; ?>:hover {
							color: <?php echo esc_attr( $title_hover_color ); ?> !important;
						}
					<?php endif; ?>
				</style>
				<?php
				$stars_output = '';
				for ( $i = 0; $i < 5; $i++ ) {
					if ( $product['rating'] >= $i + 1 ) {
						$stars_output .= '<span><img src="' . esc_url( $full_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
					} elseif ( $product['rating'] > $i && $product['rating'] < $i + 1 ) {
						$stars_output .= '<span><img src="' . esc_url( $half_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
					} else {
						$stars_output .= '<span><img src="' . esc_url( $empty_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
					}
				}

				$has_rating = ! empty( $product['rating'] ) && $product['rating'] > 0;
				?>

				<?php if ( $has_rating ) : ?>
					<div class="mb-2">
						<!-- Only Rating -->
						<div class="flex items-center">
							<div class="flex flex-row items-center gap-1">
								<?php echo $stars_output; ?>
								<span class="ml-2"
									style="
										color: <?php echo esc_attr( $rating_color ); ?>;
										font-size: <?php echo esc_attr( $rating_font_size ); ?>px;
										font-weight: <?php echo esc_attr( $rating_font_weight ); ?>;
										text-transform: <?php echo esc_attr( $rating_text_transform ); ?>;
										font-style: <?php echo esc_attr( $rating_text_style ); ?>;
										text-decoration: <?php echo esc_attr( $rating_decoration ); ?>;
										line-height: <?php echo esc_attr( $rating_line_height ); ?>px;
										letter-spacing: <?php echo esc_attr( $rating_letter_spacing ); ?>px;
									">
										(<?php echo esc_html( $product['rating_count'] ); ?>)
								</span>
							</div>
						</div>
					</div>
				<?php endif; ?>
               
				<div class="easycommerce-product-price-wrap flex flex-wrap gap-2 items-start">
					<?php do_action( 'easycommerce_after_product_price', $product ); ?>

					<?php
					if ( empty( $product['attributes'] ) ) {
					?>
					<!-- Price Section -->
					<div class="flex items-center gap-3 font-inter">
						<?php
						if ( ! empty( $product['price'] ) ) :
							$_product             = new Product_Model( $product['id'] );
							$price                = $_product->get_price( false );
							$formatted_price      = $_product->get_price();
							$sale_price           = $_product->get_sale_price( false );
							$formatted_sale_price = $_product->get_sale_price();
							if ( $sale_price > 0 ) {
								?>
								<span 
										style="
										color: <?php echo esc_attr( $price_color ); ?>;
										font-size: <?php echo esc_attr( $price_font_size ); ?>px;
										font-weight: <?php echo esc_attr( $price_font_weight ); ?>;
										text-transform: <?php echo esc_attr( $price_text_transform ); ?>;
										font-style: <?php echo esc_attr( $price_style ); ?>;
										text-decoration: <?php echo esc_attr( $price_decoration ); ?>;
										line-height: <?php echo esc_attr( $price_line_height ); ?>px;
										letter-spacing: <?php echo esc_attr( $price_letter_spacing ); ?>px;
									">
									<?php echo esc_html( $formatted_sale_price ); ?>
								</span>
								<del style="
										color: <?php echo esc_attr( $sale_price_color ); ?>;
										font-size: <?php echo esc_attr( $sale_price_font_size ); ?>px;
										font-weight: <?php echo esc_attr( $sale_price_font_weight ); ?>;
										text-transform: <?php echo esc_attr( $sale_price_text_transform ); ?>;
										font-style: <?php echo esc_attr( $sale_price_style ); ?>;
										text-decoration: <?php echo esc_attr( $sale_price_decoration ); ?>;
										line-height: <?php echo esc_attr( $sale_price_line_height ); ?>px;
										letter-spacing: <?php echo esc_attr( $sale_price_letter_spacing ); ?>px;
									">
									<?php echo esc_html( $formatted_price ); ?>
								</del>
								<?php
							} else {
								?>
								<span style="
										color: <?php echo esc_attr( $price_color ); ?>;
										font-size: <?php echo esc_attr( $price_font_size ); ?>px;
										font-weight: <?php echo esc_attr( $price_font_weight ); ?>;
										text-transform: <?php echo esc_attr( $price_text_transform ); ?>;
										font-style: <?php echo esc_attr( $price_style ); ?>;
										text-decoration: <?php echo esc_attr( $price_decoration ); ?>;
										line-height: <?php echo esc_attr( $price_line_height ); ?>px;
										letter-spacing: <?php echo esc_attr( $price_letter_spacing ); ?>px;
								">
									<?php echo esc_html( $formatted_price ); ?>
								</span>
								<?php
							}
						endif;
						?>
					</div>
					<div class="flex justify-between items-center w-full">
						<div class="easycommerce-product-price-wrap-st flex xl:flex-row flex-col gap-1 justify-between xl:items-center items-start w-full">
							<!-- Cart Button -->
							<div class="xl:w-[100%]">
								<button data-id="<?php echo esc_attr( $product['id'] ); ?>"
								class="<?php echo esc_attr( $ec_cart_btn_class ); ?> easycommerce-button-margin easycommerce-add-to-cart-shop easycommerce-add-to-cart-st w-[120px] rounded-full<?php echo $is_out_of_stock ? ' opacity-50 cursor-not-allowed' : ''; ?>"
								<?php echo $is_out_of_stock ? 'disabled' : ''; ?>
								>
									<span id="buttonText" class="add-to-cart-text">
										<?php esc_html_e( 'Add to cart', 'easycommerce' ); ?>
									</span>
									<div id="loader" class="loader" style="display: none;"></div>
								</button>
							</div>
						</div>
						
						<!-- Checkout Button Row -->
						<?php if ( ! $is_out_of_stock ) : ?>
							<div class="w-full flex justify-end">
								<div class="easycommerce-single-product-checkout-btn-single">
										<a href="<?php echo esc_url( get_permalink( easycommerce_cart_redirect() ) ); ?>"
										class="w-[144px] <?php echo esc_attr( $ec_checkout_btn_class ); ?> flex items-center gap-3 justify-center !no-underline  rounded-full border border-ec-border"
										style="outline:none">
											Checkout 
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
											</svg>
										</a>
								</div>
							</div>
						<?php endif; ?>
					</div>

						<?php
					} else {
						?>
					<!-- Price Section for products with attributes -->
					<div class="flex flex-wrap justify-between gap-x-4 items-center font-inter">
						<?php
						if ( ! empty( $product['price'] ) ) :
							$_product             = new Product_Model( $product['id'] );
							$price                = $_product->get_price( false );
							$formatted_price      = $_product->get_price();
							$sale_price           = $_product->get_sale_price( false );
							$formatted_sale_price = $_product->get_sale_price();
							if ( $sale_price > 0 ) {
								?>
								<span 
										style="
										color: <?php echo esc_attr( $price_color ); ?>;
										font-size: <?php echo esc_attr( $price_font_size ); ?>px;
										font-weight: <?php echo esc_attr( $price_font_weight ); ?>;
										text-transform: <?php echo esc_attr( $price_text_transform ); ?>;
										font-style: <?php echo esc_attr( $price_style ); ?>;
										text-decoration: <?php echo esc_attr( $price_decoration ); ?>;
										line-height: <?php echo esc_attr( $price_line_height ); ?>px;
										letter-spacing: <?php echo esc_attr( $price_letter_spacing ); ?>px;
										word-break: break-word;
									">
									<?php echo esc_html( $formatted_sale_price ); ?>
								</span>
								<del style="
										color: <?php echo esc_attr( $sale_price_color ); ?>;
										font-size: <?php echo esc_attr( $sale_price_font_size ); ?>px;
										font-weight: <?php echo esc_attr( $sale_price_font_weight ); ?>;
										text-transform: <?php echo esc_attr( $sale_price_text_transform ); ?>;
										font-style: <?php echo esc_attr( $sale_price_style ); ?>;
										text-decoration: <?php echo esc_attr( $sale_price_decoration ); ?>;
										line-height: <?php echo esc_attr( $sale_price_line_height ); ?>px;
										letter-spacing: <?php echo esc_attr( $sale_price_letter_spacing ); ?>px;
										word-break: break-word;
									">
									<?php echo esc_html( $formatted_price ); ?>
								</del>
								<?php
							} else {
								?>
								<span style="
										color: <?php echo esc_attr( $price_color ); ?>;
										font-size: <?php echo esc_attr( $price_font_size ); ?>px;
										font-weight: <?php echo esc_attr( $price_font_weight ); ?>;
										text-transform: <?php echo esc_attr( $price_text_transform ); ?>;
										font-style: <?php echo esc_attr( $price_style ); ?>;
										text-decoration: <?php echo esc_attr( $price_decoration ); ?>;
										line-height: <?php echo esc_attr( $price_line_height ); ?>px;
										letter-spacing: <?php echo esc_attr( $price_letter_spacing ); ?>px;
										word-break: break-word;
								">
									<?php echo esc_html( $formatted_price ); ?>
								</span>
								<?php
							}
						endif;
						?>
					</div>
					<div class="easycommerce-product-price-wrap-st flex xl:flex-row flex-col gap-1 justify-between xl:items-center items-start w-full">
						<!-- Choose Button -->
						<a href="<?php echo esc_attr( $product['link'] ); ?>"
							class="<?php echo esc_attr( $ec_cart_btn_class ); ?> flex items-center gap-1 !no-underline easycommerce-shop-choose-btn easycommerce-st-choose-btn rounded-full transition-all ease-in-out duration-500<?php echo $is_out_of_stock ? ' opacity-50 pointer-events-none' : ''; ?>"
						>
							<?php _e( 'Choose', 'easycommerce' ); ?>
						</a>
					</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
<?php endforeach; ?>

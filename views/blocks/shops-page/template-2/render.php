<?php
/**
 * Render callback for the EasyCommerce Shop Template 2 block.
 *
 * This template renders the shop page layout with products, filters, and pagination in a different style.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
use EasyCommerce\Models\Taxonomy as Taxonomy_Model;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product as Product_Model;
use EasyCommerce\Helpers\Utility;

$store_mode = Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test';

if ( $store_mode === 'test' && ! current_user_can( 'manage_options' ) ) {
	include EASYCOMMERCE_PLUGIN_DIR . 'views/templates/store-mode.php';
	return;
}

$settings                             = $attributes;
$product_per_page                     = $settings['ProductPerPage'] ?? 9;
$columns                              = $settings['columns'] ?? 3;
$show_filters                         = (bool) ( $settings['showFilters'] ?? true );
$show_pagination                      = (bool) ( $settings['showPagination'] ?? true );
$shop_name                            = 'template-2';
$category_color                       = $settings['categoryColor'] ?? 'var(--color-ec-secondary)';
$category_fontsize                    = (int) ( $settings['categoryFontSize'] ?? 12 );
$category_fontweight                  = $settings['categoryFontWeight'] ?? '500';
$category_text_transform              = $settings['categoryTextTransform'] ?? 'none';
$category_text_style                  = $settings['categoryStyle'] ?? 'none';
$category_decoration                  = $settings['categoryDecoration'] ?? 'none';
$category_line_height                 = (int) ( $settings['categoryLineHeight'] ?? 20 );
$category_letter_spacing              = (int) ( $settings['categorySpacing'] ?? 0 );
$title_color                          = $settings['titleColor'] ?? 'var(--color-ec-body)';
$title_hover_color                    = $settings['titleHoverColor'] ?? 'var(--color-ec-primary)';
$title_fontsize                       = (int) ( $settings['titleFontSize'] ?? 16 );
$title_fontweight                     = $settings['titleFontWeight'] ?? '500';
$title_text_transform                 = $settings['titleTextTransform'] ?? 'none';
$title_text_style                     = $settings['titleTextStyle'] ?? 'none';
$title_decoration                     = $settings['titleDecoration'] ?? 'none';
$title_line_height                    = (int) ( $settings['titleLineHeight'] ?? 20 );
$title_letter_spacing                 = (int) ( $settings['titleSpacing'] ?? 0 );
$rating_color                         = $settings['ratingColor'] ?? 'var(--color-ec-body)';
$star_size                            = (int) ( $settings['starSize'] ?? 13 );
$rating_font_size                     = (int) ( $settings['ratingFontSize'] ?? 16 );
$rating_font_weight                   = $settings['ratingFontWeight'] ?? '500';
$rating_text_transform                = $settings['ratingTextTransform'] ?? 'none';
$rating_text_style                    = $settings['ratingStyle'] ?? 'none';
$rating_decoration                    = $settings['ratingDecoration'] ?? 'none';
$rating_line_height                   = (int) ( $settings['ratingLineHeight'] ?? 20 );
$rating_letter_spacing                = (int) ( $settings['ratingSpacing'] ?? 0 );
$price_color                          = $settings['priceColor'] ?? 'var(--color-ec-body)';
$price_font_size                      = (int) ( $settings['priceFontSize'] ?? 16 );
$price_font_weight                    = $settings['priceFontWeight'] ?? '500';
$price_text_transform                 = $settings['priceTextTransform'] ?? 'none';
$price_style                          = $settings['priceStyle'] ?? 'none';
$price_decoration                     = $settings['priceDecoration'] ?? 'none';
$price_line_height                    = (int) ( $settings['priceLineHeight'] ?? 20 );
$price_letter_spacing                 = (int) ( $settings['priceSpacing'] ?? 0 );
$sale_price_color                     = $settings['salePriceColor'] ?? 'var(--color-ec-body)';
$sale_price_font_size                 = (int) ( $settings['salePriceFontSize'] ?? 16 );
$sale_price_font_weight               = $settings['salePriceFontWeight'] ?? '500';
$sale_price_text_transform            = $settings['salePriceTextTransform'] ?? 'none';
$sale_price_style                     = $settings['salePriceStyle'] ?? 'none';
$sale_price_decoration                = $settings['salePriceDecoration'] ?? 'line-through';
$sale_price_line_height               = (int) ( $settings['salePriceLineHeight'] ?? 20 );
$sale_price_letter_spacing            = (int) ( $settings['salePriceSpacing'] ?? 0 );
$cart_button_color                    = $settings['cartButtonColor'] ?? 'var(--color-ec-body)';
$cart_button_bg_color                 = $settings['cartButtonBgColor'] ?? '#F8F8F8';
$cart_button_fontsize                 = (int) ( $settings['cartButtonFontSize'] ?? 16 );
$cart_button_fontweight               = $settings['cartButtonFontWeight'] ?? '500';
$cart_button_text_transform           = $settings['cartButtonTextTransform'] ?? 'none';
$cart_button_style                    = $settings['cartButtonStyle'] ?? 'none';
$cart_button_decoration               = $settings['cartButtonDecoration'] ?? 'none';
$cart_button_line_height              = (int) ( $settings['cartButtonLineHeight'] ?? 20 );
$cart_button_letter_spacing           = (int) ( $settings['cartButtonSpacing'] ?? 0 );
$cart_button_hover_color              = $settings['cartButtonHoverColor'] ?? '#FFFFFF';
$cart_button_hover_bg_color           = $settings['cartButtonHoverBgColor'] ?? 'var(--color-ec-body)';
$cart_button_hover_fontsize           = (int) ( $settings['cartButtonHoverFontSize'] ?? 16 );
$cart_button_hover_fontweight         = $settings['cartButtonHoverFontWeight'] ?? '500';
$cart_button_hover_text_transform     = $settings['cartButtonHoverTextTransform'] ?? 'none';
$cart_button_hover_style              = $settings['cartButtonHoverStyle'] ?? 'none';
$cart_button_hover_decoration         = $settings['cartButtonHoverDecoration'] ?? 'none';
$cart_button_hover_line_height        = (int) ( $settings['cartButtonHoverLineHeight'] ?? 20 );
$cart_button_hover_letter_spacing     = (int) ( $settings['cartButtonHoverSpacing'] ?? 0 );
$cart_button_focus_color              = $settings['cartButtonFocusColor'] ?? '#ffffff';
$cart_button_focus_bg_color           = $settings['cartButtonFocusBgColor'] ?? 'var(--color-ec-primary)';
$checkout_button_color                = $settings['checkoutButtonColor'] ?? 'var(--color-ec-body)';
$checkout_button_bg_color             = $settings['checkoutButtonBgColor'] ?? '#F8F8F8';
$checkout_button_fontsize             = (int) ( $settings['checkoutButtonFontSize'] ?? 16 );
$checkout_button_fontweight           = $settings['checkoutButtonFontWeight'] ?? '500';
$checkout_button_text_transform       = $settings['checkoutButtonTextTransform'] ?? 'none';
$checkout_button_style                = $settings['checkoutButtonStyle'] ?? 'none';
$checkout_button_decoration           = $settings['checkoutButtonDecoration'] ?? 'none';
$checkout_button_line_height          = (int) ( $settings['checkoutButtonLineHeight'] ?? 20 );
$checkout_button_letter_spacing       = (int) ( $settings['checkoutButtonSpacing'] ?? 0 );
$checkout_button_hover_color          = $settings['checkoutButtonHoverColor'] ?? '#FFFFFF';
$checkout_button_hover_bg_color       = $settings['checkoutButtonHoverBgColor'] ?? 'var(--color-ec-body)';
$checkout_button_hover_fontsize       = (int) ( $settings['checkoutButtonHoverFontSize'] ?? 16 );
$checkout_button_hover_fontweight     = $settings['checkoutButtonHoverFontWeight'] ?? '500';
$checkout_button_hover_text_transform = $settings['checkoutButtonHoverTextTransform'] ?? 'none';
$checkout_button_hover_style          = $settings['checkoutButtonHoverStyle'] ?? 'none';
$checkout_button_hover_decoration     = $settings['checkoutButtonHoverDecoration'] ?? 'none';
$checkout_button_hover_line_height    = (int) ( $settings['checkoutButtonHoverLineHeight'] ?? 20 );
$checkout_button_hover_letter_spacing = (int) ( $settings['checkoutButtonHoverSpacing'] ?? 0 );
$full_star                            = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/full-star.png';
$half_star                            = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/half-star.png';
$empty_star                           = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/empty-star.png';
$arrow_first                          = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/ArrowLeft.png';
$arrow_last                           = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/ArrowRight.png';
$back                                 = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/Back.png';
$next                                 = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/Next.png';
$current_page                         = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$taxomonomy                           = new Taxonomy_Model();
$attribute                            = new Attribute();
$attributes                           = $attribute->get_all();
$brands                               = $taxomonomy->list_terms( 0, 'product_brand' );
$categories                           = $taxomonomy->list_terms();
$products                             = array();
$total_pages                          = 0;
$products_count                       = 0;
$min_price                            = 0;
$max_price                            = 10000;
$database                             = new Database( 'product_variations' );
$min_max_prices                       = $database->get_data( 'MIN(price) AS min_price, MAX(price) AS max_price' );

if ( ! empty( $min_max_prices ) ) {
	$min_price = (int) $min_max_prices[0]->min_price;
	$max_price = (int) $min_max_prices[0]->max_price;
}

$products_data  = Product_Model::list( array( 'is_shop' => 1 ), $product_per_page, ( $current_page - 1 ) * $product_per_page, true, true );
$products       = $products_data['products'];
$products_count = $products_data['total'] ?? 0;
$total_pages    = ceil( $products_count / $product_per_page );

$formatted_products = array_map(
	function ( $product ) {
		return array(
			'id'                   => $product->get_id(),
			'title'                => $product->get_title(),
			'slug'                 => $product->get_slug(),
			'description'          => $product->get_description(),
			'summary'              => $product->get_summary(),
			'status'               => $product->get_status(),
			'link'                 => $product->get_url(),
			'thumbnail'            => $product->get_thumbnail( 'easycommerce-shop-thumbnail' ),
			'rating'               => $product->get_rating(),
			'rating_count'         => $product->get_rating_count(),
			'price'                => $product->get_price(),
			'sale_price'           => $product->get_sale_price(),
			'formatted_price'      => $product->get_price( false ),
			'formatted_sale_price' => $product->get_sale_price( false ),
			'stock'                => $product->get_stock(),
			'categories'           => $product->get_categories(),
			'tags'                 => $product->get_tags(),
			'brands'               => $product->get_brands(),
			'sales'                => $product->get_sales(),
			'attributes'           => $product->get_attributes(),
			'badges'               => $product->get_badges(),
			'is_variable'          => $product->is_variable(),
		);
	},
	$products
);

$products = $formatted_products;

?>
<div class="flex flex-col gap-2">
	<div>
		<p class="easycommerce-shop-count text-ec-body !mb-0">
			<?php
			if ( $show_pagination ) {
				if ( $product_per_page < $products_count ) {
					printf(
						esc_html__( 'Showing %1$s–%2$s of %3$s results', 'easycommerce' ),
						esc_html( $current_page ),
						esc_html( $product_per_page ),
						esc_html( $products_count )
					);
				} else {
					printf(
						esc_html( _n( 'Showing %s result', 'Showing %s results', $products_count, 'easycommerce' ) ),
						esc_html( number_format_i18n( $products_count ) )
					);
				}
			}
			?>
		</p>
	</div>
	<?php do_action( 'easycommerce_before_shop_item' ); ?>
	<div id="easycommerce-filter" class="easycommerce-filter cursor-pointer flex justify-start items-center mb-4">
		<img id="easycommerce-filter-icon" class="w-9 h-9 object-contain" src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/filter.png' ); ?>" alt="<?php esc_attr_e( 'Filter', 'easycommerce' ); ?>" />
	</div>
	<div class="flex justify-between gap-10 items-start easycommerce-shop-container-front">
		<input type="hidden" id="shop-settings" name="shop-settings" value='<?php echo esc_attr( wp_json_encode( $settings ) ); ?>'>
		<input type="hidden" id='shop-name' name="shop-name" value="template-2">
		<?php if ( $show_filters ) : ?>
			<div class="easycommerce-drawer-container w-max">
				<?php require_once EASYCOMMERCE_PLUGIN_DIR . 'views/blocks/shops-page/template-2/inc/filter.php'; ?>
			</div>
		<?php endif; ?>

		<!-- Shop Grid -->
		<div class="">
			<div class="easycommerce-shop-container w-full grow grid <?php echo 'grid-cols-' . esc_attr($settings['columns']); ?> gap-4">
				<?php do_action( 'easycommerce/views/blocks/shop', $settings, $products, $shop_name, 'easycommerce-shop-container' ); ?>
			</div>

			<?php if ( $show_pagination ) : ?>
			<div class="w-max mx-auto">
				<?php require_once EASYCOMMERCE_PLUGIN_DIR . 'views/blocks/shops-page/template-2/inc/pagination.php'; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="w-full flex">
	<div class="w-[30%]"></div>
	<div class="w-[70%]">

	</div>
</div>

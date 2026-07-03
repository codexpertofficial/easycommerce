<?php
/**
 * Render callback for the EasyCommerce Shop Template 3 block.
 *
 * This template renders the shop page with grid and list view options, filters, and pagination.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
use EasyCommerce\Models\Taxonomy as Taxonomy_Model;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product as Product_Model;

$store_mode = Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test';

if ( $store_mode === 'test' && ! current_user_can( 'manage_options' ) ) {
	include EASYCOMMERCE_PLUGIN_DIR . 'views/templates/store-mode.php';
	return;
}

$settings                   		  = $attributes;
$product_per_page           		  = isset( $settings['ProductPerPage'] ) ? $settings['ProductPerPage'] : 9;
$columns                    		  = isset( $settings['columns'] ) ? $settings['columns'] : 3;
$min_price                  		  = 0;
$max_price                  		  = 10000;
$category_color             		  = isset( $settings['categoryColor'] ) ? $settings['categoryColor'] : 'var(--color-ec-secondary)';
$category_fontsize          		  = isset( $settings['categoryFontSize'] ) ? intval( $settings['categoryFontSize'] ) : 12;
$category_fontweight        		  = isset( $settings['categoryFontWeight'] ) ? $settings['categoryFontWeight'] : '500';
$category_text_transform    		  = isset( $settings['categoryTextTransform'] ) ? $settings['categoryTextTransform'] : 'none';
$category_text_style        		  = isset( $settings['categoryStyle'] ) ? $settings['categoryStyle'] : 'none';
$category_decoration        		  = isset( $settings['categoryDecoration'] ) ? $settings['categoryDecoration'] : 'none';
$category_line_height       		  = isset( $settings['categoryLineHeight'] ) ? intval( $settings['categoryLineHeight'] ) : 20;
$category_letter_spacing    		  = isset( $settings['categorySpacing'] ) ? intval( $settings['categorySpacing'] ) : 0;
$title_color                		  = isset( $settings['titleColor'] ) ? $settings['titleColor'] : 'var(--color-ec-body)';
$title_hover_color                	  = isset( $settings['titleHoverColor'] ) ? $settings['titleHoverColor'] : 'var(--color-ec-primary)';
$title_fontsize             		  = isset( $settings['titleFontSize'] ) ? intval( $settings['titleFontSize'] ) : 16;
$title_fontweight           		  = isset( $settings['titleFontWeight'] ) ? $settings['titleFontWeight'] : '500';
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
$cart_button_hover_color    		  = isset( $settings['cartButtonHoverColor'] ) ? $settings['cartButtonHoverColor'] : '#FFFFFF';
$cart_button_hover_bg_Color 		  = isset( $settings['cartButtonHoverBgColor'] ) ? $settings['cartButtonHoverBgColor'] : 'var(--color-ec-body)';
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
$checkout_button_fontsize       	  = isset( $settings['checkoutButtonFontSize'] ) ? intval( $settings['checkoutButtonFontSize'] ) : 16;
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
$show_filters               		  = isset( $settings['showFilters'] ) ? (bool) $settings['showFilters'] : true;
$show_pagination            		  = isset( $settings['showPagination'] ) ? (bool) $settings['showPagination'] : true;
$show_short_by              		  = isset( $settings['showShortBy'] ) ? (bool) $settings['showShortBy'] : true;
$easycommerce_rest_url      		  = easycommerce_rest_base();
$current_page               		  = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$shop_name                  		  = 'template-3';
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
			'is_variable'          => $product->is_variable(),
		);
	},
	$products
);

$products = $formatted_products;
?>
<div class="flex flex-col gap-2">
	<!-- <div>
		<p class="easycommerce-shop-count text-ec-body !mb-0">
			<?php
			if ( $show_pagination ) {
				if ( $product_per_page < $products_count ) {
					printf(
						esc_html__( 'Showing %1$s–%2$s of %3$s results', 'easycommerce' ),
						$current_page,
						$product_per_page,
						$products_count
					);
				} else {
					printf(
						_n(
							'Showing %s result',
							'Showing %s results',
							$products_count,
							'easycommerce'
						),
						number_format_i18n( $products_count )
					);
				}
			}
			?>
		</p>
	</div> -->
	<?php
		do_action( 'easycommerce_before_shop_item' );
	?>
	<div id="easycommerce-filter" class="easycommerce-filter cursor-pointer flex justify-start items-center mb-4">
		<img id="easycommerce-filter-icon" class="w-9 h-9 object-contain" src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/filter.png' ); ?>" />
	</div>
	<div class="flex justify-between gap-10 items-start easycommerce-shop-container-front">
		<input type="hidden" id="shop-settings" name="shop-settings" value='<?php echo wp_json_encode( $settings ); ?>'>
		<input type="hidden" id='shop-name' name="shop-name" value="<?php echo esc_attr( $shop_name ); ?>">

		<!-- Filter section -->
		<?php if ( $show_filters ) : ?>
			<div class="easycommerce-drawer-container w-max">
				<?php require_once EASYCOMMERCE_PLUGIN_DIR . 'views/blocks/shops-page/template-3/inc/filter.php'; ?>
			</div>
		<?php endif; ?>

		<!-- Shop Grid/List -->
		<div class="flex flex-col gap-7 w-full grow mt-[35px]">
			<?php if ( $show_short_by ) : ?>
			<?php require_once EASYCOMMERCE_PLUGIN_DIR . 'views/blocks/shops-page/template-3/inc/shortby.php'; ?>
			<?php endif; ?>

			<!-- Grid View -->
			<div class="easycommerce-shop-container easycommerce-st-grid easycommerce-shop-container-st w-full grid <?php echo 'grid-cols-' . esc_attr( $settings['columns'] ); ?> gap-5">
				<?php do_action( 'easycommerce/views/blocks/shop', $settings, $products, $shop_name, 'easycommerce-st-grid' ); ?>
			</div>

			<!-- List View -->
			<div class="easycommerce-shop-container easycommerce-st-list easycommerce-shop-container-st-list w-full hidden flex-col gap-5">
				<?php do_action( 'easycommerce/views/blocks/shop', $settings, $products, $shop_name, 'easycommerce-st-list' ); ?>
			</div>
		</div>
	</div>
</div>

<?php if ( $show_pagination ) : ?>
<div class="w-full flex">
	<div class="w-[30%] hidden lg:block"></div>
	<div class="lg:w-[70%] w-full">
		<?php require_once EASYCOMMERCE_PLUGIN_DIR . 'views/blocks/shops-page/template-3/inc/pagination.php'; ?>
	</div>
</div>
<?php endif; ?>


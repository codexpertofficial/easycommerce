<?php
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product as Product_Model;

$product         = new Product_Model( get_the_ID() );
$price           = $product->get_price( false );
$formatted_price = $product->get_price();
$sale_price      = $product->get_sale_price( false );
if ( $sale_price != '' ) {
	printf(
		'<del class="easycommerce-product-price text-ec-secondary font-inter font-normal text-lg leading-6">%s</del>',
		esc_html( $formatted_price )
	);
} else {
	printf(
		'<span class="easycommerce-product-sale-price font-inter font-bold text-xl leading-7 text-[#120350] mr-4">%s</span>',
		esc_html( $formatted_price )
	);
}
do_action( 'easycommerce_product_price_html', $product );

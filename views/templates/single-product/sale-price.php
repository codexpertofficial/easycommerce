<?php

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product as Product_Model;

$product    = new Product_Model( get_the_ID() );
$sale_price = $product->get_sale_price( false );

if ( $sale_price != '' ) {
	printf(
		'<span class="easycommerce-product-sale-price font-inter font-bold text-xl leading-7 text-[#120350] mr-4">%s</span>',
		esc_html( $product->get_sale_price() )
	);
}

<?php

use EasyCommerce\Models\Product as Product_Model;

if ( $product = new Product_Model( get_the_ID() ) ) {
	if ( $stock = $product->get_stock() ) {
		echo esc_html( $stock );
	}
}

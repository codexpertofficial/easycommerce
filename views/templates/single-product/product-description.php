<?php
use EasyCommerce\Models\Product as Product_Model;

if ( $product = new Product_Model( get_the_ID() ) ) {
	if ( $description = $product->get_description() ) {
		echo wp_kses_post( $description );
	}
}

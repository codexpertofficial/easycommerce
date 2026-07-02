<?php
if ( get_the_post_thumbnail_url() ) {
	echo esc_url( get_the_post_thumbnail_url() );
} else {
	echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/product/single-product-placeholder.png' );
}

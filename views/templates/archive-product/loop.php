<div class="easycommerce-product bg-white p-4 rounded">
	<?php

	do_action( 'easycommerce-before_product_item' );

	if ( has_post_thumbnail() ) {

		do_action( 'easycommerce-before_product_thumbnail' );

		echo '<div class="easycommerce-product-thumbnail mb-4">';
		the_post_thumbnail( 'easycommerce-shop-thumbnail', array( 'class' => 'w-full h-auto object-cover rounded' ) );
		echo '</div>';

		do_action( 'easycommerce-after_product_thumbnail' );
	}

	do_action( 'easycommerce-before_product_title' );

	printf( '<h2 class="easycommerce-product-title text-lg font-bold mb-2"><a href="%1$s" class="text-blue-500 hover:text-blue-700">%2$s</a></h2>', esc_url( get_permalink() ), get_the_title() );

	do_action( 'easycommerce-after_product_title' );

	do_action( 'easycommerce-before_product_price' );

	if ( $price = get_post_meta( get_the_ID(), '_price', true ) ) {
		echo '<p class="easycommerce-product-price text-gray-700 font-semibold mb-4">' . esc_html( $price ) . '</p>';
	}

	do_action( 'easycommerce-after_product_price' );

	do_action( 'easycommerce-after_product_item' );
	?>
</div>
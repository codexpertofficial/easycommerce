<?php
// Handle block content compatibility - check for blocks only if function exists
if ( function_exists( 'has_blocks' ) && has_blocks( get_the_ID() ) ) {
	the_content();
	return;
}
?>
<?php do_action( 'easycommerce-before_main_content' ); ?>

<div class="easycommerce-single-product grid grid-cols-1 gap-4">
	<?php do_action( 'easycommerce-before_single_product' ); ?>

	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
		<div class="easycommerce-product-item grid grid-cols-1 md:grid-cols-2 gap-4">
			<?php
			do_action( 'easycommerce-before_product_item' );

			echo '<div class="easycommerce-product-image">';

			if ( has_post_thumbnail() ) {
				do_action( 'easycommerce-before_product_thumbnail' );

				do_action( 'easycommerce/views/templates/single-product/thumbnail' );

				do_action( 'easycommerce-after_product_thumbnail' );
			}

			echo '</div><!-- .easycommerce-product-image -->';

			echo '<div class="easycommerce-product-details flex flex-col justify-start">';

				do_action( 'easycommerce/single-product/before_title' );

				do_action( 'easycommerce/views/templates/single-product/title' );

				do_action( 'easycommerce/single-product/after_title' );

				do_action( 'easycommerce-before_product_price' );

				do_action( 'easycommerce/views/templates/single-product/price' );

				do_action( 'easycommerce-after_product_price' );

				do_action( 'easycommerce/views/templates/single-product/add-to-cart' );

			echo '</div><!-- .easycommerce-product-details -->';

			do_action( 'easycommerce-after_product_item' );
			?>
		</div><!-- .easycommerce-product-item -->

		<div class="easycommerce-product-description col-span-1 mt-4">
			<?php
			do_action( 'easycommerce-before_product_content' );

			do_action( 'easycommerce/views/templates/single-product/excerpt' );

			do_action( 'easycommerce-after_product_content' );
			?>
		</div><!-- .easycommerce-product-description -->

			<?php endwhile; else : ?>

		<p><?php esc_html_e( 'No products found.', 'easycommerce' ); ?></p>

	<?php endif; ?>

	<?php do_action( 'easycommerce-after_single_product' ); ?>
	

</div><!-- .easycommerce-single-product -->

<?php
do_action( 'easycommerce-after_main_content' );
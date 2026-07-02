<?php
use EasyCommerce\Models\Product as Product_Model;
use EasyCommerce\Helpers\Utility;

// Handle block content compatibility - check for blocks only if function exists
if ( function_exists( 'has_blocks' ) && has_blocks( get_the_ID() ) ) {
	the_content();
	return;
}

if ( $product = new Product_Model( get_the_ID() ) ) {
	$sale_price = $product->get_sale_price();
}
?>
<?php do_action( 'easycommerce-before_main_content' ); ?>

<div class="easycommerce-single-main-wrapper">
	<?php do_action( 'easycommerce-before_single_product' ); ?>

	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>

	<!-- <div class="easycommerce-single-product-content flex flex-col 2xl:flex-row xl:flex-row lg:flex-col lg:justify-between w-full gap-6 p-2 md:flex-col sm:flex-col"> -->
	<div class="easycommerce-single-product-content">

		<div class="easycommerce-single-product-content-left">

			<!-- Product Gallery -->
			<?php do_action( 'easycommerce-before_product_gallery' ); ?>

			<?php do_action( 'easycommerce/views/templates/single-product/gallery' ); ?>

			<?php do_action( 'easycommerce-after_product_gallery' ); ?>

		</div>

		<div class="easycommerce-single-product-content-right">
			<!-- Product Title  -->
			<?php do_action( 'easycommerce-before_product_title' ); ?>

			<?php do_action( 'easycommerce/views/templates/single-product/title' ); ?>

			<?php do_action( 'easycommerce-after_product_title' ); ?>

			<!-- Product Rating  -->
			<?php do_action( 'easycommerce-before_product_rating' ); ?>

			<div class="mb-3">
				<?php do_action( 'easycommerce/views/templates/single-product/rating' ); ?>
			</div>

			<?php do_action( 'easycommerce-after_product_rating' ); ?>

			<!-- Product Buy It Now  -->
			<?php do_action( 'easycommerce-before_product_price' ); ?>

			<div class="mb-6">
				<div class="flex items-center mb-2">
					<?php do_action( 'easycommerce/views/templates/single-product/sale_price' ); ?>
					<?php do_action( 'easycommerce/views/templates/single-product/price' ); ?>
				</div>
			</div>
			<?php do_action( 'easycommerce/views/templates/single-product/summary' ); ?>

			<?php do_action( 'easycommerce-after_product_price' ); ?>

			<!-- Product Buy It Now  -->
			<?php $stock = $product->get_stock(); ?>
			<div class="mb-6 easycommerce-stock-count-wrapper">
				<p class="text-ec-body font-inter text-base leading-[26px]">
					<?php
					if ( $stock ) :
						/* Translators: %s is the stock count */
						printf( __( 'Only <span class="easycommerce-stock-count">%s</span> items left', 'easycommerce' ), $stock );
					endif;
					?>
				</p>
			</div>

			<!-- Product attributes  -->
			<?php do_action( 'easycommerce-before_product_attributes' ); ?>

			<?php do_action( 'easycommerce/views/templates/single-product/attributes' ); ?>

			<?php do_action( 'easycommerce-after_product_attributes' ); ?>

			<!-- Product Add to Cart  -->
			<?php do_action( 'easycommerce-before_product_add_to_cart' ); ?>

			<?php do_action( 'easycommerce/views/templates/single-product/add-to-cart' ); ?>

			<?php do_action( 'easycommerce-after_product_add_to_cart' ); ?>
		</div>
	</div>

	<?php do_action( 'easycommerce/views/templates/single-product/product-tab' ); ?>

	<?php endwhile; else : ?>

	<p><?php esc_html_e( 'No products found.', 'easycommerce' ); ?></p>

	<?php endif; ?>

	<?php do_action( 'easycommerce-after_single_product' ); ?>

</div><!-- .easycommerce-single-product -->

<?php
do_action( 'easycommerce-after_main_content' );
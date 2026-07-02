<?php do_action( 'easycommerce-before_main_content' ); ?>

<div class="easycommerce-products-wrap mx-auto px-4">
	<?php do_action( 'easycommerce-before_products_wrap' ); ?>

	<?php if ( have_posts() ) : ?>
		<div class="easycommerce-products grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
			<?php

			do_action( 'easycommerce-before_products_loop' );

			while ( have_posts() ) :
				the_post();

				do_action( 'easycommerce/views/templates/archive-product/loop' );

			endwhile;

			do_action( 'easycommerce-after_products_loop' );

			?>
		</div>

		<?php do_action( 'easycommerce/views/templates/archive-product/pagination' ); ?>
	
	<?php else : ?>
		<p class="text-gray-500"><?php esc_html_e( 'No products found.', 'easycommerce' ); ?></p>
	<?php endif; ?>

	<?php do_action( 'easycommerce-after_products_grid' ); ?>
</div>

<?php
do_action( 'easycommerce-after_main_content' );
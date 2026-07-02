<?php
use EasyCommerce\Models\Product;

$product_id   = get_the_ID();
$product      = new Product( $product_id );
$show_review  = $product->get_meta( 'show_review' );
$product_meta = $product->get_meta( 'review_text_mandatory' );
?>
<div class="easycommerce-single-product-description">
	<div>
		<div class="easycommerce-product-details easycommerce-tabs">
			<ul class="flex" style="border-bottom:1px solid #DBDBDB">
				<li class="easycommerce-tab active cursor-pointer text-ec-primary border-b border-ec-primary p-4 -mb-[1px]" data-tab="easycommerce-description"><?php esc_html_e( 'Product Details', 'easycommerce' ); ?></li>
				
				<?php if ( $show_review ) : ?>
					<li class="easycommerce-tab cursor-pointer p-4 hover:text-ec-primary" data-tab="easycommerce-review"><?php esc_html_e( 'Review', 'easycommerce' ); ?></li>
				<?php endif; ?>
			</ul>
			<div class="tab-content mt-8">
				<div id="easycommerce-description" class="easycommerce-tab-content active">
					<?php do_action( 'easycommerce/views/templates/single-product/description' ); ?>
				</div>

				<?php if ( $show_review ) : ?>
				<div id="easycommerce-review" class="easycommerce-tab-content hidden">
					<?php do_action( 'easycommerce/views/templates/single-product/review' ); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php
/**
 * Render callback for the EasyCommerce Product Price block.
 *
 * Displays the price and sale price for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
if( get_post_type( get_the_ID() ) !== 'product' ) {
    esc_html_e( 'Post type is not product', 'easycommerce' );
    return;
} ?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <div class="flex items-center mb-2">
        <?php do_action( 'easycommerce/views/templates/single-product/sale_price' ); ?>
        <?php do_action( 'easycommerce/views/templates/single-product/price' ); ?>
    </div>
</div>

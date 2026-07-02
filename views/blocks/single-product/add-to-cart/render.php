<?php
/**
 * Render callback for the EasyCommerce Add to Cart block.
 *
 * Displays the add to cart button and quantity selector for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
    if( get_post_type( get_the_ID() ) !== 'product' ) {
    echo "Post type is not product";
    return;
} ?>

<div <?php echo esc_attr( get_block_wrapper_attributes() ); ?>>
    <?php do_action( 'easycommerce/views/templates/single-product/add-to-cart' ) ?>
</div>

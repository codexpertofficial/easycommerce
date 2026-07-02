<?php
/**
 * Render callback for the EasyCommerce Product Thumbnail block.
 *
 * Displays the main product thumbnail image for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
if( get_post_type( get_the_ID() ) !== 'product' ) {
    echo "Post type is not product";
    return;
} ?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <img src="<?php do_action( 'easycommerce/views/templates/single-product/thumbnail' ); ?>" alt="">
</div>

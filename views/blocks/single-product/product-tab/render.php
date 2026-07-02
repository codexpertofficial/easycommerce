<?php
/**
 * Render callback for the EasyCommerce Product Tab block.
 *
 * Displays the product tabs (description, reviews, etc.) for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php do_action( 'easycommerce/views/templates/single-product/product-tab' ); ?>
</div>

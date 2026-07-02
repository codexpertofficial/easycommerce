<?php
/**
 * Render callback for the EasyCommerce Product Attributes block.
 *
 * Displays product attributes and variations for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
do_action( 'easycommerce-before_product_attributes' ); ?>

<?php do_action( 'easycommerce/views/templates/single-product/attributes' ); ?>

<?php do_action( 'easycommerce-after_product_attributes' ); ?>

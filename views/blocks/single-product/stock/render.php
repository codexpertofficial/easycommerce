<?php
/**
 * Render callback for the EasyCommerce Product Stock block.
 *
 * Displays the stock status and remaining quantity for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */
use EasyCommerce\Models\Product as Product_Model;

if( get_post_type( get_the_ID() ) !== 'product' ) {
    echo "Post type is not product";
    return;
}

$settings = $attributes;

if ( $product = new Product_Model( get_the_ID() ) ) {
    if ( $stock = $product->get_stock() ) {
        if( $stock > 0 ) {

            ?>
                <div class="easycommerce-stock-count-wrapper" <?php echo esc_attr( get_block_wrapper_attributes() ); ?>>
                    <div class="text-ec-body font-inter text-base leading-[26px] flex items-center gap-1">
                        <div class="easycommerce-stock-dot w-2 h-2 rounded-full bg-emerald-500"></div>
                        <span class="easycommerce-stock-count text-emerald-500" value="<?php do_action( 'easycommerce/views/templates/single-product/stock' ); ?>"><?php do_action( 'easycommerce/views/templates/single-product/stock' ); ?> </span> <span class="easycommerce-stock-label text-emerald-500"><?php esc_html_e( 'in stock', 'easycommerce' ); ?></span> 
                    </div>
                </div>
            <?php
        }
    }
} 
?>

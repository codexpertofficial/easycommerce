<?php
/**
 * Render callback for the EasyCommerce Product Rating block.
 *
 * Displays the star rating and review count for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */

if( get_post_type( get_the_ID() ) !== 'product' ) {
    echo "Post type is not product";
    return;
}
use EasyCommerce\Models\Product as Product_Model;

if ( $product = new Product_Model( get_the_ID() ) ) {
    $rating       = $product->get_rating();
    $rating_count = $product->get_rating_count();
}
$star_size  = 16;
$full_star  = EASYCOMMERCE_ASSETS_URL . "common/img/blocks/shop-page/full-star.png";
$half_star  = EASYCOMMERCE_ASSETS_URL . "common/img/blocks/shop-page/half-star.png";
$empty_star = EASYCOMMERCE_ASSETS_URL . "common/img/blocks/shop-page/empty-star.png";

?>
<div <?php echo esc_attr( get_block_wrapper_attributes() ); ?>>
    <?php
    $stars_output = '';
    for ( $i = 0; $i < 5; $i++ ) {
        if ( $rating >= $i + 1 ) {
            $stars_output .= '<span><img src="' . esc_url( $full_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
        } elseif ( $rating > $i && $rating < $i + 1 ) {
            $stars_output .= '<span><img src="' . esc_url( $half_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
        } else {
            $stars_output .= '<span><img src="' . esc_url( $empty_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
        }
    }
    if( ! empty( $rating ) ) : ?>
        <div class="flex flex-row items-center gap-1 mt-2 mb-6">
            <?php echo wp_kses_post( $stars_output ); ?>
            (<?php echo esc_html( $rating_count ); ?>)
        </div>
    <?php endif; ?>
</div>

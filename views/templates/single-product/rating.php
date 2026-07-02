<?php
if ( get_post_type( get_the_ID() ) !== 'product' ) {
	echo 'Post type is not product';
	return;
}
use EasyCommerce\Models\Product as Product_Model;

if ( $product = new Product_Model( get_the_ID() ) ) {
	$rating			= $product->get_rating();
	$rating_count	= $product->get_rating_count();
}
$star_size		= 16;
$full_star  	= EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/full-star.png';
$half_star  	= EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/half-star.png';
$empty_star 	= EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/empty-star.png';
$stars_output 	= '';
for ( $i = 0; $i < 5; $i++ ) {
	if ( $rating >= $i + 1 ) {
		$stars_output .= '<span><img src="' . esc_url( $full_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
	} elseif ( $rating > $i && $rating < $i + 1 ) {
		$stars_output .= '<span><img src="' . esc_url( $half_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
	} else {
		$stars_output .= '<span><img src="' . esc_url( $empty_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
	}
}
if ( ! empty( $rating ) ) : ?>
	<div class="flex flex-row items-center gap-1 mt-2 pb-6 border-b border-ec-border">
		<?php echo $stars_output; ?>
		(<?php echo esc_html( $rating_count ); ?>)
		(<?php echo esc_html( number_format( $rating, 1 ) ); ?>)
	</div>
<?php endif; ?>

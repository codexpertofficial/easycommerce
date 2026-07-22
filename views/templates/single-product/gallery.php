<?php
if ( get_post_type( get_the_ID() ) !== 'product' ) {
	esc_html_e( 'Post type is not product', 'easycommerce' );
	return;
}

use EasyCommerce\Models\Product;

if ( $product = new Product( get_the_ID() ) ) {
	$has_gallery = false;
	if ( $gallery = $product->get_gallery() ) {
		$unique_images = array();
		foreach ( $gallery as $image ) {
			if ( empty( $image['url'] ) ) {
				continue;
			}
			$parent_id = $image['parent_id'];
			if ( ! isset( $unique_images[ $image['url'] ] ) ) {
				$unique_images[ $image['url'] ] = array(
					'image'      => $image,
					'parent_ids' => array(),
				);
			}
			$unique_images[ $image['url'] ]['parent_ids'][] = $parent_id;
		}
		$has_gallery = ! empty( $unique_images );
		?>
		
		<!-- Product gallery features image slider -->
		<div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff; margin-bottom: 24px;" class="swiper mySwiper2 easycommerce-single-product-gallery-feature-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $unique_images as $data ) { ?>
					<div class="swiper-slide" data-id='<?php echo esc_attr( json_encode( $data['parent_ids'] ) ); ?>'>
						<img src="<?php echo esc_url( $data['image']['url'] ); ?>" alt="<?php echo esc_attr( $data['image']['alt'] ?? __( 'Product Image', 'easycommerce' ) ); ?>">
					</div>
				<?php } ?>
			</div>
		</div>

		<!-- Product gallery images slider -->
		<div thumbsSlider="" class="swiper mySwiper easycommerce-single-product-gallery-items-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $unique_images as $data ) { ?>
					<div class="swiper-slide easycommerce-single-product-gallery-item" data-id='<?php echo esc_attr( json_encode( $data['parent_ids'] ) ); ?>'>
						<img src="<?php echo esc_url( $data['image']['thumbnail'] ); ?>" alt="<?php echo esc_attr( $data['image']['alt'] ?? __( 'Product Image', 'easycommerce' ) ); ?>">
					</div>
				<?php } ?>
			</div>
			<div class="swiper-button-next easycommerce-single-product-gallery-next-prev-btn"></div>
			<div class="swiper-button-prev easycommerce-single-product-gallery-next-prev-btn"></div>
		</div>
		<div class="easycommerce-gallery_item" galleryItem="4"></div>
		<?php
	}

	if ( ! $has_gallery ) {
		?>
		<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/product/single-product-placeholder.png' ); ?>" alt="<?php esc_attr_e( 'Product Placeholder', 'easycommerce' ); ?>">
		<?php
	}
}
?>

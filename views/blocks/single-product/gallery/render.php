<?php
/**
 * Render callback for the EasyCommerce Product Gallery block.
 *
 * Displays the product image gallery with swiper sliders for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 * @var string $attributes['GalleryItem'] Number of gallery items to display.
 */
if ( get_post_type( get_the_ID() ) !== 'product' ) {
    esc_html_e( 'Post type is not product', 'easycommerce' );
    return;
}

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product;

$settings         = $attributes;
$gallery_item     = isset( $settings['GalleryItem'] ) ? $settings['GalleryItem'] : '4';
$show_stock_badge = easycommerce_is_stock_badge_enabled();

if ( $product = new Product( get_the_ID() ) ) {
    $get_thumbnail = $product->get_thumbnail();
    if ( ! empty( $gallery = $product->get_gallery() ) ) {
        $unique_images = [];
        if (! empty( $get_thumbnail['url']) ) {
            $unique_images[ $get_thumbnail['url'] ] = [
                'image'      => $get_thumbnail,
                'parent_ids' => [ get_the_ID() ],
            ];
        }
        foreach ( $gallery as $image ) {
            if ( empty( $image['url'] ) ) {
                continue;
            }
            $parent_id = $image['parent_id'];
            if ( ! isset( $unique_images[ $image['url'] ] ) ) {
                $unique_images[ $image['url'] ] = [
                    'image'      => $image,
                    'parent_ids' => [],
                ];
            }
            $unique_images[ $image['url'] ]['parent_ids'][] = $parent_id;
        }
        ?>

        <!-- Product gallery features image slider -->
        <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff; margin-bottom: 24px;" class="swiper mySwiper2 easycommerce-single-product-gallery-feature-slider">
            <div class="swiper-wrapper">
                <?php foreach ( $unique_images as $data ) { ?>
                    <div class="swiper-slide easycommerce-gallery-main-image relative" data-id='<?php echo esc_attr( json_encode( $data['parent_ids'] ) ); ?>'>
                        <?php if ( $show_stock_badge ) : 
                            $stock = $product->get_stock();
                            if ( $stock !== false && $stock !== null ) :
                                if ( $stock > 0 ) : ?>
                                    <span class="absolute top-3 left-3 z-10 inline-flex items-center bg-emerald-500 text-white text-xs font-semibold px-2.5 py-1 shadow-md">
                                        <?php esc_html_e( 'In Stock', 'easycommerce' ); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="absolute top-3 left-3 z-10 inline-flex items-center bg-red-500 text-white text-xs font-semibold px-2.5 py-1 shadow-md">
                                        <?php esc_html_e( 'Out of Stock', 'easycommerce' ); ?>
                                    </span>
                                <?php endif;
                            endif; 
                        endif; ?>
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
                        <img src="<?php echo esc_url( $data['image']['url'] ); ?>" alt="<?php echo esc_attr( $data['image']['alt'] ?? __( 'Product Image', 'easycommerce' ) ); ?>">
                    </div>
                <?php } ?>
            </div>
            <div class="swiper-button-next easycommerce-single-product-gallery-next-prev-btn"></div>
            <div class="swiper-button-prev easycommerce-single-product-gallery-next-prev-btn"></div>
        </div>

        <div class="easycommerce-gallery_item" galleryItem="<?php echo esc_attr( $gallery_item ); ?>"></div>

        <?php
    } else {
        ?>
        <img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/product/single-product-placeholder.png' ); ?>" alt="<?php esc_attr_e( 'Product Placeholder', 'easycommerce' ); ?>">
        <?php
    }
}

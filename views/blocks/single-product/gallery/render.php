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
    $badges        = $product->get_badges();
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
        <div class="relative">
            <?php
                $stock            = $product->get_stock();
                $is_out_of_stock  = ( $stock !== false && $stock !== null && $stock <= 0 );

                $other_badges = array_filter(
                    $badges,
                    function ( $badge ) {
                        return $badge['type'] !== 'out_of_stock';
                    }
                );

                if ( $show_stock_badge && ! $is_out_of_stock ) {
                    $other_badges[] = array(
                        'label'      => __( 'In Stock', 'easycommerce' ),
                        'color'      => '#10B981', 
                        'text_color' => '#FFFFFF',
                    );
                }
            ?>

            <?php if ( ! empty( $other_badges ) ) : ?>
                <div class="absolute top-3 left-3 z-10 flex flex-row flex-wrap items-center gap-1.5 max-w-[calc(100%-1.5rem)] pointer-events-none">
                    <?php foreach ( $other_badges as $badge ) : ?>
                        <span
                            class="inline-flex items-center text-xs font-semibold px-2.5 py-1 shadow-md rounded"
                            style="background-color: <?php echo esc_attr( $badge['color'] ); ?>; color: <?php echo esc_attr( $badge['text_color'] ); ?>;"
                        >
                            <?php echo esc_html( $badge['label'] ); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( $show_stock_badge && $is_out_of_stock ) : ?>
                <div class="absolute inset-0 z-20 flex items-center justify-center bg-black/40 pointer-events-none">
                    <span class="inline-flex items-center text-xs font-semibold px-3 py-1.5 shadow-md rounded bg-gray-700 text-white">
                        <?php esc_html_e( 'Out of Stock', 'easycommerce' ); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff; margin-bottom: 24px;" class="swiper mySwiper2 easycommerce-single-product-gallery-feature-slider">
                <div class="swiper-wrapper">
                    <?php foreach ( $unique_images as $data ) { ?>
                        <div class="swiper-slide easycommerce-gallery-main-image relative" data-id='<?php echo esc_attr( json_encode( $data['parent_ids'] ) ); ?>'>
                            <img src="<?php echo esc_url( $data['image']['url'] ); ?>" alt="<?php echo esc_attr( $data['image']['alt'] ?? __( 'Product Image', 'easycommerce' ) ); ?>">
                        </div>
                    <?php } ?>
                </div>
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

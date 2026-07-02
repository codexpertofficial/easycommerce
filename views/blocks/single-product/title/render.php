<?php
/**
 * Render callback for the EasyCommerce Product Title block.
 *
 * Displays the product title with customizable styling for a single product.
 *
 * @var array $attributes Block attributes passed from the block editor.
 * @var string $attributes['color'] Title text color.
 * @var string $attributes['fontSize'] Title font size.
 * @var string $attributes['fontWeight'] Title font weight.
 * @var string $attributes['textTransform'] Title text transform.
 * @var string $attributes['fontStyle'] Title font style.
 * @var string $attributes['decoration'] Title text decoration.
 * @var string $attributes['lineHeight'] Title line height.
 * @var string $attributes['spacing'] Title letter spacing.
 */
if( get_post_type( get_the_ID() ) !== 'product' ) {
    echo "Post type is not product";
    return;
}

$settings = $attributes;

$color          = isset( $settings['color'] ) ? $settings['color'] : 'var(--color-ec-body)';
$fontSize       = isset( $settings['fontSize'] ) ? $settings['fontSize'] : '30';
$fontWeight     = isset( $settings['fontWeight'] ) ? $settings['fontWeight'] : '';
$textTransform  = isset( $settings['textTransform'] ) ? $settings['textTransform'] : '';
$fontStyle      = isset( $settings['fontStyle'] ) ? $settings['fontStyle'] : '';
$decoration     = isset( $settings['decoration'] ) ? $settings['decoration'] : '';
$lineHeight     = isset( $settings['lineHeight'] ) ? $settings['lineHeight'] : '40';
$spacing        = isset( $settings['spacing'] ) ? $settings['spacing'] : '';

?>

<div <?php echo get_block_wrapper_attributes(); ?>>
    <h1 
        class="text-ec-body font-inter font-medium text-[30px] leading-10" 
        style="
            color: <?php echo esc_attr( $color ); ?>;
            font-size: <?php echo esc_attr( $fontSize ); ?>px;
            font-weight: <?php echo esc_attr( $fontWeight ); ?>;
            text-transform: <?php echo esc_attr( $textTransform ); ?>;
            text-decoration: <?php echo esc_attr( $decoration ); ?>;
            line-height: <?php echo esc_attr( $lineHeight ); ?>px;
            letter-spacing: <?php echo esc_attr( $spacing ); ?>px;
            "
    >
        <?php echo get_the_title(); ?>
    </h1>
</div>

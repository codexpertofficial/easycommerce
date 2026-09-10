<?php
/**
 * Render callback for the EasyCommerce Checkout block.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */

defined( 'ABSPATH' ) || exit;

$shortcode_atts = '';

// An empty attribute means "use the checkout settings", so it is not forwarded.
if ( ! empty( $attributes['template'] ) ) {
	$shortcode_atts .= sprintf( ' template="%s"', esc_attr( $attributes['template'] ) );
}

if ( ! empty( $attributes['columns'] ) ) {
	$shortcode_atts .= sprintf( ' columns="%s"', esc_attr( $attributes['columns'] ) );
}
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes this. ?>>
	<?php echo do_shortcode( "[easycommerce-checkout{$shortcode_atts}]" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The checkout templates escape their own output. ?>
</div>

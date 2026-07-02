<?php
/**
 * Storefront shell - opening. Pair with layout-footer.php.
 *
 * Every storefront surface renders between these two partials - the product
 * archive and single product (via template_include) and the "EasyCommerce Full
 * Width Layout" page template used by the shop, checkout and dashboard pages.
 * Theme chrome, the root wrapper and the width-bound content container are
 * defined here once, so all EasyCommerce pages share one layout - same
 * background, width and page-title treatment - on (almost) any theme.
 *
 * Rendered inline (not buffered) so storefront content that calls exit() - e.g.
 * the store-mode gate - still leaves the shell header/container in place.
 *
 * Container styling lives in assets/public/css/theme-compat.css; width and
 * background are passed through the --ec-storefront-width / --ec-storefront-bg
 * custom properties printed below.
 *
 * Hooks:
 * - filter easycommerce_storefront_width      ( string $width )   e.g. "1200px", "90%"
 * - filter easycommerce_storefront_background ( string $color )   storefront background
 * - filter easycommerce_storefront_classes    ( array  $classes ) root wrapper classes
 * - action easycommerce_before_storefront     ( after get_header, before the wrapper )
 * - action easycommerce_storefront_start      ( inside the container, before content )
 */

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

// Content width - single source of truth, filterable.
$ec_width = trim( (string) Utility::get_option( 'general', 'store', 'template-width', 1200 ) );
if ( '' === $ec_width ) {
	$ec_width = '1200';
}
// Normalize a bare number to px; let explicit units (px, %, rem, ...) pass through.
if ( is_numeric( $ec_width ) ) {
	$ec_width .= 'px';
}
$ec_width = apply_filters( 'easycommerce_storefront_width', $ec_width );

// Storefront background - consistent across every page, filterable.
$ec_bg = apply_filters( 'easycommerce_storefront_background', '#ffffff' );

// Root wrapper classes - filterable.
$ec_classes = apply_filters( 'easycommerce_storefront_classes', array( 'easycommerce-storefront', 'ec-root' ) );
$ec_classes = implode( ' ', array_map( 'sanitize_html_class', (array) $ec_classes ) );

get_header();

do_action( 'easycommerce_before_storefront' );
?>
<style>:root{--ec-storefront-width:<?php echo esc_attr( $ec_width ); ?>;--ec-storefront-bg:<?php echo esc_attr( $ec_bg ); ?>;}</style>
<div class="<?php echo esc_attr( $ec_classes ); ?>">
	<div class="easycommerce-full-width-container">
		<?php do_action( 'easycommerce_storefront_start' ); ?>

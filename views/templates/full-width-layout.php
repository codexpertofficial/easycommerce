<?php
/**
 * Template Name: EasyCommerce Full Width Layout
 *
 * Used by the shop, checkout and dashboard pages. Renders the page content
 * between the shared storefront shell partials (layout-header.php /
 * layout-footer.php) so every EasyCommerce page shares one wrapper, container
 * and width.
 */
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

echo Utility::get_template( 'templates/layout-header.php' );

while ( have_posts() ) :
	the_post();

	// Starter-design pages ship their own hero heading; the page title would
	// duplicate it right above the hero.
	if ( ! get_post_meta( get_the_ID(), '_easycommerce_design_page', true ) ) :
		?>
		<h1 class="easycommerce-page-title"><?php the_title(); ?></h1>
		<?php
	endif;

	the_content();
endwhile;

echo Utility::get_template( 'templates/layout-footer.php' );

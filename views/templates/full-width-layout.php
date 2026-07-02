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
	?>
	<h1 class="easycommerce-page-title"><?php the_title(); ?></h1>
	<?php
	the_content();
endwhile;

echo Utility::get_template( 'templates/layout-footer.php' );

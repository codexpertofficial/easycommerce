<?php
/**
 * Shop template dispatcher for Template 3.
 *
 * Includes the appropriate partial template based on the view type (grid or list).
 *
 * @var array $args Template arguments.
 * @var string $args['view'] View type ('easycommerce-st-list' or 'easycommerce-st-grid').
 */
if ( $args['view'] === 'easycommerce-st-list' ) {
		require 'partials/product-list.php';
	}
	if ( $args['view'] === 'easycommerce-st-grid' ) {
		require 'partials/product-grid.php';
	}
?>

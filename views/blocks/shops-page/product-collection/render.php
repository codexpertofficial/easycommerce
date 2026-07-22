<?php
/**
 * Render callback for the EasyCommerce Product Collection block.
 *
 * Renders a configurable product grid (featured / best sellers / new arrivals /
 * all, optionally filtered to a category) reusing the shared shop product-card
 * markup. Self-rendering so it can be embedded safely inside static patterns.
 *
 * @var array $attributes Block attributes passed from the block editor.
 */

use EasyCommerce\Models\Product as Product_Model;

$settings = is_array( $attributes ) ? $attributes : array();

$source   = isset( $settings['source'] ) ? sanitize_key( $settings['source'] ) : 'featured';
$category = isset( $settings['category'] ) ? sanitize_text_field( $settings['category'] ) : '';
$count    = isset( $settings['count'] ) ? max( 1, intval( $settings['count'] ) ) : 4;
$columns  = isset( $settings['columns'] ) ? max( 1, intval( $settings['columns'] ) ) : 4;

// Normalise columns onto the shared shop settings key used by shop.php.
$settings['columns'] = $columns;

// Map the block source to Product::list() sort keys.
// `featured` has no dedicated store concept yet, so it shows a price-led
// "premium picks" order - always populated (every product has a price row),
// and deliberately different from `newest` so a homepage using both grids
// doesn't render the same products twice on a fresh store.
$sort_map = array(
	'featured'     => 'high-to-low',
	'best-selling' => 'best-selling',
	'newest'       => 'latest',
	'all'          => '',
);
$sort_by = isset( $sort_map[ $source ] ) ? $sort_map[ $source ] : '';

/**
 * Filter the resolved sort key for a Product Collection source.
 *
 * Lets add-ons implement a real "featured" query without changing core.
 *
 * @param string $sort_by  Resolved Product::list() sort key.
 * @param string $source   Block source attribute.
 * @param array  $settings Block attributes.
 */
$sort_by = apply_filters( 'easycommerce_product_collection_sort_by', $sort_by, $source, $settings );

$filters = array( 'is_shop' => 1 );

if ( $sort_by ) {
	$filters['sort_by'] = $sort_by;
}

if ( '' !== $category ) {
	$field = ctype_digit( $category ) ? 'term_id' : 'slug';

	$filters['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => $field,
			'terms'    => $category,
		),
	);
}

/**
 * Filter the Product::list() query filters for a Product Collection block.
 *
 * @param array  $filters  Filters passed to Product::list().
 * @param array  $settings Block attributes.
 */
$filters = apply_filters( 'easycommerce_product_collection_filters', $filters, $settings );

$products_data = Product_Model::list( $filters, $count, 0, true, true );
$products      = isset( $products_data['products'] ) ? $products_data['products'] : array();

// Backfill: a meta-ordered sort (e.g. best-selling, which orders by the
// `total_sale` meta) silently excludes products that have never sold. On a fresh
// store that leaves the grid empty. Retry without the sort so the grid still
// populates whenever the store actually has matching products.
if ( empty( $products ) && ! empty( $filters['sort_by'] ) ) {
	$fallback_filters = $filters;
	unset( $fallback_filters['sort_by'] );

	// Stagger the best-sellers backfill past the first slice so it doesn't
	// mirror the `newest` grid while the store has no sales signal yet.
	$fallback_offset = ( 'best-selling' === $source ) ? $count : 0;

	$products_data = Product_Model::list( $fallback_filters, $count, $fallback_offset, true, true );
	$products      = isset( $products_data['products'] ) ? $products_data['products'] : array();

	// Small catalogue (fewer products than the offset skips) - take the first
	// slice after all; a duplicated grid beats an empty one.
	if ( empty( $products ) && $fallback_offset ) {
		$products_data = Product_Model::list( $fallback_filters, $count, 0, true, true );
		$products      = isset( $products_data['products'] ) ? $products_data['products'] : array();
	}
}

// Empty state — never render a void grid.
if ( empty( $products ) ) {
	$add_url    = current_user_can( 'manage_easycommerce' ) ? admin_url( 'admin.php?page=easycommerce#/products/new' ) : '';
	?>
	<div class="ec-product-collection ec-product-collection--empty">
		<p class="ec-product-collection__empty-text">
			<?php esc_html_e( 'No products to show here yet.', 'easycommerce' ); ?>
		</p>
		<?php if ( $add_url ) : ?>
			<a class="ec-product-collection__empty-link" href="<?php echo esc_url( $add_url ); ?>">
				<?php esc_html_e( 'Add your first product', 'easycommerce' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
	return;
}

// Shape products the same way the shop templates do, then reuse the shared card markup.
$formatted_products = array_map(
	function ( $product ) {
		return array(
			'id'                   => $product->get_id(),
			'title'                => $product->get_title(),
			'slug'                 => $product->get_slug(),
			'description'          => $product->get_description(),
			'summary'              => $product->get_summary(),
			'status'               => $product->get_status(),
			'link'                 => $product->get_url(),
			'thumbnail'            => $product->get_thumbnail( 'easycommerce-shop-thumbnail' ),
			'rating'               => $product->get_rating(),
			'rating_count'         => $product->get_rating_count(),
			'price'                => $product->get_price(),
			'sale_price'           => $product->get_sale_price(),
			'formatted_price'      => $product->get_price( false ),
			'formatted_sale_price' => $product->get_sale_price( false ),
			'stock'                => $product->get_stock(),
			'categories'           => $product->get_categories(),
			'tags'                 => $product->get_tags(),
			'brands'               => $product->get_brands(),
			'sales'                => $product->get_sales(),
			'attributes'           => $product->get_attributes(),
			'is_variable'          => $product->is_variable(),
		);
	},
	$products
);

$allowed_column_classes = array(
	1 => 'grid-cols-1',
	2 => 'grid-cols-2',
	3 => 'grid-cols-3',
	4 => 'grid-cols-4',
	5 => 'grid-cols-5',
	6 => 'grid-cols-6',
);
$grid_class = $allowed_column_classes[ $columns ] ?? 'grid-cols-4';
?>
<div class="ec-product-collection easycommerce-shop-container-front">
	<div class="w-full easycommerce-shop-container grid <?php echo esc_attr( $grid_class ); ?> gap-[30px]" style="display:grid;grid-template-columns:repeat(<?php echo esc_attr( $columns ); ?>, minmax(0, 1fr));gap:30px;">
		<?php do_action( 'easycommerce/views/blocks/shop', $settings, $formatted_products, 'template-1', 'ec-product-collection' ); ?>
	</div>
</div>

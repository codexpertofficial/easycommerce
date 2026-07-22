<?php
/**
 * Filter sidebar template for Shop Template 2.
 *
 * Displays categories, brands, attributes, price range, and sort options for filtering products.
 *
 * @global array $categories Product categories.
 * @global array $brands Product brands.
 * @global array $attributes Product attributes.
 * @global int $min_price Minimum product price.
 * @global int $max_price Maximum product price.
 */
use EasyCommerce\Models\Attribute_Value;
if ( ! function_exists( 'display_attribute' ) ) {

	function display_attribute( $attributes, $type ) {

		if ( empty( $attributes ) ) return;

		echo '<div class="flex flex-col gap-5">';
		foreach ( $attributes as $attribute ) {
			echo '<div class="easycommerce-accordion flex justify-between items-center">';
			echo '<label class="flex items-center">';
			echo '<input type="checkbox" class="easycommerce-input-checkoutbox" name="'.  $type .'_' . $attribute['slug'] . '" value="' . $attribute['slug'] . '" />';
			echo '<span class="ml-2">' . esc_html( substr( $attribute['name'], 0, 35 ) . '...' ) . '</span>';
			echo '</label>';
			echo '<div class="bg-[#F8F8F8] text-ec-body py-[2px] px-2 rounded-full text-xs leading-5 font-inter font-medium">';
			echo isset( $attribute['count'] ) ? esc_html( $attribute['count'] ) : 0;
			echo '</div>';
			echo '</div>';

			if ( ! empty( $attribute['children'] ) ) {
				echo '<div class="pl-4">';
				display_attribute( $attribute['children'], $type );
				echo '</div>';
			}
		}
		echo '</div>';
	}
}
?>
<div class="easycommerce-drawer-wrap mb-10 lg:w-[300px] w-[190px]">

	<div class="easycommerce-drawer-header-space" style="height: 50px;"></div>
	<div class="easycommerce-drawer-product-search relative">
		<img 
			src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/product-search.png' ); ?>" 
			alt="<?php esc_attr_e( 'Search Icon', 'easycommerce' ); ?>"
			class="absolute top-3 left-5"
		/>
		<input
			type="text"
			class="easycommerce-product-search border border-ec-border pl-14 pr-2 py-1 w-full rounded-lg hover:border-ec-secondary focus:border-ec-primary"
			placeholder="<?php esc_attr_e( 'Search', 'easycommerce' ); ?>"
		>
	</div>

	<div class="easycommerce-drawer-cross-icon bg-[#F8F8F8] border-b border-ec-border p-4">

		<div class="flex items-center gap-[6px]">
			
			<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path clip-rule="evenodd" fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"></path>
			</svg>
			<h3 class="!m-0">
				<?php esc_html_e( 'Filter', 'easycommerce' ); ?>
			</h3>
		</div>
		
		<div class="easycommerce-drawer-close w-8 h-8 border border-ec-border cursor-pointer rounded-full bg-ec-border flex items-center justify-center">
			<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path clip-rule="evenodd" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path>
			</svg>
		</div>
	</div>

	<!-- Filters Section -->
	<div class="border-b border-ec-border">
		<h5 class="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none py-6" onclick="toggleAccordion('filters')">
			<?php esc_html_e( 'Filters', 'easycommerce' ); ?>
			<span id="arrow-filters" class="transform transition-transform">
				<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
				</svg>
			</span>
		</h5>

		<div id="filters" class="accordion-content">
			<div class="pb-8 flex flex-col gap-5">
				<?php
				$sort_options = easycommerce_product_sort_options();
				foreach ( $sort_options as $key => $option ) {
					if ( $key == 'on-sale' ) continue;
					printf(
						'<div class="flex justify-between">
								<label class="flex items-center gap-2">
									<input type="radio" class="easycommerce-shop-page-input-type-radio" name="filter" value="%1$s" />
									%2$s
								</label>
							</div>',
						$key,
						$option
					);
				}
				?>
			</div>
		</div>
	</div>
	<!-- Categories Section -->
	<?php
	if ( ! empty( $categories ) ) {
		?>
			<div class="border-b border-ec-border pb-6">
				<h5 class="easycommerce-filter-heading text-base font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none leading-[26px] easycommerce-categories-title pt-6" onclick="toggleAccordion('categories')">
					<?php esc_html_e( 'Categories', 'easycommerce' ); ?>
					<span id="arrow-categories" class="transform transition-transform">
						<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
						</svg>
					</span>

				</h5>
				<div id="categories" class="accordion-content">
					<div class="pt-6 easycommerce-categories-container">
						<?php display_attribute( $categories, 'category' ); ?>
					</div>
				</div>
			</div>
			<?php
	}
	?>
				
	<!-- Price Slider Section -->
	<div class="py-3 border-b border-ec-border">
		<h5 class="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center shadow-none py-3" onclick="toggleAccordion('price-slider')">
			<?php esc_html_e( 'Price', 'easycommerce' ); ?>
			<span id="arrow-price-slider" class="transform transition-transform">
				<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
				</svg>
			</span>
		</h5>
		<div id="price-slider" class="flex-col gap-5 accordion-content">
			<div class="bg-white rounded-md pt-3 mt-4">
				<div class="relative h-2 bg-gray-200 rounded">
					<div class="absolute h-full bg-ec-primary rounded w-full"></div>
					<div class="slider">
						<div class="absolute h-full bg-ec-primary rounded"></div>
						<div class="easycommerce-handle-min cursor-pointer"></div>
						<div class="absolute w-6 h-6 bg-white border-4 border-ec-primary rounded-full cursor-pointer -top-2 easycommerce-handle-max" style="left: 80%;"></div>
					</div>
				</div>
				<div class="flex justify-between mt-4 gap-10">
					<div class="flex flex-col">
						<label class="text-[12px] mb-1 text-ec-placeholder">
							<?php esc_html_e( 'Min Price', 'easycommerce' ); ?>
						</label>
						<div class="relative">
							<input type="number" id="easycommerce-min-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr( $min_price ); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" />
							<span class="absolute inset-y-0 right-0 pr-3 flex items-center text-ec-secondary">$</span>
						</div>
					</div>
					<div class="flex flex-col">
						<label class="text-[12px] mb-1 text-ec-placeholder">
							<?php esc_html_e( 'Max Price', 'easycommerce' ); ?>
						</label>
						<div class="relative">
							<input type="number" id="easycommerce-max-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr( $max_price ); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" />
							<span class="absolute inset-y-0 right-0 pr-3 flex items-center text-ec-secondary">$</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="accordion-container">
		<?php foreach ( $attributes as $attribute ) : 
			$attribute_name         = $attribute->name;
			$attribute_slug         = $attribute->slug;
			$attribute_values_model = new Attribute_Value();
			$values                 = $attribute_values_model->get_by( $attribute->id );
			$attribute->values      = wp_list_pluck( $values, 'name', 'slug' );
			
			if( empty( $attribute_name ) || empty( $attribute->values ) ) continue;

			?>
			<div class="py-3 border-b-[1px] border-ec-border">
				<h5
					class="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none py-3"
					onclick="toggleAccordion('<?php echo esc_js( $attribute_name ); ?>')"
				>
					<?php echo esc_html( $attribute_name ); ?>
					<span id="arrow-<?php echo esc_attr( $attribute_name ); ?>" class="transform transition-transform">
						<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
						</svg>
					</span>
				</h5>
				<div id="<?php echo esc_attr( $attribute_name ); ?>" class="accordion-content">      
					<div class="py-6 flex flex-col gap-5">
						<?php
						foreach ( $values as $value_key => $value_obj ) :
							?>
							<div class="flex justify-between">
								<label class="flex items-center gap-2">
									<input type="checkbox" class="easycommerce-input-checkoutbox easycommerce-input-attribute" data-attribute="<?php echo esc_attr( $attribute_slug ); ?>" name="<?php echo esc_attr( $value_obj->slug ); ?>" value="<?php echo esc_attr( $value_obj->slug ); ?>" />
									<?php echo esc_html( $value_obj->name ); ?>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- Brand section -->
		<?php if ( ! empty( $brands ) ) : ?>
			<div class="py-3 border-b-[1px] border-ec-border">
				<h5 class="easycommerce-filter-heading text-[16px] font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none py-3" onclick="toggleAccordion('brands')">
					<?php esc_html_e( 'Brands', 'easycommerce' ); ?>
					<span id="arrow-brands" class="transform transition-transform">
						<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
						</svg>
					</span>
				</h5>
				<div id="brands" class="accordion-content">
					<div class="flex flex-col gap-5 py-4">
						<?php display_attribute( $brands, 'brand' ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
	let isToggled = false;

	function toggleAccordion(section) {
		const el = document.getElementById(section);
		const arrow = document.getElementById(`arrow-${section}`);

		// Toggle the "open" class to trigger the transition
		el.classList.toggle('open');
		arrow.classList.toggle('rotate-180');
	}

	/**
	 * Check if the height of each element exceeds 380px and add the class than add the scrolling class
	 */
	const accordionContents = document.querySelectorAll('.accordion-content');

	// Function to check if the height exceeds 380px and add the class
	function checkHeight(element) {
		if (element.scrollHeight > 380) {
			element.classList.add('easycommerce-categories-scrolling');
		} else {
			element.classList.remove('easycommerce-categories-scrolling');
		}
	}

	// Loop through each element and check its height
	accordionContents.forEach(checkHeight);

	// Optionally, use a MutationObserver to track changes in the content of the divs
	accordionContents.forEach((element) => {
		const observer = new MutationObserver(() => checkHeight(element));
		observer.observe(element, { childList: true, subtree: true });
	});
</script>

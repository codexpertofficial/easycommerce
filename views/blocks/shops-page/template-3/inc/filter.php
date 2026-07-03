<?php
/**
 * Filter sidebar template for Shop Template 3.
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
<!-- <div class="easycommerce-drawer-wrap-st flex gap-3"> -->
	<div class="easycommerce-drawer-wrap flex flex-col gap-3 mb-10 font-inter xl:w-[300px] w-[260px]">
		<div class="easycommerce-drawer-header-space" style="height: 50px;"></div>
		<div class="easycommerce-drawer-wrap-st self-end easycommerce-drawer-close w-8 h-8 border border-ec-border cursor-pointer rounded-full bg-white flex items-center justify-center">
			<svg class="w-5 h-[26px]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path clip-rule="evenodd" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path>
			</svg>
		</div>
		<!-- Categories Section -->
		<?php
		if ( ! empty( $categories ) ) {
			?>
				<div class="border border-ec-border rounded-t-lg">
					<h5 class="easycommerce-filter-heading p-4 bg-[#F8F8F8] text-base font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none leading-[26px] easycommerce-categories-title"
						onclick="toggleAccordion('categories')"
					>
						<?php esc_html_e( 'Categories', 'easycommerce' ); ?>
						<span id="arrow-categories" class="transition-all duration-300">
							<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</span>
					</h5>
					<div id="categories" class="accordion-content rounded-b-lg hidden">
						<div class="pt-6 p-4 easycommerce-categories-container">
							<div class="easycommerce-scroll-inner">
								<?php display_attribute( $categories, 'category' ); ?>
							</div>
						</div>
					</div>
				</div>

			<?php
		}
		?>		
		<!-- Price Slider Section -->
		<div class="border border-ec-border rounded-t-lg">
			<h5 class="easycommerce-filter-heading p-4 text-base bg-[#F8F8F8] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center shadow-none" onclick="toggleAccordion('price-slider')">
				<?php esc_html_e( 'Price', 'easycommerce' ); ?>
				<span id="arrow-price-slider" class="transform transition-transform">
					<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</h5>
			<div id="price-slider" class="flex-col gap-5 accordion-content rounded-b-lg">
				<div class="bg-white rounded-md p-3 mt-4">
					<div class="relative h-2 bg-[#272435] rounded">
						<div class="absolute h-full rounded w-full"></div>
						<div class="slider">
							<div class="absolute h-full bg-[#272435] rounded"></div>
							<div class="easycommerce-handle-min border-[#272435] cursor-pointer"></div>
							<div class="absolute w-6 h-6 bg-white border-4 border-[#272435] rounded-full cursor-pointer -top-2 easycommerce-handle-max" style="left: 80%;"></div>
						</div>
					</div>
					<div class="flex justify-between mt-4 gap-10">
						<div class="flex flex-col">
							<label class="text-[12px] mb-1 text-ec-placeholder">
								<?php esc_html_e( 'Min Price', 'easycommerce' ); ?>
							</label>
							<div class="relative flex items-center justify-normal">
								<input type="number" id="easycommerce-min-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr( $min_price ); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" />
								<span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#272435]">$</span>
							</div>
						</div>
						<div class="flex flex-col">
							<label class="text-[12px] mb-1 text-ec-placeholder">
								<?php esc_html_e( 'Max Price', 'easycommerce' ); ?>
							</label>
							<div class="relative flex items-center justify-normal">
								<input type="number" id="easycommerce-max-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr( $max_price ); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" />
								<span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#272435]">$</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="accordion-container flex flex-col gap-3">
			<?php foreach ( $attributes as $attribute ) : 
				$attribute_name         = $attribute->name;
				$attribute_slug         = $attribute->slug;
				$attribute_values_model = new Attribute_Value();
				$values                 = $attribute_values_model->get_by( $attribute->id );
				$attribute->values      = wp_list_pluck( $values, 'name', 'slug' );
				
				if( empty( $attribute_name ) || empty( $attribute->values ) ) continue;

				?>
				<div class="border border-ec-border rounded-t-lg">
					<h5
						class="easycommerce-filter-heading text-base font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none p-4 bg-[#F8F8F8]"
						onclick="toggleAccordion('<?php echo esc_js( $attribute_name ); ?>')"
					>
						<?php echo esc_html( $attribute_name ); ?>
						<span id="arrow-<?php echo esc_attr( $attribute_name ); ?>" class="transform transition-transform">
							<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</span>
					</h5>
					<div id="<?php echo esc_attr( $attribute_name ); ?>" class="accordion-content rounded-b-lg">
						<div class="p-4 flex flex-col gap-5 easycommerce-scroll-inner">
							<?php
							foreach ( $values as $value_key => $value_obj ) :
								?>
								<div class="flex justify-between">
									<label class="flex items-center gap-2">
										<input type="checkbox" class="easycommerce-input-checkoutbox template-3 easycommerce-input-attribute" data-attribute="<?php echo esc_attr( $attribute_slug ); ?>" name="<?php echo esc_attr( $value_obj->slug ); ?>" value="<?php echo esc_attr( $value_obj->slug ); ?>" />
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
				<div class="border border-ec-border rounded-t-lg">
					<h5 class="easycommerce-filter-heading text-base font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none p-4 bg-[#F8F8F8]" onclick="toggleAccordion('brands')">
						<?php esc_html_e( 'Brands', 'easycommerce' ); ?>
						<span id="arrow-brands" class="transform transition-transform">
							<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</span>
					</h5>
					<div id="brands" class="accordion-content ">
						<div class="flex flex-col gap-5 p-4">
							<div class="easycommerce-scroll-inner">
								<?php display_attribute( $brands, 'brand' ); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

<script>
	/**
	 * Check if the height of each element exceeds 380px and add the class than add the scrolling class
	 */
	const accordionContents = document.querySelectorAll('.easycommerce-scroll-inner');

	function checkHeight(element) {
		if (element.scrollHeight > 380) {
			element.classList.add('easycommerce-scroll-inner');
		} else {
			element.classList.remove('easycommerce-scroll-inner');
		}
	}

	accordionContents.forEach(checkHeight);

	accordionContents.forEach((element) => {
		const observer = new MutationObserver(() => checkHeight(element));
		observer.observe(element, { childList: true, subtree: true });
	});
</script>

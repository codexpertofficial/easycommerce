<?php

/**
 * Filter sidebar template for Shop Template 1.
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

if (! function_exists('display_attribute')) {

	function display_attribute($attributes, $type)
	{

		if (empty($attributes)) return;

		echo '<div class="flex flex-col gap-5">';
		foreach ($attributes as $attribute) {
			echo '<div class="easycommerce-accordion flex justify-between items-center">';
			echo '<label class="flex items-center">';
			echo '<input type="checkbox" class="easycommerce-input-checkoutbox" name="' .  $type . '_' . $attribute['slug'] . '" value="' . $attribute['slug'] . '" />';
			echo '<span class="ml-2">' . esc_html(substr($attribute['name'], 0, 35) . '...') . '</span>';
			echo '</label>';
			echo '<div class="bg-[#F8F8F8] text-ec-body py-[2px] px-2 rounded-full text-xs leading-5 font-inter font-medium">';
			echo isset($attribute['count']) ? esc_html($attribute['count']) : 0;
			echo '</div>';
			echo '</div>';

			if (! empty($attribute['children'])) {
				echo '<div class="pl-4">';
				display_attribute($attribute['children'], $type);
				echo '</div>';
			}
		}
		echo '</div>';
	}
}
?>

<div class="w-full hidden easycommerce-drawer-wrap mb-10 lg:flex align-center justify-between gap-4" style="width: 100%;">
	<div class="w-[65%] flex items-center gap-5 relative">
		<div class="left-blur"></div>
		<button class="scroll-left absolute left-0 top-1/2 transform -translate-y-1/2 z-10 flex items-center justify-center">
			<svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5 9L1 5L5 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
		</button>
		<div class="flex gap-5 scroll-content ">
			<!-- Categories Section -->
			<?php
			if (! empty($categories)) {
			?>
				<div class="">
					<div class="py-1 px-4 bg-[#FFFFFF] border border-[#EBEBEB] rounded-full  easycommerce-categories  relative">
						<h5 class="easycommerce-filter-heading text-base font-normal cursor-pointer text-ec-title w-full flex justify-between items-center shadow-none leading-[26px] easycommerce-categories-title " onclick="toggleAccordion('categories')">
							<?php esc_html_e('Categories', 'easycommerce'); ?>
							<span id="arrow-categories" class="transform transition-transform ml-2">
								<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
								</svg>
							</span>
						</h5>
					</div>

					<div id="categories" class="accordion-content hidden absolute top-[50px] bg-white rounded-xl p-6 border border-ec-border w-[210px]">
						<div class="pt-6 easycommerce-categories-container easycommerce-categories-accordion">
							<div class="easycommerce-scroll-inner">
								<?php display_attribute($categories, 'category'); ?>
							</div>
						</div>
					</div>
				</div>
			<?php
			}
			?>

			<!-- Brand section -->
			<?php if (! empty($brands)) : ?>
				<div class="">
					<div class="easycommerce-brands py-1 px-4 bg-[#FFFFFF] border border-[#EBEBEB]  rounded-full  relative">
						<h5 class="easycommerce-filter-heading text-base font-normal leading-[26px] cursor-pointer text-ec-title w-full flex justify-between items-center shadow-none" onclick="toggleAccordion('brands')">
							<?php esc_html_e('Brands', 'easycommerce'); ?>
							<span id="arrow-brands" class="transform transition-transform ml-2">
								<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
								</svg>
							</span>
						</h5>
					</div>

					<div id="brands" class="accordion-content hidden absolute top-[50px] bg-white rounded-xl p-6 border border-ec-border w-[210px]">
						<div class="flex flex-col gap-5 py-4 easycommerce-brands-accordion">
							<div class="easycommerce-scroll-inner">
								<?php display_attribute($brands, 'brand'); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Price Slider Section -->
			<div class="">
				<div class="py-1 px-4 bg-[#FFFFFF] border border-[#EBEBEB]  rounded-full easycommerce-prices relative">
					<h5 class=" easycommerce-filter-heading text-base font-normal leading-[26px] text-ec-title cursor-pointer colorec-body w-full flex justify-between items-center shadow-none" onclick="toggleAccordion('price-slider')">
						<?php esc_html_e('Price', 'easycommerce'); ?>
						<span id="arrow-price-slider" class="transform transition-transform ml-2">
							<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
							</svg>
						</span>
					</h5>
				</div>

				<div id="price-slider" class="flex-col gap-5 accordion-content hidden absolute top-[50px] bg-white rounded-xl p-6 border border-ec-border w-max">
					<div class="bg-white rounded-md mt-4 easycommerce-prices-accordion">
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
									<?php esc_html_e('Min Price', 'easycommerce'); ?>
								</label>
								<div class="relative">
									<input type="number" id="easycommerce-min-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr($min_price); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>" />
									<span class="absolute top-[7px] inset-y-0 right-0 pr-3 flex items-center text-ec-secondary">$</span>
								</div>
							</div>
							<div class="flex flex-col">
								<label class="text-[12px] mb-1 text-ec-placeholder">
									<?php esc_html_e('Max Price', 'easycommerce'); ?>
								</label>
								<div class="relative">
									<input type="number" id="easycommerce-max-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr($max_price); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>" />
									<span class="absolute top-[7px] inset-y-0 right-0 pr-3 flex items-center text-ec-secondary">$</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="accordion-container flex gap-5">
				<?php foreach ($attributes as $attribute) :
					$attribute_name         = $attribute->name;
					$attribute_slug         = $attribute->slug;
					$attribute_values_model = new Attribute_Value();
					$values                 = $attribute_values_model->get_by($attribute->id);
					$attribute->values      = wp_list_pluck($values, 'name', 'slug');

					if (empty($attribute_name) || empty($attribute->values)) continue;

				?>
					<div class="">
						<div class="py-1 px-4 bg-[#FFFFFF] border border-[#EBEBEB] rounded-full relative easycommerce-attributes">
							<h5
								class="easycommerce-attributes-heading easycommerce-filter-heading text-base font-normal leading-[26px] text-ec-title cursor-pointer w-full flex justify-between items-center shadow-none"
								onclick="toggleAccordion('<?php echo esc_js($attribute_name); ?>')">
								<?php echo esc_html($attribute_name); ?>
								<span id="arrow-<?php echo esc_attr($attribute_name); ?>" class="transform transition-transform ml-2">
									<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
									</svg>
								</span>
							</h5>
						</div>

						<div id="<?php echo esc_attr($attribute_name); ?>" class="accordion-content hidden absolute top-[50px] bg-white rounded-xl p-6 border border-ec-border w-[210px]">
							<div class="flex flex-col gap-5 easycommerce-attributes-accordion">
								<?php
								foreach ($values as $value_key => $value_obj) :
								?>
									<div class="flex justify-between">
										<label class="flex items-center gap-2">
											<input type="checkbox" class="easycommerce-input-checkoutbox easycommerce-input-attribute" data-attribute="<?php echo esc_attr($attribute_slug); ?>" name="<?php echo esc_attr($value_obj->slug); ?>" value="<?php echo esc_attr($value_obj->slug); ?>" />
											<?php echo esc_html($value_obj->name); ?>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="right-blur"></div>
		<button class="scroll-right absolute right-0 top-1/2 transform -translate-y-1/2  z-10  flex items-center justify-center">
			<svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M1 1L5 5L1 9" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
		</button>

	</div>

	<div class="w-[30%] flex gap-4">
		<div class="easycommerce-drawer-header-space" style="height: 50px;"></div>
		<div class="w-[70%] easycommerce-drawer-product-search relative">
			<img
				src="<?php echo esc_url(EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/product-search.png'); ?>"
				alt="Search Icon"
				class="search-icon" />
			<input
				type="text"
				class="easycommerce-product-search easycommerce-shop-search border border-ec-border pl-10 pr-2 py-1 w-full rounded-full hover:border-ec-secondary focus:border-ec-primary  placeholder:text-ec-light-black focus:outline-none"
				placeholder="Search...">
		</div>
		<div class="easycommerce-drawer-cross-icon bg-[#F8F8F8] border-b border-ec-border p-4">
			<div class="flex items-center gap-[6px]">
				<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path clip-rule="evenodd" fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"></path>
				</svg>
				<h3 class="!m-0">
					<?php esc_html_e('Sort by', 'easycommerce'); ?>
				</h3>
			</div>

			<div class="easycommerce-drawer-close w-8 h-8 border border-ec-border cursor-pointer rounded-full bg-ec-border flex items-center justify-center">
				<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path clip-rule="evenodd" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path>
				</svg>
			</div>
		</div>

		<!-- Filters Section -->

		<div class="w-[45%] easycommerce-filters relative">
			<div class="py-[3px] px-4 border border-ec-border rounded-full h-full">
				<h5 class="relative easycommerce-filter-heading text-base h-full font-normal leading-[26px] text-ec-light-black cursor-pointer w-full flex justify-between items-center shadow-none" onclick="toggleAccordion('filters')">
					<?php esc_html_e('Sort by', 'easycommerce'); ?>
					<span id="arrow-filters" class="transform transition-transform ml-2">
						<svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
						</svg>
					</span>
				</h5>
			</div>


			<div id="filters" class="accordion-content hidden absolute top-[50px] bg-white rounded-xl p-6 border border-ec-border w-[210px]">
				<div class="pb-8 flex flex-col gap-5 easycommerce-filters-accordion">
					<?php
					$sort_options = easycommerce_product_sort_options();
					foreach ($sort_options as $key => $option) {
						if ($key == 'on-sale') continue;
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
	</div>
</div>

<div class="easycommerce-drawer-wrap easycommerce-drawer-wrap-mobile lg:hidden flex flex-col gap-3 mb-10 font-inter w-[300px]">

	<div class="easycommerce-drawer-header-space" style="height: 50px;"></div>

	<div class="easycommerce-drawer-wrap-st self-end easycommerce-drawer-close w-8 h-8 border border-ec-border cursor-pointer rounded-full bg-white flex items-center justify-center">
		<svg class="w-5 h-[26px]" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path clip-rule="evenodd" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path>
		</svg>
	</div>

	<div class="easycommerce-drawer-product-search easycommerce-drawer-shop-search w-full relative">
		<img
			src="<?php echo esc_url(EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/product-search.png'); ?>"
			alt="Search Icon"
			class="search-icon" />
		<input
			type="text"
			class="easycommerce-product-search easycommerce-shop-search border border-ec-border pl-10 pr-2 py-1 w-full rounded-lg hover:border-ec-secondary focus:border-ec-primary  placeholder:text-ec-light-black focus:outline-none"
			placeholder="Search...">
	</div>
	<!-- Categories Section -->
	<?php
	if (! empty($categories)) {
	?>
		<div class="border border-ec-border rounded-t-lg easycommerce-categories">
			<h5 class="easycommerce-filter-heading p-4 bg-[#F8F8F8] text-base font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none leading-[26px] easycommerce-categories-title"
				onclick="toggleAccordion('categories-mobile')">
				<?php esc_html_e('Categories', 'easycommerce'); ?>
				<span id="arrow-categories-mobile" class="transition-all duration-300">
					<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</span>
			</h5>
			<div id="categories-mobile" class="accordion-content rounded-b-lg hidden">
				<div class="pt-6 p-4 easycommerce-categories-container">
					<div class="easycommerce-scroll-inner">
						<?php display_attribute($categories, 'category'); ?>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
	?>
	<!-- Price Slider Section -->
	<div class="border border-ec-border rounded-t-lg easycommerce-prices">
		<h5 class="easycommerce-filter-heading p-4 text-base bg-[#F8F8F8] font-semibold cursor-pointer colorec-body w-full flex justify-between items-center shadow-none" onclick="toggleAccordion('price-slider-mobile')">
			<?php esc_html_e('Price', 'easycommerce'); ?>
			<span id="arrow-price-slider-mobile" class="transform transition-transform">
				<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</span>
		</h5>
		<div id="price-slider-mobile" class="flex-col gap-5 accordion-content rounded-b-lg">
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
							<?php esc_html_e('Min Price', 'easycommerce'); ?>
						</label>
						<div class="relative flex items-center justify-normal">
							<input type="number" id="easycommerce-min-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr($min_price); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>" />
							<span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#272435]">$</span>
						</div>
					</div>
					<div class="flex flex-col">
						<label class="text-[12px] mb-1 text-ec-placeholder">
							<?php esc_html_e('Max Price', 'easycommerce'); ?>
						</label>
						<div class="relative flex items-center justify-normal">
							<input type="number" id="easycommerce-max-price" class="easycommerce-price-range-input-field" value="<?php echo esc_attr($max_price); ?>" class="border border-ec-body rounded px-2 py-1 w-full pr-10" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>" />
							<span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#272435]">$</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="accordion-container flex flex-col gap-3 easycommerce-categories">
		<?php foreach ($attributes as $attribute) :
			$attribute_name         = $attribute->name;
			$attribute_slug         = $attribute->slug;
			$attribute_values_model = new Attribute_Value();
			$values                 = $attribute_values_model->get_by($attribute->id);
			$attribute->values      = wp_list_pluck($values, 'name', 'slug');

			if (empty($attribute_name) || empty($attribute->values)) continue;

		?>
			<div class="border border-ec-border rounded-t-lg">
				<h5
					class="easycommerce-filter-heading text-base font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none p-4 bg-[#F8F8F8]"
					onclick="toggleAccordion('<?php echo esc_js($attribute_name); ?>-mobile')">
					<?php echo esc_html($attribute_name); ?>
					<span id="arrow-<?php echo esc_attr($attribute_name); ?>-mobile" class="transform transition-transform">
						<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
					</span>
				</h5>
				<div id="<?php echo esc_attr($attribute_name); ?>-mobile" class="accordion-content rounded-b-lg">
					<div class="p-4 flex flex-col gap-5 easycommerce-scroll-inner">
						<?php
						foreach ($values as $value_key => $value_obj) :
						?>
							<div class="flex justify-between">
								<label class="flex items-center gap-2">
									<input type="checkbox" class="easycommerce-input-checkoutbox template-3 easycommerce-input-attribute" data-attribute="<?php echo esc_attr($attribute_slug); ?>" name="<?php echo esc_attr($value_obj->slug); ?>" value="<?php echo esc_attr($value_obj->slug); ?>" />
									<?php echo esc_html($value_obj->name); ?>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- Brand section -->
		<?php if (! empty($brands)) : ?>
			<div class="border border-ec-border rounded-t-lg">
				<h5 class="easycommerce-filter-heading text-base font-semibold cursor-pointer text-ec-body w-full flex justify-between items-center shadow-none p-4 bg-[#F8F8F8]" onclick="toggleAccordion('brands-mobile')">
					<?php esc_html_e('Brands', 'easycommerce'); ?>
					<span id="arrow-brands-mobile" class="transform transition-transform">
						<svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
					</span>
				</h5>
				<div id="brands-mobile" class="accordion-content ">
					<div class="flex flex-col gap-5 p-4">
						<div class="easycommerce-scroll-inner">
							<?php display_attribute($brands, 'brand'); ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
	let isToggled = false;

	// Function to toggle mobile accordion
	function openAccordion(section) {
		const el = document.getElementById(section);

		console.log(el.classList)
		console.log(el.classList.contains('open'))

		if (el.classList.contains('open')) {
			console.log('Closing accordion');
			el.classList.remove('open');
			el.classList.add('hidden');
		} else {
			console.log('Opening accordion');
			el.classList.add('open');
			el.classList.remove('hidden');
		}
	}

	// Accordion toggle
	function toggleAccordion(section) {
		const el = document.getElementById(section);
		const arrow = document.getElementById(`arrow-${section}`);

		const allContents = document.querySelectorAll('.accordion-content');
		const allArrows = document.querySelectorAll('[id^="arrow-"]');

		allContents.forEach(content => {
			if (content !== el) {
				content.classList.add('hidden');
				content.classList.remove('open');
			}
		});

		allArrows.forEach(arr => {
			if (arr !== arrow) {
				arr.classList.remove('rotate-180');
			}
		});

		el.classList.toggle('hidden');
		el.classList.toggle('open');
		arrow.classList.toggle('rotate-180');

		const panel = el.querySelector(
			'.easycommerce-categories-accordion, .easycommerce-brands-accordion, .easycommerce-prices-accordion, .easycommerce-attributes-accordion, .easycommerce-filters-accordion'
		);
		if (!panel) return;

		const rect = el.previousElementSibling.getBoundingClientRect();
		panel.style.top = rect.bottom + 8 + 'px';
		panel.style.left = rect.left + 'px';

		if (panel.scrollHeight > 380) {
			panel.classList.add('easycommerce-scroll');
		} else {
			panel.classList.remove('easycommerce-scroll');
		}
	}


	// Smoothly close all accordions on scroll

	function closeAllAccordionsSmooth() {
		document.querySelectorAll('.accordion-content.open').forEach(content => {
			const arrow = document.getElementById(`arrow-${content.id}`);
			if (arrow) arrow.classList.remove('rotate-180');

			content.classList.add('closing');

			content.classList.remove('open');

			const transitionHandler = () => {
				content.classList.add('hidden');
				content.classList.remove('closing');
				content.removeEventListener('transitionend', transitionHandler);
			};

			content.addEventListener('transitionend', transitionHandler);
		});
	}

	// Trigger smooth close on vertical scroll
	window.addEventListener('scroll', () => {
		closeAllAccordionsSmooth();
	});


	// Close accordion when clicking outside
	document.addEventListener('click', function(e) {
		const isInsideAccordion = e.target.closest(
			'.easycommerce-attributes, .easycommerce-categories, .easycommerce-brands, .easycommerce-prices, .easycommerce-filters'
		);

		if (!isInsideAccordion) {
			document.querySelectorAll('.accordion-content').forEach(content => {
				content.classList.add('hidden');
				content.classList.remove('open');
			});
			document.querySelectorAll('[id^="arrow-"]').forEach(arrow => {
				arrow.classList.remove('rotate-180');
			});
		}
	});


	// Accordion content handling
	const accordionContents = document.querySelectorAll('.accordion-content');

	// Function to check height and add scroll if needed
	function checkHeight(element) {
		const panel = element.querySelector(
			'.easycommerce-categories-accordion, .easycommerce-brands-accordion, .easycommerce-prices-accordion, .easycommerce-attributes-accordion, .easycommerce-filters-accordion'
		);
		if (!panel) return;
		if (panel.scrollHeight > 380) {
			panel.classList.add('easycommerce-scroll');
		} else {
			panel.classList.remove('easycommerce-scroll');
		}
	}

	// Initial height check
	accordionContents.forEach(checkHeight);

	// Observe content changes
	accordionContents.forEach((element) => {
		const observer = new MutationObserver(() => checkHeight(element));
		observer.observe(element, {
			childList: true,
			subtree: true
		});
	});

	// Scroll buttons functionality
	document.addEventListener('DOMContentLoaded', () => {
		const scrollContainers = document.querySelectorAll('.scroll-content');

		scrollContainers.forEach(container => {
			const leftBtn = container.parentElement.querySelector('.scroll-left');
			const rightBtn = container.parentElement.querySelector('.scroll-right');
			const leftBlur = container.parentElement.querySelector('.left-blur');
			const rightBlur = container.parentElement.querySelector('.right-blur');

			function updateScrollButtons() {
				if (container.scrollWidth > container.clientWidth) {
					rightBtn.classList.add('visible');
					rightBlur.style.display = 'block';
				} else {
					leftBtn.classList.remove('visible');
					rightBtn.classList.remove('visible');
					leftBlur.style.display = 'none';
					rightBlur.style.display = 'none';
				}

				if (container.scrollLeft <= 0) {
					leftBtn.classList.remove('visible');
					leftBlur.style.display = 'none';

				} else {
					leftBtn.classList.add('visible');
					leftBlur.style.display = 'block';
				}

				if (container.scrollLeft + container.clientWidth >= container.scrollWidth) {
					rightBtn.classList.remove('visible');
					rightBlur.style.display = 'none';
				}
			}

			updateScrollButtons();

			container.addEventListener('scroll', updateScrollButtons);

			leftBtn.addEventListener('click', () => {
				container.scrollBy({
					left: -100,
					behavior: 'smooth'
				});
			});

			rightBtn.addEventListener('click', () => {
				container.scrollBy({
					left: 100,
					behavior: 'smooth'
				});
			});

			new ResizeObserver(updateScrollButtons).observe(container);
		});
	});
</script>
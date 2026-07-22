<?php
/**
 * Sort by template for Shop Template 3.
 *
 * Displays product count and sort options dropdown.
 *
 * @var array $attributes Block attributes.
 * @var int $products_count Total number of products.
 */
    $block_settings        = $attributes;
    $product_per_page      = isset( $block_settings['ProductPerPage'] ) ? $block_settings['ProductPerPage'] : 9;
    $current_page          = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
?>
<!-- Filters Section -->
 <div class="flex sm:flex-row flex-col justify-between items-center easycommerce-shop-top-bar gap-4 w-full font-inter">
    <div class="w-full">
        <p class="easycommerce-shop-count text-ec-body !mb-0"> 
            <?php
			if ( $show_pagination ) {
				if ( $product_per_page < $products_count ) {
					printf(
						esc_html__( 'Showing %1$s–%2$s of %3$s results', 'easycommerce' ),
						$current_page,
						$product_per_page,
						$products_count
					);
				} else {
					printf(
						_n(
							'Showing %s result',
							'Showing %s results',
							$products_count,
							'easycommerce'
						),
						number_format_i18n( $products_count )
					);
				}
			}
			?>
        </p>
    </div>
    <div class="flex flex-row sm:justify-end justify-start sm:items-center w-full gap-2">
        <div class="easycommerce-filters easycommerce-filters-st border border-ec-border rounded-[4px] relative max-w-[220px]">
            <h5 class="easycommerce-filter-heading text-base font-normal leading-[26px] text-ec-light-black cursor-pointer w-[220px] flex justify-between items-center shadow-none p-2" onclick="toggleAccordion('filters', 'shortby')">
                <?php esc_html_e( 'Sort By', 'easycommerce' ); ?>
                <span id="arrow-filters" class="transform transition-transform">
                    <svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
                    </svg>
                </span>
            </h5>

            <div id="filters" class="accordion-content accordion-shortby-content hidden">
                <div class="w-[220px] bg-white p-6 flex flex-col gap-5 easycommerce-filters-accordion-shortby border border-ec-border rounded-xl z-10 absolute mt-3 ml-0 right-0">
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
        <div class="lg:flex items-center gap-2 sm:ml-4 ml-0 hidden">
            <button id="gridViewBtn" class="easycommerce-shop-st-grid-btn hover:outline-none focus:outline-none" aria-label="<?php esc_attr_e( 'Grid View', 'easycommerce' ); ?>">
                <svg width="43" height="40" viewBox="0 0 43 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" fill="#272435"/>
                    <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" stroke=""/>
                    <path d="M12.25 13.625C12.276 13.1042 12.4583 12.6615 12.7969 12.2969C13.1615 11.9583 13.6042 11.776 14.125 11.75H17.875C18.3958 11.776 18.8385 11.9583 19.2031 12.2969C19.5417 12.6615 19.724 13.1042 19.75 13.625V17.375C19.724 17.8958 19.5417 18.3385 19.2031 18.7031C18.8385 19.0417 18.3958 19.224 17.875 19.25H14.125C13.6042 19.224 13.1615 19.0417 12.7969 18.7031C12.4583 18.3385 12.276 17.8958 12.25 17.375V13.625ZM14.125 17.375H17.875V13.625H14.125V17.375ZM12.25 23.625C12.276 23.1042 12.4583 22.6615 12.7969 22.2969C13.1615 21.9583 13.6042 21.776 14.125 21.75H17.875C18.3958 21.776 18.8385 21.9583 19.2031 22.2969C19.5417 22.6615 19.724 23.1042 19.75 23.625V27.375C19.724 27.8958 19.5417 28.3385 19.2031 28.7031C18.8385 29.0417 18.3958 29.224 17.875 29.25H14.125C13.6042 29.224 13.1615 29.0417 12.7969 28.7031C12.4583 28.3385 12.276 27.8958 12.25 27.375V23.625ZM14.125 27.375H17.875V23.625H14.125V27.375ZM27.875 11.75C28.3958 11.776 28.8385 11.9583 29.2031 12.2969C29.5417 12.6615 29.724 13.1042 29.75 13.625V17.375C29.724 17.8958 29.5417 18.3385 29.2031 18.7031C28.8385 19.0417 28.3958 19.224 27.875 19.25H24.125C23.6042 19.224 23.1615 19.0417 22.7969 18.7031C22.4583 18.3385 22.276 17.8958 22.25 17.375V13.625C22.276 13.1042 22.4583 12.6615 22.7969 12.2969C23.1615 11.9583 23.6042 11.776 24.125 11.75H27.875ZM27.875 13.625H24.125V17.375H27.875V13.625ZM22.25 23.625C22.276 23.1042 22.4583 22.6615 22.7969 22.2969C23.1615 21.9583 23.6042 21.776 24.125 21.75H27.875C28.3958 21.776 28.8385 21.9583 29.2031 22.2969C29.5417 22.6615 29.724 23.1042 29.75 23.625V27.375C29.724 27.8958 29.5417 28.3385 29.2031 28.7031C28.8385 29.0417 28.3958 29.224 27.875 29.25H24.125C23.6042 29.224 23.1615 29.0417 22.7969 28.7031C22.4583 28.3385 22.276 27.8958 22.25 27.375V23.625ZM24.125 27.375H27.875V23.625H24.125V27.375Z" fill="white"/>
                </svg>
            </button>

            <button id="listViewBtn" class="easycommerce-shop-st-list-btn hover:outline-none focus:outline-none" aria-label="<?php esc_attr_e( 'List View', 'easycommerce' ); ?>">
                <svg width="43" height="40" viewBox="0 0 43 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" fill="#EBEBEB"/>
                    <rect x="0.5" y="0.5" width="42" height="39" rx="3.5" stroke="#EBEBEB"/>
                    <path d="M13.5 13C13.8646 13 14.1641 13.1172 14.3984 13.3516C14.6328 13.5859 14.75 13.8854 14.75 14.25C14.75 14.6146 14.6328 14.9141 14.3984 15.1484C14.1641 15.3828 13.8646 15.5 13.5 15.5C13.1354 15.5 12.8359 15.3828 12.6016 15.1484C12.3672 14.9141 12.25 14.6146 12.25 14.25C12.25 13.8854 12.3672 13.5859 12.6016 13.3516C12.8359 13.1172 13.1354 13 13.5 13ZM30.0625 13.3125C30.6354 13.3646 30.9479 13.6771 31 14.25C30.9479 14.8229 30.6354 15.1354 30.0625 15.1875H18.1875C17.6146 15.1354 17.3021 14.8229 17.25 14.25C17.3021 13.6771 17.6146 13.3646 18.1875 13.3125H30.0625ZM30.0625 19.5625C30.6354 19.6146 30.9479 19.9271 31 20.5C30.9479 21.0729 30.6354 21.3854 30.0625 21.4375H18.1875C17.6146 21.3854 17.3021 21.0729 17.25 20.5C17.3021 19.9271 17.6146 19.6146 18.1875 19.5625H30.0625ZM30.0625 25.8125C30.6354 25.8646 30.9479 26.1771 31 26.75C30.9479 27.3229 30.6354 27.6354 30.0625 27.6875H18.1875C17.6146 27.6354 17.3021 27.3229 17.25 26.75C17.3021 26.1771 17.6146 25.8646 18.1875 25.8125H30.0625ZM13.5 21.75C13.1354 21.75 12.8359 21.6328 12.6016 21.3984C12.3672 21.1641 12.25 20.8646 12.25 20.5C12.25 20.1354 12.3672 19.8359 12.6016 19.6016C12.8359 19.3672 13.1354 19.25 13.5 19.25C13.8646 19.25 14.1641 19.3672 14.3984 19.6016C14.6328 19.8359 14.75 20.1354 14.75 20.5C14.75 20.8646 14.6328 21.1641 14.3984 21.3984C14.1641 21.6328 13.8646 21.75 13.5 21.75ZM13.5 25.5C13.8646 25.5 14.1641 25.6172 14.3984 25.8516C14.6328 26.0859 14.75 26.3854 14.75 26.75C14.75 27.1146 14.6328 27.4141 14.3984 27.6484C14.1641 27.8828 13.8646 28 13.5 28C13.1354 28 12.8359 27.8828 12.6016 27.6484C12.3672 27.4141 12.25 27.1146 12.25 26.75C12.25 26.3854 12.3672 26.0859 12.6016 25.8516C12.8359 25.6172 13.1354 25.5 13.5 25.5Z" fill="#272435"/>
                </svg>
            </button>
        </div>
    </div>
</div>
<script>
    function toggleAccordion(section, type = 'default') {
        const el = document.getElementById(section);
        const arrow = document.getElementById(`arrow-${section}`);
        
        const plusIcon = `
            <svg width="17" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.5 1V14M14 7.5H1" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;
        
        const minusIcon = `
            <svg width="17" height="3" viewBox="0 0 17 3" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1.5H16" stroke="#272435" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;
        
        // Collapse if already open
        if (el.classList.contains('open')) {
            el.classList.add('hidden');
            el.classList.remove('open');
            
            if (type === 'shortby') {
                arrow.classList.remove('rotate-180');
            } else {
                arrow.innerHTML = plusIcon;
            }
            return;
        }
        
        // Close other accordions based on type
        if (type === 'shortby') {
            document.querySelectorAll('.accordion-shortby-content').forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('open');
            });
            document.querySelectorAll('[id^="arrow-"]').forEach(arr => {
                arr.classList.remove('rotate-180');
            });
        } else {
            document.querySelectorAll('.accordion-content').forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('open');
            });
            document.querySelectorAll('[id^="arrow-"]').forEach(arr => {
                arr.innerHTML = plusIcon;
            });
        }
        
        // Open clicked accordion
        el.classList.remove('hidden');
        el.classList.add('open');
        
        if (type === 'shortby') {
            arrow.classList.add('rotate-180');
        } else {
            arrow.innerHTML = minusIcon;
        }
    }

    function closeAllAccordionsSmooth() {
        document.querySelectorAll('.accordion-shortby-content.open').forEach(content => {
            const arrow = document.getElementById(`arrow-${content.id}`);
            if (arrow) arrow.classList.remove('rotate-180');
            content.classList.add('hidden');
            content.classList.remove('open');
        });
    }

    window.addEventListener('scroll', closeAllAccordionsSmooth);

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.easycommerce-filters')) {
            closeAllAccordionsSmooth();
        }
    });
</script>

<?php
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;

?>

<div>
	<?php
	$i = 0;
	if ( ! empty( $cart['items'] ) ) {
		foreach ( $cart['items'] as $item ) {
			$item_image = $item['thumbnail']['url'] ?? '';
			$product_id = $item['product_id'];
			$price_id   = $item['price_id'];
			$product    = new Product( $product_id );
			$_variation = new Product_Variation();
			$variation  = $_variation->get_by_price( $price_id, $product_id );
			$stock      = $variation->get_stock();
			$type       = $variation->get_type();
			$item_total = easycommerce_price( $item['subtotal'] ?? 0 );
			if ( empty( $item_image ) ) {
				$thumbnail  = $product->get_thumbnail( 'thumbnail' );
				$item_image = ! empty( $thumbnail['url'] ) ? $thumbnail['url'] : EASYCOMMERCE_ASSETS_URL . 'public/img/product/shop-product-placeholder.png';
			}
			?>
	<div class="easycommerce-cart-product flex justify-between p-2 md:p-4 bg-white mb-3 border border-ec-border rounded-lg w-full">
		<div class="flex items-center w-[calc(100%_-_60px)] sm:w-[calc(100%_-_45px)]">
			<div
				class="md:w-[82px] md:h-[82px] w-[60px] h-[50px] rounded-md border border-ec-border flex items-center justify-center mr-4">
				<img class="w-full h-full object-cover" src="<?php echo esc_url( $item_image ); ?>" />
			</div>
			<div class="w-[calc(100%_-_100px)] sm:w-[calc(100%_-_80px)] flex gap-2 sm:gap-4 md:mt-0 mt-2">
				<div class="w-[55%] md:w-[45%]">
					<h5
						class="font-inter text-ec-body font-medium leading-[26px] text-base mt-3 md:mt-1 mb-1 mr-4">
						<?php echo esc_html( mb_strimwidth( $item['title'], 0, 30, '...' ) ); ?>
					</h5>
					<span
						class="block mb-[13px] text-ec-placeholder font-inter font-normal leading-5 text-[12px]">
						<?php echo esc_html( implode( ', ', array_values( $item['attributes'] ) ) ); ?>
					</span>
				</div>
			
				<div class="easycommerce-cart-quantity-wrap flex flex-col items-center md:gap-[10px] justify-center w-[45%] md:w-[55%] gap-2">
					<span id="easycommerce-checkout-cart-item-subtotal_<?php echo esc_attr( $i ); ?>"
						class="easycommerce-checkout-cart-item-subtotal text-ec-body text-base font-inter font-semibold leading-[26px] p-[5px] ">
						<?php echo esc_html( $item_total ); ?>
					</span>
					<div class="flex items-center border border-ec-border rounded-[4px] py-2 px-2 md:py-[8px] md:px-[10px] md:gap-[10px] gap-2">
						<button type="button"
							class="easycommerce-qunatity-button easycommerce-checkout-cart-quantity-minus-btn text-[30px] flex items-center justify-center text-[#737791] hover:bg-white focus:bg-white active:bg-white">
							<svg width="12" height="12" viewBox="0 0 11 3" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M10.375 1.5C10.3438 1.84375 10.1562 2.03125 9.8125 2.0625H1.1875C0.84375 2.03125 0.65625 1.84375 0.625 1.5C0.65625 1.15625 0.84375 0.96875 1.1875 0.9375H9.8125C10.1562 0.96875 10.3438 1.15625 10.375 1.5Z" fill="#737791"/>
							</svg>
						</button>
						<input
							class="easycommerce-cart-quantity-input easycommerce-qunatity-input font-inter leading-[12px] font-normal text-base text-[#737791]"
							type="number"
							value="<?php echo esc_attr( $item['quantity'] ); ?>"
							product-id="<?php echo esc_attr( $item['product_id'] ); ?>"
							price-id="<?php echo esc_attr( $item['price_id'] ); ?>"
							min="1"
							<?php if ( ! empty( $stock ) ) : ?>
								data-stock-count="<?php echo esc_attr( $stock ); ?>"
							<?php endif; ?>
							data-type="<?php echo esc_attr( $type ); ?>"
						/>

						<button type="button"
							class="easycommerce-qunatity-button easycommerce-checkout-cart-quantity-plus-btn flex items-center justify-center hover:bg-white focus:bg-white active:bg-white">
							<svg width="12" height="12" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M10.375 5.5C10.3438 5.84375 10.1562 6.03125 9.8125 6.0625H6.0625V9.8125C6.03125 10.1562 5.84375 10.3438 5.5 10.375C5.15625 10.3438 4.96875 10.1562 4.9375 9.8125V6.0625H1.1875C0.84375 6.03125 0.65625 5.84375 0.625 5.5C0.65625 5.15625 0.84375 4.96875 1.1875 4.9375H4.9375V1.1875C4.96875 0.84375 5.15625 0.65625 5.5 0.625C5.84375 0.65625 6.03125 0.84375 6.0625 1.1875V4.9375H9.8125C10.1562 4.96875 10.3438 5.15625 10.375 5.5Z" fill="#737791"/>
							</svg>
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="flex gap-[6px]">
	
			<button type="button"
				class="easycommerce-checkout-cart-item-delete-btn p-1 w-[24px] h-[24px] group rounded-[3px] bg-[#ffffff] hover:bg-[#FF3A520D] flex items-center justify-center focus:bg-[#F8F8F8] border border-[#EBEBEB]"
				product-id="<?php echo esc_attr( $item['product_id'] ); ?>"
				price-id="<?php echo esc_attr( $item['price_id'] ); ?>">
				<img class="block group-hover:hidden"
					src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/icons/delete-14-16.png' ); ?>" />
				<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/icons/delete-red-14-16.png' ); ?>"
					class="hidden group-hover:block" />
			</button>
			<?php
				$variation_args = array();
			if ( ! empty( $item['attributes'] ) ) {
				foreach ( $item['attributes'] as $attr => $value ) {
					$variation_args[ $attr ] = $value;
				}
			}

				$query_args = array_merge(
					array(
						'edit_item' => $item['product_id'],
						'qty'       => $item['quantity'],
					),
					$variation_args
				);

				$edit_link = esc_url( add_query_arg( $query_args, get_permalink( $item['product_id'] ) ) );
			?>
			<a href="<?php echo $edit_link; ?>"
				class="p-1 w-[24px] h-[24px] group rounded-[3px] bg-[#ffffff]  hover:bg-[#7351FD]/10 flex items-center justify-center focus:bg-[#F8F8F8] border border-[#EBEBEB] text-[#737791] hover:text-[#7351FD]"
			>
				<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M10.582 1.37891C10.9284 1.03255 11.3385 0.859375 11.8125 0.859375C12.2865 0.859375 12.6966 1.03255 13.043 1.37891L13.3711 1.70703C13.7174 2.05339 13.8906 2.46354 13.8906 2.9375C13.8906 3.41146 13.7174 3.82161 13.3711 4.16797L7.35547 10.2109C7.11849 10.4297 6.83594 10.5846 6.50781 10.6758L4.04688 11.25C3.88281 11.2682 3.74609 11.2227 3.63672 11.1133C3.52734 11.0039 3.48177 10.8672 3.5 10.7031L4.07422 8.24219C4.16536 7.91406 4.32031 7.6224 4.53906 7.36719L10.582 1.37891ZM12.4414 1.98047C12.2591 1.81641 12.0495 1.73438 11.8125 1.73438C11.5755 1.73438 11.3659 1.81641 11.1836 1.98047L10.4727 2.71875L12.0312 4.27734L12.7695 3.56641C12.9336 3.38411 13.0156 3.17448 13.0156 2.9375C13.0156 2.70052 12.9336 2.49089 12.7695 2.30859L12.4414 1.98047ZM4.94922 8.43359L4.51172 10.2383L6.28906 9.82812C6.47135 9.79167 6.61719 9.70052 6.72656 9.55469L11.4023 4.90625L9.84375 3.34766L5.16797 8.02344C5.05859 8.13281 4.98568 8.26953 4.94922 8.43359ZM5.6875 2.5C5.96094 2.51823 6.10677 2.66406 6.125 2.9375C6.10677 3.21094 5.96094 3.35677 5.6875 3.375H2.1875C1.82292 3.39323 1.51302 3.52083 1.25781 3.75781C1.02083 4.01302 0.893229 4.32292 0.875 4.6875V12.5625C0.893229 12.9271 1.02083 13.237 1.25781 13.4922C1.51302 13.7292 1.82292 13.8568 2.1875 13.875H10.0625C10.4271 13.8568 10.737 13.7292 10.9922 13.4922C11.2292 13.237 11.3568 12.9271 11.375 12.5625V9.0625C11.3932 8.78906 11.5391 8.64323 11.8125 8.625C12.0859 8.64323 12.2318 8.78906 12.25 9.0625V12.5625C12.2318 13.1823 12.0221 13.7018 11.6211 14.1211C11.2018 14.5221 10.6823 14.7318 10.0625 14.75H2.1875C1.56771 14.7318 1.04818 14.5221 0.628906 14.1211C0.227865 13.7018 0.0182292 13.1823 0 12.5625V4.6875C0.0182292 4.06771 0.227865 3.54818 0.628906 3.12891C1.04818 2.72786 1.56771 2.51823 2.1875 2.5H5.6875Z" fill="currentColor"/>
				</svg>
			</a>
		</div>
	</div>
			<?php
			++$i;
		}
	} else {
		esc_html_e( 'No items in the cart', 'easycommerce' );
	}
	?>
</div>

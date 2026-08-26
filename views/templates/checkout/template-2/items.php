<?php
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;

?>
<div class="flex items-center lg:mb-[30px] mb-2">
	<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/checkout/your-order.png' ); ?>"
		class="md:w-[53px] md:h-[53px] mr-4 rtl:ml-4 rtl:mr-0 w-[45px] h-[42px]" />
	<h3 class="font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
		<?php esc_html_e( 'Your Cart', 'easycommerce' ); ?>
	</h3>
</div>
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
	<div class="easycommerce-cart-product flex items-center justify-between pt-5 pb-5">
		<div class="flex items-center w-[calc(100%_-_60px)]">
			<div
				class="lg:w-[80px] lg:h-[90px] w-[60px] h-[50px] rounded-lg flex items-center justify-center mr-5 rtl:ml-5 rtl:mr-0">
				<img src="<?php echo esc_url( $item_image ); ?>" />
			</div>
			<div class="w-[calc(100%_-_100px)]">
				<h5
					class="font-inter text-ec-body font-medium leading-[26px] text-sm md:text-base mb-1 ">
					<?php echo esc_html( $item['title'] ); ?></h5>
				<span
					class="block mb-[13px] text-ec-placeholder font-inter font-normal leading-5 text-[12px]">
					<?php echo esc_html( implode( ', ', array_values( $item['attributes'] ) ) ); ?>
				</span>
				<div class="easycommerce-cart-quantity-wrap flex items-center gap-[10px]">
					<button type="button"
						class="easycommerce-qunatity-button easycommerce-checkout-cart-quantity-minus-btn border text-[30px] border-ec-border w-[24px] h-[24px] flex items-center justify-center rounded-[3px] text-ec-placeholder hover:bg-white hover:text-ec-placeholder active:text-ec-placeholder focus:text-ec-placeholder focus:border-ec-border focus:bg-white hover:border-ec-border">
						<svg width="11" height="3" viewBox="0 0 11 3" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10.375 1.5C10.3438 1.84375 10.1562 2.03125 9.8125 2.0625H1.1875C0.84375 2.03125 0.65625 1.84375 0.625 1.5C0.65625 1.15625 0.84375 0.96875 1.1875 0.9375H9.8125C10.1562 0.96875 10.3438 1.15625 10.375 1.5Z" fill="#737791"/>
						</svg>
					</button>
					<input
						class="easycommerce-cart-quantity-input easycommerce-qunatity-input font-inter leading-[12px] font-normal"
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
						class="easycommerce-qunatity-button easycommerce-checkout-cart-quantity-plus-btn border border-ec-border w-[24px] h-[24px] flex items-center justify-center rounded-[3px] text-ec-placeholder hover:bg-white hover:text-placeholder active:text-placeholder focus:text-placeholder hover:border-ec-border focus:border-ec-border focus:bg-white">
						<svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10.375 5.5C10.3438 5.84375 10.1562 6.03125 9.8125 6.0625H6.0625V9.8125C6.03125 10.1562 5.84375 10.3438 5.5 10.375C5.15625 10.3438 4.96875 10.1562 4.9375 9.8125V6.0625H1.1875C0.84375 6.03125 0.65625 5.84375 0.625 5.5C0.65625 5.15625 0.84375 4.96875 1.1875 4.9375H4.9375V1.1875C4.96875 0.84375 5.15625 0.65625 5.5 0.625C5.84375 0.65625 6.03125 0.84375 6.0625 1.1875V4.9375H9.8125C10.1562 4.96875 10.3438 5.15625 10.375 5.5Z" fill="#737791"/>
						</svg>
					</button>
				</div>
			</div>
		</div>
		<div class="flex flex-col items-end gap-5 justify-center">
			<span id="easycommerce-checkout-cart-item-subtotal_<?php echo esc_attr( $i ); ?>"
				class="easycommerce-checkout-cart-item-subtotal text-ec-body text-base font-inter font-normal leading-[26px] p-[5px] border border-ec-border rounded-[4px] bg-[#F8F8F8]">
				<?php echo esc_html( $item_total ); ?>
			</span>
			<button type="button"
				class="easycommerce-checkout-cart-item-delete-btn p-2 lg:p-[10px] lg:w-[35px] lg:h-[35px] w-[30px] h-[30px] group rounded-[5px] bg-[#F8F8F8] hover:bg-[#FF3A520D] flex items-center justify-center focus:bg-[#F8F8F8]"
				product-id="<?php echo esc_attr( $item['product_id'] ); ?>"
				price-id="<?php echo esc_attr( $item['price_id'] ); ?>">
				<img class="block group-hover:hidden"
					src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/icons/delete-14-16.png' ); ?>" />
				<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/icons/delete-red-14-16.png' ); ?>"
					class="hidden group-hover:block" />
			</button>
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

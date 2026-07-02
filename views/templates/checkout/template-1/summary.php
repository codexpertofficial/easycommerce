<?php

/**
 * Checkout Order Summary Template
 *
 * This template can be overridden by copying it to yourtheme/easycommerce/checkout/summary.php.
 *
 * @var array $cart The cart data.
 * @var \EasyCommerce\Models\Cart $cart_obj The cart object.
 */
?>
<div class="flex items-center mb-4 md:mb-[30px] mx-[2px] mt-4 md:mt-0">
	<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/checkout/order-summary.png' ); ?>"
		class="md:w-[53px] md:h-[53px] mr-4 rtl:ml-4 rtl:mr-0 w-[45px] h-[42px]" />
	<h3 class="easycommerce-billing-title text-ec-body font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
		<?php esc_html_e( 'Order Summary', 'easycommerce' ); ?>
	</h3>
</div>
<div>
	<div class="easycommerce-coupon-wrapper relative mb-4">
		<input type="text" placeholder="Discount code" id="easycommerce-coupon-field" />
		<button type="button"
			class="absolute top-[9px] right-[9px] rtl:right-auto rtl:left-[9px] py-1 md:py-[7px] px-5 border border-ec-border text-[#737791] focus:text-[#737791] rounded-[6px] focus:border-ec-border bg-[#F8F8F8] focus:bg-[#F8F8F8] text-base font-inter font-normal leading-[26px] shadow-none"
			id="easycommerce-coupon-apply">
			<?php esc_html_e( 'Apply', 'easycommerce' ); ?>
		</button>

		<p id="easycommerce-checkout-coupon-message"
			class="mt-1 font-inter font-normal text-base leading-[26px]" style="display: none;"></p>
	</div>

	<div class="flex items-center justify-between p-4 border-b border-dotted border-ec-border">
		<label class="text-ec-placeholder font-inter font-normal text-base leading-[26px]">
			<?php esc_html_e( 'Subtotal', 'easycommerce' ); ?>
		</label>
		<span
			class="easycommerce-checkout-subtotal-price mb-0 text-ec-body font-inter font-medium text-base leading-[26px]">
			<?php echo esc_html( easycommerce_price( $cart['amounts']['subtotal'] ?? 0 ) ); ?>
		</span>
	</div>
	<?php if ( ! empty( $cart['fragments']['coupons'] ) ) : ?>
		<div class="flex flex-col p-4 border-b border-dotted border-ec-border easycommerce-discount-wrapper">
			<div class="text-ec-placeholder font-inter font-normal text-base leading-[26px]">
				<?php esc_html_e( 'Discount', 'easycommerce' ); ?>
			</div>
			<div class="ml-4 mt-2">
				<div
					class="easycommerce-checkout-discount mb-0 text-ec-body font-inter text-base leading-[26px]">
					<?php
					if ( ! empty( $cart['fragments']['coupons'] ) ) {
						foreach ( $cart['fragments']['coupons'] as $code => $discount ) {
							?>
							<div class="flex justify-between">
								<div class="flex items-center gap-2">
									<svg class="cursor-pointer easycommerce-remove-coupon"
										data-id="<?php echo esc_attr( $code ); ?>"
										width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M10.75 9.71875C11.0417 10.0729 11.0417 10.4271 10.75 10.7812C10.3958 11.0729 10.0417 11.0729 9.6875 10.7812L6 7.0625L2.28125 10.7812C1.92708 11.0729 1.57292 11.0729 1.21875 10.7812C0.927083 10.4271 0.927083 10.0729 1.21875 9.71875L4.9375 6L1.21875 2.25C0.927083 1.89583 0.927083 1.54167 1.21875 1.1875C1.57292 0.895833 1.92708 0.895833 2.28125 1.1875L6 4.9375L9.71875 1.21875C10.0729 0.927083 10.4271 0.927083 10.7812 1.21875C11.0729 1.57292 11.0729 1.92708 10.7812 2.28125L7.0625 6L10.75 9.71875Z"
										fill="#737791"/>
									</svg>
									<div class="easycommerce-discount-code font-inter text-[14px] text-[#737791]"><?php echo esc_html( $code ); ?></div>
								</div>
								<div class="easycommerce-discount-code text-ec-body font-inter font-medium text-base leading-[26px]">
									<?php echo esc_html( $discount['amount'] ?? 0 ); ?>
								</div>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>
		</div>
		<?php
	endif;
	if ( $cart_obj->has_item_type( 'physical' ) ) :
		?>
		<div class="flex flex-col justify-between p-4 border-b border-dotted border-ec-border easycommerce-shipping-method-wrapper">
			<label class="text-ec-placeholder font-inter font-normal text-base leading-[26px]">
				<?php esc_html_e( 'Shipping Cost', 'easycommerce' ); ?>
			</label>
			<span
				class="easycommerce-checkout-shipping-cost mb-0 text-ec-body font-inter font-medium text-base leading-[26px]">
				<?php
				if ( ! empty( $shipping_methods = $cart_obj->get_shipping_methods() ) ) {
					$selected_method   = $cart_obj->cart['data']['shipping_method'] ?? null;
					$shipping_discount = $cart['fragments']['shipping_fee_discount'] ?? 0;
					foreach ( $shipping_methods as $index => $shipping_method ) {
						$shipping_method = is_array( $shipping_method ) ? $shipping_method : (array) $shipping_method;
						$is_checked      = ( $selected_method === null && $index === 0 ) || ( $selected_method == $shipping_method['id'] ) ? 'checked' : '';

						// Show "Free" if shipping cost is zero
						if ( $shipping_discount > 0 ) {
							printf(
								'<label class="font-inter font-normal text-[14px] text-[#737791]">
								<input type="radio" name="shipping_method" value="%1$s" class="easycommerce-shipping-method" %4$s required />
								<del>%2$s at %3$s</del> (%5$s)
							</label><br />',
								$shipping_method['id'],
								$shipping_method['name'],
								esc_html( easycommerce_price( $shipping_method['cost'] ) ),
								esc_attr( $is_checked ),
								esc_html__( 'Free Shipping', 'easycommerce' )
							);
						} else {
							printf(
								'<label class="font-inter font-normal text-[14px] text-[#737791]">
								<input type="radio" name="shipping_method" value="%1$s" class="easycommerce-shipping-method" %4$s required />
								%2$s at %3$s
							</label><br />',
								$shipping_method['id'],
								$shipping_method['name'],
								esc_html( easycommerce_price( $shipping_method['cost'] ) ),
								esc_attr( $is_checked )
							);
						}
					}
				} else {
					printf( '<span>%1$s</span>', esc_html__( 'No shipping methods found', 'easycommerce' ) );
				}
				?>
			</span>
			<span class="easycommerce-shipping-method-error max-w-max mt-2 bg-[#FFF8F8] text-[#FF7373] text-[14px] px-3 py-1 rounded-md hidden"
				style="background-color: #FFF8F8; color: #FF7373;max-width: max-content;">
				<?php _e( 'Please select a shipping method', 'easycommerce' ); ?>
			</span>
		</div>
	<?php endif; ?>
	<?php
		$product_tax  = $cart['amounts']['tax'] ?? 0;
		$shipping_tax = $cart['amounts']['shipping_tax'] ?? 0;
	if ( $product_tax > 0 ) {
		?>
			<div class="flex items-center justify-between p-4 border-b border-dotted border-ec-border">
				<label class="text-ec-placeholder font-inter font-normal text-base leading-[26px]">
				<?php esc_html_e( 'Product Tax', 'easycommerce' ); ?>
				</label>
				<span
					class="easycommerce-checkout-tax mb-0 text-ec-body font-inter font-medium text-base leading-[26px]">
				<?php
					echo esc_html( easycommerce_price( $product_tax ) );
				?>
				</span>
			</div>
			<?php
	}
	if ( $shipping_tax > 0 ) {
		?>
			<div class="flex items-center justify-between p-4 border-b border-dotted border-ec-border">
				<label class="text-ec-placeholder font-inter font-normal text-base leading-[26px]">
				<?php esc_html_e( 'Shipping Tax', 'easycommerce' ); ?>
				</label>
				<span
					class="easycommerce-checkout-shipping-tax mb-0 text-ec-body font-inter font-medium text-base leading-[26px]">
				<?php
					echo esc_html( easycommerce_price( $shipping_tax ) );
				?>
				</span>
			</div>
			<?php
	}
		do_action( 'easycommerce/views/templates/checkout/summary/totals', $cart, $cart_obj );
	?>

	<div class="flex items-center justify-between p-4 border-t border-ec-border">
		<label class="text-ec-body font-inter font-bold text-base leading-[26px]">
			<?php esc_html_e( 'Order Total', 'easycommerce' ); ?>
		</label>
		<span
			class="easycommerce-checkout-total mb-0 text-ec-body font-inter font-bold text-base leading-[26px]">
			<?php echo esc_html( easycommerce_price( $cart['amounts']['total'] ?? 0 ) ); ?>
		</span>
	</div>
</div>
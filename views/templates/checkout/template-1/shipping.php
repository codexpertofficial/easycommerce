<?php
$has_physical = $cart_obj->has_item_type( 'physical' );
if ( ! $has_physical ) {
	return;
}
?>
<div class="border border-ec-border bg-white rounded-lg p-3 lg:p-7 mb-0 md:mb-5 easycommerce-clearfix">
	<div class="flex items-center justify-between px-[2px]">
		<div class="flex items-center">
			<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/checkout/shipping.png' ); ?>"
				class="w-[45px] h-[42px] mr-2 rtl:ml-4 rtl:mr-0 lg:mr-4 md:w-[53px] md:h-[53px]" />
			<h3
				class="easycommerce-billing-title font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
				<?php esc_html_e( 'Shipping Address', 'easycommerce' ); ?>
			</h3>
		</div>

		<label for="easycommerce-checkout-same-as-shipping" class="flex items-center cursor-pointer">
			<input type="checkbox" id="easycommerce-checkout-same-as-shipping"
				class="easycommerce-input-checkoutbox" name="billing_as_shipping" checked="true" />
			<span
				class="text-ec-body font-inter font-medium text-[12px] lg:text-base leading-4 md:leading-[26px] ml-2 rtl:ml-0 rtl:mr-2">
				<?php esc_html_e( 'Same as Billing', 'easycommerce' ); ?>
			</span>
		</label>
	</div>

	<div class="easycommerce-address easycommerce-checkout-shipping easycommerce-checkout-shipping-same-as-address  mt-[30px]"
		data-address_type="shipping">
		<?php
		if ( is_user_logged_in() ) {
			$shipping_address = get_user_meta( get_current_user_id(), 'shipping_address', true ) ?: array();
		} else {
			$shipping_address = $cart_obj->get_shipping_address() ?: array();
		}
		foreach ( easycommerce_checkout_fields( 'shipping' ) as $field_id => $field ) {
			if ( class_exists( $field_factory = easycommerce_get_field_factory( $field['type'] ) ) ) {
				$field['name']  = "shipping_address[{$field['id']}]";
				$field['value'] = array_key_exists( $field['id'], $shipping_address ) ? $shipping_address[ $field['id'] ] : '';
				$field['id']    = "shipping_{$field['id']}";
				$field_obj      = new $field_factory( $field );
				echo $field_obj->render();
			}
		}
		?>
	</div>
</div>
<?php
$has_physical = $cart_obj->has_item_type( 'physical' );
if ( ! $has_physical ) {
	return;
}
?>
<div class="mb-0 md:mb-5 easycommerce-clearfix">
	<div>
		<label for="easycommerce-checkout-same-as-shipping" class="flex items-center cursor-pointer">
			<input type="checkbox" id="easycommerce-checkout-same-as-shipping"
				class="easycommerce-input-checkoutbox" name="billing_as_shipping" checked="true" />
			<span
				class="text-[#272435] font-inter font-medium text-base leading-[26px] ml-4 rtl:mr-4 rtl:ml-0">
				<?php esc_html_e( 'Same as Billing Address', 'easycommerce' ); ?>
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

				if ( in_array( $field_id, array( 'state', 'city', 'postcode' ), true ) ) {
					$field['class'] .= ' easycommerce-col';
				} else {
					$field['class'] .= ' easycommerce-col-full easycommerce-checkout-template-3_field-wrapper';
				}

				$field_obj = new $field_factory( $field );
				echo $field_obj->render();
			}
		}
		?>
	</div>

</div>
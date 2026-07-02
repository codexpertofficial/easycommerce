<div class="border border-ec-border bg-white rounded-lg p-3 md:p-7 pb-0 mb-5 easycommerce-clearfix">
	<div class="flex items-center mb-4 md:mb-[30px] px-[2px]">
		<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/checkout/billing.png' ); ?>"
			class="w-[45px] h-[42px] mr-2 rtl:ml-4 rtl:mr-0 lg:mr-4 md:w-[53px] md:h-[53px]" />
		<h3
			class="easycommerce-billing-title font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
			<?php esc_html_e( 'Billing Address', 'easycommerce' ); ?>
		</h3>
	</div>
	<div class="easycommerce-fieldset easycommerce-address easycommerce-checkout-billing"
		data-address_type="billing">
		<?php
		if ( is_user_logged_in() ) {
			$billing_address = get_user_meta( get_current_user_id(), 'billing_address', true ) ?: array();
		} else {
			$billing_address = $cart_obj->get_billing_address() ?: array();
		}
		foreach ( easycommerce_checkout_fields( 'billing' ) as $field_id => $field ) {
			if ( class_exists( $field_factory = easycommerce_get_field_factory( $field['type'] ) ) ) {
				$field['name']  = "billing_address[{$field['id']}]";
				$field['value'] = array_key_exists( $field['id'], $billing_address ) ? $billing_address[ $field['id'] ] : '';
				$field['id']    = "billing_{$field['id']}";
				$field_obj      = new $field_factory( $field );
				echo $field_obj->render();
			}
		}
		?>
	</div>
</div>
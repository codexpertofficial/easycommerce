<div class="mt-8 easycommerce-clearfix">
	<div class="mb-4">
		<h3
			class="easycommerce-billing-title font-inter leading-8 font-medium text-xl mb-0 text-[#272435]">
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
<?php
$has_physical = $cart_obj->has_item_type( 'physical' );
?>
<div class="easycommerce-payment-wrapper mt-12 mb-5">
	<div class="mb-6">
		<h3
			class="easycommerce-billing-title font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
			<?php esc_html_e( 'Select Payment Option', 'easycommerce' ); ?>
		</h3>
	</div>
	<div id="easycommerce-payment_methods">
		<?php
		$payment_methods = easycommerce_payment_methods();
		$active_methods  = easycommerce_active_payment_methods();
		$selected_method = $cart_obj->get_payment_method();
		$visible_methods = array_values(
			array_filter(
				$active_methods,
				function ( $payment_id ) use ( $payment_methods, $has_physical, $cart_obj ) {
					if ( apply_filters( 'easycommerce_unset_payment_method_' . $payment_id, false, $has_physical ) ) {
						return false;
					}
					if ( ! apply_filters( 'easycommerce_supports_recurring', true, $payment_id, $cart_obj ) ) {
						return false;
					}
					if ( ! isset( $payment_methods[ $payment_id ] ) || ! class_exists( $payment_methods[ $payment_id ]['class'] ) ) {
						return false;
					}
					return true;
				}
			)
		);

		if ( ! in_array( $selected_method, $visible_methods, true ) ) {
			$selected_method = $visible_methods[0] ?? '';
		}

		foreach ( $visible_methods as $payment_id ) {
			$payment_method     = new $payment_methods[ $payment_id ]['class']();
			$checked_attr       = checked( $selected_method, $payment_id, false );
			$payment_form_class = ( count( $visible_methods ) > 1 ) ? 'ml-10' : '';
			$method_btn_class   = ( count( $visible_methods ) > 1 ) ? '' : 'hidden';
			?>
			<div class="easycommerce-payment_method easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>-wrap">
				<div id="easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>-container">
					<div class=" mb-4 border border-ec-border rounded-lg md:p-6 p-4">
						<div class="flex items-center gap-[15px]">
							<input type="radio"
								id="easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>"
								class="easycommerce-payment_method <?php echo esc_attr( $method_btn_class ); ?>"
								name="easycommerce-payment_method"
								value="<?php echo esc_attr( $payment_id ); ?>"
								<?php echo esc_attr( $checked_attr ); ?>
							/>

							<div class="border border-[#ececec] rounded-md w-14 h-8 flex items-center justify-center">
								<img class="w-11 h-6 object-contain" src="<?php echo esc_url( $payment_methods[ $payment_id ]['icon'] ?? '' ); ?>" alt="<?php echo esc_attr( $payment_method->get_method_name() ); ?>">
							</div>

							<label for="easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>"
								class="block text-[#272435] font-inter font-medium text-base leading-[26px] easycommerce-payment_method-name"><?php echo esc_html( $payment_method->get_method_name() ); ?>
							</label>
						</div>
						
						<div class="easycommerce-payment_method-form <?php echo esc_attr( $payment_form_class ); ?>"
							id="easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>-form"
							style="display: <?php echo $checked_attr ? 'block' : 'none'; ?>;">
							<?php echo wp_kses_post( $payment_method->payment_form() ); ?>
							<?php do_action( 'easycommerce_after_payment_form', $payment_id ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<span class="easycommerce-payment-method-error bg-[#FFF8F8] text-[#FF7373] px-3 py-1 text-[14px] rounded-md hidden"
			style="background-color: #FFF8F8; color: #FF7373;">
			<?php _e( 'Please select a payment method', 'easycommerce' ); ?>
		</span>
	</div>
</div>
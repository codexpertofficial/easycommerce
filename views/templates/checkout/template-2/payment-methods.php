<?php
defined( 'ABSPATH' ) || exit;

$has_physical = $cart_obj->has_item_type( 'physical' );
?>
<div class="easycommerce-payment-wrapper border border-ec-border bg-white rounded-lg p-4 md:p-[30px] mb-5">
	<div class="flex items-center mb-[30px]">
		<img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/checkout/payment-method.png' ); ?>"
			class="md:w-[53px] md:h-[53px] w-[45px] h-[42px] mr-4 rtl:ml-4 rtl:mr-0" />
		<h3
			class="easycommerce-billing-title font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
			<?php esc_html_e( 'Payment', 'easycommerce' ); ?>
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
					<div class="flex items-center lg:gap-5 gap-3 lg:mb-6 mb-3">
						<input type="radio"
							id="easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>"
							class="easycommerce-payment_method <?php echo esc_attr( $method_btn_class ); ?>"
							name="easycommerce-payment_method"
							value="<?php echo esc_attr( $payment_id ); ?>"
							<?php echo $checked_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- checked() output. ?>
						/>

						<div class="border border-[#ececec] rounded-md w-14 h-8 flex items-center justify-center">
							<img class="w-11 h-6 object-contain" src="<?php echo esc_url( $payment_methods[ $payment_id ]['icon'] ?? '' ); ?>" alt="<?php echo esc_attr( $payment_method->get_method_name() ); ?>">
						</div>
						
						<label for="easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>"
							class="block text-ec-body font-inter font-medium text-base leading-[26px] easycommerce-payment_method-name"><?php echo esc_html( $payment_method->get_method_name() ); ?></label>
					</div>
				</div>
				<div class="easycommerce-payment_method-form <?php echo esc_attr( $payment_form_class ); ?>"
					id="easycommerce-payment_method-<?php echo esc_attr( $payment_id ); ?>-form"
					style="display: <?php echo $checked_attr ? 'block' : 'none'; ?>;">
					<?php echo wp_kses_post( $payment_method->payment_form() ); ?>
					<?php do_action( 'easycommerce_after_payment_form', $payment_id ); ?>
				</div>
			</div>
			<?php
		}
		?>
		<span class="easycommerce-payment-method-error bg-[#FFF8F8] text-[#FF7373] px-3 py-1 text-[14px] rounded-md hidden"
			style="background-color: #FFF8F8; color: #FF7373;">
			<?php esc_html_e( 'Please select a payment method', 'easycommerce' ); ?>
		</span>
	</div>
</div>
<?php
/**
 * Checkout Template Two - the compact, digital-only checkout.
 *
 * DO NOT ADD A SHIPPING ADDRESS OR SHIPPING METHODS HERE.
 *
 * first_name, last_name, email and country only. Physical stores use template-1 or -3.
 */
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Cart;

$cart_obj   = new Cart();
$cart_total = $cart_obj->get_amount();
$cart       = $cart_obj->get( true );
$atts       = $args['atts'] ?? array();
?>
<div class="easycommerce-checkout-wrapper easycommerce-checkout-template-two">

    <form method="post" id="easycommerce-checkout"
        class="<?php echo esc_attr( ( isset( $atts['columns'] ) && (int) $atts['columns'] === 1 ) ? '' : 'grid grid-cols-1 lg:grid-cols-2' ); ?> lg:gap-[22px] mt-[35px]">

        <input type="hidden" name="items" value="<?php echo esc_attr( easycommerce_get_cart_hash() ); ?>">

        <?php if ( isset( $atts['columns'] ) && (int) $atts['columns'] === 1 ) : ?>

            <!-- CHECKOUT RIGHT FOR COLUMN-1 -->
            <div class="easycommerce-checkout-right">

                <?php do_action( 'easycommerce-before_cart_items' ); ?>

                <div class="easycommerce-cart-wrapper border border-ec-border bg-white rounded-lg p-3 md:p-[30px] mb-5">
                    <?php do_action( 'easycommerce/views/templates/checkout/items', $cart ); ?>
                </div>

                <?php
                do_action( 'easycommerce-after_cart_items' );
                do_action( 'easycommerce-before_cart_summary' );
                ?>

                <div class="easycommerce-summary-wrapper border border-ec-border bg-white rounded-lg p-7 mb-5">
                    <?php do_action( 'easycommerce/views/templates/checkout/summary', $cart, $cart_obj ); ?>
                </div>

                <?php do_action( 'easycommerce-after_cart_summary' ); ?>
            </div>

            <!-- CHECKOUT LEFT FOR COLUMN-1 -->
            <div class="easycommerce-checkout-left">

                <!-- START YOUR INFO FIELDS -->
                <div class="border border-ec-border bg-white rounded-lg p-3 md:p-7 pb-0 mb-5 easycommerce-clearfix">
                    <div class="flex items-center mb-4 md:mb-[30px] px-[2px]">
                        <img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/checkout/billing.png' ); ?>"
                            class="md:w-[53px] md:h-[53px] w-[45px] h-[42px] mr-2 lg:mr-4 rtl:ml-4 rtl:mr-0" />
                        <h3 class="easycommerce-billing-title font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
                            <?php esc_html_e( 'Your Info', 'easycommerce' ); ?>
                        </h3>
                    </div>

                    <div class="easycommerce-fieldset easycommerce-address easycommerce-checkout-billing" data-address_type="billing">
                        <?php
                        $easycommerce_checkout_fields = easycommerce_checkout_fields( 'billing' );
                        $compact_checkout_fields      = array_filter(
                            $easycommerce_checkout_fields,
                            function ( $args ) {
                                return in_array( $args['id'], array( 'first_name', 'last_name', 'email', 'country' ), true );
                            }
                        );

                        $billing_address = is_user_logged_in()
                            ? ( get_user_meta( get_current_user_id(), 'billing_address', true ) ?: array() )
                            : ( $cart_obj->get_billing_address() ?: array() );

                        foreach ( $compact_checkout_fields as $field_id => $field ) {
                            $field_factory = easycommerce_get_field_factory( $field['type'] );
                            if ( class_exists( $field_factory ) ) {
                                $field['name']  = "billing_address[{$field['id']}]";
                                $field['value'] = array_key_exists( $field['id'], $billing_address )
                                    ? $billing_address[ $field['id'] ]
                                    : '';
                                $field['id']    = "billing_{$field['id']}";
                                $field_obj      = new $field_factory( $field );
                                echo $field_obj->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                        }
                        ?>
                    </div>
                </div>
                <!-- END YOUR INFO FIELDS -->

                <?php do_action( 'easycommerce-before_cart_payment_methods' ); ?>

                <div class="easycommerce-payment-methods">
                    <?php
                    if ( $cart_total > 0 ) {
                        do_action( 'easycommerce/views/templates/checkout/payment_methods', $cart_obj );
                    }
                    ?>
                </div>

                <?php do_action( 'easycommerce-after_cart_payment_methods' ); ?>

                <!-- START SUBMIT -->
                <div>
                    <div class="flex mb-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" id="easycommerce-terms" name="terms" class="easycommerce-input-checkoutbox easycommerce-terms-input-checkoutbox rtl:mt-4" required />
                            <p class="text-[#737791] font-inter font-normal text-base leading-[26px] ml-2 rtl:mr-2">
                                <?php esc_html_e( 'By clicking this, I agree to ', 'easycommerce' ); ?>
                                <a href="<?php echo esc_url( easycommerce_terms_of_service_page( true ) ); ?>" class="text-ec-primary no-underline font-semibold">
                                    <?php esc_html_e( 'Terms & Conditions', 'easycommerce' ); ?>
                                </a>
                                <?php esc_html_e( ' and ', 'easycommerce' ); ?>
                                <a href="<?php echo esc_url( easycommerce_privacy_policy_page( true ) ); ?>" target="_blank" class="text-ec-primary no-underline font-semibold">
                                    <?php esc_html_e( 'Privacy Policy', 'easycommerce' ); ?>
                                </a>
                            </p>
                        </label>
                    </div>

                    <button type="submit"
                        class="easycommerce-checkout-main-btn text-white w-full font-inter bg-ec-primary group border border-ec-primary py-[11px] px-8 rounded-lg font-normal hover:text-white focus:bg-ec-primary focus:border-ec-primary hover:bg-ec-secondary focus:shadow-none focus:text-white hover:border-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px] mb-6">
                        <?php esc_html_e( 'Confirm Order', 'easycommerce' ); ?>
                    </button>

                    <div class="easycommerce-css-loader-wrapper w-full p-4 rounded-lg text-base leading-[26px] font-semibold hover:text-white focus:text-white bg-[#F4F0FF]"
                        style="display: none;">
                        <div id="loader" class="easycommerce-css-loader"></div>
                    </div>

                    <div id="easycommerce-checkout-order-error" class="w-full mt-3 p-3 rounded-lg text-sm font-inter text-[#FF3A52] bg-[#FFF0F2] border border-[#FF3A52]" style="display: none;"></div>
                </div>
                <!-- END SUBMIT -->
            </div>

        <?php else : ?>

            <!-- CHECKOUT LEFT -->
            <div class="easycommerce-checkout-left order-2 lg:order-1">

                <!-- START YOUR INFO FIELDS -->
                <div class="border border-ec-border bg-white rounded-lg p-3 md:p-7 pb-0 mb-5 easycommerce-clearfix">
                    <div class="flex items-center mb-4 md:mb-[30px] px-[2px]">
                        <img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'public/img/checkout/billing.png' ); ?>"
                            class="md:w-[53px] md:h-[53px] w-[45px] h-[42px] mr-2 lg:mr-4 rtl:ml-4 rtl:mr-0" />
                        <h3 class="easycommerce-billing-title font-inter leading-8 font-semibold !text-base md:!text-xl mb-0">
                            <?php esc_html_e( 'Your Info', 'easycommerce' ); ?>
                        </h3>
                    </div>

                    <div class="easycommerce-fieldset easycommerce-address easycommerce-checkout-billing" data-address_type="billing">
                        <?php
                        $easycommerce_checkout_fields = easycommerce_checkout_fields( 'billing' );
                        $compact_checkout_fields      = array_filter(
                            $easycommerce_checkout_fields,
                            function ( $args ) {
                                return in_array( $args['id'], array( 'first_name', 'last_name', 'email', 'country' ), true );
                            }
                        );

                        $billing_address = is_user_logged_in()
                            ? ( get_user_meta( get_current_user_id(), 'billing_address', true ) ?: array() )
                            : ( $cart_obj->get_billing_address() ?: array() );

                        foreach ( $compact_checkout_fields as $field_id => $field ) {
                            $field_factory = easycommerce_get_field_factory( $field['type'] );
                            if ( class_exists( $field_factory ) ) {
                                $field['name']  = "billing_address[{$field['id']}]";
                                $field['value'] = array_key_exists( $field['id'], $billing_address )
                                    ? $billing_address[ $field['id'] ]
                                    : '';
                                $field['id']    = "billing_{$field['id']}";
                                $field_obj      = new $field_factory( $field );
                                echo $field_obj->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                        }
                        ?>
                    </div>
                </div>
                <!-- END YOUR INFO FIELDS -->

                <?php do_action( 'easycommerce-before_cart_payment_methods' ); ?>

                <div class="easycommerce-payment-methods">
                    <?php
                    if ( $cart_total > 0 ) {
                        do_action( 'easycommerce/views/templates/checkout/payment_methods', $cart_obj );
                    }
                    ?>
                </div>

                <?php do_action( 'easycommerce-after_cart_payment_methods' ); ?>

                <!-- START SUBMIT -->
                <div>
                    <div class="flex mb-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" id="easycommerce-terms" name="terms" class="easycommerce-input-checkoutbox easycommerce-terms-input-checkoutbox rtl:mt-4" required />
                            <p class="text-[#737791] font-inter font-normal text-base leading-[26px] ml-2 rtl:mr-2">
                                <?php esc_html_e( 'By clicking this, I agree to ', 'easycommerce' ); ?>
                                <a href="<?php echo esc_url( easycommerce_terms_of_service_page( true ) ); ?>" class="text-ec-primary no-underline font-semibold">
                                    <?php esc_html_e( 'Terms & Conditions', 'easycommerce' ); ?>
                                </a>
                                <?php esc_html_e( ' and ', 'easycommerce' ); ?>
                                <a href="<?php echo esc_url( easycommerce_privacy_policy_page( true ) ); ?>" target="_blank" class="text-ec-primary no-underline font-semibold">
                                    <?php esc_html_e( 'Privacy Policy', 'easycommerce' ); ?>
                                </a>
                            </p>
                        </label>
                    </div>

                    <button type="submit"
                        class="easycommerce-checkout-main-btn text-white w-full font-inter bg-ec-primary group border border-ec-primary py-[11px] px-8 rounded-lg font-normal hover:text-white focus:bg-ec-primary focus:border-ec-primary hover:bg-ec-secondary focus:shadow-none focus:text-white hover:border-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px] mb-6">
                        <?php esc_html_e( 'Confirm Order', 'easycommerce' ); ?>
                    </button>

                    <div class="easycommerce-css-loader-wrapper w-full p-4 rounded-lg text-base leading-[26px] font-semibold hover:text-white focus:text-white bg-[#F4F0FF]"
                        style="display: none;">
                        <div id="loader" class="easycommerce-css-loader"></div>
                    </div>

                    <div id="easycommerce-checkout-order-error" class="w-full mt-3 p-3 rounded-lg text-sm font-inter text-[#FF3A52] bg-[#FFF0F2] border border-[#FF3A52]" style="display: none;"></div>
                </div>
                <!-- END SUBMIT -->
            </div>

            <!-- CHECKOUT RIGHT -->
            <div class="easycommerce-checkout-right order-1 lg:order-2">

                <?php do_action( 'easycommerce-before_cart_items' ); ?>

                <div class="easycommerce-cart-wrapper border border-ec-border bg-white rounded-lg p-3 md:p-[30px] mb-5">
                    <?php do_action( 'easycommerce/views/templates/checkout/items', $cart ); ?>
                </div>

                <?php
                do_action( 'easycommerce-after_cart_items' );
                do_action( 'easycommerce-before_cart_summary' );
                ?>

                <div class="easycommerce-summary-wrapper border border-ec-border bg-white rounded-lg p-7 mb-5">
                    <?php do_action( 'easycommerce/views/templates/checkout/summary', $cart, $cart_obj ); ?>
                </div>

                <?php do_action( 'easycommerce-after_cart_summary' ); ?>
            </div>

        <?php endif; ?>

    </form>
</div>

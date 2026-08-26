<?php
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Cart;

$cart_obj   = new Cart();
$cart_total = $cart_obj->get_amount();
$cart       = $cart_obj->get( true );
$atts       = $args['atts'] ?? array();
?>
<div class="easycommerce-checkout-wrapper easycommerce-checkout-template-three">

    <form method="post" id="easycommerce-checkout"
        class="<?php echo esc_attr( ( isset( $atts['columns'] ) && (int) $atts['columns'] === 1 ) ? '' : 'grid grid-cols-1 lg:grid-cols-2' ); ?> lg:gap-[22px] mt-[35px]">

        <input type="hidden" name="items" value="<?php echo esc_attr( easycommerce_get_cart_hash() ); ?>">

        <!-- CHECKOUT LEFT -->
        <div class="easycommerce-checkout-left">
            <div class="w-full h-auto border border-ec-border bg-[#F6F8FA] rounded-2xl p-4 md:p-12 mb-6">

                <a href="<?php echo esc_url( get_permalink( easycommerce_shop_page() ) ); ?>" class="w-full flex gap-3 mb-4 no-underline">
                    <div class="w-8 h-8 rounded-full bg-white border border-ec-border flex items-center justify-center">
                        <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.1875 0.718749L0.718749 5.46875C0.572915 5.63542 0.499999 5.8125 0.499999 6C0.499999 6.1875 0.572915 6.35417 0.718749 6.5L5.1875 11.25C5.54167 11.5625 5.89583 11.5729 6.25 11.2812C6.5625 10.9479 6.57292 10.5937 6.28125 10.2187L2.28125 6L6.28125 1.75C6.57292 1.39583 6.57292 1.05208 6.28125 0.718749C5.90625 0.427082 5.54167 0.427082 5.1875 0.718749Z" fill="#272435" />
                        </svg>
                    </div>
                    <p class="font-inter font-normal text-base text-[#000000] mt-1">
                        <?php esc_html_e( 'Back to shop', 'easycommerce' ); ?>
                    </p>
                </a>

                <?php do_action( 'easycommerce-before_cart_items' ); ?>

                <h3 class="font-inter leading-8 font-medium text-xl text-[#272435] mb-4">
                    <?php esc_html_e( 'Product items', 'easycommerce' ); ?>
                </h3>

                <div class="easycommerce-cart-wrapper">
                    <?php do_action( 'easycommerce/views/templates/checkout/items', $cart ); ?>
                </div>

                <?php do_action( 'easycommerce-after_cart_items' ); ?>
                <?php do_action( 'easycommerce-before_cart_billing' ); ?>
                <?php do_action( 'easycommerce/views/templates/checkout/billing', $cart_obj ); ?>
                <?php do_action( 'easycommerce-after_cart_billing' ); ?>
                <?php do_action( 'easycommerce-before_cart_shipping' ); ?>
                <?php do_action( 'easycommerce/views/templates/checkout/shipping', $cart_obj ); ?>
                <?php do_action( 'easycommerce-after_cart_shipping' ); ?>
            </div>
        </div>

        <!-- CHECKOUT RIGHT -->
        <div class="easycommerce-checkout-right">
            <div class="w-full h-auto border border-ec-border bg-white rounded-2xl p-4 md:p-12 mb-8">

                <?php do_action( 'easycommerce-before_cart_summary' ); ?>

                <div class="easycommerce-summary-wrapper mt-12 mb-5">
                    <?php do_action( 'easycommerce/views/templates/checkout/summary', $cart, $cart_obj ); ?>
                </div>

                <?php do_action( 'easycommerce-after_cart_summary' ); ?>
                <?php do_action( 'easycommerce-before_cart_payment_methods' ); ?>

                <div class="easycommerce-payment-methods">
                    <?php
                    if ( $cart_total > 0 ) {
                        do_action( 'easycommerce/views/templates/checkout/payment_methods', $cart_obj );
                    }
                    ?>
                </div>

                <?php do_action( 'easycommerce-after_cart_payment_methods' ); ?>

                <div class="mt-0">
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
                        class="easycommerce-checkout-main-btn text-white text-base w-full font-inter bg-ec-primary group border border-ec-primary py-[11px] px-8 rounded-lg font-semibold hover:text-white focus:bg-ec-primary focus:border-ec-primary hover:bg-ec-secondary focus:shadow-none focus:text-white hover:border-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px]">
                        <?php esc_html_e( 'Pay Now', 'easycommerce' ); ?>
                    </button>

                    <div class="easycommerce-css-loader-wrapper w-full p-4 rounded-lg text-base leading-[26px] font-semibold hover:text-white focus:text-white bg-[#F4F0FF]"
                        style="display: none;">
                        <div id="loader" class="easycommerce-css-loader"></div>
                    </div>

                    <div id="easycommerce-checkout-order-error" class="w-full mt-3 p-3 rounded-lg text-sm font-inter text-[#FF3A52] bg-[#FFF0F2] border border-[#FF3A52]" style="display: none;"></div>
                </div>
            </div>
        </div>
    </form>
</div>

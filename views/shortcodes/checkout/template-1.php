<?php
use EasyCommerce\Models\Cart;

$cart_obj   = new Cart();
$cart_total = $cart_obj->get_amount();
$cart       = $cart_obj->get( true );
$atts       = $args['atts'] ?? array();
?>
<div class="easycommerce-checkout-wrapper easycommerce-checkout-template-one">

    <form method="post" id="easycommerce-checkout"
        class="<?php echo esc_attr( ( isset( $atts['columns'] ) && (int) $atts['columns'] === 1 ) ? '' : 'grid grid-cols-1 lg:grid-cols-2' ); ?> gap-[22px] mt-[35px]">

        <input type="hidden" name="items" value="<?php echo esc_attr( easycommerce_get_cart_hash() ); ?>">

        <!-- CHECKOUT LEFT -->
        <div class="easycommerce-checkout-left">
            <?php do_action( 'easycommerce-before_cart_billing' ); ?>
            <?php do_action( 'easycommerce/views/templates/checkout/billing', $cart_obj ); ?>
            <?php do_action( 'easycommerce-after_cart_billing' ); ?>

            <?php do_action( 'easycommerce-before_cart_shipping' ); ?>
            <?php do_action( 'easycommerce/views/templates/checkout/shipping', $cart_obj ); ?>
            <?php do_action( 'easycommerce-after_cart_shipping' ); ?>
        </div>

        <!-- CHECKOUT RIGHT -->
        <div class="easycommerce-checkout-right mb-6">

            <?php do_action( 'easycommerce-before_cart_items' ); ?>

            <div class="easycommerce-cart-wrapper border border-ec-border bg-white rounded-lg p-3 md:p-[30px] mb-5">
                <?php do_action( 'easycommerce/views/templates/checkout/items', $cart ); ?>
            </div>

            <?php do_action( 'easycommerce-after_cart_items' ); ?>
            <?php do_action( 'easycommerce-before_cart_summary' ); ?>

            <div class="easycommerce-summary-wrapper border border-ec-border bg-white rounded-lg p-4 md:p-7 mb-5">
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

            <div>
                <button type="submit"
                    class="easycommerce-checkout-main-btn text-white w-full font-inter bg-ec-primary group border border-ec-primary py-[11px] px-8 rounded-lg font-normal hover:text-white focus:bg-ec-primary focus:border-ec-primary hover:bg-ec-secondary focus:shadow-none focus:text-white hover:border-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px]">
                    <?php esc_html_e( 'Confirm Order', 'easycommerce' ); ?>
                </button>

                <div class="easycommerce-css-loader-wrapper w-full p-4 rounded-lg text-base leading-[26px] font-semibold hover:text-white focus:text-white bg-[#F4F0FF]"
                    style="display: none;">
                    <div id="loader" class="easycommerce-css-loader"></div>
                </div>

                <div id="easycommerce-checkout-order-error" class="w-full mt-3 p-3 rounded-lg text-sm font-inter text-[#FF3A52] bg-[#FFF0F2] border border-[#FF3A52]" style="display: none;"></div>
            </div>
        </div>
    </form>
</div>

<?php
use EasyCommerce\Models\Order as Order_Model;
use EasyCommerce\Models\Order_Item_Meta;

defined( 'ABSPATH' ) || exit;

$order_id = (int) ( $_GET['order_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification

if ( ! $order_id ) {
	echo '<p class="text-center py-10 text-ec-placeholder">' . esc_html__( 'No order specified.', 'easycommerce' ) . '</p>';
	return;
}

$order = new Order_Model( $order_id );

if ( ! $order->exists() ) {
	echo '<p class="text-center py-10 text-ec-placeholder">' . esc_html__( 'Order not found.', 'easycommerce' ) . '</p>';
	return;
}

if ( $order->get_status() !== 'pending' ) {
	echo '<p class="text-center py-10 text-ec-placeholder">' . esc_html__( 'This order has already been processed.', 'easycommerce' ) . '</p>';
	return;
}

$raw_items       = $order->get_items();
$order_item_meta = new Order_Item_Meta();
$items           = array_map( function( $item ) use ( $order_item_meta ) {
	$item->meta = $order_item_meta->get( $item->id );
	return $item;
}, $raw_items );
$billing      = $order->get_meta( 'billing_address' );
$total        = $order->get_total();

$stub_cart = new class( $order ) {
	private $order;
	public function __construct( $order ) { $this->order = $order; }
	public function has_item_type( $type ) { return $type === 'physical'; }
	public function get_payment_method() { return ''; }
	public function get_items() { return array(); }
};
?>
<script>
(function() {
    var _orderTotal = <?php echo (float) $total; ?>;
    var _orderId    = <?php echo (int) $order_id; ?>;
    var _origFetch  = window.fetch.bind(window);

    window.fetch = function(url, opts) {
        if (typeof url === 'string') {
            // Intercept GET /cart → return order amounts
            if (/\/cart(\?.*)?$/.test(url) && (!opts || !opts.method || opts.method === 'GET')) {
                return Promise.resolve(new Response(JSON.stringify({
                    success: true,
                    data: {
                        cart: {
                            amounts: { total: _orderTotal, subtotal: _orderTotal, discount_amount: 0, shipping_fee: 0, tax: 0, fees: 0 },
                            items: []
                        }
                    }
                }), { status: 200, headers: { 'Content-Type': 'application/json' } }));
            }

            // Intercept POST /stripe/payment-intent → inject order_id
            if (/\/stripe\/payment-intent(\?.*)?$/.test(url) && opts && opts.method === 'POST') {
                var body = {};
                try { body = JSON.parse(opts.body || '{}'); } catch(e) {}
                body.order_id = _orderId;
                opts = Object.assign({}, opts, { body: JSON.stringify(body) });
            }
        }
        return _origFetch(url, opts);
    };
})();
</script>

<div class="easycommerce-payment-page-wrapper max-w-2xl mx-auto my-10">

	<div class="border border-ec-border bg-white rounded-lg p-4 md:p-7 mb-5">
		<h3 class="font-inter font-semibold text-xl text-ec-body mb-4">
			<?php
			/* translators: %d: order ID */
			printf( esc_html__( 'Order #%d', 'easycommerce' ), $order_id );
			?>
		</h3>

		<?php if ( ! empty( $billing ) ) : ?>
		<p class="text-ec-placeholder text-sm mb-1">
			<?php echo esc_html( ( $billing['first_name'] ?? '' ) . ' ' . ( $billing['last_name'] ?? '' ) ); ?>
			&bull;
			<?php echo esc_html( $billing['email'] ?? '' ); ?>
		</p>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
		<table class="w-full text-sm mt-4">
			<tbody>
			<?php foreach ( $items as $item ) : ?>
				<tr class="border-b border-ec-border">
					<td class="py-2 text-ec-body">
						<?php echo esc_html( $item->meta['name'] ?? '' ); ?>
						<?php if ( ! empty( $item->meta['attributes'] ) ) : ?>
							<span class="text-ec-placeholder"> &mdash; <?php echo esc_html( $item->meta['attributes'] ); ?></span>
						<?php endif; ?>
					</td>
					<td class="py-2 text-right text-ec-placeholder">
						&times;<?php echo (int) $item->quantity; ?>
					</td>
					<td class="py-2 text-right font-medium text-ec-body">
						<?php echo wp_kses_post( easycommerce_price( $item->subtotal ) ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
				<?php
					$subtotal = $order->get_subtotal();
					$discount = $order->get_discount_total();
					$shipping = $order->get_shipping_total();
					$tax      = $order->get_tax_total();
				?>
				<?php if ( $subtotal > 0 && $subtotal !== $total ) : ?>
				<tr>
					<td colspan="2" class="pt-3 text-sm text-ec-placeholder"><?php esc_html_e( 'Subtotal', 'easycommerce' ); ?></td>
					<td class="pt-3 text-right text-sm text-ec-placeholder"><?php echo wp_kses_post( easycommerce_price( $subtotal ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( $discount > 0 ) : ?>
				<tr>
					<td colspan="2" class="pt-1 text-sm text-ec-placeholder"><?php esc_html_e( 'Discount', 'easycommerce' ); ?></td>
					<td class="pt-1 text-right text-sm text-ec-placeholder">&minus;<?php echo wp_kses_post( easycommerce_price( $discount ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( $shipping > 0 ) : ?>
				<tr>
					<td colspan="2" class="pt-1 text-sm text-ec-placeholder"><?php esc_html_e( 'Shipping', 'easycommerce' ); ?></td>
					<td class="pt-1 text-right text-sm text-ec-placeholder"><?php echo wp_kses_post( easycommerce_price( $shipping ) ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( $tax > 0 ) : ?>
				<tr>
					<td colspan="2" class="pt-1 text-sm text-ec-placeholder"><?php esc_html_e( 'Tax', 'easycommerce' ); ?></td>
					<td class="pt-1 text-right text-sm text-ec-placeholder"><?php echo wp_kses_post( easycommerce_price( $tax ) ); ?></td>
				</tr>
				<?php endif; ?>
				<tr class="border-t border-ec-border">
					<td colspan="2" class="pt-4 font-semibold text-ec-body"><?php esc_html_e( 'Total', 'easycommerce' ); ?></td>
					<td class="pt-4 text-right font-semibold text-ec-body"><?php echo wp_kses_post( easycommerce_price( $total ) ); ?></td>
				</tr>
			</tfoot>
		</table>
		<?php endif; ?>
	</div>

	<form id="easycommerce-checkout" data-order-id="<?php echo esc_attr( $order_id ); ?>">
		<?php
		$payment_methods_map = easycommerce_payment_methods();
		foreach ( $payment_methods_map as $pm_id => $pm_data ) {
			if ( ! isset( $pm_data['class'] ) || ! class_exists( $pm_data['class'] ) ) continue;
			$pm_obj = new $pm_data['class']();
			if ( $pm_obj->is_offline() ) {
				add_filter( 'easycommerce_unset_payment_method_' . $pm_id, '__return_true' );
			}
		}
	?>
	<?php do_action( 'easycommerce/views/templates/checkout/payment_methods', $stub_cart ); ?>

		<div id="easycommerce-payment-message" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

		<button type="submit"
			class="easycommerce-checkout-main-btn text-white w-full font-inter bg-ec-primary border border-ec-primary py-[11px] px-8 rounded-lg font-normal hover:text-white hover:bg-ec-secondary hover:border-ec-secondary transition-all ease-in-out duration-500 leading-[26px]">
			<?php esc_html_e( 'Pay Now', 'easycommerce' ); ?>
		</button>

		<div class="easycommerce-css-loader-wrapper w-full p-4 rounded-lg text-base leading-[26px] font-semibold bg-[#F4F0FF]" style="display:none;">
			<div id="loader" class="easycommerce-css-loader"></div>
		</div>
	</form>

</div>

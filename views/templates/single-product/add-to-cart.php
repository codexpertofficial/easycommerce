<?php
use EasyCommerce\Models\Product as Product_Model;
use EasyCommerce\Helpers\Utility;

$product         = new Product_Model( get_the_ID() );
$stock           = $product->get_stock();
$variations      = $product->get_variations();
$first_variation = $variations ? $variations[0] : null;
$type            = $first_variation ? $first_variation->get_type() : null;
?>
<label class="block text-ec-body font-inter font-normal text-base leading-[26px] mb-4">
	<?php if ( $stock > 0 ) : ?>
		<?php // printf( esc_html__( 'Quantity: %s', 'easycommerce' ), $stock ); ?>
	<?php elseif ( 0 === $stock ) : ?>
		<div class="text-ec-body font-inter text-base leading-[26px] flex items-center gap-1">
			<div class="w-2 h-2 rounded-full bg-red-500"></div>
			<span class="easycommerce-stock-count text-red-500"><?php esc_html_e( 'Out of stock', 'easycommerce' ); ?></span> 
		</div>
	<?php endif; ?>
</label>
<div class="mb-4">

	<!-- Quantity input  -->
	<form class="easycommerce-add-to-cart w-[168px] mb-8">
		<div class="flex items-center border border-[#1203501A] rounded-md bg-white">
			<button type="button"
				class="easycommerce-quantity-minus-btn rounded-none w-[50px] h-[50px] border-r border-[#1203501A] font-inter leading-[26px] text-2xl bg-white rounded-l-md hover:bg-white focus:bg-white focus:border-[#1203501A] hover:border-[#1203501A] hover:text-ec-secondary active:text-ec-secondary focus:text-ec-secondary">
				-
			</button>
			<input
				data-stock-count="<?php echo esc_attr( $stock ); ?>"
				data-type = "<?php echo esc_attr( $type ); ?>"
				class="easycommerce-qunatity-input text-ec-body font-inter font-semibold text-base leading-[26px] text-center"
				type="text" value="1" min="1" id="quantity" name="quantity" />
			<button type="button"
				class="easycommerce-quantity-plus-btn rounded-none w-[50px] h-[50px] border-l border-[#1203501A] font-inter leading-[26px] text-2xl bg-white rounded-r-md hover:bg-white focus:bg-white focus:border-[#1203501A] hover:text-ec-secondary active:text-ec-secondary focus:text-ec-secondary">
				+
			</button>
		</div>
	</form>

	<!-- Add to cart button  -->
	<button type="button"
		class="easycommerce-add-to-cart-button flex items-center justify-center font-inter font-semibold text-base leading-[26px] bg-ec-primary p-3 w-full rounded-md"
		<?php echo ( 0 === $stock ) ? 'disabled' : ''; ?>>
		<span id="buttonText" class="text-white"><?php esc_html_e( 'Add to Cart', 'easycommerce' ); ?></span>
		<div id="loader" class="loader" style="display: none;"></div>
	</button>

</div>

<!-- Go to Checkout Button -->
<div class="easycommerce-single-product-checkout-btn">
	<a href="<?php echo esc_url( get_permalink( easycommerce_cart_redirect() ) ); ?>"
		class="easycommcer-proceed-checkout-btn flex items-center gap-3 justify-center p-3 font-inter text-base leading-[26px] w-full border-none rounded-md text-ec-primary focus:text-ec-primary hover:text-ec-primary font-medium"
		style="outline:none"><?php esc_html_e( 'Proceed to checkout', 'easycommerce' ); ?> <img className="ml-3" height="10" width="26"
			src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/arrow-blue-right.png' ); ?>" alt="">
	</a>
</div>
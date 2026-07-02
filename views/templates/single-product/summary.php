<?php
use EasyCommerce\Models\Product as Product_Model;

if ( $product = new Product_Model( get_the_ID() ) ) {
	
	if ( $summary = $product->get_summary() ) {
		
		$word_count = str_word_count( $summary );

		$show_read_more = $word_count > 30;

		if ( $show_read_more ) {
			$short_summary = wp_trim_words( $summary, 30, '...' );
		} else {
			$short_summary = $summary;
		}
        ?>

		<div class="ec-summary-text">
			<div class="short-text"<?php echo $show_read_more ? '' : ' style="display:none;"'; ?>>
				<?php printf( __( '%s', 'easycommerce' ), esc_html( $short_summary ) ); ?>
				<?php if ( $show_read_more ) : ?>
					<a class="show-more cursor-pointer font-bold"><?php esc_html_e( 'Read More', 'easycommerce' ); ?></a>
				<?php endif; ?>
			</div>

			<div class="full-text" <?php echo $show_read_more ? 'style="display:none;"' : ''; ?>>
				<?php printf( __( '%s', 'easycommerce' ), esc_html( $summary ) ); ?>
				<?php if ( $show_read_more ) : ?>
					<a class="show-less cursor-pointer font-bold"><?php esc_html_e( 'Show Less', 'easycommerce' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<?php
	}
}
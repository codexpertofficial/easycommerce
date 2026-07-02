<?php

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product;

$full_star  = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/full-star.png';
$half_star  = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/half-star.png';
$empty_star = EASYCOMMERCE_ASSETS_URL . 'common/img/blocks/shop-page/empty-star.png';

$product = new Product( get_the_ID() );
$reviews = $product->get_reviews();

if ( get_post_type( get_the_ID() ) !== 'product' ) {
	echo 'Post type is not product';
	return;
}
?>

<div class="easycommerce-reviews-warpper">
	<?php
	if ( ! empty( $reviews ) ) {
		?>
				<div class="easycommerce-reviews-header mb-12">
					<h2 class="font-inter text-xl font-semibold leading-8 text-ec-body !mb-1">
					<?php esc_html_e( 'Reviews', 'easycommerce' ); ?> 
					</h2>
					<span class="text-ec-placeholder font-inter text-[12px] font-medium leading-5">
					<?php
					/* Translators: %d is the review count */
					printf( esc_html__( ' Showing  reviews %d', 'easycommerce' ), count( $reviews ) );
					?>
					</span>
				</div>
				<div class="easycommerce-reviews">
			<?php
			foreach ( $reviews as $review ) {
				?>
						<div class="easycommerce-single-reivew mb-4">
							<div class="flex items-center">
								<img
									class="w-[45px] h-[45px] rounded-full border border-[#DBDBDB] mr-3"
									src="<?php echo esc_attr( $review['user']['photo'] ); ?>"
									alt="<?php echo esc_attr( $review['user']['name'] ); ?>"
								/>
								<div class="w-full flex flex-col">
									<div class="easycommerce-reviews-user-wrapper flex items-center justify-between">
										<h3 class="text-ec-body font-inter font-semibold !text-base !leading-[26px] !mb-0">
											<?php echo esc_html( $review['user']['name'] ); ?>
										</h3>
										<span class="text-[#737373] text-xs">
											<?php echo esc_html( date( 'F j, Y, g:i a', strtotime( $review['time'] ) ) ); ?>
										</span>
									</div>                                            
						<?php
							$stars_output = '';
							$star_size    = 16;
							$rating       = $review['rating'];

						for ( $i = 0; $i < 5; $i++ ) {
							if ( $rating >= $i + 1 ) {
								$stars_output .= '<span><img src="' . esc_url( $full_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
							} elseif ( $rating > $i && $rating < $i + 1 ) {
								$stars_output .= '<span><img src="' . esc_url( $half_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
							} else {
								$stars_output .= '<span><img src="' . esc_url( $empty_star ) . '" style="width:' . $star_size . 'px; height:' . $star_size . 'px;"></span>';
							}
						}

						?>
									<div class="flex flex-row items-center gap-1">
							<?php echo $stars_output; ?>
											(<?php echo esc_html( $rating ); ?>)
										</span>
									</div>
								</div>
							</div>
							<div class="ml-[70px] border-b-2 border-b-ec-border">
								<p class="font-inter text-base leading-[26px] font-normal text-ec-body mt-4 mb-6">
									<?php echo esc_html( $review['text'] ); ?>
								</p>
							</div>
						</div>
					<?php
			}
			?>
			</div>
		<?php
	}
	?>

	<div class="easycommerce-reviews-form border border-ec-border mt-11 rounded-xl p-6">
		<h2 class="font-inter text-xl font-semibold leading-8 text-ec-body !mb-1">
			<?php esc_html_e( 'Write a review', 'easycommerce' ); ?>
		</h2>
		
		<?php
		if ( is_user_logged_in() ) {
			?>
				<p class="font-inter !text-[12px] font-normal leading-5 !text-ec-placeholder">
					<?php esc_html_e( 'Your email address will not be published. required fields are marked', 'easycommerce' ); ?>
					<span class="text-[#FF3A52] font-inter text-[12px]">
						<?php esc_html_e( '*', 'easycommerce' ); ?>
					</span>
				</p>
				<form class="easycommerce-review-form">
					<div>
						<style>
							.star-rating {
								display: flex;
								gap: 8px;
							}

							.star {
								width: 24px;
								cursor: pointer;
							}

							.star.full {
								content: url('<?php echo $full_star; ?>');
							}
						</style>                              
						<div class="easycommerce-star-rating-wrap flex items-center gap-[10px] mb-4">
							<div>
								<span><?php esc_html_e( 'Your Rating', 'easycommerce' ); ?></span>
								<span class="text-[#FF3A52] font-inter text-base">
									<?php esc_html_e( '*', 'easycommerce' ); ?>
								</span>
							</div>
							<div class="star-rating">
								<input type="hidden" id="easycommerce-star-rating-value" value="0">
								<img class="star empty" src="<?php echo esc_attr( $empty_star ); ?>" data-value="1" alt="Star">
								<img class="star empty" src="<?php echo esc_attr( $empty_star ); ?>" data-value="2" alt="Star">
								<img class="star empty" src="<?php echo esc_attr( $empty_star ); ?>" data-value="3" alt="Star">
								<img class="star empty" src="<?php echo esc_attr( $empty_star ); ?>" data-value="4" alt="Star">
								<img class="star empty" src="<?php echo esc_attr( $empty_star ); ?>" data-value="5" alt="Star">
							</div>
						</div>
					</div>

					<div>
						<textarea
							class="easycommerce-single-product-review-text w-full p-[15px] mb-5 rounded-md resize-none"
							name=""
							id=""
							placeholder="Write your review"
						></textarea>
					</div>
					<button
						id="easycommerce-single-product-review-submit-btn"
						class="py-[15px] px-[45px] bg-ec-primary rounded-md text-white font-medium font-inter leading-[26px] hover:bg-ec-primary hover:text-white active:bg-ec-primary active:text-white focus:bg-ec-primary focus:text-white"
						type="submit"
					>
					<?php esc_html_e( 'Submit Now', 'easycommerce' ); ?>
					</button>
					<p class="easycommerce-rating-message mt-3" style="display: none;">
						<?php esc_html_e( 'Rating and review is required', 'easycommerce' ); ?>
					</p>
				</form>
				<?php
		} else {
			printf( '<p class="mt-5 text-ec-body">Please Login first to write a review</p>' );
		}
		?>
	</div>
</div>

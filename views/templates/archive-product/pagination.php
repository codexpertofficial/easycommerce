<div class="easycommerce-pagination mt-6 w-full flex justify-center">
	<?php
	the_posts_pagination(
		array(
			'mid_size'           => 2,
			'prev_text'          => '<span class="inline-block px-4 py-2 mx-1 border border-[#03836c] rounded bg-[#03836c] text-white hover:bg-[#026654]">« ' . __( 'Previous', 'easycommerce' ) . '</span>',
			'next_text'          => '<span class="inline-block px-4 py-2 mx-1 border border-[#03836c] rounded bg-[#03836c] text-white hover:bg-[#026654]">' . __( 'Next »', 'easycommerce' ) . '</span>',
			'before_page_number' => '<span class="inline-block px-3 py-1 mx-1 border border-[#03836c] rounded bg-[#03836c] text-white hover:bg-[#026654]">',
			'after_page_number'  => '</span>',
		)
	);
	?>
</div>

<?php
/**
 * Pagination template for Shop Template 1.
 *
 * Displays numbered pagination links for navigating through product pages.
 *
 * @global int $total_pages Total number of pages.
 * @global int $current_page Current page number.
 */
?>
<!-- Pagination -->
<?php if( $total_pages > 1 ) : ?>
    <div class="pagination-container mt-12">
            <div class="pagination-buttons flex justify-center gap-2">
                <button class="easycommerce-pagination-link bg-white text-black w-20 h-9 border border-ec-border flex items-center justify-center gap-2" data-page="1">
                    <span class="text-[#000]">
                        <svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5"></path>
                        </svg>
                    </span>
                    <?php esc_html_e( 'First', 'easycommerce' ); ?>
                </button>
                <?php 
                $range  = 2;
                $start  = max( 2, $current_page - $range ); 
                $end    = min( $total_pages - 1, $current_page + $range ); 
                if ( $start > 2 ) : ?>
                    <button class="easycommerce-pagination-link bg-white text-black w-8 h-9 border border-ec-border" disabled>
                        ...
                    </button>
                <?php endif; ?>

                <?php 
                for ( $i = $start; $i <= $end; $i++ ) : ?>
                    <button class="easycommerce-pagination-link bg-white text-black w-8 h-9 border border-ec-border" data-page="<?php echo esc_attr( $i ); ?>">
                        <?php echo esc_html( $i ); ?>
                    </button>
                <?php endfor; ?>

                <?php
                if ( $end < $total_pages - 1 ) : ?>
                    <button class="easycommerce-pagination-link bg-white text-black w-8 h-9 border border-ec-border" disabled>
                        ...
                    </button>
                <?php endif; ?>

                <button class="easycommerce-pagination-link bg-white text-black w-20 h-9 border border-ec-border flex items-center justify-center gap-2" data-page="<?php echo esc_attr( $total_pages ); ?>">
                    <?php esc_html_e( 'Last', 'easycommerce' ); ?>
                    <span class="text-[#000]"> 
                        <svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5"></path>
                        </svg>
                    </span>
                </button>
            </div>
       
    </div>
<?php endif; ?>

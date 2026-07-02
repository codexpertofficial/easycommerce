<div class='easycommerce-product-section product-title-section p-6 flex gap-11 items-center mb-6'>
    <input class="product-title" type="text" name="product_name" placeholder="Add your product title here">

    <div class="flex items-center justify-between gap-5">
        <select class="product-status" name="product-status">
            <option value="publish">
                <?php esc_html_e( 'Published', 'easycommerce' ); ?>
            </option>
            <option value="draft">
                <?php esc_html_e( 'Draft', 'easycommerce' ); ?>
            </option>
            <option value="private">
                <?php esc_html_e( 'Private', 'easycommerce' ); ?>
            </option>
        </select>

        <button class="easycommerce-primary-button">
            <?php esc_html_e( 'Save Product', 'easycommerce' ); ?>
        </button>
    </div>
</div>
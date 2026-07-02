<?php
use EasyCommerce\Helpers\Utility;

echo Utility::get_template( 'store/common/header.php', ['slug' => 'Add Product'] );
?>

<div class="flex flex-row">
    <div class="w-[230px] bg-white h-screen">
        <?php echo Utility::get_template( 'store/common/sidebar.php' ); ?>
    </div>
    <div class="grow-[1] p-5">
        <?php 
        echo Utility::get_template( 'store/add-product/title.php' );
        echo Utility::get_template( 'store/add-product/draggable-grid.php' );
        ?>
    </div>
</div>
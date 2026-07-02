<?php
use EasyCommerce\Helpers\Utility;

echo Utility::get_template( 'store/common/header.php', ['slug' => 'Transactions'] );
?>

<div class="flex flex-row">
    <div class="w-[230px] bg-white h-screen">
        <?php echo Utility::get_template( 'store/common/sidebar.php' ); ?>
    </div>
    <div class="grow-[1] p-5">
        <?php 
        echo Utility::get_template( 'store/transactions/header.php' );
        ?>
    </div>
</div>
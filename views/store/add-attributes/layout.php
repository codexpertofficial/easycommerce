<?php
use EasyCommerce\Helpers\Utility;

echo Utility::get_template( 'store/common/header.php', ['slug' => 'Add Attributes'] );
?>

<div class="flex flex-row">
    <div class="w-[230px] bg-white h-screen">
        <?php echo Utility::get_template( 'store/common/sidebar.php' ); ?>
    </div>
    <div class="w-full p-5">
    <?php 
        echo Utility::get_template( 'store/add-attributes/add-new.php' );
    ?>
    </div>
</div>
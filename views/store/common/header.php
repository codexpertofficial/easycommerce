<?php
use EasyCommerce\Helpers\Utility;
$logo               = EASYCOMMERCE_ASSETS_URL . 'common/img/logo.png';
$breadcrumb_icon    = EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/breadcump.png';
$right_divider      = EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/header-right.png';
$user_icon          = EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/header-default-user-icon.png';
$profile_icon       = EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/profile-icon.png';
$disconnect_icon    = EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/disconnect-icon.png';
$breadcrumb_slug    = $args['slug'];

?>
<div class="flex flex-row bg-white">
    <div class="w-[230px] flex items-center justify-center border-r border-b border-[#EEF4FF]">
        <img src="<?php echo esc_url( $logo ); ?>" alt="logo" class="pointer-events-none w-[185px]" />
    </div>
    <div class="relative py-6 px-12 flex justify-between items-center grow-[1]">
        <div class="flex items-center gap-8">
            <div class="flex items-center gap-3">
                <img src="<?php echo esc_url( $breadcrumb_icon ); ?>" alt="breadcrumb" class="w-4 h-4 pointer-events-none" />
                <h3>
                    <a class="text-sm leading-5 font-medium text-ec-placeholder hover:text-ec-primary"
                    href="?page=easycommerce-store">
                        <?php _e('Store', 'easycommerce' ); ?>
                    </a>
                </h3>
                <img src="<?php echo esc_url( $right_divider ); ?>" alt="divider" class="w-3 h-3 pointer-events-none" />
                <h3>
                    <a class="text-sm leading-5 font-medium text-ec-placeholder hover:text-ec-primary"
                    href="?page=easycommerce-store#/products">
                        <?php _e('Products', 'easycommerce' ); ?>
                    </a>
                </h3>
                <img src="<?php echo esc_url( $right_divider ); ?>" alt="divider" class="w-3 h-3 pointer-events-none" />
                <h3 class="text-sm leading-5 font-medium text-ec-primary">
                    <?php echo esc_html( $breadcrumb_slug ); ?>
                </h3>
            </div>
        </div>
        <div class="relative flex justify-end items-center gap-1">
            <div class="relative group">
                <button class="w-12 h-12 flex justify-center items-center border border-ec-border rounded-md">
                </button>
            </div>
        </div>
    </div>
</div>

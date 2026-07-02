<?php
use EasyCommerce\Helpers\Utility;

$easycommerce_menus = easycommerce_menus();
$current_page       = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

foreach ( $easycommerce_menus as $parent_menu ) {
    if ( $parent_menu['title'] === 'Store' ) {
        ?>
        <ul class="menu-title py-6 px-4">
            <?php foreach ( $parent_menu['submenus'] as $submenu ) :
                $title        = $submenu['menu_title'];
                $slug         = $submenu['slug'];
                $icon         = isset( $submenu['icon'] ) ? $submenu['icon'] : '';
                $url          = add_query_arg( 'page', $slug, admin_url( 'admin.php' ) );
                $has_submenus = isset( $submenu['submenus'] ) && is_array( $submenu['submenus'] );

                $is_active = ( $slug === $current_page );
                if ( $has_submenus ) {
                    foreach ( $submenu['submenus'] as $sub_submenu ) {
                        if ( $sub_submenu['slug'] === $current_page ) {
                            $is_active = true;
                            break;
                        }
                    }
                }
                ?>
                <li>
                    <a href="<?php echo esc_attr( $has_submenus ) ? 'javascript:void(0);' : esc_url( $url ); ?>"
                       class="hover:bg-[var(--color-ec-active)] hover:text-inherit hover:rounded-[8px] flex items-center text-sm p-3 my-3 gap-2 text-ec-title focus:ring-0 <?php echo esc_attr( $has_submenus ) ? 'submenu-toggle' : ''; ?> <?php echo $is_active ? 'easycommerce-active-menu' : ''; ?>">
                        <?php if ( $icon ) : ?>
                            <img src="<?php echo esc_url( $icon ); ?>" alt="" class="menu-icon" style="width: 24px; height: 24px; vertical-align: middle; margin-right: 5px;">
                        <?php endif; ?>
                        <?php echo esc_html( $title ); ?>
                        <?php if ( $has_submenus ) : ?>
                            <img src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/up.png' ); ?>" class="ml-auto arrow-icon transition-transform duration-300" style="width: 11px;" />
                        <?php endif; ?>
                    </a>

                    <?php if ( $has_submenus ) : ?>
                        <ul class="submenu-items border-l border-[#ECE6FF] ml-[30px] pl-3 <?php echo $is_active ? '' : 'hidden'; ?>">
                            <?php foreach ( $submenu['submenus'] as $sub_submenu ) :
                                $sub_title      = $sub_submenu['menu_title'];
                                $sub_slug       = $sub_submenu['slug'];
                                $sub_url        = add_query_arg( 'page', $sub_slug, admin_url( 'admin.php' ) );
                                $is_sub_active  = ( $sub_slug === $current_page );
                                ?>
                                <li>
                                    <a href="<?php echo esc_url( $sub_url ); ?>" class="hover:bg-[var(--color-ec-active)] hover:text-inherit hover:rounded-[8px] block text-sm p-3 my-3 text-ec-title focus:ring-0 <?php echo $is_sub_active ? 'easycommerce-active-menu' : ''; ?>">
                                        <?php echo esc_html( $sub_title ); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }
}
?>

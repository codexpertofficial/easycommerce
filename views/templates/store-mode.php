<?php
/**
 * Store Mode Template
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="easycommerce-coming-soon-wrapper">
    <h2 class="easycommerce-coming-soon-title">
        <?php esc_html_e( 'Store Launching Soon!', 'easycommerce' ); ?>
    </h2>
    <p class="easycommerce-coming-soon-desc">
        <?php esc_html_e( 'We’re getting ready to welcome you. Stay tuned for the launch.', 'easycommerce' ); ?>
    </p>
</div>
<?php
get_footer();
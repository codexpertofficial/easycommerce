<?php
/**
 * AI Connectivity tab (Settings > AI > Connectivity).
 *
 * Shown only while the store is NOT connected to the AI service (the tab is
 * registered in app/Config/settings.php based on connection state). Presents a
 * connect call-to-action: the submit button is intercepted by the settings SPA
 * (spa/admin/settings/src/App.jsx, active on menu=ai while disconnected) which
 * opens the "Two AI Agents, One Free Key" connect popup (APIScreen) - the same
 * popup the Store Copilot button uses.
 */

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>
<div class="easycommerce-ai-connectivity">
	<div style="max-width:520px;margin:40px auto;text-align:center;padding:32px;">
		<h2 style="font-size:22px;font-weight:600;margin:0 0 8px;color:#1f1f29;"><?php esc_html_e( 'Connect to unlock AI', 'easycommerce' ); ?></h2>
		<p style="color:#7f7f98;font-size:14px;line-height:22px;margin:0 0 24px;"><?php esc_html_e( 'Connect a free API key to power Store Copilot, Shopping Agent, AI Writer and more - 200 free AI credits your first month, then 100 every month.', 'easycommerce' ); ?></p>
		<button type="submit" class="button button-primary" style="background:#7351fd;border-color:#7351fd;color:#fff;height:auto;padding:6px 14px;font-size:13px;line-height:1.6;border-radius:6px;box-shadow:none;">
			<?php esc_html_e( 'Connect your store', 'easycommerce' ); ?>
		</button>
	</div>
</div>

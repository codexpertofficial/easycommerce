<?php
/**
 * AI Usage tab (Settings > AI > Usage).
 *
 * Store-owner-facing view of AI credit consumption. The headline figures (plan,
 * monthly allowance, used, remaining, next reset) come from the hub - the
 * authoritative monthly ledger - via easycommerce_ai_status(). The table below
 * is the local per-action record from ec_ai_logs (one row per AI request:
 * text, image, builder, smart search, Copilot/agent). Dollar cost lives on the
 * hub, not here.
 *
 * Rendered via include from views/settings/layout.php (template section).
 */

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

global $wpdb;

// No usage to show until the store is connected to the AI service. Show a connect
// CTA instead. The button is a submit button so the settings SPA (settings App.jsx,
// active on menu=ai while disconnected) intercepts the click and opens the
// "Two AI Agents, One Free Key" connect popup (APIScreen).
$ec_api       = get_option( 'easycommerce_api' );
$ec_connected = ! empty( $ec_api->email );

if ( ! $ec_connected ) {
	?>
	<div class="easycommerce-ai-usage">
		<div style="max-width:520px;margin:40px auto;text-align:center;padding:32px;">
			<h2 style="font-size:22px;font-weight:600;margin:0 0 8px;color:#1f1f29;"><?php esc_html_e( 'Connect to unlock AI', 'easycommerce' ); ?></h2>
			<p style="color:#7f7f98;font-size:14px;line-height:22px;margin:0 0 24px;"><?php esc_html_e( 'Connect a free API key to power Store Copilot, Shopping Agent, AI Writer and more - 200 free AI credits your first month, then 100 every month.', 'easycommerce' ); ?></p>
			<button type="submit" class="button button-primary" style="background:#7351fd;border-color:#7351fd;color:#fff;height:auto;padding:6px 14px;font-size:13px;line-height:1.6;border-radius:6px;box-shadow:none;">
				<?php esc_html_e( 'Connect your store', 'easycommerce' ); ?>
			</button>
		</div>
	</div>
	<?php
	return;
}

// hub path (stored as `type`) => friendly label.
$ec_ai_labels = array(
	'write'               => __( 'AI Writer', 'easycommerce' ),
	'generate_attributes' => __( 'Attribute Generator', 'easycommerce' ),
	'builder'             => __( 'Template Builder', 'easycommerce' ),
	'fixspell'            => __( 'Spelling Fix', 'easycommerce' ),
	'search'              => __( 'Smart Search', 'easycommerce' ),
	'paint'               => __( 'Image Generator', 'easycommerce' ),
	'enhance'             => __( 'Image Editor', 'easycommerce' ),
	'removebg'            => __( 'Background Removal', 'easycommerce' ),
	'copilot'             => __( 'Store Copilot', 'easycommerce' ),
	'shopping_agent'      => __( 'Shopping Agent', 'easycommerce' ),
	'agent'               => __( 'AI Agent', 'easycommerce' ),
);

$ec_ai_label_for = function ( $type ) use ( $ec_ai_labels ) {
	return $ec_ai_labels[ $type ] ?? ucwords( str_replace( '_', ' ', $type ) );
};

$ec_ai_table = $wpdb->prefix . 'ec_ai_logs';
$per_page    = 20;
$paged       = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$offset      = ( $paged - 1 ) * $per_page;

// Table may not exist until the first AI call creates it.
$ec_ai_ready = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $ec_ai_table ) ) === $ec_ai_table );

$total = 0;
$rows  = array();

if ( $ec_ai_ready ) {
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$ec_ai_table}`" );
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT type, input, output, credit, user_id, created_at FROM `{$ec_ai_table}` ORDER BY created_at DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		)
	);
}

// Authoritative monthly status from the hub (plan, allowance, usage, reset).
$ec_status      = function_exists( 'easycommerce_ai_status' ) ? easycommerce_ai_status() : array();
$ec_plan_slug   = $ec_status['plan'] ?? 'free';
$ec_plan_name   = $ec_status['plan_name'] ?? '';
$ec_limit       = (int) ( $ec_status['limit'] ?? 0 );
$ec_remaining   = (int) ( $ec_status['remaining'] ?? easycommerce_get_ai_credits() );
// `remaining` re-syncs on every AI call; `used` only on a /ai/status fetch (cached
// 10 min), so the stored `used` can lag. Derive it from the fresher `remaining`
// (limit - remaining) so the bar and the "Used this period" card never disagree.
$ec_used_period = $ec_limit > 0 ? max( 0, $ec_limit - $ec_remaining ) : (int) ( $ec_status['used'] ?? 0 );
$ec_next_raw    = $ec_status['next_refresh'] ?? '';

$ec_pct         = $ec_limit > 0 ? min( 100, (int) round( $ec_used_period / $ec_limit * 100 ) ) : 0;
$ec_plan_label  = $ec_plan_name ?: ucfirst( $ec_plan_slug );
$ec_next_label  = $ec_next_raw ? date_i18n( get_option( 'date_format' ), strtotime( $ec_next_raw ) ) : '—';
$ec_upgrade_url = 'https://easycommerce.dev/pricing/';
$ec_is_out      = ( $ec_limit > 0 && $ec_remaining <= 0 );

$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
?>

<div class="easycommerce-ai-usage">

	<style>
		/* Branded primary buttons (Upgrade / Buy more) */
		.easycommerce-ai-usage .button-primary,
		.easycommerce-ai-usage .button-primary:focus {
			background: #7351fd !important;
			border-color: #7351fd !important;
			color: #fff !important;
			border-radius: 6px !important;
			box-shadow: none !important;
			text-shadow: none !important;
			padding: 4px 14px !important;
			height: auto !important;
			font-size: 13px !important;
			line-height: 1.7 !important;
		}
		.easycommerce-ai-usage .button-primary:hover {
			background: #5d3fe0 !important;
			border-color: #5d3fe0 !important;
			color: #fff !important;
		}

		/* Usage table */
		.easycommerce-ai-usage .ec-ai-table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			border: 1px solid #ece9f6;
			border-radius: 10px;
			overflow: hidden;
			background: #fff;
		}
		.easycommerce-ai-usage .ec-ai-table thead th {
			background: #faf9ff;
			color: #6b6b80;
			font-size: 11px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: .04em;
			text-align: left;
			padding: 12px 16px;
			border-bottom: 1px solid #ece9f6;
		}
		.easycommerce-ai-usage .ec-ai-table tbody td {
			padding: 12px 16px;
			border-bottom: 1px solid #f2f0fa;
			color: #3c3c42;
			font-size: 13px;
			vertical-align: top;
		}
		.easycommerce-ai-usage .ec-ai-table tbody tr:last-child td {
			border-bottom: none;
		}
		.easycommerce-ai-usage .ec-ai-table tbody tr:hover td {
			background: #faf9ff;
		}
		.easycommerce-ai-usage .ec-ai-credit-badge {
			display: inline-block;
			min-width: 20px;
			padding: 2px 9px;
			border-radius: 9999px;
			background: #f0edff;
			color: #7351fd;
			font-weight: 600;
			font-size: 12px;
			text-align: center;
		}

		/* Pagination */
		.easycommerce-ai-usage .tablenav-pages .page-numbers {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-width: 30px;
			height: 30px;
			padding: 0 8px;
			margin: 0 3px 0 0;
			border: 1px solid #ece9f6;
			border-radius: 6px;
			background: #fff;
			color: #3c3c42;
			text-decoration: none;
			font-size: 13px;
			line-height: 1;
		}
		.easycommerce-ai-usage .tablenav-pages .page-numbers:hover {
			border-color: #7351fd;
			color: #7351fd;
		}
		.easycommerce-ai-usage .tablenav-pages .page-numbers.current {
			background: #7351fd;
			border-color: #7351fd;
			color: #fff;
		}
		.easycommerce-ai-usage .tablenav-pages .page-numbers.dots {
			border: none;
			background: transparent;
			min-width: 0;
		}
	</style>

	<?php if ( $ec_is_out ) : ?>
		<div class="notice notice-warning inline" style="margin:0 0 20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
			<p style="margin:.6em 0;"><?php esc_html_e( "You've used all your AI credits for this period. Upgrade your plan or buy more to keep using AI features.", 'easycommerce' ); ?></p>
			<a href="<?php echo esc_url( $ec_upgrade_url ); ?>" target="_blank" rel="noopener" class="button button-primary"><?php esc_html_e( 'Upgrade', 'easycommerce' ); ?></a>
		</div>
	<?php endif; ?>

	<div class="border border-ec-border rounded-lg p-4 mb-6">
		<div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px;">
			<div>
				<div class="text-ec-body text-sm mb-1"><?php esc_html_e( 'Current plan', 'easycommerce' ); ?></div>
				<div style="display:flex;align-items:center;gap:8px;">
					<span class="text-ec-title text-2xl font-semibold"><?php echo esc_html( $ec_plan_label ); ?></span>
					<?php if ( $ec_limit > 0 ) : ?>
						<span style="display:inline-block;font-size:12px;font-weight:600;padding:2px 8px;border-radius:9999px;background:#f0edff;color:#7351FD;white-space:nowrap;">
							<?php echo esc_html( sprintf( __( '%s credits/month', 'easycommerce' ), number_format_i18n( $ec_limit ) ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
			<a href="<?php echo esc_url( $ec_upgrade_url ); ?>" target="_blank" rel="noopener" class="button button-primary">
				<?php echo esc_html( 'free' === $ec_plan_slug ? __( 'Upgrade plan', 'easycommerce' ) : __( 'Buy more credits', 'easycommerce' ) ); ?>
			</a>
		</div>

		<?php if ( $ec_limit > 0 ) : ?>
			<div style="height:10px;width:100%;background:#eee;border-radius:9999px;overflow:hidden;margin-bottom:8px;">
				<div style="height:100%;width:<?php echo esc_attr( $ec_pct ); ?>%;background:#7351FD;border-radius:9999px;"></div>
			</div>
			<div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;" class="text-sm text-ec-body">
				<span><?php echo esc_html( sprintf( __( '%1$s of %2$s credits used', 'easycommerce' ), number_format_i18n( $ec_used_period ), number_format_i18n( $ec_limit ) ) ); ?></span>
				<span><?php echo esc_html( sprintf( __( 'Resets %s', 'easycommerce' ), $ec_next_label ) ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<div class="flex flex-wrap gap-4 mb-4">
		<div class="flex-1 min-w-[160px] border border-ec-border rounded-lg p-4">
			<div class="text-ec-body text-sm font-normal mb-1"><?php esc_html_e( 'Used this period', 'easycommerce' ); ?></div>
			<div class="text-ec-title text-2xl font-semibold"><?php echo esc_html( number_format_i18n( $ec_used_period ) ); ?></div>
		</div>
		<div class="flex-1 min-w-[160px] border border-ec-border rounded-lg p-4">
			<div class="text-ec-body text-sm font-normal mb-1"><?php esc_html_e( 'Credits remaining', 'easycommerce' ); ?></div>
			<div class="text-ec-title text-2xl font-semibold"><?php echo esc_html( number_format_i18n( $ec_remaining ) ); ?></div>
		</div>
		<div class="flex-1 min-w-[160px] border border-ec-border rounded-lg p-4">
			<div class="text-ec-body text-sm font-normal mb-1"><?php esc_html_e( 'Next refill', 'easycommerce' ); ?></div>
			<div class="text-ec-title text-2xl font-semibold"><?php echo esc_html( $ec_next_label ); ?></div>
		</div>
	</div>

	<p class="text-ec-body text-xs mb-6" style="margin-bottom:20px;"><?php esc_html_e( 'Plan credits reset monthly and do not roll over. Top-up credits (coming soon) never expire.', 'easycommerce' ); ?></p>

	<table class="ec-ai-table">
		<thead>
			<tr>
				<th style="width:160px;"><?php esc_html_e( 'Date', 'easycommerce' ); ?></th>
				<th style="width:140px;"><?php esc_html_e( 'Feature', 'easycommerce' ); ?></th>
				<th><?php esc_html_e( 'Prompt', 'easycommerce' ); ?></th>
				<th><?php esc_html_e( 'Response', 'easycommerce' ); ?></th>
				<th style="width:120px;"><?php esc_html_e( 'User', 'easycommerce' ); ?></th>
				<th style="width:70px;"><?php esc_html_e( 'Credits', 'easycommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No AI usage yet.', 'easycommerce' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $ec_r ) : ?>
					<?php
					$ec_user      = $ec_r->user_id ? get_userdata( (int) $ec_r->user_id ) : null;
					$ec_user_name = $ec_user ? $ec_user->display_name : '—';
					?>
					<tr>
						<td><?php echo esc_html( date_i18n( $date_format, strtotime( $ec_r->created_at ) ) ); ?></td>
						<td><?php echo esc_html( $ec_ai_label_for( $ec_r->type ) ); ?></td>
						<td title="<?php echo esc_attr( $ec_r->input ); ?>"><?php echo esc_html( wp_trim_words( (string) $ec_r->input, 12, '…' ) ); ?></td>
						<td title="<?php echo esc_attr( $ec_r->output ); ?>"><?php echo esc_html( wp_trim_words( (string) $ec_r->output, 16, '…' ) ); ?></td>
						<td><?php echo esc_html( $ec_user_name ); ?></td>
						<td><span class="ec-ai-credit-badge"><?php echo esc_html( (int) $ec_r->credit ); ?></span></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php
	$total_pages = (int) ceil( $total / $per_page );
	if ( $total_pages > 1 ) {
		$links = paginate_links(
			array(
				// Explicit base so links always stay on this tab, regardless of any
				// other query params present on the current request.
				'base'      => add_query_arg(
					array(
						'page'    => 'easycommerce-settings',
						'menu'    => 'ai',
						'submenu' => 'usage',
						'paged'   => '%#%',
					),
					admin_url( 'admin.php' )
				),
				'format'    => '',
				'current'   => $paged,
				'total'     => $total_pages,
				'prev_text' => '‹',
				'next_text' => '›',
			)
		);
		if ( $links ) {
			echo '<div class="tablenav"><div class="tablenav-pages" style="margin:12px 0;">' . wp_kses_post( $links ) . '</div></div>';
		}
	}
	?>
</div>

<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Notice;
use EasyCommerce\Models\Attribute_Value;
use EasyCommerce\Models\Log as Log_Model;
use EasyCommerce\API\Reports\Reports;

class Init {

	use Hook;
	use Asset;
	use Cache;
	use Cleaner;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_init', array( $this, 'migrate_old_block_names' ) );
		$this->action( 'wp_footer', array( $this, 'modal' ) );
		$this->action( 'admin_footer', array( $this, 'modal' ) );
		$this->action( 'template_redirect', array( $this, 'auto_login' ) );
		$this->filter( 'get_edit_post_link', array( $this, 'edit_product_link' ), 10, 3 );
		$this->action( 'wp_before_admin_bar_render', array( $this, 'add_admin_bar_menu' ) );
		$this->action( 'pre_get_posts', array( $this, 'restrict_media_access' ) );
		$this->filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
		$this->action( 'easycommerce_log', array( $this, 'add_log' ) );
		$this->action( 'init', array( $this, 'add_notices' ) );
		$this->filter( 'theme_page_templates', array( $this, 'register_full_width_template' ) );
		$this->filter( 'template_include', array( $this, 'load_full_width_template' ) );
		$this->action( 'admin_bar_menu', array( $this, 'add_store_mode_admin_bar' ), 31 );
		$this->action( 'admin_bar_menu', array( $this, 'add_ai_credits_admin_bar' ), 999 );
		$this->action( 'wp_head', array( $this, 'output_admin_bar_css' ) );
		$this->action( 'admin_head', array( $this, 'output_admin_bar_css' ) );

		$this->action( 'easycommerce_create_product', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_update_product', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_delete_product', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_after_create_order', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_after_delete_order', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_after_bulk_delete_order', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce-set_order_status', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_order_status_updated', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_order_fulfillment_status_updated', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce-set_order_fulfillment_status', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_after_refund_order', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_user_created', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_user_updated', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_after_user_delete', array( $this, 'invalidate_cache' ) );
		$this->action( 'easycommerce_review_added', array( $this, 'invalidate_cache' ) );
	}

	public function invalidate_cache() {
		Reports::delete_all_cache();
	}
	/**
	 * Custom capability mapping for EasyCommerce features
	 * This allows editors to access certain store features
	 */
	public function map_meta_cap( $caps, $cap, $user_id ) {
		// Allow users with 'edit_others_posts' capability (like editors) to access these features
		if ( 'easycommerce_view_store' === $cap ) {
			if ( user_can( $user_id, 'edit_others_posts' ) ) {
				$caps = array( 'exist' );
			}
		}

		return $caps;
	}

	public function migrate_old_block_names() {
		if ( (bool) get_option( 'easycommerce_block_migrated' ) === true ) {
			return;
		}

		global $wpdb;

		$old_name = 'easycommerce/shop';
		$new_name = 'easycommerce/template-2';

		$result = $wpdb->query(
			"UPDATE {$wpdb->posts}
			 SET post_content = REPLACE(
				 post_content,
				 '<!-- wp:{$old_name}',
				 '<!-- wp:{$new_name}'
			 )
			 WHERE post_content LIKE '%<!-- wp:{$old_name}%'"
		);

		update_option( 'easycommerce_block_migrated', $result );
	}

	public function modal() {
		echo '
		<div id="easycommerce-modal" style="display: none">
			<img id="easycommerce-modal-loader" src="' . esc_attr( EASYCOMMERCE_ASSETS_URL . 'common/img/loader.gif' ) . '" />
		</div>';
	}

	public function auto_login() {

		if ( isset( $_GET['user_id'] ) && isset( $_GET['token'] ) ) {
			$user_id = intval( $this->sanitize( $_GET['user_id'] ) );
			$token   = $this->sanitize( $_GET['token'] );

			// Generate the cache key based on the user ID
			$key = 'temp_login_' . $user_id;

			// Check if the cached token matches the token in the URL
			if ( ( $cached_token = $this->get_cache( $key ) ) && $cached_token === $token && get_user_by( 'id', $user_id ) ) {

				wp_set_auth_cookie( $user_id );
				wp_set_current_user( $user_id );

				$this->delete_cache( $key );

				wp_safe_redirect( remove_query_arg( array( 'user_id', 'token' ) ) );
			}
		}
	}

	public function edit_product_link( $link, $post_id, $context ) {

		if ( get_post_type( $post_id ) === 'product' ) {
			$link = add_query_arg( array( 'page' => "easycommerce#/products/edit/{$post_id}" ), admin_url( 'admin.php' ) );
		}

		return $link;
	}

	public function add_admin_bar_menu() {

		if( ! current_user_can( 'manage_options' ) ) return;

		global $wp_admin_bar;

		$activated = apply_filters( 'easycommerce-pro_activated', false );
		$licensed  = apply_filters( 'easycommerce-pro_licensed', false );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'easycommerce',
				'title' => __( 'EasyCommerce', 'easycommerce' ),
				'href'  => '#',
				'meta'  => array( 'class' => '!bg-ec-primary hover:!bg-ec-secondary' ),
			)
		);

		$menus = array();

		if ( is_admin() ) {

			if ( ! empty( $shop_page = easycommerce_shop_page() ) ) {
				$menus['shop'] = array(
					'url'   => get_permalink( $shop_page ),
					'title' => esc_html__( 'Shop', 'easycommerce' ),
					'meta'  => array( 'target' => '_blank' ),
				);
			}

			if ( ! empty( $checkout_page = easycommerce_checkout_page() ) ) {
				$menus['checkout'] = array(
					'url'   => get_permalink( $checkout_page ),
					'title' => esc_html__( 'Checkout', 'easycommerce' ),
					'meta'  => array( 'target' => '_blank' ),
				);
			}

			if ( ! empty( $dashboard_page = easycommerce_dashboard_page() ) ) {
				$menus['dashboard'] = array(
					'url'   => get_permalink( $dashboard_page ),
					'title' => esc_html__( 'Dashboard', 'easycommerce' ),
					'meta'  => array( 'target' => '_blank' ),
				);
			}
		} else {
			$menus['store'] = array(
				'url'      => '#',
				'title'    => esc_html__( 'Store', 'easycommerce' ),
				'meta'     => array(),
				'children' => array(
					'easycommerce-dashboard' => array( // don't change it to 'dashboard' because WordPress default dashboard is called 'dashboard'
						'url'   => admin_url( 'admin.php?page=easycommerce' ),
						'title' => esc_html__( 'Dashboard', 'easycommerce' ),
					),
					'products'               => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/products' ),
						'title' => esc_html__( 'Products', 'easycommerce' ),
					),
					'orders'                 => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/orders' ),
						'title' => esc_html__( 'Orders', 'easycommerce' ),
					),
					'refunds'                 => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/refunds' ),
						'title' => esc_html__( 'Refunds', 'easycommerce' ),
					),
					'abandoned-cart'                 => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/abandoned-cart' ),
						'title' => esc_html__( 'Abandoned Cart', 'easycommerce' ),
					),
					'transactions'           => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/transactions' ),
						'title' => esc_html__( 'Transactions', 'easycommerce' ),
					),
					'customers'              => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/customers' ),
						'title' => esc_html__( 'Customers', 'easycommerce' ),
					),
					'coupons'                => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/coupons' ),
						'title' => esc_html__( 'Coupons', 'easycommerce' ),
					),
					'reports'                => array(
						'url'   => admin_url( 'admin.php?page=easycommerce#/reports' ),
						'title' => esc_html__( 'Reports', 'easycommerce' ),
					),
					'settings'               => array(
						'url'   => admin_url( 'admin.php?page=easycommerce-settings' ),
						'title' => esc_html__( 'Settings', 'easycommerce' ),
					),
				),
			);
		}

		$menus['setup'] = array(
			'url'   => admin_url( 'admin.php?page=easycommerce-wizard' ),
			'title' => esc_html__( 'Setup Wizard', 'easycommerce' ),
			'meta'  => array(),
		);

		$menus['addons'] = array(
			'url'   => admin_url( 'admin.php?page=easycommerce#/addons' ),
			'title' => esc_html__( 'Addons', 'easycommerce' ),
			'meta'  => array(),
		);

		$menus['help'] = array(
			'url'   => admin_url( 'admin.php?page=easycommerce#/help' ),
			'title' => esc_html__( 'Help & Support', 'easycommerce' ),
			'meta'  => array(),
		);

		$menus['pro'] = array(
			'url'   => admin_url( 'admin.php?page=easycommerce#/pro' ),
			'title' => ( $licensed && $activated )
					? '<strong style="color:#FFC400">' . esc_html__( 'EasyCommerce Pro', 'easycommerce' ) . '</strong>'
					: ( ! $licensed && $activated
						? '<strong style="color:#FFC400">' . esc_html__( 'Activate License', 'easycommerce' ) . '</strong>'
						: '<span style="background-color:#ffc400; color:black; padding:0 10px; border-radius:3px; display:block; text-align:center; font-weight:bold; line-height:2; margin-top:4px;">' . esc_html__( 'Get Pro', 'easycommerce' ) . '</span>'
					),
			'meta'  => array(),
		);

		$menus = apply_filters( 'easycommerce-admin_bar-menus', $menus );

		foreach ( $menus as $id => $menu ) {
			$wp_admin_bar->add_node(
				array(
					'id'     => $id,
					'parent' => 'easycommerce',
					'title'  => $menu['title'],
					'href'   => $menu['url'],
					'meta'   => $menu['meta'],
				)
			);

			if ( ! empty( $menu['children'] ) ) {
				foreach ( $menu['children'] as $child_id => $child_menu ) {
					$wp_admin_bar->add_node(
						array(
							'id'     => $child_id,
							'parent' => $id,
							'title'  => $child_menu['title'],
							'href'   => $child_menu['url'],
							'meta'   => $child_menu['meta'] ?? array(),
						)
					);
				}
			}
		}

		// add product builder link
		if( ! is_admin() && is_singular( 'product' ) ) {
			$wp_admin_bar->add_node(
				array(
					'id'     => 'easycommerce-builder',
					'parent' => 'edit',
					'title'  => __( 'Product Builder', 'easycommerce' ),
					'href'	 => add_query_arg( [ 'action' => 'edit', 'builder' => 1, 'post' => get_the_ID() ], admin_url( 'post.php' ) )
				)
			);
		}
	}

	public function restrict_media_access( $query ) {
		if ( ! current_user_can( 'edit_pages' ) && $query->get( 'post_type' ) === 'attachment' ) {
			$query->set( 'author', get_current_user_id() );
		}
	}

	/**
	 * Add a log entry to the database.
	 *
	 * This method is hooked to the 'easycommerce_log' action and handles logging
	 * various activities within the e-commerce system such as order creation,
	 * product updates, coupon usage, etc.
	 *
	 * @param array $data Log data array with the following keys:
	 *   - object (string, required): The object type being logged ('order', 'product', 'coupon', etc.)
	 *   - action (string, required): The action performed ('created', 'updated', 'deleted', etc.)
	 *   - object_id (int, required): The ID of the object being logged
	 *   - user_id (int, optional): User ID who performed the action. Defaults to current user
	 *   - note (string, optional): Additional notes or description of the action
	 *   - ip_address (string, optional): IP address of the client. Auto-detected if not provided
	 *   - type (string, optional): Log level/type ('info', 'warning', 'error'). Defaults to 'info'
	 *   - meta (string|array, optional): Additional meta. Can be JSON string or array (will be JSON encoded)
	 *
	 * @return bool|int Log ID on success, false on failure or validation error.
	 *
	 * @example
	 * do_action( 'easycommerce_log', array(
	 *     'object'    => 'order',
	 *     'action'    => 'created',
	 *     'object_id' => 123,
	 *     'note'      => 'Order placed via checkout',
	 *     'type'      => 'info',
	 *     'meta'  	   => array('amount' => 99.99, 'currency' => 'USD')
	 * ) );
	 */
	public function add_log( $data ) {
		if ( empty( $data['object'] ) || empty( $data['action'] ) ) {
			return false;
		}

		// Get IP address - use provided one or detect automatically
		if ( ! empty( $data['ip_address'] ) ) {
			$ip_address = $data['ip_address'];
		} else {
			// Detect client IP address
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
			}

			// Handle comma-separated IPs (from proxies)
			if ( strpos( $ip, ',' ) !== false ) {
				$ip = trim( explode( ',', $ip )[0] );
			}

			$ip_address = filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '127.0.0.1';
		}

		// Handle meta - JSON encode arrays, keep strings as-is
		$meta = null;
		if ( ! empty( $data['meta'] ) ) {
			if ( is_array( $data['meta'] ) ) {
				$meta = wp_json_encode( $data['meta'] );
			} else {
				$meta = $data['meta'];
			}
		}

		$log_data = array(
			'object'     => $this->sanitize( $data['object'] ),
			'action'     => $this->sanitize( $data['action'] ),
			'object_id'  => (int) ( $data['object_id'] ?? 0 ),
			'user_id'    => ! empty( $data['user_id'] ) ? (int) $data['user_id'] : get_current_user_id(),
			'note'       => ! empty( $data['note'] ) ? $this->sanitize( $data['note'], 'textarea' ) : null,
			'ip_address' => $ip_address,
			'seen'       => 0,
			'type'       => ! empty( $data['type'] ) ? $this->sanitize( $data['type'] ) : 'info',
			'meta'   => $meta,
		);

		$log_model = new Log_Model();
		return $log_model->add( $log_data );
	}

	public function add_notices() {

		if( ! current_user_can( 'manage_options' ) ) return;

		$notice = new Notice();

		// permalink notice
		if( empty( get_option( 'permalink_structure' ) ) ) {
			$notice->add( [
				'id'			=> 'permalink',
				'title'			=> __( 'Permalink Not Set', 'easycommerce' ),
				'message'		=> __( 'Your permalink structure is set to \'Plain\'. Please choose any other option and click \'Save Changes\' to make your product and checkout pages work properly.', 'easycommerce' ),
				'type'			=> 'error',
				'button'		=> __( 'Set Permalink', 'easycommerce' ),
				'url'			=> admin_url( 'options-permalink.php' ),
				'dismissible'	=> false,
			] );
		}

		// missing pages notice
		if( ! easycommerce_shop_page( true ) || ! easycommerce_checkout_page( true ) || ! easycommerce_dashboard_page( true ) ) {
			$notice->add( [
				'id'			=> 'page-missing',
				'title'			=> __( 'Missing Required Pages', 'easycommerce' ),
				'message'		=> __( 'EasyCommerce needs a Shop page, Checkout page, and Customer Dashboard. One or more is missing or not configured.', 'easycommerce' ),
				'type'			=> 'error',
				'button'		=> __( 'Configure Settings', 'easycommerce' ),
				'url'			=> admin_url( 'admin.php?page=easycommerce-settings&menu=general&submenu=store' ),
				'dismissible'	=> false,
			] );
		}

		// theme compatibilty notice
		// if( ! in_array( get_stylesheet(), easycommerce_compatible_themes() ) ) {
		// 	$notice->add( [
		// 		'id'			=> 'theme-compatibiity',
		// 		'title'			=> __( 'Theme Compatibility Issue', 'easycommerce' ),
		// 		'message'		=> __( 'Your theme isn\'t optimized for EasyCommerce. You might experience layout issues. Switch to a compatible theme for full functionality and a professional store.', 'easycommerce' ),
		// 		'type'			=> 'warning',
		// 		'button'		=> __( 'Explore Themes', 'easycommerce' ),
		// 		'url'			=> admin_url( 'theme-install.php?browse=easycommerce-recommended' ),
		// 		'dismissible'	=> true,
		// 	] );
		// }

		// payment method notice
		if( empty( easycommerce_active_payment_methods() ) ) {
			$notice->add( [
				'id'			=> 'payment-method',
				'title'			=> __( 'Payment Not Configured', 'easycommerce' ),
				'message'		=> __( 'Your store doesn\'t have any payment options configured. Make sure to configure a payment method or start with "Cash on Delivery" for testing.', 'easycommerce' ),
				'type'			=> 'error',
				'button'		=> __( 'Configure Now', 'easycommerce' ),
				'url'			=> admin_url( 'admin.php?page=easycommerce-settings&menu=payment&submenu=methods' ),
				'dismissible'	=> false,
			] );
		}
	}

	/**
	 * Register EasyCommerce Full Width page template
	 *
	 * @param array $templates Existing page templates.
	 * @return array Modified page templates.
	 */
	public function register_full_width_template( $templates ) {
		$templates['full-width-layout.php'] = __( 'EasyCommerce Full Width Layout', 'easycommerce' );

		return $templates;
	}

	/**
	 * Load EasyCommerce Full Width page template from plugin
	 *
	 * @param string $template Current template path.
	 * @return string Modified template path.
	 */
	public function load_full_width_template( $template ) {
		$page_template = get_page_template_slug();

		if ( $page_template === 'full-width-layout.php' ) {
			$plugin_template = EASYCOMMERCE_PLUGIN_DIR . 'views/templates/full-width-layout.php';

			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Add Store Mode badge to admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public function add_store_mode_admin_bar( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$mode    = Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test';
		$is_live = $mode === 'live';
		$dot_color = $is_live ? '#00a32a' : '#cc1818';
		$label     = $is_live ? esc_html__( 'Live', 'easycommerce' ) : esc_html__( 'Test', 'easycommerce' );

		$title = sprintf(
			'<span style="font-weight:600;color:#000;text-transform:uppercase;font-size:11px;margin-top:2px;">
				<span style="width:7px;height:7px;min-width:7px;min-height:7px;border-radius:50%%;background:%s;display:inline-block;flex-shrink:0;margin-right:4px;"></span>%s</span>',
			$dot_color,
			$label
		);

		$wp_admin_bar->add_node( array(
			'id'    => 'easycommerce-store-mode-badge',
			'title' => $title,
			'href'  => admin_url( 'admin.php?page=easycommerce-settings&tab=general&submenu=visibility' ),
			'parent' => 'root-default',
			'meta'  => array(
				'class' => 'easycommerce-site-status-badge-' . ( $is_live ? 'live' : 'test' ),
			),
		) );
	}

	/**
	 * Show an "AI Credits: used/limit" badge at the top-right of the admin bar,
	 * left of the Howdy menu, linking to the AI usage screen.
	 *
	 * Reads the locally cached credit state only (no hub call on page load); the
	 * numbers refresh on AI usage and when the usage screen is opened.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 */
	public function add_ai_credits_admin_bar( $wp_admin_bar ) {
		// Administrators only; shown wherever the admin bar appears (front + admin).
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Only when the store is connected to the AI service.
		$api = get_option( 'easycommerce_api' );
		if ( empty( $api->email ) ) {
			return;
		}

		$data      = function_exists( 'easycommerce_ai_data' ) ? easycommerce_ai_data() : array();
		$limit     = (int) ( $data['limit'] ?? 0 );
		$remaining = (int) ( $data['remaining'] ?? 0 );

		if ( $limit > 0 ) {
			$used  = max( 0, $limit - $remaining );
			/* translators: 1: credits used, 2: monthly credit allowance */
			$label = sprintf( __( 'AI Credits: %1$s/%2$s', 'easycommerce' ), number_format_i18n( $used ), number_format_i18n( $limit ) );
		} else {
			/* translators: %s: remaining AI credits */
			$label = sprintf( __( 'AI Credits: %s', 'easycommerce' ), number_format_i18n( $remaining ) );
		}

		$wp_admin_bar->add_node( array(
			'id'     => 'easycommerce-ai-credits',
			'title'  => esc_html( $label ),
			'href'   => admin_url( 'admin.php?page=easycommerce-settings&menu=ai&submenu=usage' ),
			'parent' => 'top-secondary',
			// Front-end background relies on Tailwind (loaded on EC storefront
			// pages); wp-admin uses the rule in admin/css/common.css.
			'meta'   => array( 'class' => '!bg-ec-primary' ),
		) );
	}

	public function output_admin_bar_css() {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		?>
		<style>
			#wpadminbar #wp-admin-bar-easycommerce-store-mode-badge {
				height: 32px !important;
				display: flex !important;
				align-items: center !important;
			}

			#wpadminbar .quicklinks #wp-admin-bar-easycommerce-store-mode-badge a.ab-item {
				background: #fff !important;
				padding: 2px 7px !important;
				border-radius: 20px !important;
				display: inline-flex !important;
				align-items: center !important;
				height: 15px !important;
				line-height: 1 !important;
				box-sizing: border-box !important;
			}

			#wpadminbar .quicklinks #wp-admin-bar-easycommerce-store-mode-badge a.ab-item:hover,
			#wpadminbar .quicklinks #wp-admin-bar-easycommerce-store-mode-badge a.ab-item:focus {
				background: #f0f0f0 !important;
				color: #000 !important;
			}
		</style>
		<?php
	}
}

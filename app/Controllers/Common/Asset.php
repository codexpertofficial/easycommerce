<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Tax;
use EasyCommerce\Traits\Asset as Asset_Trait;
use EasyCommerce\Models\Product;

class Asset {

	use Hook;
	use Asset_Trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
		$this->action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		$this->action( 'wp_enqueue_scripts', array( $this, 'add_assets' ) );
		$this->action( 'admin_enqueue_scripts', array( $this, 'add_assets' ) );
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_assets() {

		$dependencies = array( 'wp-blocks', 'wp-element', 'wp-hooks', 'jquery' );

		if ( is_admin() ) {
			$dependencies[] = 'wp-editor';
		}

		$this->enqueue_script(
			'easycommerce_blocks',
			EASYCOMMERCE_BUILD_URL . 'blocks.bundle.js',
			$dependencies
		);
	}

	/**
	 * Enqueue block editor only assets.
	 */
	public function enqueue_block_editor_assets() {
		$this->enqueue_script(
			'easycommerce_editor',
			EASYCOMMERCE_BUILD_URL . 'editor.bundle.js',
			array( 'wp-element', 'wp-data', 'wp-i18n', 'wp-api-fetch', 'wp-hooks', 'react', 'react-dom' )
		);
	}

	/**
	 * Whether the current public request needs the shared EasyCommerce frontend
	 * bundle (Tailwind + plugin common CSS/JS).
	 *
	 * Loading the bundle site-wide injects Tailwind's preflight reset onto every
	 * page, including the theme's own (non-EasyCommerce) pages. This limits it to
	 * pages that actually render EasyCommerce content:
	 *   - the product CPT (single + archive),
	 *   - any singular page/post containing an EasyCommerce block or shortcode,
	 *   - any page when the AI shopping agent chatbot is enabled (shown site-wide).
	 *
	 * Block themes can place EasyCommerce blocks inside templates/template parts
	 * (not the post content), which this cannot detect - the
	 * `easycommerce_load_storefront_assets` filter is the escape hatch for that.
	 *
	 * @param \WP_Post|null $post The queried post, if any.
	 * @return bool
	 */
	private function is_storefront_context( $post ) {
		if ( is_singular( 'product' ) || is_post_type_archive( 'product' ) ) {
			return true;
		}

		// AI shopping agent chatbot is rendered site-wide when enabled.
		$api = get_option( 'easycommerce_api' );
		if ( '1' === Utility::get_option( 'ai', 'agentic-ai', 'enable', '0' )
			&& ( ! empty( $api->email ) || apply_filters( 'easycommerce-pro_licensed', false ) ) ) {
			return true;
		}

		// EasyCommerce block or shortcode in the current singular content.
		if ( is_singular() && $post instanceof \WP_Post ) {
			if ( false !== strpos( $post->post_content, 'wp:easycommerce/' )
				|| false !== strpos( $post->post_content, '[easycommerce-' ) ) {
				return true;
			}
		}

		/**
		 * Force-load the shared storefront bundle on the current request.
		 *
		 * Use this when EasyCommerce content is rendered somewhere the automatic
		 * detection cannot see it - e.g. a block placed in a block-theme template
		 * part rather than in post content.
		 *
		 * @param bool $load Default false.
		 */
		return (bool) apply_filters( 'easycommerce_load_storefront_assets', false );
	}

	public function add_assets() {

		global $post;
		global $current_screen;
		global $easycommerce_menus;

		$customer           = new Customer( get_current_user_id() );
		$user               = get_userdata( get_current_user_id() );
		$load_common_assets = false;
		$checkout_page      = Utility::get_option( 'general', 'store', 'checkout', '' );
		$payment_page       = Utility::get_option( 'general', 'store', 'payment', '' );
		$cart_obj   		= new Cart( easycommerce_get_cart_hash() );
		$billing_address 	= $customer->get_billing_address();
		$shipping_address 	= $customer->get_shipping_address();
		
		if ( empty( $billing_address ) ) {
			$billing_address = $cart_obj->get_billing_address();
		}

		if ( empty( $shipping_address ) ) {
			$shipping_address = $cart_obj->get_shipping_address();
		}

		/**
		 * Localize PHP variables to be used in the JS files
		 *
		 * @since 0.1
		 */
		$localized = array(
			'rest_base'       => easycommerce_rest_base(),
			'permalink'       => get_option( 'permalink_structure' ) != '',
			'home_url'        => easycommerce_home_url(),
			'product_base'    => easycommerce_home_url( 'products' ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'logout_url'      => wp_logout_url(),
			'admin_url'       => admin_url( 'admin.php' ),
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
			'assets'          => EASYCOMMERCE_ASSETS_URL,
			'credits'         => easycommerce_get_ai_credits(),
			'customer'        => array(
				'address' => array(
					'billing'  => $billing_address,
					'shipping' => $shipping_address,
				),
			),
			'active_payment_methods'=> easycommerce_active_payment_methods(),
			'countries'       		=> easycommerce_countries(),
			'shipping'        		=> array(
				'countries' => array_intersect_key(
					easycommerce_countries(),
					array_flip( (array) Utility::get_option( 'shipping', 'settings', 'countries', array() ) )
				),
			),
			'tax'             => array(
				'countries' => array_intersect_key(
					easycommerce_countries(),
					array_flip( (array) Utility::get_option( 'tax', 'settings', 'countries', array() ) )
				),
			),
			'pro'	=> [
				'activated' => apply_filters( 'easycommerce-pro_activated', false ),
				'licensed'	=> apply_filters( 'easycommerce-pro_licensed', false ),
				'addons'	=> apply_filters( 'easycommerce-pro_addons', [] )
			],
			'sampleCsvUrl'	   => EASYCOMMERCE_PLUGIN_URL . 'samples/dummy-data/products.csv',
			'order_statuses'    => easycommerce_order_statuses(),
    		'fulfill_statuses'  => easycommerce_fulfill_statuses(),
		    'payment_page_url'  => $payment_page ? easycommerce_payment_page( true ) : '',
		);

		if( $user ) {
			$user_info 			= [
				'id'    => $user->ID,
				'name'  => $user->display_name,
				'email' => $user->user_email,
			];

			$localized['user'] = $user_info;
		}

		/**
		 * Assests for entire `wp-admin` pages
		 */
		if ( is_admin() ) {
			$this->enqueue_style(
				'easycommerce_admin_common',
				EASYCOMMERCE_ASSETS_URL . 'admin/css/common.css'
			);

			$this->enqueue_script(
				'easycommerce_notice',
				EASYCOMMERCE_ASSETS_URL . 'admin/js/notice.js',
				array( 'jquery' )
			);
			$detected_migration_plugin = easycommerce_detect_external_plugins_for_migration() ?? '';
			$notice_localized = array(
				'ajax_url'					=> admin_url( 'admin-ajax.php' ),
				'nonce'						=> wp_create_nonce( 'easycommerce_dismiss_pro_notice_nonce' ),
				'migration_addon_active' 	=> is_plugin_active( 'easycommerce-migration/easycommerce-migration.php' ),
				'migration_api_root'		=> esc_url_raw( rest_url() ),
				'migration_addon_page'		=> admin_url() . "admin.php?page=easycommerce-settings&menu=migration&source=" . strtolower( $detected_migration_plugin ),
				'rest_nonce' 				=> wp_create_nonce( 'wp_rest' ),
			);

			$this->localize_script(
				'easycommerce_notice',
				'EASYCOMMERCE_NOTICE',
				apply_filters( 'easycommerce-notice-localized_vars', $notice_localized )
			);
		}

		/**
		 * Assets that are required in the specific screens under `wp-admin` only
		 *
		 * @since 0.1
		 */
		if ( is_admin() && ( strpos( $screen = str_replace( array( 'toplevel_page_', 'easycommerce_page_' ), array(), $current_screen->base ), 'easycommerce' ) !== false || 'post' === $current_screen->base ) ) {

			$load_common_assets = true;

			// add menu items to the $localized list
			$localized['product_edit_base'] = add_query_arg(
				array(
					'action' => 'edit',
					'builder'=> 1,
					'post'   => '',
				),
				admin_url( 'post.php' )
			);
			$localized['admin']['menus']    = easycommerce_menus();

			$this->enqueue_script(
				'easycommerce_admin',
				EASYCOMMERCE_ASSETS_URL . 'admin/js/init.js',
				array( 'easycommerce' )
			);

			$this->enqueue_style(
				'easycommerce_admin',
				EASYCOMMERCE_ASSETS_URL . 'admin/css/init.css',
			);

			if ( $screen == 'easycommerce' ) {

				$localized['date_ranges']      = easycommerce_date_ranges();
				$localized['email_events']     = easycommerce_email_events();
				$localized['refund_reasons']   = easycommerce_refund_reasons();
				$localized['sort_options']     = easycommerce_product_sort_options();
				$localized['order_statuses']   = easycommerce_order_statuses();
				$localized['product_statuses'] = easycommerce_product_statuses();
				$localized['fulfill_statuses'] = easycommerce_fulfill_statuses();
				$localized['countries']        = easycommerce_countries();
				$localized['units']            = array(
					'length' => easycommerce_length_units(),
					'weight' => easycommerce_weight_units(),
					'time'   => easycommerce_time_units(),
				);

				$localized['payment_methods'] = array_map(
					function ( $method ) {
						return array(
							'title'          => $method['title'] ?? '',
							'icon'           => $method['icon'] ?? '',
							'support_refund' => $method['support_refund'] ?? false,
						);
					},
					easycommerce_payment_methods()
				);

				// Load media uplaoder
				wp_enqueue_media();

				// Load editor
				wp_enqueue_editor();
				wp_enqueue_script( 'wp-tinymce' );

				$this->enqueue_script(
					'easycommerce_main-menu',
					EASYCOMMERCE_BUILD_URL . 'easycommerce.bundle.js',
					array( 'wp-element', 'easycommerce', 'wp-hooks', 'wp-i18n', 'wp-components', 'wp-plugins', 'wp-api-fetch' )
				);

				wp_set_script_translations(
					'easycommerce_main-menu',
					'easycommerce',
				);
				
			}

			if ( $screen == 'easycommerce-settings' ) {
				
				$this->enqueue_script(
					'easycommerce_settings-react',
					EASYCOMMERCE_BUILD_URL . 'settings.bundle.js',
					array( 'wp-element', 'easycommerce' ),
					EASYCOMMERCE_VERSION,
					true 
				);
    
				$this->enqueue_script(
					'easycommerce_shipping-methods',
					EASYCOMMERCE_BUILD_URL . 'shipping.bundle.js',
					array( 'wp-element', 'easycommerce' )
				);

				$this->enqueue_script(
					'easycommerce_tax-classes',
					EASYCOMMERCE_BUILD_URL . 'tax.bundle.js',
					array( 'wp-element', 'easycommerce' )
				);

				$this->enqueue_script(
					'easycommerce_settings',
					EASYCOMMERCE_ASSETS_URL . 'admin/js/settings.js',
					array( 'jquery', 'easycommerce' )
				);

				$this->enqueue_style(
					'easycommerce_settings',
					EASYCOMMERCE_ASSETS_URL . 'admin/css/settings.css',
				);

				// Enqueue Select2 from WordPress.
				wp_enqueue_script( 'select2' );
				wp_enqueue_style( 'select2' );

				// Enqueue custom styles for the dropdown.
				wp_enqueue_style(
					'easycommerce-tinymce-email-placeholders',
					EASYCOMMERCE_ASSETS_URL . 'admin/css/email-placeholders.css',
					[],
					EASYCOMMERCE_VERSION
				);

				do_action( 'easycommerce_after_settings_assets' );

				$localized['units']            = array(
					'length' => easycommerce_length_units(),
					'weight' => easycommerce_weight_units(),
					'time'   => easycommerce_time_units(),
				);
			}

			if ( $screen == 'easycommerce-wizard' ) {

				// Load media uplaoder
				wp_enqueue_media();

				$this->enqueue_script(
					'easycommerce_wizard-menu',
					EASYCOMMERCE_BUILD_URL . 'wizard.bundle.js',
					array( 'wp-element', 'easycommerce' )
				);

				$localized['pages']                   = Utility::get_posts( array( 'post_type' => 'page' ) );
				$localized['currencies']              = easycommerce_currencies();
				$localized['currency_format_options'] = easycommerce_currency_format_options();
				$localized['business_type']           = easycommerce_get_business_types();
				$localized['api_connected']           = easycommerce_is_api_connected();
				$localized['all_payment_methods']	  = easycommerce_get_all_payment_methods();
			}

			if ( $screen == 'easycommerce-settings' ) {

				$this->enqueue_style(
					'select2',
					EASYCOMMERCE_ASSETS_URL . 'common/lib/select2/select2.min.css',
					array(),
					'4.0.13'
				);

				$this->enqueue_script(
					'select2',
					EASYCOMMERCE_ASSETS_URL . 'common/lib/select2/select2.min.js',
					array( 'jquery' ),
					'4.0.13'
				);
			}

			$this->enqueue_style(
				'easycommerce-tailwind',
				EASYCOMMERCE_ASSETS_URL . 'common/css/tailwind.css'
			);

		} elseif ( is_admin() && ( $screen = get_current_screen() ) && $screen->base == 'plugins' ) {

			$this->enqueue_style(
				'easycommerce_survey',
				EASYCOMMERCE_ASSETS_URL . 'admin/css/survey.css'
			);

			$this->enqueue_script(
				'easycommerce_survey',
				EASYCOMMERCE_ASSETS_URL . 'admin/js/survey.js',
				// [ 'easycommerce', 'jquery' ]
				array( 'jquery' )
			);
		} elseif ( is_admin() && ( $screen = get_current_screen() ) && $screen->base == 'theme-install' ) {
			// Enqueue this script to show all compatible themes in add themes screen.
			$this->enqueue_script(
				'compatible-themes',
				EASYCOMMERCE_ASSETS_URL . 'admin/js/compatible-themes.js',
				array( 'jquery' ),
			);

			$themes_data 				= easycommerce_compatible_themes();
			$compatible_themes_data 	= array(
				'label'			=> __( 'EasyCommerce Compatible Themes', 'easycommerce' ),
				'slugs'       	=> $themes_data,
			);

			$this->localize_script(
				'compatible-themes',
				'compatible_themes',
				$compatible_themes_data
			);
		}

		/**
		 * Assets that are required in the public-facing screens only
		 *
		 * @since 0.1
		 */
		elseif ( ! is_admin() ) {

			// Only pull in the shared bundle (and its site-wide Tailwind preflight)
			// where EasyCommerce content is actually rendered - keeps the theme's
			// own pages untouched. See is_storefront_context().
			$load_common_assets = $this->is_storefront_context( $post );

			$localized['direct_checkout'] = Utility::get_option( 'checkout', 'settings', 'direct_checkout' );

			// single product localized
			if ( is_singular( 'product' ) ) {
				$product   = new Product( get_the_ID() );
				$show_text = $product->get_meta( 'review_text_mandatory' );

				$localized['product_id']            = get_the_ID();
				$localized['review_text_mandatory'] = $show_text;
				$localized['review_msg_text']       = array(
					'both'   => __( 'Rating and review is required', 'easycommerce' ),
					'rating' => __( 'Rating is required', 'easycommerce' ),
					'review' => __( 'Review is required', 'easycommerce' ),
				);
			}

			$this->enqueue_style(
				'style',
				EASYCOMMERCE_ASSETS_URL . 'public/css/style.css'
			);

			if ( $load_common_assets ) {
				$this->enqueue_script(
					'easycommerce_public',
					EASYCOMMERCE_ASSETS_URL . 'public/js/init.js',
					array( 'easycommerce' )
				);
			}

			if ( $checkout_page && is_page( $checkout_page ) ) {
				$this->enqueue_style(
					'easycommerce_checkout',
					EASYCOMMERCE_ASSETS_URL . 'public/css/checkout.css'
				);

				$this->enqueue_style(
					'easycommerce_trendy_checkout',
					EASYCOMMERCE_ASSETS_URL . 'public/css/checkout-template-3.css'
				);

				$this->enqueue_script(
					'easycommerce_checkout',
					EASYCOMMERCE_ASSETS_URL . 'public/js/checkout.js'
				);

				$localized['cart'] = ( new Cart() )->get( true );

				do_action( 'easycommerce_checkout_enqueue_scripts' );
			}

			if ( $payment_page && is_page( $payment_page ) ) {
				$this->enqueue_style(
					'easycommerce_checkout',
					EASYCOMMERCE_ASSETS_URL . 'public/css/checkout.css'
				);

				$this->enqueue_script(
					'easycommerce_checkout',
					EASYCOMMERCE_ASSETS_URL . 'public/js/checkout.js'
				);

				$this->enqueue_script(
					'easycommerce_payment',
					EASYCOMMERCE_ASSETS_URL . 'public/js/payment.js',
					array( 'jquery', 'easycommerce', 'easycommerce_checkout' )
				);

				do_action( 'easycommerce_checkout_enqueue_scripts' );
			}

			$api = get_option( 'easycommerce_api' );
			if ( '1' === Utility::get_option( 'ai', 'agentic-ai', 'enable', '0' ) && ( ! empty( $api->email ) || apply_filters( 'easycommerce-pro_licensed', false ) ) ) {
				$this->enqueue_style(
					'easycommerce_agent_chatbot',
					EASYCOMMERCE_ASSETS_URL . 'public/css/agent-chatbot.css'
				);

				$this->enqueue_script(
					'easycommerce_agent_chatbot',
					EASYCOMMERCE_ASSETS_URL . 'public/js/agent-chatbot.js',
					array( 'easycommerce' )
				);

				$agent_avatar_id  = Utility::get_option( 'ai', 'agentic-ai', 'agent_avatar', '' );
				$agent_avatar_url = $agent_avatar_id ? wp_get_attachment_url( $agent_avatar_id ) : '';

				$this->localize_script( 'easycommerce_agent_chatbot', 'EC_AGENT', array(
					'name'     => Utility::get_option( 'ai', 'agentic-ai', 'agent_name', __( 'AI Shopping Assistant', 'easycommerce' ) ),
					'avatar'   => $agent_avatar_url ?: '',
					'position' => Utility::get_option( 'ai', 'agentic-ai', 'bubble_position', 'right' ),
					'color'    => Utility::get_option( 'ai', 'agentic-ai', 'primary_color', '#7351FD' ),
				) );
			}

			if ( is_page() && has_shortcode( $post->post_content, 'easycommerce-dashboard' ) ) {
				$this->enqueue_script(
					'easycommerce_dashboard',
					EASYCOMMERCE_BUILD_URL . 'dashboard.bundle.js',
					array( 'wp-element', 'easycommerce', 'wp-hooks', 'wp-dom-ready', 'wp-components', 'wp-plugins', )
				);

				wp_enqueue_media();

				$this->enqueue_script(
					'easycommerce_customer_dashboard_extra',
					EASYCOMMERCE_ASSETS_URL . 'public/js/customer-dashboard.js',
					array( 'jquery', 'easycommerce' )
				);

				$localized['payment_methods'] = array_map(
					function ( $method ) {
						return array(
							'title'          => $method['title'] ?? '',
							'icon'           => $method['icon'] ?? '',
						);
					},
					easycommerce_payment_methods()
				);

				do_action( 'easycommerce_dashboard_enqueue_scripts' );
			}

			if ( ( is_singular( 'product' ) && ! has_block( '', $post ) ) || has_block( 'easycommerce/single-product-gallery', $post ) ) {

				$this->enqueue_style(
					'swiper',
					EASYCOMMERCE_ASSETS_URL . 'common/lib/swiper/swiper-bundle.min.css',
					array(),
					'11'
				);

				$this->enqueue_script(
					'swiper',
					EASYCOMMERCE_ASSETS_URL . 'common/lib/swiper/swiper-bundle.min.js',
					array(),
					'11',
					array( 'in_footer' => true )
				);

				$this->enqueue_script(
					'easycommerce_swiper',
					EASYCOMMERCE_ASSETS_URL . 'public/js/swiper.js',
					array( 'swiper' )
				);

				$this->enqueue_script(
					'easycommerce_product_single',
					EASYCOMMERCE_ASSETS_URL . 'public/js/product-single.js',
					array( 'jquery' )
				);
			}

			if ( $load_common_assets ) {
				$this->enqueue_script(
					'swatches',
					EASYCOMMERCE_ASSETS_URL . 'public/js/swatches.js',
					array( 'jquery', 'easycommerce' )
				);

				$this->enqueue_script(
					'shop',
					EASYCOMMERCE_ASSETS_URL . 'public/js/shop.js',
					array( 'jquery', 'easycommerce' )
				);
			}

			/**
			 * Theme compat
			 *
			 * Generic, theme-agnostic baseline first - keeps the storefront
			 * acceptable even on themes with no file of their own. The per-theme
			 * file under assets/public/css/themes/{theme}.css (if present) loads
			 * after the baseline and layers theme-specific fine-tuning on top.
			 * See GitHub issue #2862.
			 */
			$this->enqueue_style(
				'easycommerce-theme-compat',
				EASYCOMMERCE_ASSETS_URL . 'public/css/theme-compat.css'
			);

			if ( file_exists( EASYCOMMERCE_PLUGIN_DIR . 'assets/public/css/themes/' . ( $theme = get_template() ) . '.css' ) ) {
				$this->enqueue_style(
					"easycommerce-{$theme}",
					EASYCOMMERCE_PLUGIN_URL . "assets/public/css/themes/{$theme}.css"
				);
			}
		}

		/**
		 * Assets that are required in both the `wp-admin` and public-facing screens
		 *
		 * @since 0.1
		 */
		if ( $load_common_assets ) {

			$this->enqueue_script(
				'easycommerce',
				EASYCOMMERCE_ASSETS_URL . 'common/js/init.js',
				array( 'jquery' )
			);

			$this->enqueue_style(
				'easycommerce',
				EASYCOMMERCE_ASSETS_URL . 'common/css/init.css'
			);

			$this->enqueue_style(
				'google-fonts',
				'//fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap'
			);

			$this->enqueue_style(
				'block',
				EASYCOMMERCE_ASSETS_URL . 'common/css/block.css'
			);

			$this->enqueue_script(
				'tailwind',
				EASYCOMMERCE_BUILD_URL . 'tailwind.bundle.js',
				array(),
				null,
				array( 'in_footer' => false )
			);

			$branding                     = get_option( 'easycommerce-general-branding' );
			$localized['currency_symbol'] = easycommerce_currency_symbol();
			$localized['currency_code']   = easycommerce_currency();

			$this->localize_script(
				'easycommerce',
				'EASYCOMMERCE',
				apply_filters( 'easycommerce-localized_vars', $localized )
			);
		}

		if ( is_admin() && 'plugins' === $current_screen->base ) {
			$survey_localized = array(
				'rest_base' => easycommerce_rest_base(),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'home'      => get_bloginfo( 'url' ),
				'user'      => $user_info ?? null,
			);
			$this->localize_script(
				'easycommerce_survey',
				'EASYCOMMERCE_SURVEY',
				apply_filters( 'easycommerce-survey-localized_vars', $survey_localized )
			);
		}
	}
}

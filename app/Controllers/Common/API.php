<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use WP_REST_Server;
use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Rest;
use EasyCommerce\Traits\Auth;
use EasyCommerce\API\Agent\Assistant;
use EasyCommerce\API\Agent\Copilot;
use EasyCommerce\API\AI;
use EasyCommerce\API\Cart;
use EasyCommerce\API\Option;
use EasyCommerce\API\Attribute;
use EasyCommerce\API\Product;
use EasyCommerce\API\Customer;
use EasyCommerce\API\Profile;
use EasyCommerce\API\Taxonomy;
use EasyCommerce\API\Order;
use EasyCommerce\API\Refund;
use EasyCommerce\API\Transaction;
use EasyCommerce\API\Dashboard;
use EasyCommerce\API\Connectivity;
use EasyCommerce\API\Addon;
use EasyCommerce\API\Shipping_Plan;
use EasyCommerce\API\Abandoned_Cart;
use EasyCommerce\API\Tax;
use EasyCommerce\API\Coupon;
use EasyCommerce\API\Geo;
use EasyCommerce\API\Email_Placeholder;
use EasyCommerce\API\Product_Review;
use EasyCommerce\API\Importer;
use EasyCommerce\API\Log;
use EasyCommerce\API\Notice;
use EasyCommerce\API\Reports\Reports;
use EasyCommerce\API\Reports\Overview;
use EasyCommerce\API\Reports\Orders;
use EasyCommerce\API\Reports\Products;
use EasyCommerce\API\Reports\LegacyReports;
use EasyCommerce\API\Reports\Revenue;
use EasyCommerce\API\Reports\Customers;

class API {

	use Hook;
	use Auth;
	use Rest;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Register all API endpoints
	 */
	public function register_endpoints() {
		$this->register_connectivity_endpoints();
		$this->register_addon_endpoints();
		$this->register_dashboard_endpoints();
		$this->register_reports_endpoints();
		$this->register_option_endpoints();
		$this->register_attribute_endpoints();
		$this->register_product_endpoints();
		$this->register_product_review_endpoints();
		$this->register_customer_endpoints();
		$this->register_profile_endpoints();
		$this->register_taxonomy_endpoints();
		$this->register_cart_endpoints();
		$this->register_order_endpoints();
		$this->register_refund_endpoints();
		$this->register_transaction_endpoints();
		$this->register_shipping_plan_endpoints();
		$this->register_location_endpoints();
		$this->register_coupon_endpoints();
		$this->register_tax_endpoints();
		$this->register_abandoned_cart_endpoints();
		$this->register_ai_endpoints();
		$this->register_agent_assistant_endpoints();
		$this->register_agent_copilot_endpoints();
		$this->register_email_placeholder_endpoints();
		$this->register_importer_endpoints();
		$this->register_log_endpoints();
		$this->register_notice_endpoints();
	}

	/**
	 * Register Auth-related API endpoints
	 */
	private function register_connectivity_endpoints() {
		$connectivity = new Connectivity();

		// Set Setup wizard data
		$this->register_route(
			'/connectivity/setup/save',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'save' ),
				'args'		 => array(
					'data' => array(
						'description' => __( 'Your setup wizard data', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'object',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Apply a ready-made store design (setup wizard Store Setup step).
		$this->register_route(
			'/connectivity/apply-design',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $connectivity, 'apply_design' ),
				'args'                => array(
					'design_id' => array(
						'description' => __( 'The store design id to apply (or "skip").', 'easycommerce' ),
						'required'    => true,
						'type'        => 'string',
					),
				),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);

		// Set the static front page (confirm homepage overwrite from the wizard).
		$this->register_route(
			'/connectivity/set-front-page',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $connectivity, 'set_front_page' ),
				'args'                => array(
					'page_id' => array(
						'description' => __( 'The page id to set as the static front page.', 'easycommerce' ),
						'required'    => true,
						'type'        => 'integer',
					),
				),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);

		// User Registration
		$this->register_route(
			'/connectivity/registration',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'registration' ),
				'args'		 => array(
					'username'		  => array(
						'description' => __( 'Your Username', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'email'			  => array(
						'description'		=> __( 'Your Email', 'easycommerce' ),
						'required'			=> true,
						'validate_callback' => 'is_email',
						'type'        => 'string',
					),
					'password'		  => array(
						'description' => __( 'Your Password', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'confirmPassword' => array(
						'description' => __( 'Your Confirm Password', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_nonce_verified' ),
			)
		);

		// Reset Password
		$this->register_route(
			'/connectivity/reset-password',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'reset_password_request' ),
				'args'		 => array(
					'user_login' => array(
						'description' => __( 'Username or Email', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_nonce_verified' ),
			)
		);

		// Reset Password Confirmation
		$this->register_route(
			'/connectivity/reset-password-confirm',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'reset_password_confirm' ),
				'args'		 => array(
					'key'	   => array(
						'description' => __( 'Reset Key', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'login'	   => array(
						'description' => __( 'User Login', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'password' => array(
						'description' => __( 'New Password', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'confirm_password' => array(
						'description' => __( 'Confirm Password', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_nonce_verified' ),
			)
		);

		// Get setup wizard data
		$this->register_route(
			'/connectivity/setup/get',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $connectivity, 'get_setup_wizard' ),
				'args'		 => array(),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Get API
		$this->register_route(
			'/connectivity/check',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $connectivity, 'check' ),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Get API
		$this->register_route(
			'/connectivity/disconnect',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $connectivity, 'disconnect' ),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Generate token
		$this->register_route(
			'/connectivity/token',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'generate_token' ),
				'args'		 => array(
					'email' => array(
						'description'		=> __( 'Your email', 'easycommerce' ),
						'required'			=> true,
						'validate_callback' => 'is_email',
						'type'        		=> 'string',
					),
					'name'	=> array(
						'description' => __( 'Your name', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Verify token
		$this->register_route(
			'/connectivity/token/verify',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'verify_token' ),
				'args'		 => array(
					'email' => array(
						'description'		=> __( 'Your email', 'easycommerce' ),
						'required'			=> true,
						'validate_callback' => 'is_email',
						'type'        		=> 'string',
					),
					'token' => array(
						'description' => __( 'Your token', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Get docs
		$this->register_route(
			'/connectivity/docs',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $connectivity, 'get_docs' ),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Get a doc
		$this->register_route(
			'/connectivity/docs/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $connectivity, 'get_doc' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The doc id', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Submit feedback
		$this->register_route(
			'/connectivity/feedback',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'feedback' ),
				'args'		 => array(
					'event'	  => array(
						'description' => __( 'Telemetry event name', 'easycommerce' ),
						'required'	  => false,
						'type'        => 'string',
					),
					'name'	  => array(
						'description' => __( 'Your name', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'email'	  => array(
						'description'		=> __( 'Your email', 'easycommerce' ),
						'required'			=> true,
						'validate_callback' => 'is_email',
						'type'        		=> 'string',
					),
					'home'	  => array(
						'description' => __( 'Site URL', 'easycommerce' ),
						'required'	  => false,
						'type'        => 'string',
					),
					'subject' => array(
						'description' => __( 'Subject', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'message' => array(
						'description' => __( 'Message', 'easycommerce' ),
						'required'	  => false,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		//intregation request

		$this->register_route(
			'/connectivity/requests',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $connectivity, 'requests' ),
				'args'		 => array(
					'name'	  => array(
						'description' => __( 'Your name', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'email'	  => array(
						'description'		=> __( 'Your email', 'easycommerce' ),
						'required'			=> true,
						'validate_callback' => 'is_email',
						'type'        		=> 'string',
					),
					'home'	  => array(
						'description' => __( 'Site URL', 'easycommerce' ),
						'required'	  => false,
						'type'        => 'string',
					),
					'subject' => array(
						'description' => __( 'Subject', 'easycommerce' ),
						'required'	  => true,
						'type'        => 'string',
					),
					'message' => array(
						'description' => __( 'Message', 'easycommerce' ),
						'required'	  => false,
						'type'        => 'string',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);
	}

	/**
	 * Register Addon-related API endpoints
	 */
	private function register_addon_endpoints() {
		$addon = new Addon();

		// Get addons
		$this->register_route(
			'/addons',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $addon, 'list' ),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Install an addon
		$this->register_route(
			'/addons',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $addon, 'manage' ),
				'args'		 => array(
					'addon'	 => array(
						'description' => __( 'The addon slug', 'easycommerce' ),
						'required'	  => true,
					),
					'action' => array(
						'description' => __( 'The action- activate or deactivate', 'easycommerce' ),
						'required'	  => false,
						'default'	  => 'activate',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Activate license for an addon
		$this->register_route(
			'/addons/license',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $addon, 'license' ),
				'args'		 => array(
					'key'  => array(
						'description' => __( 'The license key', 'easycommerce' ),
						'required'	  => false,
					),
					'addon'	 => array(
						'description' => __( 'The addon slug', 'easycommerce' ),
						'required'	  => true,
					),
					'action' => array(
						'description' => __( 'The action- activate or deactivate', 'easycommerce' ),
						'required'	  => false,
						'default'	  => 'activate',
					),
					'email' => array(
						'description' => __( 'User email for license', 'easycommerce' ),
						'required'	  => false,
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);
	}

	/**
	 * Register Dashboard-related API endpoints
	 */
	private function register_dashboard_endpoints() {
		$dashboard = new Dashboard();

		// Dashboard stats.
		$this->register_route(
			'/dashboard/stats',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_stats' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'An array of \'from\' and \'to\', or a string', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							if (
								( is_array( $param ) && isset( $param['from'] ) && isset( $param['to'] ) )
								|| ( in_array( $param, array_keys( easycommerce_date_ranges() ) ) )
							) {
								return true;
							}

							return false;
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Dashboard setup status
		$this->register_route(
			'/dashboard/setup_status',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_setup_status' ),
				'args'		 => array(),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Dashboard order statuses
		$this->register_route(
			'/dashboard/order_statuses',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_order_statuses' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'An array of \'from\' and \'to\', or a string', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							if (
								( is_array( $param ) && isset( $param['from'] ) && isset( $param['to'] ) )
								|| ( in_array( $param, array_keys( easycommerce_date_ranges() ) ) )
							) {
								return true;
							}

							return false;
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Dashboard sales
		$this->register_route(
			'/dashboard/sales',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_sales' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'An array of \'from\' and \'to\', or a string', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							if (
								( is_array( $param ) && isset( $param['from'] ) && isset( $param['to'] ) )
								|| ( in_array( $param, array_keys( easycommerce_date_ranges() ) ) )
							) {
								return true;
							}

							return false;
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Single abandoned cart details (items, customer, last activity)
		$this->register_route(
			'/dashboard/abandoned-cart',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_abandoned_cart_details' ),
				'args'		 => array(
					'hash' => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Send custom reminder email for abandoned cart
		$this->register_route(
			'/dashboard/abandoned-cart/send-reminder',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $dashboard, 'send_abandoned_cart_reminder' ),
				'args'		 => array(
					'hash' => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => true,
					),
					'subject' => array(
						'description' => __( 'Email subject line', 'easycommerce' ),
						'required'	  => true,
					),
					'message' => array(
						'description' => __( 'Email message body', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Dashboard low stock
		$this->register_route(
			'/dashboard/lowstock',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_low_stock' ),
				'args'		 => array(),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Dashboard recent orders
		$this->register_route(
			'/dashboard/recent_orders',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_recent_orders' ),
				'args'		 => array(
					'range' => array(
						'type'    => 'string',
						'default' => 'last-30',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Dashboard top sellers
		$this->register_route(
			'/dashboard/topsellers',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_top_sellers' ),
				'args'		 => array(
					'range' => array(
						'type'    => 'string',
						'default' => 'last-30',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Dashboard recent activities
		$this->register_route(
			'/dashboard/activities',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $dashboard, 'get_activities' ),
				'args'		 => array(
					'type' => array(
						'description' => __( 'Activity type: All, Orders, Refunds, Reviews, Others', 'easycommerce' ),
						'required'	 => false,
						'type'		 => 'string',
						'default'	 => 'All',
					),
					'from' => array(
						'description' => __( 'From date (Y-m-d)', 'easycommerce' ),
						'required'	 => false,
						'type'		 => 'string',
					),
					'to' => array(
						'description' => __( 'To date (Y-m-d)', 'easycommerce' ),
						'required'	 => false,
						'type'		 => 'string',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Reports-related API endpoints
	 */
	private function register_reports_endpoints() {
		$reports    = new LegacyReports();
		$overview   = new Overview();
		$orders     = new Orders();
		$products   = new Products();
		$revenue    = new Revenue();
		$customers  = new Customers();

		$this->register_route(
			'/dashboard/reports',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $reports, 'reports' ),
				'args'		 => array(
					'range'		 => array(
						'description'		=> __( 'An array of \'from\' and \'to\', or a string', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							if (
								( is_array( $param ) && isset( $param['from'] ) && isset( $param['to'] ) )
								|| ( in_array( $param, array_keys( easycommerce_date_ranges() ) ) )
							) {
								return true;
							}

							return false;
						},
					),
					'product_id' => array(
						'description' => __( 'The product ID in case of product-specific reports', 'easycommerce' ),
						'required'    => false,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/stats',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'stats' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
					'comparison' => array(
						'description'		=> __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/sales-refund-revenue',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'sales_refund_revenue' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/order-vs-refund',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'order_vs_refund' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/order-status',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'order_status' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/order-type',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'order_type' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
					'comparison' => array(
						'description'		=> __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/top-selling',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'top_selling' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/catalog-stats',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'catalog_stats' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "all-time")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/customer-type',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'customer_type' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
					'comparison' => array(
						'description'		=> __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/overview/customer-overview',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $overview, 'customer_overview' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
					'comparison' => array(
						'description'		=> __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/orders/stats',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $orders, 'stats' ),
				'args'		 => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
					'comparison' => array(
						'description'		=> __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'			=> false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/orders/over-time',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $orders, 'over_time' ),
				'args'       => array(
					'range' => array(
						'description'		=> __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
					'comparison' => array(
						'description'		=> __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/orders/customers',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $orders, 'order_frequency' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/orders/statuses',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $orders, 'status_breakdown' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/orders/fulfillment',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $orders, 'fulfillment_breakdown' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/orders/heatmap',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $orders, 'heatmap' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "2026-01-01,2026-03-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/orders/locations',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $orders, 'order_by_location' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "2026-01-01,2026-03-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/stats',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'stats' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "all-time")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
					'comparison' => array(
						'description'       => __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/sold-over-time',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'products_sold_over_time' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "last-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
					'comparison' => array(
						'description'       => __( 'Comparison range (e.g., "previous-day", "previous-week", "previous-month", "previous-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/most-sold',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'most_sold' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "last-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/least-sold',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'least_sold' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "last-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/single-stats',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'single_product_stats' ),
				'args'       => array(
					'product_id' => array(
						'description'       => __( 'The product ID', 'easycommerce' ),
						'required'          => true,
						'validate_callback' => fn( $param ) => is_numeric( $param ),
					),
					'range' => array(
						'description'       => __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "all-time")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/single-info',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'single_product_info' ),
				'args'       => array(
					'product_id' => array(
						'description'       => __( 'The product ID', 'easycommerce' ),
						'required'          => true,
						'validate_callback' => fn( $param ) => is_numeric( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/single-sales-over-time',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'single_product_sales_over_time' ),
				'args'       => array(
					'product_id' => array(
						'description'       => __( 'The product ID', 'easycommerce' ),
						'required'          => true,
						'validate_callback' => fn( $param ) => is_numeric( $param ),
					),
					'range' => array(
						'description'       => __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "last-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/locations',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'product_sale_by_location' ),
				'args'       => array(
					'product_id' => array(
						'description'       => __( 'The product ID', 'easycommerce' ),
						'required'          => true,
						'validate_callback' => fn( $param ) => is_numeric( $param ),
					),
					'range' => array(
						'description'       => __( 'Date range key (e.g., "today", "this-week", "this-month", "this-year", "last-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/single-reviews',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'single_product_reviews' ),
				'args'       => array(
					'product_id' => array(
						'description'       => __( 'The product ID', 'easycommerce' ),
						'required'          => true,
						'validate_callback' => fn( $param ) => is_numeric( $param ),
					),
					'limit' => array(
						'description'       => __( 'Number of reviews to return (default: 6)', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_numeric( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/products/product-name',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $products, 'get_product_name' ),
				'args'       => array(
					'product_id' => array(
						'description'       => __( 'The product ID', 'easycommerce' ),
						'required'          => true,
						'validate_callback' => fn( $param ) => is_numeric( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/revenue/stats',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $revenue, 'stats' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "this-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => function ( $param ) {
							return is_string( $param )
								&& (
									in_array( $param, array_keys( easycommerce_date_ranges() ) )
									|| strpos( $param, ',' ) !== false
								);
						},
					),
					'comparison' => array(
						'description'       => __( 'Comparison range key', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/revenue/over-time',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $revenue, 'over_time' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "this-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => function ( $param ) {
							return is_string( $param )
								&& (
									in_array( $param, array_keys( easycommerce_date_ranges() ) )
									|| strpos( $param, ',' ) !== false
								);
						},
					),
					'comparison' => array(
						'description'       => __( 'Comparison range key', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/revenue/top-customers',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $revenue, 'top_customers' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "this-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => function ( $param ) {
							return is_string( $param )
								&& (
									in_array( $param, array_keys( easycommerce_date_ranges() ) )
									|| strpos( $param, ',' ) !== false
								);
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/revenue/locations',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $revenue, 'revenue_by_location' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "2026-01-01,2026-03-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => function ( $param ) {
							return is_string( $param )
								&& (
									in_array( $param, array_keys( easycommerce_date_ranges() ) )
									|| strpos( $param, ',' ) !== false
								);
						},
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/customers/stats',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $customers, 'stats' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "this-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
					'comparison' => array(
						'description'       => __( 'Comparison range key', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/customers/over-time',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $customers, 'over_time' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "this-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
					'comparison' => array(
						'description'       => __( 'Comparison range key', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/customers/heatmap',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $customers, 'heatmap' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "2026-01-01,2026-03-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/customers/locations',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $customers, 'customer_by_location' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "2026-01-01,2026-03-30")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/reports/customers/top-customers',
			array(
				'methods'    => WP_REST_Server::READABLE,
				'callback'   => array( $customers, 'top_customers' ),
				'args'       => array(
					'range' => array(
						'description'       => __( 'Date range key (e.g., "last-30", "this-month", "this-year")', 'easycommerce' ),
						'required'          => false,
						'validate_callback' => fn( $param ) => is_string( $param ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Option-related API endpoints
	 */
	private function register_option_endpoints() {
		$option = new Option();

		// Get an option
		$this->register_route(
			'/option',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $option, 'get' ),
				'args'		 => array(
					'key' => array(
						'description' => __( 'The option `key` name', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Update an option
		$this->register_route(
			'/option',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $option, 'update' ),
				'args'		 => array(
					'key'	=> array(
						'description' => __( 'The option `key` name', 'easycommerce' ),
						'required'	  => true,
					),
					'value' => array(
						'description' => __( 'The option `value`', 'easycommerce' ),
						'required'	  => false, // at times value can be empty
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete an option
		$this->register_route(
			'/option',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $option, 'delete' ),
				'args'		 => array(
					'key' => array(
						'description' => __( 'The option `key` name', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Attribute-related API endpoints
	 */
	private function register_attribute_endpoints() {
		$attribute = new Attribute();

		// Add new attributes with values
		$this->register_route(
			'/attributes',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $attribute, 'add_item' ),
				'args'		 => array(
					'name' => array(
						'description' => __( 'Attribute name', 'easycommerce' ),
						'type'		  => 'string',
						'required'	  => true,
					),
					'slug' => array(
						'description' => __( 'Slug for the attribute', 'easycommerce' ),
						'type'		  => 'string',
						'required'	  => false,
					),
					'type' => array(
						'description' => __( 'Type of the attribute (e.g. select, text)', 'easycommerce' ),
						'type'		  => 'string',
						'required'	  => true,
					),
					'options' => array(
						'description' => __( 'Array of options with label and value', 'easycommerce' ),
						'type'		  => 'array',
						'required'	  => true,
						'items'		  => array(
							'type'		 => 'object',
							'required'	 => true,
							'properties' => array(
								'name' => array(
									'type'		  => 'string',
									'description' => __( 'Display label for the option', 'easycommerce' ),
									'required'	  => true,
								),
								'slug' => array(
									'type'		  => 'string',
									'description' => __( 'Display label for the option', 'easycommerce' ),
									'required'	  => false,
								),
								'value' => array(
									'description' => __( 'Stored value for the option', 'easycommerce' ),
									'required'	  => false,
									'validate_callback' => function( $param, $request, $key ) {
										return is_string( $param ) || is_int( $param );
									}
								),
							),
						),
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		$this->register_route(
			'/attributes',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $attribute, 'list' ),
				'args'		 => array(
					'page'		  => array(
						'description' => __( 'The page number', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'	  => array(
						'description' => __( 'The number of products per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		$this->register_route(
			'/attributes/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $attribute, 'get_attribute' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The attribute ID', 'easycommerce' ),
						'type'		  => 'integer',
						'required'	  => true
					)
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		$this->register_route(
			'/attributes/(?P<id>\d+)',
			array(
				'methods'	=> WP_REST_Server::EDITABLE,
				'callback'	=> array( $attribute, 'update_attribute' ),
				'permission' => array( $this, 'is_admin' ),
				'args'		=> array(
					'id' => array(
						'description' => __( 'The attribute ID', 'easycommerce' ),
						'type'		  => 'integer',
						'required'	  => true
					),
					'name' => array(
						'description' => __( 'Attribute name', 'easycommerce' ),
						'type'		  => 'string',
						'required'	  => true,
					),
					'slug' => array(
						'description' => __( 'Slug for the attribute', 'easycommerce' ),
						'type'		  => 'string',
						'required'	  => true,
					),
					'type' => array(
						'description' => __( 'Type of the attribute (e.g. select, text)', 'easycommerce' ),
						'type'		  => 'string',
						'required'	  => true,
					),
					'options' => array(
						'description' => __( 'Array of options with id, name, slug, value', 'easycommerce' ),
						'type'		  => 'array',
						'required'	  => true,
						'items'		  => array(
							'type'		 => 'object',
							'required'	 => true,
							'properties' => array(
								'id' => array(
									'type'		  => 'integer',
									'description' => __( 'Option ID', 'easycommerce' ),
									'required'	  => false,
								),
								'name' => array(
									'type'		  => 'string',
									'description' => __( 'Display label for the option', 'easycommerce' ),
									'required'	  => true,
								),
								'slug' => array(
									'type'		  => 'string',
									'description' => __( 'Display label for the option', 'easycommerce' ),
									'required'	  => true,
								),
								'value' => array(
									'description'		=> __( 'Stored value for the option', 'easycommerce' ),
									'required'			=> true,
									'validate_callback' => function( $param, $request, $key ) {
										return is_string( $param ) || is_int( $param );
									},
								),
							),
						),
					),
				)
			)
		);

		$this->register_route(
			'/attributes/(?P<id>\d+)',
			array(
				'methods'	=> WP_REST_Server::DELETABLE,
				'callback'	=> array( $attribute, 'delete_attribute' ),
				'permission' => array( $this, 'is_admin' ),
				'args'		=> array(
					'id' => array(
						'description' => __( 'The attribute ID', 'easycommerce' ),
						'type'		  => 'integer',
						'required'	  => true
					)
				)
			)
		);

		$this->register_route(
			'/attributes/bulk-delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $attribute, 'bulk_delete_attributes' ),
				'permission' => array( $this, 'is_admin' ),
				'args'		 => array(
					'ids' => array(
						'description' => __( 'The attribute IDs', 'easycommerce' ),
						'type'		  => 'array',
						'required'	  => true,
					)
				)
			)
		);
	}

	/**
	 * Register Product-related API endpoints
	 */
	private function register_product_endpoints() {
		$product = new Product();

		// List products
		$this->register_route(
			'/products',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $product, 'list' ),
				'args'		 => array(
					'page'		  => array(
						'description' => __( 'The page number', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'	  => array(
						'description' => __( 'The number of products per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 9,
					),
					's'			  => array(
						'description' => __( 'The search query', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'sku'		  => array(
						'description' => __( 'The SKU of a variation to search product with', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'	  => array(
						'description' => __( 'The product status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'publish',
					),
					'brands'	  => array(
						'description' => __( 'The product brands', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
					),
					'categories'  => array(
						'description' => __( 'The product categories', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
					),
					'sort_by'	  => array(
						'description' => __( 'The product Sort By', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'attributes'  => array(
						'description' => __( 'Array of attributes with values', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'name'	 => array(
									'description' => __( 'Attribute name', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'values' => array(
									'description' => __( 'Array of attribute values', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
							),
						),
					),
					'min_price'	  => array(
						'description' => __( 'Minimum price', 'easycommerce' ),
						'required'	  => false,
						'type'		  => ['integer', 'null'],
						'default'	  => null,
					),
					'max_price'	  => array(
						'description' => __( 'Maximum price', 'easycommerce' ),
						'required'	  => false,
						'type'		   => ['integer', 'null'],
						'default'	  => null,
					),
					'show_prices' => array(
						'description' => __( 'Either it should include prices', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => false,
					),
					'return_html' => array(
						'description' => __( 'Either it should return HTML', 'easycommerce' ),
						'required'	  => false,
						'type'		  => ['integer', 'null'],
						'default'	  => null,
					),
					'settings'	  => array(
						'description' => __( 'Array of settings', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'default'	  => false,
					),
					'is_shop'	  => array(
						'description' => __( 'Either it should return HTML', 'easycommerce' ),
						'required'	  => false,
						'type'		  => ['boolean', 'null'],
						'default'	  => false,
					),
					'shop_name'	  => array(
						'description' => __( 'Shop name', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'template-1',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Create a product
		$this->register_route(
			'/products',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $product, 'create' ),
				'args'		 => array(
					'title'		  => array(
						'description' => __( 'The product title', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'summary'		=> array(
						'description' => __( 'The product summary', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'description' => array(
						'description' => __( 'The product description', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'	  => array(
						'description' => __( 'The product status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'publish',
					),
					'slug'		  => array(
						'description' => __( 'The product slug', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'thumbnail'	  => array(
						'description' => __( 'The product thumbnail ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => array('null', 'string'),
					),
					'categories'  => array(
						'description' => __( 'List of category slugs or IDs', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(),
					),
					'brands'	  => array(
						'description' => __( 'List of brand slugs or IDs', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
					),
					'attributes'  => array(
						'description' => __( 'Product attributes and their values', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'required'	 => true,
							'properties' => array(
								'id'	 => array(
									'description' => __( 'Attribute ID', 'easycommerce' ),
									'type'		  => 'integer',
									'required'	  => true,
								),
								'values'  => array(
									'description' => __( 'Attribute values', 'easycommerce' ),
									'type'		  => 'array',
									'required'	  => true,
									'items'		 => array(
										'type'		 => 'object',
										'required'	 => true,
										'properties' => array(
											'id'	 => array(
												'description' => __( 'Value ID', 'easycommerce' ),
												'type'		  => 'integer',
												'required'	  => true,
											),
										),
									),
								),
							),
						),
					),
					'variations'  => array(
						'description' => __( 'Product variations', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'name'			  => array(
									'type'	   => 'string',
									'required' => true,
								),
								'sku'			  => array(
									'type'	   => 'string',
									'required' => true,
								),
								'type'			  => array(
									'type'	   => 'string',
									'required' => false,
									'default'  => 'physical',
								),
								'regular_price'	  => array(
									'type'	   => 'number',
									'required' => true,
								),
								'tax_class'		  => array('type' => 'string'),
								'low_stock_limit' => array('type' => 'integer'),
								'sale_price'	  => array( 'type' => ['number', 'boolean', 'null'] ),
								'stock_quantity'  => array('type' => ['integer', 'null']),
								'status'		  => array(
									'type'	   => 'string',
									'required' => false,
									'default'  => 'in_stock',
								),
								'attributes'  => array(
									'description' => __( 'Variation attributes and their values', 'easycommerce' ),
									'required'	  => false,
									'type'		  => 'array',
									'items'		  => array(
										'type'		 => 'object',
										'required'	 => true,
										'properties' => array(
											'attribute_id' => array(
												'description' => __( 'Attribute ID', 'easycommerce' ),
												'type'		  => 'integer',
												'required'	  => true,
											),
											'attribute_slug' => array(
												'description' => __( 'Attribute slug', 'easycommerce' ),
												'type'		  => 'string',
												'required'	  => true,
											),
											'value_slug' => array(
												'description' => __( 'Attribute value slug', 'easycommerce' ),
												'type'		  => 'string',
												'required'	  => true,
											),
											'value_id' => array(
												'description' => __( 'Attribute value ID', 'easycommerce' ),
												'type'		  => 'integer',
												'required'	  => true,
											),
										),
									),
								),
								'meta'			  => array(
									'type'	   => 'object',
									'required' => false,
								),
								'downloads'		  => array(
									'type'	   => 'array',
									'required' => false,
								), // @see Product_Variation_Download::add()
							),
						),
					),
					'meta'		  => array(
						'description' => __( 'Product meta data', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Get a product details
		$this->register_route(
			'/products/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $product, 'get' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Calculate a product's variations
		$this->register_route(
			'/products/(?P<id>\d+)/variations',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $product, 'get_variations' ),
				'args'		 => array(
					'id'		 => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'attributes' => array(
						'description' => __( 'An object of attributes key-value pair', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Edit a product
		$this->register_route(
			'/products/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $product, 'update' ),
				'args'		 => array(
					'id'		  => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'title'		  => array(
						'description' => __( 'The product title', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'description' => array(
						'description' => __( 'The product description', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'	  => array(
						'description' => __( 'The product status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'publish',
					),
					'slug'		  => array(
						'description' => __( 'The product slug', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'thumbnail'	  => array(
						'description' => __( 'The product thumbnail ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'categories'  => array(
						'description' => __( 'List of category slugs or IDs', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(),
					),
					'brands'	  => array(
						'description' => __( 'List of brand slugs or IDs', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
					),
					'attributes'  => array(
						'description' => __( 'Product attributes and their values', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'required'	 => true,
							'properties' => array(
								'id'	 => array(
									'description' => __( 'Attribute ID', 'easycommerce' ),
									'type'		  => 'integer',
									'required'	  => true,
								),
								'values'  => array(
									'description' => __( 'Attribute values', 'easycommerce' ),
									'type'		  => 'array',
									'required'	  => true,
									'items'		 => array(
										'type'		 => 'object',
										'required'	 => true,
										'properties' => array(
											'id'	 => array(
												'description' => __( 'Value ID', 'easycommerce' ),
												'type'		  => 'integer',
												'required'	  => true,
											),
										),
									),
								),
							),
						),
					),
					'variations'  => array(
						'description' => __( 'Product variations', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'id'			  => array(
									'type'	   => 'integer',
									'required' => false,
								),
								'name'			  => array(
									'type'	   => 'string',
									'required' => true,
								),
								'sku'			  => array(
									'type'	   => 'string',
									'required' => true,
								),
								'type'			  => array(
									'type'	   => 'string',
									'required' => false,
									'default'  => 'physical',
								),
								'regular_price'	  => array(
									'type'	   => 'number',
									'required' => true,
								),
								'tax_class'		  => array( 'type' => 'string' ),
								'low_stock_limit' => array( 'type' => 'integer' ),
								'sale_price'	  => array( 'type' => ['number', 'boolean', 'null'] ),
								'stock_quantity'  => array( 'type' => ['integer', 'null'] ),
								'status'		  => array(
									'type'	   => 'string',
									'required' => false,
									'default'  => 'in_stock',
								),
								'attributes'  => array(
									'description' => __( 'Variation attributes and their values', 'easycommerce' ),
									'required'	  => false,
									'type'		  => 'array',
									'items'		  => array(
										'type'		 => 'object',
										'required'	 => true,
										'properties' => array(
											'attribute_id' => array(
												'description' => __( 'Attribute ID', 'easycommerce' ),
												'type'		  => 'integer',
												'required'	  => true,
											),
											'attribute_slug' => array(
												'description' => __( 'Attribute slug', 'easycommerce' ),
												'type'		  => 'string',
												'required'	  => true,
											),
											'value_slug' => array(
												'description' => __( 'Attribute value slug', 'easycommerce' ),
												'type'		  => 'string',
												'required'	  => true,
											),
											'value_id' => array(
												'description' => __( 'Attribute value ID', 'easycommerce' ),
												'type'		  => 'integer',
												'required'	  => true,
											),
										),
									),
								),
								'meta'			  => array(
									'type'	   => 'object',
									'required' => false,
								),
								'downloads'		  => array(
									'type'	   => 'array',
									'required' => false,
								),
							),
						),
					),
					'meta'		  => array(
						'description' => __( 'Product meta data', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Delete a product
		$this->register_route(
			'/products/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $product, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Bulk delete products
		$this->register_route(
			'/products/bulk-delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $product, 'bulk_delete' ),
				'args'		 => array(
					'product_ids' => array(
						'description' => __( 'List of product IDs to delete', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		 => array(
							'type' => 'integer',
						),
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Update Bulk Product Statuses

		$this->register_route(
			'/products/update-statuses',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $product, 'bulk_update_status' ),
				'args'		 => array(
					'product_ids' => array(
						'description' => __( 'List of product IDs to update', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		 => array(
							'type' => 'integer',
						),
					),
					'status'	=> array(
						'description' => __( 'The new status for the orders', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// List a product's reviews
		$this->register_route(
			'/products/(?P<id>\d+)/reviews',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $product, 'get_reviews' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Create reviews for a product
		$this->register_route(
			'/products/(?P<id>\d+)/reviews',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $product, 'add_review' ),
				'args'		 => array(
					'id'	 => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'text'	 => array(
						'description' => __( 'Review text', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'rating' => array(
						'description'		=> __( 'Rating value, out of 5', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'integer',
						'validate_callback' => function ( $param ) {

							if ( $param >= 1 && $param <= 5 ) {
								return true;
							}

							$this->response_error( array( 'message' => __( 'Value must be between 1 to 5.', 'easycommerce' ) ), 403 );
						},
					),
				),
				'permission' => array( $this, 'is_customer' ),
			)
		);

		// Get active badges for a product
		$this->register_route(
			'/products/(?P<id>\d+)/badges',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $product, 'get_badges' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Set badge(s) for a product
		$this->register_route(
			'/products/(?P<id>\d+)/badges',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $product, 'set_badges' ),
				'args'		 => array(
					'id'	 => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'badges' => array(
						'description' => __( 'Object of badge_type => bool. Supported keys: new, best_seller, featured, sale.', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'object',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

	}

	/**
	 * Register Product Review-related API endpoints
	 */
	private function register_product_review_endpoints() {
		$review = new Product_Review();

		$this->register_route(
			'/product-reviews',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $review, 'get_all' ),
				'args'	   => array(
					'page' => array(
						'description' => __( 'The page number', 'easycommerce' ),
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page' => array(
						'description' => __( 'Reviews per page', 'easycommerce' ),
						'type'		  => 'integer',
						'default'	  => 10,
					),
					'product_id' => array(
						'type' => 'integer',
					),
					'customer_id' => array(
						'type' => 'integer',
					),
					'status' => array(
						'type'	  => 'string',
						'default' => 'approve',
					),
					'search' => array(
						'description' => __( 'The search query', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete a product review
		$this->register_route(
			'/product-reviews/(?P<id>\\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $review, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The review ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);
	}

	/**
	 * Register Customer-related API endpoints
	 */
	private function register_customer_endpoints() {
		$customer = new Customer();

		// Add a new customer
		$this->register_route(
			'/customers',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $customer, 'create' ),
				'args'		 => array(
					'first_name'	 => array(
						'description' => __( 'The customer first name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'last_name'		 => array(
						'description' => __( 'The customer last name', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'photo'			 => array(
						'description' => __( 'The customer photo URL', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'email'			 => array(
						'description'		=> __( 'The customer email address', 'easycommerce' ),
						'required'			=> true,
						'type'				=> 'string',
						'validate_callback' => 'is_email',
					),
					'password'		 => array(
						'description'		=> __( 'The customer password', 'easycommerce' ),
						'required'			=> true,
						'type'				=> 'string',
						'validate_callback' => function ( $param ) {
							return strlen( $param ) >= 6;
						},
					),
					'password_again' => array(
						'description' => __( 'The confirmation password', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'meta'			 => array(
						'description' => __( 'The key-value pair of meta data', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete a customer
		$this->register_route(
			'/customers/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $customer, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The customer ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// List all customers
		$this->register_route(
			'/customers',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $customer, 'list' ),
				'args'		 => array(
					's'		   => array(
						'description' => __( 'Search query for customer name or email', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'page'	   => array(
						'description' => __( 'Page number for pagination', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page' => array(
						'description' => __( 'Number of customers per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Get a specific customer
		$this->register_route(
			'/customers/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $customer, 'get' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The customer ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Get a customer's orders
		$this->register_route(
			'/customers/(?P<id>\d+)/orders',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $customer, 'list_orders' ),
				'args'		 => array(
					'id'	   => array(
						'description' => __( 'The customer ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					's'		   => array(
						'description' => __( 'Search key', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'   => array(
						'description' => __( 'Filter by transaction status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'page'	   => array(
						'description' => __( 'Page number for pagination', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page' => array(
						'description' => __( 'Number of transactions per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Profile-related API endpoints
	 */
	private function register_profile_endpoints() {
		$profile = new Profile();

		// Get current user summary
		$this->register_route(
			'/me/summary',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $profile, 'get_summary' ),
				'args'		 => array(),
				'permission' => array( $this, 'is_customer' ),
			)
		);

		// Get current user
		$this->register_route(
			'/me',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $profile, 'get_data' ),
				'args'		 => array(
					'fields' => array(
						'description' => __( 'Comma separated or an array of fields', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_customer' ),
			)
		);

		// Set current user
		$this->register_route(
			'/me',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $profile, 'set_data' ),
				'args'		 => array(
					'fields' => array(
						'description' => __( 'An array of key-value pair of fields', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_customer' ),
			)
		);

		// Get a customer's orders
		$this->register_route(
			'/me/orders',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $profile, 'get_orders' ),
				'args'		 => array(
					'status'   => array(
						'description' => __( 'Filter by order status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'per_page' => array(
						'description' => __( 'Filter per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_customer' ),
			)
		);

		// Get a customer's transactions
		$this->register_route(
			'/me/transactions',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $profile, 'get_transactions' ),
				'args'		 => array(
					'status' => array(
						'description' => __( 'Filter by transaction status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_customer' ),
			)
		);

		// Get a customer's downloads
		$this->register_route(
			'/me/downloads',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $profile, 'get_downloads' ),
				'args'		 => array(),
				'permission' => array( $this, 'is_customer' ),
			)
		);
	}

	/**
	 * Register Taxonomy-related API endpoints
	 */
	private function register_taxonomy_endpoints() {
		$taxonomy = new Taxonomy();

		// List product categories
		$this->register_route(
			'/products/categories',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $taxonomy, 'get_categories' ),
				'args'		 => array(
					'page'		  => array(
						'description' => __( 'The page number', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'	  => array(
						'description' => __( 'The number of products per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Create product category
		$this->register_route(
			'/products/categories',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $taxonomy, 'add_category' ),
				'args'		 => array(
					'name'	 => array(
						'description' => __( 'The category name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'slug'	 => array(
						'description' => __( 'The category slug', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'parent' => array(
						'description' => __( 'The parent category ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/products/categories/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $taxonomy, 'update_category' ),
				'args'		 => array(
					'id'	 => array(
						'description' => __( 'The category ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'name'	 => array(
						'description' => __( 'The category name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'slug'	 => array(
						'description' => __( 'The category slug', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'parent' => array(
						'description' => __( 'The parent category ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete product category
		$this->register_route(
			'/products/categories/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $taxonomy, 'delete_category' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The category ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/products/categories/bulk-delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $taxonomy, 'bulk_delete_categories' ),
				'args'		 => array(
					'ids' => array(
						'description' => __( 'The category IDs', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// List product brands
		$this->register_route(
			'/products/brands',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $taxonomy, 'get_brands' ),
				'args'		 => array(
					'page'		  => array(
						'description' => __( 'The page number', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'	  => array(
						'description' => __( 'The number of products per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Create product brand
		$this->register_route(
			'/products/brands',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $taxonomy, 'add_brand' ),
				'args'		 => array(
					'name'	 => array(
						'description' => __( 'The brand name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'parent' => array(
						'description' => __( 'The parent brand ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/products/brands/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $taxonomy, 'update_brand' ),
				'args'		 => array(
					'id'	 => array(
						'description' => __( 'The brand ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'name'	 => array(
						'description' => __( 'The brand name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'slug'	 => array(
						'description' => __( 'The brand slug', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'parent' => array(
						'description' => __( 'The parent brand ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete product brand
		$this->register_route(
			'/products/brands/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $taxonomy, 'delete_brand' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The brands ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Bulk delete product brands
		$this->register_route(
			'/products/brands/bulk-delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $taxonomy, 'bulk_delete_brands' ),
				'args'		 => array(
					'ids' => array(
						'description' => __( 'The brand IDs', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// List product tags
		$this->register_route(
			'/products/tags',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $taxonomy, 'get_tags' ),
				'args'		 => array(
					'page'		  => array(
						'description' => __( 'The page number', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'	  => array(
						'description' => __( 'The number of products per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Create product tag
		$this->register_route(
			'/products/tags',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $taxonomy, 'add_tag' ),
				'args'		 => array(
					'name'	 => array(
						'description' => __( 'The tag name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'parent' => array(
						'description' => __( 'The parent tag ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/products/tags/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $taxonomy, 'update_tag' ),
				'args'		 => array(
					'id'	 => array(
						'description' => __( 'The tag ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'name'	 => array(
						'description' => __( 'The tag name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'slug'	 => array(
						'description' => __( 'The tag slug', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'parent' => array(
						'description' => __( 'The parent tag ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete product tag
		$this->register_route(
			'/products/tags/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $taxonomy, 'delete_tag' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The tags ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Bulk delete product tags
		$this->register_route(
			'/products/tags/bulk-delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $taxonomy, 'bulk_delete_tags' ),
				'args'		 => array(
					'ids' => array(
						'description' => __( 'The tag IDs', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Cart-related API endpoints
	 */
	private function register_cart_endpoints() {
		$cart = new Cart();

		// Get the cart
		$this->register_route(
			'/cart',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $cart, 'list' ),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Add items to the cart
		$this->register_route(
			'/cart',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $cart, 'add_items' ),
				'args'		 => array(
					'products' => array(
						'description' => __( 'Array of products to add, each with an id and quantity.', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'id'	   => array(
									'type'		  => 'integer',
									'description' => __( 'The product ID', 'easycommerce' ),
									'required'	  => true,
								),
								'price_id' => array(
									'description' => __( 'The variation\'s price_id or an array of attributes', 'easycommerce' ),
									'required'	  => false,
									'default'	  => 1,
								),
								'quantity' => array(
									'type'		  => 'integer',
									'description' => __( 'The quantity to add', 'easycommerce' ),
									'required'	  => false,
									'default'	  => 1,
								),
							),
						),
					),
					'user_id'  => array(
						'description' => __( 'The user ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Update an item in the cart
		$this->register_route(
			'/cart/update',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $cart, 'update_item' ),
				'args'		 => array(
					'id'	   => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'price_id' => array(
						'description' => __( 'The product price ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
					'quantity' => array(
						'description' => __( 'The quantity to set', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
						'default'	  => 1,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Remove an item from the cart
		$this->register_route(
			'/cart/remove',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $cart, 'remove_item' ),
				'args'		 => array(
					'id'	   => array(
						'description' => __( 'The product ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'price_id' => array(
						'description' => __( 'The product price ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Lock the cart for payment processing
		$this->register_route(
			'/cart/lock',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $cart, 'lock' ),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Calculate shipping cost
		$this->register_route(
			'/cart/shipping',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $cart, 'get_shipping_options' ),
				'args'		 => array(
					'hash'			   => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => false,
					),
					'shipping_address' => array(
						'description' => __( 'The shipping address', 'easycommerce' ),
						'required'	  => false,
					),
					'billing_address'  => array(
						'description' => __( 'The billing address', 'easycommerce' ),
						'required'	  => false,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Set shipping method
		$this->register_route(
			'/cart/shipping',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $cart, 'set_shipping_method' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The shipping method ID', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		//set payment method
		$this->register_route(
			'/cart/payment',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $cart, 'set_payment_method' ),
				'args'		 => array(
					'payment_method' => array(
						'description' => __( 'The payment method', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Apply coupon to the cart
		$this->register_route(
			'/cart/coupon',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $cart, 'apply_coupon' ),
				'args'		 => array(
					'hash' => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'code' => array(
						'description' => __( 'The coupon code', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Remove coupon from the cart
		$this->register_route(
			'/cart/coupon/remove',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $cart, 'remove_coupon' ),
				'args'		 => array(
					'hash' => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'code' => array(
						'description' => __( 'The coupon code', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Clear the cart
		$this->register_route(
			'/cart/clear',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $cart, 'clear' ),
				'permission' => array( $this, 'is_user' ),
			)
		);
	}

	/**
	 * Register Order-related API endpoints
	 */
	private function register_order_endpoints() {
		$order = new Order();

		// Retrieve all orders or a specific order by ID
		$this->register_route(
			'/orders',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $order, 'get_all' ),
				'args'		 => array(
					'customer_id'	 => array(
						'description'		=> __( 'The customer ID', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'integer',
						'validate_callback' => function ( $param, $request, $key ) {

							if ( $this->is_admin( $request ) ) {
								return true;
							}

							$this->response_error( array( 'message' => __( 'You do not have permission to get the customer order.', 'easycommerce' ) ), 403 );
						},
					),
					'search_query'	  => array(
						'description'		=> __( 'Search by order ID, order name, or customer email', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'string'
					),
					'page'			 => array(
						'description' => __( 'Page number for pagination', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'		 => array(
						'description' => __( 'Number of transactions per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
					'from_date'		 => array(
						'description' => __( 'Filter by from date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'to_date'		 => array(
						'description' => __( 'Filter by from date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_member' ),
			)
		);

		/**
		 * Bulk delete orders
		 */
		$this->register_route(
			'/orders/bulk-delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $order, 'bulk_delete' ),
				'args'		 => array(
					'order_ids' => array(
						'description' => __( 'An array of order IDs to delete', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		  => array(
							'type' => 'integer',
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		/**
		 * Get an order's details
		 */
		$this->register_route(
			'/orders/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $order, 'get' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The order ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_customer' ),
			)
		);

		// Create a new order
		$this->register_route(
			'/orders',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $order, 'create' ),
				'args'		 => array(
					'items'				  => array(
						'description' => __( 'The order items or the cart hash', 'easycommerce' ),
						'required'	  => false,
					),
					'customer'			  => array(
						'description' => __( 'The customer ID or an array of customer data (name and email)', 'easycommerce' ),
						'required'	  => true,
					),
					'status'			  => array(
						'description' => __( 'The order status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'pending',
					),
					'billing_address'	  => array(
						'description' => __( 'The billing address', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
					),
					'shipping_address'	  => array(
						'description' => __( 'The shipping address', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
					),
					'meta'				  => array(
						'description' => __( 'The meta data', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'object',
					),
					'billing_as_shipping' => array(
						'description' => __( 'Should we use the billing address as shipping?', 'easycommerce' ),
						'required'	  => false,
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Update an existing order
		$this->register_route(
			'/orders/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $order, 'update' ),
				'args'		 => array(
					'id'			 => array(
						'description' => __( 'The order ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'status'		 => array(
						'description' => __( 'The new order status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'fulfill_status' => array(
						'description' => __( 'The new order fulfillment status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'total'			 => array(
						'description' => __( 'The updated total amount of the order', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'float',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Bulk update	statuses
		$this->register_route(
			'/orders/update-statuses',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $order, 'bulk_update_statuses' ),
				'args'		 => array(
					'order_ids' => array(
						'description' => __( 'An array of order IDs to update', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		  => array(
							'type' => 'integer',
						),
					),
					'status'	=> array(
						'description' => __( 'The new status for the orders', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'type' => array(
						'description' => __( 'The type of status to update: status or fulfillment', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
						'enum'		  => array( 'status', 'fulfillment' ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete an order
		$this->register_route(
			'/orders/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $order, 'delete_order' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The order ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Refund an order
		$this->register_route(
			'/orders/(?P<id>\d+)/refund',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $order, 'refund' ),
				'args'		 => array(
					'id'	 => array(
						'description' => __( 'The order ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'amount' => array(
						'description' => __( 'The refund amount', 'easycommerce' ),
						'required'	  => false,
					),
					'reason' => array(
						'description' => __( 'The refund reason', 'easycommerce' ),
						'required'	  => false,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Pay for an existing pending order (agent flow)
		$this->register_route(
			'/orders/(?P<id>\d+)/pay',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $order, 'pay' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The order ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);

		// Send email reminder
		$this->register_route(
			'/orders/(?P<id>\d+)/email',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $order, 'email' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The order ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'event' => array(
						'description' => __( 'The email event', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

	}

	/**
	 * Register Transaction-related API endpoints
	 */
	private function register_transaction_endpoints() {
		$transaction = new Transaction();

		// List transactions with filters
		$this->register_route(
			'/transactions',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $transaction, 'list' ),
				'args'		 => array(
					'page'			 => array(
						'description' => __( 'Page number for pagination', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'		 => array(
						'description' => __( 'Number of transactions per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
					'order_id'		 => array(
						'description'		=> __( 'Filter by order ID', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'integer',
						'validate_callback' => function ( $param, $request, $key ) {

							if ( $this->is_admin( $request ) ) {
								return true;
							}

							$this->response_error( array( 'message' => __( 'You do not have permission to set the customer ID.', 'easycommerce' ) ), 403 );
						},
					),
					'customer_id'	 => array(
						'description'		=> __( 'The customer ID', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'integer',
						'validate_callback' => function ( $param, $request, $key ) {

							if ( $this->is_admin( $request ) ) {
								return true;
							}

							$this->response_error( array( 'message' => __( 'You do not have permission to set the customer ID.', 'easycommerce' ) ), 403 );
						},
					),
					'customer_email' => array(
						'description'		=> __( 'The customer email', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'string',
						'validate_callback' => function ( $param, $request, $key ) {

							if ( $this->is_admin( $request ) ) {
								return true;
							}

							$this->response_error( array( 'message' => __( 'You do not have permission to get the customer order.', 'easycommerce' ) ), 403 );
						},
					),
					'from_date'		 => array(
						'description' => __( 'Filter by from date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'to_date'		 => array(
						'description' => __( 'Filter by from date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'type'			 => array(
						'description' => __( 'Filter by transaction type', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete a transaction
		$this->register_route(
			'/transactions/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $transaction, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The transaction ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Shipping Plan-related API endpoints
	 */
	private function register_shipping_plan_endpoints() {
		$shipping_plan = new Shipping_Plan();

		$this->register_route(
			'/shipping-plans',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $shipping_plan, 'get_all' ),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/shipping-plans',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $shipping_plan, 'create' ),
				'args'		 => array(
					'name'			   => array(
						'description'		=> __( 'The shipping plan name', 'easycommerce' ),
						'required'			=> true,
						'type'				=> 'string',
						'validate_callback' => function ( $param ) {
							return ! empty( $param );
						},
					),
					'description'	   => array(
						'description' => __( 'The shipping plan description', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'active'		   => array(
						'description' => __( 'The active status of the shipping plan (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => 1,
					),
					'taxable'		   => array(
						'description' => __( 'If this shipping plan is taxable (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => 0,
					),
					'calculation_base' => array(
						'description'		=> __( 'The calculation base for the shipping plan (e.g., price, weight, quantity)', 'easycommerce' ),
						'required'			=> true,
						'type'				=> 'string',
						'validate_callback' => function ( $param ) {
							return in_array( $param, array( 'price', 'weight', 'quantity' ), true );
						},
					),
					'regions' => array(
						'description' => __( 'Array of regions associated with this shipping plan', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'country' => array(
									'description' => __( 'Country code', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'state' => array(
									'description' => __( 'State code', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'city' => array(
									'description' => __( 'City name', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'zip_code' => array(
									'description' => __( 'Zip/postal code', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => false,
								),
							),
						),
					),

					'methods'		   => array(
						'description' => __( 'Array of shipping methods for the plan, each including name, min, max, and cost', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'name' => array(
									'description' => __( 'Method name', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'min'  => array(
									'description' => __( 'Minimum value for this method range', 'easycommerce' ),
									// 'type'		 => 'number',
									'required'	  => false,
								),
								'max'  => array(
									'description' => __( 'Maximum value for this method range', 'easycommerce' ),
									// 'type'		 => 'number',
									'required'	  => false,
								),
								'cost' => array(
									'description' => __( 'Cost associated with this method range', 'easycommerce' ),
									// 'type'		 => 'number',
									'required'	  => false,
								),
							),
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/shipping-plans/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $shipping_plan, 'update' ),
				'args'		 => array(
					'id'			   => array(
						'description' => __( 'The shipping plan ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'name'			   => array(
						'description' => __( 'The shipping plan name', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'description'	   => array(
						'description' => __( 'The shipping plan description', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'active'		   => array(
						'description' => __( 'The active status of the shipping plan (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
					),
					'taxable'		   => array(
						'description' => __( 'If this shipping plan is taxable (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => 0,
						'default'	  => false,
					),
					'calculation_base' => array(
						'description'		=> __( 'The calculation base for the shipping plan (e.g., price, weight, quantity)', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'string',
						'validate_callback' => function ( $param ) {
							return in_array( $param, array( 'price', 'weight', 'quantity' ), true );
						},
					),
					'regions' => array(
						'description' => __( 'Array of region codes associated with this shipping plan', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'country'	=> array( 'type' => 'string' ),
								'state'		=> array( 'type' => 'string' ),
								'city'		=> array( 'type' => 'string' ),
								'zip_code'	=> array( 'type' => 'string' ),
							),
						),
					),

					'methods'		   => array(
						'description' => __( 'Array of shipping methods for the plan, each including name, min, max, and cost', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'name' => array(
									'description' => __( 'Method name', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'min'  => array(
									'description' => __( 'Minimum value for this method range', 'easycommerce' ),
									'type'		  => 'number',
									'required'	  => true,
								),
								'max'  => array(
									'description' => __( 'Maximum value for this method range', 'easycommerce' ),
									'type'		  => 'number',
									'required'	  => true,
								),
								'cost' => array(
									'description' => __( 'Cost associated with this method range', 'easycommerce' ),
									'type'		  => 'number',
									'required'	  => true,
								),
							),
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/shipping-plans/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $shipping_plan, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The shipping plan ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/shipping-plans/by-location',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $shipping_plan, 'get_by_location' ),
				'args'		 => array(
					'region' => array(
						'description' => __( 'The region code', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_user' ),
			)
		);
	}

	/**
	 * Register Geo-related API endpoints
	 */
	private function register_location_endpoints() {
		$geo = new Geo();

		$this->register_route(
			'/geo/countries',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $geo, 'list_countries' ),
				'permission' => array( $this, 'is_user' ),
				'args'		 => array(
					'details' => array(
						'description' => __( 'Should it return details information', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => false,
					),
				),
			)
		);

		$this->register_route(
			'/geo/states/(?P<country>[a-zA-Z]+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $geo, 'list_states' ),
				'permission' => array( $this, 'is_user' ),
				'args'		 => array(
					'country' => array(
						'description'		=> __( 'The iso2 code of the country name', 'easycommerce' ),
						'required'			=> true,
						'type'				=> 'string',
						'validate_callback' => function ( $param ) {
							if ( strlen( $param ) !== 2 ) {
								$this->response_error(
									array(
										'message' => __( 'Country code must be of 2 characters', 'easycommerce' ),
									)
								);
							}
						},
					),
					'details' => array(
						'description' => __( 'Should it return details information', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => false,
					),
				),
			)
		);

		$this->register_route(
			'/geo/cities/(?P<country>[a-zA-Z]+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $geo, 'list_cities' ),
				'permission' => array( $this, 'is_user' ),
				'args'		 => array(
					'country' => array(
						'description'		=> __( 'The iso2 code of the country name', 'easycommerce' ),
						'required'			=> true,
						'type'				=> 'string',
						'validate_callback' => function ( $param ) {
							if ( strlen( $param ) !== 2 ) {
								$this->response_error(
									array(
										'message' => __( 'Country code must be of 2 characters', 'easycommerce' ),
									)
								);
							}
						},
					),
					'state'	  => array(
						'description' => __( 'The state code', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'details' => array(
						'description' => __( 'Should it return details information', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => false,
					),
				),
			)
		);

		$this->register_route(
			'/geo/currencies',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $geo, 'list_currencies' ),
				'permission' => array( $this, 'is_user' ),
				'args'		 => array(
					'country_code' => array(
						'description'		=> __( 'The iso2 code of the country name', 'easycommerce' ),
						'required'			=> false,
						'type'				=> 'string',
						'validate_callback' => function ( $param ) {
							if ( strlen( $param ) !== 2 ) {
								$this->response_error(
									array(
										'message' => __( 'Country code must be of 2 characters', 'easycommerce' ),
									)
								);
							}
						},
					),
				),
			)
		);
	}

	/**
	 * Register Coupon-related API endpoints
	 */
	private function register_coupon_endpoints() {
		$coupon = new Coupon();

		// Endpoint to retrieve all coupons or a specific coupon by ID.
		$this->register_route(
			'/coupons',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $coupon, 'get_all' ),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Endpoint to create a new coupon.
		$this->register_route(
			'/coupons',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $coupon, 'create' ),
				'args'		 => array(
					'name'			=> array(
						'description' => __( 'The coupon name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'code'			=> array(
						'description' => __( 'The unique coupon code', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'type'			=> array(
						'description'	=> __( 'The discount type', 'easycommerce' ),
						'required'		=> true,
						'type'			=> 'string',
						'enum'			=> array( 'percentage', 'fixed', 'products', 'free_shipping' ),
					),
					'offer'			=> array(
						'description' => __( 'The discounted offer', 'easycommerce' ),
						'required'	  => false,
						'type'		  => array( 'null', 'text' ),
					),
					'active'		=> array(
						'description' => __( 'The active status of the coupon (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => 1,
					),
					'rules'			=> array(
						'description' => __( 'Array of coupon rules, each with type and value', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'type'	=> array(
									'description' => __( 'The rule type', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'value' => array(
									'description' => __( 'The rule value', 'easycommerce' ),
									'type'		  => 'mixed',
									'required'	  => true,
								),
							),
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Endpoint to retrieve details of a specific coupon by ID
		$this->register_route(
			'/coupons/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $coupon, 'get_single' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The coupon ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Endpoint to update a coupon by ID
		$this->register_route(
			'/coupons/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $coupon, 'update' ),
				'args'		 => array(
					'id'			=> array(
						'description' => __( 'The coupon ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'name'			=> array(
						'description' => __( 'The coupon name', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'code'			=> array(
						'description' => __( 'The unique coupon code', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'type'		   => array(
						'description' => __( 'The discount type', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'enum'		  => array( 'percentage', 'fixed', 'products', 'free_shipping' ),
					),
					'offer'			=> array(
						'description' => __( 'The discounted offer', 'easycommerce' ),
						'required'	  => false,
						'type'		  => array( 'null', 'text' ),
					),
					'active'		=> array(
						'description' => __( 'The active status of the coupon (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
					),
					'rules'			=> array(
						'description' => __( 'Array of coupon rules, each with type and value', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'type'	=> array(
									'description' => __( 'The rule type', 'easycommerce' ),
									'type'		  => 'string',
								),
								'value' => array(
									'description' => __( 'The rule value', 'easycommerce' ),
									'type'		  => 'mixed',
								),
							),
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Endpoint to delete a coupon by ID
		$this->register_route(
			'/coupons/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $coupon, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'The coupon ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Bulk delete coupons
		$this->register_route(
			'/coupons/bulk/delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $coupon, 'bulk_delete' ),
				'args'		 => array(
					'coupon_ids' => array(
						'description' => __( 'List of coupon IDs to delete', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		 => array(
							'type' => 'integer',
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
		// Bulk update coupons
		$this->register_route(
			'/coupons/bulk/status',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $coupon, 'bulk_status_update' ),
				'args'		 => array(
					'coupon_ids' => array(
						'description' => __( 'List of coupon IDs to update', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		  => array(
							'type' => 'integer',
						),
					),
					'status' => array(
						'description' => __( 'Status value: active or inactive', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
						'enum'		  => array( 'active', 'inactive' ),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Unified Tax API endpoints.
	 */
	private function register_tax_endpoints() {
		$tax = new Tax();

		$this->register_route(
			'/taxes',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $tax, 'list_classes' ),
				'args'		 => array(),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/taxes',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $tax, 'create_class' ),
				'args'		 => array(
					'name'		  => array(
						'description' => __( 'Tax class name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
						'validate_callback' => function( $param ) {
							if ( empty( $param ) ) {
								$this->response_error( array( 'message' => __( 'Class name must not be empty.', 'easycommerce' ) ), 403 );
							}

							return true;
						},
					),
					'description' => array(
						'description' => __( 'Description of the tax class', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'	  => array(
						'description' => __( 'Status of the tax class (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
						'default'	  => true,
					),
					'rates'		  => array(
						'description' => __( 'Array of rates for the tax class', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'country'  => array(
									'description' => __( 'Country code (ISO2)', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'state'	   => array(
									'description' => __( 'State code', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => false,
								),
								'city'	   => array(
									'description' => __( 'City code', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => false,
								),
								'rate'	   => array(
									'description' => __( 'Tax rate percentage', 'easycommerce' ),
									'type'		  => 'number',
									'required'	  => true,
								),
								'priority' => array(
									'description' => __( 'Priority of this rate', 'easycommerce' ),
									'type'		  => 'integer',
									'default'	  => 1,
								),
								'compound' => array(
									'description' => __( 'Is this compound', 'easycommerce' ),
									'type'		  => 'integer',
									'default'	  => 0,
								),
							),
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/taxes/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $tax, 'get_class' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'Tax class ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/taxes/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $tax, 'update_class' ),
				'args'		 => array(
					'id'		  => array(
						'description' => __( 'Tax class ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'name'		  => array(
						'description' => __( 'Tax class name', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
						'validate_callback' => function( $param ) {
							if ( empty( $param ) ) {
								$this->response_error( array( 'message' => __( 'Class name must not be empty.', 'easycommerce' ) ), 403 );
							}

							return true;
						},
					),
					'description' => array(
						'description' => __( 'Description of the tax class', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'	  => array(
						'description' => __( 'Status of the tax class (1 for active, 0 for inactive)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'boolean',
					),
					'rates'		  => array(
						'description' => __( 'Array of rates for the tax class', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		  => array(
							'type'		 => 'object',
							'properties' => array(
								'country'  => array(
									'description' => __( 'Country code (ISO2)', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => true,
								),
								'state'	   => array(
									'description' => __( 'State code', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => false,
								),
								'city'	   => array(
									'description' => __( 'City code', 'easycommerce' ),
									'type'		  => 'string',
									'required'	  => false,
								),
								'rate'	   => array(
									'description' => __( 'Tax rate percentage', 'easycommerce' ),
									'type'		  => 'number',
									'required'	  => true,
								),
								'priority' => array(
									'description' => __( 'Priority of this rate', 'easycommerce' ),
									'type'		  => 'integer',
									'default'	  => 1,
								),
								'compound' => array(
									'description' => __( 'Is this compound', 'easycommerce' ),
									'type'		  => 'integer',
									'default'	  => 0,
								),
							),
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		$this->register_route(
			'/taxes/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $tax, 'delete_class' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'Tax class ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// API for searching for csv tax file names
		$this->register_route(
			'/taxes/tax-files',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $tax, 'get_tax_files' ),
				'permission' => array( $this, 'is_manager' ),
				'args'				=> array(
					'countries'			=> array(
						'required'		=> true,
						'type'			=> 'array',
						'items'         => array(
							'type'              => 'string',
							'validate_callback' => function( $value ) {
								return (bool) preg_match( '/^[a-zA-Z]{2}$/', $value );
							},
						),
					),
				),
			)
		);

		// API for getting tax rates from a csv file
		$this->register_route(
			'/taxes/rates',
			[
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => [ $tax, 'get_tax_rates' ],
				'permission' => array( $this, 'is_manager' ),
				'args' => array(
					'country' => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => function( $value ) {
							return (bool) preg_match( '/^[a-zA-Z]{2}$/', $value );
						},
					),
				),
			]
		);

	}

	/**
	 * Register Abandoned Carts API endpoints.
	 */
	private function register_abandoned_cart_endpoints() {
		$abandoned_cart = new Abandoned_Cart();

		$this->register_route(
			'/abandoned-carts',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $abandoned_cart, 'get_all' ),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Abandoned cart removal
		$this->register_route(
			'/abandoned-carts/remove',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $abandoned_cart, 'remove' ),
				'args'		 => array(
					'hash' => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Get email content (subject + resolved body) for a specific cart
		$this->register_route(
			'/abandoned-carts/email-content',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $abandoned_cart, 'get_email_content' ),
				'args'		 => array(
					'hash' => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Abandoned cart reminder
		$this->register_route(
			'/abandoned-carts/remind',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $abandoned_cart, 'remind' ),
				'args'		 => array(
					'hash' => array(
						'description' => __( 'The cart hash', 'easycommerce' ),
						'required'	  => true,
					),
					'subject' => array(
						'description' => __( 'Custom email subject (optional — uses settings default when omitted)', 'easycommerce' ),
						'required'	  => false,
					),
					'body' => array(
						'description' => __( 'Custom email body HTML (optional — uses settings default when omitted)', 'easycommerce' ),
						'required'	  => false,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Bulk delete abandoned carts
		$this->register_route(
			'/abandoned-carts/bulk/delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $abandoned_cart, 'bulk_delete' ),
				'args'		 => array(
					'abandoned_cart_hashes' => array(
						'description' => __( 'List of abandoned cart hashes to delete', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		 => array(
							'type' => 'string',
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		//bulk reminder abandoned cart
		$this->register_route(
			'/abandoned-carts/bulk/reminder',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $abandoned_cart, 'bulk_reminder' ),
				'args'		 => array(
					'abandoned_cart_hashes' => array(
						'description' => __( 'List of abandoned cart hashes to remind', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		 => array(
							'type' => 'string',
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Clear all abandoned carts
		$this->register_route(
			'/abandoned-carts/clean-all',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $abandoned_cart, 'clean' ),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Clear only invalid abandoned carts
		$this->register_route(
			'/abandoned-carts/clean-invalid',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $abandoned_cart, 'clean_invalid' ),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register AI API endpoints.
	 */
	private function register_ai_endpoints() {
		$ai = new AI();

		$this->register_route(
			'/ai/write',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $ai, 'write_copy' ),
				'args'		 => array(
					'product'	=> array(
						'description' => __( 'The `product` name', 'easycommerce' ),
						'required'	  => true,
					),
					'prompt'   => array(
						'description' => __( 'The `prompt`', 'easycommerce' ),
						'required'	  => true,
					),
					'length' => array(
						'description' => __( 'Content length- `short` or `long`', 'easycommerce' ),
						'required'	  => false,
						'validate_callback' => function( $value ) {
							return in_array( $value, ['long', 'short'] );
						},
						'default'	=> 'short',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		$this->register_route(
			'/ai/draw',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $ai, 'draw_image' ),
				'args'		 => array(
					'prompt'   => array(
						'description' => __( 'The `prompt`', 'easycommerce' ),
						'required'	  => true,
					),
					'size' => array(
						'description' => __( 'Image size - `1024x1024`, `1792x1024`, `1024x1792`', 'easycommerce' ),
						'required'	  => false,
						'enum'		  => array( '1024x1024', '1792x1024', '1024x1792' ),
						'default'	=> '1024x1024',
					),
					'quality'	=> array(
						'description' => __( 'The quality - `standard`, `hd`', 'easycommerce' ),
						'required'	  => false,
						'enum'		  => array( 'standard', 'hd' ),
						'default'	=> 'standard'
					),
					'product'	=> array(
						'description' => __( 'The `product` name', 'easycommerce' ),
						'required'	  => false,
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		$this->register_route(
			'/ai/edit',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $ai, 'edit_image' ),
				'args'		 => array(
					'image'	  => array(
						'description' => __( 'The `image` URL or the attachment_id', 'easycommerce' ),
						'required'	  => true,
					),
					'prompt'   => array(
						'description' => __( 'The `prompt`', 'easycommerce' ),
						'required'	  => true,
					),
					'size' => array(
						'description' => __( 'Image size - `1024x1024`, `1792x1024`, `1024x1792`', 'easycommerce' ),
						'required'	  => false,
						'enum'		  => array( '1024x1024', '1792x1024', '1024x1792' ),
						'default'	=> '1024x1024',
					),
					'quality'	=> array(
						'description' => __( 'The quality - `standard`, `hd`', 'easycommerce' ),
						'required'	  => false,
						'enum'		  => array( 'standard', 'hd' ),
						'default'	=> 'standard'
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		$this->register_route(
			'/ai/design',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $ai, 'design_template' ),
				'args'		 => array(
					'prompt'   => array(
						'description' => __( 'The `prompt`', 'easycommerce' ),
						'required'	  => true,
					),
					'product'	=> array(
						'description' => __( 'The `product`', 'easycommerce' ),
						'required'	  => false,
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		$this->register_route(
			'/ai/generate-attributes',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $ai, 'generate_attributes' ),
				'args'		 => array(
					'product'	=> array(
						'description'		=> __( 'The product name', 'easycommerce' ),
						'required'			=> true,
						'type'				=> 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

	}

	/**
	 * Register AI Assistant endpoint (customer-facing).
	 */
	private function register_agent_assistant_endpoints() {
		$agent = new Assistant();

		$this->register_route(
			'/ai/agent/assistant',
			array(
				'methods'    => WP_REST_Server::CREATABLE,
				'callback'   => array( $agent, 'handle' ),
				'args'       => array(
					'message'    => array(
						'description'       => __( 'Customer message', 'easycommerce' ),
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'session_id' => array(
						'description'       => __( 'Unique conversation session ID. Omit to start a new session.', 'easycommerce' ),
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission' => array( $this, 'is_nonce_verified' ),
			)
		);
	}

	/**
	 * Register AI Copilot endpoint (admin only).
	 */
	private function register_agent_copilot_endpoints() {
		$copilot = new Copilot();

		$this->register_route(
			'/ai/agent/copilot',
			array(
				'methods'    => WP_REST_Server::CREATABLE,
				'callback'   => array( $copilot, 'handle' ),
				'args'       => array(
					'message'    => array(
						'description'       => __( 'Admin message or question', 'easycommerce' ),
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'session_id' => array(
						'description'       => __( 'Unique conversation session ID. Omit to start a new session.', 'easycommerce' ),
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);
	}

	/**
	 * Register Email Placeholder-related API endpoints
	 */
	private function register_email_placeholder_endpoints() {
		$email_placeholder = new Email_Placeholder();

		// Get email placeholders for TinyMCE plugin
		$this->register_route(
			'/email-placeholders',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $email_placeholder, 'get_placeholders' ),
				'args'		 => array(
					'search' => array(
						'description' => __( 'Search term to filter placeholders', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}

	/**
	 * Register Importer-related API endpoints
	 */
	private function register_importer_endpoints() {
		$importer = new Importer();

		// Upload CSV
		$this->register_route(
			'/importer/upload',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $importer, 'upload_csv' ),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Map columns
		$this->register_route(
			'/importer/map',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $importer, 'map_columns' ),
				'args'		 => array(
					'mapping' => array(
						'description' => __( 'Column mapping object', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'object',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Import products
		$this->register_route(
			'/importer/import',
			array(
				'methods'			  => WP_REST_Server::CREATABLE,
				'callback'			  => array( $importer, 'import_products' ),
				'args'				  => array(
					'import_id' => array(
						'type'	   => 'string',
						'required' => false,
					),
					'offset' => array(
						'type'	  => 'integer',
						'default' => 0,
					),
				),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);

		// Import demo products
		$this->register_route(
			'/importer/samples',
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $importer, 'import_sample_products' ),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);

		// Demo content status (how many demo products exist)
		$this->register_route(
			'/importer/demo',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $importer, 'demo_status' ),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);

		// Remove demo products, media and categories
		$this->register_route(
			'/importer/demo',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $importer, 'delete_demo' ),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);

		// Sideload an image from an external link
		$this->register_route(
			'/importer/sideload',
			array(
				'methods'			  => WP_REST_Server::CREATABLE,
				'callback'			  => array( $importer, 'sideload_image' ),
				'args'				  => array(
					'url' => array(
						'description' => __( 'The URL to the external image', 'easycommerce' ),
						'type'	   => 'string',
						'required' => true,
					),
					'post_id' => array(
						'description' => __( 'The post_id to attach this image to', 'easycommerce' ),
						'type'	   => 'integer',
						'required' => false,
						'default'  => 0,
					),
					'description' => array(
						'description' => __( 'Image description', 'easycommerce' ),
						'type'	   => 'string',
						'required' => false,
					),
				),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);
	}

	/**
	 * Register Log-related API endpoints
	 */
	private function register_log_endpoints() {
		$log = new Log();

		// List logs
		$this->register_route(
			'/logs',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $log, 'list' ),
				'args'		 => array(
					'object'	=> array(
						'description' => __( 'Filter by object', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'action'	=> array(
						'description' => __( 'Filter by action', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'type'		=> array(
						'description' => __( 'Filter by log type', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'object_id' => array(
						'description' => __( 'Filter by object ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
					'user_id'	=> array(
						'description' => __( 'Filter by user ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
					'is_public'	=> array(
						'description' => __( 'Filter by public status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
					'from_date' => array(
						'description' => __( 'From date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'to_date'	=> array(
						'description' => __( 'To date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'page'		=> array(
						'description' => __( 'Page number', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'	=> array(
						'description' => __( 'Per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
					'sort'		=> array(
						'description' => __( 'Sort order (asc or desc)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'desc',
						'enum'		  => array( 'asc', 'desc' ),
					),
				),
				'permission' => array( $this, 'is_member' ),
			)
		);

		// Get single log
		$this->register_route(
			'/logs/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $log, 'get' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'Log ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Add log
		$this->register_route(
			'/logs',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $log, 'add' ),
				'args'		 => array(
					'object'	 => array(
						'description' => __( 'Object', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'action'	 => array(
						'description' => __( 'Action', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'string',
					),
					'object_id'	 => array(
						'description' => __( 'Object ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'user_id'	 => array(
						'description' => __( 'User ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
					'note'		 => array(
						'description' => __( 'Note', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'ip_address' => array(
						'description' => __( 'IP Address', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'seen'		 => array(
						'description' => __( 'Seen status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 0,
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Delete log
		$this->register_route(
			'/logs/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $log, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'Log ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);
	}

	/**
	 * Register Notice-related API endpoints
	 */
	private function register_notice_endpoints() {
		$notice = new Notice();

		// List notices with optional type and screen filters
		$this->register_route(
			'/notices',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $notice, 'list' ),
				'args'		 => array(
					'type'	 => array(
						'description' => __( 'Filter notices by type (e.g., error, success)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'screen' => array(
						'description' => __( 'Filter notices by screen context (e.g., #/orders)', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'limit' => array(
						'description' => __( 'Number of notices to return', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 2
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);

		// Dismiss a notice
		$this->register_route(
			'/notices/(?P<id>[a-zA-Z0-9_-]+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $notice, 'dismiss' ),
				'args'		 => array(
					'id'	 => array(
						'description' => __( 'Notife ID to remove', 'easycommerce' ),
						'required'	  => true,
					),
				),
				'permission' => array( $this, 'is_admin' ),
			)
		);
	}

	/**
	 * Register Refund-related API endpoints
	 */
	private function register_refund_endpoints() {
		$refund = new Refund();

		// List refunds
		$this->register_route(
			'/refunds',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $refund, 'list' ),
				'args'		 => array(
					'order_id'		  => array(
						'description' => __( 'Filter by order ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
					'status'		  => array(
						'description' => __( 'Filter by refund status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'payment_gateway' => array(
						'description' => __( 'Filter by payment gateway', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'from_date'		  => array(
						'description' => __( 'Filter by from date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'to_date'		  => array(
						'description' => __( 'Filter by to date', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'page'			  => array(
						'description' => __( 'Page number', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 1,
					),
					'per_page'		  => array(
						'description' => __( 'Per page', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
						'default'	  => 10,
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Create a refund
		$this->register_route(
			'/refunds',
			array(
				'methods'	 => WP_REST_Server::CREATABLE,
				'callback'	 => array( $refund, 'create' ),
				'args'		 => array(
					'order_id'		  => array(
						'description' => __( 'Order ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'amount'		  => array(
						'description' => __( 'Refund amount', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'number',
					),
					'currency'		  => array(
						'description' => __( 'Currency', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'USD',
					),
					'reason'		  => array(
						'description' => __( 'Refund reason', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'		  => array(
						'description' => __( 'Refund status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
						'default'	  => 'pending',
					),
					'transaction_id'  => array(
						'description' => __( 'Transaction ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'payment_gateway' => array(
						'description' => __( 'Payment gateway', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'notes'			  => array(
						'description' => __( 'Notes', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'refunded_by'	  => array(
						'description' => __( 'Refunded by user ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Get a refund
		$this->register_route(
			'/refunds/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	 => array( $refund, 'get' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'Refund ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Update a refund
		$this->register_route(
			'/refunds/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::EDITABLE,
				'callback'	 => array( $refund, 'update' ),
				'args'		 => array(
					'id'			  => array(
						'description' => __( 'Refund ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
					'order_id'		  => array(
						'description' => __( 'Order ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
					'amount'		  => array(
						'description' => __( 'Refund amount', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'number',
					),
					'currency'		  => array(
						'description' => __( 'Currency', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'reason'		  => array(
						'description' => __( 'Refund reason', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'status'		  => array(
						'description' => __( 'Refund status', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'transaction_id'  => array(
						'description' => __( 'Transaction ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'payment_gateway' => array(
						'description' => __( 'Payment gateway', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'notes'			  => array(
						'description' => __( 'Notes', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'string',
					),
					'refunded_by'	  => array(
						'description' => __( 'Refunded by user ID', 'easycommerce' ),
						'required'	  => false,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Delete a refund
		$this->register_route(
			'/refunds/(?P<id>\d+)',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $refund, 'delete' ),
				'args'		 => array(
					'id' => array(
						'description' => __( 'Refund ID', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'integer',
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);

		// Bulk delete refunds
		$this->register_route(
			'/refunds/bulk-delete',
			array(
				'methods'	 => WP_REST_Server::DELETABLE,
				'callback'	 => array( $refund, 'bulk_delete' ),
				'args'		 => array(
					'ids' => array(
						'description' => __( 'List of refund IDs to delete', 'easycommerce' ),
						'required'	  => true,
						'type'		  => 'array',
						'items'		 => array(
							'type' => 'integer',
						),
					),
				),
				'permission' => array( $this, 'is_manager' ),
			)
		);
	}
}
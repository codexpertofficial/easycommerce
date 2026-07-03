<?php
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Location;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Customer;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Coupon as Coupon_Model;

function easycommerce_dev_store( $path = '' ) {
	$home = defined( 'EASYCOMMERCE_STORE' ) ? untrailingslashit( EASYCOMMERCE_STORE ) : 'https://my.easycommerce.dev';
	$path = ltrim( $path, '/' );

	return "{$home}/{$path}";
}

function easycommerce_dev_docs( $path = '' ) {
	$home = defined( 'EASYCOMMERCE_DOCS' ) ? untrailingslashit( EASYCOMMERCE_DOCS ) : 'https://easycommerce.dev/docs';
	$path = ltrim( $path, '/' );

	return "{$home}/{$path}";
}

/**
 * Whether a Generative AI feature is enabled in settings.
 *
 * Reads the raw option array directly instead of Utility::get_option(): that
 * helper treats a stored "0" as empty and falls back to its default, which would
 * make the disabled state unreachable for a default-on checkbox. Defaults to
 * enabled when the feature key has never been saved (matches the field defaults
 * in app/Config/settings.php → ai → generative-ai).
 *
 * @param string $feature Feature key: 'text_generator', 'image_generator',
 *                        'template_generator', 'attribute_generator'.
 * @return bool
 */
function easycommerce_is_ai_feature_enabled( $feature ) {
	$generative = get_option( 'easycommerce-ai-generative-ai' );

	if ( ! is_array( $generative ) || ! isset( $generative[ $feature ] ) ) {
		return true;
	}

	return '0' !== (string) $generative[ $feature ];
}

/**
 * Returns the home URL of the WordPress site.
 *
 * @param string $path    Optional. Path relative to the home URL.
 * @param int    $blog_id Optional. ID of the blog in a multisite installation.
 *
 * @return string Home URL with optional path appended.
 */
function easycommerce_home_url( $path = '', $blog_id = null ) {
	return get_home_url( $blog_id, $path );
}

function easycommerce_shop_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'shop' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_checkout_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'checkout' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_dashboard_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'dashboard' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_registration_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'registration' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_terms_of_service_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'terms-of-service' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_privacy_policy_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'privacy-policy' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_reset_password_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'reset-password' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_payment_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'payment' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function send_reset_password_email( $user_data, $key ) {
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    $site_name  = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

    $reset_url = add_query_arg(
        array(
            'action' => 'ecrp',
            'key'    => $key,
            'login'  => rawurlencode( $user_login ),
        ),
        easycommerce_reset_password_page( true )
    );

    $subject = sprintf( __( '[%s] Password Reset', 'easycommerce' ), $site_name );

    $message  = __( 'Someone has requested a password reset for the following account:', 'easycommerce' ) . "\r\n\r\n";
    $message .= sprintf( __( 'Site Name: %s', 'easycommerce' ), $site_name ) . "\r\n\r\n";
    $message .= sprintf( __( 'Username: %s', 'easycommerce' ), $user_login ) . "\r\n\r\n";
    $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'easycommerce' ) . "\r\n\r\n";
    $message .= __( 'To reset your password, visit the following address:', 'easycommerce' ) . "\r\n\r\n";
    $message .= $reset_url . "\r\n";

    return wp_mail( $user_email, $subject, $message );
}

function easycommerce_rest_base() {
	return rest_url( '/easycommerce/v1' );
}

function easycommerce_single_product_patterns() {
	return array(
		'template-1' => __( 'Template 1', 'easycommerce' ),
		'template-2' => __( 'Template 2', 'easycommerce' ),
	);
}

function easycommerce_checkout_template() {
	return Utility::get_option( 'checkout', 'settings', 'checkout_template', 'template-1' );
}

function easycommerce_checkout_templates() {
	return array(
		'template-1' => __( 'Template 1', 'easycommerce' ),
		'template-2' => __( 'Template 2', 'easycommerce' ),
		'template-3' => __( 'Template 3', 'easycommerce' ),
	);
}

function easycommerce_refund_reasons() {
	return array(
		'requested_by_customer' => __( 'Request by Customer', 'easycommerce' ),
		'duplicate'             => __( 'Duplicate', 'easycommerce' ),
		'fraudulent'            => __( 'Fraudulent', 'easycommerce' ),
	);
}

function easycommerce_product_sort_options() {
	return array(
		'low-to-high'    => __( 'Low to High', 'easycommerce' ),
		'high-to-low'    => __( 'High to Low', 'easycommerce' ),
		'newest'         => __( 'Newest', 'easycommerce' ),
		'oldest'         => __( 'Oldest', 'easycommerce' ),
		'best-selling'   => __( 'Best Selling', 'easycommerce' ),
		'lowest-selling' => __( 'Lowest Selling', 'easycommerce' ),
		'top-rating'     => __( 'Top rating', 'easycommerce' ),
		'lowest-rating'  => __( 'Lowest rating', 'easycommerce' ),
	);
}

function easycommerce_email_events() {
	return array(
		'pending'    => __( 'Pending Order', 'easycommerce' ),
		'processing' => __( 'Processing Order', 'easycommerce' ),
		'completed'  => __( 'Completed Order', 'easycommerce' ),
		'cancelled'  => __( 'Cancelled Order', 'easycommerce' ),
		'on_hold'    => __( 'On-hold Order', 'easycommerce' ),
		'refunded'   => __( 'Refunded Order', 'easycommerce' ),
		'partially_refunded' => __( 'Partially Refunded Order', 'easycommerce' ),
		'failed'     => __( 'Failed Order', 'easycommerce' ),
	);
}

function easycommerce_email_default( $event ) {
	$default = include EASYCOMMERCE_PLUGIN_DIR . "app/Config/email-defaults/{$event}.php";

	return $default;
}

function easycommerce_order_statuses() {
	return array(
		'pending'    			=> __( 'Pending', 'easycommerce' ),
		'processing' 			=> __( 'Processing', 'easycommerce' ),
		'completed'  			=> __( 'Completed', 'easycommerce' ),
		'cancelled'  			=> __( 'Cancelled', 'easycommerce' ),
		'on_hold'    			=> __( 'On Hold', 'easycommerce' ),
		'partially_refunded'	=> __( 'Partially Refunded', 'easycommerce' ),
		'refunded'   			=> __( 'Refunded', 'easycommerce' ),
		'failed'   				=> __( 'Failed', 'easycommerce' ),
	);
}

function easycommerce_get_all_payment_methods() {

    $methods = array(
        'cash-on-delivery' => array(
            'label' => __('Cash on Delivery', 'easycommerce'),
            'is_addon' => false
        ),
        'stripe' => array(
            'label' => __('Stripe', 'easycommerce'),
            'is_addon' => false
        ),
        'paypal' => array(
            'label' => __('PayPal', 'easycommerce'),
            'is_addon' => false
        ),
        'square' => array(
            'label' => __('Square', 'easycommerce'),
            'is_addon' => false
        ),
        'braintree' => array(
            'label' => __('Braintree', 'easycommerce'),
            'is_addon' => false
        ),
        'mollie' => array(
            'label' => __('Mollie', 'easycommerce'),
            'is_addon' => false
        ),
        'paddle' => array(
            'label' => __('Paddle', 'easycommerce'),
            'is_addon' => true
        ),
        'bank' => array(
            'label' => __('Bank Transfer', 'easycommerce'),
            'is_addon' => true
        ),
    );

    // Add icons (custom uploaded or default)
    $methods = easycommerce_add_payment_method_icons( $methods );

    return $methods;
}

function easycommerce_product_statuses() {
	return array(
		'publish' => __( 'Live', 'easycommerce' ),
		'draft'   => __( 'Draft', 'easycommerce' ),
		'trash'   => __( 'Trash', 'easycommerce' ),
	);
}

function easycommerce_global_default_status() {
	return Utility::get_option( 'order', 'settings', 'default_order_status' ) ?? 'pending';
}

function easycommerce_fulfill_statuses() {
	return array(
		'unfulfilled'         => __( 'Unfulfilled', 'easycommerce' ),
		'fulfilled'           => __( 'Fulfilled', 'easycommerce' ),
		'partially_fulfilled' => __( 'Partially Fulfilled', 'easycommerce' ),
		'shipped'             => __( 'Shipped', 'easycommerce' ),
		'delivered'           => __( 'Delivered', 'easycommerce' ),
		'returned'            => __( 'Returned', 'easycommerce' ),
	);
}

function easycommerce_length_units() {
	return array(
        array(
            'value' => 'mm',
            'label' => __('Millimeter', 'easycommerce')
        ),
        array(
            'value' => 'cm',
            'label' => __('Centimeter', 'easycommerce')
        ),
        array(
            'value' => 'in',
            'label' => __('Inch', 'easycommerce')
        ),
        array(
            'value' => 'm',
            'label' => __('Meter', 'easycommerce')
        ),
		array(
            'value' => 'ft',
            'label' => __('Foot', 'easycommerce')
        ),
		array(
            'value' => 'yd',
            'label' => __('Yard', 'easycommerce')
        ),
    );
}

function easycommerce_weight_units() {
	return array(
        array(
            'value' => 'g',
            'label' => __('Gram', 'easycommerce')
        ),
        array(
            'value' => 'kg',
            'label' => __('Kilogram', 'easycommerce')
        ),
        array(
            'value' => 'lb',
            'label' => __('Pound', 'easycommerce')
        ),
        array(
            'value' => 'oz',
            'label' => __('Ounce', 'easycommerce')
        ),
    );
}

function easycommerce_weight_unit_conversion( $baseUnit = 'g' ) {
	$conversions = [
        'g' => [
            'g'  => 1,          // Grams
            'kg' => 1000,       // Kilograms to grams
            'lb' => 453.592,    // Pounds to grams
            'oz' => 28.3495,    // Ounces to grams
        ],
        'kg' => [
            'kg' => 1,          // Kilograms to kilograms
            'lb' => 0.453592,   // Pounds to kilograms
            'oz' => 0.0283495,  // Ounces to kilograms
            'g'  => 0.001,      // Grams to kilograms
        ]
    ];

    return isset( $conversions[$baseUnit] ) ? $conversions[$baseUnit] : $conversions['g'];
}

function easycommerce_time_units() {
	return array(
		'second' => __( 'Second', 'easycommerce' ),
		'minute' => __( 'Minute', 'easycommerce' ),
		'hour'   => __( 'Hour', 'easycommerce' ),
		'day'    => __( 'Day', 'easycommerce' ),
		'week'   => __( 'Week', 'easycommerce' ),
		'month'  => __( 'Month', 'easycommerce' ),
		'year'   => __( 'Year', 'easycommerce' ),
	);
}

function easycommerce_get_field_factory( $type ) {

	if ( $type == 'switch' ) {
		$type = 'switcher';
	} elseif ( $type == 'wysiwyg' ) {
		$type = 'WYSIWYG';
	}

	return '\\EasyCommerce\\Helpers\\Field\\' . ucfirst( $type );
}

function easycommerce_product_post_type() {
	return apply_filters( 'easycommerce_product_post_type', 'product' );
}

function easycommerce_get_cart() {
	$cart = new Cart();
	return $cart;
}

function easycommerce_get_cart_hash() {
	if ( isset( $_GET['cart'] ) ) {
		return sanitize_text_field( $_GET['cart'] );
	} elseif ( is_user_logged_in() ) {
		return get_user_meta( get_current_user_id(), '_easycommerce_cart_hash', true );
	} else {
		return isset( $_COOKIE['easycommerce_cart_hash'] ) ? sanitize_text_field( $_COOKIE['easycommerce_cart_hash'] ) : null;
	}
}

function easycommerce_add_payment_method_icons( $methods ) {
	// Add icons to payment methods
	foreach ( $methods as $key => $method ) {
		if ( empty( $method['icon'] ) ) {
			// Check for custom uploaded logo first
			$logo_option_key = $key . '_logo';
			$icon_id = Utility::get_option( 'payment', $key, $logo_option_key, '' );
			$icon_url = '';

			if ( wp_attachment_is_image( $icon_id ) ) {
				$icon_url = wp_get_attachment_url( $icon_id );
			}

			// If no custom logo, use default icon
			if ( empty( $icon_url ) ) {
				$icon_file = str_replace( '_', '-', $key ) . '.svg';
				$icon_path = EASYCOMMERCE_PLUGIN_DIR . 'assets/payment/img/' . $icon_file;
				if ( file_exists( $icon_path ) ) {
					$icon_url = EASYCOMMERCE_ASSETS_URL . 'payment/img/' . $icon_file;
				}
			}

			if ( ! empty( $icon_url ) ) {
				$methods[ $key ]['icon'] = $icon_url;
			}
		}
	}

	return $methods;
}

function easycommerce_payment_methods() {
	$methods = apply_filters( 'easycommerce_payment_methods', array() );
	return easycommerce_add_payment_method_icons( $methods );
}

/**
 * @return array of payment method IDs
 */
function easycommerce_active_payment_methods() {
	$methods = Utility::get_option( 'payment', 'methods', 'active_methods', array() );
	return (array) $methods;
}

function easycommerce_payment_method_class( $method ) {
	$payment_methods = easycommerce_payment_methods();

	if ( ! isset( $payment_methods[ $method ] ) || ! class_exists( $payment_methods[ $method ]['class'] ) ) {
		return false;
	}

	return $payment_method = new $payment_methods[ $method ]['class']();
}

function easycommerce_cart_redirect() {
	return apply_filters( 'easycommerce_cart_redirect', easycommerce_checkout_page() );
}

function easycommerce_order_redirect( $order_id ) {
	$dashboard_page = easycommerce_dashboard_page( true );
	$redirect_page  = isset( $dashboard_page ) ? $dashboard_page : easycommerce_home_url();
	$redirect_url   = "{$redirect_page}/#orders/{$order_id}";

	return apply_filters( 'easycommerce_order_redirect', $redirect_url, $order_id );
}

function easycommerce_date_ranges() {
	return apply_filters(
		'easycommerce_date_ranges',
		array(
			'today'      => __( 'Today', 'easycommerce' ),
			'yesterday'  => __( 'Yesterday', 'easycommerce' ),
			'this-week'  => __( 'This Week', 'easycommerce' ),
			'last-week'  => __( 'Last Week', 'easycommerce' ),
			'last-7'     => __( 'Last 7 Days', 'easycommerce' ),
			'this-month' => __( 'This Month', 'easycommerce' ),
			'last-month' => __( 'Last Month', 'easycommerce' ),
			'last-30'    => __( 'Last 30 Days', 'easycommerce' ),
			'this-year'  => __( 'This Year', 'easycommerce' ),
			'last-year'  => __( 'Last Year', 'easycommerce' ),
		)
	);
}

// get_range_dates
function easycommerce_get_range_dates( $range, $custom_from = null, $custom_to = null ) {
    $today = date('Y-m-d');

    switch ( $range ) {
        case 'today':
            return ['from' => "$today 00:00:00", 'to' => "$today 23:59:59"];

        case 'yesterday':
            $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
            return ['from' => "$yesterday 00:00:00", 'to' => "$yesterday 23:59:59"];

        case 'this-week':
            $start = date( 'Y-m-d', strtotime( 'monday this week' ) );
            $end   = date( 'Y-m-d', strtotime( 'sunday this week' ) );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-week':
            $start = date( 'Y-m-d', strtotime( 'monday last week' ) );
            $end   = date( 'Y-m-d', strtotime( 'sunday last week' ) );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-7':
            $start = date('Y-m-d', strtotime( '-6 days' ) );
            return ['from' => "$start 00:00:00", 'to' => "$today 23:59:59"];

        case 'this-month':
            $start = date( 'Y-m-01' );
            $end   = date( 'Y-m-t' );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-month':
            $start = date( 'Y-m-01', strtotime( 'first day of last month' ) );
            $end   = date( 'Y-m-t', strtotime( 'last day of last month' ) );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-30':
            $start = date( 'Y-m-d', strtotime( '-29 days' ) );
            return ['from' => "$start 00:00:00", 'to' => "$today 23:59:59"];

        case 'this-year':
            $start = date( 'Y-01-01' );
            $end   = date( 'Y-12-31' );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-year':
            $year  = date( 'Y ') - 1;
            $start = "$year-01-01";
            $end   = "$year-12-31";
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'custom':
            if ( $custom_from && $custom_to ) {
                return [
                    'from' => date('Y-m-d 00:00:00', strtotime( $custom_from ) ),
                    'to'   => date('Y-m-d 23:59:59', strtotime( $custom_to ) )
                ];
            }
            return null;

        default:
            return null;
    }
}

/**
 * The cache wrapper
 */
function easycommerce_cache() {
	$cacher = new class() {
		use Cache;
	};

	return $cacher;
}

/**
 * Get an associative list of countries
 *
 * @return array [ 'BD' => 'Bangladesh', ... ]
 */
function easycommerce_countries() {
	
	$cacher = easycommerce_cache();

	if ( false === ( $countries = $cacher->get_cache( 'countries' ) ) ) {
		$countries = array_column( Location::get_countries(), 'name', 'iso2' );

		$cacher->set_cache( 'countries', $countries, MONTH_IN_SECONDS );
	}

	return $countries;
}

/**
 * Get an associative list of currencies
 *
 * @return []
 */
function easycommerce_currencies( $label = 'currency_name' ) {

	$cacher = easycommerce_cache();

	if ( false == ( $currencies = $cacher->get_cache( "currencies-{$label}" ) ) ) {
		$currencies = wp_list_pluck( Location::get_currencies(), $label, 'currency' );

		$cacher->set_cache( "currencies-{$label}", $currencies, MONTH_IN_SECONDS );
	}

	return $currencies;
}

function easycommerce_currency_format_options() {
	return array(
		'us'        => '$12,345.67',        // US, Canada
		'us_after'  => '12,345.67$',        // Rare, but seen in Quebec
		'eu'        => '12.345,67 $',       // Germany, France, Spain
		'eu_before' => '$12.345,67',        // Alternative EU style
		'ch'        => 'USD 12’345.67',     // Switzerland
		'iso'       => 'USD 12,345.67',     // ISO format
		'iso_after' => '12,345.67 USD',     // Alternate ISO
		'plain'     => '12,345.67',         // No symbol/code
	);
}

function easycommerce_menus() {
	$licensed 	= apply_filters( 'easycommerce-pro_licensed', false );
	$pro_active = apply_filters( 'easycommerce-pro_activated', false );
	$pro_menu = ( $licensed || $pro_active ) ? [
		'page_title' 	=> __( 'EasyCommerce', 'easycommerce' ),
		'menu_title' 	=> __( 'Pro', 'easycommerce' ),
		'slug' 			=> 'easycommerce#/pro',
		'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro.svg',
		'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro-hover.svg',
	] : [
		'page_title' 	=> __( 'EasyCommerce', 'easycommerce' ),
		'menu_title' 	=> __( 'Get Pro', 'easycommerce' ),
		'slug' 			=> 'easycommerce#/get-pro',
		'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro.svg',
		'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro-hover.svg',
	];

	return apply_filters( 'easycommerce_menus', [
		[
			'title' 		=> __( 'EasyCommerce', 'easycommerce' ),
			'menu_title' 	=> __( 'EasyCommerce', 'easycommerce' ),
			'capability' 	=> 'manage_options',
			'slug' 			=> 'easycommerce',
			'callback'		=> function() {
				printf(
					'<div class="wrap">
						<div id="easycommerce_render">%1$s</div>
					</div>',
					esc_html__( 'Loading..', 'easycommerce' )
				);
			},
			'icon' 			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/logo/easycommerce.png',
			'position' 		=> 2,
			'submenus' => [
				[
					'page_title' 	=> __( 'Dashboard', 'easycommerce' ),
					'menu_title' 	=> __( 'Dashboard', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/dashboard.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/dashboard-hover.svg',
				],
				[
					'page_title' 	=> __( 'Products', 'easycommerce' ),
					'menu_title' 	=> __( 'Products', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/products',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/products.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/products-hover.svg',

					'submenus' => [
						[
							'page_title' 	=> __( 'All Products', 'easycommerce' ),
							'menu_title' 	=> __( 'All Products', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/products',
						],
						[
							'page_title' 	=> __( 'Add Product', 'easycommerce' ),
							'menu_title' 	=> __( 'Add Product', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/products/add',
						],
						[
							'page_title' 	=> __( 'Attributes', 'easycommerce' ),
							'menu_title' 	=> __( 'Attributes', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/attributes',
						],
						[
							'page_title' 	=> __( 'Categories', 'easycommerce' ),
							'menu_title' 	=> __( 'Categories', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/categories',
						],
						[
							'page_title' 	=> __( 'Tags', 'easycommerce' ),
							'menu_title' 	=> __( 'Tags', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/tags',
						],
						[
							'page_title' 	=> __( 'Brands', 'easycommerce' ),
							'menu_title' 	=> __( 'Brands', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/brands',
						],
					]
				],
				[
					'page_title' 	=> __( 'Orders', 'easycommerce' ),
					'menu_title' 	=> __( 'Orders', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/orders',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/orders.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/orders-hover.svg',
				],
				[
					'page_title' 	=> __( 'Refunds', 'easycommerce' ),
					'menu_title' 	=> __( 'Refunds', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/refunds',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/refunds.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/refunds-hover.svg',
				],
				[
					'page_title' 	=> __( 'Abandoned Carts', 'easycommerce' ),
					'menu_title' 	=> __( 'Abandoned Carts', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/abandoned-cart',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/abandoned-cart.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/abandoned-cart-hover.svg',
				],
				[
					'page_title' 	=> __( 'Transactions', 'easycommerce' ),
					'menu_title' 	=> __( 'Transactions', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/transactions',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/transactions.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/transactions-hover.svg',
				],
				[
					'page_title' 	=> __( 'Customers', 'easycommerce' ),
					'menu_title' 	=> __( 'Customers', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/customers',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/customers.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/customers-hover.svg',
				],
				[
					'page_title' 	=> __( 'Reviews', 'easycommerce' ),
					'menu_title' 	=> __( 'Reviews', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/reviews',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reviews.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reviews-hover.svg',
				],
				[
					'page_title' 	=> __( 'Coupons', 'easycommerce' ),
					'menu_title' 	=> __( 'Coupons', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/coupons',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/coupons.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/coupons-hover.svg',
				],
				[
					'page_title' 	=> __( 'Reports', 'easycommerce' ),
					'menu_title' 	=> __( 'Reports', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/reports',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reports.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reports-hover.svg',
					'submenus' => [
						[
							'page_title' 	=> __( 'Overview', 'easycommerce' ),
							'menu_title' 	=> __( 'Overview', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports',
						],
						[
							'page_title' 	=> __( 'Orders', 'easycommerce' ),
							'menu_title' 	=> __( 'Orders', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/orders',
						],
						[
							'page_title' 	=> __( 'Revenues', 'easycommerce' ),
							'menu_title' 	=> __( 'Revenues', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/revenues',
						],
						[
							'page_title' 	=> __( 'Products', 'easycommerce' ),
							'menu_title' 	=> __( 'Products', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/products',
						],
						[
							'page_title' 	=> __( 'Customers', 'easycommerce' ),
							'menu_title' 	=> __( 'Customers', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/customers',
						],
					]
				],
				[
					'page_title' 	=> __( 'Settings', 'easycommerce' ),
					'menu_title' 	=> __( 'Settings', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce-settings',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/settings.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/settings-hover.svg',
					'callback' 		=> fn() => do_action( 'easycommerce-settings' )
				],
				[
					'page_title' 	=> __( 'Addons', 'easycommerce' ),
					'menu_title' 	=> __( 'Addons', 'easycommerce' ),
					'slug' 			=> 'easycommerce#/addons',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/addons.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/addons-hover.svg',
				],
				[
					'page_title' 	=> __( 'Help & Support', 'easycommerce' ),
					'menu_title' 	=> __( 'Help & Support', 'easycommerce' ),
					'slug' 			=> 'easycommerce#/help',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/help.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/help-hover.svg',
				
				],
				$pro_menu,
			]
		],
		[
			'title' 		=> __( 'Wizard', 'easycommerce' ),
			'menu_title' 	=> __( 'Wizard', 'easycommerce' ),
			'capability' 	=> 'manage_options',
			'slug' 			=> 'easycommerce-wizard',
			'callback'		=> function() {
				printf(
					'<div class="wrap">
						<div id="easycommerce_wizard_render">%1$s</div>
					</div>',
					esc_html__( 'Loading..', 'easycommerce' )
				);
			},
			'icon' 			=> 'dashicons-cart',
			'position' 		=> 2,
			'submenus' => [
				[
					'page_title' 	=> __( 'Wizard', 'easycommerce' ),
					'menu_title' 	=> __( 'Wizard', 'easycommerce' ),
					'slug' 			=> 'easycommerce-wizard',
				]
			]
		]
	]);
}

/**
 * @see app/Config/settings.php
 */
function easycommerce_settings_menus() {
	include EASYCOMMERCE_PLUGIN_DIR . 'app/Config/settings.php';

	return $easycommerce_settings_menus;
}

function easycommerce_render_sidebar() {
	$sidebar	= '';
	$menus		= easycommerce_menus();
	$user_icon	= EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/header-default-user-icon.png';

	foreach ( $menus as $menu ) {

		if ( $menu['title'] !== 'EasyCommerce' ) continue;

		$sidebar .= '<div class="py-2 px-4 w-[240px] bg-white items-center gap-4 h-full">';
		$sidebar .= '<ul class="menu-title">';

		if ( ! empty( $menu['submenus'] ) ) {
			foreach ( $menu['submenus'] as $submenu ) {
				$sidebar .= easycommerce_render_menu_item( $submenu );
			}
		}

		$sidebar .= '</ul>';

		$sidebar .= '
			<div id="easycommerce-connect-box" class="flex flex-col items-center gap-4 bg-ec-table-stock p-8 rounded-xl mt-[70px] mb-[30px]"></div>
		</div>';

	}

	return $sidebar;
}

function easycommerce_render_menu_item( $item ) {
	$has_submenus = ! empty( $item['submenus'] );
	$is_settings  = $item['slug'] === 'easycommerce-settings';
	$menu_url     = admin_url( 'admin.php?page=' . $item['slug'] );

	$output = '<li>';
	$output .= '<a href="' . esc_url( $menu_url ) . '" class="hover:bg-[var(--color-ec-active)] hover:text-inherit hover:rounded-[8px] flex items-center text-sm p-3 my-2 gap-2 focus:ring-0 ' .
				( $has_submenus ? 'submenu-toggle' : '' ) .
				( $is_settings ? ' easycommerce-active-menu text-ec-primary' : 'text-ec-title' ) .
			   '">';

	if ( ! empty( $item['icon'] ) ) {
		$icon_url = ( $is_settings && ! empty( $item['hover_icon'] ) ) ? $item['hover_icon'] : $item['icon'];
		$output .= '<img src="' . esc_url( $icon_url ) . '" alt="" class="menu-icon" style="width: 20px; height: 20px;" />';
	}

	$output .= esc_html( $item['menu_title'] );

	if ( $has_submenus ) {
		$arrow = EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/up.png';
		$output .= '<img src="' . esc_url( $arrow ) . '" class="ml-auto arrow-icon transition-transform duration-300 rotate-180" style="width: 11px;" />';
	}

	$output .= '</a>';

	if ( $has_submenus ) {
		$output .= '<ul class="submenu-items border-l border-[#ECE6FF] ml-[30px] pl-3" style="display: none;">';
		foreach ( $item['submenus'] as $submenu ) {
			$output .= easycommerce_render_menu_item( $submenu );
		}
		$output .= '</ul>';
	}

	$output .= '</li>';

	return $output;
}

/**
 * @see app/Config/order-fields.php
 */
function easycommerce_checkout_fields( $section = '' ) {

	global $easycommerce_checkout_fields;

	if ( empty( $easycommerce_checkout_fields ) ) {
		include_once EASYCOMMERCE_PLUGIN_DIR . 'app/Config/checkout-fields.php';
	}

	if ( $section != '' && array_key_exists( $section, $easycommerce_checkout_fields ) ) {
		return $easycommerce_checkout_fields[ $section ];
	}

	return $easycommerce_checkout_fields;
}

function easycommerce_custom_checkout_fields( $sections = '' ) {
	global $easycommerce_custom_checkout_fields;

	if ( empty( $easycommerce_custom_checkout_fields ) ) {
		$easycommerce_custom_checkout_fields = get_option( 'easycommerce_checkout_editor_billing_fields', array() );
	}
	if ( $sections != '' && is_array( $easycommerce_custom_checkout_fields ) && array_key_exists( $sections, $easycommerce_custom_checkout_fields ) ) {
		return $easycommerce_custom_checkout_fields[ $sections ];
	}

	return $easycommerce_custom_checkout_fields;
}

function easycommerce_get_file_type( $filename ) {
	$icon_map = array(
		'pdf'  => 'pdf',
		'doc'  => 'word',
		'docx' => 'word',
		'xls'  => 'excel',
		'xlsx' => 'excel',
		'jpg'  => 'image',
		'jpeg' => 'image',
		'png'  => 'image',
		'gif'  => 'image',
		'zip'  => 'archive',
		'rar'  => 'archive',
		'txt'  => 'alt',
		'mp3'  => 'audio',
		'mp4'  => 'video',
		'bin'  => 'code',
		'exe'  => 'code',
	);

	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	return isset( $icon_map[ $ext ] ) ? $icon_map[ $ext ] : 'file';
}

function easycommerce_format_size( $bytes ) {
	if ( $bytes > -1024 && $bytes < 1024 ) {
		return $bytes . ' B';
	}

	$suffixes = 'KMGTPE';
	$index    = 0;

	while ( $bytes <= -999950 || $bytes >= 999950 ) {
		$bytes /= 1024;
		++$index;
	}

	return sprintf( '%.1f %sB', $bytes / 1024.0, $suffixes[ $index ] );
}

function easycommerce_currency() {
	$currency = Utility::get_option( 'payment', 'pricing', 'currency', 'USD' );

	return apply_filters( 'easycommerce_currency', $currency );
}

function easycommerce_currency_format() {
	return Utility::get_option( 'payment', 'pricing', 'format', 'us' );
}

function easycommerce_currency_symbol() {
	$currency      = easycommerce_currency();
	$currency_list = easycommerce_currencies( 'currency_symbol' );
	$symbol        = $currency_list[ $currency ] ?? '$';

	return apply_filters( 'easycommerce_currency_symbol', $symbol, $currency );
}

/**
 * Formats price
 */
function easycommerce_price( $price ) {
	$symbol = easycommerce_currency_symbol();
	$code   = easycommerce_currency();
	$format = easycommerce_currency_format();


	// choose separators
	if ( in_array( $format, array( 'eu', 'eu_before' ), true ) ) {
		$ts = '.';
		$ds = ',';
	} elseif ( $format === 'ch' ) {
		$ts = "'";
		$ds = '.';
	} else {
		$ts = ',';
		$ds = '.';
	}

	$amount = number_format( $price, 2, $ds, $ts );

	switch ( $format ) {
		case 'us_after':
			return "{$amount}{$symbol}";
		case 'eu_before':
			return "{$symbol}{$amount}";
		case 'eu':
			return "{$amount} {$symbol}";
		case 'iso':
			return "{$code} {$amount}";
		case 'iso_after':
			return "{$amount} {$code}";
		case 'ch':
			return "{$code}{$amount}";
		case 'plain':
			return $amount;
		default: // covers 'us', 'uk', 'ch' (symbol before), etc.
			return "{$symbol}{$amount}";
	}
}

function easycommerce_secure_download( $media_id ) {
	return add_query_arg(
		array(
			'action'   => 'easycommerce-download',
			'media_id' => $media_id,
		),
		admin_url()
	);
}

function easycommerce_get_business_types() {
	return apply_filters(
		'easycommerce_business_types',
		array(
			''               => __( 'Select Business Type', 'easycommerce' ),
			'art-antiques'   => __( 'Art and Antiques', 'easycommerce' ),
			'clothing'       => __( 'Clothing & Apparel', 'easycommerce' ),
			'beauty'         => __( 'Beauty & Cosmetics', 'easycommerce' ),
			'electronics'    => __( 'Electronics & Gadgets', 'easycommerce' ),
			'home-kitchen'   => __( 'Home & Kitchen Goods', 'easycommerce' ),
			'pet-supplies'   => __( 'Pet Supplies', 'easycommerce' ),
			'fitness'        => __( 'Sporting Goods & Fitness Equipment', 'easycommerce' ),
			'software'       => __( 'Software & SaaS', 'easycommerce' ),
			'ebooks'         => __( 'eBooks & Online Courses', 'easycommerce' ),
			'digital-art'    => __( 'Digital Art & Graphics', 'easycommerce' ),
			'themes-plugins' => __( 'Website Themes & Plugins', 'easycommerce' ),
			'consulting'     => __( 'Consulting & Coaching Services', 'easycommerce' ),
			'subscriptions'  => __( 'Subscription Boxes', 'easycommerce' ),
			'gourmet-foods'  => __( 'Gourmet & Specialty Foods', 'easycommerce' ),
			'coffee-tea'     => __( 'Coffee & Tea Products', 'easycommerce' ),
			'automotive'     => __( 'Automotive Parts & Accessories', 'easycommerce' ),
		)
	);
}

function easycommerce_get_product( $product_id ) {
	$product = new Product( $product_id );

	return $product;
}

function easycommerce_order_placeholders( $order_id ) {

	$order         = new Order( $order_id );
	$product_items = $order->get_items();
	$customer      = new Customer( $order->get_customer_id() );

	// Start table HTML
	$product_table = '<table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif;">
	    <thead>
	        <tr>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Product</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Variation</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Qty</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Rate</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Subtotal</th>
	        </tr>
	    </thead>
	    <tbody>';

	// Start list HTML
	$product_list = '<ul>';

	// Loop through order items
	foreach ( $order->get_items() as $item ) {
		$product   = new Product( $item->product_id );
		$variation = new Product_Variation( $item->variation_id );

		// Append table row
		$product_table .= '<tr>
	        <td style="border:1px solid #ddd; padding:8px;">' . $product->get_title() . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . $variation->get_name( false ) . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . $item->quantity . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item->rate ) . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item->price ) . '</td>
	    </tr>';

		// Append list item
		$product_list .= '<li>' . $product->get_title() . '</li>';
	}

	// Close table and list
	$product_table .= '</tbody></table>';
	$product_list  .= '</ul>';

	$placeholders = array(

		// Order details
		'##order_id##'                     => $order_id,

		// Customer basic details
		'##customer_name##'                => $customer->get_name(),
		'##customer_email##'               => $customer->get_email(),
		'##customer_phone##'               => $customer->get_phone(),

		// Billing details
		'##billing_first_name##'           => $customer->get_first_name('billing'),
		'##billing_last_name##'            => $customer->get_last_name('billing'),
		'##billing_email##'                => $customer->get_email('billing'),
		'##billing_phone##'                => $customer->get_phone('billing'),
		'##billing_address_1##'            => $customer->get_address_1('billing'),
		'##billing_address_2##'            => $customer->get_address_2('billing'),
		'##billing_country##'              => $customer->get_country('billing'),
		'##billing_state##'                => $customer->get_state('billing'),
		'##billing_city##'                 => $customer->get_city('billing'),
		'##billing_postcode##'             => $customer->get_postcode('billing'),
		'##billing_address##'              => $customer->get_address('billing'),

		// Shipping details
		'##shipping_first_name##'          => $customer->get_first_name( 'shipping' ),
		'##shipping_last_name##'           => $customer->get_last_name( 'shipping' ),
		'##shipping_email##'               => $customer->get_email( 'shipping' ),
		'##shipping_phone##'               => $customer->get_phone( 'shipping' ),
		'##shipping_address_1##'           => $customer->get_address_1( 'shipping' ),
		'##shipping_address_2##'           => $customer->get_address_2( 'shipping' ),
		'##shipping_country##'             => $customer->get_country( 'shipping' ),
		'##shipping_state##'               => $customer->get_state( 'shipping' ),
		'##shipping_city##'                => $customer->get_city( 'shipping' ),
		'##shipping_postcode##'            => $customer->get_postcode( 'shipping' ),
		'##shipping_address##'             => $customer->get_address( 'shipping' ),

		// Customer order statistics
		'##customer_total_spent##'         => $customer->get_total_spent(),
		'##customer_total_order_count##'   => $customer->get_order_count(),
		'##customer_average_order_value##' => $customer->get_aov(),

		'##product_list##'                 => $product_list,
		'##product_table##'                => $product_table,
		'##number_of_items##'              => count( $order->get_items() ),
		'##order_total##'                  => easycommerce_price( $order->get_total() ),
		'##refunded_amount##'              => easycommerce_price( $order->get_total_refunded() ),
	);

	return apply_filters( 'easycommerce_order_placeholders', $placeholders, $order_id );
}

function easycommerce_cart_placeholders( $cart ) {

	// Start table HTML
	$product_table = '<table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif;">
	    <thead>
	        <tr>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Product</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Variation</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Qty</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Rate</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Subtotal</th>
	        </tr>
	    </thead>
	    <tbody>';

	// Start list HTML
	$product_list = '<ul>';

	// Loop through order items
	foreach ( $cart->get_items() as $product_id => $variations ) {
		foreach ( $variations as $variation_id => $item ) {
			$product   = new Product( $product_id );
			$variation = new Product_Variation( $variation_id );

			// Append table row
			$product_table .= '<tr>
		        <td style="border:1px solid #ddd; padding:8px;">' . $product->get_title() . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . $variation->get_name( false ) . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . $item['quantity'] . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item['rate'] ) . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item['price'] ) . '</td>
		    </tr>';

			// Append list item
			$product_list .= '<li>' . $product->get_title() . '</li>';
		}
	}

	// Close table and list
	$product_table .= '</tbody></table>';
	$product_list  .= '</ul>';

	$placeholders = array(
		// General cart details
		'##hash##'               => $cart->get_hash(),
		'##name##'               => $cart->get_customer_name(),
		'##customer_name##'      => $cart->get_customer_name(),
		'##email##'              => $cart->get_customer_email(),
		'##cart_link##'          => $cart->get_link(),
		'##cart_total##'         => easycommerce_price( $cart->get_amount( 'total' ) ),
		'##amount##'             => easycommerce_price( $cart->get_amount( 'total' ) ),
		'##number_of_items##'    => $cart->get_item_count(),
		'##order_status##'       => $cart->get_status(),
		'##random_coupon_code##' => (Utility::get_option( 'abandoned-cart', 'settings', 'random_coupon_discount_percentage', 0 ) > 0) ? easycommerce_generate_random_coupon_code() : '',

		// Billing details
		'##billing_phone##'      => $cart->get_phone(),
		'##billing_address_1##'  => $cart->get_address_1(),
		'##billing_address_2##'  => $cart->get_address_2(),
		'##billing_country##'    => $cart->get_country(),
		'##billing_state##'      => $cart->get_state(),
		'##billing_city##'       => $cart->get_city(),
		'##billing_postcode##'   => $cart->get_postcode(),

		// Shipping details
		'##shipping_phone##'     => $cart->get_phone( 'shipping' ),
		'##shipping_address_1##' => $cart->get_address_1( 'shipping' ),
		'##shipping_address_2##' => $cart->get_address_2( 'shipping' ),
		'##shipping_country##'   => $cart->get_country( 'shipping' ),
		'##shipping_state##'     => $cart->get_state( 'shipping' ),
		'##shipping_city##'      => $cart->get_city( 'shipping' ),
		'##shipping_postcode##'  => $cart->get_postcode( 'shipping' ),

		'##product_list##'       => $product_list,
		'##product_table##'      => $product_table,

	);

	return $placeholders;
}

function easycommerce_generate_random_coupon_code() {
	$offer = Utility::get_option( 'abandoned-cart', 'settings', 'random_coupon_discount_percentage', 0 );
	if ( $offer <= 0 ) {
		return false;
	}

	$coupon_model = new Coupon_Model();
	$random_code  = strtoupper( substr( str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ), 0, 8 ) );

	$coupon_id = $coupon_model->create(
		array(
			'name'   => $random_code,
			'code'   => $random_code,
			'type'   => 'percentage',
			'offer'  => $offer,
			'active' => 1,
		)
	);

	return $coupon_id ? $random_code : false;
}

function easycommerce_is_api_connected() {
	$api = get_option( 'easycommerce_api' );
	if ( ! empty( $api ) && ! empty( $api->email ) ) {
		return true;
	}

	if ( apply_filters( 'easycommerce-pro_licensed', false ) ) {
		return true;
	}

	return false;
}

/**
 * Returns true if the current page is a product page.
 *
 * @return bool
 */
function easycommerce_is_product(): bool {
	return is_singular( 'product' );
}

/**
 * Returns true if the current page is the shop page.
 *
 * @return bool
 */
function easycommerce_is_shop(): bool {
	return is_page( easycommerce_shop_page() );
}

/**
 * Returns true if the current page is the checkout page.
 *
 * @return bool
 */
function easycommerce_is_checkout(): bool {
	return is_page( easycommerce_checkout_page() ) || easycommerce_is_payment_page();
}

function easycommerce_is_payment_page(): bool {
	return is_page( easycommerce_payment_page() );
}

/**
 * Returns true if the current page is the account page.
 *
 * @return bool
 */
function easycommerce_is_dashboard(): bool {
	return is_page( easycommerce_dashboard_page() );
}

/**
 * Returns true if the current page is one of the EasyCommerce pages.
 *
 * @return bool
 */
function is_easycommerce_page(): bool {
	return easycommerce_is_product()
	|| easycommerce_is_shop()
	|| easycommerce_is_checkout()
	|| easycommerce_is_dashboard()
	|| easycommerce_is_payment_page();
}

/**
 * Import a remote image URL into WP Media Library.
 *
 * @param string $url     Remote image URL.
 * @param int    $post_id Optional post ID to attach to (0 = unattached).
 * @param string $desc    Optional attachment description.
 * @return array|WP_Error {
 *     On success: [
 *         'attachment_id' => int,
 *         'url'           => string,
 *         'file'          => string, // full path
 *         'mime'          => string,
 *         'width'         => int,
 *         'height'        => int,
 *         'sizes'         => array,
 *     ]
 *     On failure: WP_Error
 * }
 */
function easycommerce_import_image( $url, $post_id = 0, $desc = '' ) {
    if ( empty( $url ) ) {
        return new WP_Error( 'no_url', 'No URL provided.' );
    }

    // WP helpers
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // download temp file
    $tmp = download_url( $url );
    if ( is_wp_error( $tmp ) ) {
        return $tmp;
    }

    // derive filename
    $path = wp_parse_url( $url, PHP_URL_PATH );
    $filename = $path ? wp_basename( $path ) : '';
    $filename = sanitize_file_name( $filename );
    if ( ! $filename ) {
        $ext = 'jpg';
        $filename = 'easycommerce-' . time() . '.' . $ext;
    }

    $file_array = array(
        'name'     => $filename,
        'tmp_name' => $tmp,
    );

    // sideload into uploads and create attachment
    $attach_id = media_handle_sideload( $file_array, $post_id, $desc );

    // cleanup temp on error
    if ( is_wp_error( $attach_id ) ) {
        @unlink( $tmp );
        return $attach_id;
    }

    // gather details
    $attachment_url = wp_get_attachment_url( $attach_id );
    $file_path      = get_attached_file( $attach_id );
    $mime_type      = get_post_mime_type( $attach_id );
    $meta           = wp_get_attachment_metadata( $attach_id );

    $width  = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
    $height = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
    $sizes  = isset( $meta['sizes'] ) ? $meta['sizes'] : array();

    return array(
        'attachment_id' => (int) $attach_id,
        'url'           => $attachment_url,
        'file'          => $file_path,
        'mime'          => $mime_type,
        'width'         => $width,
        'height'        => $height,
        'sizes'         => $sizes,
    );
}

function easycommerce_import_encoded_image( $data, $post_id = 0, $desc = '' ) {
	if ( empty( $data ) || ! preg_match( '/^data:image\/(\w+);base64,/', $data, $type ) ) {
		return new WP_Error( 'invalid_data', 'Invalid image data.' );
	}

	$data = substr( $data, strpos( $data, ',' ) + 1 );
	$data = base64_decode( $data );
	if ( $data === false ) {
		return new WP_Error( 'decode_error', 'Base64 decode failed.' );
	}

	// create temp file
	$tmp = tmpfile();
	if ( ! $tmp ) {
		return new WP_Error( 'temp_file_error', 'Could not create temp file.' );
	}

	fwrite( $tmp, $data );
	$meta = stream_get_meta_data( $tmp );
	$tmp_path = $meta['uri'];

	// derive filename
	$ext = strtolower( $type[1] );
	$filename = 'easycommerce-' . time() . '.' . $ext;

	$file_array = array(
		'name'     => sanitize_file_name( $filename ),
		'tmp_name' => $tmp_path,
	);

	// sideload into uploads and create attachment
	$attach_id = media_handle_sideload( $file_array, $post_id, $desc );

	// cleanup temp
	fclose( $tmp );

	if ( is_wp_error( $attach_id ) ) {
		return $attach_id;
	}

	// gather details
	$attachment_url = wp_get_attachment_url( $attach_id );
	$file_path      = get_attached_file( $attach_id );
	$mime_type      = get_post_mime_type( $attach_id );
	$meta           = wp_get_attachment_metadata( $attach_id );

	$width  = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
	$height = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
	$sizes  = isset( $meta['sizes'] ) ? $meta['sizes'] : array();

	return array(
		'attachment_id' => (int) $attach_id,
		'url'           => $attachment_url,
		'file'          => $file_path,
		'mime'          => $mime_type,
		'width'         => $width,
		'height'        => $height,
		'sizes'         => $sizes,
	);
}

function easycommerce_is_paid_addon_active() {
    return apply_filters( 'easycommerce_is_paid_addon_active', false );
}

/**
 * Single source of truth for the client's AI credit/plan state.
 *
 * One option (`easycommerce_ai`) holds everything: plan, monthly allowance,
 * usage, remaining balance, next reset date and the last hub-sync timestamp. It
 * replaces the legacy `easycommerce_ai_credits` + `easycommerce_ai_plan` options
 * and the status transient, migrating them once on first read.
 *
 * @return array { plan, plan_name, limit, used, remaining, next_refresh, synced_at }
 */
function easycommerce_ai_data() {

	$defaults = array(
		'plan'         => 'free',
		'plan_name'    => '',
		'limit'        => 0,
		'used'         => 0,
		'remaining'    => 100,
		'next_refresh' => '',
		'synced_at'    => 0,
	);

	$data = get_option( 'easycommerce_ai' );

	// One-time migration from the legacy split keys.
	if ( ! is_array( $data ) ) {
		$legacy_credits = get_option( 'easycommerce_ai_credits' );

		$data = array(
			'remaining' => is_numeric( $legacy_credits ) ? (int) $legacy_credits : $defaults['remaining'],
			'plan'      => get_option( 'easycommerce_ai_plan', $defaults['plan'] ),
		);

		update_option( 'easycommerce_ai', wp_parse_args( $data, $defaults ) );
		delete_option( 'easycommerce_ai_credits' );
		delete_option( 'easycommerce_ai_plan' );
		delete_transient( 'easycommerce_ai_status' );
	}

	return wp_parse_args( is_array( $data ) ? $data : array(), $defaults );
}

/**
 * Merge a partial update into the single AI state option.
 *
 * @param array $patch Keys to overwrite.
 * @return array The full, updated state.
 */
function easycommerce_ai_update( array $patch ) {
	$data = array_merge( easycommerce_ai_data(), $patch );
	update_option( 'easycommerce_ai', $data );
	return $data;
}

function easycommerce_get_ai_credits() {
    $credits = easycommerce_ai_data()['remaining'];
    if( ! is_numeric( $credits ) ) {
    	$credits = 100;
    }

    return apply_filters( 'easycommerce_ai_credits', $credits );
}

function easycommerce_deduct_ai_credits( $deduct = 1, $credits = null ) {

	if( is_null( $credits ) ) {
		$credits = easycommerce_get_ai_credits();
	}

	easycommerce_ai_update( array( 'remaining' => $credits - $deduct ) );
}

/**
 * Fetch the authoritative monthly AI credit status from the hub.
 *
 * Returns the plan, monthly allowance, usage, remaining balance and the next
 * reset date from the single `easycommerce_ai` option. Re-syncs from the hub
 * when the cached state is older than 10 minutes (tracked via `synced_at`).
 * Falls back to the locally-stored state when the hub is unreachable or no
 * account is connected.
 *
 * @param bool $force Skip the freshness check and re-fetch.
 * @return array See easycommerce_ai_data().
 */
function easycommerce_ai_status( $force = false ) {

	$data = easycommerce_ai_data();

	// Serve the stored state while it is still fresh.
	if ( ! $force && $data['synced_at'] && ( time() - (int) $data['synced_at'] ) < 10 * MINUTE_IN_SECONDS ) {
		return $data;
	}

	$api = get_option( 'easycommerce_api' );
	if ( empty( $api->email ) ) {
		return $data;
	}

	$response = wp_remote_get(
		easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/ai/status' ),
		array(
			'timeout' => 15,
			'headers' => array( 'email' => $api->email ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $data;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ) );
	if ( empty( $body->success ) || empty( $body->data ) ) {
		return $data;
	}

	return easycommerce_ai_update(
		array(
			'plan'         => $body->data->plan ?? 'free',
			'plan_name'    => $body->data->plan_name ?? '',
			'limit'        => (int) ( $body->data->limit ?? 0 ),
			'used'         => (int) ( $body->data->used ?? 0 ),
			'remaining'    => (int) ( $body->data->remaining ?? 0 ),
			'next_refresh' => $body->data->next_refresh ?? '',
			'synced_at'    => time(),
		)
	);
}

function easycommerce_get_conflicting_plugins() {
    return apply_filters( 'easycommerce-conflicting_plugins', array(
        'easycommerce-stripe/easycommerce-stripe.php',
        'easycommerce-paypal/easycommerce-paypal.php',
        'easycommerce-square/easycommerce-square.php',
        'easycommerce-mollie/easycommerce-mollie.php',
        'easycommerce-stripe/easycommerce-stripe.php',
        'easycommerce-braintree/easycommerce-braintree.php',
        'easycommerce-cash-on-delivery/easycommerce-cash-on-delivery.php',
        'easycommerce-csv-importer/easycommerce-csv-importer.php',
    ) );
}

function easycommerce_detect_external_plugins_for_migration() {
    $active_plugins = array(
        'woocommerce/woocommerce.php'	=> 'WooCommerce',
	);

    $detected = null;

    foreach ( $active_plugins as $main_file => $name ) {
        if ( is_plugin_active( $main_file ) ) {
            $detected = $name;
            break;
        }
    }

    return $detected;
}

function easycommerce_is_compatible_theme_active() {
	$current_theme 			= wp_get_theme();
	$current_theme_slug 	= $current_theme->get_template();

	$compatible_theme_list 	= easycommerce_compatible_themes();

	if ( in_array( $current_theme_slug, $compatible_theme_list ) ) {
		return true;
	}

	return false;
}

function easycommerce_compatible_themes() {
	$themes = array_map(
		fn( $file ) => pathinfo( $file, PATHINFO_FILENAME ),
		glob( EASYCOMMERCE_PLUGIN_DIR . '/assets/public/css/themes/' . '*.css' )
	);

	return apply_filters( 'easycommerce_compatible_themes', $themes );
}

function easycommerce_get_user_country() {
	$cacher = easycommerce_cache();

    $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

    if ( $ip === '127.0.0.1' || $ip === '::1' ) {
        $ip = '8.8.8.8'; // Use Google DNS for testing, or set default country
    }

    if ( $cached = $cacher->get_cache( 'user_country_' . md5( $ip ) ) ) {
        return $cached;
    }

    $country = null;

    // Get country from IP API
    $response = wp_remote_get( "https://ipapi.co/{$ip}/country_code/", [
        'timeout' => 10
    ] );

    if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
        $country = trim( wp_remote_retrieve_body( $response ) );
    }

    if ( ! $country || strlen( $country ) !== 2 ) {
        $country = 'US'; // Default
    }

    $cacher->set_cache( 'user_country_' . md5( $ip ), $country, DAY_IN_SECONDS );

    return $country;
}

function easycommerce_business_address_1() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['address_1'] ?? '';
}
function easycommerce_business_address_2() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['address_2'] ?? '';
}
function easycommerce_business_state() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['state'] ?? '';
}
function easycommerce_business_city() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['city'] ?? '';
}
function easycommerce_business_postcode() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['postcode'] ?? '';
}
function easycommerce_business_country() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['country'] ?? '';
}
function easycommerce_business_full_address(): string {
	$address = array(
		easycommerce_business_address_1(),
		easycommerce_business_address_2(),
		easycommerce_business_state(),
		easycommerce_business_city(),
		easycommerce_business_country(),
		easycommerce_business_postcode(),
	);

	return implode( ', ', $address );
}

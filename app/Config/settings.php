<?php
defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

// Fetch available pages to populate page-related dropdowns in the settings.
$pages            = Utility::get_posts( array( 'post_type' => 'page' ) );
$store_name       = Utility::get_option( 'general', 'business', 'store_name' );
$store_logo       = Utility::get_option( 'general', 'business', 'logo' );
$full_address     = Utility::get_option( 'general', 'business', 'full_address' );

// AI service connection state - drives the AI tab set (see the tail of this file):
// disconnected shows a "Connectivity" tab, connected shows "Usage".
$ec_api           = get_option( 'easycommerce_api' );
$ec_ai_connected  = ! empty( $ec_api->email );




$payment_methods  = easycommerce_payment_methods();
$active_methods   = easycommerce_active_payment_methods();
$payment_options  = array_reduce(
    $payment_methods,
    function ( $options, $method ) use ( $active_methods ) {

        $label = esc_html( $method['title'] );

        // Show Settings only for active methods
        if ( in_array( $method['id'], $active_methods, true ) ) {

            $settings_url = esc_url(
				add_query_arg(
					array(
						'page'    => 'easycommerce-settings',
						'menu'    => 'payment',
						'submenu' => $method['id'],
					),
					admin_url( 'admin.php' )
				)
			);

            $label .= sprintf(
                ' <a href="%s" class="ec-payment-settings-link">%s</a>',
                esc_url( $settings_url ),
                __( '&raquo; Settings', 'easycommerce' )
            );
        }

        $options[ $method['id'] ] = $label;

        return $options;
    },
    array()
);

$easycommerce_settings_menus = apply_filters(
	'easycommerce_settings_menus',
	array(

		// General Settings
		'general'        => array(
			'label'      => __( 'General', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/general.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/general-hover.png',
			'reload'   	 => true,
			'submenus'   => array(
				// Branding Submenu for customizing store identity
				'business' => array(
					'label'    => __( 'Business', 'easycommerce' ),
					'desc'     => __( 'Adjust business settings for a personalized store experience.', 'easycommerce' ),
					'sections' => array(
						array(
							'label'  => __( 'Identity', 'easycommerce' ),
							'desc'   => __( 'Customize your store’s name, logo, and appearance.', 'easycommerce' ),
							'fields' => array(

								// File upload for the store logo
								'logo'           => array(
									'id'          => 'logo',
									'type'        => 'image',
									'label'       => __( 'Store Logo', 'easycommerce' ),
									'description' => __( 'Upload your store’s logo. Recommended dimensions: 512x512 px.', 'easycommerce' ),
									'placeholder' => __( 'Choose logo file', 'easycommerce' ),
								),

								// Text field for the store name
								'store_name'     => array(
									'id'          => 'store_name',
									'type'        => 'text',
									'label'       => __( 'Business Name', 'easycommerce' ),
									'description' => __( 'The name of your store as displayed to customers.', 'easycommerce' ),
									'placeholder' => __( 'Enter Store Name', 'easycommerce' ),
								),

								// Business type: sports, software, clothing etc
								'business_email' => array(
									'id'          => 'business_email',
									'type'        => 'email',
									'label'       => __( 'Business Email', 'easycommerce' ),
									'description' => __( 'It\'ll be used for admin communications.', 'easycommerce' ),
									'default'     => get_option( 'admin_email' ),
								),

								// Business type: sports, software, clothing etc
								'business_type'  => array(
									'id'          => 'business_type',
									'type'        => 'select',
									'options'     => easycommerce_get_business_types(),
									'label'       => __( 'Business Type', 'easycommerce' ),
									'description' => __( 'The type of your business.', 'easycommerce' ),
									'placeholder' => __( 'Enter Business Type', 'easycommerce' ),
								),
							),
						),
						// Store address section with country and full address fields
						'address' => array(
							'label'  => __( 'Address', 'easycommerce' ),
							'desc'   => __( 'Details of your store’s physical location.(If have any)', 'easycommerce' ),
							'fields' => array(
								// Dropdown for selecting the store's country
								'country'      => array(
									'id'          => 'country',
									'type'        => 'select',
									'label'       => __( 'Country', 'easycommerce' ),
									'description' => __( 'The country where your store is based.', 'easycommerce' ),
									'options'     => easycommerce_countries(),
									'placeholder' => __( 'Select Country', 'easycommerce' ),
								),
								'address_1'    => array(
									'id'          => 'address_1',
									'type'        => 'text',
									'label'       => __( 'Address 1', 'easycommerce' ),
									'description' => __( 'Street address, P.O. box, or company name.', 'easycommerce' ),
									'placeholder' => __( 'Your address 1', 'easycommerce' ),
									'required'    => true,
								),
								'address_2'    => array(
									'id'          => 'address_2',
									'type'        => 'text',
									'label'       => __( 'Address 2', 'easycommerce' ),
									'description' => __( 'Apartment, suite, unit, building, or floor (optional).', 'easycommerce' ),
									'placeholder' => __( 'Your address 2', 'easycommerce' ),
									'required'    => false,
								),
								'state'        => array(
									'id'          => 'state',
									'type'        => 'select',
									'allow_input' => true,
									'label'       => __( 'State', 'easycommerce' ),
									'description' => __( 'Pick your state from the list, or type it in if it\'s not listed.', 'easycommerce' ),
									'placeholder' => __( 'Your State', 'easycommerce' ),
									'required'    => false,
								),
								'city'         => array(
									'id'          => 'city',
									'type'        => 'select',
									'allow_input' => true,
									'label'       => __( 'City', 'easycommerce' ),
									'description' => __( 'Pick your city from the list, or type it in if it\'s not listed.', 'easycommerce' ),
									'placeholder' => __( 'Your City', 'easycommerce' ),
									'required'    => true,
								),
								'postcode'     => array(
									'id'          => 'postcode',
									'type'        => 'text',
									'label'       => __( 'Postcode', 'easycommerce' ),
									'description' => __( 'The ZIP or postal code of your store\'s location.', 'easycommerce' ),
									'placeholder' => __( 'Write here', 'easycommerce' ),
									'required'    => false,
								),
							),
						),
					),
				),
				// Pages Submenu for configuring important pages
				'store'    => array(
					'label'    => __( 'Store', 'easycommerce' ),
					'desc'     => __( 'Select the pages used by EasyCommerce for key functionality.', 'easycommerce' ),
					'sections' => array(
						array(
							'label'  => __( 'Pages', 'easycommerce' ),
							'fields' => array(
								// Dropdown for selecting the Shop page
								'shop'      => array(
									'id'          => 'shop',
									'type'        => 'select',
									'label'       => __( 'Shop Page', 'easycommerce' ),
									'description' => __( 'The main page where products are displayed for customers to browse.', 'easycommerce' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Shop Page', 'easycommerce' ),
								),
								// Dropdown for selecting the Checkout page
								'checkout'  => array(
									'id'          => 'checkout',
									'type'        => 'select',
									'label'       => __( 'Checkout Page', 'easycommerce' ),
									/* Translators: %s is a shortcode that should be included in the page content. */
									'description' => sprintf( __( 'The page where customers can review their cart and complete their purchase. This page content must contain the %s shortcode.', 'easycommerce' ), '<code>[easycommerce-checkout]</code>' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Checkout Page', 'easycommerce' ),
								),
								// Dropdown for selecting the Customer Dashboard page
								'dashboard' => array(
									'id'          => 'dashboard',
									'type'        => 'select',
									'label'       => __( 'Customer Dashboard', 'easycommerce' ),
									/* Translators: %s is a shortcode that should be included in the page content. */
									'description' => sprintf( __( 'The account page where customers can view orders, update information, etc. This page content must contain the %s shortcode.', 'easycommerce' ), '<code>[easycommerce-dashboard]</code>' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Dashboard Page', 'easycommerce' ),
								),
								'payment' => array(
									'id'          => 'payment',
									'type'        => 'select',
									'label'       => __( 'Payment Page', 'easycommerce' ),
									/* Translators: %s is a shortcode that should be included in the page content. */
									'description' => sprintf( __( 'The page where customers can pay for pending orders. This page content must contain the %s shortcode.', 'easycommerce' ), '<code>[easycommerce-payment]</code>' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Payment Page', 'easycommerce' ),
								),
								'registration' => array(
									'id'          => 'registration',
									'type'        => 'select',
									'label'       => __( 'Customer Registration', 'easycommerce' ),
									/* Translators: %s is a shortcode that should be included in the page content. */
									'description' => sprintf( __( 'The page where customers can register and create an account. This page content must contain the %s shortcode.', 'easycommerce' ), '<code>[easycommerce-register]</code>' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Registration Page', 'easycommerce' ),
								),
								'reset-password' => array(
									'id'          => 'reset-password',
									'type'        => 'select',
									'label'       => __( 'Customer Reset Password', 'easycommerce' ),
									/* Translators: %s is a shortcode that should be included in the page content. */
									'description' => sprintf( __( 'The page where customers can reset their password. This page content must contain the %s shortcode.', 'easycommerce' ), '<code>[easycommerce-reset]</code>' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Reset Password Page', 'easycommerce' ),
								),
								'terms-of-service' => array(
									'id'          => 'terms-of-service',
									'type'        => 'select',
									'label'       => __( 'Terms of Service', 'easycommerce' ),
									/* Translators: %s is a shortcode that should be included in the page content. */
									'description' => sprintf( __( 'The page that contains the terms and conditions for using the site.', 'easycommerce' ), '' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Terms of Service Page', 'easycommerce' ),
								),
								'privacy-policy' => array(
									'id'          => 'privacy-policy',
									'type'        => 'select',
									'label'       => __( 'Privacy Policy', 'easycommerce' ),
									/* Translators: %s is a shortcode that should be included in the page content. */
									'description' => sprintf( __( 'The page that contains the privacy policy for using the site.', 'easycommerce' ), '' ),
									'options'     => $pages,
									'placeholder' => __( 'Select Privacy Policy Page', 'easycommerce' ),
								),
								'template-width' => array(
									'id'          => 'template-width',
									'type'        => 'number',
									'label'       => __( 'Template Width', 'easycommerce' ),
									'description' => __( 'Set the maximum width for the page template container.', 'easycommerce' ),
									'placeholder' => __( '1200', 'easycommerce' ),
									'default'     => 1200,
									'atts'		  => [
										'min'	=> 600,
										'max'	=> 1920,
										'step'	=> 1
									]
								),
								'stock-badge' => array(
									'id'          => 'stock-badge',
									'type'        => 'switch',
									'label'       => __( 'Enable Stock Badge', 'easycommerce' ),
									'description' => __( 'Display a stock availability badge on product images across the shop and single product pages.', 'easycommerce' ),
									'default'     => true,
								),
							),
						),
					),
				),
				'visibility' => array(
					'label'    => __( 'Visibility', 'easycommerce' ),
					'desc'     => __( 'Control your store visibility and access settings.', 'easycommerce' ),
					'sections' => array(
						array(
							'label'  => __( 'Store Visibility', 'easycommerce' ),
							'desc'   => __( 'Choose if your store is live or still in setup mode.', 'easycommerce' ),
							'fields' => array(
								'store_mode' => array(
									'id'          => 'store_mode',
									'type'        => 'radio',
									'label'       => __( 'Store Mode', 'easycommerce' ),
									'description' => __( 'Live mode makes your store public; test mode keeps it private for setup and testing.', 'easycommerce' ),
									'default'     => 'test',
									'options'     => array(
										'live' => __( 'Live', 'easycommerce' ),
										'test' => __( 'Test', 'easycommerce' ),
									),
								),
							),
						),
					),
				),
			),
		),
		// Payment Settings
		'payment'        => array(
			'label'      => __( 'Payment', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/payment.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/payment-hover.png',
			'reload'     => true,
			'submenus'   => array_merge(
				array(
					// Methods submenu for managing active payment methods
					'pricing' => array(
						'label'    => __( 'Pricing', 'easycommerce' ),
						'sections' => array(
							array(
								'fields' => array(

									// Dropdown for choosing the currency
									'currency' => array(
										'id'          => 'currency',
										'type'        => 'select',
										'label'       => __( 'Currency', 'easycommerce' ),
										'description' => __( 'Select the currency used for pricing and transactions.', 'easycommerce' ),
										'options'     => easycommerce_currencies(),
										'placeholder' => __( 'Select Currency', 'easycommerce' ),
										'default'     => 'USD',
									),
									// Pricing format
									'format'   => array(
										'id'          => 'format',
										'type'        => 'select',
										'label'       => __( 'Format', 'easycommerce' ),
										'description' => __( 'How should the prices show up?', 'easycommerce' ),
										'options'     => easycommerce_currency_format_options(),
										'default'     => 'us',
									),
								),
							),
						),
					),
					'methods' => array(
						'label'    => __( 'Methods', 'easycommerce' ),
						'reload'	=> true,
						'sections' => array(
							array(
								'fields' => array(

									// Multicheck for selecting active payment methods
									'active_methods' => array(
										'id'          => 'active_methods',
										'type'        => 'multicheck',
										'label'       => __( 'Enable Payment Methods', 'easycommerce' ),
										'description' => sprintf( __( 'Select the payment methods you want to enable for checkout. Find more options from the <a href="%1$s">Addons page</a>.', 'easycommerce' ), admin_url( 'admin.php?page=easycommerce#/addons' ) ),
										'options'     => $payment_options,
										'placeholder' => __( 'Choose payment methods', 'easycommerce' ),
										'class'       => 'payment-methods',
									),
								),
							),
						),
					),
				),
				array_reduce(
					$payment_methods,
					function ( $menu, $method ) use ($active_methods) {

						// Show Settings only for active methods
						if ( ! in_array( $method['id'], $active_methods, true ) ) {
							return $menu;
						}

						// skip if not array
						if ( empty( $method['settings'] ) ) {
							return $menu;
						}

						$settings = $method['settings'] ?? array();

						// wrap single section into numeric array
						if ( isset( $settings['label'], $settings['fields'] ) ) {
							$settings = array( $settings );
						}

						$menu[ $method['id'] ] = array(
							'label'    => $method['title'],
							'desc'     => $method['desc'] ?? '',
							'sections' => $settings,
						);

						return $menu;
					},
					array()
				),
				apply_filters( 'easycommerce_payment_settings', array() )
			),
		),
		'order'          => array(
			'label'      => __( 'Orders', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/orders.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/orders-hover.png',
			'submenus'   => array(
				'settings' => array(
					'label'    => __( 'settings', 'easycommerce' ),
					'desc'     => __( 'Select the order default statuses.', 'easycommerce' ),
					'sections' => array(
						array(
							'fields' => array(
								'default_order_status'   => array(
									'id'          => 'default_order_status',
									'type'        => 'select',
									'label'       => __( 'Default Order Status', 'easycommerce' ),
									'description' => __( 'The default order status for new orders.', 'easycommerce' ),
									'options'     => array(
										'pending'    => __( 'Pending', 'easycommerce' ),
										'completed'  => __( 'Completed', 'easycommerce' ),
										'processing' => __( 'Processing', 'easycommerce' ),
									),
									'default'     => 'completed',
									'placeholder' => __( 'Select Default Order Status', 'easycommerce' ),
								),
								'default_fulfill_status' => array(
									'id'          => 'default_fulfill_status',
									'type'        => 'select',
									'label'       => __( 'Default Fulfillment Status', 'easycommerce' ),
									'description' => __( 'The default Fulfillment status for new orders.', 'easycommerce' ),
									'options'     => array(
										'unfulfilled' => __( 'Unfulfilled', 'easycommerce' ),
										'fulfilled'   => __( 'Fulfilled', 'easycommerce' ),
										'partially_fulfilled' => __( 'Partially Fulfilled', 'easycommerce' ),
										'shipped'     => __( 'Shipped', 'easycommerce' ),
										'delivered'   => __( 'Delivered', 'easycommerce' ),
									),
									'default'     => 'unfulfilled',
									'placeholder' => __( 'Select Default fulfill Status', 'easycommerce' ),
								),
							),
						),
					),
				),
			),
		),
		//checkout
		'checkout'       => array(
			'label'      => __( 'Checkout', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/checkout.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/checkout-hover.png',
			'submenus'   => array(
				'settings' => array(
					'label'    => __( 'checkout template', 'easycommerce' ),
					'sections' => array(
						array(
							'fields' => array(
								'checkout_template'   => array(
									'id'          => 'checkout_template',
									'type'        => 'select',
									'label'       => __( 'Default Checkout Template', 'easycommerce' ),
									'description' => __( 'The default checkout template. <span id="easycommerce-checkout-template-preview-button">Preview Selected</span>', 'easycommerce' ),
									'options'     => easycommerce_checkout_templates(),
									'default'     => 'template-1',
								),
								'columns' => array(
									'id'          => 'columns',
									'type'        => 'select',
									'label'       => __( 'Columns', 'easycommerce' ),
									'description' => __( 'The number of columns to display.', 'easycommerce' ),
									'options'     => array(
										'1' => __( '1', 'easycommerce' ),
										'2' => __( '2', 'easycommerce' ),
									),
									'default'     => '2',
								),
								'direct_checkout' => array(
									'id'          => 'direct_checkout',
									'type'        => 'switch',
									'label'       => __( 'Enable Direct Checkout', 'easycommerce' ),
									'description' => __( 'Allow customers to proceed directly to checkout from product pages without adding to cart.', 'easycommerce' ),
									'default'     => false,
								),
							),
						),
					)
				)
			)
		),
		// Email Settings
		'email'          => array(
			'label'      => __( 'Emails', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/email.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/email-hover.png',
			'submenus'   => array_merge(
				array(
					// Email layout for configuring header and footer branding
					'layout' => array(
						'label'    => __( 'Settings', 'easycommerce' ),
						'sections' => array(

							array(
								'label'  => __( 'Layout', 'easycommerce' ),
								'fields' => array(

									'header' => array(
										'id'          => 'header',
										'type'        => 'editor',
										'label'       => __( 'Email Header', 'easycommerce' ),
										'description' => __( 'It\'ll be included as the header part of the transactional emails.', 'easycommerce' ),
										'default'     => sprintf( '<img src="%s" class="aligncenter" height="82" />', esc_url( wp_get_attachment_url( $store_logo ) ) ),
										'args'        => array(
											'teeny' => true,
											'rows'  => 6,
										),
									),

									'footer' => array(
										'id'          => 'footer',
										'type'        => 'editor',
										'label'       => __( 'Email footer', 'easycommerce' ),
										'description' => __( 'Email footer content', 'easycommerce' ),
										'default'     => sprintf(
											'<div class="footer" style="padding: 20px; border-top: 1px solid #e3e3e3; background-color: #f9f9f9; text-align: center;">© %1$s %2$s<br/>%3$s</div>',
											esc_html( $store_name ),
											date( 'Y' ),
											esc_html( $full_address ),
										),
										'args'        => array(
											'teeny' => true,
											'media' => false,
											'rows'  => 8,
										),
									),
								),
							),

							array(
								'label'  => __( 'Layout', 'easycommerce' ),
								'fields' => array(

									'width'      => array(
										'id'          => 'width',
										'type'        => 'number',
										'label'       => __( 'Container width', 'easycommerce' ),
										'description' => __( 'The width of the email body container. Default is 600px.', 'easycommerce' ),
										'default'     => '600',
										'atts'		  => [
											'min'	=> 380,
											'max'	=> 800,
											'step'	=> 1
										]
									),

									'body_bg'    => array(
										'id'          => 'body_bg',
										'type'        => 'color',
										'label'       => __( 'Body Background', 'easycommerce' ),
										'description' => __( 'Background color of the main email content', 'easycommerce' ),
										'default'     => '#fafafa',
									),

									'wrapper_bg' => array(
										'id'          => 'wrapper_bg',
										'type'        => 'color',
										'label'       => __( 'Container Background', 'easycommerce' ),
										'description' => __( 'Background color of the outside wrapper area', 'easycommerce' ),
										'default'     => '#ffffff',
									),
								),
							),
						),
					),
					'new_account' => array(
						'label'    => __( 'New Account', 'easycommerce' ),
						'sections' => array(
							array(
								'label'  => __( 'Customer Notification', 'easycommerce' ),
								'fields' => array(
									'customer_enabled' => array(
										'id'    => 'customer_enabled',
										'type'  => 'switch',
										'label' => __( 'Enable', 'easycommerce' ),
										'description' => __( 'Send this email notification to the customer.', 'easycommerce' ),
									),
									// Text field for email subject
									'customer_subject' => array(
										'id'          => 'customer_subject',
										'type'        => 'text',
										'label'       => __( 'Email Subject', 'easycommerce' ),
										'description' => __( 'The subject line for this email notification.', 'easycommerce' ),
										'placeholder' => __( 'Enter email subject', 'easycommerce' ),
										'value'       => easycommerce_email_default( 'new_account' )['customer_subject'],
									),
									// Editor field for email body content
									'customer_body'    => array(
										'id'          => 'customer_body',
										'type'        => 'editor',
										'label'       => __( 'Email Body', 'easycommerce' ),
										'description' => sprintf(
											__( 'The main content of the email. To find all the related placeholders <a href="%s" target="_blank">click here.</a>', 'easycommerce' ),
											easycommerce_dev_docs( '/settings/email-placeholders-list/' )
										),
										'placeholder' => __( 'Enter email content here', 'easycommerce' ),
										'value'       => easycommerce_email_default( 'new_account' )['customer_body'],
									),
								),
							),
							array(
								'label'  => __( 'Admin Notification', 'easycommerce' ),
								'fields' => array(
									'admin_enabled' => array(
										'id'    => 'admin_enabled',
										'type'  => 'switch',
										'label' => __( 'Enable', 'easycommerce' ),
										'description' => __( 'Send this email notification to the store admin.', 'easycommerce' ),
									),
									// Text field for email subject
									'admin_subject' => array(
										'id'          => 'admin_subject',
										'type'        => 'text',
										'label'       => __( 'Email Subject', 'easycommerce' ),
										'description' => __( 'The subject line for this email notification.', 'easycommerce' ),
										'placeholder' => __( 'Enter email subject', 'easycommerce' ),
										'value'       => easycommerce_email_default( 'new_account' )['admin_subject'],
									),
									// Editor field for email body content
									'admin_body'    => array(
										'id'          => 'admin_body',
										'type'        => 'editor',
										'label'       => __( 'Email Body', 'easycommerce' ),
										'description' => sprintf(
											__( 'The main content of the email. To find all the related placeholders <a href="%s" target="_blank">click here.</a>', 'easycommerce' ),
											easycommerce_dev_docs( '/settings/email-placeholders-list/' )
										),
										'placeholder' => __( 'Enter email content here', 'easycommerce' ),
										'value'       => easycommerce_email_default( 'new_account' )['admin_body'],
										'args'        => array(
											'media' => false,
										),
									),
								),
							),
						),
					),
				),
				// Generates submenus dynamically for each email event (e.g., new order)
				array_reduce(
					array_keys( $events = easycommerce_email_events() ),
					function ( $submenus, $event ) use ( $events ) {
						$default = easycommerce_email_default( $event );
						$submenus[ $event ] = array(
							'label'    => $events[ $event ],
							'sections' => array(
								array(
									'label'  => __( 'Customer Notification', 'easycommerce' ),
									'fields' => array(
										'customer_enabled' => array(
											'id'    => 'customer_enabled',
											'type'  => 'switch',
											'label' => __( 'Enable', 'easycommerce' ),
											'description' => __( 'Send this email notification to the customer.', 'easycommerce' ),
										),
										// Text field for email subject
										'customer_subject' => array(
											'id'          => 'customer_subject',
											'type'        => 'text',
											'label'       => __( 'Email Subject', 'easycommerce' ),
											'description' => __( 'The subject line for this email notification.', 'easycommerce' ),
											'placeholder' => __( 'Enter email subject', 'easycommerce' ),
											'value'       => $default['customer_subject'],
										),
										// Editor field for email body content
										'customer_body'    => array(
											'id'          => 'customer_body',
											'type'        => 'editor',
											'label'       => __( 'Email Body', 'easycommerce' ),
											'description' => sprintf(
												__( 'The main content of the email. To find all the related placeholders <a href="%s" target="_blank">click here.</a>', 'easycommerce' ),
												easycommerce_dev_docs( '/settings/email-placeholders-list/' )
											),
											'placeholder' => __( 'Enter email content here', 'easycommerce' ),
											'value'       => $default['customer_body'],
										),
									),
								),
								array(
									'label'  => __( 'Admin Notification', 'easycommerce' ),
									'fields' => array(
										'admin_enabled' => array(
											'id'    => 'admin_enabled',
											'type'  => 'switch',
											'label' => __( 'Enable', 'easycommerce' ),
											'description' => __( 'Send this email notification to the store admin.', 'easycommerce' ),
										),
										// Text field for email subject
										'admin_subject' => array(
											'id'          => 'admin_subject',
											'type'        => 'text',
											'label'       => __( 'Email Subject', 'easycommerce' ),
											'description' => __( 'The subject line for this email notification.', 'easycommerce' ),
											'placeholder' => __( 'Enter email subject', 'easycommerce' ),
											'value'       => $default['admin_subject'],
										),
										// Editor field for email body content
										'admin_body'    => array(
											'id'          => 'admin_body',
											'type'        => 'editor',
											'label'       => __( 'Email Body', 'easycommerce' ),
											'description' => sprintf(
												__( 'The main content of the email. To find all the related placeholders <a href="%s" target="_blank">click here.</a>', 'easycommerce' ),
												easycommerce_dev_docs( '/settings/email-placeholders-list/' )
											),
											'placeholder' => __( 'Enter email content here', 'easycommerce' ),
											'value'       => $default['admin_body'],
											'args'        => array(
												'media' => false,
											),
										),
									),
								),
							),
						);

						return $submenus;
					},
					array()
				)
			),
		),
		// Shipping Settings
		'shipping'       => array(
			'label'      => __( 'Shipping', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/shipping.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/shipping-hover.png',
			'hide_form'  => ( isset( $_GET['submenu'] ) && $_GET['submenu'] == 'methods' ),
			'submenus'   => array(
				'settings' => array(
					'label'    => __( 'Settings', 'easycommerce' ),
					'desc'     => __( 'Shipping related settings', 'easycommerce' ),
					'reset_button' => [
						'show' => true,
						'text' => __( 'Reset Settings', 'easycommerce' ),
					],
					'save_button' => [
						'show' => true,
						'text' => __( 'Save Settings', 'easycommerce' ),
					],

					'sections' => array(
						array(
							'fields' => array(
								'countries' => array(
									'id'          => 'countries',
									'type'        => 'select',
									'multiple'    => true,
									'label'       => __( 'Shipping Countries', 'easycommerce' ),
									'description' => __( 'Choose the countries you ship products to.', 'easycommerce' ),
									'options'     => easycommerce_countries(),
								),
							),
						),
					),
				),
				'methods'  => array(
					'label'    => __( 'Plans', 'easycommerce' ),
					'desc'     => __( 'Select the pages used by EasyCommerce for key functionality.', 'easycommerce' ),
					'reset_button' => [
						'show' => false
					],
					'save_button' => [
						'show' => false,
					],

					'sections' => array(
						array(
							'template' => EASYCOMMERCE_PLUGIN_DIR . 'views/settings/shipping.php',
						),
					),
				),
			),
		),
		// Tax Settings
		'tax'            => array(
			'label'      => __( 'Taxation', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/tax.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/tax-hover.png',
			'hide_form'  => ( isset( $_GET['submenu'] ) && $_GET['submenu'] == 'classes' ),
			'submenus'   => array(
				'settings' => array(
					'label'    => __( 'Settings', 'easycommerce' ),
					'desc'     => __( 'Tax related settings', 'easycommerce' ),
					'reset_button' => [
						'show' => true,
						'text' => __( 'Reset Settings', 'easycommerce' ),
					],
					'save_button' => [
						'show' => true,
						'text' => __( 'Save Settings', 'easycommerce' ),
					],

					'sections' => array(
						array(
							'fields' => array(
								'countries' => array(
									'id'          => 'countries',
									'type'        => 'select',
									'multiple'    => true,
									'label'       => __( 'Tax Countries', 'easycommerce' ),
									'description' => __( 'Choose the countries you sell products to.', 'easycommerce' ),
									'options'     => easycommerce_countries(),
								),
							),
						),
					),
				),
				'classes'  => array(
					'label'    => __( 'Classes', 'easycommerce' ),
					'desc'     => __( 'Select the pages used by EasyCommerce for key functionality.', 'easycommerce' ),
					'reset_button' => [
						'show' => false,
					],
					'save_button' => [
						'show' => false,
					],

					'sections' => array(
						array(
							'template' => EASYCOMMERCE_PLUGIN_DIR . 'views/settings/tax.php',
						),
					),
				),
			),
		),
		// Abandoned cart Settings
		'abandoned-cart' => array(
			'label'      => __( 'Cart Recovery', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/cart-recovery.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/cart-recovery-hover.png',
			'submenus'   => array(

				'settings' => array(
					'label'    => __( 'Settings', 'easycommerce' ),
					'desc'     => __( 'Abandoned cart settings', 'easycommerce' ),
					'sections' => array(
						array(
							'fields' => array(
								'delay' => array(
									'id'          => 'delay',
									'type'        => 'number',
									'label'       => __( 'Delay (minutes)', 'easycommerce' ),
									'description' => __( 'After when can we call a cart abandoned?', 'easycommerce' ),
									'placeholder' => __( 'Enter time delay in minutes', 'easycommerce' ),
									'default'        => 30,
									'required'	=> true,
									'atts'		=>[
										'min'	=> 1,
										'step'	=> 1,
									]
								),
								'random_coupon_discount_percentage' => array(
									'id'          => 'random_coupon_discount_percentage',
									'type'        => 'number',
									'label'       => __( 'Abandoned Cart Discount %', 'easycommerce' ),
									'description' => __( 'Set a discount percentage to include in abandoned cart emails. If set to 0, no coupon will be included.', 'easycommerce' ),
									'placeholder' => __( 'Enter discount percentage (0 to disable)', 'easycommerce' ),
									'default'        => 0,
									'required'	=> true,
									'atts'		=>[
										'min'	=> 0,
										'step'	=> 1,
									]
								),
								'recurring_email' => array(
									'id'          => 'recurring_email',
									'type'        => 'checkbox',
									'label'       => __( 'Enable Automated Reminder', 'easycommerce' ),
									'description' => __( 'Automatically send a reminder email to any pending abandoned cart that has not received a reminder yet.', 'easycommerce' ),
									'default'     => false,
								),
							),
						),
						array(
							'fields' => array(
								'subject' => array(
									'id'          => 'subject',
									'type'        => 'text',
									'label'       => __( 'Subject', 'easycommerce' ),
									'description' => __( 'The subject line for the abandoned cart reminder email.', 'easycommerce' ),
									'default'     => '##shop_name##- Your Order Is Yet to Be Placed!',
									'placeholder' => __( 'Enter email subject', 'easycommerce' ),
								),
								'body'    => array(
									'id'          => 'body',
									'type'        => 'editor',
									'label'       => __( 'Body', 'easycommerce' ),
									'description' => sprintf(
										__( 'To find all the related placesholders <a href="%s" target="_blank">click here.</a>', 'easycommerce' ),
										easycommerce_dev_docs( '/settings/email-placeholders-list/' )
									),
									'placeholder' => __( 'Enter email Body', 'easycommerce' ),
									'default'     => 'Hi ##name##,
We noticed you left some items in your cart at ##shop_name##. Your cart, worth ##cart_total##, is still waiting for you!
Here’s what you left behind:
##product_list##
Don’t miss out—your items might sell out soon! Click below to return to your cart and complete your purchase.
Go to Checkout 👉 ##cart_link## 
Need help? Feel free to reach out. We’re happy to assist!
Best,
##shop_name## Team',
								),
							),
						),
					),
				),
			),
		),

		// AI Settings
		'ai' => array(
			'label'      => __( 'AI', 'easycommerce' ),
			'icon'       => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/ai.png',
			'hover-icon' => EASYCOMMERCE_ASSETS_URL . 'admin/img/settings/ai-hover.png',
			// Usage + Connectivity tabs are read-only; drop the settings form + Save/Reset there.
			'hide_form'  => in_array( isset( $_GET['submenu'] ) ? sanitize_key( $_GET['submenu'] ) : '', array( 'usage', 'connectivity' ), true ),
			'submenus'   => array(
				// 'service' => array(
				// 	'label'    => __( 'Service Provider', 'easycommerce' ),
				// 	'desc'     => __( 'Service Provider', 'easycommerce' ),
				// 	'sections' => array(
				// 		array(
				// 			'fields' => array(
				// 			    'provider' => array(
				// 			        'id'          => 'provider',
				// 			        'type'        => 'select',
				// 			        'options'	=> [
				// 			        	'easycommerce' => __( 'EasyCommerce', 'easycommerce' ),
				// 			        	'openai' => __( 'OpenAI', 'easycommerce' ),
				// 			        	'deepseek' => __( 'DeepSeek', 'easycommerce' ),
				// 			        ],
				// 			        'label'       => __( 'Select Provider', 'easycommerce' ),
				// 			        'description' => __( 'Select an AI service provider. For providers other than "EasyCommerce", you need to input the API credentials.', 'easycommerce' ),
				// 			    ),
				// 			    'openai' => array(
				// 			        'id'          => 'openai',
				// 			        'type'        => 'text',
				// 			        'label'       => __( 'OpenAI API key', 'easycommerce' ),
				// 			        'description' => sprintf( __( 'Input your OpenAI API key. You can get the key <a href="%s" target="_blank">from here</a>.', 'easycommerce' ), 'https://platform.openai.com/api-keys' )
				// 			    ),
				// 			    'deepseek' => array(
				// 			        'id'          => 'deepseek',
				// 			        'type'        => 'text',
				// 			        'label'       => __( 'DeepSeek API key', 'easycommerce' ),
				// 			        'description' => sprintf( __( 'Input your DeepSeek API key. You can get the key <a href="%s" target="_blank">from here</a>.', 'easycommerce' ), 'https://platform.deepseek.com/api_keys' )
				// 			    ),
				// 			),
				// 		),
				// 	),
				// ),
				'generative-ai' => array(
					'label'    => __( 'Generative AI', 'easycommerce' ),
					'desc'     => __( 'Generative AI', 'easycommerce' ),
					'sections' => array(
						array(
							'fields' => array(
								'text_generator' => array(
									'id'          => 'text_generator',
									'type'        => 'checkbox',
									'label'       => __( 'Text Generator', 'easycommerce' ),
									'description' => sprintf( __( 'Enable AI Writer to suggest and create product descriptions instantly. <a href="%1$s" target="_blank">See more.</a>', 'easycommerce' ), 'https://easycommerce.dev/features/ai/?utm_source=plugin&utm_medium=settings&utm_campaign=text-generator' ),
									'default'     => true,
								),
								'image_generator' => array(
									'id'          => 'image_generator',
									'type'        => 'checkbox',
									'label'       => __( 'Image Generator', 'easycommerce' ),
									'description' => sprintf( __( 'Generate high-quality product images with AI directly from your dashboard. <a href="%1$s" target="_blank">See more.</a>', 'easycommerce' ), 'https://easycommerce.dev/features/ai/?utm_source=plugin&utm_medium=settings&utm_campaign=image-generator' ),
									'default'     => true,
								),
								'template_generator' => array(
									'id'          => 'template_generator',
									'type'        => 'checkbox',
									'label'       => __( 'Template Generator', 'easycommerce' ),
									'description' => sprintf( __( 'Enable AI Template Generator to create ready-to-use block layouts. <a href="%1$s" target="_blank">See more.</a>', 'easycommerce' ), 'https://easycommerce.dev/features/ai/?utm_source=plugin&utm_medium=settings&utm_campaign=template-generator' ),
									'default'     => true,
								),
								'attribute_generator' => array(
									'id'          => 'attribute_generator',
									'type'        => 'checkbox',
									'label'       => __( 'Attribute Generator', 'easycommerce' ),
									'description' => sprintf( __( 'Enable AI to suggest product attributes and values instantly. <a href="%1$s" target="_blank">See more.</a>', 'easycommerce' ), 'https://easycommerce.dev/features/ai/?utm_source=plugin&utm_medium=settings&utm_campaign=attribute-generator' ),
									'default'     => true,
								),
							),
						),
					),
				),
				'agentic-ai' => array(
					'label'    => __( 'Agentic AI', 'easycommerce' ),
					'desc'     => __( 'Agentic AI', 'easycommerce' ),
					'sections' => array(
						array(
							'label'  => __( 'Shopping Agent', 'easycommerce' ),
							'desc'   => __( 'Your customers will see a chat button on your store where they can ask questions, find products, check stock, or even place orders right from the chatting screen.', 'easycommerce' ),
							'fields' => array(
								'enable' => array(
									'id'          => 'enable',
									'type'        => 'checkbox',
									'label'       => __( 'Enable Shopping Agent', 'easycommerce' ),
									'description' => __( 'Show the AI shopping chat button on your storefront so customers can ask questions and place orders.', 'easycommerce' ),
									'default'     => true,
								),
								'agent_name' => array(
									'id'          => 'agent_name',
									'type'        => 'text',
									'label'       => __( 'Chat Assistant Name', 'easycommerce' ),
									'description' => __( 'What customers see at the top of the chat window. Example: "Store Helper" or "Shopping Assistant".', 'easycommerce' ),
									'default'     => __( 'Shopping Assistant', 'easycommerce' ),
								),
								'agent_avatar' => array(
									'id'          => 'agent_avatar',
									'type'        => 'image',
									'label'       => __( 'Chat Assistant Avatar', 'easycommerce' ),
									'description' => __( 'Profile picture shown in the chat window. Use a square image (at least 80×80 pixels). Leave empty to use a default avatar.', 'easycommerce' ),
									'default'     => '',
								),
								'bubble_position' => array(
									'id'          => 'bubble_position',
									'type'        => 'select',
									'label'       => __( 'Chat Button Position', 'easycommerce' ),
									'description' => __( 'Where should the chat button appear on your store pages?', 'easycommerce' ),
									'options'     => array(
										'right' => __( 'Right', 'easycommerce' ),
										'left'  => __( 'Left', 'easycommerce' ),
									),
									'default'     => 'right',
								),
								'primary_color' => array(
									'id'          => 'primary_color',
									'type'        => 'color',
									'label'       => __( 'Chat Color Theme', 'easycommerce' ),
									'description' => __( 'Choose the color for the chat button and message bubbles. Use your brand color for consistency.', 'easycommerce' ),
									'default'     => '#7351FD',
								),
							),
						),
						array(
							'label'  => __( 'Search & Discovery', 'easycommerce' ),
							'desc'   => __( 'Let AI power your product search and recommendations.', 'easycommerce' ),
							'fields' => array(
								'smart_search' => array(
									'id'          => 'smart_search',
									'type'        => 'checkbox',
									'label'       => __( 'Smart Search', 'easycommerce' ),
									'description' => sprintf( __( 'Help customers find products even with spelling errors. The AI understands what they\'re looking for and shows the right products. <a href="%1$s" target="_blank">See more.</a>', 'easycommerce' ), 'https://easycommerce.dev/features/ai/?utm_source=plugin&utm_medium=settings&utm_campaign=smart-search' ),
									'default'     => false,
								),
								'voice_search' => array(
									'id'          => 'voice_search',
									'type'        => 'checkbox',
									'label'       => __( 'Voice Search', 'easycommerce' ),
									'description' => sprintf( __( 'Let customers search by speaking instead of typing. Hands-free shopping experience. (Coming soon) <a href="%1$s" target="_blank">See more.</a>', 'easycommerce' ), 'https://easycommerce.dev/features/ai/?utm_source=plugin&utm_medium=settings&utm_campaign=voice-search' ),
									'disabled'    => true,
								),
								'cross_sell' => array(
									'id'          => 'cross_sell',
									'type'        => 'checkbox',
									'label'       => __( 'Smart Recommendations', 'easycommerce' ),
									'description' => sprintf( __( 'The AI suggests related products to customers based on what they\'re looking at. Boost average order value. (Coming soon) <a href="%1$s" target="_blank">See more.</a>', 'easycommerce' ), 'https://easycommerce.dev/features/ai/?utm_source=plugin&utm_medium=settings&utm_campaign=cross-sell' ),
									'disabled'    => true,
								),
							),
						),
					),
				),
				// Usage tab - read-only AI credit usage log.
				'usage' => array(
					'label'        => __( 'Usage', 'easycommerce' ),
					'desc'         => __( 'Your AI credit usage history.', 'easycommerce' ),
					'save_button'  => array( 'show' => false ),
					'reset_button' => array( 'show' => false ),
					'sections'     => array(
						array(
							'template' => EASYCOMMERCE_PLUGIN_DIR . 'views/settings/ai-usage.php',
						),
					),
				),
			)
		)
	)
);

// The AI tab set depends on the AI service connection state:
// - disconnected: prepend a "Connectivity" tab and hide "Usage" (no credits yet).
// - connected: no "Connectivity"; "Usage" stays last (defined inline above).
if ( isset( $easycommerce_settings_menus['ai']['submenus'] ) ) {
	if ( ! $ec_ai_connected ) {
		unset( $easycommerce_settings_menus['ai']['submenus']['usage'] );

		$easycommerce_settings_menus['ai']['submenus'] = array(
			'connectivity' => array(
				'label'        => __( 'Connectivity', 'easycommerce' ),
				'desc'         => __( 'Connect your store to the EasyCommerce AI service.', 'easycommerce' ),
				'save_button'  => array( 'show' => false ),
				'reset_button' => array( 'show' => false ),
				'sections'     => array(
					array(
						'template' => EASYCOMMERCE_PLUGIN_DIR . 'views/settings/ai-connectivity.php',
					),
				),
			),
		) + $easycommerce_settings_menus['ai']['submenus'];
	}
}

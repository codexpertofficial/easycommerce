<?php

use EasyCommerce\Helpers\Utility;
use Stripe\StripeClient;
use Stripe\Util\ApiVersion;

/**
 * Retrieves the Stripe API version being used.
 *
 * @return string The Stripe API version.
 */
function easycommerce_stripe_get_api_version(): string {
	return Utility::get_option( 'payment', 'stripe', 'api_version', ApiVersion::CURRENT, );
}

/**
 * Checks if Stripe is configured in sandbox mode.
 *
 * @return bool True if sandbox mode is enabled, false otherwise.
 */
function easycommerce_stripe_is_sandbox(): bool {
	return (bool) Utility::get_option( 'payment', 'stripe', 'is_sandbox_mode', false );
}

/**
 * Retrieves the Stripe publishable key based on the current mode (sandbox or live).
 *
 * @return string The publishable key for Stripe integration.
 */
function easycommerce_stripe_get_publishable_key(): string {
	$field_name = easycommerce_stripe_is_sandbox() ? 'sandbox_publishable_key' : 'publishable_key';

	return Utility::get_option( 'payment', 'stripe', $field_name, '' );
}

/**
 * Retrieves the Stripe secret key based on the current mode (sandbox or live).
 *
 * @return string The secret key for Stripe API authentication.
 */
function easycommerce_stripe_get_secret_key(): string {
	$field_name = easycommerce_stripe_is_sandbox() ? 'sandbox_secret_key' : 'secret_key';

	return Utility::get_option( 'payment', 'stripe', $field_name, '' );
}

/**
 * Retrieves the Stripe webhook signing secret based on the current mode (sandbox or live).
 *
 * @return string The webhook signing secret for verifying webhook authenticity.
 */
function easycommerce_stripe_get_webhook_secret(): string {
	$field_name = easycommerce_stripe_is_sandbox() ? 'sandbox_webhook_secret' : 'webhook_secret';

	return Utility::get_option( 'payment', 'stripe', $field_name, '' );
}

/**
 * Updates the Stripe webhook signing secret in the database.
 *
 * @param string $signing_secret The new webhook signing secret from Stripe.
 *
 * @return void
 */
function easycommerce_stripe_update_webhook_secret( string $signing_secret ): void {
	$option_name = 'easycommerce-payment-stripe';
	$field_name  = easycommerce_stripe_is_sandbox() ? 'sandbox_webhook_secret' : 'webhook_secret';

	// Get the existing options.
	$existing_options = get_option( $option_name );

	// Update the signing secret in the options.
	$existing_options[ $field_name ] = $signing_secret;

	update_option( $option_name, $existing_options );
}

/**
 * Checks if Stripe payment gateway is properly configured and ready for use.
 *
 * @return bool True if both publishable and secret keys are configured, false otherwise.
 */
function easycommerce_stripe_is_ready(): bool {
	return ! empty( easycommerce_stripe_get_publishable_key() ) && ! empty( easycommerce_stripe_get_secret_key() );
}

/**
 * Generates the webhook URL for Stripe webhook endpoints.
 *
 * @return string The full REST API URL for Stripe webhooks.
 */
function easycommerce_stripe_get_webhook_url(): string {
	return rest_url( 'easycommerce/v1/stripe/webhook' );
}

/**
 * Validates a URL for public accessibility, HTTPS, and ensures it's not a local/test domain.
 *
 * @param string $url The URL to validate.
 *
 * @return bool True if valid, false otherwise.
 */
function easycommerce_stripe_is_valid_domain_url( string $url ): bool {
	// Basic checks: must be a valid URL and HTTPS.
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return false;
	}

	if ( strpos( $url, 'https://' ) !== 0 ) {
		return false;
	}

	$parsed = wp_parse_url( $url );
	$host   = $parsed['host'] ?? '';

	// Block common local/test domains.
	$local_domains = array( 'localhost', '127.0.0.1', '*.local', '*.test' );

	if ( in_array( $host, $local_domains, true ) || preg_match( '/\.(local|test)$/', $host ) ) {
		return false;
	}

	return true;
}

/**
 * Retrieves or creates a singleton instance of the Stripe API client.
 *
 * @return StripeClient|null The configured Stripe API client instance or null if API key is empty.
 */
function easycommerce_stripe_get_api_client() {
	static $stripe_client;

	if ( ! $stripe_client ) {
		$secret_key = easycommerce_stripe_get_secret_key();

		if ( empty( $secret_key ) ) {
			return null;
		}

		$stripe_client = new StripeClient(
			array(
				'api_key'        => $secret_key,
				'app_info'       => array(
					'name'    => __( 'EasyCommerce Stripe Payment Gateway', 'easycommerce' ),
					'url'     => 'https://easycommerce.dev/addons/easycommerce',
					'version' => EASYCOMMERCE_VERSION,
				),
				'stripe_version' => easycommerce_stripe_get_api_version(),
			)
		);
	}

	return $stripe_client;
}


/**
 * Filter payment methods based on currency compatibility.
 *
 * @param array  $payment_methods List of payment method types.
 * @param string $currency        Currency code (lowercase).
 *
 * @return array Filtered payment methods compatible with the currency.
 */
function easycommerce_stripe_filter_payment_methods_by_currency( $payment_methods, $currency ) {
	$currency_restrictions = array(
		// Cards
		'card'              => array(), // All currencies
		'cartes_bancaires'  => array( 'eur' ),
		'kr_card'           => array( 'krw' ),

		// Wallets
		'alipay'            => array( 'usd', 'aud', 'cad', 'eur', 'gbp', 'hkd', 'jpy', 'sgd', 'myr', 'nzd', 'cny' ),
		'amazon_pay'        => array(), // All currencies
		'apple_pay'         => array(), // All currencies
		'cashapp'           => array( 'usd' ),
		'google_pay'        => array(), // All currencies
		'kakao_pay'         => array( 'krw' ),
		'link'              => array(), // All currencies
		'mb_way'            => array( 'eur' ),
		'naver_pay'         => array( 'krw' ),
		'payco'             => array( 'krw' ),
		'samsung_pay'       => array( 'krw' ),
		'wechat_pay'        => array( 'usd', 'aud', 'cad', 'eur', 'gbp', 'hkd', 'jpy', 'sgd', 'cny' ),
		'crypto'            => array( 'usd' ),

		// Vouchers
		'multibanco'        => array( 'eur' ),

		// Bank redirects
		'bancontact'        => array( 'eur' ),
		'blik'              => array( 'pln' ),
		'eps'               => array( 'eur' ),
		'giropay'           => array( 'eur' ),
		'ideal'             => array( 'eur' ),
		'p24'               => array( 'eur', 'pln' ),
		'sofort'            => array( 'eur' ),

		// Buy now, pay later
		'affirm'            => array( 'usd', 'cad' ),
		'afterpay_clearpay' => array( 'aud', 'cad', 'nzd', 'gbp', 'usd' ),
		'klarna'            => array( 'usd' ), // @using euro causing issues
		'zip'               => array( 'aud', 'usd' ),
		'scalapay'          => array( 'eur', 'gbp' ),
		'sunbit'            => array( 'usd' ),
		'satispay'          => array( 'eur' ),

		// Bank debits
		'us_bank_account'   => array( 'usd' ), // ACH Direct Debit
		'acss_debit'        => array( 'cad' ), // Canadian pre-authorized debits
		'bacs_debit'        => array( 'gbp' ), // Bacs Direct Debit
		'sepa_debit'        => array( 'eur' ),

		// Bank transfers
		'customer_balance'  => array( 'eur', 'gbp', 'jpy', 'mxn', 'usd' ),

		// Real-time payments
		'pix'               => array( 'brl' ),
	);

	$filtered_methods = array();

	foreach ( $payment_methods as $method ) {
		if ( ! isset( $currency_restrictions[ $method ] ) ) {
			continue;
		}

		// Empty list = supported in every currency.
		if ( empty( $currency_restrictions[ $method ] ) || in_array( $currency, $currency_restrictions[ $method ], true ) ) {
			$filtered_methods[] = $method;
		}
	}

	return $filtered_methods;
}

/**
 * Filter out only the wallets Stripe rejects as explicit Payment Element
 * paymentMethodTypes. `apple_pay` and `google_pay` are NOT valid types — passing
 * them throws and breaks the whole element; the Payment Element instead surfaces
 * them automatically as buttons when the shopper's browser/device supports them.
 * Every other wallet (alipay, amazon_pay, cashapp, wechat_pay, link, samsung_pay,
 * kakao_pay, mb_way, naver_pay, payco) is a valid type and stays so it renders.
 *
 * @param array $payment_methods List of payment method types.
 *
 * @return array Filtered payment methods without the non-listable express wallets.
 */
function easycommerce_stripe_filter_wallet_payment_methods( $payment_methods ) {
	$wallet_methods = array(
		'apple_pay',
		'google_pay',
	);

	return array_values( array_diff( $payment_methods, $wallet_methods ) );
}

/**
 * Single source of truth for Stripe payment method minimum transaction amounts.
 *
 * Keyed by method, then lowercase currency code, value in cents. Shared by the
 * server-side filter and localized to the client (stripe-payment.js) so both
 * sides apply identical thresholds. These are public Stripe limits, not secrets.
 *
 * @return array<string, array<string, int>> Minimum amounts in cents.
 */
function easycommerce_stripe_payment_method_minimums() {
	return array(
		'affirm'            => array(
			'usd' => 3500,
			'cad' => 3500,
		),
		'afterpay_clearpay' => array(
			'usd' => 100,
			'aud' => 100,
			'cad' => 100,
			'nzd' => 100,
			'gbp' => 100,
		),
		'klarna'            => array(
			'usd' => 100,
			'eur' => 100,
			'gbp' => 100,
			'aud' => 100,
			'cad' => 100,
		),
		'zip'               => array(
			'usd' => 3500,
			'aud' => 3500,
		),
		'sunbit'            => array( 'usd' => 6000 ),
		'scalapay'          => array(
			'eur' => 500,
			'gbp' => 500,
		),
	);
}

/**
 * Single source of truth for Stripe payment method maximum transaction amounts.
 *
 * Keyed by method, then lowercase currency code, value in cents. Shared by the
 * server-side filter and localized to the client (stripe-payment.js) so both
 * sides apply identical thresholds. These are public Stripe limits, not secrets.
 *
 * @return array<string, array<string, int>> Maximum amounts in cents.
 */
function easycommerce_stripe_payment_method_maximums() {
    return array(
        'afterpay_clearpay' => array(
            'usd' => 400000,   
            'aud' => 400000,  
            'cad' => 200000,   
            'nzd' => 400000,   
            'gbp' => 120000, 
        ),
        'affirm'            => array(
            'usd' => 3000000,  
            'cad' => 3000000,  
        ),
        'cashapp'           => array(
            'usd' => 200000,   
        ),
        'zip'               => array(
            'usd' => 150000,   
            'aud' => 100000,   
        ),
    );
}

/**
 * Filter payment methods based on both minimum and maximum transaction amounts.
 *
 * @param array  $payment_methods List of payment method types.
 * @param int    $amount_in_cents Transaction amount in cents.
 * @param string $currency        Currency code (lowercase).
 *
 * @return array Filtered payment methods within the allowed amount range.
 */
function easycommerce_stripe_filter_payment_methods_by_amount( $payment_methods, $amount_in_cents, $currency ) {
	$amount_minimums = easycommerce_stripe_payment_method_minimums();
	$amount_maximums = easycommerce_stripe_payment_method_maximums();

	return array_values(
		array_filter(
			$payment_methods,
			function ( $method ) use ( $amount_in_cents, $currency, $amount_minimums, $amount_maximums ) {
				if ( isset( $amount_minimums[ $method ] ) ) {
					$min = $amount_minimums[ $method ][ $currency ] ?? null;
					if ( $min !== null && $amount_in_cents < $min ) {
						return false;
					}
				}
				if ( isset( $amount_maximums[ $method ] ) ) {
					$max = $amount_maximums[ $method ][ $currency ] ?? null;
					if ( $max !== null && $amount_in_cents > $max ) {
						return false;
					}
				}
				return true;
			}
		)
	);
}

<?php

namespace EasyCommerce\Controllers\Payment\Stripe\Helpers;

use Throwable;

defined( 'ABSPATH' ) || exit;

class Payment_Methods {

	protected $api_client;

	protected array $payment_method_configs = array();

	protected string $cache_key = 'easycommerce_stripe_payment_methods_cache';

	protected string $pmc_id_cache_key = 'easycommerce_stripe_default_pmc_id';

	public function __construct() {
		$this->api_client = easycommerce_stripe_get_api_client();
	}

	/**
	 * Force sync payment methods from Stripe API and save to database
	 *
	 * @return void
	 */
	public function force_sync_payment_methods(): void {
		if ( ! $this->api_client ) {
			return;
		}

		$methods = $this->fetch_payment_methods_from_api();
		if ( ! empty( $methods ) ) {
			update_option( $this->cache_key, $methods );
		}
	}

	/**
	 * Find the account's default payment method configuration.
	 *
	 * Stripe uses this configuration for any PaymentIntent that does not name a
	 * specific one, so it is the single source of truth for what shows at
	 * checkout. Per Stripe's API, the account default is uniquely identified by
	 * `parent === null` (a top-level config, not a child) AND `is_default === true`.
	 * Stripe guarantees exactly one such configuration per account/mode.
	 *
	 * `name` is a user-editable label and is deliberately NOT matched on — a
	 * renamed default, or an unrelated config coincidentally named "Default",
	 * must not change the result.
	 *
	 * The list is auto-paginated, so the default is found even when the account
	 * has more configurations than fit in a single API page.
	 *
	 * @return \Stripe\PaymentMethodConfiguration|null The account default, or null if none/error.
	 */
	private function get_account_default_pmc() {
		if ( ! $this->api_client ) {
			return null;
		}

		try {
			$collection = $this->api_client->paymentMethodConfigurations->all( array( 'limit' => 100 ) );

			foreach ( $collection->autoPagingIterator() as $config ) {
				if ( null === $config->parent && true === (bool) $config->is_default ) {
					return $config;
				}
			}
		} catch ( Throwable $e ) {
			return null;
		}

		return null;
	}

	/**
	 * Fetch payment methods directly from Stripe API
	 *
	 * @return array Array of payment method data
	 */
	private function fetch_payment_methods_from_api(): array {
		$methods = array();

		try {
			$main_default_pmc = $this->get_account_default_pmc();

			if ( $main_default_pmc ) {
				update_option( $this->pmc_id_cache_key, $main_default_pmc->id );

				$config_data  = $main_default_pmc->toArray();
				$exclude_keys = array( 'id', 'object', 'application', 'is_default', 'livemode', 'name', 'parent', 'created', 'metadata', 'active' );

				foreach ( $config_data as $key => $value ) {
					if ( in_array( $key, $exclude_keys, true ) ) {
						continue;
					}

					if ( is_array( $value ) && isset( $value['available'] ) ) {
						$methods[ $key ] = array(
							'available'  => $value['available'],
							'preference' => $value['display_preference']['value'] ?? 'off',
							'label'      => $this->get_payment_method_label( $key ),
						);
					}
				}
			}
		} catch ( Throwable $e ) {
		}

		if ( empty( $methods ) ) {
			$all_labels = $this->get_all_payment_method_labels();
			$methods    = array();
			foreach ( $all_labels as $method_key => $label ) {
				$methods[ $method_key ] = array(
					'available'  => false,
					'preference' => 'off',
					'label'      => $label,
				);
			}
		}

		return $methods;
	}

	/**
	 * Gets enabled payment methods from Stripe's default configuration.
	 *
	 * @return array Array of enabled payment method types.
	 */
	public function get_enabled_payment_methods(): array {
		$enabled_methods = array();

		$all_payment_methods = $this->get_all_available_payment_methods();

		foreach ( $all_payment_methods as $method_key => $method_data ) {
			if ( $method_data['available'] && isset( $method_data['preference'] ) && $method_data['preference'] === 'on' ) {
				$enabled_methods[] = $method_key;
			}
		}

		return $enabled_methods;
	}

	/**
	 * Get the id of the default Stripe payment method configuration in use.
	 *
	 * Returns the cached id, syncing from the Stripe API if not yet cached.
	 * Used to deep-link the admin settings to the exact configuration that
	 * supplies the checkout payment methods.
	 *
	 * @return string The default PMC id (pmc_...), or empty string if unavailable.
	 */
	public function get_default_pmc_id(): string {
		$pmc_id = get_option( $this->pmc_id_cache_key, '' );

		if ( ! empty( $pmc_id ) ) {
			return $pmc_id;
		}

		// Not cached yet — fetch_payment_methods_from_api() persists the id as a side effect.
		$this->get_all_available_payment_methods();

		return (string) get_option( $this->pmc_id_cache_key, '' );
	}

	/**
	 * Gets a human-readable label for a payment method type.
	 *
	 * @param string $method_type The payment method type.
	 *
	 * @return string The human-readable label.
	 */
	private function get_payment_method_label( string $method_type ): string {
		$labels = $this->get_all_payment_method_labels();

		return $labels[ $method_type ] ?? ucfirst( str_replace( '_', ' ', $method_type ) );
	}

	/**
	 * Get all available payment methods from Stripe's default configuration
	 *
	 * @return array Array of payment method data
	 */
	public function get_all_available_payment_methods(): array {

		$cached_methods = get_option( $this->cache_key, array() );

		if ( ! empty( $cached_methods ) ) {
			return $cached_methods;
		}

		$methods = $this->fetch_payment_methods_from_api();
		if ( ! empty( $methods ) ) {
			update_option( $this->cache_key, $methods );
		}

		return $methods;
	}

	/**
	 * Get all possible payment method labels
	 *
	 * @return array Array of payment method labels
	 */
	private function get_all_payment_method_labels(): array {
		$labels = array(
			// Cards (3 methods)
			'card'              => __( 'Credit/Debit Cards', 'easycommerce' ),
			'cartes_bancaires'  => __( 'Cartes Bancaires', 'easycommerce' ),
			'kr_card'           => __( 'Korean Cards', 'easycommerce' ),

			// Wallets (12 methods)
			'alipay'            => __( 'Alipay', 'easycommerce' ),
			'amazon_pay'        => __( 'Amazon Pay', 'easycommerce' ),
			'apple_pay'         => __( 'Apple Pay', 'easycommerce' ),
			'cashapp'           => __( 'Cash App Pay', 'easycommerce' ),
			'google_pay'        => __( 'Google Pay', 'easycommerce' ),
			'kakao_pay'         => __( 'Kakao Pay', 'easycommerce' ),
			'link'              => __( 'Link', 'easycommerce' ),
			'mb_way'            => __( 'MB WAY', 'easycommerce' ),
			'naver_pay'         => __( 'Naver Pay', 'easycommerce' ),
			'payco'             => __( 'PAYCO', 'easycommerce' ),
			'samsung_pay'       => __( 'Samsung Pay', 'easycommerce' ),
			'wechat_pay'        => __( 'WeChat Pay', 'easycommerce' ),

			// Crypto (1 method)
			'crypto'            => __( 'Stablecoins and Crypto', 'easycommerce' ),

			// Vouchers (1 method)
			'multibanco'        => __( 'Multibanco', 'easycommerce' ),

			// Bank redirects (6 methods)
			'bancontact'        => __( 'Bancontact', 'easycommerce' ),
			'blik'              => __( 'BLIK', 'easycommerce' ),
			'eps'               => __( 'EPS', 'easycommerce' ),
			'giropay'           => __( 'giropay', 'easycommerce' ),
			'ideal'             => __( 'iDEAL', 'easycommerce' ),
			'p24'               => __( 'Przelewy24', 'easycommerce' ),
			'sofort'            => __( 'Sofort', 'easycommerce' ),

			// Buy now, pay later (4 methods)
			'affirm'            => __( 'Affirm', 'easycommerce' ),
			'afterpay_clearpay' => __( 'Afterpay/Clearpay', 'easycommerce' ),
			'klarna'            => __( 'Klarna', 'easycommerce' ),
			'zip'               => __( 'Zip', 'easycommerce' ),

			// Bank debits (4 methods)
			'us_bank_account'   => __( 'ACH Direct Debit', 'easycommerce' ),
			'acss_debit'        => __( 'Canadian Pre-authorized Debits', 'easycommerce' ),
			'bacs_debit'        => __( 'Bacs Direct Debit', 'easycommerce' ),
			'sepa_debit'        => __( 'SEPA Direct Debit', 'easycommerce' ),

			// Bank transfers (1 method)
			'customer_balance'  => __( 'Bank Transfer', 'easycommerce' ),

			// Real-time payments (1 method)
			'pix'               => __( 'Pix', 'easycommerce' ),
		);

		return apply_filters( 'easycommerce_payment_method_labels', $labels );
	}
}

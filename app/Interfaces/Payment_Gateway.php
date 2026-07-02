<?php
namespace EasyCommerce\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface Payment_Gateway
 *
 * Defines the standard methods that all payment gateways must implement.
 */
interface Payment_Gateway {

	/**
	 * Initialize the payment gateway.
	 *
	 * @return void
	 */
	public function initialize();

	/**
	 * Process a payment.
	 *
	 * @param array $payment_data
	 * @return array
	 */
	public function process_payment( array $payment_data );

	/**
	 * Handle payment webhook.
	 *
	 * @param array $webhook_data
	 * @return void
	 */
	public function handle_webhook( array $webhook_data );

	/**
	 * Get the payment gateway ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get the payment gateway title.
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get the payment gateway description.
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Check if the payment gateway is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Get the settings fields for the payment gateway.
	 *
	 * @return array
	 */
	public function get_settings_fields();
}

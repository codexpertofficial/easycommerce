<?php

namespace EasyCommerce\Controllers\Payment\Stripe\Helpers;

use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Throwable;

defined( 'ABSPATH' ) || exit;

class Webhook {

	protected $api_client;

	protected $events = array(
		Event::PAYMENT_INTENT_SUCCEEDED,
		Event::PAYMENT_INTENT_REQUIRES_ACTION,
		Event::PAYMENT_INTENT_AMOUNT_CAPTURABLE_UPDATED,
		Event::PAYMENT_INTENT_PAYMENT_FAILED,
		Event::SETUP_INTENT_SUCCEEDED,
		Event::SETUP_INTENT_SETUP_FAILED,
		Event::CHARGE_SUCCEEDED,
		Event::CHARGE_CAPTURED,
		Event::CHARGE_FAILED,
		Event::CHARGE_DISPUTE_CREATED,
		Event::CHARGE_DISPUTE_CLOSED,
		Event::BALANCE_AVAILABLE,
		Event::REVIEW_OPENED,
		Event::REVIEW_CLOSED,
		Event::CUSTOMER_SUBSCRIPTION_CREATED,
		Event::CUSTOMER_SUBSCRIPTION_UPDATED,
		Event::CUSTOMER_SUBSCRIPTION_DELETED,
		Event::CUSTOMER_SUBSCRIPTION_PAUSED,
		Event::CUSTOMER_SUBSCRIPTION_RESUMED,
		Event::CUSTOMER_SUBSCRIPTION_TRIAL_WILL_END,
		Event::INVOICE_PAYMENT_SUCCEEDED,
		Event::INVOICE_PAYMENT_FAILED,
		Event::INVOICE_PAYMENT_ACTION_REQUIRED,
		Event::INVOICE_PAID,
		Event::REFUND_CREATED,
		Event::ACCOUNT_UPDATED,
	);

	public function __construct() {
		$this->api_client = easycommerce_stripe_get_api_client();
	}

	/**
	 * List of events to register.
	 *
	 * @return string[]
	 */
	public function get_events(): array {
		return apply_filters( 'easycommerce_stripe_webhook_events', $this->events );
	}

	/**
	 * Deletes any existing webhook event destination matching the provided URL.
	 *
	 * @param string $webhook_url The webhook URL to match for deletion.
	 *
	 * @return void
	 * @throws ApiErrorException If an error occurs during deletion.
	 */
	protected function delete_existing_endpoint( string $webhook_url ): void {
		if ( ! $this->api_client ) {
			return;
		}

		$event_destinations = $this->api_client->v2->core->eventDestinations->all( array( 'include' => array( 'webhook_endpoint.url' ) ) );

		foreach ( $event_destinations->data as $event_destination ) {
			if ( $event_destination->webhook_endpoint->url !== $webhook_url ) {
				continue;
			}

			$this->api_client->v2->core->eventDestinations->delete( $event_destination->id );
		}
	}

	/**
	 * Creates a new event destination in Stripe and updates the signing secret if available.
	 *
	 * @param string $webhook_url The URL for the new webhook endpoint.
	 *
	 * @return void
	 * @throws ApiErrorException If an error occurs during creation.
	 */
	protected function create_new_endpoint( string $webhook_url ): void {
		if ( ! $this->api_client ) {
			return;
		}

		$params = array(
			'name'             => 'EasyCommerce Stripe Integration',
			'description'      => 'Webhook endpoint for EasyCommerce Stripe integration.',
			'type'             => 'webhook_endpoint',
			'event_payload'    => 'snapshot',
			'enabled_events'   => $this->get_events(),
			'include'          => array( 'webhook_endpoint.signing_secret', 'webhook_endpoint.url' ),
			'webhook_endpoint' => array( 'url' => $webhook_url ),
		);

		/**
		 * Filter the parameters for creating a new webhook endpoint.
		 *
		 * @param array $params The parameters for creating a new webhook endpoint.
		 */
		$params = apply_filters( 'easycommerce_stripe_webhook_endpoint_params', $params );

		$response = $this->api_client->v2->core->eventDestinations->create( $params );

		if ( empty( $response->webhook_endpoint->signing_secret ) ) {
			error_log( 'Failed to register and retrieve signing secret for webhook endpoint.' );
			return;
		}
		easycommerce_stripe_update_webhook_secret( $response->webhook_endpoint->signing_secret );
	}

	/**
	 * Registers a new event destination in the Stripe payment gateway.
	 *
	 * @see https://docs.stripe.com/webhooks
	 * @see https://docs.stripe.com/api/v2/core/event_destinations/create
	 *
	 * @return void
	 */
	public function register() {

		if ( ! $this->api_client ) {
			return;
		}

		$webhook_url = easycommerce_stripe_get_webhook_url();
		if ( ! easycommerce_stripe_is_valid_domain_url( $webhook_url ) ) {
			error_log( 'Invalid webhook URL detected: ' . $webhook_url . '. Stripe requires a publicly accessible HTTPS URL. For development, use a tunneling service like ngrok to expose your local server.' );

			return;
		}

		try {
			$this->delete_existing_endpoint( $webhook_url );
			$this->create_new_endpoint( $webhook_url );
		} catch ( Throwable $e ) {
			error_log( 'Error registering webhook: ' . $e->getMessage() );
		}
	}

	/**
	 * Deregisters the webhook endpoint from Stripe.
	 *
	 * @return void
	 */
	public function deregister(): void {
		if ( ! $this->api_client ) {
			return;
		}

		$webhook_url = easycommerce_stripe_get_webhook_url();
		if ( ! easycommerce_stripe_is_valid_domain_url( $webhook_url ) ) {
			return;
		}

		try {
			$this->delete_existing_endpoint( $webhook_url );
		} catch ( Throwable $e ) {
			error_log( 'Error deregistering webhook: ' . $e->getMessage() );
		}
	}
}

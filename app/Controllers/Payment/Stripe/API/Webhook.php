<?php

namespace EasyCommerce\Controllers\Payment\Stripe\API;

use EasyCommerce\Traits\Rest;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook as StripeWebhook;
use WP_Error;
use WP_REST_Request;

class Webhook {

	use Rest;

	/**
	 * Validates the incoming request to ensure it is a legitimate webhook event from Stripe.
	 * It checks for the presence of required parameters and verifies the request signature.
	 *
	 * @see https://docs.stripe.com/webhooks?snapshot-or-thin=thin#verify-webhook-signatures-with-official-libraries
	 *
	 * @param WP_REST_Request $request The incoming REST request to validate.
	 *
	 * @return Event|WP_Error
	 */
	public function validate_request( WP_REST_Request $request ) {
		if ( ! easycommerce_stripe_is_ready() || empty( easycommerce_stripe_get_webhook_secret() ) || ! easycommerce_stripe_get_api_client() ) {
			return new WP_Error( 'invalid_request', __( 'Invalid request.', 'easycommerce' ) );
		}

		$sig_header = $request->get_header( 'stripe-signature' );

		if ( empty( $sig_header ) ) {
			return new WP_Error( 'missing_signature', __( 'Missing Stripe signature.', 'easycommerce' ) );
		}

		try {
			return StripeWebhook::constructEvent(
				$request->get_body(),
				$sig_header,
				easycommerce_stripe_get_webhook_secret()
			);
		} catch ( SignatureVerificationException $e ) {
			return new WP_Error( 'invalid_signature', $e->getMessage() );
		}
	}

	/**
	 * Listen to webhook events.
	 *
	 * @see https://docs.stripe.com/webhooks#webhook-endpoint-def
	 *
	 * @return void
	 */
	public function listen( WP_REST_Request $request ) {
		$event = $this->validate_request( $request );
		if ( is_wp_error( $event ) ) {
			$this->response_error( $event->get_error_message() );
			return;
		}

		$handled = apply_filters( 'easycommerce_stripe_webhook_handle_event', false, $event, $this );

		if ( $handled ) {
			return;
		}

		switch ( $event->type ) {
			case Event::PAYMENT_INTENT_SUCCEEDED:
				$this->payment_intent_succeeded( $event );
				break;
			case Event::PAYMENT_INTENT_PAYMENT_FAILED:
				$this->payment_intent_failed( $event );
				break;
			default:
				do_action( 'easycommerce_stripe_webhook_received_unknown', $event );
				$this->response_success( 'Webhook received' );
				break;
		}

		do_action( 'easycommerce_stripe_webhook_received', $event );
	}

	/**
	 * Handle successful payment intent
	 *
	 * @param Event $event The event object.
	 *
	 * @return void
	 */
	public function payment_intent_succeeded( $event ) {
		$payment_intent = $event->data->object;

		do_action( 'easycommerce_stripe_payment_intent_succeeded', $payment_intent, $event );

		$this->response_success( 'Payment intent succeeded' );
	}

	/**
	 * Handle failed payment intent
	 *
	 * @param Event $event The event object.
	 *
	 * @return void
	 */
	public function payment_intent_failed( $event ) {
		$payment_intent = $event->data->object;

		do_action( 'easycommerce_stripe_payment_intent_failed', $payment_intent, $event );

		$this->response_success( 'Payment intent failed' );
	}
}

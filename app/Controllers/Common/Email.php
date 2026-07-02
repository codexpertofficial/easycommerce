<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Order;
use EasyCommerce\Traits\Hook;
use EasyCommerce\Helpers\Email as Emailer;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Product;
use EasyCommerce\Abstracts\User;


class Email {

	use Hook;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		// the triggerer
		$this->action( 'easycommerce_email', array( $this, 'shoot' ), 10, 4 );

		// email events
		$this->action( 'easycommerce_user_created', array( $this, 'remind_password_reset' ), 10, 2 );
		$this->action( 'easycommerce_order_email', array( $this, 'order_email' ), 10, 2 );
		$this->action( 'easycommerce_send_abandoned_reminder', array( $this, 'send_abandoned_reminder' ) );
	}

	/**
	 * The sender
	 */
	public function shoot( $recipient, $subject, $body, $placeholders = array() ) {
		$email = new Emailer();

		$email->set_title( $subject );
		$email->set_body( $body );
		$email->set_subject( $subject );
		$email->add_recipient( $recipient );
		$email->set_placeholders( $placeholders );

		$sent = $email->send();

		remove_all_filters( 'easycommerce_mail_sent' );
		add_filter(
			'easycommerce_mail_sent',
			function ( $is_sent ) use ( $sent ) {
				return $sent;
			}
		);
	}

	public function remind_password_reset( $user_id, User $user ) {
		$admin_email  = Utility::get_option( 'general', 'business', 'business_email', '' ) ?: get_option( 'admin_email' );
		$placeholders = [
			'##customer_name##'       => $user->get_name(),
			'##customer_email##'      => $user->get_email(),
			'##registration_date##'   => $user->get_join_date(),
			'##password_reset_link##' => easycommerce_dashboard_page( true ) . '#password',
		];

		// Email to the customer
		if (
			Utility::get_option( 'email', 'new_account', 'customer_enabled', false )
			&& ! empty( $customer_subject = Utility::get_option( 'email', 'new_account', 'customer_subject', '' ) )
			&& ! empty( $customer_body = Utility::get_option( 'email', 'new_account', 'customer_body', '' ) )
		) {
			do_action( 'easycommerce_email', $user->get_email(), $customer_subject, $customer_body, $placeholders );
		}

		// Email to the admin
		if (
			Utility::get_option( 'email', 'new_account', 'admin_enabled', false )
			&& ! empty( $admin_subject = Utility::get_option( 'email', 'new_account', 'admin_subject', '' ) )
			&& ! empty( $admin_body = Utility::get_option( 'email', 'new_account', 'admin_body', '' ) )
		) {
			do_action( 'easycommerce_email', $admin_email, $admin_subject, $admin_body, $placeholders );
		}
	}

	public function order_email( $event, $order_id ) {

		$order        = new Order( $order_id );
		$customer     = new Customer( $order->get_customer_id() );
		$admin_email  = Utility::get_option( 'general', 'business', 'business_email', '' ) ?: get_option( 'admin_email' );
		$placeholders = easycommerce_order_placeholders( $order_id );

		// Email to the customer
		if (
			Utility::get_option( 'email', $event, 'customer_enabled', false )
			&& ! empty( $customer->get_email() )
			&& ! empty( $customer_subject = Utility::get_option( 'email', $event, 'customer_subject', '' ) )
			&& ! empty( $customer_body = Utility::get_option( 'email', $event, 'customer_body', '' ) )
		) {
			do_action( 'easycommerce_email', $customer->get_email(), $customer_subject, $customer_body, $placeholders );
		}

		// Email to the admin
		if (
			Utility::get_option( 'email', $event, 'admin_enabled', false )
			&& ! empty( $admin_subject = Utility::get_option( 'email', $event, 'admin_subject', '' ) )
			&& ! empty( $admin_body = Utility::get_option( 'email', $event, 'admin_body', '' ) )
		) {
			do_action( 'easycommerce_email', $admin_email, $admin_subject, $admin_body, $placeholders );
		}
	}

	public function send_abandoned_reminder( $hash ) {

		$subject = Utility::get_option( 'abandoned-cart', 'settings', 'subject', '' );
		$body    = Utility::get_option( 'abandoned-cart', 'settings', 'body', '' );

		if ( in_array( '', array( $subject, $body ) ) ) {
			return;
		}

		$cart           = new Cart( $hash );
		$customer_email = $cart->get_customer_email();

		if ( empty( $customer_email ) ) {
			return;
		}

		$placeholders = easycommerce_cart_placeholders( $cart );

		do_action( 'easycommerce_email', $customer_email, $subject, $body, $placeholders );
	}
}

<?php
namespace EasyCommerce\Controllers\Front;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Cleaner;

class Shortcode {

	use Hook;
	use Asset;
	use Cleaner;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->shortcode( 'easycommerce-login', array( $this, 'login' ) );
		$this->shortcode( 'easycommerce-register', array( $this, 'register' ) );
		$this->shortcode( 'easycommerce-reset', array( $this, 'reset_password' ) );
		$this->shortcode( 'easycommerce-checkout', array( $this, 'checkout' ) );
		$this->shortcode( 'easycommerce-dashboard', array( $this, 'dashboard' ) );
		$this->shortcode( 'easycommerce-payment', array( $this, 'payment' ) );
	}

	/**
	 * Markup for the React auth SPA mount point.
	 *
	 * The login/registration/reset-password screens are rendered by the React
	 * `auth` bundle which mounts into this container. The initial screen is
	 * passed via data-screen so a directly-loaded shortcode page still opens on
	 * the expected screen; in-app navigation is handled by the hash router.
	 *
	 * @param string $screen One of `login`, `register`, `reset`.
	 * @return string
	 */
	private function auth_container( $screen = 'login' ) {
		return sprintf(
			'<div id="easycommerce_auth_render" class="easycommerce-auth" data-screen="%s"></div>',
			esc_attr( $screen )
		);
	}

	/**
	 * Panel shown when a logged-in user lands on an auth screen.
	 */
	private function already_logged_in( $title, $message ) {
		return sprintf(
			'<div class="easycommerce-auth-complete text-center !max-w-[620px] mx-auto !my-20 bg-white py-[77px] px-8 rounded-xl">
				<h3 class="!m-0 !font-inter !font-semibold text-2xl text-ec-body">%s</h3>
				<p class="!m-0 font-inter font-medium text-base leading-[26px] text-ec-placeholder">%s</p>
			</div>',
			esc_html( $title ),
			esc_html( $message )
		);
	}

	public function reset_password() {
		if ( is_user_logged_in() ) {
			return $this->already_logged_in(
				__( 'You are already logged in.', 'easycommerce' ),
				__( 'You cannot reset your password while logged in.', 'easycommerce' )
			);
		}
		return $this->auth_container( 'reset' );
	}


	public function login() {
		if ( is_user_logged_in() ) {
			return $this->already_logged_in(
				__( 'You are already logged in.', 'easycommerce' ),
				__( 'You are signed in to your account.', 'easycommerce' )
			);
		}
		return $this->auth_container( 'login' );
	}

	public function register() {
		if ( is_user_logged_in() ) {
			return $this->already_logged_in(
				__( 'You are already registered.', 'easycommerce' ),
				__( 'You are already logged in.', 'easycommerce' )
			);
		}
		return $this->auth_container( 'register' );
	}

	/**
	 * Render the checkout, dispatching to the template chosen in settings.
	 *
	 * The active template comes from Settings -> Checkout ("checkout_template").
	 * Note that template-2 is the intentionally compact checkout: it collects
	 * only name, email and country and does not gather a full shipping/billing
	 * address (see views/shortcodes/checkout/template-2.php). That is by design
	 * for low-friction / digital-goods stores, not a missing-address bug.
	 */
	public function checkout( $atts ) {

		$store_mode = Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test';

		if ( $store_mode === 'test' && ! current_user_can( 'manage_options' ) ) {
			return Utility::get_template( 'templates/store-mode.php' );
		}

		$templates = $this->checkout_templates();

		$atts = shortcode_atts(
			array(
				'template' => Utility::get_option( 'checkout', 'settings', 'checkout_template', 'template-1' ),
				'columns'  => Utility::get_option( 'checkout', 'settings', 'columns' ),
			),
			$atts,
			'easycommerce-checkout'
		);

		// An unknown template resolves to no file, which renders a blank checkout.
		if ( ! in_array( $atts['template'], $templates, true ) ) {
			$atts['template'] = 'template-1';
		}

		$template = $atts['template'];

		return Utility::get_template( "shortcodes/checkout/{$template}.php", array( 'atts' => $atts ) );
	}

	/**
	 * Checkout template slugs that have a file to render.
	 *
	 * @return array
	 */
	private function checkout_templates() {
		return apply_filters( 'easycommerce_checkout_template_slugs', array_keys( easycommerce_checkout_templates() ) );
	}

	public function dashboard( $atts ) {

		$store_mode = Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test';

		if ( $store_mode === 'test' && ! current_user_can( 'manage_options' ) ) {
			return Utility::get_template( 'templates/store-mode.php' );
		}

		$atts = shortcode_atts( array( 'template' => 'template-1' ), $atts, 'easycommerce-dashboard' );

		$templates = array( 'template-1', 'template-2', 'template-3' );

		if ( is_user_logged_in() ) {
			if ( in_array( $atts['template'], $templates ) ) {
				return sprintf( '<div id="easycommerce_dashboard_render" class="%s"></div>', $atts['template'] );
			}
		} else {
			return $this->auth_container( 'login' );
		}
	}

	public function payment() {
		return Utility::get_template( 'shortcodes/payment/template-1.php' );
	}
}

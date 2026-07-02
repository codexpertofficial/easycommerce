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

	public function reset_password() {
		if ( is_user_logged_in() ) {
			return sprintf(
				'<div class="easycommerce-reset-complete text-center !max-w-[620px] mx-auto !my-20 bg-white py-[77px] px-8 rounded-xl">
					<h3 class="!m-0 !font-inter !font-semibold text-2xl text-ec-body">%s</h3>
					<p class="!m-0 font-inter font-medium text-base leading-[26px] text-ec-placeholder">%s</p>
				</div>',
				__( 'You are already logged in.', 'easycommerce' ),
				__( 'You cannot reset your password while logged in.', 'easycommerce' )
			);
		}
		return Utility::get_template( 'shortcodes/reset/template-1.php' );
	}


	public function login() {

		echo isset( $_GET['registration'] ) ? __( 'Registration Complete please login', 'easycommerce' ) : '';

		ob_start();

		wp_login_form(
			array(
				'form_id'        => 'easycommerce-login-form',
				'label_username' => __( 'Email Address', 'easycommerce' ),
				'label_password' => __( 'Password', 'easycommerce' ),
				'label_remember' => __( 'Remember Me', 'easycommerce' ),
				'label_log_in'   => __( 'Sign In', 'easycommerce' ),
				'remember'       => true,
				'value_remember' => true,
			)
		);

		$form_output = ob_get_clean();

		// Add "required" attributes to username & password fields
		$form_output = str_replace(
			['id="user_login"', 'id="user_pass"'],
			['id="user_login" required', 'id="user_pass" required'],
			$form_output
		);

		// Add "Forgot Password?" link
		$forgot_password_url  = easycommerce_reset_password_page( true );
		$forgot_password_link = sprintf(
			'<p class="forgot-password"><a class="text-ec-placeholder font-inter font-normal text-base leading-[26px] hover:!text-royal-purple focus:text-royal-purple !no-underline" href="%1$s">%2$s</a></p>',
			esc_url( $forgot_password_url ),
			__( 'Forgot Password?', 'easycommerce' )
		);

		$register_page_id  = easycommerce_registration_page( true );
		$reset_password_url = easycommerce_reset_password_page( true );

		$form_html = sprintf(
			'<div class="easycommerce-login-form-wrapper !max-w-[620px] mx-auto !my-20 bg-white py-[77px] px-8 rounded-xl">
				<div class="easycommerce-login-form-header mb-8 flex flex-col gap-2">
					<h3 class="!m-0 !font-inter !font-semibold text-2xl text-ec-body">%1$s</h3>
					<p class="!m-0 font-inter font-medium text-base leading-[26px] text-ec-placeholder">%2$s</p>
				</div>

				<div class="easycommerce-login-form-body">
					%3$s
				</div>

				<div class="easycommerce-login-form-footer flex justify-center items-center gap-2">
					<div class="easycommerce-login-form-footer">
						%4$s
						<a href="%5$s" class="font-inter text-royal-purple font-normal text-base leading-[26px] hover:!text-royal-purple focus:text-royal-purple !no-underline">%6$s</a>
					</div>
				</div>
			</div>',
			__( 'Sign In', 'easycommerce' ),
			__( 'Welcome back! sign in and let the greenery spark your joy', 'easycommerce' ),
			$form_output . $forgot_password_link,
			__( 'Don\'t have an account?', 'easycommerce' ),
			esc_url( $register_page_id ),
			__( 'Sign Up', 'easycommerce' )
		);

		return $form_html;
	}

	public function register() {
		if ( is_user_logged_in() ) {
			return sprintf(
				'<div class="easycommerce-registration-complete text-center !max-w-[620px] mx-auto !my-20 bg-white py-[77px] px-8 rounded-xl">
					<h3 class="!m-0 !font-inter !font-semibold text-2xl text-ec-body">%s</h3>
					<p class="!m-0 font-inter font-medium text-base leading-[26px] text-ec-placeholder">%s</p>
				</div>',
				__( 'You are already registered.', 'easycommerce' ),
				__( 'You are already logged in.', 'easycommerce' )
			);
		} else {
			return Utility::get_template( 'shortcodes/register/template-1.php' );
		}
	}

	public function checkout( $atts ) {

		$store_mode = Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test';

		if ( $store_mode === 'test' && ! current_user_can( 'manage_options' ) ) {
			return Utility::get_template( 'templates/store-mode.php' );
		}

		$template 			= Utility::get_option( 'checkout', 'settings', 'checkout_template', 'template-1' );
		$columns  			= Utility::get_option( 'checkout', 'settings', 'columns' );
		$atts     			= array( 'template' => $template, 'columns'  => $columns );

		return Utility::get_template( "shortcodes/checkout/{$template}.php", array( 'atts' => $atts ) );

		return sprintf(
			__( 'Invlaid template <code>%1$s</code> used. Valid options are: <code>%2$s</code>.', 'easycommerce' ),
			$atts['template'],
			implode( ', ', $template )
		);
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
			echo do_shortcode( '[easycommerce-login]' );
		}
	}

	public function payment() {
		return Utility::get_template( 'shortcodes/payment/template-1.php' );
	}
}

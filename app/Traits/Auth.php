<?php
namespace EasyCommerce\Traits;

defined( 'ABSPATH' ) || exit;

trait Auth {

	/**
	 * Check if sandbox/test mode is enabled.
	 *
	 * @return bool True if sandbox mode is enabled, false otherwise.
	 */
	protected function is_sandbox_mode() {
		return defined( 'EASYCOMMERCE_SANDBOX' ) && EASYCOMMERCE_SANDBOX;
	}

	/**
	 * Verifies if it's a human user, not bots
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool True for regular cases, false otherwise.
	 *
	 * @todo Introduce real check
	 */
	public function is_user( $request ) {
		return __return_true();
	}

	/**
	 * Check if the request is from a logged-in user or carries a valid wp_rest nonce.
	 *
	 * Used for storefront endpoints that incur real costs (e.g. AI credits) but must
	 * remain accessible to guest users who loaded the storefront and received a nonce
	 * via EASYCOMMERCE.nonce. Raw unauthenticated API calls without a nonce are rejected.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool
	 */
	public function is_nonce_verified( $request ) {
		if ( is_user_logged_in() ) {
			return true;
		}

		$nonce = $request->get_header( 'x-wp-nonce' ) ?: $request->get_param( '_wpnonce' );

		return ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wp_rest' ) !== false;
	}

	/**
	 * Check if the current user is a guest (not logged in).
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool True if sandbox mode is disabled and the user is not logged in, false otherwise.
	 */
	public function is_guest( $request ) {
		return ! $this->is_sandbox_mode() && ! is_user_logged_in();
	}

	/**
	 * Check if the current user is a logged in user.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool True if sandbox mode is enabled or the user is logged in, false otherwise.
	 */
	public function is_member( $request ) {
		return $this->is_sandbox_mode() || is_user_logged_in();
	}

	/**
	 * Check if the current user is a customer.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool True if sandbox mode is enabled or the user is logged in, false otherwise.
	 */
	public function is_customer( $request ) {
		return $this->is_sandbox_mode() || $this->is_admin( $request ) || current_user_can( 'customer' ) || current_user_can( 'has_order' );
	}

	/**
	 * Check if the current user is an editor.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool True if sandbox mode is enabled or the user has editor capabilities, false otherwise.
	 */
	public function is_editor( $request ) {
		return $this->is_sandbox_mode() || current_user_can( 'editor' );
	}

	/**
	 * Check if the current user is an administrator.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool True if sandbox mode is enabled or the user has administrator capabilities, false otherwise.
	 */
	public function is_admin( $request ) {
		return $this->is_sandbox_mode() || current_user_can( 'administrator' );
	}

	/**
	 * Check if the current user is an administrator or an manager.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool True if sandbox mode is enabled or the user has administrator capabilities or user has manager capabilities, false otherwise.
	 */
	public function is_manager( $request ) {
		return $this->is_sandbox_mode() || current_user_can( 'administrator' ) || current_user_can( 'manager' );
	}
}

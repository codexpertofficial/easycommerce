<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;

class Option extends API {

	/**
	 * Get the value of a specified option.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get( $request ) {
		$key = $request->get_param( 'key' );

		if ( empty( $key ) ) {
			$this->response_error( array( 'message' => __( 'Option key is required.', 'easycommerce' ) ) );
		}

		if ( strpos( $key, 'easycommerce' ) !== 0 ) {
			$this->response_error( array( 'message' => __( 'Option key is not allowed.', 'easycommerce' ) ) );
		}

		$value = get_option( $key );

		if ( empty( $value ) ) {
			$this->response_success( array( 'message' => __( 'Option not found.', 'easycommerce' ) ) );
		}

		/**
		 * Filters the option value before sending the response.
		 *
		 * @since 1.9
		 * @param mixed $value The option value.
		 * @param string $key The option key.
		 * @param WP_REST_Request $request The request object.
		 */
		$value = apply_filters( 'easycommerce_get_option', $value, $key, $request );

		$this->response_success( $value );
	}

	/**
	 * Update the value of a specified option.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( $request ) {
		$key   = $request->get_param( 'key' );
		$value = $request->get_param( 'value' );

		if ( empty( $key ) ) {
			$this->response_error( __( 'Option key is required.', 'easycommerce' ) );
		}

		if ( strpos( $key, 'easycommerce' ) !== 0 ) {
			$this->response_error( __( 'Option key is not allowed.', 'easycommerce' ) );
		}

		update_option( $key, $value );

		/**
		 * Fires after an option is updated.
		 *
		 * @since 1.9
		 * @param string $key   The option key.
		 * @param mixed  $value The option value.
		 */
		do_action( 'easycommerce_option_updated', $key, $value );

		$this->response_success( __( 'Settings Saved Successfully.', 'easycommerce' ) );
	}

	/**
	 * Delete the specified option.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$key = $request->get_param( 'key' );

		if ( empty( $key ) ) {
			$this->response_error( __( 'Option key is required.', 'easycommerce' ) );
		}

		if ( strpos( $key, 'easycommerce' ) !== 0 ) {
			$this->response_error( __( 'Option key is not allowed.', 'easycommerce' ) );
		}

		/**
		 * Fires before an option is deleted.
		 *
		 * @since 1.9
		 * @param string $key The option key.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_option', $key, $request );

		$deleted = delete_option( $key );

		/**
		 * Fires after an option is deleted.
		 *
		 * @since 1.9
		 * @param string $key The option key.
		 * @param bool $deleted Whether the option was successfully deleted.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_option', $key, $deleted, $request );

		if ( ! $deleted ) {
			// $this->response_error( __( 'Failed to delete option.', 'easycommerce' ) );
		}

		$this->response_success( __( 'Settings Reset successfully.', 'easycommerce' ) );
	}
}

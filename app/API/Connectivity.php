<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Traits\Queue;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Product;

class Connectivity extends API {

	use Cache;
	use Queue;


	public function save( $request ) {
		$grouped = array();
		$type    = $request->get_param( 'type' );
		$data    = $request->get_param( 'data' );

		// Flush rewrite rules
		flush_rewrite_rules();

		// Set a flag
		update_option( 'easycommerce-setup_wizard', 1 );

		if ( ! empty( $data['general-business-logo'] ) ) {
			$data['general-business-logo'] = attachment_url_to_postid( $data['general-business-logo'] );
		} else {
			unset( $data['general-business-logo'] );
		}

		foreach ( $data as $key => $value ) {
			list( $section, $sub, $field )            = explode( '-', $key, 3 );
			$grouped[ "{$section}-{$sub}" ][ $field ] = $value;
		}

		if ( 'store' === $type ) {
			// Pages are created in the background on install; ensure they exist
			// here too (idempotent) so finishing the wizard never leaves the
			// store without its core pages. Dropdowns were removed, so any
			// submitted group only carries incidental keys (e.g. page-template).
			easycommerce_ensure_store_pages( $grouped['general-store'] ?? array() );
		}

		if ( isset( $grouped['general-store'] ) ) {
			unset( $grouped['general-store'] );
		}

		foreach ( $grouped as $option_key => $values ) {
			$existing = get_option( "easycommerce-{$option_key}", array() );

			if ( empty( array_filter( $values ) ) ) {
				delete_option( "easycommerce-{$option_key}" );
				continue;
			}

			if ( empty( $values['active_methods'] ) && ! empty( $existing['active_methods'] ) ) {
				$values['active_methods'] = $existing['active_methods'];
			}
			update_option( "easycommerce-{$option_key}", $values );
		}

		if ( isset( $data['dont_share_data'] ) ) {
			update_option( '_easycommerce-no_tracking', 1 );
		}

		if ( isset( $data['payment-methods-active_methods'] ) && is_array( $data['payment-methods-active_methods'] ) ) {
			$active_methods  = $data['payment-methods-active_methods'];
			$payment_methods = easycommerce_get_all_payment_methods();
			foreach ( $active_methods as $addon ) {
				if ( isset( $payment_methods[ $addon ] ) && $payment_methods[ $addon ]['is_addon'] ) {
					$addon_slug = 'easycommerce-' . $addon;
					$this->schedule( 'easycommerce_install_addon', array( 'slug' => $addon_slug ) );
				}
			}
		}

		if ( isset( $data['woocommerce_migration'] ) ) {
			$this->schedule( 'easycommerce_install_addon', array( 'slug' => 'easycommerce-migration' ) );
		}

		$country          = easycommerce_business_country();
		$business_country = array(
			'countries' => ! empty( $country ) ? (array) $country : array(),
		);
		update_option( 'easycommerce-tax-settings', $business_country );
		update_option( 'easycommerce-shipping-settings', $business_country );

		/**
		 * Fires after connectivity settings are saved.
		 *
		 * @since 1.9
		 * @param array $grouped The grouped settings data.
		 * @param string $type The type of settings.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_connectivity_saved', $grouped, $type, $request );

		$this->response_success(
			array(
				'updated' => true,
				'message' => __( 'Store data updated', 'easycommerce' ),
			)
		);
	}

	public function registration( $request ) {

		$errors           = new \WP_Error();
		$username         = $request->get_param( 'username' );
		$email            = $request->get_param( 'email' );
		$new_password     = $request->get_param( 'password' );
		$confirm_password = $request->get_param( 'confirmPassword' );

		if ( empty( $username ) || empty( $email ) || empty( $new_password ) || empty( $confirm_password ) ) {
			$errors->add( 'field', __( 'Required form field is missing', 'easycommerce' ) );
		}
		if ( username_exists( $username ) ) {
			$errors->add( 'username_exists', __( 'Username already exists', 'easycommerce' ) );
		}
		if ( ! is_email( $email ) ) {
			$errors->add( 'email_invalid', __( 'Email is not valid', 'easycommerce' ) );
		}
		if ( email_exists( $email ) ) {
			$errors->add( 'email_exists', __( 'Email already registered', 'easycommerce' ) );
		}
		if ( $new_password !== $confirm_password ) {
			$errors->add( 'password_mismatch', __( 'Passwords do not match', 'easycommerce' ) );
		}
		if ( strlen( (string) $new_password ) < 6 ) {
			$errors->add( 'password_too_short', __( 'Password must be at least 6 characters long.', 'easycommerce' ) );
		}
		if ( empty( $errors->get_error_messages() ) ) {
			/**
			 * Fires before user registration.
			 *
			 * @since 1.9
			 * @param string $username The username.
			 * @param string $email The email.
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'easycommerce_before_user_registration', $username, $email, $request );

			$user_id = wp_create_user( $username, $confirm_password, $email );

			if ( ! is_wp_error( $user_id ) ) {
				$user = new \WP_User( $user_id );
				$user->set_role( 'customer' );

				/**
				 * Fires after user registration.
				 *
				 * @since 1.9
				 * @param int $user_id The user ID.
				 * @param WP_REST_Request $request The request object.
				 */
				do_action( 'easycommerce_after_user_registration', $user_id, $request );

				wp_send_json_success(
					array( 'redirect_url' => add_query_arg( array( 'registration' => '1' ), get_permalink( easycommerce_dashboard_page() ) ) ),
					200
				);
			}
		} else {
			foreach ( $errors->get_error_messages() as $error ) {
				wp_send_json_error( array( 'message' => $error ) );
			}
		}
	}

	/**
	 * Handle reset password request
	 */
	public function reset_password_request( $request ) {
		$user_login = sanitize_text_field( $request->get_param( 'user_login' ) );

		if ( empty( $user_login ) ) {
			wp_send_json_error( array( 'message' => __( 'Enter a username or email address.', 'easycommerce' ) ), 400 );
			return;
		}

		$user_data = strpos( $user_login, '@' )
			? get_user_by( 'email', trim( wp_unslash( $user_login ) ) )
			: get_user_by( 'login', trim( wp_unslash( $user_login ) ) );

		// Send the reset email only when the account exists, but always return the
		// same response either way so this endpoint cannot be used to discover
		// which emails/usernames are registered (user enumeration).
		if ( ! empty( $user_data ) ) {
			$key = get_password_reset_key( $user_data );

			if ( ! is_wp_error( $key ) ) {
				send_reset_password_email( $user_data, $key );
			}
		}

		wp_send_json_success(
			array(
				'message' => __( 'If an account matches that email or username, a password reset link has been sent.', 'easycommerce' ),
			),
			200
		);
	}

	/**
	 * Handle reset password confirmation
	 */
	public function reset_password_confirm( $request ) {
		$errors           = new \WP_Error();
		$key              = sanitize_text_field( $request->get_param( 'key' ) );
		$login            = sanitize_text_field( $request->get_param( 'login' ) );
		$password         = $request->get_param( 'password' );
		$confirm_password = $request->get_param( 'confirm_password' );

		if ( empty( $key ) || empty( $login ) ) {
			$errors->add( 'invalid_key', __( 'Invalid key or login.', 'easycommerce' ) );
		}

		if ( empty( $password ) || empty( $confirm_password ) ) {
			$errors->add( 'empty_password', __( 'Password fields cannot be empty.', 'easycommerce' ) );
		}

		if ( $password !== $confirm_password ) {
			$errors->add( 'password_mismatch', __( 'Passwords do not match.', 'easycommerce' ) );
		}

		if ( strlen( $password ) < 6 ) {
			$errors->add( 'password_too_short', __( 'Password must be at least 6 characters long.', 'easycommerce' ) );
		}

		if ( ! empty( $errors->get_error_messages() ) ) {
			foreach ( $errors->get_error_messages() as $error ) {
				wp_send_json_error( array( 'message' => $error ), 400 );
			}
			return;
		}

		// Check reset key
		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid or expired reset key.', 'easycommerce' ),
				),
				400
			);
			return;
		}
		reset_password( $user, $password );

		wp_send_json_success(
			array(
				'message'      => __( 'Password has been reset successfully.', 'easycommerce' ),
				'redirect_url' => add_query_arg( array( 'reset' => 'success' ), get_permalink( easycommerce_dashboard_page() ) ),
			),
			200
		);
	}

	/**
	 * Authenticate a user for the React auth screens.
	 *
	 * Mirrors the native wp_login_form behaviour (which posts to wp-login.php)
	 * but returns JSON so the storefront can log in without a full page reload
	 * being driven by the browser's form submit. On success wp_signon sets the
	 * auth cookies, and the client redirects to the dashboard.
	 */
	public function login( $request ) {
		$user_login    = trim( (string) $request->get_param( 'user_login' ) );
		$user_password = (string) $request->get_param( 'password' );
		$remember      = filter_var( $request->get_param( 'remember' ), FILTER_VALIDATE_BOOLEAN );

		if ( empty( $user_login ) || empty( $user_password ) ) {
			wp_send_json_error( array( 'message' => __( 'Email/username and password are required.', 'easycommerce' ) ), 400 );
			return;
		}

		// Throttle repeated failed logins per client IP to blunt brute-force /
		// credential-stuffing. Set the max-attempts filter to 0 to disable.
		$max_attempts = (int) apply_filters( 'easycommerce_login_max_attempts', 10 );
		$window       = (int) apply_filters( 'easycommerce_login_attempt_window', 15 * MINUTE_IN_SECONDS );
		$throttle_key = $this->login_throttle_key();
		$attempts     = (int) get_transient( $throttle_key );

		if ( $max_attempts > 0 && $attempts >= $max_attempts ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many failed login attempts. Please try again in a few minutes.', 'easycommerce' ) ),
				429
			);
			return;
		}

		/**
		 * Fires before a storefront login attempt.
		 *
		 * The plaintext password is deliberately NOT passed to listeners.
		 *
		 * @since 1.32
		 * @param string $user_login The username or email.
		 */
		do_action( 'easycommerce_before_user_login', $user_login );

		$user = wp_signon(
			array(
				'user_login'    => $user_login,
				'user_password' => $user_password,
				'remember'      => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			if ( $max_attempts > 0 ) {
				set_transient( $throttle_key, $attempts + 1, $window );
			}

			wp_send_json_error(
				array( 'message' => __( 'Invalid email/username or password.', 'easycommerce' ) ),
				401
			);
			return;
		}

		// A successful login clears this client's failed-attempt counter.
		delete_transient( $throttle_key );

		wp_set_current_user( $user->ID );

		/**
		 * Fires after a successful storefront login.
		 *
		 * @since 1.32
		 * @param WP_User $user The logged-in user.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_user_login', $user, $request );

		$redirect = get_permalink( easycommerce_dashboard_page() );

		wp_send_json_success(
			array(
				'redirect_url' => $redirect ? $redirect : home_url(),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
			),
			200
		);
	}

	/**
	 * Best-effort client IP for login throttling.
	 *
	 * Uses REMOTE_ADDR only (proxy headers are spoofable and must not be trusted
	 * for a security control); sites behind a trusted proxy can override via the
	 * easycommerce_client_ip filter.
	 *
	 * @return string
	 */
	private function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return (string) apply_filters( 'easycommerce_client_ip', $ip );
	}

	/**
	 * Transient key holding the failed-login count for the current client.
	 *
	 * @return string
	 */
	private function login_throttle_key() {
		return 'easycommerce_login_fails_' . md5( $this->client_ip() );
	}

	public function get_setup_wizard( $request ) {
		$data = array(
			'currency'        => '',
			'format'          => '',
			'business_type'   => '',
			'payment_methods' => array(),
			'checkout'        => '',
			'dashboard'       => '',
			'shop'            => '',
			'import_demo'     => false,
			'name'            => '',
			'email'           => '',
			'subscribe'       => false,
			'country'		  => easycommerce_get_user_country(),
			'store_name'      => html_entity_decode( get_option( 'blogname' ) ),
			'logo'            => null,
		);

		$store_data       = get_option( 'easycommerce-general-store', array() );
		$business_data    = get_option( 'easycommerce-general-business', array() );
		$pm_methods       = get_option( 'easycommerce-payment-methods', array() );
		$payments_pricing = get_option( 'easycommerce-payment-pricing', array() );

		if ( ! empty( $business_data ) ) {
			$data = array_merge( $data, $business_data );
		}

		if ( ! empty( $data['logo'] ) && is_numeric( $data['logo'] ) ) {
			$data['logo'] = wp_get_attachment_url( $data['logo'] );
		}

		if ( ! empty( $pm_methods ) ) {
			$data['payment_methods'] = $pm_methods['active_methods'];
		}

		if ( ! empty( $payments_pricing ) ) {
			$data = array_merge( $data, $payments_pricing );
		}

		if ( ! empty( $store_data ) ) {
			$data = array_merge( $data, $store_data );
		}

		if ( ! $data || empty( $data ) ) {
			$this->response_success( array( 'message' => __( 'No data found', 'easycommerce' ) ) );
		}

		// Ready-made store designs for the Store Setup step (#3174).
		$designs = array();
		foreach ( easycommerce_store_designs() as $design_id => $design ) {
			$designs[] = array(
				'id'          => $design_id,
				'label'       => $design['label'] ?? $design_id,
				'description' => $design['description'] ?? '',
				'screenshot'  => $design['screenshot'] ?? '',
			);
		}
		$data['designs'] = $designs;
		$data['design']  = get_option( 'easycommerce_store_design', '' );

		// Whether the site already has a static front page — the wizard uses this
		// to default the "set as homepage" opt-in OFF on existing/live sites.
		$data['has_static_front'] = ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) > 0 );

		// Whether the store already has any products (any status) — the wizard
		// only auto-checks "import demo products" on design select when the store
		// is empty. Mirrors the demo importer's own empty-store guard in
		// Process::import_sample_products().
		$product_counts = wp_count_posts( 'product' );
		$product_total  = 0;
		if ( $product_counts ) {
			foreach ( (array) $product_counts as $count ) {
				$product_total += (int) $count;
			}
		}
		$data['has_products'] = $product_total > 0;

		/**
		 * Filters the setup wizard data.
		 *
		 * @since 1.9
		 * @param array $data The setup data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_get_setup_wizard_data', $data, $request );

		$this->response_success( array( 'data' => $data ) );
	}

	/**
	 * Apply a ready-made store design from the setup wizard (#3174).
	 *
	 * By default this only *creates* the design's pages and leaves the site's
	 * front page untouched — safest for existing/live sites. The new home page
	 * is set as the static front page ONLY when the store owner explicitly opts
	 * in (`set_homepage`), so we never silently clobber a live homepage.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function apply_design( $request ) {
		$design_id    = sanitize_key( (string) $request->get_param( 'design_id' ) );
		$set_homepage = (bool) $request->get_param( 'set_homepage' );

		// Skipping is a no-op — current behaviour (pages already created in the
		// background per #3170) is left untouched.
		if ( '' === $design_id || 'skip' === $design_id ) {
			return $this->response_success( array( 'skipped' => true ) );
		}

		$group = easycommerce_apply_store_design( $design_id );

		if ( false === $group ) {
			return $this->response_error( __( 'Unknown store design.', 'easycommerce' ), 400 );
		}

		$home_id   = isset( $group['home'] ) ? (int) $group['home'] : 0;
		$front_set = false;

		// Only touch the front page on an explicit opt-in.
		if ( $home_id && $set_homepage ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
			$front_set = true;
		}

		return $this->response_success(
			array(
				'design_id' => $design_id,
				'home_id'   => $home_id,
				'home_url'  => $home_id ? get_permalink( $home_id ) : home_url( '/' ),
				'front_set' => $front_set,
			)
		);
	}

	/**
	 * Set a page as the static front page (used to confirm an overwrite from the
	 * wizard after the user opts in). #3174.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function set_front_page( $request ) {
		$page_id = (int) $request->get_param( 'page_id' );

		if ( ! $page_id || 'page' !== get_post_type( $page_id ) ) {
			return $this->response_error( __( 'Invalid page.', 'easycommerce' ), 400 );
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );

		return $this->response_success(
			array(
				'home_url' => get_permalink( $page_id ),
			)
		);
	}

	public function check( $request ) {
		if ( $api = get_option( 'easycommerce_api' ) ) {
			/**
			 * Filters the API check response data.
			 *
			 * @since 1.9
			 * @param array $api The API data.
			 * @param WP_REST_Request $request The request object.
			 */
			$api = apply_filters( 'easycommerce_connectivity_check_api', $api, $request );

			$this->response_success(
				array(
					'connected' => true,
					'user'      => $api,
				)
			);
		}

		if ( apply_filters( 'easycommerce-pro_licensed', false ) ) {
			$current_user = wp_get_current_user();
			$user         = (object) array(
				'name'  => $current_user->display_name,
				'email' => $current_user->user_email,
				'photo' => get_avatar_url( $current_user->ID ),
			);

			$this->response_success(
				array(
					'connected' => true,
					'user'      => $user,
				)
			);
		}

		$this->response_success(
			array(
				'connected' => false,
				'message'   => __( 'API not connected', 'easycommerce' ),
			)
		);
	}

	public function disconnect( $request ) {

		delete_option( 'easycommerce_api' );

		// Drop the cached AI plan/credit state so a later reconnect (possibly a
		// different account) does not show the old account's stale numbers.
		delete_option( 'easycommerce_ai' );

		/**
		 * Fires after API disconnection.
		 *
		 * @since 1.9
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_api_disconnected', $request );

		$this->response_success(
			array(
				'connected' => false,
				'message'   => __( 'API disconnected', 'easycommerce' ),
			)
		);
	}

	public function generate_token( $request ) {

		$args = array(
			'body' => array(
				'email'  => $request->get_param( 'email' ),
				'name'   => $request->get_param( 'name' ),
				'source' => $request->get_param( 'source' ),
			),
		);

		$response = wp_remote_post( easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/token' ), $args );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $body->data ) ) {
			$this->response_success( $body->data );
		}

		$this->response_success( array( 'message' => __( 'Something went wrong', 'easycommerce' ) ) );
	}

	public function verify_token( $request ) {

		$args = array(
			'body' => array(
				'email' => $request->get_param( 'email' ),
				'token' => $request->get_param( 'token' ),
			),
		);

		$response = wp_remote_post( easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/token/verify' ), $args );

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $body->data->verified ) && $body->data->verified == true ) {

			$api        = $body->data->user;
			$api->email = $request->get_param( 'email' );

			/**
			 * Fires before verifying token.
			 *
			 * @since 1.9
			 * @param string $email The email.
			 * @param string $token The token.
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'easycommerce_before_verify_token', $request->get_param( 'email' ), $request->get_param( 'token' ), $request );

			update_option( 'easycommerce_api', $api );

			/**
			 * Fires after verifying token.
			 *
			 * @since 1.9
			 * @param object $api The API data.
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'easycommerce_after_verify_token', $api, $request );
		} else {
			delete_option( 'easycommerce_api' );
			delete_option( 'easycommerce_ai' );
		}

		if ( isset( $body->data ) ) {
			$this->response_success( $body->data );
		}

		$this->response_success( array( 'message' => __( 'Token not verified', 'easycommerce' ) ) );
	}

	public function get_addons( $request ) {

		/**
		 * Should we store cache of addons data
		 */
		$caching = false;

		if ( ! $caching || false === $addons = $this->get_cache( 'addons' ) ) {

			$api     = get_option( 'easycommerce_api' );
			$headers = isset( $api->email ) ? array( 'email' => $api->email ) : array();

			$addons_url  = easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/addons/' );
			$addons_json = json_decode( wp_remote_retrieve_body( wp_remote_get( $addons_url, array( 'headers' => $headers ) ) ), true );

			if ( empty( $addons_json['data']['addons'] ) ) {
				$this->response_success( array( 'message' => __( 'Something went wrong', 'easycommerce' ) ) );
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( isset( $addons_json['data']['addons']['easycommerce'] ) ) {
				unset( $addons_json['data']['addons']['easycommerce'] );
			}

			$addons = array_map(
				function ( $addon, $slug ) {

					$addon['slug']        = $slug;
					$addon['type']        = $addon['price'] == 0 ? __( 'Free', 'easycommerce' ) : __( 'Pro', 'easycommerce' );
					$addon['status']      = is_plugin_active( "{$slug}/{$slug}.php" ) ? 'active' : ( $addon['accessible'] ? 'inactive' : 'buyable' );
					$addon['action_text'] = is_plugin_active( "{$slug}/{$slug}.php" ) ? __( 'Disable', 'easycommerce' ) : ( $addon['accessible'] ? __( 'Enable', 'easycommerce' ) : __( 'Enable', 'easycommerce' ) );

					return $addon;
				},
				$addons_json['data']['addons'],
				array_keys( $addons_json['data']['addons'] )
			);

			$this->set_cache( 'addons', $addons, DAY_IN_SECONDS );
		}

		/**
		 * Filters the list of addons before sending the response.
		 *
		 * @since 1.9
		 * @param array $addons The list of addons.
		 * @param WP_REST_Request $request The request object.
		 */
		$addons = apply_filters( 'easycommerce_connectivity_list_addons', $addons, $request );

		$this->response_success( array( 'addons' => $addons ) );
	}

	public function get_docs( $request ) {

		$cacheable = false;

		if ( ! $cacheable || false === $docs = $this->get_cache( 'docs' ) ) {

			$response = wp_remote_get( easycommerce_dev_docs( '/wp-json/helpwp/v1/docs/product/easycommerce?count=-1' ), [ 'timeout' => 30 ] );
			$docs     = json_decode( wp_remote_retrieve_body( $response ) );

			$this->set_cache( 'docs', $docs, DAY_IN_SECONDS );
		}

		if ( isset( $docs->data ) ) {
			$this->response_success(
				array(
					'message' => __( 'Docs found', 'easycommerce' ),
					'docs'    => $docs->data,
				)
			);
		}

		$this->response_success( array( 'message' => __( 'Something went wrong', 'easycommerce' ) ) );
	}

	public function get_doc( $request ) {
		$id = $request->get_param( 'id' );

		$response = wp_remote_get( easycommerce_dev_docs( "/wp-json/helpwp/v1/docs/{$id}" ) );
		$doc      = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $doc->success == true ) {
			$this->response_success(
				array(
					'message' => __( 'Doc found', 'easycommerce' ),
					'doc'     => $doc->data,
				)
			);
		}

		$this->response_success( array( 'message' => __( 'Doc not found', 'easycommerce' ) ) );
	}

	public function feedback( $request ) {

		$deactivated = (int) $request->get_param( 'deactivated' );

		// Bump the lifetime deactivation counter before building the snapshot so
		// it reflects the current deactivation (repeat vs first-time churner).
		if ( $deactivated ) {
			update_option( 'easycommerce_deactivation_count', (int) get_option( 'easycommerce_deactivation_count', 0 ) + 1 );
		}

		$args = array(
			'body' => array(
				'email'		=> $request->get_param( 'email' ),
				'name'		=> $request->get_param( 'name' ),
				'home'		=> $request->get_param( 'home' ),
				'subject'	=> $request->get_param( 'subject' ),
				'message'	=> $request->get_param( 'message' ),
				'deactivated' => $deactivated,
				'activated' => get_option( 'easycommerce_activated' ),
				'plugins'	=> get_option( 'active_plugins' ),
				'theme'		=> get_option( 'template' ),
				'onboarding' => easycommerce_onboarding_snapshot(),
			),
		);

		/**
		 * Fires before sending feedback.
		 *
		 * @since 1.9
		 * @param array $args The request args.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_send_feedback', $args, $request );

		$response = wp_remote_post( easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/feedback' ), $args );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $body->data ) ) {
			$this->response_success( $body->data );
		}

		$this->response_success( array( 'message' => __( 'Something went wrong', 'easycommerce' ) ) );
	}

	public function requests( $request ) {

		$args = array(
			'body' => array(
				'email'   => $request->get_param( 'email' ),
				'name'    => $request->get_param( 'name' ),
				'home'    => $request->get_param( 'home' ),
				'subject' => $request->get_param( 'subject' ),
				'message' => $request->get_param( 'message' ),
			),
		);

		/**
		 * Fires before sending request.
		 *
		 * @since 1.9
		 * @param array $args The request args.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_send_request', $args, $request );

		$response = wp_remote_post( easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/requests' ), $args );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $body->data ) ) {
			$this->response_success( $body->data );
		}

		$this->response_success( array( 'message' => __( 'Something went wrong', 'easycommerce' ) ) );
	}
}

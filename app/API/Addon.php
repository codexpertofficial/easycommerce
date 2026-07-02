<?php

namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Traits\Cache;
use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;
use WP_Plugin_Dependencies;
use WP_REST_Request;

class Addon extends API {

	use Cache;

	private $menu_slug = [
		'easycommerce-subscriptions'           => 'easycommerce-store#/subscriptions',
		'easycommerce-wpbakery'                => '',
		'easycommerce-license'                 => 'easycommerce-store#/licenses',
		'easycommerce-paddle'                  => 'easycommerce-settings&menu=payment&submenu=paddle',
		'easycommerce-points-and-rewards'      => 'easycommerce-settings&menu=points-rewards',
		'easycommerce-product-recommendations' => 'easycommerce-settings&menu=product-recommendations',
		'easycommerce-fluentcrm'               => 'easycommerce-settings&menu=fluentcrm',
		'easycommerce-bank-transfer'           => 'easycommerce-settings&menu=payment&submenu=bank-transfer',
		'easycommerce-sliding-cart'            => '',
		'easycommerce-rocket'                  => 'easycommerce-settings&menu=payment&submenu=rocket',
		'easycommerce-csv-importer'            => 'easycommerce-store#/easycommerce-importer',
		'easycommerce-wishlist'                => '',
		'easycommerce-checkout-editor'         => 'easycommerce-checkout-editor',
		'easycommerce-nagad'                   => 'easycommerce-settings&menu=payment&submenu=nagad',
		'easycommerce-bkash'                   => 'easycommerce-settings&menu=payment&submenu=bkash',
		'easycommerce-google-sheets-sync'      => 'easycommerce-settings&menu=google_sheets',
		'easycommerce-klaviyo'                 => 'easycommerce-settings&menu=klaviyo',
		'easycommerce-zendesk'                 => 'easycommerce-settings&menu=zendesk',
		'easycommerce-pdf-invoice'             => 'easycommerce-settings&menu=pdf-invoice',
		'easycommerce-delivery-date-picker'    => '',
		'easycommerce-mailchimp'               => 'easycommerce-settings&menu=mailchimp',
		'easycommerce-hubspot'                 => 'easycommerce-settings&menu=hubspot',
		'easycommerce-migration'               => 'easycommerce-settings&menu=migration',
		'easycommerce-slack'                   => 'easycommerce-settings&menu=slack'
	];

	public function list( $request ) {

		/**
		 * Should we store cache of addons data
		 */
		$caching = false;

		if ( ! $caching || false === $addons = $this->get_cache( 'addons' ) ) {

			$license = get_option( 'easycommerce-pro_license' );
			$headers = isset( $license['email'] ) ? array( 'email' => $license['email'] ) : array();

			$addons_url  = easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/addons/' );
			$addons_json = json_decode( wp_remote_retrieve_body( wp_remote_get( $addons_url, array( 'headers' => $headers ) ) ), true );

			if ( empty( $addons_json['data']['addons'] ) ) {
				$this->response_success( array( 'message' => __( 'Something went wrong', 'easycommerce' ) ) );

				return;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( isset( $addons_json['data']['addons']['easycommerce'] ) ) {
				unset( $addons_json['data']['addons']['easycommerce'] );
			}

			$menu_slug_map = $this->menu_slug;
			$addons        = array_map(
				function ( $addon, $slug ) use ( $menu_slug_map ) {
					$plugin_file          = $this->find_plugin_file( $slug );
					$is_active            = $plugin_file && is_plugin_active( $plugin_file );
					$addon['slug']        = $slug;
					$addon['type']        = isset( $addon['price'] ) && $addon['price'] == 0 ? __( 'Free', 'easycommerce' ) : __( 'Pro', 'easycommerce' );
					$addon['status']      = $is_active ? 'active' : ( $addon['accessible'] ? 'inactive' : 'buyable' );
					$addon['action_text'] = $is_active ? __( 'Disable', 'easycommerce' ) : __( 'Enable', 'easycommerce' );
					$addon['menu_slug']   = $menu_slug_map[ $slug ] ?? '';

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
		 * @param array $addons The list of addons.
		 * @param WP_REST_Request $request The request object.
		 *
		 * @since 1.9
		 */
		$addons = apply_filters( 'easycommerce_list_addons', $addons, $request );

		$this->response_success( array( 'addons' => $addons ) );
	}

	public function manage( $request ) {

		$addon = $request->get_param( 'addon' );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		/**
		 * Activate if asked to
		 */
		if ( 'activate' === $request->get_param( 'action' ) ) {

			// Install addon if not already on disk.
			$plugin_file = $this->find_plugin_file( $addon );
			if ( ! $plugin_file ) {
				$license = get_option( 'easycommerce-pro_license' );
				$headers = isset( $license['email'] ) ? array( 'email' => $license['email'] ) : array();

				$data_url = easycommerce_dev_store( "/wp-json/easycommerce/v1/hub/addons/{$addon}" );
				$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $data_url, array( 'headers' => $headers ) ) ) );

				if ( empty( $response->success ) ) {
					$this->response_success( array( 'status' => false, 'message' => __( 'Invalid addon', 'easycommerce' ) ) );
				}

				$zip_url = (string) $response->data->download_url;
				$zip_url = filter_var( trim( $zip_url ), FILTER_SANITIZE_URL );

				if ( empty( $response->data->is_free ) && empty( $zip_url ) ) {
					$this->response_success( array( 'status' => false, 'message' => __( 'Activate EasyCommerce Pro to install this addon', 'easycommerce' ) ) );
				}

				if ( empty( $zip_url ) ) {
					$this->response_success( array( 'status' => false, 'message' => __( 'Invalid addon', 'easycommerce' ) ) );
				}

				// install_plugin() returns the actual installed file path via Plugin_Upgrader::plugin_info(),
				// which resolves the real directory name regardless of the zip URL structure.
				$plugin_file = $this->install_plugin( $zip_url, $headers, $addon );
				if ( is_wp_error( $plugin_file ) ) {
					$this->response_success(
						array(
							'status'  => false,
							// translators: The error message.
							'message' => sprintf( __( 'Could not install the addon: %s', 'easycommerce' ), $plugin_file->get_error_message() ),
						)
					);
				}

				$this->refresh_plugin_dependencies();
			}

			if ( ! $plugin_file ) {
				$this->response_error( array( 'code' => 'activation_error', 'message' => __( 'Plugin file could not be resolved.', 'easycommerce' ) ) );
			}

			/**
			 * Fires before activating an addon.
			 *
			 * @param string $addon The addon slug.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_before_activate_addon', $addon, $request );

			// Ensure all plugin dependencies are activated.
			$plugin_data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin_file );
			$requires    = ! empty( $plugin_data['RequiresPlugins'] ) ? array_map( 'trim', explode( ',', $plugin_data['RequiresPlugins'] ) ) : array();
			foreach ( $requires as $require_slug ) {
				if ( 'easycommerce' === $require_slug ) {
					continue;
				}

				$require_file = $this->find_plugin_file( $require_slug );

				// Not installed — fetch from WordPress.org and install.
				if ( ! $require_file ) {
					require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

					$api = plugins_api( 'plugin_information', array( 'slug' => $require_slug ) );
					if ( is_wp_error( $api ) ) {
						$this->response_error(
							array(
								'code'    => 'dependency_not_found',
								'message' => sprintf(
									/* translators: %s: dependency plugin slug */
									__( 'Required plugin "%s" could not be found on WordPress.org.', 'easycommerce' ),
									$require_slug
								),
							)
						);
					}

					$require_file = $this->install_plugin( $api->download_link );
					if ( is_wp_error( $require_file ) ) {
						$this->response_error(
							array(
								'code'    => 'dependency_install_error',
								'message' => sprintf(
									/* translators: %s: dependency plugin slug */
									__( 'Could not install required plugin "%s".', 'easycommerce' ),
									$require_slug
								),
							)
						);
					}

					$this->refresh_plugin_dependencies();
				}

				if ( ! $require_file || is_plugin_active( $require_file ) ) {
					continue;
				}

				$dep_res = activate_plugin( $require_file );
				if ( is_wp_error( $dep_res ) ) {
					$this->response_error(
						array(
							'code'    => 'dependency_activation_error',
							'message' => sprintf(
								/* translators: 1: dependency plugin slug, 2: error message */
								__( 'Could not activate required plugin "%1$s": %2$s', 'easycommerce' ),
								$require_slug,
								$dep_res->get_error_message()
							),
						)
					);
				}
			}

			// Refresh one final time so WP's internal dep check sees all activated deps.
			$this->refresh_plugin_dependencies();

			$res = activate_plugin( $plugin_file );
			if ( is_wp_error( $res ) ) {
				$this->response_error( array( 'code' => 'activation_error', 'message' => $res->get_error_message() ) );
			}

			/**
			 * Fires after activating an addon.
			 *
			 * @param string $addon The addon slug.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_after_activate_addon', $addon, $request );

			do_action( 'easycommerce_log', array( 'object' => 'addon', 'action' => 'activate', 'object_id' => $addon ) );

			$this->response_success( array( 'status' => true, 'message' => __( 'Addon installed and activated', 'easycommerce' ) ) );
		}

		/**
		 * Deactivate if asked to
		 */
		if ( 'deactivate' === $request->get_param( 'action' ) ) {

			$plugin_file = $this->find_plugin_file( $addon );

			if ( ! $plugin_file || ! is_plugin_active( $plugin_file ) ) {
				$this->response_error( array( 'code' => 'deactivation_error', 'message' => __( 'Plugin is not active', 'easycommerce' ) ) );
			}

			/**
			 * Fires before deactivating an addon.
			 *
			 * @param string $addon The addon slug.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_before_deactivate_addon', $addon, $request );

			deactivate_plugins( $plugin_file );

			/**
			 * Fires after deactivating an addon.
			 *
			 * @param string $addon The addon slug.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_after_deactivate_addon', $addon, $request );

			do_action( 'easycommerce_log', array( 'object' => 'addon', 'action' => 'deactivate', 'object_id' => $addon ) );

			$this->response_success( array( 'status' => true, 'message' => __( 'Addon deactivated', 'easycommerce' ) ) );
		}

		$this->response_success( array( 'status' => true, 'message' => __( 'Addon installed', 'easycommerce' ) ) );
	}

	/**
	 * Resolves a plugin slug to its installed file path (e.g. "fluent-crm" → "fluent-crm/fluentcrm.php").
	 *
	 * Uses WP_Plugin_Dependencies (WP 6.5+) when available, then falls back to
	 * scanning get_plugins() for a file inside the slug's directory.
	 *
	 * @param string $slug Plugin directory slug.
	 * @return string|false Relative plugin file path, or false if not installed.
	 */
	private function find_plugin_file( string $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( class_exists( 'WP_Plugin_Dependencies' ) ) {
			WP_Plugin_Dependencies::initialize();
			$file = WP_Plugin_Dependencies::get_dependency_filepath( $slug );
			if ( $file ) {
				return $file;
			}
		}

		foreach ( get_plugins() as $plugin_path => $plugin_info ) {
			if ( strpos( $plugin_path, trailingslashit( $slug ) ) === 0 ) {
				return $plugin_path;
			}
		}

		// Fallback for non-canonical install dirs (e.g. "easycommerce-fluentcrm-Xy12/easycommerce-fluentcrm.php"):
		// match by main file basename {slug}.php — EasyCommerce addon convention.
		$expected_basename = $slug . '.php';
		foreach ( get_plugins() as $plugin_path => $plugin_info ) {
			if ( basename( $plugin_path ) === $expected_basename ) {
				return $plugin_path;
			}
		}

		return false;
	}

	/**
	 * Clears plugin caches and resets WP_Plugin_Dependencies static state.
	 *
	 * WP_Plugin_Dependencies (WP 6.5+) populates its internal maps once per request
	 * via static properties. After installing a new plugin mid-request those statics
	 * are stale, so activate_plugin() wrongly reports unmet dependencies.
	 *
	 * Resetting the statics via reflection forces the next initialize() call to
	 * re-read the live plugin list, keeping the dependency check accurate.
	 */
	private function refresh_plugin_dependencies(): void {
		wp_clean_plugins_cache( false );

		if ( ! class_exists( 'WP_Plugin_Dependencies' ) ) {
			return;
		}

		try {
			$ref = new \ReflectionClass( WP_Plugin_Dependencies::class );
			foreach ( [ 'plugins', 'plugin_data', 'dependency_filepaths', 'dependencies', 'dependent_plugin_data' ] as $prop_name ) {
				if ( $ref->hasProperty( $prop_name ) ) {
					$prop = $ref->getProperty( $prop_name );
					$prop->setAccessible( true );
					$prop->setValue( null, null );
				}
			}
			WP_Plugin_Dependencies::initialize();
		} catch ( \ReflectionException $e ) {
			// WP internals changed — stale statics remain; activate_plugin() may still
			// fail with plugin_missing_dependencies on freshly installed deps.
		}
	}

	/**
	 * Installs a plugin from a zip URL using the WordPress upgrader.
	 *
	 * Supports optional auth headers (e.g. for private EasyCommerce store downloads)
	 * by temporarily filtering wp_remote_get arguments.
	 *
	 * Returns the installed plugin's relative file path (e.g. "fluent-crm/fluentcrm.php")
	 * via Plugin_Upgrader::plugin_info() — the WP-native way to get the actual installed path
	 * regardless of what directory name the zip was extracted to.
	 *
	 * @param string $zip_url  Direct URL to the plugin zip file.
	 * @param array  $headers  Optional HTTP headers to attach to the download request.
	 * @param string $slug     Optional canonical plugin slug. When set, the extracted
	 *                         source directory is renamed to this slug before install so
	 *                         the addon lands in {slug}/ instead of a temp/obfuscated dir.
	 * @return string|\WP_Error  Installed plugin file path on success, WP_Error on failure.
	 */
	private function install_plugin( string $zip_url, array $headers = [], string $slug = '' ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		WP_Filesystem();

		$inject = null;
		if ( ! empty( $headers ) ) {
			$inject = static function ( $args ) use ( $headers ) {
				$args['headers'] = array_merge( $args['headers'] ?? array(), $headers );
				return $args;
			};
			add_filter( 'http_request_args', $inject );
		}

		// EasyCommerce Hub addon zips contain their files at the zip root (no wrapping
		// {slug}/ folder). WP then names the install directory after the downloaded
		// package, whose temp filename carries a random wp_tempnam() suffix (e.g.
		// "easycommerce-rocket-GAb4t0"), so the addon lands under a wrong directory that
		// breaks update detection and shows a random slug in the UI. Rename the extracted
		// source directory to the canonical slug before WP moves it into wp-content/plugins/.
		//
		// The corrected path is built from dirname( $source ) — a SIBLING of the source —
		// never trailingslashit( $source ) . $slug, because when the zip has root-level
		// files WP sets $source to the working directory itself and moving a directory into
		// its own child fails. See issue #2892.
		$rename = null;
		if ( '' !== $slug ) {
			$rename = static function ( $source ) use ( $slug ) {
				global $wp_filesystem;

				if ( is_wp_error( $source ) ) {
					return $source;
				}

				$source_dir = untrailingslashit( $source );

				if ( basename( $source_dir ) === $slug ) {
					return $source;
				}

				$corrected = trailingslashit( dirname( $source_dir ) ) . $slug;

				if ( $wp_filesystem->exists( $corrected ) ) {
					$wp_filesystem->delete( $corrected, true );
				}

				if ( ! $wp_filesystem->move( $source, $corrected, true ) ) {
					return $source;
				}

				return trailingslashit( $corrected );
			};
			add_filter( 'upgrader_source_selection', $rename, 10, 1 );
		}

		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $zip_url );

		if ( $inject ) {
			remove_filter( 'http_request_args', $inject );
		}
		if ( $rename ) {
			remove_filter( 'upgrader_source_selection', $rename, 10 );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! $result ) {
			return new \WP_Error( 'install_failed', __( 'Plugin installation failed.', 'easycommerce' ) );
		}

		// plugin_info() returns the actual installed file path (e.g. "slug/main-file.php")
		// regardless of what directory name the zip extracted to — critical for store plugins
		// whose download URLs are obfuscated and don't carry the plugin slug as a filename.
		$plugin_file = $upgrader->plugin_info();
		if ( ! $plugin_file ) {
			return new \WP_Error( 'install_failed', __( 'Plugin installed but file path could not be resolved.', 'easycommerce' ) );
		}

		return $plugin_file;
	}

	/**
	 * @todo implement API verification
	 */
	public function license( $request ) {
		$key      = $request->get_param( 'key' );
		$addon    = $request->get_param( 'addon' );
		$action   = $request->get_param( 'action' );
		$email    = $request->get_header( 'email' );
		$site_url = easycommerce_home_url();

		if ( $action == 'deactivate' && ! empty( $license = get_option( "{$addon}_license" ) ) ) {
			$key = $license['key'];
		}

		$url = easycommerce_dev_store( "/wp-json/easycommerce/v1/licenses/{$key}/{$action}" );

		$response = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode(
					array(
						'site_url' => $site_url,
						'key'      => $key,
						'email'    => $email,
						'product'  => $addon,
					)
				),
				'headers' => array(
					'Content-Type' => 'application/json',
					'email'        => $email,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->response_success(
				array(
					'status'  => false,
					'message' => $response->get_error_message(),
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body, true );

		// if the license API responses negetive
		if ( empty( $data['success'] ) && $action === 'activate' ) {
			$this->response_error(
				array(
					'code'    => $data['data']['code'],
					'message' => $data['data']['message'],
				)
			);
		}

		if ( isset( $data['success'] ) && $data['success'] && $action === 'activate' ) {
			/**
			 * Fires before activating a license.
			 *
			 * @param string $addon The addon slug.
			 * @param string $key The license key.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_before_activate_license', $addon, $key, $request );

			update_option(
				"{$addon}_license",
				array(
					'key'    => $key,
					'expiry' => $data['data']['expiry'],
					'email'  => $email,
				)
			);

			// activate the addon
			$this->manage( $request );

			/**
			 * Fires after activating a license.
			 *
			 * @param string $addon The addon slug.
			 * @param string $key The license key.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_after_activate_license', $addon, $key, $request );

			$this->response_success(
				array(
					'status'  => true,
					'message' => __( 'Addon installed and activated', 'easycommerce' ),
				)
			);
		} elseif ( $action === 'deactivate' ) {
			/**
			 * Fires before deactivating a license.
			 *
			 * @param string $addon The addon slug.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_before_deactivate_license', $addon, $request );

			delete_option( "{$addon}_license" );

			// deactivate the addon
			$this->manage( $request );

			/**
			 * Fires after deactivating a license.
			 *
			 * @param string $addon The addon slug.
			 * @param WP_REST_Request $request The request object.
			 *
			 * @since 1.9
			 */
			do_action( 'easycommerce_after_deactivate_license', $addon, $request );

			$this->response_success(
				array(
					'status'  => true,
					'message' => __( 'Addon deactivated', 'easycommerce' ),
				)
			);
		}

		$this->response_error(
			array(
				'code'    => 'license_error',
				'message' => __( 'Something went wrong..', 'easycommerce' ),
			)
		);
	}
}

<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class Updater {

	/**
	 * List of plugin slugs to check for updates.
	 *
	 * @var array
	 */
	private $slugs = array();

	/**
	 * API URLs for fetching plugin update data.
	 *
	 * @var array
	 */
	private $data_urls = array();

	/**
	 * Initializes the updater by setting plugin slugs and generating API URLs.
	 */
	public function __construct() {

		/**
		 * Fires before setting up plugin updater.
		 */
		do_action( 'easycommerce_before_updater_setup' );

		/**
		 * Filters the list of plugin slugs to check for updates.
		 *
		 * @param array $slugs The default plugin slugs.
		 */
		$this->slugs = apply_filters( 'easycommerce_updater', array() );

		// Generate data URLs for all plugins
		foreach ( $this->slugs as $slug ) {
			$this->data_urls[ $slug ] = easycommerce_dev_store( "/wp-json/easycommerce/v1/hub/addons/{$slug}" );
		}

		/**
		 * Fires after setting up plugin updater.
		 */
		do_action( 'easycommerce_after_updater_setup', $this->slugs, $this->data_urls );
	}

	/**
	 * Checks for plugin updates.
	 *
	 * @param object $transient The update transient object.
	 * @return object Modified transient object with update data.
	 */
	public function check( $transient ) {
		/**
		 * Fires before checking for updates.
		 */
		do_action( 'easycommerce_before_check_updates', $transient );

		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		foreach ( $this->slugs as $slug ) {
			$plugin_file = "{$slug}/{$slug}.php";

			// Skip if the plugin is not in the list of checked plugins
			if ( ! isset( $transient->checked[ $plugin_file ] ) ) {
				continue;
			}

			$api     = get_option( 'easycommerce_api' );
			$headers = isset( $api->email ) ? array( 'email' => $api->email ) : array();

			$response = wp_remote_get( $this->data_urls[ $slug ], array( 'headers' => $headers ) );
			if ( is_wp_error( $response ) ) {
				continue;
			}

			$plugin_info = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( isset( $plugin_info['data'] ) ) {
				$plugin = $plugin_info['data'];

				/**
				 * Fires before processing update response.
				 */
				do_action( 'easycommerce_before_process_update', $plugin, $transient );

				// If a newer version is available, add it to the transient response
				if ( isset( $plugin['version'] ) && version_compare( $plugin['version'], $transient->checked[ $plugin_file ], '>' ) ) {
					$transient->response[ $plugin_file ] = (object) apply_filters(
						'easycommerce_update_response',
						array(
							'slug'        => $slug,
							'new_version' => $plugin['version'],
							'package'     => $plugin['download_url'] ?? false,
							'url'         => $plugin['author_homepage'],
							'icons'       => array(
								'default' => $plugin['thumbnail'],
								'2x'      => $plugin['thumbnail'],
							),
						),
						$plugin
					);
				}

				/**
				 * Fires after processing update response.
				 */
				do_action( 'easycommerce_after_process_update', $plugin, $transient );
			}
		}

		/**
		 * Fires after checking for updates.
		 */
		do_action( 'easycommerce_after_check_updates', $transient );

		return $transient;
	}

	/**
	 * Retrieves plugin information for the WordPress update system.
	 *
	 * @param mixed  $result The result object.
	 * @param string $action The type of request.
	 * @param object $args   Request arguments.
	 * @return mixed Plugin information or the original result.
	 */
	public function get_info( $result, $action, $args ) {
		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		/**
		 * Fires before fetching plugin information.
		 */
		do_action( 'easycommerce_before_get_plugin_info', $args );

		// Check if the requested plugin slug is in the list
		if ( ! in_array( $args->slug, $this->slugs, true ) ) {
			return $result;
		}

		$api     = get_option( 'easycommerce_api' );
		$headers = isset( $api->email ) ? array( 'email' => $api->email ) : array();

		$response = wp_remote_get( $this->data_urls[ $args->slug ], array( 'headers' => $headers ) );
		if ( is_wp_error( $response ) ) {
			return $result;
		}

		$plugin_info = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $plugin_info['data'] ) ) {
			$plugin = (object) $plugin_info['data'];

			if ( ! isset( $plugin->name ) ) {
				return $result;
			}

			// Format screenshots as HTML
			$screenshots = '';
			if ( ! empty( $plugin->screenshots ) && is_array( $plugin->screenshots ) ) {
				$screenshots .= '<div class="plugin-screenshots">';
				foreach ( $plugin->screenshots as $screenshot ) {
					$screenshots .= '<img src="' . esc_url( $screenshot ) . '" />';
				}
				$screenshots .= '</div>';
			}

			/**
			 * Filters the plugin information response.
			 *
			 * @param array $plugin_data The default plugin data.
			 */
			$plugin_data = apply_filters(
				'easycommerce_plugin_info',
				array(
					'name'            => $plugin->name,
					'slug'            => $plugin->slug,
					'version'         => $plugin->version,
					'author'          => $plugin->author,
					'author_homepage' => $plugin->author_homepage,
					'requires'        => $plugin->requires,
					'tested'          => $plugin->tested,
					'requires_php'    => $plugin->requires_php,
					'sections'        => array_merge(
						(array) $plugin->sections,
						array(
							'screenshots' => $screenshots,
						)
					),
					'thumbnail'       => $plugin->thumbnail,
					'banners'         => $plugin->banners,
					'download_link'   => isset( $plugin->download_url ) ? $plugin->download_url : false,
				),
				$plugin
			);

			/**
			 * Fires after fetching plugin information.
			 */
			do_action( 'easycommerce_after_get_plugin_info', $plugin_data );

			return (object) $plugin_data;
		}

		return $result;
	}

	/**
	 * When an addon is activated, store its first activation time
	 */
	public function set_addon_flag( $plugin_file, $network_wide ) {

		if ( false !== strpos( $plugin = dirname( $plugin_file ), 'easycommerce' ) && $plugin != 'easycommerce' ) {
			$addons = get_option( 'easycommerce_addons', array() );

			if ( ! isset( $addons[ $plugin ] ) ) {
				$addons[ $plugin ] = array( 'installed' => time() );

				update_option( 'easycommerce_addons', $addons );
			}
		}
	}
}

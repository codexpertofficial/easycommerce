<?php
namespace EasyCommerce\Bootstrap;

defined( 'ABSPATH' ) || exit;

class Initializer {

	/**
	 * Initializes the plugin's components.
	 *
	 * @return void
	 */
	public static function initialize() {

		$initializer = new self();

		/**
		 * Fires before the initialization process starts.
		 */
		do_action( 'easycommerce_before_initialize', $initializer );

		$initializer->load_config();
		$initializer->load_admin_controllers();
		$initializer->load_public_controllers();
		$initializer->load_common_controllers();
		$initializer->load_payment_controllers();

		/**
		 * Fires after the initialization process is completed.
		 */
		do_action( 'easycommerce_after_initialize', $initializer );
	}

	/**
	 * Loads configuration files for the plugin.
	 *
	 * @return void
	 */
	public function load_config() {
		/**
		 * Fires before loading the configuration files.
		 */
		do_action( 'easycommerce_before_load_config' );

		$config_files = glob( EASYCOMMERCE_PLUGIN_DIR . 'app/Config/*.php' );
		$config_files = apply_filters( 'easycommerce_config_files', $config_files );

		foreach ( $config_files as $config_file ) {
			if ( file_exists( $config_file ) ) {
				require_once $config_file;
			}
		}

		/**
		 * Fires after loading the configuration files.
		 */
		do_action( 'easycommerce_after_load_config' );
	}

	/**
	 * Initializes controllers for wp-admin.
	 *
	 * @return void
	 */
	private function load_admin_controllers() {
		if ( is_admin() ) {
			/**
			 * Fires before loading admin controllers.
			 */
			do_action( 'easycommerce_before_load_admin_controllers' );

			$controller_dir = EASYCOMMERCE_PLUGIN_DIR . 'app/Controllers/Admin/';
			$controllers    = glob( $controller_dir . '*.php' );
			$controllers    = apply_filters( 'easycommerce_admin_controllers', $controllers );

			foreach ( $controllers as $file ) {
				$class_name = basename( $file, '.php' );
				$controller = "\\EasyCommerce\\Controllers\\Admin\\{$class_name}";

				if ( class_exists( $controller ) ) {
					new $controller();
				}
			}

			/**
			 * Fires after loading admin controllers.
			 */
			do_action( 'easycommerce_after_load_admin_controllers' );
			
		}
	}

	/**
	 * Initializes controllers for public-facing parts of the site.
	 *
	 * @return void
	 */
	private function load_public_controllers() {
		if ( ! is_admin() ) {
			/**
			 * Fires before loading public-facing controllers.
			 */
			do_action( 'easycommerce_before_load_public_controllers' );

			$controller_dir = EASYCOMMERCE_PLUGIN_DIR . 'app/Controllers/Front/';
			$controllers    = glob( $controller_dir . '*.php' );
			$controllers    = apply_filters( 'easycommerce_public_controllers', $controllers );

			foreach ( $controllers as $file ) {
				$class_name = basename( $file, '.php' );
				$controller = "\\EasyCommerce\\Controllers\\Front\\{$class_name}";

				if ( class_exists( $controller ) ) {
					new $controller();
				}
			}

			/**
			 * Fires after loading public-facing controllers.
			 */
			do_action( 'easycommerce_after_load_public_controllers' );
		}
	}

	/**
	 * Initializes controllers that operate on both admin and public interfaces.
	 *
	 * @return void
	 */
	private function load_common_controllers() {
		/**
		 * Fires before loading common controllers.
		 */
		do_action( 'easycommerce_before_load_common_controllers' );

		$controller_dir = EASYCOMMERCE_PLUGIN_DIR . 'app/Controllers/Common/';
		$controllers    = glob( $controller_dir . '*.php' );
		$controllers    = apply_filters( 'easycommerce_common_controllers', $controllers );

		foreach ( $controllers as $file ) {
			$class_name = basename( $file, '.php' );
			$controller = "\\EasyCommerce\\Controllers\\Common\\{$class_name}";

			if ( class_exists( $controller ) ) {
				new $controller();
			}
		}

		/**
		 * Fires after loading common controllers.
		 */
		do_action( 'easycommerce_after_load_common_controllers' );
	}

	private function load_payment_controllers() {
		/**
		 * Fires before loading payment controllers.
		 */
		do_action( 'easycommerce_before_load_payment_controllers' );

		$controller_dir = EASYCOMMERCE_PLUGIN_DIR . 'app/Controllers/Payment/';
		$controllers    = glob( $controller_dir . '*.php' );
		$controllers    = apply_filters( 'easycommerce_payment_controllers', $controllers );

		foreach ( $controllers as $file ) {
			$class_name = basename( $file, '.php' );
			$controller = "\\EasyCommerce\\Controllers\\Payment\\{$class_name}";

			if ( class_exists( $controller ) ) {
				new $controller();
			}
		}

		/**
		 * Fires after loading payment controllers.
		 */
		do_action( 'easycommerce_after_load_payment_controllers' );
	}
}
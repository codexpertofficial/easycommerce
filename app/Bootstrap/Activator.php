<?php
namespace EasyCommerce\Bootstrap;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;

class Activator {

	use Hook;

	/**
	 * Handles plugin activation tasks.
	 *
	 * @return void
	 */
	public static function activate() {

		$activator = new self();

		/**
		 * Fires before the plugin activation process begins.
		 */
		do_action( 'easycommerce_before_activate', $activator );

		$activator->deactivate_conflicting_plugins();
		$activator->sync_updates();
		$activator->register_roles();
		$activator->register_post_types();
		$activator->register_taxonomies();
		$activator->register_thumbnails();

		/**
		 * Fires after the plugin activation process is completed.
		 */
		do_action( 'easycommerce_after_activate', $activator );
	}

	/**
	 * Synchronizes plugin updates with the WordPress update system.
	 *
	 * @return void
	 */
	public function sync_updates() {

		/**
		 * Fires before syncing updates.
		 */
		do_action( 'easycommerce_before_sync_updates' );

		$this->filter( 'pre_set_site_transient_update_plugins', array( new Activator\Updater(), 'check' ) );
		$this->filter( 'plugins_api', array( new Activator\Updater(), 'get_info' ), 10, 3 );
		$this->filter( 'activated_plugin', array( new Activator\Updater(), 'set_addon_flag' ), 10, 2 );

		/**
		 * Fires after syncing updates.
		 */
		do_action( 'easycommerce_after_sync_updates' );
	}

	/**
	 * Registers custom user roles for the plugin.
	 *
	 * @return void
	 */
	public function register_roles() {

		/**
		 * Fires before registering user roles.
		 */
		do_action( 'easycommerce_before_register_roles' );

		$this->action( 'init', array( new Activator\User_Role(), 'register' ) );

		/**
		 * Fires after registering user roles.
		 */
		do_action( 'easycommerce_after_register_roles' );
	}

	/**
	 * Registers custom post types for the plugin.
	 *
	 * @return void
	 */
	public function register_post_types() {

		/**
		 * Fires before registering post types.
		 */
		do_action( 'easycommerce_before_register_post_types' );

		$this->action( 'init', array( new Activator\Post_Type(), 'register' ) );
		$this->action( 'wp_insert_post', array( new Activator\Post_Type(), 'insert_default_content' ) , 10, 3 );

		/**
		 * Fires after registering post types.
		 */
		do_action( 'easycommerce_after_register_post_types' );
	}

	/**
	 * Registers custom taxonomies for the plugin.
	 *
	 * @return void
	 */
	public function register_taxonomies() {

		/**
		 * Fires before registering taxonomies.
		 */
		do_action( 'easycommerce_before_register_taxonomies' );

		$this->action( 'init', array( new Activator\Taxonomy(), 'register' ) );

		/**
		 * Fires after registering taxonomies.
		 */
		do_action( 'easycommerce_after_register_taxonomies' );
	}

	/**
	 * Registers thumbnail sizes for the plugin.
	 *
	 * @return void
	 */
	public function register_thumbnails() {

		/**
		 * Fires before registering thumbnail sizes.
		 */
		do_action( 'easycommerce_before_register_thumbnails' );

		$this->action( 'after_setup_theme', array( new Activator\Thumbnail(), 'register' ) );

		/**
		 * Fires after registering thumbnail sizes.
		 */
		do_action( 'easycommerce_after_register_thumbnails' );
	}

	/**
	 * Deactivates conflicting plugins if they are active.
	 *
	 * @return void
	 */
	public function deactivate_conflicting_plugins() {
		$conflicting_plugins = easycommerce_get_conflicting_plugins();

		foreach ( $conflicting_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
			}
		}
	}
}

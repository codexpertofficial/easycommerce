<?php
namespace EasyCommerce\Bootstrap;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;
use EasyCommerce\Traits\Queue;

class Installer {
	use Queue;
	/**
	 * Runs the installation routines.
	 *
	 * @return void
	 */
	public static function install() {

		$installer = new self();

		/**
		 * Fires before the installation process starts.
		 */
		do_action( 'easycommerce_before_install', $installer );

		// Hook for handling data migration for specific tables
		add_action( 'easycommerce_migrate_coupons_table', array( $installer, 'handle_coupons_data_migration' ), 10, 4 );
		add_action( 'easycommerce_migrate_cart_sessions_table', array( $installer, 'handle_cart_sessions_data_migration' ), 10, 4 );
		add_action( 'easycommerce_migrate_orders_table', array( $installer, 'handle_orders_data_migration' ), 10, 4 );

		if ( ! $installer->is_database_up_to_date() ) {
			$installer->prepare();
			$installer->set_cron();
			$installer->create_tables();
			$installer->update_existing_tables();
			$installer->update_db_version();
		}

		add_action( 'activated_plugin', array( $installer, 'setup_wizard_redirect' ) );

		/**
		 * Fires after the installation process is completed.
		 */
		do_action( 'easycommerce_after_install', $installer );
	}

	public function setup_wizard_redirect( $plugin ) {
		if( isset( $_REQUEST['bulk_action'] ) || wp_doing_ajax() ) {
			return;
		}

		if( $plugin == plugin_basename( EASYCOMMERCE_FILE ) && ! get_option( 'easycommerce-setup_wizard' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=easycommerce-wizard' ) );
			exit;
		}
	}

	/**
	 * Prepares the plugin settings and configurations.
	 *
	 * @return void
	 */
	public function prepare() {
		/**
		 * Fires before preparing the plugin settings.
		 */
		do_action( 'easycommerce_before_prepare' );

		// Set a flag that indicates the plugin has been activated
		if( ! get_option( 'easycommerce_activated' ) ) {
			update_option( 'easycommerce_activated', time() );
		}

		/**
		 * Set memory limit.
		 *
		 * @todo Use WP CLI for better handling.
		 */
		$memory_required = apply_filters( 'easycommerce_memory_limit', 512 ); // in MB

		if ( defined( 'WP_MEMORY_LIMIT' ) && absint( WP_MEMORY_LIMIT ) < $memory_required ) {
			$wp_config_path = ABSPATH . 'wp-config.php';

			if ( file_exists( $wp_config_path ) && is_writable( $wp_config_path ) ) {
				$config_content = file_get_contents( $wp_config_path );

				// Check if WP_MEMORY_LIMIT is already defined
				if ( strpos( $config_content, "define('WP_MEMORY_LIMIT'" ) !== false ) {

					// Update existing WP_MEMORY_LIMIT
					$config_content = preg_replace(
						"/define\(\s*'WP_MEMORY_LIMIT'\s*,\s*'[^']+'\s*\);/",
						"define('WP_MEMORY_LIMIT', '{$memory_required}M');",
						$config_content
					);
				} else {
					// Insert WP_MEMORY_LIMIT before "That's all, stop editing!"
					$config_content = preg_replace(
						"/(\/\* That's all, stop editing! Happy publishing. \*\/)/",
						"define('WP_MEMORY_LIMIT', '{$memory_required}M');\n\n$1",
						$config_content
					);
				}

				file_put_contents( $wp_config_path, $config_content );
			}
		}

		/**
		 * Schedule an event
		 */
		$this->schedule( 'easycommerce_prepare_background' );

		/**
		 * Fires after preparing the plugin settings.
		 */
		do_action( 'easycommerce_after_prepare' );
	}

	/**
	 * Sets up scheduled tasks for the plugin.
	 *
	 * @return void
	 */
	public function set_cron() {
		/**
		 * Fires before setting up cron jobs.
		 */
		do_action( 'easycommerce_before_set_cron' );

		// Cron job logic goes here

		/**
		 * Fires after setting up cron jobs.
		 */
		do_action( 'easycommerce_after_set_cron' );
	}

	/**
	 * Checks if the database is up to date.
	 *
	 * @return bool True if the database is up to date, false otherwise.
	 */
	protected function is_database_up_to_date() {
		$installed_ver = get_option( 'easycommerce_db_version' );
		return version_compare( $installed_ver, EASYCOMMERCE_VERSION, '=' );
	}

	/**
	 * Creates database tables from the configuration file.
	 *
	 * @return void
	 */
	protected function create_tables() {
		/**
		 * Fires before creating database tables.
		 */
		do_action( 'easycommerce_before_create_tables' );

		global $easycommerce_tables;

		if ( empty( $easycommerce_tables ) ) {
			require_once EASYCOMMERCE_PLUGIN_DIR . 'app/Config/tables.php';
		}

		$easycommerce_tables = apply_filters( 'easycommerce_install_tables', $easycommerce_tables );

		foreach ( $easycommerce_tables as $table_name => $table_data ) {
			$db = new Database( $table_name );

			$columns = $table_data['columns'];
			$options = ! empty( $table_data['options'] ) ? $table_data['options'] : array();

			// Create the table and log the result
			if ( ! $db->create_table( $columns, $options ) ) {
			}
		}

		/**
		 * Fires after creating database tables.
		 */
		do_action( 'easycommerce_after_create_tables' );
	}

	/**
	 * Updates existing database tables to match the current schema.
	 *
	 * @return void
	 */
	protected function update_existing_tables() {
		global $wpdb;

		/**
		 * Fires before updating existing database tables.
		 */
		do_action( 'easycommerce_before_update_existing_tables' );

		global $easycommerce_tables;

		if ( empty( $easycommerce_tables ) ) {
			require_once EASYCOMMERCE_PLUGIN_DIR . 'app/Config/tables.php';
		}

		$easycommerce_tables = apply_filters( 'easycommerce_install_tables', $easycommerce_tables );

		foreach ( $easycommerce_tables as $table_name => $table_data ) {
			$db              = new Database( $table_name );
			$table_full_name = $db->get_prefix() . $table_name;

			$columns = $table_data['columns'];
			$options = ! empty( $table_data['options'] ) ? $table_data['options'] : array();

			/**
			 * Fires before migrating a specific table, allowing custom data handling.
			 *
			 * The hook name is dynamic: easycommerce_migrate_{$table_name}_table
			 *
			 * @param Database $database_instance Database instance.
			 * @param string   $table_full_name   Full table name with prefix.
			 * @param array    $columns           Current column definitions.
			 * @param array    $options           Current table options.
			 */
			do_action( "easycommerce_migrate_{$table_name}_table", $db, $table_full_name, $columns, $options );

			/**
			 * Fires after migrating any table, allowing custom migration handling.
			 *
			 * @param Database $database_instance Database instance.
			 * @param string   $table_full_name   Full table name with prefix.
			 * @param array    $columns           Current column definitions.
			 * @param array    $options           Current table options.
			 * @param string   $table_name        Table name.
			 */
			do_action( 'easycommerce_migrate_table', $db, $table_full_name, $columns, $options, $table_name );
		}

		/**
		 * Fires after updating existing database tables.
		 *
		 * @param Installer $installer           Installer instance.
		 * @param array     $easycommerce_tables Table configurations.
		 * @param \wpdb     $wpdb                WordPress database global.
		 */
		do_action( 'easycommerce_after_update_existing_tables', $this, $easycommerce_tables, $wpdb );
	}

	/**
	 * Handles data migration for the coupons table during schema updates.
	 *
	 * This method migrates legacy columns ('discount_type' to 'type', 'amount' to 'offer')
	 * by adding new columns, copying data, and dropping legacy ones.
	 *
	 * @param Database $db              Database instance.
	 * @param string   $table_full_name Full table name with prefix.
	 * @param array    $columns         Current column definitions from config.
	 * @param array    $options         Current table options from config.
	 *
	 * @return void
	 */
	public function handle_coupons_data_migration( Database $db, string $table_full_name, array $columns, array $options ) {
		global $wpdb;

		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$table_full_name}'" ) ) {
			return;
		}

		$existing_columns = (array) $wpdb->get_col( "DESCRIBE `{$table_full_name}`;" );

		if ( ! isset( $columns['type'] ) || ! isset( $columns['offer'] ) ) {
			return;
		}

		// Clean up: Drop the incorrectly added columns if they exist
		if ( in_array( 'type', $existing_columns, true ) && in_array( 'discount_type', $existing_columns, true ) ) {
			$wpdb->query( "ALTER TABLE `{$table_full_name}` DROP COLUMN `type`" );
		}

		if ( in_array( 'offer', $existing_columns, true ) && in_array( 'amount', $existing_columns, true ) ) {
			$wpdb->query( "ALTER TABLE `{$table_full_name}` DROP COLUMN `offer`" );
		}

		// Refresh column list after dropping
		$existing_columns = (array) $wpdb->get_col( "DESCRIBE `{$table_full_name}`;" );

		// Now rename the old columns
		if ( in_array( 'discount_type', $existing_columns, true ) && ! in_array( 'type', $existing_columns, true ) ) {
			$wpdb->query( "ALTER TABLE `{$table_full_name}` CHANGE COLUMN `discount_type` `type` " . $columns['type'] );
		}

		if ( in_array( 'amount', $existing_columns, true ) && ! in_array( 'offer', $existing_columns, true ) ) {
			$wpdb->query( "ALTER TABLE `{$table_full_name}` CHANGE COLUMN `amount` `offer` " . $columns['offer'] );
		}
	}

	/**
	 * Handles schema migration for the cart_sessions table during schema updates.
	 *
	 * Adds the `payment_initiated` value to the `status` ENUM on existing installs.
	 * dbDelta does not reliably ALTER ENUM definitions on existing columns, so the
	 * change is applied explicitly here. Without it, the payment lock silently fails
	 * because MySQL coerces the unknown ENUM value to an empty string.
	 *
	 * @param Database $db              Database instance.
	 * @param string   $table_full_name Full table name with prefix.
	 * @param array    $columns         Current column definitions from config.
	 * @param array    $options         Current table options from config.
	 *
	 * @return void
	 */
	public function handle_cart_sessions_data_migration( Database $db, string $table_full_name, array $columns, array $options ) {
		global $wpdb;

		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$table_full_name}'" ) ) {
			return;
		}

		if ( empty( $columns['status'] ) ) {
			return;
		}

		// Read the current column definition; skip if it already supports the value.
		$row = $wpdb->get_row( "SHOW COLUMNS FROM `{$table_full_name}` LIKE 'status'" );

		if ( $row && false !== stripos( $row->Type, 'payment_initiated' ) ) {
			return;
		}

		$wpdb->query( "ALTER TABLE `{$table_full_name}` MODIFY COLUMN `status` " . $columns['status'] );
	}

	/**
	 * Handles schema migration for the orders table during schema updates.
	 *
	 * Adds the `failed` value to the `status` ENUM on existing installs and
	 * back-fills any orders whose status was previously coerced to '' (failed
	 * payments written before `failed` existed) to `failed`. dbDelta does not
	 * reliably alter ENUM definitions on existing columns, so the change is
	 * applied explicitly here.
	 *
	 * @param Database $db              Database instance.
	 * @param string   $table_full_name Full table name with prefix.
	 * @param array    $columns         Current column definitions from config.
	 * @param array    $options         Current table options from config.
	 *
	 * @return void
	 */
	public function handle_orders_data_migration( Database $db, string $table_full_name, array $columns, array $options ) {
		global $wpdb;

		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$table_full_name}'" ) ) {
			return;
		}

		if ( empty( $columns['status'] ) ) {
			return;
		}

		$row = $wpdb->get_row( "SHOW COLUMNS FROM `{$table_full_name}` LIKE 'status'" );

		// Add the ENUM value if it is not already supported.
		if ( ! $row || false === stripos( $row->Type, 'failed' ) ) {
			$wpdb->query( "ALTER TABLE `{$table_full_name}` MODIFY COLUMN `status` " . $columns['status'] );
		}

		// Recover orders left with an empty status (failed payments written
		// before `failed` was a valid ENUM value, coerced by MySQL to '').
		$wpdb->query( "UPDATE `{$table_full_name}` SET `status` = 'failed' WHERE `status` = ''" );
	}

	/**
	 * Updates or adds the database version in the options table.
	 *
	 * @return void
	 */
	protected function update_db_version() {
		/**
		 * Fires before updating the database version.
		 */
		do_action( 'easycommerce_before_update_db_version' );

		update_option( 'easycommerce_db_version', EASYCOMMERCE_VERSION );

		/**
		 * Fires after updating the database version.
		 */
		do_action( 'easycommerce_after_update_db_version' );
	}
}

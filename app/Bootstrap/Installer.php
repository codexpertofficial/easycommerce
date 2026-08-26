<?php
namespace EasyCommerce\Bootstrap;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;
use EasyCommerce\Traits\Queue;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Bootstrap\Activator\Post_Type;
use EasyCommerce\Bootstrap\Activator\Taxonomy;

class Installer {
	use Queue;
	/**
	 * Runs the installation routines.
	 *
	 * @return void
	 */
	public static function install() {

		// Activation fires before the `init` hook that normally loads the text domain, so without
		// this the store pages below would always be created with English titles.
		load_plugin_textdomain( 'easycommerce', false, dirname( plugin_basename( EASYCOMMERCE_FILE ) ) . '/languages' );

		$installer = new self();

		/**
		 * Fires before the installation process starts.
		 */
		do_action( 'easycommerce_before_install', $installer );

		// Hook for handling data migration for specific tables
		add_action( 'easycommerce_migrate_coupons_table', array( $installer, 'handle_coupons_data_migration' ), 10, 4 );
		add_action( 'easycommerce_migrate_cart_sessions_table', array( $installer, 'handle_cart_sessions_data_migration' ), 10, 4 );
		add_action( 'easycommerce_migrate_orders_table', array( $installer, 'handle_orders_data_migration' ), 10, 4 );
		add_action( 'easycommerce_migrate_transactions_table', array( $installer, 'handle_transactions_data_migration' ), 10, 4 );

		$is_fresh_install = ! get_option( 'easycommerce_activated' );

		if ( ! $installer->is_database_up_to_date() ) {
			$installer->prepare();
			$installer->set_cron();
			$installer->create_tables();
			$installer->update_existing_tables();
			$installer->migrate_stripe_customer_meta();
			$installer->update_db_version();
		}

		if ( $is_fresh_install ) {
			$installer->setup_defaults();
		}

		// Register CPT and taxonomies so their rewrite rules are included in the flush.
		( new Post_Type() )->register();
		( new Taxonomy() )->register();
		flush_rewrite_rules();

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
	 * Seeds sensible defaults on a fresh install so the store is usable
	 * even when the setup wizard is skipped.
	 *
	 * Each value is only written if the slot is currently empty, so existing
	 * data is never overwritten (e.g. on upgrades or re-activations).
	 *
	 * @return void
	 */
	private function setup_defaults() {

		// Store name → WordPress site title.
		if ( ! Utility::get_option( 'general', 'business', 'store_name' ) ) {
			Utility::set_option( 'general', 'business', 'store_name', get_bloginfo( 'name' ) );
		}

		// Business email → WordPress admin email.
		if ( ! Utility::get_option( 'general', 'business', 'business_email' ) ) {
			Utility::set_option( 'general', 'business', 'business_email', get_option( 'admin_email' ) );
		}

		// Cash on Delivery → enable automatically when no payment method is configured.
		if ( empty( Utility::get_option( 'payment', 'methods', 'active_methods', array() ) ) ) {
			Utility::set_option( 'payment', 'methods', 'active_methods', array( 'cash-on-delivery' ) );
		}

		// Store pages → create each one if the setting is missing or the post was deleted.
		$store         = get_option( 'easycommerce-general-store', array() );
		$page_template = 'full-width-layout.php';

		$pages = array(
			'shop'      => array(
				'title'   => __( 'Shop', 'easycommerce' ),
				'content' => '<!-- wp:easycommerce/template-2 {"ProductPerPage":9,"columns":3} /-->',
			),
			'checkout'  => array(
				'title'   => __( 'Checkout', 'easycommerce' ),
				'content' => '<!-- wp:shortcode -->[easycommerce-checkout]<!-- /wp:shortcode -->',
			),
			'dashboard' => array(
				'title'   => __( 'Dashboard', 'easycommerce' ),
				'content' => '<!-- wp:shortcode -->[easycommerce-dashboard]<!-- /wp:shortcode -->',
			),
			'payment'   => array(
				'title'   => __( 'Payment', 'easycommerce' ),
				'content' => '<!-- wp:shortcode -->[easycommerce-payment]<!-- /wp:shortcode -->',
			),
		);

		foreach ( $pages as $key => $page_data ) {
			if ( ! empty( $store[ $key ] ) && get_post( $store[ $key ] ) ) {
				continue;
			}

			$page_id = Utility::create_post( array(
				'type'    => 'page',
				'title'   => $page_data['title'],
				'content' => $page_data['content'],
			) );

			if ( $page_id && ! is_wp_error( $page_id ) ) {
				$store[ $key ] = $page_id;
				update_post_meta( $page_id, '_wp_page_template', $page_template );
			}
		}

		update_option( 'easycommerce-general-store', $store );
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

		// Seed Cash on Delivery as an active payment method on fresh install
		$this->seed_default_payment_methods();

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
	 * Seeds Cash on Delivery as an active payment method on fresh install.
	 *
	 * @return void
	 */
	protected function seed_default_payment_methods() {
		if ( false !== get_option( 'easycommerce-payment-methods' ) ) {
			return;
		}

		update_option(
			'easycommerce-payment-methods',
			array( 'active_methods' => array( 'cash-on-delivery' ) )
		);
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

		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_full_name ) ) ) ) {
			return;
		}

		// Only ever splice a hardcoded ENUM definition into ALTER TABLE; guard against a future filter injecting arbitrary SQL.
		if ( empty( $columns['status'] ) || ! preg_match( '/^ENUM\s*\(/i', $columns['status'] ) ) {
			return;
		}

		$current = $wpdb->get_row( $wpdb->prepare( "SHOW COLUMNS FROM `{$table_full_name}` LIKE %s", 'status' ) );
		if ( $current && false === strpos( (string) $current->Type, 'payment_initiated' ) ) {
			$wpdb->query( "ALTER TABLE `{$table_full_name}` MODIFY COLUMN `status` " . $columns['status'] );
		}
	}

	/**
	 * Migrates the Stripe customer id from the legacy `_stripe_customer_id` user-meta
	 * key to the canonical `stripe_customer_id` so existing customers are reused after
	 * the PaymentIntent helper was standardized onto the canonical key.
	 *
	 * @return void
	 */
	public function migrate_stripe_customer_meta() {
		global $wpdb;

		// Where both keys exist, keep the canonical one (written by the main gateway) and drop the legacy duplicate.
		$wpdb->query(
			"DELETE legacy FROM {$wpdb->usermeta} legacy
			INNER JOIN {$wpdb->usermeta} canonical
				ON canonical.user_id = legacy.user_id AND canonical.meta_key = 'stripe_customer_id'
			WHERE legacy.meta_key = '_stripe_customer_id'"
		);

		// Rename the remaining legacy keys to the canonical key.
		$wpdb->query( "UPDATE {$wpdb->usermeta} SET meta_key = 'stripe_customer_id' WHERE meta_key = '_stripe_customer_id'" );
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

		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_full_name ) ) ) ) {
			return;
		}

		if ( empty( $columns['status'] ) || ! preg_match( '/^ENUM\s*\(/i', $columns['status'] ) ) {
			return;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SHOW COLUMNS FROM `{$table_full_name}` LIKE %s", 'status' ) );

		// Add the ENUM value if it is not already supported.
		if ( ! $row || false === strpos( (string) $row->Type, 'failed' ) ) {
			$wpdb->query( "ALTER TABLE `{$table_full_name}` MODIFY COLUMN `status` " . $columns['status'] );
		}

		// Recover orders left with an empty status (failed payments written
		// before `failed` was a valid ENUM value, coerced by MySQL to '').
		$wpdb->query( "UPDATE `{$table_full_name}` SET `status` = 'failed' WHERE `status` = ''" );
	}

	/**
	 * Handles schema migration for the transactions table during schema updates.
	 *
	 * Adds a UNIQUE(transaction_id) constraint on existing installs so a
	 * duplicate gateway transaction id can never be recorded twice (which would
	 * corrupt the payment audit trail and reconciliation). Existing duplicate
	 * rows are removed first — the earliest row (smallest id) is kept for each
	 * transaction_id — because MySQL rejects the constraint while duplicates
	 * remain. Distinct transactions carry distinct gateway ids, so only true
	 * duplicates are deleted; no distinct transaction is lost. dbDelta does not
	 * add keys to existing tables, so the change is applied explicitly here and
	 * guarded to run at most once.
	 *
	 * @param Database $db              Database instance.
	 * @param string   $table_full_name Full table name with prefix.
	 * @param array    $columns         Current column definitions from config.
	 * @param array    $options         Current table options from config.
	 *
	 * @return void
	 */
	public function handle_transactions_data_migration( Database $db, string $table_full_name, array $columns, array $options ) {
		global $wpdb;

		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_full_name ) ) ) ) {
			return;
		}

		// Idempotent: bail if the unique key already exists.
		$existing_index = $wpdb->get_var(
			$wpdb->prepare( "SHOW INDEX FROM `{$table_full_name}` WHERE Key_name = %s", 'uk_transaction_id' )
		);
		if ( $existing_index ) {
			return;
		}

		// De-duplicate before the constraint can be applied: keep the earliest
		// row per transaction_id, delete the rest.
		$wpdb->query(
			"DELETE dup FROM `{$table_full_name}` dup
			INNER JOIN `{$table_full_name}` keep
				ON keep.transaction_id = dup.transaction_id
				AND keep.id < dup.id"
		);

		// Apply the uniqueness guarantee.
		$wpdb->query( "ALTER TABLE `{$table_full_name}` ADD UNIQUE KEY `uk_transaction_id` (`transaction_id`)" );
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

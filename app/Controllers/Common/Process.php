<?php
namespace EasyCommerce\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Traits\Queue;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Traits\Rest;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\API\Importer;
use EasyCommerce\Models\Abandoned_Cart as Abandoned_Cart_Model;
use EasyCommerce\Models\Cart;

class Process {

	use Hook;
	use Asset;
	use Cache;
	use Queue;
	use Cleaner;
	use Rest;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'easycommerce_prepare_background', array( $this, 'download_geo_db' ) );
		$this->action( 'easycommerce_install_addon', array( $this, 'handle_addon_installation' ) );
		$this->action( 'easycommerce_process_import_batch', array( $this, 'process_import_batch' ), 10, 2 );
		$this->action( 'easycommerce_sample_product_import', array( $this, 'import_sample_products' ), 10, 2 );

		// Abandoned-cart auto-send reminders
		$this->filter( 'cron_schedules', array( $this, 'register_cron_intervals' ) );
		$this->action( 'init', array( $this, 'sync_abandoned_cart_cron' ), 20 );
		$this->action( 'easycommerce_send_abandoned_cart_reminders', array( $this, 'send_abandoned_cart_reminders' ) );
	}

	/**
	 * Registers custom WP-Cron recurrence intervals.
	 */
	public function register_cron_intervals( $schedules ) {
		$schedules['easycommerce_five_minutes'] = array(
			'interval' => 300,
			'display'  => __( 'Every 5 Minutes', 'easycommerce' ),
		);
		return $schedules;
	}

	/**
	 * Keeps the abandoned-cart auto-send cron in sync with the setting.
	 * Schedules every 5 minutes when enabled; unschedules when disabled.
	 */
	public function sync_abandoned_cart_cron() {
		$enabled = (bool) Utility::get_option( 'abandoned-cart', 'settings', 'recurring_email', false );

		if ( $enabled ) {
			$this->schedule_recurring( time(), 'easycommerce_five_minutes', 'easycommerce_send_abandoned_cart_reminders' );
		} else {
			$this->clear_schedules( 'easycommerce_send_abandoned_cart_reminders' );
		}
	}

	/**
	 * Cron callback: sends a reminder to every pending abandoned cart that has not yet received one.
	 */
	public function send_abandoned_cart_reminders() {
		if ( ! (bool) Utility::get_option( 'abandoned-cart', 'settings', 'recurring_email', false ) ) {
			return;
		}

		$abandoned_cart_obj = new Abandoned_Cart_Model();
		$entries            = $abandoned_cart_obj->list();

		foreach ( $entries as $entry ) {
			if ( empty( $entry->hash ) || $entry->status !== 'pending' ) {
				continue;
			}

			if ( (int) $entry->reminders !== 0 ) {
				continue;
			}

			$cart = new Cart( $entry->hash );

			if ( $cart->get_item_count() < 1 || empty( $cart->get_customer_email() ) ) {
				continue;
			}

			do_action( 'easycommerce_send_abandoned_reminder', $entry->hash );

			$mail_sent = apply_filters( 'easycommerce_mail_sent', false, $entry->hash, null );

			if ( $mail_sent ) {
				$reminders               = (int) $cart->get_data( 'reminders' );
				$cart->cart['reminders'] = $reminders + 1;
				$cart->save();
			}
		}
	}
	public function process_import_batch( $import_id, $offset ) {
		$importer = new Importer();
		$rows = get_option( 'easycommerce_importer_rows', array() );
		$all_imports = get_option( 'easycommerce_import_statuses', array() );

		if ( empty( $rows ) || ! isset( $all_imports[ $import_id ] ) ) {
			return;
		}

		$status = $all_imports[ $import_id ];
		$batch_size = 2;
		$batch = array_slice( $rows, $offset, $batch_size );

		foreach ( $batch as $index => $row ) {
			try {
				$importer->create_product( $row );
				$status['imported']++;
			} catch ( \Exception $e ) {
				$status['errors'][] = 'Row ' . ($offset + $index + 1) . ': ' . $e->getMessage();
			}
			$status['processed']++;
		}

		// Save updated status
		$all_imports[ $import_id ] = $status;
		update_option( 'easycommerce_import_statuses', $all_imports );

		$next_offset = $offset + $batch_size;

		if ( $next_offset < count( $rows ) ) {
			$this->schedule( 'easycommerce_process_import_batch', array( $import_id, $next_offset ) );
		} else {
			$status['status']       = 'completed';
			$status['completed_at'] = current_time( 'mysql' );
			$all_imports[ $import_id ] = $status;
			update_option( 'easycommerce_import_statuses', $all_imports );

			delete_option( 'easycommerce_importer_rows' );
			delete_option( 'easycommerce_importer_headers' );
			delete_option( 'easycommerce_importer_mapping' );
		}
	}

	public function import_sample_products() {
		$file = EASYCOMMERCE_PLUGIN_DIR . 'samples/dummy-data/products.csv';
		$importer = new Importer();

		if ( ! file_exists( $file ) ) {
			return $this->response_error( 'Demo CSV not found', 404 );
		}

		$handle = fopen( $file, 'r' );

		if ( ! $handle ) {
			return $this->response_error( 'Unable to open demo file', 500 );
		}

		$rows    = array();
		$headers = fgetcsv( $handle );

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$rows[] = $row; 
		}

		fclose( $handle );

		$sanitized_headers = $this->sanitize( $headers, 'array' );
		update_option( 'easycommerce_importer_headers', $sanitized_headers );
		update_option( 'easycommerce_importer_rows', $rows );

		$mapping = array();
		foreach ( $headers as $header ) {
			$mapping[ $header ] = $header;
		}
		update_option( 'easycommerce_importer_mapping', $mapping );

		$imported_count = 0;
		$failed_count = 0;
		$errors = array();

		foreach ( $rows as $row ) {
			try {
				$product_id = $importer->create_product( $row );
				if ( $product_id ) {
					$imported_count++;
				} else {
					$failed_count++;
				}
			} catch ( Exception $e ) {
				$failed_count++;
				$errors[] = $e->getMessage();
			}
		}

		return $this->response_success( array(
			'headers'    => $headers,
			'mapping'    => $mapping,
			'total_rows' => count( $rows ),
			'imported'   => $imported_count,
			'failed'     => $failed_count,
			'errors'     => $errors,
			'message' => sprintf( __( 'Successfully imported %d of %d demo products', 'easycommerce' ), $imported_count, count( $rows ) )
		) );
	}

	/**
	 * Downloads the `locations.json` database file from the CDN
	 */
	public function download_geo_db() {

		$remote_url = 'https://cdn.easycommerce.dev/locations.json';
		$upload_dir = wp_upload_dir();
		$save_path  = $upload_dir['basedir'] . '/easycommerce/locations.json';

		// Ensure the directory exists
		if ( ! file_exists( $upload_dir['basedir'] . '/easycommerce' ) ) {
			wp_mkdir_p( $upload_dir['basedir'] . '/easycommerce' );
		}

		// Download file
		$response = wp_remote_get( $remote_url, array( 'timeout' => 600 ) );

		if ( is_wp_error( $response ) ) {

			$this->schedule( 'easycommerce_prepare_background' );
			return;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! empty( $body ) ) {
			file_put_contents( $save_path, $body );
			
			update_option( 'easycommerce-locations_db_loaded', 1 );
			$this->unschedule( 'easycommerce_prepare_background' );
		}
	}

	public function handle_addon_installation( $slug ) {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_dir  = WP_PLUGIN_DIR . '/' . $slug;
		$plugin_file = "{$slug}/{$slug}.php";

		if ( file_exists( $plugin_dir ) ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				wp_clean_plugins_cache();
				activate_plugin( $plugin_file );
			}
			return;
		}
		$api     = get_option( 'easycommerce_api' );
		$headers = isset( $api->email ) ? array( 'email' => $api->email ) : array();

		$data_url = easycommerce_dev_store( "/wp-json/easycommerce/v1/hub/addons/{$slug}" );
		$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $data_url, array( 'headers' => $headers ) ) ) );

		if ( ! empty( $response->success ) && $response->success && ! empty( $response->data->download_url ) ) {
			$temp_file = download_url( $response->data->download_url );

			if ( ! is_wp_error( $temp_file ) ) {
				$unzip_result = unzip_file( $temp_file, trailingslashit( WP_PLUGIN_DIR ) . $slug );
				@unlink( $temp_file );

				if ( is_wp_error( $unzip_result ) ) {
					return;
				}

				if ( ! is_plugin_active( $plugin_file ) ) {
					wp_clean_plugins_cache();
					activate_plugin( $plugin_file );
				}
			}
		}
	}
}
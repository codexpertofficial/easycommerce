<?php
namespace EasyCommerce\Bootstrap;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;

class Uninstaller {

	/**
	 * Runs the uninstallation routines.
	 *
	 * @return void
	 */
	public static function uninstall() {

		$uninstaller = new self();

		/**
		 * Fires before the uninstallation process starts.
		 */
		do_action( 'easycommerce_before_uninstall', $uninstaller );

		$uninstaller->delete_flags();

		/**
		 * Fires after the uninstallation process is completed.
		 */
		do_action( 'easycommerce_after_uninstall', $uninstaller );
	}

	/**
	 * Removes stored flags and options related to the plugin.
	 *
	 * @return void
	 */
	protected function delete_flags() {
		/**
		 * Filters the list of options to be deleted during uninstallation.
		 *
		 * @param array $deletable_options List of option keys to be deleted.
		 */
		$deletable_options = apply_filters(
			'easycommerce_uninstall_deletable_options',
			array( 'easycommerce_db_version' )
		);

		foreach ( $deletable_options as $option ) {
			delete_option( $option );
		}
	}
}

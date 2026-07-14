<?php
/**
 * @package EasyCommerce
 *
 * Plugin Name: EasyCommerce
 * Plugin URI: https://wordpress.org/plugins/easycommerce/
 * Author: EasyCommerce
 * Author URI: https://easycommerce.dev/
 * Description: AI-Powered Ecommerce To Sell Physical & Digital Products
 * Version: 1.45
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * Text Domain: easycommerce
 * Domain Path: /languages
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * EasyCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * EasyCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace EasyCommerce;

defined( 'ABSPATH' ) || exit;

define( 'EASYCOMMERCE_FILE', __FILE__ );
define( 'EASYCOMMERCE_VERSION', '1.45' );
define( 'EASYCOMMERCE_PLUGIN_DIR', plugin_dir_path( EASYCOMMERCE_FILE ) );
define( 'EASYCOMMERCE_PLUGIN_URL', plugin_dir_url( EASYCOMMERCE_FILE ) );
define( 'EASYCOMMERCE_ASSETS_URL', EASYCOMMERCE_PLUGIN_URL . 'assets/' );
define( 'EASYCOMMERCE_BUILD_URL', EASYCOMMERCE_PLUGIN_URL . 'build/' );
define( 'EASYCOMMERCE_SPA_URL', EASYCOMMERCE_PLUGIN_URL . 'spa/' );

require_once 'vendor/autoload.php';

/**
 * Install the plugin.
 *
 * This function is triggered on plugin activation.
 * It installs necessary database tables, seeds initial data,
 * and checks for database version compatibility.
 *
 * @return void
 */
register_activation_hook( EASYCOMMERCE_FILE, __NAMESPACE__ . '\\easycommerce_install' );
function easycommerce_install() {
	Bootstrap\Installer::install();
}

/**
 * Activate the plugin.
 *
 * This function is triggered when the plugin is loaded.
 * It sets up cron jobs, registers custom user roles, and
 * performs other necessary activation tasks.
 *
 * @return void
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\easycommerce_activate' );
function easycommerce_activate() {
	Bootstrap\Activator::activate();
}

/**
 * Initialize the plugin.
 *
 * This function is triggered after all active plugins are fully loaded.
 * It sets the plugin runtime environment and initializes hooks.
 *
 * @return void
 */
add_action( 'init', __NAMESPACE__ . '\\easycommerce_initialize', -999 );
function easycommerce_initialize() {
	Bootstrap\Initializer::initialize();
}

/**
 * Uninstall the plugin.
 *
 * This function is triggered when the plugin is deactivated.
 * It cleans up resources, removes options, and unregisters custom roles.
 *
 * @return void
 */
register_deactivation_hook( EASYCOMMERCE_FILE, __NAMESPACE__ . '\\easycommerce_uninstall' );
function easycommerce_uninstall() {
	Bootstrap\Uninstaller::uninstall();
}

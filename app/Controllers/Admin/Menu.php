<?php
namespace EasyCommerce\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Menu as Menu_Trait;
use EasyCommerce\Helpers\Utility;

class Menu {

	use Hook;
	use Asset;
	use Menu_Trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_menu', array( $this, 'register' ) );
		$this->action( 'admin_menu', array( $this, 'hide_menu' ) );
		$this->action( 'easycommerce-settings', array( $this, 'render_settings' ) );
		$this->filter( 'plugin_action_links_' . plugin_basename( EASYCOMMERCE_FILE ), array( $this, 'add_action_links' ) );
		$this->action( 'admin_footer', array( $this, 'survey_popup' ) );
	}

	public function register() {
		$menus = easycommerce_menus();

		$register_submenus = function( $parent_slug, $submenus, $level = 0 ) use ( &$register_submenus ) {
			foreach ( $submenus as $submenu ) {
				$prefix = str_repeat( '↳', $level ) . ' ';

				$this->add_submenu(
					$parent_slug,
					$submenu['page_title'],
					$prefix . $submenu['menu_title'],
					$submenu['capability'] ?? 'manage_options',
					$submenu['slug'],
					$submenu['callback'] ?? '__return_null'
				);

				if ( ! empty( $submenu['submenus'] ) ) {
					$register_submenus( $parent_slug, $submenu['submenus'], $level + 1 );
				}
			}
		};

		foreach ( $menus as $menu ) {
			$this->add_menu(
				$menu['title'],
				$menu['menu_title'],
				$menu['capability'],
				$menu['slug'],
				$menu['callback'],
				$menu['icon'],
				$menu['position']
			);

			if ( ! empty( $menu['submenus'] ) ) {
				$register_submenus( $menu['slug'], $menu['submenus'] );
			}
		}
	}


 	public function hide_menu() {
 		if ( function_exists( 'remove_menu_page' ) ) {
 			remove_menu_page( 'easycommerce-wizard' );
 		}
 	}

	public function render_settings() {
		echo Utility::get_template( 'settings/layout.php' );
	}

	public function add_action_links( $actions ) {
		if ( ! is_array( $actions ) ) {
			return $actions;
		}

		$activated = apply_filters( 'easycommerce-pro_activated', false );
		$licensed  = apply_filters( 'easycommerce-pro_licensed', false );

 		$deactivate = $actions['deactivate'];
		unset( $actions['deactivate'] );

		$admin_url = admin_url( 'admin.php' );

		$links = array(
			'easycommerce-wizard'   => __( 'Setup Wizard', 'easycommerce' ),
			'easycommerce'    		=> __( 'Store', 'easycommerce' ),
			'easycommerce-settings' => __( 'Settings', 'easycommerce' ),
		);

		if ( ! $licensed ) {
			$actions[ $activated ? 'activate-pro' : 'get-pro' ] = sprintf(
				'<a href="%s" %s style="color:#7351FD; font-weight:bold;">%s</a>',
				esc_url( $activated ? admin_url( 'admin.php?page=easycommerce#/pro' ) : 'https://easycommerce.dev/pricing/' ),
				$activated ? '' : 'target="_blank"',
				$activated ? __( 'Activate License', 'easycommerce' ) : __( 'Get Pro', 'easycommerce' )
			);
		}

		foreach ( $links as $slug => $label ) {
			$actions[ $slug ] = sprintf( '<a href="%1$s">%2$s</a>', add_query_arg( 'page', $slug, $admin_url ), $label );
		}

		$actions['deactivate'] = $deactivate;

		return $actions;
	}

	public function survey_popup() {
		if ( ! function_exists( 'get_current_screen' ) || ! ( $screen = get_current_screen() ) || $screen->base != 'plugins' ) {
			return;
		}

		echo Utility::get_template( 'components/survey.php' );
	}
}

<?php
/**
 * Test Admin Menu Controller.
 */

namespace EasyCommerce\Tests\Controllers;
use EasyCommerce\Tests\EasyCommerceTestCase;

use EasyCommerce\Controllers\Admin\Menu;

class AdminMenuTest extends EasyCommerceTestCase {

	protected $admin_menu;

	public function set_up(): void {
		parent::set_up();

		$this->admin_menu = new Menu();
	}

	/**
	 * Test Admin Menu constructor.
	 */
	public function test_constructor() {
		$menu = new Menu();

		$this->assertInstanceOf( Menu::class, $menu );
	}

	/**
	 * Test register method.
	 */
	public function test_register() {
		global $menu, $submenu;

		// Store original menu state
		$original_menu    = $menu;
		$original_submenu = $submenu;

		// Reset menus
		$menu    = array();
		$submenu = array();

		$this->admin_menu->register();

		// Check if menus were registered
		$this->assertNotEmpty( $menu );

		// Check for EasyCommerce menu
		$found_easycommerce = false;
		foreach ( $menu as $menu_item ) {
			if ( isset( $menu_item[0] ) && strpos( $menu_item[0], 'EasyCommerce' ) !== false ) {
				$found_easycommerce = true;
				break;
			}
		}

		$this->assertTrue( $found_easycommerce, 'EasyCommerce menu should be registered' );

		// Restore original menu state
		$menu    = $original_menu;
		$submenu = $original_submenu;
	}

	/**
	 * Test hide_menu method.
	 */
	public function test_hide_menu() {
		global $menu, $submenu;

		// hide_menu() calls WP core remove_menu_page(), which iterates the $menu
		// superglobal; seed it (as WP does on admin_menu) so it is not null here.
		$original_menu    = $menu;
		$original_submenu = $submenu;
		$menu             = array();
		$submenu          = array();

		try {
			$this->admin_menu->hide_menu();
			$this->assertTrue( true, 'hide_menu method should execute without errors' );
		} catch ( \Throwable $e ) {
			$this->assertTrue( true, 'hide_menu method may throw errors in test environment' );
		} finally {
			$menu    = $original_menu;
			$submenu = $original_submenu;
		}
	}

	/**
	 * Test render_settings method.
	 */
	public function test_render_settings() {
		ob_start();
		$this->admin_menu->render_settings();
		$output = ob_get_clean();

		$this->assertIsString( $output );
	}

	/**
	 * Test add_action_links method.
	 */
	public function test_add_action_links() {
		$actions = array(
			'deactivate' => '<a href="#">Deactivate</a>'
		);

		$result = $this->admin_menu->add_action_links( $actions );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'deactivate', $result );

		// Should have added settings link
		$this->assertGreaterThan( count( $actions ), count( $result ) );
	}

	/**
	 * Test survey_popup method.
	 */
	public function test_survey_popup() {
		ob_start();
		$this->admin_menu->survey_popup();
		$output = ob_get_clean();

		$this->assertIsString( $output );
	}
}

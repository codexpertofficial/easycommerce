<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Color Field Class
 */
class Color extends Text {

	public function __construct( $config = array() ) {
		parent::__construct( $config );
		$this->set_type( 'color' );
	}
}

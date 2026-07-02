<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Submit Field Class
 */
class Submit extends Text {

	public function __construct( $config = array() ) {
		parent::__construct( $config );
		$this->set_type( 'submit' );
	}
}

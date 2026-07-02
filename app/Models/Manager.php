<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\User;

/**
 * Concrete Manager Class
 */
class Manager extends User {

	protected $role = 'manager';

	public function __construct( $id = null ) {
		parent::__construct( $id );
	}
}

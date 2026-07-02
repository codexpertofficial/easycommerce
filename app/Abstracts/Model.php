<?php
namespace EasyCommerce\Abstracts;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;

/**
 * Abstract Model Class
 * Provides a base for all model classes with Database dependency injection.
 */
abstract class Model {

	/**
	 * @var Database Instance for handling DB operations
	 */
	public $db;

	/**
	 * @var string Table name (without prefix)
	 */
	protected $table;

	/**
	 * @var string Primary key column
	 */
	protected $primary_key = 'id';

	/**
	 * Constructor for the Model class.
	 *
	 * @param Database|null $db Optional. Database instance for dependency injection.
	 */
	public function __construct( Database $db = null ) {
		$this->db = $db ?: new Database( $this->table, $this->primary_key );
	}
}
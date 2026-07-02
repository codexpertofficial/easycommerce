<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Meta;

/**
 * Class Product_Meta
 * Handles product meta operations.
 *
 * This class extends the abstract Meta class to handle operations
 * related to product metadata. It specifies the table name as
 * 'product_meta' and uses 'product_id' as the unique identifier key.
 *
 * @package EasyCommerce\Model
 */
class Product_Meta extends Meta {

	/**
	 * Product_Meta constructor.
	 * Initializes the Product_Meta class with the specified table name and unique identifier key.
	 */
	public function __construct() {
		parent::__construct( 'product_meta', 'product_id' );
	}
}

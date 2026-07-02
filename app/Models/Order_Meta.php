<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Meta;

/**
 * Class Order_Meta
 * Handles order meta operations.
 *
 * This class extends the abstract Meta class to handle operations
 * related to order metadata. It specifies the table name as
 * 'order_meta' and uses 'order_id' as the unique identifier key.
 *
 * @package EasyCommerce\Model
 */
class Order_Meta extends Meta {

	/**
	 * Order_Meta constructor.
	 * Initializes the Order_Meta class with the specified table name and unique identifier key.
	 */
	public function __construct() {
		parent::__construct( 'order_meta', 'order_id' );
	}
}

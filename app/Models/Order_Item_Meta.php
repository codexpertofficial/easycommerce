<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Meta;

/**
 * Class Order_Item_Meta
 * Handles product meta operations.
 *
 * This class extends the abstract Meta class to handle operations
 * related to product metadata. It specifies the table name as
 * 'order_item_meta' and uses 'order_item_id' as the unique identifier key.
 *
 * @package EasyCommerce\Model
 */
class Order_Item_Meta extends Meta {

	/**
	 * Order_Item_Meta constructor.
	 * Initializes the Order_Item_Meta class with the specified table name and unique identifier key.
	 */
	public function __construct() {
		parent::__construct( 'order_item_meta', 'order_item_id' );
	}
}

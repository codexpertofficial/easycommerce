<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Meta;

/**
 * Class Product_Variation_Meta
 * Handles product variation meta operations.
 *
 * This class extends the abstract Meta class to handle operations
 * related to product variation metadata. It specifies the table name as
 * 'product_variation_meta' and uses 'variation_id' as the unique identifier key.
 *
 * @package EasyCommerce\Model
 */
class Product_Variation_Meta extends Meta {

	/**
	 * Product_Variation_Meta constructor.
	 * Initializes the Product_Variation_Meta class with the specified table name and unique identifier key.
	 */
	public function __construct() {
		parent::__construct( 'product_variation_meta', 'variation_id' );
	}
}

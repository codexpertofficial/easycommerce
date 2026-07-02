<?php
namespace EasyCommerce\Abstracts;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Rest;

/**
 * Abstract API class
 *
 * Provides common functionality for API classes.
 */
abstract class API {

    use Rest;

}
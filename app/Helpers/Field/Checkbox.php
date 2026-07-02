<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Checkbox Field Class
 */
class Checkbox extends Field {

	/**
	 * Get the field label.
	 *
	 * @return string
	 */
	public function get_value() {
		$value = $this->value === '' ? $this->default : $this->value;
		return apply_filters( 'easycommerce_field_get_value', $value, $this );
	}

	public function render() {
		$template = '<div id="%1$s" class="%2$s"><label><input type="checkbox" class="easycommerce-input-checkoutbox" id="%3$s" name="%4$s" value="1" %5$s %6$s %7$s %8$s /> %9$s</label>%10$s</div>';

		return sprintf(
			$template,
			$this->get_wrapper_id(),                // %1$s: Wrapper ID
			$this->get_wrapper_class(),             // %2$s: Wrapper class
			$this->get_field_id(),                  // %3$s: Input ID
			$this->get_name(),                      // %4$s: Input name
			$this->get_value() ? 'checked' : '',    // %5$s: Checked attribute
			$this->is_disabled() ? 'disabled' : '', // %6$s: Disabled attribute
			$this->is_required() ? 'required' : '', // %7$s: Required attribute
			$this->render_atts(),                   // %8$s: Rendered additional attributes
			$this->get_label(),                     // %9$s: Label text
			$this->get_description( 'p' )           // %10$s: Description
		);
	}
}

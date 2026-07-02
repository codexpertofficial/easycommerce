<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

class Multicheck extends Field {

	protected $option_type = 'checkbox';

	public function render() {
		$template = '<div id="%1$s" class="%2$s"><h5>%5$s</h5>%3$s%4$s</div>';

		$inputs   = '';
		$disabled = $this->is_disabled();

		foreach ( $this->get_options() as $key => $value ) {
			$is_field_disabled = is_array( $disabled ) ? in_array( $key, $disabled ) : $disabled;

			$checked = ( is_array( $this->get_value() ) && in_array( $key, $this->get_value() ) ) || $this->get_value() == $key ? 'checked' : '';

			$inputs .= sprintf(
				'<label><input type="%5$s" id="%1$s_%2$s" name="%6$s" value="%2$s" class="easycommerce-input-checkoutbox" %3$s %4$s %7$s /> %8$s</label>',
				$this->get_field_id(),                  // %1$s: Input ID base
				$key,                                   // %2$s: Option key
				$checked,                               // %3$s: Checked attribute
				$is_field_disabled ? 'disabled' : '',   // %4$s: Disabled attribute
				$this->option_type,                     // %5$s: Input type
				$this->get_name(),                      // %6$s: Input name
				$this->render_atts(),                   // %7$s: Additional attributes
				$value,                                  // %8$s: Option label
			);
		}

		return sprintf(
			$template,
			$this->get_wrapper_id(),                // %1$s: Wrapper ID
			$this->get_wrapper_class(),             // %2$s: Wrapper class
			$inputs,                                // %3$s: Inputs HTML
			$this->get_description( 'p' ),          // %4$s: Description
			$this->get_label()                      // %5$s: Label text
		);
	}
}

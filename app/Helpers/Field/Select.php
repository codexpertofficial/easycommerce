<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Select Field Class
 */
class Select extends Field {

	public function render() {
		$template = '
        <div id="%1$s" class="%2$s">
            <label for="%3$s">%4$s</label>
            <select id="%3$s" name="%5$s" class="%6$s" %7$s %8$s %9$s %10$s>%11$s</select>
            %12$s
        </div>';

		$options_html = '';
		$disabled     = $this->is_disabled();

		foreach ( $this->get_options() as $key => $value ) {
			$is_option_disabled = is_array( $disabled ) ? in_array( $key, $disabled ) : $disabled;

			$selected = (
				$this->is_multiple() && in_array( $key, (array) $this->get_value() )
			) || (
				! $this->is_multiple() && $this->get_value() == $key
			) ? 'selected' : '';

			$options_html .= sprintf(
				'<option value="%1$s" %2$s %3$s>%4$s</option>',
				$key,
				$selected,
				$is_option_disabled ? 'disabled' : '',
				$value
			);
		}

		return sprintf(
			$template,
			$this->get_wrapper_id(),                            // %1$s: Wrapper ID
			$this->get_wrapper_class(),                         // %2$s: Wrapper class
			$this->get_field_id(),                              // %3$s: Input ID
			$this->get_label(),                                 // %4$s: Label text
			$this->get_name(),                                  // %5$s: Input name
			$this->get_field_class(),                           // %6$s: Disabled attribute
			$this->is_disabled() === true ? 'disabled' : '',    // %7$s: Disabled attribute
			$this->is_multiple() ? 'multiple' : '',             // %8$s: Multiple attribute
			$this->is_required() ? 'required' : '',             // %9$s: Required attribute
			$this->render_atts(),                               // %10$s: Rendered additional attributes
			$options_html,                                      // %11$s: Options HTML
			$this->get_description( 'p' )                       // %12$s: Description
		);
	}
}

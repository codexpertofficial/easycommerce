<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Select Field Class
 */
class Select extends Field {

	/**
	 * Whether the field allows free-text input alongside the options list.
	 *
	 * @var bool
	 */
	protected $allow_input = false;

	/**
	 * Constructor.
	 *
	 * @param array $config Field configuration.
	 */
	public function __construct( $config = array() ) {
		parent::__construct( $config );

		$this->allow_input = apply_filters(
			'easycommerce_field_allow_input',
			(bool) ( $config['allow_input'] ?? false ),
			$config
		);
	}

	/**
	 * Whether the field allows free-text input alongside the options list.
	 *
	 * @return bool
	 */
	public function allow_input() {
		return apply_filters( 'easycommerce_field_get_allow_input', $this->allow_input, $this );
	}

	public function render() {

		// Open-text mode: render an input bound to a datalist so users can pick
		// an option or type their own value. Not supported with multiple select.
		if ( $this->allow_input() && ! $this->is_multiple() ) {
			return $this->render_open_text();
		}

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
				esc_attr( $key ),
				$selected,
				$is_option_disabled ? 'disabled' : '',
				esc_html( $value )
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

	/**
	 * Render the field as a free-text input backed by a datalist.
	 *
	 * @return string
	 */
	protected function render_open_text() {
		$template = '
        <div id="%1$s" class="%2$s">
            <label for="%3$s">%4$s</label>
            <input id="%3$s" name="%5$s" class="%6$s" type="text" list="%7$s" value="%8$s" placeholder="%9$s" autocomplete="off" %10$s %11$s %12$s>
            <datalist id="%7$s">%13$s</datalist>
            %14$s
        </div>';

		$options_html = '';
		foreach ( $this->get_options() as $key => $value ) {
			$options_html .= sprintf(
				'<option value="%1$s">%2$s</option>',
				esc_attr( $key ),
				esc_html( $value )
			);
		}

		return sprintf(
			$template,
			$this->get_wrapper_id(),                            // %1$s: Wrapper ID
			$this->get_wrapper_class(),                         // %2$s: Wrapper class
			$this->get_field_id(),                              // %3$s: Input ID
			$this->get_label(),                                 // %4$s: Label text
			$this->get_name(),                                  // %5$s: Input name
			$this->get_field_class(),                           // %6$s: Field class
			esc_attr( $this->get_field_id() . '-list' ),        // %7$s: Datalist ID
			esc_attr( $this->get_value() ),                     // %8$s: Current value
			esc_attr( $this->get_placeholder() ),               // %9$s: Placeholder
			$this->is_disabled() === true ? 'disabled' : '',    // %10$s: Disabled attribute
			$this->is_required() ? 'required' : '',             // %11$s: Required attribute
			$this->render_atts(),                               // %12$s: Rendered additional attributes
			$options_html,                                      // %13$s: Datalist options
			$this->get_description( 'p' )                       // %14$s: Description
		);
	}
}

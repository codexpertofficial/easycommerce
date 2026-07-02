<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Radio Field Class
 */
class Radio extends Multicheck {
	protected $option_type = 'radio';

	protected function get_input_class(): string {
        return 'easycommerce-input-radio';
    }

    public function render() {
        $output  = sprintf( '<div id="%s" class="%s">', $this->get_wrapper_id(), $this->get_wrapper_class() );
        $output .= sprintf( '<label class="ec-field-label">%s</label>', $this->get_label() );

        foreach ( $this->get_options() as $key => $value ) {
            $checked  = checked( $this->get_value(), $key, false );
            $disabled = $this->is_disabled() ? 'disabled' : '';

            $output .= sprintf(
                '<label><input type="radio" class="%s" id="%s_%s" name="%s" value="%s" %s %s /> %s</label>',
                $this->get_input_class(),
                $this->get_field_id(),
                esc_attr( $key ),
                esc_attr( $this->get_name() ),
                esc_attr( $key ),
                $checked,
                $disabled,
                esc_html( $value )
            );
        }

        $output .= $this->get_description( 'p' );
        $output .= '</div>';

        return $output;
    }
}

<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * File Field Class
 */
class Media extends Text {

	public function render() {
		wp_enqueue_media();

		$template =
		'<div id="%1$s" class="%2$s">
            <p>
                <label for="%3$s">%4$s</label>
                <span class="flex items-start gap-2">
                    <input type="hidden" name="%6$s" value="%7$s" class="easycommerce-field-media-value" />
                    <input type="text" value="%8$s" class="%9$s easycommerce-field-media-url" %10$s %12$s />
                    <button type="button" class="px-7 py-[9px] rounded-lg border text-ec-secondary font-medium text-base border-ec-secondary hover:border-royal-purple hover:text-royal-purple easycommerce-field-media" %12$s>%11$s</button>
                </span>
                %13$s
            </p>
        </div>';

		$value = $this->get_value();
		if ( empty( $url = wp_get_attachment_url( $value ) ) ) {
			$value = '';
			$url   = EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/demo-image-hd.png';
		}

		return sprintf(
			$template,
			$this->get_wrapper_id(),                    // %1$s: Wrapper ID
			$this->get_wrapper_class(),                 // %2$s: Wrapper class
			$this->get_field_id(),                      // %3$s: Input ID for `for` attribute in label
			$this->get_label(),                         // %4$s: Label text
			$this->get_type(),                          // %5$s: Input type
			$this->get_name(),                          // %6$s: Input name
			esc_attr( $value ),                         // %7$s: Input value
			esc_attr( $url ),                           // %8$s: Input URL
			esc_attr( $this->get_class() ),             // %9$s: Input class
			$this->is_required() ? 'required' : '',     // %10$s: Required attribute
			__( 'Upload your file', 'easycommerce' ),   // %11$s: Button text
			$this->render_atts(),                       // %12$s: Additional attributes
			$this->get_description( 'span' )            // %13$s: Description
		);
	}
}

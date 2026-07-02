<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * File Field Class
 */
class Image extends Text {

	public function render() {
		wp_enqueue_media();

		$template =
		'<div id="%1$s" class="%2$s easycommerce-field-image-wrapper">
            <p>
                <label for="%3$s">%4$s</label>
                <input type="hidden" name="%6$s" value="%7$s" %8$s class="easycommerce-field-image-value" %11$s />
                <img id="%3$s" src="%12$s" class="%9$s h-auto easycommerce-field-image" %11$s />
                %10$s
            </p>
        </div>';

		$value = $this->get_value();

		$url = wp_get_attachment_url( $value );

		if ( empty( $url ) ) {
			$value = '';
			$field_name = $this->get_name();

			// If this is a payment method logo field (e.g., 'credit_card_logo')
			if ( str_ends_with( $field_name, '_logo' ) ) {
				$payment_method = str_replace( '_logo', '', $field_name );
				$icon_file      = str_replace( '_', '-', $payment_method ) . '.svg';
				$icon_path      = EASYCOMMERCE_PLUGIN_DIR . 'assets/payment/img/' . $icon_file;

				// Use custom payment icon if it exists
				$url = file_exists( $icon_path ) ? EASYCOMMERCE_ASSETS_URL . 'payment/img/' . $icon_file: '';
			}

			// Fallback to demo image if no icon found
			if ( empty( $url ) ) {
				$url = EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/demo-image-hd.png';
			}
		}

		return sprintf(
			$template,
			$this->get_wrapper_id(),                // %1$s: Wrapper ID
			$this->get_wrapper_class(),             // %2$s: Wrapper class
			$this->get_field_id(),                  // %3$s: Input ID for `for` attribute in label
			$this->get_label(),                     // %4$s: Label text
			$this->get_type(),                      // %5$s: Input type (not directly used here but placeholder)
			$this->get_name(),                      // %6$s: Input name
			esc_attr( $value ),                     // %7$s: Input value
			$this->is_required() ? 'required' : '', // %8$s: Required attribute
			esc_attr( $this->get_class() ),         // %9$s: Input class
			$this->get_description( 'span' ),       // %10$s: Description
			$this->render_atts(),                   // %11$s: Additional attributes
			esc_attr( $url ),                       // %12$s: Image src
		);
	}
}

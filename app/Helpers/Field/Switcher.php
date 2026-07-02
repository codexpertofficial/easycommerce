<?php
namespace EasyCommerce\Helpers\Field;

use EasyCommerce\Abstracts\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Switcher Field Class
 */
class Switcher extends Checkbox {

	public function render() {
		$template = '<div id="%1$s" class="switch-wrapper %2$s">
                        <div class="switch">
                            <label class="switch-label">
                                <input type="checkbox" id="%3$s" name="%4$s" value="1" %5$s %6$s %7$s %8$s />
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="switch-desc">
                            <label>%9$s</label>
                            %10$s
                        </div>
                     </div>';

		if ( ! did_action( 'easycommerce-after_switch_render' ) ) {
			?>
			<style type="text/css">
				/* The switch - the box around the slider */
				.switch {
					position: relative;
					display: inline-block;
					width: 40px;
					height: 22px;
				}

				/* Hide default HTML checkbox */
				.switch input {
					opacity: 0;
					width: 0;
					height: 0;
				}

				/* The slider */
				.slider {
					position: absolute;
					cursor: pointer;
					top: 0;
					left: 0;
					right: 0;
					bottom: 0;
					background-color: #ccc;
					transition: .4s;
					border-radius: 34px;
				}

				.slider:before {
					position: absolute;
					content: "";
					height: 18px;
					width: 18px;
					left: 2px;
					bottom: 2px;
					background-color: white;
					transition: .4s;
					border-radius: 50%;
				}

				input:checked + .slider {
					background-color: var(--color-ec-primary);
				}

				input:focus + .slider {
					box-shadow: 0px 2px 4px 0px #00000040;
				}

				input:checked + .slider:before {
					transform: translateX(19px);
				}

				/* Optional: styling for label to the right */
				.switch-label {
					display: flex;
					align-items: center;
					gap: 10px;
				}
			</style>
			<?php
		}

		do_action( 'easycommerce-after_switch_render' );

		return sprintf(
			$template,
			$this->get_wrapper_id(),                // %1$s: Wrapper ID
			$this->get_wrapper_class(),             // %2$s: Wrapper class
			$this->get_field_id(),                  // %3$s: Input ID
			$this->get_name(),                      // %4$s: Input name
			$this->get_value() ? 'checked' : '',    // %5$s: Checked attribute
			$this->is_disabled() ? 'disabled' : '', // %6$s: Disabled attribute
			$this->is_required() ? 'required' : '', // %7$s: Required attribute
			$this->render_atts(),                   // %8$s: Additional attributes
			$this->get_label(),                     // %9$s: Label text
			$this->get_description( 'p' )           // %10$s: Description
		);
	}
}
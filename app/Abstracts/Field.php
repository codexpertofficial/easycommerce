<?php
namespace EasyCommerce\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Field Class
 */
abstract class Field {

	/**
	 * Field ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Field name attribute.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Field label.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Field value.
	 *
	 * @var string
	 */
	protected $value = '';

	/**
	 * Field description.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Field column size.
	 *
	 * @var int
	 */
	protected $cols = 1;

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder = '';

	/**
	 * Field options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Field default value.
	 *
	 * @var mixed
	 */
	protected $default = '';

	/**
	 * Whether the field is disabled.
	 *
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * Whether the field is readonly.
	 *
	 * @var bool
	 */
	protected $readonly = false;

	/**
	 * Whether the field is required.
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * Field type.
	 *
	 * @var string
	 */
	protected $type = 'text';

	/**
	 * Field CSS class.
	 *
	 * @var string
	 */
	protected $class = '';

	/**
	 * Field attributes.
	 *
	 * @var array
	 */
	protected $atts = array();

	/**
	 * Additional field args.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Whether the field supports multiple selections.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Constructor.
	 *
	 * @param array $config Field configuration.
	 */
	public function __construct( $config = array() ) {
		$this->id          = apply_filters( 'easycommerce_field_id', $config['id'] ?? '', $config );
		$this->name        = apply_filters( 'easycommerce_field_name', $config['name'] ?? $this->id, $config );
		$this->label       = apply_filters( 'easycommerce_field_label', $config['label'] ?? '', $config );
		$this->value       = apply_filters( 'easycommerce_field_value', $config['value'] ?? '', $config );
		$this->description = apply_filters( 'easycommerce_field_description', $config['description'] ?? $config['desc'] ?? '', $config );
		$this->cols        = apply_filters( 'easycommerce_field_cols', $config['cols'] ?? 1, $config );
		$this->placeholder = apply_filters( 'easycommerce_field_placeholder', $config['placeholder'] ?? '', $config );
		$this->options     = apply_filters( 'easycommerce_field_options', $config['options'] ?? array(), $config );
		$this->default     = apply_filters( 'easycommerce_field_default', $config['default'] ?? '', $config );
		$this->disabled    = apply_filters( 'easycommerce_field_disabled', $config['disabled'] ?? false, $config );
		$this->readonly    = apply_filters( 'easycommerce_field_readonly', $config['readonly'] ?? false, $config );
		$this->required    = apply_filters( 'easycommerce_field_required', $config['required'] ?? false, $config );
		$this->type        = apply_filters( 'easycommerce_field_type', $config['type'] ?? 'text', $config );
		$this->class       = apply_filters( 'easycommerce_field_class', $config['class'] ?? '', $config );
		$this->multiple    = apply_filters( 'easycommerce_field_multiple', $config['multiple'] ?? false, $config );
		$this->atts        = apply_filters( 'easycommerce_field_atts', $config['atts'] ?? array(), $config );
		$this->args        = apply_filters( 'easycommerce_field_args', $config['args'] ?? array(), $config );

		/**
		 * Fires after a field is constructed.
		 *
		 * @since 1.9
		 * @param Field $field The field instance.
		 */
		do_action( 'easycommerce_field_constructed', $this );
	}

	/**
	 * Set whether the field supports multiple selections.
	 *
	 * @param bool $multiple
	 */
	public function set_multiple( $multiple = false ) {
		/**
		 * Fires before the field multiple selection is set.
		 *
		 * @since 1.9
		 * @param bool  $multiple Whether the field supports multiple selections.
		 * @param Field $field    The field instance.
		 */
		do_action( 'easycommerce_before_field_set_multiple', $multiple, $this );

		$this->multiple = apply_filters( 'easycommerce_field_set_multiple', $multiple, $this );

		/**
		 * Fires after the field multiple selection is set.
		 *
		 * @since 1.9
		 * @param bool  $multiple Whether the field supports multiple selections.
		 * @param Field $field    The field instance.
		 */
		do_action( 'easycommerce_after_field_set_multiple', $multiple, $this );
	}

	/**
	 * Get whether the field supports multiple selections.
	 *
	 * @return bool
	 */
	public function is_multiple() {
		return apply_filters( 'easycommerce_field_is_multiple', $this->multiple, $this );
	}

	/**
	 * Get the field ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return apply_filters( 'easycommerce_field_get_id', $this->id, $this );
	}

	/**
	 * Get the field name attribute.
	 *
	 * @return string
	 */
	public function get_name() {
		return apply_filters( 'easycommerce_field_get_name', $this->name, $this );
	}

	/**
	 * Get the field label.
	 *
	 * @return string
	 */
	public function get_label() {
		return apply_filters( 'easycommerce_field_get_label', $this->label, $this );
	}

	/**
	 * Get the field value.
	 *
	 * @return string
	 */
	public function get_value() {
		return apply_filters( 'easycommerce_field_get_value', $this->value ?: $this->default, $this );
	}

	/**
	 * Get the field description.
	 *
	 * @param string $wrapper Wrapper HTML tag.
	 * @return string
	 */
	public function get_description( $wrapper = '' ) {
		if ( empty( $this->description ) ) {
			return '';
		}

		if ( empty( $wrapper ) ) {
			return apply_filters( 'easycommerce_field_get_description', $this->description, $this );
		}

		return apply_filters( 'easycommerce_field_get_description_wrapped', "<{$wrapper}>{$this->description}</{$wrapper}>", $this );
	}

	/**
	 * Get the field column size.
	 *
	 * @return int
	 */
	public function get_cols() {
		return apply_filters( 'easycommerce_field_get_cols', $this->cols, $this );
	}

	/**
	 * Get the field placeholder.
	 *
	 * @return string
	 */
	public function get_placeholder() {
		return apply_filters( 'easycommerce_field_get_placeholder', $this->placeholder, $this );
	}

	/**
	 * Get the field options.
	 *
	 * @return array
	 */
	public function get_options() {
		return apply_filters( 'easycommerce_field_get_options', $this->options, $this );
	}

	/**
	 * Get the field default value.
	 *
	 * @return mixed
	 */
	public function get_default() {
		return apply_filters( 'easycommerce_field_get_default', $this->default, $this );
	}

	/**
	 * Check if the field is disabled.
	 *
	 * @return bool
	 */
	public function is_disabled() {
		return apply_filters( 'easycommerce_field_is_disabled', $this->disabled, $this );
	}

	/**
	 * Check if the field is readonly.
	 *
	 * @return bool
	 */
	public function is_readonly() {
		return apply_filters( 'easycommerce_field_is_readonly', $this->readonly, $this );
	}

	/**
	 * Check if the field is required.
	 *
	 * @return bool
	 */
	public function is_required() {
		return apply_filters( 'easycommerce_field_is_required', $this->required, $this );
	}

	/**
	 * Get the field type.
	 *
	 * @return string
	 */
	public function get_type() {
		return apply_filters( 'easycommerce_field_get_type', $this->type, $this );
	}

	/**
	 * Get the field class.
	 *
	 * @return string
	 */
	public function get_class() {
		return apply_filters( 'easycommerce_field_get_class', $this->class, $this );
	}

	/**
	 * Set the field value.
	 *
	 * @param string $value
	 */
	public function set_value( $value = '' ) {
		/**
		 * Fires before the field value is set.
		 *
		 * @since 1.9
		 * @param string $value The field value.
		 * @param Field  $field The field instance.
		 */
		do_action( 'easycommerce_before_field_set_value', $value, $this );

		$this->value = apply_filters( 'easycommerce_field_set_value', $value, $this );

		/**
		 * Fires after the field value is set.
		 *
		 * @since 1.9
		 * @param string $value The field value.
		 * @param Field  $field The field instance.
		 */
		do_action( 'easycommerce_after_field_set_value', $value, $this );
	}

	/**
	 * Set the field description.
	 *
	 * @param string $description
	 */
	public function set_description( $description = '' ) {
		/**
		 * Fires before the field description is set.
		 *
		 * @since 1.9
		 * @param string $description The field description.
		 * @param Field  $field       The field instance.
		 */
		do_action( 'easycommerce_before_field_set_description', $description, $this );

		$this->description = apply_filters( 'easycommerce_field_set_description', $description, $this );

		/**
		 * Fires after the field description is set.
		 *
		 * @since 1.9
		 * @param string $description The field description.
		 * @param Field  $field       The field instance.
		 */
		do_action( 'easycommerce_after_field_set_description', $description, $this );
	}

	/**
	 * Set the field column size.
	 *
	 * @param int $cols
	 */
	public function set_cols( $cols = 1 ) {
		/**
		 * Fires before the field column size is set.
		 *
		 * @since 1.9
		 * @param int   $cols  The field column size.
		 * @param Field $field The field instance.
		 */
		do_action( 'easycommerce_before_field_set_cols', $cols, $this );

		$this->cols = apply_filters( 'easycommerce_field_set_cols', $cols, $this );

		/**
		 * Fires after the field column size is set.
		 *
		 * @since 1.9
		 * @param int   $cols  The field column size.
		 * @param Field $field The field instance.
		 */
		do_action( 'easycommerce_after_field_set_cols', $cols, $this );
	}

	/**
	 * Set the field placeholder.
	 *
	 * @param string $placeholder
	 */
	public function set_placeholder( $placeholder = '' ) {
		/**
		 * Fires before the field placeholder is set.
		 *
		 * @since 1.9
		 * @param string $placeholder The field placeholder.
		 * @param Field  $field       The field instance.
		 */
		do_action( 'easycommerce_before_field_set_placeholder', $placeholder, $this );

		$this->placeholder = apply_filters( 'easycommerce_field_set_placeholder', $placeholder, $this );

		/**
		 * Fires after the field placeholder is set.
		 *
		 * @since 1.9
		 * @param string $placeholder The field placeholder.
		 * @param Field  $field       The field instance.
		 */
		do_action( 'easycommerce_after_field_set_placeholder', $placeholder, $this );
	}

	/**
	 * Set the field options.
	 *
	 * @param array $options
	 */
	public function set_options( $options = array() ) {
		/**
		 * Fires before the field options are set.
		 *
		 * @since 1.9
		 * @param array $options The field options.
		 * @param Field $field   The field instance.
		 */
		do_action( 'easycommerce_before_field_set_options', $options, $this );

		$this->options = apply_filters( 'easycommerce_field_set_options', $options, $this );

		/**
		 * Fires after the field options are set.
		 *
		 * @since 1.9
		 * @param array $options The field options.
		 * @param Field $field   The field instance.
		 */
		do_action( 'easycommerce_after_field_set_options', $options, $this );
	}

	/**
	 * Set the field default value.
	 *
	 * @param mixed $default
	 */
	public function set_default( $default = '' ) {
		/**
		 * Fires before the field default value is set.
		 *
		 * @since 1.9
		 * @param mixed $default The field default value.
		 * @param Field $field   The field instance.
		 */
		do_action( 'easycommerce_before_field_set_default', $default, $this );

		$this->default = apply_filters( 'easycommerce_field_set_default', $default, $this );

		/**
		 * Fires after the field default value is set.
		 *
		 * @since 1.9
		 * @param mixed $default The field default value.
		 * @param Field $field   The field instance.
		 */
		do_action( 'easycommerce_after_field_set_default', $default, $this );
	}

	/**
	 * Set the field disabled state.
	 *
	 * @param bool $disabled
	 */
	public function set_disabled( $disabled = false ) {
		/**
		 * Fires before the field disabled state is set.
		 *
		 * @since 1.9
		 * @param bool  $disabled Whether the field is disabled.
		 * @param Field $field    The field instance.
		 */
		do_action( 'easycommerce_before_field_set_disabled', $disabled, $this );

		$this->disabled = apply_filters( 'easycommerce_field_set_disabled', $disabled, $this );

		/**
		 * Fires after the field disabled state is set.
		 *
		 * @since 1.9
		 * @param bool  $disabled Whether the field is disabled.
		 * @param Field $field    The field instance.
		 */
		do_action( 'easycommerce_after_field_set_disabled', $disabled, $this );
	}

	/**
	 * Set the field readonly state.
	 *
	 * @param bool $readonly
	 */
	public function set_readonly( $readonly = false ) {
		/**
		 * Fires before the field readonly state is set.
		 *
		 * @since 1.9
		 * @param bool  $readonly Whether the field is readonly.
		 * @param Field $field    The field instance.
		 */
		do_action( 'easycommerce_before_field_set_readonly', $readonly, $this );

		$this->readonly = apply_filters( 'easycommerce_field_set_readonly', $readonly, $this );

		/**
		 * Fires after the field readonly state is set.
		 *
		 * @since 1.9
		 * @param bool  $readonly Whether the field is readonly.
		 * @param Field $field    The field instance.
		 */
		do_action( 'easycommerce_after_field_set_readonly', $readonly, $this );
	}

	/**
	 * Set the field type.
	 *
	 * @param string $type
	 */
	public function set_type( $type = 'text' ) {
		/**
		 * Fires before the field type is set.
		 *
		 * @since 1.9
		 * @param string $type  The field type.
		 * @param Field  $field The field instance.
		 */
		do_action( 'easycommerce_before_field_set_type', $type, $this );

		$this->type = apply_filters( 'easycommerce_field_set_type', $type, $this );

		/**
		 * Fires after the field type is set.
		 *
		 * @since 1.9
		 * @param string $type  The field type.
		 * @param Field  $field The field instance.
		 */
		do_action( 'easycommerce_after_field_set_type', $type, $this );
	}

	/**
	 * Set the field class.
	 *
	 * @param string $class
	 */
	public function set_class( $class = '' ) {
		/**
		 * Fires before the field class is set.
		 *
		 * @since 1.9
		 * @param string $class The field class.
		 * @param Field  $field The field instance.
		 */
		do_action( 'easycommerce_before_field_set_class', $class, $this );

		$this->class = apply_filters( 'easycommerce_field_set_class', $class, $this );

		/**
		 * Fires after the field class is set.
		 *
		 * @since 1.9
		 * @param string $class The field class.
		 * @param Field  $field The field instance.
		 */
		do_action( 'easycommerce_after_field_set_class', $class, $this );
	}

	/**
	 * Generate the CSS ID for the field.
	 *
	 * @return string
	 */
	public function get_field_id() {
		return apply_filters( 'easycommerce_field_get_field_id', 'easycommerce-field-' . $this->id, $this );
	}

	/**
	 * Generate the CSS class for the field.
	 *
	 * @return string
	 */
	public function get_field_class() {
		return apply_filters( 'easycommerce_field_get_field_class', 'easycommerce-field easycommerce-field-' . $this->type, $this );
	}

	/**
	 * Set attributes.
	 *
	 * @param array $atts
	 */
	public function set_atts( $atts = array() ) {
		/**
		 * Fires before the field attributes are set.
		 *
		 * @since 1.9
		 * @param array $atts  The field attributes.
		 * @param Field $field The field instance.
		 */
		do_action( 'easycommerce_before_field_set_atts', $atts, $this );

		$this->atts = apply_filters( 'easycommerce_field_set_atts', $atts, $this );

		/**
		 * Fires after the field attributes are set.
		 *
		 * @since 1.9
		 * @param array $atts  The field attributes.
		 * @param Field $field The field instance.
		 */
		do_action( 'easycommerce_after_field_set_atts', $atts, $this );
	}

	/**
	 * Get additional attributes.
	 *
	 * @return array
	 */
	public function get_atts() {
		return apply_filters( 'easycommerce_field_get_atts', $this->atts, $this );
	}

	/**
	 * Render additional attributes as a string.
	 *
	 * @return string
	 */
	protected function render_atts() {
		$atts_str = '';
		foreach ( $this->atts as $key => $value ) {
			$atts_str .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return apply_filters( 'easycommerce_field_render_atts', $atts_str, $this );
	}

	/**
	 * Set args.
	 *
	 * @param array $args
	 */
	public function set_args( $args = array() ) {
		/**
		 * Fires before the field args are set.
		 *
		 * @since 1.9
		 * @param array $args  The field args.
		 * @param Field $field The field instance.
		 */
		do_action( 'easycommerce_before_field_set_args', $args, $this );

		$this->args = apply_filters( 'easycommerce_field_set_args', $args, $this );

		/**
		 * Fires after the field args are set.
		 *
		 * @since 1.9
		 * @param array $args  The field args.
		 * @param Field $field The field instance.
		 */
		do_action( 'easycommerce_after_field_set_args', $args, $this );
	}

	/**
	 * Get args.
	 *
	 * @return array
	 */
	public function get_args() {
		return apply_filters( 'easycommerce_field_get_args', $this->args, $this );
	}

	/**
	 * Get a single arg.
	 *
	 * @return mix
	 */
	public function get_arg( $key, $default = '' ) {
		$args = $this->get_args();

		return array_key_exists( $key, $args ) ? $args[ $key ] : $default;
	}

	/**
	 * Generate the CSS ID for the field wrapper.
	 *
	 * @return string
	 */
	public function get_wrapper_id() {
		return apply_filters( 'easycommerce_field_get_wrapper_id', 'easycommerce-field-wrapper-' . $this->id, $this );
	}

	/**
	 * Generate the CSS class for the field wrapper.
	 *
	 * @return string
	 */
	public function get_wrapper_class() {
		return apply_filters( 'easycommerce_field_get_wrapper_class', 'easycommerce-field-wrapper easycommerce-field-wrapper-' . $this->type . ' ' . $this->class, $this );
	}

	/**
	 * Render the field.
	 *
	 * @return string
	 */
	abstract public function render();
}

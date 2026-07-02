<?php
namespace EasyCommerce\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Payment Method Class
 */
abstract class Payment_Method {

	/**
	 * Payment method ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Payment method title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Payment method description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Constructor
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $description
	 */
	public function __construct( $id, $title, $description = '' ) {
		$this->id          = $id;
		$this->title       = $title;
		$this->description = $description;

		/**
		 * Fires after a payment method is constructed.
		 *
		 * @since 1.9
		 * @param string $id          The payment method ID.
		 * @param string $title       The payment method title.
		 * @param string $description The payment method description.
		 * @param Payment_Method $payment_method The payment method instance.
		 */
		do_action( 'easycommerce_payment_method_constructed', $this->id, $this->title, $this->description, $this );

		// Hook to register the payment method during WordPress initialization
		add_action( 'init', array( $this, 'register_payment_method' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'easycommerce_order_status', array( $this, 'process_payment' ), 10, 4 );
		add_action( "easycommerce-{$this->get_id()}_payment_complete", array( $this, 'insert_transaction' ), 10, 5 );
		add_action( "easycommerce_after_refund_order_{$this->get_id()}", array( $this, 'refund' ), 10, 3 );
		add_filter( "easycommerce_unset_payment_method_{$this->get_id()}", array( $this, 'validate_availability' ), 10, 2 );
		add_filter( 'easycommerce-localized_vars', array( $this, 'localized' ) );
		add_filter( 'easycommerce_supports_recurring', array( $this, 'supports_recurring' ), 10, 3 );
	}

	/**
	 * Register payment method in the checkout form
	 */
	public function register_payment_method() {
		/**
		 * Fires before registering a payment method.
		 *
		 * @since 1.9
		 * @param string $id The payment method ID.
		 */
		do_action( 'easycommerce_before_register_payment_method', $this->id );

		/**
		 * Filter to modify payment methods list
		 *
		 * @since 1.9
		 *
		 * @param array $methods List of registered payment methods.
		 */
		add_filter( 'easycommerce_payment_methods', array( $this, 'add_payment_method' ) );
	}

	/**
	 * Add payment method to the available methods
	 *
	 * @param array $methods Existing payment methods.
	 * @return array Updated list of payment methods.
	 */
	public function add_payment_method( $methods ) {
		$method_data = array(
			'id'          	 => $this->get_id(),
			'title'       	 => $this->get_title(),
			'description' 	 => $this->get_description(),
			'status'      	 => $this->get_status(),
			'settings'    	 => $this->settings(),
			'form'        	 => $this->payment_form(),
			'icon'        	 => $this->get_icon(),
			'class'       	 => get_class( $this ),
			'support_refund' => $this->supports_refund(),
		);

		$methods[ $this->id ] = $method_data;

		/**
		 * Filters the payment method data before adding to the list.
		 *
		 * @since 1.9
		 * @param array  $method_data The payment method data.
		 * @param string $id          The payment method ID.
		 */
		$methods[ $this->id ] = apply_filters( 'easycommerce_add_payment_method', $method_data, $this->id );

		return $methods;
	}

	/**
	 * Enqueue payment method scripts
	 */
	public function enqueue_scripts() {}

	/**
	 * Localize payment method variables
	 *
	 * @param array $vars Existing localized variables.
	 * @return array Updated list of localized variables.
	 */
	public function localized( $vars ) {
		return $vars;
	}

	/**
	 * Display and save payment method settings
	 *
	 * @return array Payment method settings.
	 */
	public function settings() {
		return array();
	}

	/**
	 * Process payment
	 *
	 * @param string $status Payment status.
	 * @param int $order_id Order ID.
	 * @param array $params Payment parameters.
	 * @param int $customer_id Customer ID.
	 */
	public function process_payment( $status, $order_id, $params, $customer_id ) {
		/**
		 * Fires when processing a payment.
		 *
		 * @since 1.9
		 * @param string $status      Payment status.
		 * @param int    $order_id    Order ID.
		 * @param array  $params      Payment parameters.
		 * @param int    $customer_id Customer ID.
		 * @param string $method_id   Payment method ID.
		 */
		do_action( 'easycommerce_process_payment', $status, $order_id, $params, $customer_id, $this->id );

		return $status;
	}

	/**
	 * Process transaction
	 *
	 * @param mixed $transaction_id Transaction ID.
	 * @param int $order_id Order ID.
	 * @param float $total_amount Total amount.
	 * @param int $customer_id Customer ID.
	 * @param array $params Payment parameters.
	 */
	public function insert_transaction( $transaction_id, $order_id, $total_amount, $customer_id, $params ) {
		/**
		 * Fires before inserting a transaction.
		 *
		 * @since 1.9
		 * @param mixed  $transaction_id Transaction ID.
		 * @param int    $order_id       Order ID.
		 * @param float  $total_amount   Total amount.
		 * @param int    $customer_id    Customer ID.
		 * @param array  $params         Payment parameters.
		 * @param string $method_id      Payment method ID.
		 */
		do_action( 'easycommerce_before_insert_transaction', $transaction_id, $order_id, $total_amount, $customer_id, $params, $this->id );

		// Insert transaction logic here.

		/**
		 * Fires after inserting a transaction.
		 *
		 * @since 1.9
		 * @param mixed  $transaction_id Transaction ID.
		 * @param int    $order_id       Order ID.
		 * @param float  $total_amount   Total amount.
		 * @param int    $customer_id    Customer ID.
		 * @param array  $params         Payment parameters.
		 * @param string $method_id      Payment method ID.
		 */
		do_action( 'easycommerce_after_insert_transaction', $transaction_id, $order_id, $total_amount, $customer_id, $params, $this->id );
	}

	/**
	 * Display payment form
	 *
	 * @return string Payment form HTML.
	 */
	public function payment_form() {
		return '';
	}

	/**
	 * Get payment method status
	 *
	 * @return bool Payment method status.
	 */
	public function get_status() {
		$status = get_option( 'easycommerce_payment_method_' . $this->id . '_status', false ) == 1;

		/**
		 * Filter the status of a specific payment method.
		 *
		 * @since 1.9
		 *
		 * @param bool $status Payment method enabled status.
		 */
		return apply_filters( 'easycommerce_payment_method_' . $this->id . '_status', $status );
	}

	/**
	 * Get payment method ID
	 *
	 * @return string Payment method ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get payment method title
	 *
	 * @return string Payment method title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get payment method name
	 *
	 * @return string Payment method name.
	 */
	public function get_method_name() {
		$option      = get_option( "easycommerce-payment-{$this->id}" ) ?? array();
		$method_name = ! empty( $option['payment_method_name'] ) ? $option['payment_method_name'] : $this->title;
		return $method_name;
	}

	/**
	 * Get payment method description
	 *
	 * @return string Payment method description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get payment method icon
	 *
	 * @return string Payment method icon HTML or URL.
	 */
	public function get_icon() {
		/**
		 * Filter the payment method icon.
		 *
		 * @since 1.9
		 *
		 * @param string $icon Payment method icon HTML or URL.
		 */
		return apply_filters( 'easycommerce_payment_method_' . $this->id . '_icon', '' );
	}

	public function is_enabled() {
		return in_array( $this->id, easycommerce_active_payment_methods(), true );
	}

	public function is_available() {
		return true;
	}

	public function is_offline(): bool {
		return false;
	}

	public function refund( $order_id, $reason, $amount ) {
		/**
		 * Fires before processing a refund.
		 *
		 * @since 1.9
		 * @param int    $order_id Order ID.
		 * @param string $reason   Refund reason.
		 * @param float  $amount   Refund amount.
		 * @param string $method_id Payment method ID.
		 */
		do_action( 'easycommerce_before_refund', $order_id, $reason, $amount, $this->id );

		// Refund logic here.

		/**
		 * Fires after processing a refund.
		 *
		 * @since 1.9
		 * @param int    $order_id Order ID.
		 * @param string $reason   Refund reason.
		 * @param float  $amount   Refund amount.
		 * @param string $method_id Payment method ID.
		 */
		do_action( 'easycommerce_after_refund', $order_id, $reason, $amount, $this->id );
	}

	public function supports_refund() {
		/**
		 * Filters whether the payment method supports refunds.
		 *
		 * @since 1.9
		 * @param string $method_id Payment method ID.
		 */
		return apply_filters( 'easycommerce_payment_method_supports_refund', false, $this->id );
	}

	/**
	 * Check payment method availability
	 *
	 * @param bool $unset Unset flag.
	 * @param bool $has_physical Flag for physical product.
	 *
	 * @return bool Updated unset flag.
	 */
	public function validate_availability( $unset, $has_physical ) {
		$availability = ! $this->is_available() || $unset;

		/**
		 * Filters the payment method availability.
		 *
		 * @since 1.9
		 * @param bool   $availability Whether the payment method is available.
		 * @param bool   $has_physical  Flag for physical product.
		 * @param string $method_id     Payment method ID.
		 */
		return apply_filters( 'easycommerce_validate_payment_method_availability', $availability, $has_physical, $this->id );
	}

	/**
	 * Supports recurring payments
	 *
	 * @param bool $supports Supports recurring payments.
	 * @return bool Updated supports flag.
	 *
	 */
	public function supports_recurring( $supports, $payment_id, $cart ) {
		/**
		 * Filters whether the payment method supports recurring payments.
		 *
		 * @since 1.9
		 * @param bool   $supports   Whether the payment method supports recurring payments.
		 * @param string $payment_id Payment method ID.
		 * @param mixed  $cart       Cart data.
		 * @param string $method_id  Payment method ID.
		 */
		return apply_filters( 'easycommerce_payment_method_supports_recurring', $supports, $payment_id, $cart, $this->id );
	}
}

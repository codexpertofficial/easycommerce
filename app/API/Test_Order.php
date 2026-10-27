<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Cart as Cart_Model;
use EasyCommerce\Models\Order as Order_Model;
use EasyCommerce\Models\Product;

/**
 * Test order health check.
 *
 * The admin popup drives a real guest checkout through the public cart and
 * order endpoints. This class supplies what those endpoints cannot: the
 * config checks, a run token, and the cleanup that removes every trace of the
 * order the run placed (stock, sales counter, customer, cart, logs).
 */
class Test_Order extends API {

	const TOKEN_PARAM    = 'easycommerce-test-token';
	const PAYMENT_METHOD = 'easycommerce-test';
	const ORDER_FLAG     = '_ec_test_order';
	const RESULT_OPTION  = 'easycommerce_test_order_last_run';
	const TOKEN_TTL      = 1800;

	/**
	 * Hook the order endpoint so a run carrying a valid token is tagged and silent.
	 */
	public function __construct() {
		add_filter( 'rest_request_before_callbacks', array( $this, 'silence_mail' ), 10, 3 );
		add_action( 'easycommerce_after_create_order', array( $this, 'tag_order' ), 5, 3 );
	}

	/**
	 * Whether the token was issued by start() and has not expired.
	 *
	 * @param mixed $token The token from the request.
	 * @return bool
	 */
	private function is_valid_token( $token ) {
		return is_string( $token )
			&& preg_match( '/^[A-Za-z0-9]{24}$/', $token )
			&& false !== get_transient( 'easycommerce_test_run_' . $token );
	}

	/**
	 * The throwaway customer email for a run.
	 *
	 * @param string $token The run token.
	 * @return string
	 */
	private function test_email( $token ) {
		return 'ec-test-' . strtolower( substr( $token, 0, 8 ) ) . '@example.com';
	}

	/**
	 * Block every outgoing mail for the order request of a test run.
	 *
	 * Runs before the order callback, so the new-account and order emails the
	 * request would send to the throwaway customer are both stopped.
	 *
	 * @param mixed            $response Current response.
	 * @param array            $handler  The matched route handler.
	 * @param \WP_REST_Request $request  The request.
	 * @return mixed
	 */
	public function silence_mail( $response, $handler, $request ) {
		if ( $request instanceof \WP_REST_Request
			&& 'POST' === $request->get_method()
			&& '/easycommerce/v1/orders' === $request->get_route()
			&& $this->is_valid_token( $request->get_param( self::TOKEN_PARAM ) )
		) {
			add_filter( 'pre_wp_mail', '__return_false' );
		}

		return $response;
	}

	/**
	 * Flag the order a test run just placed, and remember what to undo.
	 *
	 * @param int   $order_id    The order ID.
	 * @param array $params      The request params.
	 * @param int   $customer_id The customer ID.
	 */
	public function tag_order( $order_id, $params, $customer_id ) {
		$token = $params[ self::TOKEN_PARAM ] ?? '';

		if ( ! $this->is_valid_token( $token ) ) {
			return;
		}

		$order = new Order_Model( $order_id );
		$order->update_meta( self::ORDER_FLAG, $token );

		if ( is_string( $params['items'] ?? null ) ) {
			$order->update_meta( '_ec_test_cart_hash', sanitize_text_field( $params['items'] ) );
		}

		$user = get_userdata( (int) $customer_id );

		if ( $user && $user->user_email === $this->test_email( $token ) ) {
			$order->update_meta( '_ec_test_customer', (int) $customer_id );
		}
	}

	/**
	 * Start a run: clear leftovers of earlier runs and issue a token.
	 */
	public function start( $request ) {
		$this->cleanup( $this->find_test_orders() );

		$token = wp_generate_password( 24, false );

		set_transient( 'easycommerce_test_run_' . $token, get_current_user_id(), self::TOKEN_TTL );

		$this->response_success(
			array(
				'token'          => $token,
				'email'          => $this->test_email( $token ),
				'payment_method' => self::PAYMENT_METHOD,
			)
		);
	}

	/**
	 * Run one server-side check.
	 */
	public function check( $request ) {
		$step = $request->get_param( 'step' );

		switch ( $step ) {
			case 'store':
				$result = $this->check_store();
				break;
			case 'product':
				$result = $this->check_product();
				break;
			default:
				$result = $this->check_gateways();
		}

		$this->response_success( $result );
	}

	/**
	 * The shop and checkout pages, and the store mode.
	 *
	 * @return array
	 */
	private function check_store() {
		$details      = array();
		$failed       = false;
		$pages_failed = false;

		$shop_id = (int) easycommerce_shop_page();

		if ( $shop_id && 'publish' === get_post_status( $shop_id ) ) {
			$details[] = array( 'status' => 'passed', 'message' => __( 'Shop page is published.', 'easycommerce' ) );
		} else {
			$failed       = true;
			$pages_failed = true;
			$details[]    = array( 'status' => 'failed', 'message' => __( 'The shop page is missing or not published.', 'easycommerce' ) );
		}

		$checkout_id   = (int) easycommerce_checkout_page();
		$checkout_post = $checkout_id ? get_post( $checkout_id ) : null;

		if ( $checkout_post && 'publish' === $checkout_post->post_status
			&& ( has_shortcode( $checkout_post->post_content, 'easycommerce-checkout' ) || has_block( 'easycommerce/checkout', $checkout_post ) ) ) {
			$details[] = array( 'status' => 'passed', 'message' => __( 'Checkout page is published and contains the checkout.', 'easycommerce' ) );
		} else {
			$failed       = true;
			$pages_failed = true;
			$details[]    = array( 'status' => 'failed', 'message' => __( 'The checkout page is missing, unpublished, or does not contain the checkout.', 'easycommerce' ) );
		}

		$is_test = 'live' !== ( Utility::get_option( 'general', 'visibility', 'store_mode' ) ?: 'test' );

		if ( $is_test ) {
			$failed    = true;
			$details[] = array( 'status' => 'failed', 'message' => __( 'The store is in Test mode: customers cannot see the shop or checkout, so a real order cannot be placed.', 'easycommerce' ) );
		} else {
			$details[] = array( 'status' => 'passed', 'message' => __( 'Store is Live.', 'easycommerce' ) );
		}

		// A missing page is fixed under General; Test mode is fixed under General > Visibility.
		if ( $pages_failed ) {
			$link = admin_url( 'admin.php?page=easycommerce-settings&menu=general' );
		} elseif ( $is_test ) {
			$link = admin_url( 'admin.php?page=easycommerce-settings&menu=general&submenu=visibility' );
		} else {
			$link = '';
		}

		return array(
			'status'  => $failed ? 'failed' : 'passed',
			'message' => $failed ? __( 'The store is not ready to accept real orders.', 'easycommerce' ) : __( 'Store pages are ready and the store is Live.', 'easycommerce' ),
			'details' => $details,
			'link'    => $link,
		);
	}

	/**
	 * Find the cheapest published, in-stock product to buy.
	 *
	 * @return array
	 */
	private function check_product() {
		$ids = get_posts(
			array(
				'post_type'        => apply_filters( 'easycommerce_product_post_type', 'product' ),
				'post_status'      => 'publish',
				'posts_per_page'   => 50,
				'fields'           => 'ids',
				'suppress_filters' => false,
			)
		);

		$best = null;
		$free = null;

		foreach ( $ids as $id ) {
			$product = new Product( $id );

			foreach ( $product->get_variations() as $variation ) {
				if ( 'in_stock' !== $variation->get_status() ) {
					continue;
				}

				if ( $variation->manages_stock() && (int) $variation->get_stock() < 1 ) {
					continue;
				}

				$price     = (float) $variation->get_price();
				$candidate = array(
					'product_id' => (int) $id,
					'price_id'   => (int) $variation->get_price_id(),
					'price'      => $price,
					'title'      => $product->get_title(),
				);

				// A paid product exercises the real payment path, so it wins over a free one.
				if ( $price > 0 ) {
					if ( null === $best || $price < $best['price'] ) {
						$best = $candidate;
					}
				} elseif ( null === $free ) {
					$free = $candidate;
				}
			}
		}

		$best = $best ?: $free;

		if ( ! $best ) {
			return array(
				'status'  => 'failed',
				'message' => __( 'No published, in-stock product could be found to buy.', 'easycommerce' ),
				'details' => array(),
				'link'    => admin_url( 'admin.php?page=easycommerce#/products' ),
			);
		}

		return array(
			'status'  => 'passed',
			/* translators: %s: product title. */
			'message' => sprintf( __( 'Using "%s" for the test.', 'easycommerce' ), $best['title'] ),
			'details' => array(),
			'link'    => '',
			'product' => $best,
		);
	}

	/**
	 * Whether payment gateways are switched on and configured.
	 *
	 * This checks configuration only. A real gateway is never charged, because
	 * a card payment needs the customer's browser.
	 *
	 * @return array
	 */
	private function check_gateways() {
		$active  = array_values(
			array_filter(
				easycommerce_active_payment_methods(),
				function ( $id ) {
					return is_string( $id ) && '' !== $id && '0' !== $id;
				}
			)
		);
		$methods = easycommerce_payment_methods();
		$link    = admin_url( 'admin.php?page=easycommerce-settings&menu=payment' );

		if ( empty( $active ) ) {
			return array(
				'status'  => 'failed',
				'message' => __( 'No payment method is active, so customers cannot pay.', 'easycommerce' ),
				'details' => array(),
				'link'    => $link,
			);
		}

		$details  = array();
		$usable   = 0;

		foreach ( $active as $id ) {
			$title = $methods[ $id ]['title'] ?? $id;
			$class = easycommerce_payment_method_class( $id );

			if ( $class && $class->is_available() ) {
				++$usable;
				/* translators: %s: payment method title. */
				$details[] = array( 'status' => 'passed', 'message' => sprintf( __( '%s is active and configured.', 'easycommerce' ), $title ) );
			} else {
				/* translators: %s: payment method title. */
				$details[] = array( 'status' => 'failed', 'message' => sprintf( __( '%s is active but not fully configured.', 'easycommerce' ), $title ) );
			}
		}

		if ( ! $usable ) {
			return array(
				'status'  => 'failed',
				'message' => __( 'Active payment methods are missing their settings.', 'easycommerce' ),
				'details' => $details,
				'link'    => $link,
			);
		}

		return array(
			'status'  => $usable === count( $active ) ? 'passed' : 'warning',
			/* translators: %d: number of usable payment methods. */
			'message' => sprintf( _n( '%d payment method is ready. Gateways are checked, not charged.', '%d payment methods are ready. Gateways are checked, not charged.', $usable, 'easycommerce' ), $usable ),
			'details' => $details,
			'link'    => $usable === count( $active ) ? '' : $link,
		);
	}

	/**
	 * Verify the order a run placed, undo it, and remember the outcome.
	 */
	public function finish( $request ) {
		$token = $request->get_param( 'token' );

		if ( ! $this->is_valid_token( $token ) ) {
			$this->response_error( __( 'This test run has expired. Run it again.', 'easycommerce' ), 400 );
		}

		$order_ids = $this->find_test_orders( $token );
		$verified  = false;

		foreach ( $order_ids as $order_id ) {
			$order = new Order_Model( $order_id );

			if ( $order->exists() && ! empty( $order->get_items() ) ) {
				$verified = true;
			}
		}

		$cleaned = $this->cleanup( $order_ids );

		// A run that failed before the order leaves its guest cart behind.
		$cart_hash = $request->get_param( 'cart_hash' );

		if ( is_string( $cart_hash ) && preg_match( '/^[A-Za-z0-9]{16,64}$/', $cart_hash ) ) {
			$this->delete_cart( $cart_hash );
		}

		delete_transient( 'easycommerce_test_run_' . $token );

		$results = array();

		foreach ( (array) $request->get_param( 'results' ) as $row ) {
			$status = $row['status'] ?? '';

			$results[] = array(
				'id'      => sanitize_key( $row['id'] ?? '' ),
				'status'  => in_array( $status, array( 'passed', 'warning', 'failed', 'skipped' ), true ) ? $status : 'failed',
				'message' => mb_substr( sanitize_text_field( $row['message'] ?? '' ), 0, 300 ),
			);
		}

		$run = array(
			'time'    => time(),
			'passed'  => $verified && ! in_array( 'failed', wp_list_pluck( $results, 'status' ), true ),
			'results' => $results,
		);

		update_option( self::RESULT_OPTION, $run, false );

		$this->response_success(
			array(
				'verified' => $verified,
				'cleaned'  => $cleaned,
				'run'      => $run,
			)
		);
	}

	/**
	 * The result of the last run.
	 */
	public function last( $request ) {
		$this->response_success( array( 'run' => get_option( self::RESULT_OPTION, null ) ) );
	}

	/**
	 * IDs of orders flagged as test orders.
	 *
	 * @param string $token Limit to one run; empty for every leftover.
	 * @return int[]
	 */
	private function find_test_orders( $token = '' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'ec_order_meta';

		if ( '' !== $token ) {
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT order_id FROM {$table} WHERE meta_key = %s AND meta_value = %s", self::ORDER_FLAG, $token ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} else {
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT order_id FROM {$table} WHERE meta_key = %s", self::ORDER_FLAG ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		return array_map( 'intval', $ids );
	}

	/**
	 * Undo test orders: stock, sales counter, order, cart, logs and customer.
	 *
	 * Only orders carrying the test flag are touched.
	 *
	 * @param int[] $order_ids Test order IDs.
	 * @return int Number of orders removed.
	 */
	private function cleanup( $order_ids ) {
		global $wpdb;

		$removed = 0;

		foreach ( $order_ids as $order_id ) {
			$order = new Order_Model( $order_id );

			if ( ! $order->exists() || ! $order->get_meta( self::ORDER_FLAG ) ) {
				continue;
			}

			$order->restore_stock();

			foreach ( $order->get_items() as $item ) {
				if ( empty( $item->product_id ) ) {
					continue;
				}

				$sold = intval( get_post_meta( $item->product_id, 'total_sale', true ) );
				update_post_meta( $item->product_id, 'total_sale', max( 0, $sold - (int) $item->quantity ) );
			}

			$cart_hash = $order->get_meta( '_ec_test_cart_hash' );
			$user_id   = (int) $order->get_meta( '_ec_test_customer' );

			$order->delete();

			$wpdb->delete( $wpdb->prefix . 'ec_logs', array( 'object' => 'order', 'object_id' => $order_id ) );

			if ( $cart_hash ) {
				$this->delete_cart( $cart_hash );
			}

			if ( $user_id ) {
				$this->delete_test_customer( $user_id );
			}

			++$removed;
		}

		if ( $removed ) {
			do_action( 'easycommerce_clear_cache', 'all' );
		}

		return $removed;
	}

	/**
	 * Delete a test cart and the log row its add-to-cart left behind.
	 *
	 * @param string $hash The cart hash.
	 */
	private function delete_cart( $hash ) {
		global $wpdb;

		( new Cart_Model( $hash ) )->delete();

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ec_logs WHERE object = 'cart' AND meta LIKE %s", '%' . $wpdb->esc_like( $hash ) . '%' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Delete the throwaway customer, never a real account.
	 *
	 * @param int $user_id The user ID.
	 */
	private function delete_test_customer( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! $user || ! preg_match( '/^ec-test-[a-z0-9]{8}@example\.com$/', $user->user_email ) || user_can( $user, 'manage_options' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/user.php';

		wp_delete_user( $user_id );
	}
}

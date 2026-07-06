<?php

namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Order_Meta;
use EasyCommerce\Models\Order_Item;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Refund;
use EasyCommerce\Models\Log;
use DateTime;
use DateInterval;
use DatePeriod;
/**
 * Dashboard API
 */
class Dashboard extends API {

	/**
	 * The dashboard stats
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_stats( $request ) {

		$range = $request->get_param( 'range' );
		$from  = $request->get_param( 'from' );
		$to    = $request->get_param( 'to' );

		if ( ! $range && $from && $to ) {
			$range = 'custom';
		}

		$completed_sales     	   = $this->total_sales( $range, 'completed' );
		$processing_sales    	   = $this->total_sales( $range, 'processing' );
		$partially_refunded_sales  = $this->total_sales( $range, 'partially_refunded' );
		$fully_refunded_sales      = $this->total_sales( $range, 'refunded' );
		$completed_orders    	   = $this->total_orders( $range, 'completed' );
		$processing_orders   	   = $this->total_orders( $range, 'processing' );
		$partially_refunded_orders = $this->total_orders( $range, 'partially_refunded' );
		$fully_refunded_orders     = $this->total_orders( $range, 'refunded' );
		$refunds  				   = $this->total_refunded_amount( $range );
		$sales               	   = $completed_sales + $processing_sales + $partially_refunded_sales + $fully_refunded_sales;
		$orders   				   = array_merge( $completed_orders, $processing_orders, $partially_refunded_orders, $fully_refunded_orders );
		$customers           	   = $this->get_customers( $range );
		$products            	   = $this->total_products_sold( $range );
		$abandoned           	   = $this->get_abandoned_carts( $range, $from, $to );
		$total_products 	 	   = $this->get_total_products();
		$revenue 			 	   = $sales - $refunds;

		$stats = array(
			'orders'    		=> count( $orders ),
			'sales'     		=> easycommerce_price( $sales ),
			'refunds'   		=> easycommerce_price( $refunds ),
			'revenue'   		=> easycommerce_price( $revenue ),
			'product'   		=> $products,
			'customers' 		=> count( array_unique( $customers ) ),
			'total_products' 	=> $total_products,
			'abandoned' 		=> $abandoned,			
		);

		/**
		 * Filters the dashboard stats before sending the response.
		 *
		 * @since 1.9
		 * @param array $stats The stats.
		 * @param string $range The range.
		 * @param string $from The from date.
		 * @param string $to The to date.
		 * @param WP_REST_Request $request The request object.
		 */
		$stats = apply_filters( 'easycommerce_get_dashboard_stats', $stats, $range, $from, $to, $request );

		$this->response_success(
			array(
				'message' => __( 'Store stats', 'easycommerce' ),
				'stats'   => $stats,
			)
		);
	}

	/**
	 * Orders by statuses
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_order_statuses( $request ) {
		$range = $request->get_param( 'range' );

		if ( ! $range ) {
			$from  = $request->get_param( 'from' );
			$to    = $request->get_param( 'to' );
			$range = implode( ',', array( $from, $to ) );
		}

		$statuses = array( 'pending', 'completed', 'failed', 'refunded', 'processing', 'on_hold', 'cancelled' );
		$orders   = array();

		foreach ( $statuses as $status ) {
			$orders[ $status ] = $this->total_orders_by_status( $range, $status );
		}

		/**
		 * Filters the order statuses before sending the response.
		 *
		 * @since 1.9
		 * @param array $orders The orders by status.
		 * @param string $range The range.
		 * @param WP_REST_Request $request The request object.
		 */
		$orders = apply_filters( 'easycommerce_get_order_statuses', $orders, $range, $request );

		$this->response_success(
			array(
				'message' => __( 'Store order statuses', 'easycommerce' ),
				'orders'  => $orders,
			)
		);
	}

	public function get_setup_status( $request ) {
		$steps_config = [
			'business' => [
				'label' => __( 'Business Info', 'easycommerce' ),
				'option' => 'easycommerce-general-business',
				'url'	=> admin_url( 'admin.php?page=easycommerce-settings&menu=general&submenu=business' ),
			],
			'store' => [
				'label' => __( 'Store Pages', 'easycommerce' ),
				'option' => 'easycommerce-general-store',
				'url'	=> admin_url( 'admin.php?page=easycommerce-settings&menu=general&submenu=store' ),
			],
			'currency' => [
				'label' => __( 'Currency & Format', 'easycommerce' ),
				'option' => 'easycommerce-payment-pricing',
				'url'	=> admin_url( 'admin.php?page=easycommerce-settings&menu=payment&submenu=pricing' ),
			],
			'payment' => [
				'label' => __( 'Payment Method', 'easycommerce' ),
				'option' => 'easycommerce-payment-methods',
				'url'	=> admin_url( 'admin.php?page=easycommerce-settings&menu=payment&submenu=methods' ),
			],
			// 'email' => [
			// 	'label' => __( 'Email Templates', 'easycommerce' ),
			// 	'option' => 'easycommerce-email-layout',
			// 	'url'	=> admin_url( 'admin.php?page=easycommerce-settings&menu=email' ),
			// ],
			// 'cart-recovery' => [
			// 	'label' => __( 'Cart Recovery', 'easycommerce' ),
			// 	'option' => 'easycommerce-abandoned-cart-settings',
			// 	'url'	=> admin_url( 'admin.php?page=easycommerce-settings&menu=abandoned-cart' ),
			// ],
		];
	
		$setup_steps = [];
		foreach ( $steps_config as $tab => $config ) {
			$setup_steps[] = [
				'label'  => $config['label'],
				'status' => is_array( $data = get_option( $config['option'], true ) ) && ! in_array( '', $data, true ),
				'link'   => $config['url'],
			];
		}
	
		/**
		 * Filters the setup steps before sending the response.
		 *
		 * @since 1.9
		 * @param array $setup_steps The setup steps.
		 * @param WP_REST_Request $request The request object.
		 */
		$setup_steps = apply_filters( 'easycommerce_get_setup_status', $setup_steps, $request );

		$this->response_success( [
			'message'    => __( 'Store setup status', 'easycommerce' ),
			'setupSteps' => $setup_steps,
		] );
	}
	
	/**
	 * Sales
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_sales( $request ) {
		$range = $request->get_param( 'range' );
		if ( ! $range ) {
			$from  = $request->get_param( 'from' );
			$to    = $request->get_param( 'to' );
			$range = implode( ',', array( $from, $to ) );
		}
		if ( in_array( $range, array( 'last-week' ) ) ) {
			$start_of_week     = get_option( 'start_of_week' );
			$current_day       = gmdate( 'w' );
			$days_to_subtract  = ( $current_day - $start_of_week + 14 ) % 7 + 7;
			$from_date         = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days" ) );
			$x_values          = array();
			$day_names         = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
			for ( $i = 0; $i < 7; $i++ ) {
				$day_index  = ( $start_of_week + $i ) % 7;
				$x_values[] = $day_names[ $day_index ];
			}
		} elseif ( in_array( $range, array( 'this-week' ) ) ) {
			$start_of_week    = get_option( 'start_of_week' );
			$current_day      = gmdate( 'w' );
			$days_to_subtract = ( $current_day - $start_of_week + 7 ) % 7;
			$from_date        = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days" ) );
			$x_values         = array();
			$day_names        = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
			for ( $i = 0; $i < 7; $i++ ) {
				$day_index  = ( $start_of_week + $i ) % 7;
				$x_values[] = $day_names[ $day_index ];
			}
		} elseif ( in_array( $range, array( 'last-7' ) ) ) {
			$x_values = array();
			for ( $i = 6; $i >= 0; $i-- ) {
				$x_values[] = gmdate( 'D', strtotime( "-$i days" ) );
			}
		} elseif ( in_array( $range, array( 'this-month', 'last-month' ) ) ) {
			$x_values = array();

			if ( 'this-month' === $range ) {
				$date = new DateTime();
			} else {
				$date = new DateTime( 'first day of last month' );
			}

			$days_in_month = (int) $date->format( 't' );

			for ( $day = 1; $day <= $days_in_month; $day++ ) {
				if ( $day % 10 == 1 && $day % 100 != 11 ) {
					$suffix = 'st';
				} elseif ( $day % 10 == 2 && $day % 100 != 12 ) {
					$suffix = 'nd';
				} elseif ( $day % 10 == 3 && $day % 100 != 13 ) {
					$suffix = 'rd';
				} else {
					$suffix = 'th';
				}

				$x_values[] = $day . $suffix;
			}
		} elseif ( in_array( $range, array( 'last-30' ) ) ) {
			$x_values   = array();
			$end_date   = new DateTime();
			$start_date = clone $end_date;
			$start_date->modify( '-29 days' );

			$interval   = new DateInterval( 'P1D' );
			$date_range = new DatePeriod( $start_date, $interval, $end_date->modify( '+1 day' ) );

			foreach ( $date_range as $date ) {
				$day = (int) $date->format( 'j' );

				if ( $day % 10 == 1 && $day % 100 != 11 ) {
					$suffix = 'st';
				} elseif ( $day % 10 == 2 && $day % 100 != 12 ) {
					$suffix = 'nd';
				} elseif ( $day % 10 == 3 && $day % 100 != 13 ) {
					$suffix = 'rd';
				} else {
					$suffix = 'th';
				}

				$x_values[] = $day . $suffix;
			}
		} elseif ( in_array( $range, array( 'this-year', 'last-year' ) ) ) {
			$x_values = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
		} elseif ( strpos( $range, ',' ) !== false ) {
			// Handle custom date range.
			$range_dates   = explode( ',', $range );
			$start         = new DateTime( $range_dates[0] );
			$end           = new DateTime( $range_dates[1] );
			$interval      = new DateInterval( 'P1D' );
			$date_range    = new DatePeriod( $start, $interval, $end->modify( '+1 day' ) );
			$x_values      = array();
			foreach ( $date_range as $date ) {
				$x_values[] = $date->format( 'd M' ); // Format as '01 Jan'
			}
		} elseif ( in_array( $range, array( 'today' ) ) ) {
			$x_values = array( gmdate( 'D' ) );
		} elseif ( in_array( $range, array( 'yesterday' ) ) ) {
			$x_values = array( gmdate( 'D', strtotime( '-1 day' ) ) );
		} else {
			$x_values = array( '' );
		}

		// Initialize data arrays with zeros for all dates.
		$sale_data  = array_fill_keys( $x_values, 0 );
		$sale_count = array_fill_keys( $x_values, 0 );
		$orders     = array_merge(
			$this->total_orders( $range, 'completed' ),
			$this->total_orders( $range, 'processing' ),
			$this->total_orders( $range, 'refunded' ),
			$this->total_orders( $range, 'partially_refunded' ),
		);
		$sales      = array();

		foreach ( $orders as $order ) {
			$date_key                 = $this->get_date_key( strtotime( $order['created_at'] ), $range );
			$sale_data[ $date_key ]  += (float) $order['total'];
			$sale_count[ $date_key ] += 1;
		}

		$amount_formatted = array();
		$count_formatted  = array();

		foreach ( $x_values as $key ) {
			$amount_formatted[] = array( 'x' => $key, 'y' => $sale_data[ $key ] );
			$count_formatted[]  = array( 'x' => $key, 'y' => $sale_count[ $key ] );
		}

		$sales = array(
			array(
				'id'    => __( 'Sales Amount', 'easycommerce' ),
				'color' => '#7351FD',
				'data'  => $amount_formatted,
			),
			array(
				'id'    => __( 'Sales Count', 'easycommerce' ),
				'color' => '#19AA79',
				'data'  => $count_formatted,
			),
		);

		/**
		 * Filters the sales data before sending the response.
		 *
		 * @since 1.9
		 * @param array $sales The sales data.
		 * @param string $range The range.
		 * @param WP_REST_Request $request The request object.
		 */
		$sales = apply_filters( 'easycommerce_get_sales', $sales, $range, $request );

		$this->response_success(
			array(
				'message' => __( 'Store sales', 'easycommerce' ),
				'sales'   => $sales,
			)
		);
	}
	
	/**
	 * Recent orders
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_recent_orders( $request ) {
		$range = $request->get_param( 'range' );

		$args  = array( 'per_page' => 5 );

		if ( ! empty( $range ) ) {
			$date_range = Utility::get_date_range( $range );
			$args['from_date'] = $date_range[0];
			$args['to_date']   = $date_range[1];
		}

		$query  = Order::list( $args );
		$orders = $query['orders'];
		$orders = array_map(
			function ( $order ) {
				return array(
					'id'      => $order['id'],
					'total'   => easycommerce_price( $order['total'] ),
					'status'  => $order['status'],
					'created_at' => wp_date( 'd/m/Y', strtotime( $order['created_at'] ) ),
				);
			},
			$orders
		);

		/**
		 * Filters the recent orders before sending the response.
		 *
		 * @since 1.9
		 * @param array $orders The orders.
		 * @param WP_REST_Request $request The request object.
		 */
		$orders = apply_filters( 'easycommerce_get_recent_orders', $orders, $request );

		$this->response_success(
			array(
				'message' => __( 'Recent orders', 'easycommerce' ),
				'orders'  => $orders,
			)
		);
	}

	/**
	 * Get single abandoned cart details including items.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_abandoned_cart_details( $request ) {
		$hash     = $request->get_param( 'hash' );
		$cart_obj = new Cart( $hash );
		$cart     = $cart_obj->get( true );
		$items    = array();

		foreach ( $cart['items'] ?? array() as $item ) {
			$attributes = array_values( $item['attributes'] );
			$items[]    = array(
				'name'     => $item['title'],
				'variant'  => ! empty( $attributes ) ? implode( ' / ', $attributes ) : '',
				'quantity' => (int) $item['quantity'],
				'price'    => $item['unit_price'],
				'total'    => $item['formatted_total'],
				'image'    => $this->get_image_url( $item['thumbnail'], $item['product_id'] ),
			);
		}

		$this->response_success( array(
			'hash'       => $hash,
			'name'       => $cart_obj->get_customer_name(),
			'email'      => $cart_obj->get_customer_email(),
			'items'      => $items,
			'item_count' => $cart_obj->get_item_count(),
			'total'      => easycommerce_price( $cart_obj->get_amount() ),
			'updated_at' => $cart_obj->get_data( 'updated_at' ),
			'created_at' => $cart_obj->get_data( 'created_at' ),
		) );
	}

	/**
	 * Resolve a thumbnail value to a full image URL.
	 *
	 * @param int|string|array $thumbnail  Attachment ID, full URL, relative path, or thumbnail array with id/url keys.
	 * @param int|null         $product_id Optional product ID to fall back to if thumbnail is empty.
	 * @return string Full image URL, or empty string if not resolvable.
	 */
	private function get_image_url( $thumbnail, $product_id = null ) {
		$placeholder = EASYCOMMERCE_ASSETS_URL . 'public/img/product/shop-product-placeholder.png';

		if ( is_array( $thumbnail ) ) {
			if ( ! empty( $thumbnail['url'] ) ) {
				return $thumbnail['url'];
			}
			$thumbnail = $thumbnail['id'] ?? null;
		}

		if ( ! empty( $thumbnail ) ) {
			if ( is_numeric( $thumbnail ) ) {
				$src = wp_get_attachment_image_src( (int) $thumbnail, 'thumbnail' );
				return $src ? $src[0] : $placeholder;
			}
			return filter_var( $thumbnail, FILTER_VALIDATE_URL ) ? $thumbnail : site_url( $thumbnail );
		}

		if ( ! empty( $product_id ) ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
			return $src ? $src[0] : $placeholder;
		}

		return $placeholder;
	}

	/**
	 * Send a custom reminder email for an abandoned cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function send_abandoned_cart_reminder( $request ) {
		$hash     = $request->get_param( 'hash' );
		$subject  = sanitize_text_field( $request->get_param( 'subject' ) );
		$message  = wp_kses_post( $request->get_param( 'message' ) );
		$cart_obj = new Cart( $hash );
		$email    = $cart_obj->get_customer_email();

		if ( empty( $email ) ) {
			$this->response_success( array(
				'sent'    => false,
				'message' => __( 'No email address found for this cart.', 'easycommerce' ),
			) );
			return;
		}

		do_action( 'easycommerce_email', $email, $subject, $message, array() );
		$sent = (bool) apply_filters( 'easycommerce_mail_sent', false );

		if ( $sent ) {
			$reminders                   = (int) $cart_obj->get_data( 'reminders' );
			$cart_obj->cart['reminders'] = $reminders + 1;
			$cart_obj->save();
		}

		$this->response_success( array(
			'sent'    => $sent,
			'message' => $sent
				? __( 'Reminder email sent successfully.', 'easycommerce' )
				: __( 'Failed to send reminder email.', 'easycommerce' ),
		) );
	}

	/**
	 * Low stock
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_low_stock( $request ) {

		$product_query        = Product::list( array( 'status' => 'publish', 'is_shop' => false ), -1, 0, false );
		$low_stock_variations = array();

		foreach ( $product_query['products'] as $product ) {
			$product_obj = new Product( $product->ID );
			$variations  = $product_obj->get_variations();

			foreach ( $variations as $variation ) {

				$variation_stock = $variation->get_stock();
				$low_stock_limit = $variation->get_low_stock_limit();

				if ( 'digital' !== $variation->get_type() && isset( $low_stock_limit ) ) {
					if ( $variation_stock <= $low_stock_limit ) {
						$low_stock_variations[] = array(
							'variation_id' => $variation->get_id(),
							'title'        => $variation->get_name( true ),
							'stock'        => $variation_stock,
						);
					}
				}
			}
		}

		usort(
			$low_stock_variations,
			function ( $a, $b ) {
				return $a['stock'] <=> $b['stock'];
			}
		);

		$top_low_stock_variations = array_slice( $low_stock_variations, 0, 5 );

		if ( ! empty( $top_low_stock_variations ) ) {
			$variation_ids     = array_column( $top_low_stock_variations, 'variation_id' );
			$var_ph            = implode( ',', array_fill( 0, count( $variation_ids ), '%d' ) );
			$db_order_items    = new Database( 'order_items' );
			$order_items_table = $db_order_items->get_table();
			$db_orders         = new Database( 'orders' );
			$orders_table      = $db_orders->get_table();

			$sales_query = $db_order_items->prepare(
				"SELECT oi.variation_id, SUM(oi.quantity) as total_sold
				FROM {$order_items_table} oi
				INNER JOIN {$orders_table} o ON oi.order_id = o.id
				WHERE oi.variation_id IN ({$var_ph})
				AND o.status IN ('completed', 'processing', 'refunded', 'partially_refunded')
				AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
				GROUP BY oi.variation_id",
				$variation_ids
			);

			$sales_results = $db_order_items->exec( $sales_query, ARRAY_A );

			$sales_by_variation = array();
			foreach ( $sales_results as $row ) {
				$sales_by_variation[ (int) $row['variation_id'] ] = (int) $row['total_sold'];
			}

			foreach ( $top_low_stock_variations as &$item ) {
				$variation_id = $item['variation_id'];
				$total_sold_7 = $sales_by_variation[ $variation_id ] ?? 0;
				$avg_per_day  = $total_sold_7 / 7;

				if ( $avg_per_day > 0 ) {
					$predicted_days = (int) ceil( $item['stock'] / $avg_per_day );
					$item['days']   = $predicted_days . ' ' . ( $predicted_days === 1 ? __( 'day', 'easycommerce' ) : __( 'days', 'easycommerce' ) );
					$item['status'] = 'low_stock';
				} else {
					$item['days']   = '—';
					$item['status'] = 'low_stock';
				}

				unset( $item['variation_id'] );
			}
			unset( $item );
		}

		/**
		 * Filters the low stock data before sending the response.
		 *
		 * @since 1.9
		 * @param array $top_low_stock_variations The low stock variations.
		 * @param WP_REST_Request $request The request object.
		 */
		$top_low_stock_variations = apply_filters( 'easycommerce_get_low_stock', $top_low_stock_variations, $request );

		$this->response_success(
			array(
				'message' => __( 'Most lowest stock', 'easycommerce' ),
				'stock'   => $top_low_stock_variations,
			)
		);
	}

	/*
	 * Top seller
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_top_sellers( $request ) {
		$range = $request->get_param( 'range' );

		if ( ! empty( $range ) ) {
			$date_range = Utility::get_date_range( $range );
			$from_date  = $date_range[0] . ' 00:00:00';
			$to_date    = $date_range[1] . ' 23:59:59';
		} else {
			$from_date = null;
			$to_date   = null;
		}

		global $wpdb;

		$where_clause = "oi.product_id IS NOT NULL AND o.status IN ('completed', 'processing', 'refunded', 'partially_refunded')";

		if ( ! empty( $from_date ) && ! empty( $to_date ) ) {
			$where_clause .= $wpdb->prepare( " AND o.created_at >= %s AND o.created_at <= %s", $from_date, $to_date );
		}

		$sales_query = "
			SELECT oi.product_id, SUM(oi.quantity) as items_sold, SUM(oi.subtotal) as sales
			FROM {$wpdb->prefix}ec_order_items oi
			INNER JOIN {$wpdb->prefix}ec_orders o ON oi.order_id = o.id
			WHERE {$where_clause}
			GROUP BY oi.product_id
			ORDER BY sales DESC
			LIMIT 5
		";

		$sales_results = $wpdb->get_results( $sales_query );

		$sellers 	   = array();
		foreach ( $sales_results as $row ) {
			$product_obj = new Product( (int) $row->product_id );
			if ( $product_obj->get_id() ) {
				$sellers[] = array(
					'id'         => (int) $row->product_id,
					'title'      => $product_obj->get_title(),
					'items_sold' => (int) $row->items_sold,
					'sales'      => easycommerce_price( $row->sales ),
				);
			}
		}

		/**
		 * Filters the top sellers before sending the response.
		 *
		 * @since 1.9
		 * @param array $sellers The sellers.
		 * @param WP_REST_Request $request The request object.
		 */
		$sellers = apply_filters( 'easycommerce_get_top_sellers', $sellers, $request );

		$this->response_success(
			array(
				'message' => __( 'Top sellers', 'easycommerce' ),
				'sellers' => $sellers,
			)
		);
	}

	/**
	 * Recent activities
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_activities( $request ) {
		$type     = $request->get_param( 'type' ) ?? 'All';
		$from     = $request->get_param( 'from' );
		$to       = $request->get_param( 'to' );
		$log_args = array();
		$limit    = 10;

		if ( ! empty( $from ) && ! empty( $to ) ) {
			$log_args['from_date'] = $from;
			$log_args['to_date']   = $to;
		}

		switch ( $type ) {
			case 'Orders':
				$log_args['object'] = 'order';
				$log_args['exclude_actions'] = array( 'refund' );
				break;
			case 'Refunds':
				$log_args['object']   = 'order';
				$log_args['action'] = 'refund';
				break;
			case 'Reviews':
				$log_args['object'] = 'review';
				break;
			case 'Others':
				$log_args['exclude_objects'] = array( 'order', 'review' );
				$log_args['exclude_actions'] = array( 'refund' );
				break;
		}

		$query = Log::list( $log_args, $limit, 0, 'desc' );
		$logs  = $query['logs'];

		$activities = array_map(
			function ( $log ) use ( $type ) {
				$log_type = 'Others';
				if ( 'order' === $log->object ) {
					if ( 'refund' === $log->action && 'Orders' !== $type ) {
						$log_type = 'Refunds';
					} else {
						$log_type = 'Orders';
					}
				} elseif ( 'review' === $log->object ) {
					$log_type = 'Reviews';
				}

				$title = ucfirst( str_replace( '_', ' ', $log->action ) );
				if ( in_array( $log->action, array( 'create', 'add' ) ) ) {
					$title = ucfirst( $log->object ) . ' Added';
				}
				if ( 'add_note' === $log->action ) {
					if ( empty( $log->is_public ) ) {
						$title = __( 'Private Note', 'easycommerce' );
					} else {
						$title = __( 'Note to Customer', 'easycommerce' );
					}
				}

				return array(
					'id'          => $log->id,
					'type'        => $log_type,
					'title'       => $title,
					'description' => $log->note ?: 'Action performed',
					'date'        => $log->created_at,
				);
			},
			$logs
		);

		/**
		 * Filters the recent activities before sending the response.
		 *
		 * @since 1.9
		 * @param array $activities The activities.
		 * @param WP_REST_Request $request The request object.
		 */
		$activities = apply_filters( 'easycommerce_get_activities', $activities, $request );

		$this->response_success(
			array(
				'message' => __( 'Recent activities', 'easycommerce' ),
				'activities' => $activities,
			)
		);
	}

	/**
	 * Reports
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function reports( $request ) {

		$range = $request->get_param( 'range' );
		if ( ! $range ) {
			$from  = $request->get_param( 'from' );
			$to    = $request->get_param( 'to' );
			$range = implode( ',', array( $from, $to ) );
		}
		if ( in_array( $range, array( 'last-week' ) ) ) {
			$start_of_week = get_option( 'start_of_week' );
			$x_values      = array();
			$day_names     = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
			for ( $i = 0; $i < 7; $i++ ) {
				$day_index  = ( $start_of_week + $i ) % 7;
				$x_values[] = $day_names[ $day_index ];
			}
		} elseif ( in_array( $range, array( 'this-week' ) ) ) {
			$start_of_week = get_option( 'start_of_week' );
			$x_values      = array();
			$day_names     = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
			for ( $i = 0; $i < 7; $i++ ) {
				$day_index  = ( $start_of_week + $i ) % 7;
				$x_values[] = $day_names[ $day_index ];
			}
		} elseif ( in_array( $range, array( 'last-7' ) ) ) {

			$x_values = array();
			for ( $i = 6; $i >= 0; $i-- ) {
				$x_values[] = gmdate( 'D', strtotime( "-$i days" ) );
			}
		} elseif ( in_array( $range, array( 'this-month', 'last-month' ) ) ) {
			$x_values = array();

			if ( 'this-month' === $range ) {
				$date = new DateTime();
			} else {
				$date = new DateTime( 'first day of last month' );
			}

			$days_in_month = (int) $date->format( 't' );

			for ( $day = 1; $day <= $days_in_month; $day++ ) {
				if ( $day % 10 == 1 && $day % 100 != 11 ) {
					$suffix = 'st';
				} elseif ( $day % 10 == 2 && $day % 100 != 12 ) {
					$suffix = 'nd';
				} elseif ( $day % 10 == 3 && $day % 100 != 13 ) {
					$suffix = 'rd';
				} else {
					$suffix = 'th';
				}

				$x_values[] = $day . $suffix;
			}
		} elseif ( in_array( $range, array( 'last-30' ) ) ) {
			$x_values   = array();
			$end_date   = new DateTime();
			$start_date = clone $end_date;
			$start_date->modify( '-29 days' );

			$interval   = new DateInterval( 'P1D' );
			$date_range = new DatePeriod( $start_date, $interval, $end_date->modify( '+1 day' ) );

			foreach ( $date_range as $date ) {
				$day = (int) $date->format( 'j' );

				if ( $day % 10 == 1 && $day % 100 != 11 ) {
					$suffix = 'st';
				} elseif ( $day % 10 == 2 && $day % 100 != 12 ) {
					$suffix = 'nd';
				} elseif ( $day % 10 == 3 && $day % 100 != 13 ) {
					$suffix = 'rd';
				} else {
					$suffix = 'th';
				}

				$x_values[] = $day . $suffix;
			}
		} elseif ( in_array( $range, array( 'this-year', 'last-year' ) ) ) {
			$x_values = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
		} elseif ( strpos( $range, ',' ) !== false ) {
			$range_dates  = explode( ',', $range );
			$start        = new DateTime( $range_dates[0] );
			$end          = new DateTime( $range_dates[1] );
			$interval     = new DateInterval( 'P1D' );
			$date_range   = new DatePeriod( $start, $interval, $end->modify( '+1 day' ) );
			$x_values     = array();
			foreach ( $date_range as $date ) {
				$x_values[] = $date->format( 'd M' ); // Format as '01 Jan'.
			}
		} elseif ( in_array( $range, array( 'today' ) ) ) {
			$x_values = array( gmdate( 'D' ) );
		} elseif ( in_array( $range, array( 'yesterday' ) ) ) {
			$x_values = array( gmdate( 'D', strtotime( '-1 day' ) ) );
		} else {
			$x_values = array( '' );
		}

		$sales_count_by_date      = $sales_amount_by_date = $refund_count_by_date =
		$refund_amount_by_date    = $pending_count_by_date = $pending_amount_by_date =
		$completed_count_by_date  = $completed_amount_by_date =
		$cancelled_count_by_date  = $cancelled_amount_by_date =
		$onhold_count_by_date     = $onhold_amount_by_date =
		$processing_count_by_date = $processing_amount_by_date =
		array_fill_keys( $x_values, 0 );

		$total_sales       = $refund_count = $pending_count = $completed_count = $cancelled_count = $onhold_count = $processing_count = 0;
		$product_id        = $request->get_param( 'product_id' );
		$sales_amount      = $this->total_sales( $range, '', $product_id );
		$orders            = $this->total_orders( $range, '', $product_id );
		$refunds_amount    = $this->total_sales( $range, 'refunded', $product_id );
		$pending_amount    = $this->total_sales( $range, 'pending', $product_id );
		$completed_amount  = $this->total_sales( $range, 'completed', $product_id );
		$cancelled_amount  = $this->total_sales( $range, 'cancelled', $product_id );
		$onhold_amount     = $this->total_sales( $range, 'on_hold', $product_id );
		$processing_amount = $this->total_sales( $range, 'processing', $product_id );

		foreach ( $orders as $order ) {
			$date_key = $this->get_date_key( strtotime( $order['created_at'] ), $range );
			++$sales_count_by_date[ $date_key ];
			$sales_amount_by_date[ $date_key ] += $order['total'];

			if ( $order['status'] === 'refunded' ) {
				++$refund_count_by_date[ $date_key ];
				$refund_amount_by_date[ $date_key ] += $order['total'];
				++$refund_count;
			}

			if ( $order['status'] === 'pending' ) {
				++$pending_count_by_date[ $date_key ];
				$pending_amount_by_date[ $date_key ] += $order['total'];
				++$pending_count;
			}

			if ( $order['status'] === 'completed' ) {
				++$completed_count_by_date[ $date_key ];
				$completed_amount_by_date[ $date_key ] += $order['total'];
				++$completed_count;
			}

			if ( $order['status'] === 'cancelled' ) {
				++$cancelled_count_by_date[ $date_key ];
				$cancelled_amount_by_date[ $date_key ] += $order['total'];
				++$cancelled_count;
			}

			if ( $order['status'] === 'on_hold' ) {
				++$onhold_count_by_date[ $date_key ];
				$onhold_amount_by_date[ $date_key ] += $order['total'];
				++$onhold_count;
			}

			if ( $order['status'] === 'processing' ) {
				++$processing_count_by_date[ $date_key ];
				$processing_amount_by_date[ $date_key ] += $order['total'];
				++$processing_count;
			}
			++$total_sales;
		}

		$sales_count_by_date       = array_values( $sales_count_by_date );
		$sales_amount_by_date      = array_values( $sales_amount_by_date );
		$refund_count_by_date      = array_values( $refund_count_by_date );
		$refund_amount_by_date     = array_values( $refund_amount_by_date );
		$pending_count_by_date     = array_values( $pending_count_by_date );
		$pending_amount_by_date    = array_values( $pending_amount_by_date );
		$completed_count_by_date   = array_values( $completed_count_by_date );
		$completed_amount_by_date  = array_values( $completed_amount_by_date );
		$cancelled_count_by_date   = array_values( $cancelled_count_by_date );
		$cancelled_amount_by_date  = array_values( $cancelled_amount_by_date );
		$onhold_count_by_date      = array_values( $onhold_count_by_date );
		$onhold_amount_by_date     = array_values( $onhold_amount_by_date );
		$processing_count_by_date  = array_values( $processing_count_by_date );
		$processing_amount_by_date = array_values( $processing_amount_by_date );

		$data_groups = array(
			'sales'      => array(
				'count'        => $sales_count_by_date,
				'amount'       => $sales_amount_by_date,
				'count_total'  => $total_sales,
				'amount_total' => $sales_amount,
				'color_count'  => '#88CD44',
				'color_amount' => '#7AAA49',
				'label'        => 'Sales',
			),
			'refund'     => array(
				'count'        => $refund_count_by_date,
				'amount'       => $refund_amount_by_date,
				'count_total'  => $refund_count,
				'amount_total' => $refunds_amount,
				'color_count'  => '#7A59FF',
				'color_amount' => '#5433DA',
				'label'        => 'Refund',
			),
			'completed'  => array(
				'count'        => $completed_count_by_date,
				'amount'       => $completed_amount_by_date,
				'count_total'  => $completed_count,
				'amount_total' => $completed_amount,
				'color_count'  => '#19AA79',
				'color_amount' => '#0A875D',
				'label'        => 'Completed',
			),
			'pending'    => array(
				'count'        => $pending_count_by_date,
				'amount'       => $pending_amount_by_date,
				'count_total'  => $pending_count,
				'amount_total' => $pending_amount,
				'color_count'  => '#FD7F51',
				'color_amount' => '#D55D31',
				'label'        => 'Pending',
			),
			'processing' => array(
				'count'        => $processing_count_by_date,
				'amount'       => $processing_amount_by_date,
				'count_total'  => $processing_count,
				'amount_total' => $processing_amount,
				'color_count'  => '#B759FF',
				'color_amount' => '#9427E8',
				'label'        => 'Processing',
			),
			'onhold'     => array(
				'count'        => $onhold_count_by_date,
				'amount'       => $onhold_amount_by_date,
				'count_total'  => $onhold_count,
				'amount_total' => $onhold_amount,
				'color_count'  => '#5283FF',
				'color_amount' => '#2356D7',
				'label'        => 'Onhold',
			),
			'cancelled'  => array(
				'count'        => $cancelled_count_by_date,
				'amount'       => $cancelled_amount_by_date,
				'count_total'  => $cancelled_count,
				'amount_total' => $cancelled_amount,
				'color_count'  => '#FF598E',
				'color_amount' => '#E24476',
				'label'        => 'Cancelled',
			),
		);

		/**
		 * Profit Margin Amount Calculation
		 */
		$margin_amount_by_date 	= array_fill_keys( $x_values, 0 );
		$margin_amount_total 	= 0;
		$completed_orders 		= $this->total_orders( $range, 'completed', $product_id );

		foreach ( $completed_orders as $order ) {
			$order_obj      = new Order( $order['id'] );
			$items          = $order_obj->get_items();
			$date_key       = $this->get_date_key( strtotime( $order['created_at'] ), $range );
			$total_discount = abs( $order_obj->get_discount_total() );

			$total_original_price = 0;
			foreach ( $items as $item ) {
				$product_variation 		= new Product_Variation( $item->variation_id );
				$price 					= $product_variation->get_price();
				$total_original_price 	+= ( $price * $item->quantity );
			}
			
			$order_profit = 0;
			
			foreach ( $items as $item ) {
				$product_variation  = new Product_Variation( $item->variation_id );
				$order_item_model   = new Order_Item();
				$order_iteam        = $order_item_model->get_by_id( $item->id );
				$cost               = $product_variation->get_meta('cost_per_item', true);

				if ( empty( $cost ) || $order_iteam->subtotal == '0.00' ) {
					continue;
				}
				$price 				= $product_variation->get_price();
				$discounted_price 	= $price;
				
				if ( $total_discount > 0 && $total_original_price > 0 ) {
					$item_original_total 	= $price * $item->quantity;
					$item_discount 			= ( $total_discount * $item_original_total ) / $total_original_price;
					$discount_per_unit 		= $item_discount / $item->quantity;
					$discounted_price 		= max( 0, $price - $discount_per_unit );
				}

				$item_profit 	= ( $discounted_price - $cost ) * $item->quantity;
				$order_profit 	+= $item_profit;
			}
			
			$margin_amount_by_date[ $date_key ] += $order_profit;
			$margin_amount_total += $order_profit;
		}

		$margin_amount_by_date = array_values( $margin_amount_by_date );

		// Add margin data to the datasets
		$data_groups['margin'] = array(
			'count' 	   => array_fill( 0, count( $x_values ), 0 ),
			'amount'       => $margin_amount_by_date,
			'count_total'  => 0,
			'amount_total' => $margin_amount_total,
			'color_count'  => '#009D68',
			'color_amount' => '#009D68',
			'label'        => 'Margin',
		);

		$datasets = array();
		foreach ( $data_groups as $key => $group ) {
			if ( $key !== 'margin' ) {
				$datasets[] = array(
					'id'              => "{$key}-count",
					/* Translators: %s is the group label */
					'label'           => sprintf( __( '%s Count', 'easycommerce' ), $group['label'] ),
					'data'            => $group['count'],
					'total'           => $group['count_total'],
					'yAxisID'         => 'y2',
					'borderColor'     => $group['color_count'],
					'backgroundColor' => $group['color_count'],
					'tension'         => 0.4,
					'fill'            => false,
					'pointRadius'     => 5,
					'pointStyle'      => 'circle',
				);
			}

			$datasets[] = array(
				'id'              => "{$key}-amount",
				/* Translators: %s is the group label */
				'label'           => sprintf( __( '%s Amount', 'easycommerce' ), $group['label'] ),
				'data'            => $group['amount'],
				'total'           => $group['amount_total'],
				'yAxisID'         => 'y1',
				'borderColor'     => $group['color_amount'],
				'backgroundColor' => $group['color_amount'],
				'tension'         => 0.4,
				'fill'            => false,
				'pointRadius'     => 5,
				'pointStyle'      => 'circle',
			);
		}

		$reports = array(
			'labels'   => $x_values,
			'datasets' => $datasets,
		);

		/**
		 * Filters the reports data before sending the response.
		 *
		 * @since 1.9
		 * @param array $reports The reports.
		 * @param string $range The range.
		 * @param WP_REST_Request $request The request object.
		 */
		$reports = apply_filters( 'easycommerce_get_reports', $reports, $range, $request );

		$this->response_success( array( 'reports' => $reports ) );
	}

	/**
	 * Returns a date key based on the given timestamp and range.
	 *
	 * The format of the date key depends on the range:
	 * - If the range is a custom range (i.e. it contains a comma), then the date key is the day of the month (e.g. 1st, 2nd, 3rd, etc.).
	 * - If the range is 'this-year' or 'last-year', then the date key is the full month name (e.g. January, February, etc.).
	 * - If the range is 'this-week', 'last-week', 'last-7', 'today', or 'yesterday', then the date key is the abbreviated day of the week (e.g. Mon, Tue, Wed, etc.).
	 *
	 * @param int    $timestamp The timestamp to get the date key for.
	 * @param string $range The range to get the date key for.
	 * @return string The date key.
	 */
	public function get_date_key( $timestamp, $range ) {
		if ( strpos( $range, ',' ) !== false ) {
			return gmdate( 'd M', $timestamp );
		}

		if ( in_array( $range, array( 'this-year', 'last-year' ) ) ) {
			return date( 'M', $timestamp );
		}

		if ( in_array( $range, array( 'this-week', 'last-week', 'last-7', 'today', 'yesterday' ) ) ) {
			return date( 'D', $timestamp );
		}

		$day = date( 'j', $timestamp );

		if ( $day % 10 == 1 && $day % 100 != 11 ) {
			$suffix = 'st';
		} elseif ( $day % 10 == 2 && $day % 100 != 12 ) {
			$suffix = 'nd';
		} elseif ( $day % 10 == 3 && $day % 100 != 13 ) {
			$suffix = 'rd';
		} else {
			$suffix = 'th';
		}

		return $day . $suffix;
	}

	/**
	 * Total orders
	 *
	 * @param string $range The date range to query.
	 * @param string $status The order status to filter by.
	 * @param int    $product_id The product ID.
	 * @return array
	 */
	public function total_orders( $range = 'this-month', $status = '', $product_id = null ) {
		$from_date  = Utility::get_date_range( $range )[0];
		$to_date    = Utility::get_date_range( $range )[1];
		$date_items = explode( ',', $range );

		if ( count( $date_items ) == 2 ) {
			$from_date = $date_items[0];
			$to_date   = $date_items[1];
		}

		$args = array(
			'from_date'  => $from_date,
			'to_date'    => $to_date,
			'product_id' => $product_id,
			'per_page'   => -1,
		);

		if ( ! empty( $status ) ) {
			$args['status'] = $status;
		}

		$orders = Order::list( $args );

		return $orders['orders'];
	}

	/**
	 * Total sales
	 *
	 * @param string $range The date range to query.
	 * @param string $status The order status to filter by.
	 * @param int    $product_id The product ID.
	 * @return float
	 */
	public function total_sales( $range = 'this-month', $status = '', $product_id = null ) {

		$orders = $this->total_orders( $range, $status, $product_id );

		if ( empty( $orders ) ) {
			return 0;
		}
		$total_sales = 0;
		foreach ( $orders as $order ) {
			$total_sales += $order['total'];
		}

		return $total_sales;
	}

	/**
	 * Total products sold
	 *
	 * @param string $range The date range to query.
	 * @return int
	 */
	public function total_products_sold( $range = 'this-month' ) {
		$orders = $this->total_orders( $range );
		$total  = 0;
		foreach ( $orders as $order ) {
			$total += $order['items'];
		}
		return $total;
	}

	/**
	 * Total orders by status
	 *
	 * @param string $range The date range to query.
	 * @param string $status The order status to filter by.
	 * @return int
	 */
	public function total_orders_by_status( $range, $status ) {
		$orders = $this->total_orders( $range, $status );
		$total  = 0;
		foreach ( $orders as $order ) {
			if ( $order['status'] === $status ) {
				++$total;
			}
		}
		return $total;
	}

	/**
	 * Get customers
	 *
	 * @param string $range The date range to query.
	 * @param string $status The order status to filter by.
	 * @return array
	 */
	public function get_customers( $range = 'this-month' ) {
		global $wpdb;

		$date_range = Utility::get_date_range( $range );
		$from_date  = $date_range[0] . ' 00:00:00';
		$to_date    = $date_range[1] . ' 23:59:59';

		if ( empty( $from_date ) || empty( $to_date ) ) {
			return array();
		}

		$query = $wpdb->prepare(
			"
			SELECT u.ID 
			FROM {$wpdb->users} u
			INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
			WHERE u.user_registered BETWEEN %s AND %s
			AND um.meta_key = %s
			AND (um.meta_value LIKE %s OR um.meta_value LIKE %s)
			",
			$from_date,
			$to_date,
			"{$wpdb->prefix}capabilities",
			'%"has_order"%',
			'%"customer"%'
		);
		return $wpdb->get_col( $query );
	}

	public function get_abandoned_carts( $range = 'this-month', $from = null, $to = null ) {
		$cart  = new Cart;
		$delay = Utility::get_option( 'abandoned-cart', 'settings', 'delay', 30 );
		return count( $cart->get_abandoned( $delay, $range, $from, $to ) );
	}

	private function get_total_products() {
		return (int) wp_count_posts( 'product' )->publish;
	}

	/**
	 * Get total refunded amount from the Refund model (handles partial refunds correctly)
	 *
	 * @param string $range The date range to query.
	 * @return float
	 */
	public function total_refunded_amount( $range = 'this-month' ) {
		$date_range = Utility::get_date_range( $range );
		$from_date  = $date_range[0];
		$to_date    = $date_range[1];
		$date_items = explode( ',', $range );

		if ( count( $date_items ) == 2 ) {
			$from_date = $date_items[0];
			$to_date   = $date_items[1];
		}

		$result = Refund::list(
			array(
				'status'    => 'approved',
				'from_date' => $from_date,
				'to_date'   => $to_date,
			),
			-1,
			0
		);

		if ( empty( $result['refunds'] ) ) {
			return 0;
		}

		return array_sum( array_column( (array) $result['refunds'], 'amount' ) );
	}

}
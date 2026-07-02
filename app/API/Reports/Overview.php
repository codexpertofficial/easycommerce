<?php

namespace EasyCommerce\API\Reports;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product;
use WP_REST_Request;

/**
 * Reports Overview API
 */
class Overview extends Reports {

	/**
	 * Reports stats overview
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function stats( $request ) {
		$range             = $request->get_param( 'range' ) ?: 'last-30';
		$comparison        = $request->get_param( 'comparison' );
		$comparison_range  = $comparison ?: $this->get_comparison_range( $range );
		$cache_key         = $this->generate_cache_key( 'overview_stats', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $comparison_range, $request ) {
				$current_stats      = $this->calculate_stats( $range );
				$previous_stats     = $this->calculate_stats( $comparison_range );
				$products_sold      = $this->get_products_sold( $range );
				$prev_products_sold = $this->get_products_sold( $comparison_range );
				$customers          = $this->get_unique_customers( $range );
				$prev_customers     = $this->get_unique_customers( $comparison_range );

				$overview_data = array(
					array(
						'title'      => __( 'Orders', 'easycommerce' ),
						'value'      => number_format( $current_stats['orders'] ),
						'icon'       => 'orders-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['orders'], $previous_stats['orders'] ),
						'tooltip'    => __( 'All orders except On Hold, Pending, Cancelled.', 'easycommerce' ),
					),
					array(
						'title'      => __( 'Sales', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['sales'] ),
						'icon'       => 'sales-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['sales'], $previous_stats['sales'] ),
						'tooltip'    => __( 'Total sales before any refunds or deductions.', 'easycommerce' ),
					),
					array(
						'title'      => __( 'Refunds', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['refunds'] ),
						'icon'       => 'refunds-icon',
						'comparison' => $this->get_refund_comparison( $current_stats['refunds'], $previous_stats['refunds'] ),
					),
					array(
						'title'      => __( 'Net Revenue', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['net_revenue'] ),
						'icon'       => 'revenue-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['net_revenue'], $previous_stats['net_revenue'] ),
						'tooltip'    => __( 'Total revenue after any refunds or deductions.', 'easycommerce' ),
					),
					array(
						'title'      => __( 'Products Sold', 'easycommerce' ),
						'value'      => number_format( $products_sold ),
						'icon'       => 'products-icon',
						'comparison' => $this->get_comparison_with_vibe( $products_sold, $prev_products_sold ),
					),
					array(
						'title'      => __( 'Customers', 'easycommerce' ),
						'value'      => number_format( $customers ),
						'icon'       => 'customers-icon',
						'comparison' => $this->get_comparison_with_vibe( $customers, $prev_customers ),
					),
				);

				return apply_filters( 'easycommerce_reports_stats', $overview_data, $range, $request );
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success(
			array(
				'stats'           => $result,
				'range'           => $range,
				'comparison_with' => $comparison_range,
			)
		);
	}

	/**
	 * Get total products sold
	 *
	 * @param string $range The date range.
	 * @return int
	 */
	private function get_products_sold( $range ) {
		$dates             = $this->resolve_dates( $range );
		$from_date         = $dates['from'];
		$to_date           = $dates['to'];
		$db                = new Database( 'order_items' );
		$order_items_table = $db->get_table();
		$orders_table      = $db->get_wp_prefix() . 'ec_orders';

		$query  = $db->prepare(
			"SELECT SUM(oi.quantity) as total_sold
			FROM {$order_items_table} oi
			INNER JOIN {$orders_table} o ON oi.order_id = o.id
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'refunded', 'partially_refunded')",
			$from_date,
			$to_date . ' 23:59:59'
		);
		$result = $db->exec( $query, ARRAY_A );

		return $result[0]['total_sold'] ?? 0;
	}

	/**
	 * Get unique customers count
	 *
	 * @param string $range The date range.
	 * @return int
	 */
	private function get_unique_customers( $range ) {
		$dates        = $this->resolve_dates( $range );
		$from_date    = $dates['from'];
		$to_date      = $dates['to'];
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();

		$query  = $db->prepare(
			"SELECT COUNT(DISTINCT customer_id) as total_customers
			FROM {$orders_table}
			WHERE created_at >= %s AND created_at <= %s
			AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')",
			$from_date,
			$to_date . ' 23:59:59'
		);
		$result = $db->exec( $query, ARRAY_A );

		return $result[0]['total_customers'] ?? 0;
	}

	/**
	 * Get Sales vs Refund vs Revenue data with chart visualization
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function sales_refund_revenue( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'overview_sales_refund_revenue', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$labels = $this->get_chart_labels( $range );

				$sales_by_date   = $this->get_sales_by_date( $range );
				$refunds_by_date = $this->get_refunds_by_date( $range );
				$revenue_by_date = $this->get_revenue_by_date( $range );

				$datasets = array(
					array(
						'id'    => 'Sales',
						'color' => '#19AA79',
						'fill'  => false,
						'data'  => $sales_by_date,
					),
					array(
						'id'    => 'Refunds',
						'color' => '#FD7F51',
						'fill'  => false,
						'data'  => $refunds_by_date,
					),
					array(
						'id'    => 'Net Revenue',
						'color' => '#5283FF',
						'fill'  => false,
						'data'  => $revenue_by_date,
					),
				);

				return array(
					'labels'   => $labels,
					'datasets' => apply_filters( 'easycommerce_reports_sales_refund_revenue', $datasets, $range, $request ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success(
			array(
				'labels'   => $result['labels'],
				'datasets' => $result['datasets'],
				'range'    => $range,
			)
		);
	}

	/**
	 * Count items by date from an array of records with 'created_at'.
	 *
	 * @param array  $items Array of records with 'created_at' key.
	 * @param string $range The date range.
	 * @return array Chart-formatted data array.
	 */
	private function count_items_by_date( $items, $range ) {
		$labels = $this->get_chart_labels( $range );
		$data   = array_fill_keys( $labels, 0 );

		foreach ( $items as $item ) {
			$date_key = $this->get_date_key( strtotime( $item['created_at'] ), $range );
			if ( isset( $data[ $date_key ] ) ) {
				++$data[ $date_key ];
			}
		}

		return $this->format_chart_data( array_values( $data ), $labels );
	}

	/**
	 * Sum values by date from an array of records with 'created_at'.
	 *
	 * @param array  $items Array of records with 'created_at' and 'total'.
	 * @param string $range The date range.
	 * @return array Chart-formatted data array.
	 */
	private function sum_items_by_date( $items, $range ) {
		$labels = $this->get_chart_labels( $range );
		$data   = array_fill_keys( $labels, 0 );

		foreach ( $items as $item ) {
			$date_key = $this->get_date_key( strtotime( $item['created_at'] ), $range );
			if ( isset( $data[ $date_key ] ) ) {
				$data[ $date_key ] += $item['total'];
			}
		}

		return $this->format_chart_data( array_values( $data ), $labels );
	}

	/**
	 * Get sales amount by date
	 *
	 * @param string $range The date range.
	 * @param string $status Optional status filter.
	 *
	 * @return array
	 */
	private function get_sales_by_date( $range ) {
		$orders = array();
		foreach ( array( 'completed', 'processing', 'partially_refunded', 'refunded' ) as $status ) {
			$orders = array_merge( $orders, $this->total_orders( $range, array( 'status' => $status ) ) );
		}
		return $this->sum_items_by_date( $orders, $range );
	}

	/**
	 * Get refunds by date from the refunds table
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_refunds_by_date( $range ) {
		$dates         = $this->resolve_dates( $range );
		$labels        = $this->get_chart_labels( $range );
		$db            = new Database( 'refunds' );
		$refunds_table = $db->get_table();

		$query = $db->prepare(
			"SELECT DATE(created_at) as order_date, SUM(amount) as total_refunds
			FROM {$refunds_table}
			WHERE created_at >= %s AND created_at <= %s
			AND status IN ('processed', 'approved')
			GROUP BY DATE(created_at)",
			$dates['from'] . ' 00:00:00',
			$dates['to'] . ' 23:59:59'
		);

		$values = $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'total_refunds', 'float' );

		return $this->format_chart_data( $values, $labels );
	}

	/**
	 * Sum refunds by date and format for chart
	 *
	 * @param array  $refunds_by_date Refunds keyed by date.
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function sum_refunds_by_date( $refunds_by_date, $range ) {
		$labels = $this->get_chart_labels( $range );
		$data   = array();

		if ( in_array( $range, array( 'this-year', 'last-year' ) ) ) {
			$months = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
			foreach ( $labels as $label ) {
				$month_index = array_search( $label, $months );
				$year        = 'this-year' === $range ? gmdate( 'Y' ) : gmdate( 'Y', strtotime( '-1 year' ) );
				$month_date  = gmdate( 'Y-m', strtotime( $year . '-' . ( $month_index + 1 ) . '-01' ) );
				$total      = 0;
				foreach ( $refunds_by_date as $date => $amount ) {
					if ( strpos( $date, $month_date ) === 0 ) {
						$total += $amount;
					}
				}
				$data[] = array( 'x' => $label, 'y' => $total );
			}
		} elseif ( in_array( $range, array( 'this-week', 'last-week' ) ) ) {
			$start_of_week = get_option( 'start_of_week' );
			$today         = new \DateTime();
			$day_names     = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );

			if ( 'this-week' === $range ) {
				$week_start = ( clone $today )->modify( '-' . ( (int) $today->format( 'w' ) - $start_of_week + 7 ) % 7 . ' days' );
			} else {
				$week_start = ( clone $today )->modify( '-' . ( (int) $today->format( 'w' ) - $start_of_week + 7 ) % 7 . ' days' )->modify( '-7 days' );
			}

			foreach ( $labels as $label ) {
				$day_index = array_search( $label, $day_names );
				$day_date  = ( clone $week_start )->modify( "+{$day_index} days" )->format( 'Y-m-d' );
				$y         = isset( $refunds_by_date[ $day_date ] ) ? $refunds_by_date[ $day_date ] : 0;
				$data[]    = array( 'x' => $label, 'y' => $y );
			}
		} else {
			$from = new \DateTime( $this->resolve_dates( $range )['from'] );
			$to   = new \DateTime( $this->resolve_dates( $range )['to'] );
			$to->modify( '+1 day' );

			$interval = new \DateInterval( 'P1D' );
			$period   = new \DatePeriod( $from, $interval, $to );

			$label_index = 0;
			foreach ( $period as $date ) {
				$date_str   = $date->format( 'Y-m-d' );
				$label      = isset( $labels[ $label_index ] ) ? $labels[ $label_index ] : '';
				$y          = isset( $refunds_by_date[ $date_str ] ) ? $refunds_by_date[ $date_str ] : 0;
				$data[]     = array( 'x' => $label, 'y' => $y );
				$label_index++;
			}
		}

		return $data;
	}

	/**
	 * Get net revenue by date
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_revenue_by_date( $range ) {
		$labels       = $this->get_chart_labels( $range );
		$sales_data   = $this->get_sales_by_date( $range );
		$refund_data  = $this->get_refunds_by_date( $range );
		$revenue_data = array();

		for ( $i = 0; $i < count( $sales_data ); $i++ ) {
			$sales          = $sales_data[ $i ]['y'];
			$refund         = isset( $refund_data[ $i ] ) ? $refund_data[ $i ]['y'] : 0;
			$revenue_data[] = $sales - $refund;
		}

		return $this->format_chart_data( $revenue_data, $labels );
	}

	/**
	 * Get orders vs refund count comparison for chart visualization
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function order_vs_refund( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'overview_order_vs_refund', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$labels          = $this->get_chart_labels( $range );
				$orders_by_date  = $this->get_order_count_by_date( $range );
				$refunds_by_date = $this->get_refund_count_by_date( $range );

				$datasets = array(
					array(
						'id'    => 'Order Count',
						'color' => '#F63C3C',
						'fill'  => true,
						'data'  => $orders_by_date,
					),
					array(
						'id'         => 'Refund Count',
						'color'      => '#FD7F51',
						'fill'       => false,
						'data'       => $refunds_by_date,
						'borderDash' => array( 6, 4 ),
					),
				);

				return array(
					'labels'   => $labels,
					'datasets' => apply_filters( 'easycommerce_reports_order_vs_refund', $datasets, $range, $request ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success(
			array(
				'labels'   => $result['labels'],
				'datasets' => $result['datasets'],
				'range'    => $range,
			)
		);
	}

	/**
	 * Get order count by date
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_order_count_by_date( $range ) {
		$completed_orders          = $this->total_orders( $range, array( 'status' => 'completed' ) );
		$processing_orders         = $this->total_orders( $range, array( 'status' => 'processing' ) );
		$partially_refunded_orders = $this->total_orders( $range, array( 'status' => 'partially_refunded' ) );
		$refunded_orders           = $this->total_orders( $range, array( 'status' => 'refunded' ) );
		$orders                    = array_merge( $completed_orders, $processing_orders, $partially_refunded_orders, $refunded_orders );

		return $this->count_items_by_date( $orders, $range );
	}

	/**
	 * Get refund count by date
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_refund_count_by_date( $range ) {
		$dates         = $this->resolve_dates( $range );
		$db            = new Database( 'refunds' );
		$refunds_table = $db->get_table();

		$query = $db->prepare(
			"SELECT DATE(created_at) as order_date, COUNT(*) as order_count
			FROM {$refunds_table}
			WHERE created_at >= %s AND created_at <= %s
			AND status IN ('processed', 'approved')
			GROUP BY DATE(created_at)",
			$dates['from'] . ' 00:00:00',
			$dates['to'] . ' 23:59:59'
		);

		$labels = $this->get_chart_labels( $range );
		$values = $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'order_count', 'int' );

		return $this->format_chart_data( $values, $labels );
	}

	/**
	 * Get order status breakdown
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function order_status( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'overview_order_status', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$statuses  = array_keys( $this->get_status_labels() );
				$breakdown = $this->build_status_breakdown( $range, array_combine( $statuses, $statuses ), $this->get_status_colors(), $this->get_status_labels() );

				return apply_filters(
					'easycommerce_reports_order_status',
					$breakdown,
					$range,
					$request
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Get order type breakdown (new vs returning orders)
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function order_type( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'overview_order_type', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$categories = $this->get_chart_labels( $range );
				$new_orders = $this->get_order_type_by_date( $range, 'new' );
				$returning  = $this->get_order_type_by_date( $range, 'returning' );

				$datasets = array(
					array(
						'id'    => __( 'New Orders', 'easycommerce' ),
						'data'  => $new_orders,
						'color' => '#7351FD',
					),
					array(
						'id'    => __( 'Renewal Orders', 'easycommerce' ),
						'data'  => $returning,
						'color' => '#A78BFA',
					),
				);

				return array(
					'categories' => $categories,
					'datasets'   => apply_filters( 'easycommerce_reports_order_type', $datasets, $range, $request ),
					'range'      => $range,
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Get order count by type (new vs returning) for chart data
	 *
	 * @param string $range The date range.
	 * @param string $type The order type ('new' or 'returning').
	 *
	 * @return array
	 */
	private function get_order_type_by_date( $range, $type = 'new' ) {
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();
		$dates        = $this->resolve_dates( $range );
		$from_date    = $dates['from'];
		$to_date      = $dates['to'];
		$labels       = $this->get_chart_labels( $range );

		$single_order_customers = 'new' === $type
			? '( SELECT customer_id FROM ' . $orders_table . ' GROUP BY customer_id HAVING COUNT(*) = 1 )'
			: '( SELECT customer_id FROM ' . $orders_table . ' GROUP BY customer_id HAVING COUNT(*) > 1 )';

		$query = $db->prepare(
			"SELECT DATE(created_at) as order_date, COUNT(*) as order_count
			FROM {$orders_table}
			WHERE created_at >= %s AND created_at <= %s
			AND status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			AND customer_id IN " . $single_order_customers . "
			GROUP BY DATE(created_at)",
			$from_date,
			$to_date . ' 23:59:59'
		);

		$values = $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'order_count', 'int' );

		return $this->format_chart_data_with_labels( $values, $labels );
	}

	/**
	 * Get top selling products
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function top_selling( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'overview_top_selling', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$top_products = $this->get_top_selling_products( $range, 5 );

				return apply_filters( 'easycommerce_reports_top_selling', $top_products, $range, $request );
			},
			self::CACHE_DURATION_DAY
		);

		$this->response_success(
			array(
				'products' => $result,
				'range'    => $range,
			)
		);
	}

	/**
	 * Get top selling products
	 *
	 * @param string $range The date range.
	 * @param int    $limit Number of products to return.
	 *
	 * @return array
	 */
	private function get_top_selling_products( $range, $limit = 5 ) {
		$dates             = $this->resolve_dates( $range );
		$from_date         = $dates['from'];
		$to_date           = $dates['to'];
		$db                = new Database( 'order_items' );
		$order_items_table = $db->get_table();
		$orders_table      = $db->get_wp_prefix() . 'ec_orders';

		$query = $db->prepare(
			"SELECT oi.product_id, SUM(oi.quantity) as total_sold,
				SUM(
					CASE WHEN (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) > 0
					THEN oi.rate * oi.quantity
					ELSE oi.subtotal END
				) as revenue
			FROM {$order_items_table} oi
			INNER JOIN {$orders_table} o ON oi.order_id = o.id
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			GROUP BY oi.product_id
			ORDER BY total_sold DESC
			LIMIT %d",
			$from_date,
			$to_date . ' 23:59:59',
			$limit
		);
		$items = $db->exec( $query, ARRAY_A );

		$top_products = array();
		$rank         = 1;

		foreach ( $items as $item ) {
			$product_id = (int) $item['product_id'];
			$product    = new Product( $product_id );

			if ( ! $product->exists() ) {
				continue;
			}

			$thumbnail = $product->get_thumbnail( 'thumbnail' );

			$top_products[] = array(
				'rank'       => $rank,
				'product_id' => $product_id,
				'name'       => $product->get_title(),
				'thumbnail'  => $thumbnail['url'] ?? '',
				'unit_sold'  => (int) $item['total_sold'],
				'revenue'    => round( (float) $item['revenue'], 2 ),
			);

			++$rank;
		}

		return $top_products;
	}

	/**
	 * Get product catalog stats
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function catalog_stats( $request ) {

		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'overview_catalog_stats', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$stats = $this->get_catalog_stats_data( $range );

				$catalog_data = array(
					array(
						'title' => __( 'Products in Catalog', 'easycommerce' ),
						'icon'  => 'single-cube',
						'value' => number_format( (int) $stats['total_products'] ),
					),
					array(
						'title' => __( 'Variations across products', 'easycommerce' ),
						'icon'  => 'tri-cubes',
						'value' => number_format( (int) $stats['total_variations'] ),
					),
					array(
						'title' => __( 'Average product price', 'easycommerce' ),
						'icon'  => 'revenue-icon',
						'value' => easycommerce_price( (float) $stats['avg_price'] ),
					),
					array(
						'title' => __( 'Inventory count', 'easycommerce' ),
						'icon'  => 'project-mrr',
						'value' => number_format( (int) $stats['total_inventory'] ),
					),
				);

				return apply_filters( 'easycommerce_reports_catalog_stats', $catalog_data, $range, $request );
			},
			self::CACHE_DURATION_DAY
		);

		$this->response_success(
			array(
				'stats' => $result,
				'range' => $range,
			)
		);
	}

	/**
	 * Get catalog stats data
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_catalog_stats_data( $range ) {
		$db               = new Database( 'product_variations' );
		$variations_table = $db->get_table();
		$posts_table      = $db->get_wp_prefix() . 'posts';

		if ( 'all-time' === $range ) {
			$query = $db->prepare(
				"SELECT 
					COUNT(DISTINCT pv.product_id) as total_products,
					COUNT(*) as total_variations,
					AVG(pv.price) as avg_price,
					SUM(COALESCE(pv.stock_quantity, 0)) as total_inventory
				FROM {$variations_table} pv
				INNER JOIN {$posts_table} p ON pv.product_id = p.ID
				WHERE p.post_type = %s AND p.post_status = 'publish'",
				'product'
			);
		} else {
			$dates     = $this->resolve_dates( $range );
			$from_date = $dates['from'];
			$to_date   = $dates['to'];

			$query = $db->prepare(
				"SELECT 
					COUNT(DISTINCT pv.product_id) as total_products,
					COUNT(*) as total_variations,
					AVG(pv.price) as avg_price,
					SUM(COALESCE(pv.stock_quantity, 0)) as total_inventory
				FROM {$variations_table} pv
				INNER JOIN {$posts_table} p ON pv.product_id = p.ID
				WHERE p.post_type = %s AND p.post_status = 'publish'
				AND p.post_date >= %s AND p.post_date <= %s",
				'product',
				$from_date,
				$to_date . ' 23:59:59'
			);
		}

		$result = $db->exec( $query, ARRAY_A );

		return $result[0] ?? array(
			'total_products'   => 0,
			'total_variations' => 0,
			'avg_price'        => 0,
			'total_inventory'  => 0,
		);
	}

	/**
	 * Get new vs returning customers chart data
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function customer_type( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'overview_customer_type', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$labels        = $this->get_chart_labels( $range );
				$new_customers = $this->get_customer_type_by_date( $range, 'new' );
				$returning     = $this->get_customer_type_by_date( $range, 'returning' );

				$datasets = array(
					array(
						'id'    => __( 'New', 'easycommerce' ),
						'color' => '#287CFF',
						'data'  => $this->format_chart_data_with_labels( $new_customers, $labels ),
					),
					array(
						'id'    => __( 'Returning', 'easycommerce' ),
						'color' => '#A451FD',
						'data'  => $this->format_chart_data_with_labels( $returning, $labels ),
					),
				);

				return array(
					'datasets' => apply_filters( 'easycommerce_reports_customer_type', $datasets, $range, $request ),
					'range'    => $range,
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Get customer type data by date
	 *
	 * @param string $range The date range.
	 * @param string $type The customer type ('new' or 'returning').
	 *
	 * @return array
	 */
	private function get_customer_type_by_date( $range, $type = 'new' ) {
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();
		$dates        = $this->resolve_dates( $range );
		$from_date    = $dates['from'];
		$to_date      = $dates['to'];

		$customer_filter = 'new' === $type
			? 'AND o.customer_id IN ( SELECT customer_id FROM ' . $orders_table . ' GROUP BY customer_id HAVING COUNT(*) = 1 )'
			: 'AND o.customer_id IN ( SELECT customer_id FROM ' . $orders_table . ' GROUP BY customer_id HAVING COUNT(*) > 1 )';

		$query = $db->prepare(
			"SELECT DATE(o.created_at) as order_date, COUNT(DISTINCT o.customer_id) as customer_count
			FROM {$orders_table} o
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			{$customer_filter}
			GROUP BY DATE(o.created_at)",
			$from_date,
			$to_date . ' 23:59:59'
		);

		return $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'customer_count', 'int' );
	}

	/**
	 * Get customer overview stats and top customers
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function customer_overview( $request ) {
		$range             = $request->get_param( 'range' ) ?: 'last-30';
		$comparison        = $request->get_param( 'comparison' );
		$comparison_range  = $comparison ?: $this->get_comparison_range( $range );
		$cache_key         = $this->generate_cache_key( 'overview_customer_overview', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $comparison_range, $request ) {
				$current_metrics  = $this->get_customer_metrics( $range );
				$previous_metrics = $this->get_customer_metrics( $comparison_range );
				$top_customers    = $this->get_top_customers( $range, 5 );

				$overview_data = array(
					array(
						'title'      => __( 'AOV Per Customer', 'easycommerce' ),
						'value'      => number_format( $current_metrics['aov_per_customer'], 0 ),
						'icon'       => 'aov-customer',
						'comparison' => $this->calculate_comparison(
							$current_metrics['aov_per_customer'],
							$previous_metrics['aov_per_customer']
						),
					),
					array(
						'title'      => __( 'Orders Per Customer', 'easycommerce' ),
						'value'      => number_format( $current_metrics['orders_per_customer'], 0 ),
						'icon'       => 'orders-customer',
						'comparison' => $this->calculate_comparison(
							$current_metrics['orders_per_customer'],
							$previous_metrics['orders_per_customer']
						),
					),
					array(
						'title'      => __( 'Items Per Customer', 'easycommerce' ),
						'value'      => number_format( $current_metrics['items_per_customer'], 1 ),
						'icon'       => 'items-customer',
						'comparison' => $this->calculate_comparison(
							$current_metrics['items_per_customer'],
							$previous_metrics['items_per_customer']
						),
					),
				);

				return array(
					'overview'        => apply_filters( 'easycommerce_reports_customer_overview', $overview_data, $range, $request ),
					'customers'       => $top_customers,
					'range'           => $range,
					'comparison_with' => $comparison_range,
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Get customer metrics for a date range
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_customer_metrics( $range ) {
		$db                = new Database( 'orders' );
		$orders_table      = $db->get_table();
		$order_items_db    = new Database( 'order_items' );
		$order_items_table = $order_items_db->get_table();
		$dates             = $this->resolve_dates( $range );
		$from_date         = $dates['from'];
		$to_date           = $dates['to'];

		$orders_query = $db->prepare(
			"SELECT
				COUNT(DISTINCT o.customer_id) as total_customers,
				COUNT(o.id) as total_orders,
				SUM(o.total) as total_revenue
			FROM {$orders_table} o
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')",
			$from_date,
			$to_date . ' 23:59:59'
		);
		$orders_result = $db->exec( $orders_query, ARRAY_A );

		$items_query = $db->prepare(
			"SELECT COALESCE(SUM(oi.quantity), 0) as total_items
			FROM {$order_items_table} oi
			INNER JOIN {$orders_table} o ON o.id = oi.order_id
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')",
			$from_date,
			$to_date . ' 23:59:59'
		);
		$items_result = $db->exec( $items_query, ARRAY_A );

		$total_customers = (int) ( $orders_result[0]['total_customers'] ?? 0 );
		$total_orders    = (int) ( $orders_result[0]['total_orders'] ?? 0 );
		$total_revenue   = (float) ( $orders_result[0]['total_revenue'] ?? 0 );
		$total_items     = (int) ( $items_result[0]['total_items'] ?? 0 );

		return array(
			'aov_per_customer'    => $total_customers > 0 ? $total_revenue / $total_customers : 0,
			'orders_per_customer' => $total_customers > 0 ? $total_orders / $total_customers : 0,
			'items_per_customer'  => $total_customers > 0 ? $total_items / $total_customers : 0,
		);
	}

	/**
	 * Get top customers by spending
	 *
	 * @param string $range The date range.
	 * @param int    $limit Number of customers to return.
	 *
	 * @return array
	 */
	private function get_top_customers( $range, $limit = 10 ) {
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();
		$users_table  = $db->get_wp_prefix() . 'users';
		$dates        = $this->resolve_dates( $range );
		$from_date    = $dates['from'];
		$to_date      = $dates['to'];

		$query   = $db->prepare(
			"SELECT 
				o.customer_id,
				u.display_name as customer_name,
				COUNT(o.id) as order_count,
				SUM(o.total) as total_spent
			FROM {$orders_table} o
			LEFT JOIN {$users_table} u ON o.customer_id = u.ID
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			AND o.customer_id > 0
			GROUP BY o.customer_id
			ORDER BY total_spent DESC
			LIMIT %d",
			$from_date,
			$to_date . ' 23:59:59',
			$limit
		);
		$results = $db->exec( $query, ARRAY_A );

		$customers = array();
		foreach ( $results as $index => $row ) {
			$customers[] = array(
				'id'     => (int) $row['customer_id'],
				'name'   => ! empty( $row['customer_name'] ) ? $row['customer_name'] : __( 'Guest', 'easycommerce' ),
				'orders' => (int) $row['order_count'],
				'spent'  => easycommerce_price( (float) $row['total_spent'] ),
			);
		}

		return $customers;
	}
}

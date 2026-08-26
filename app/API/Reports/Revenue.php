<?php

namespace EasyCommerce\API\Reports;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Order;
use WP_REST_Request;
use EasyCommerce\Models\Database;
/**
 * Reports Revenue API
 */
class Revenue extends Reports {

	/**
	 * Revenue stats - gross revenue, averages by customer, order, and product
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function stats( $request ) {
		$range             = $request->get_param( 'range' ) ?: 'last-30';
		$comparison        = $request->get_param( 'comparison' );
		$comparison_range  = $comparison ?: $this->get_comparison_range( $range );
		$cache_key         = $this->generate_cache_key( 'revenue_stats', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $comparison_range ) {
				$current_stats  = $this->calculate_revenue_stats( $range );
				$previous_stats = $this->calculate_revenue_stats( $comparison_range );

				$revenue_data = array(
					array(
						'title'      => __( 'Gross Revenue', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['gross_revenue'] ),
						'icon'       => 'revenue-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['gross_revenue'], $previous_stats['gross_revenue'] ),
						'tooltip'    => __( 'Total revenue before any refunds or deductions.', 'easycommerce' ),
					),
					array(
						'title'      => __( 'Net Revenue', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['net_revenue'] ),
						'icon'       => 'revenue-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['net_revenue'], $previous_stats['net_revenue'] ),
						'tooltip'    => __( 'Total revenue after any refunds or deductions.', 'easycommerce' ),
					),
					array(
						'title'      => __( 'Avg Revenue Per Customer', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['avg_per_customer'] ),
						'icon'       => 'customer-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['avg_per_customer'], $previous_stats['avg_per_customer'] ),
					),
					array(
						'title'      => __( 'Avg Revenue Per Order', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['avg_per_order'] ),
						'icon'       => 'orders-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['avg_per_order'], $previous_stats['avg_per_order'] ),
					),
					array(
						'title'      => __( 'Avg Revenue Per Product', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['avg_per_product'] ),
						'icon'       => 'product-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['avg_per_product'], $previous_stats['avg_per_product'] ),
					),
				);

				return array(
					'stats'           => apply_filters( 'easycommerce_reports_revenue', $revenue_data, $range ),
					'range'           => $range,
					'comparison_with' => $comparison_range,
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Revenue over time — Revenue Amount datasets (current + comparison)
	 *
	 * @param WP_REST_Request $request
	 */
	public function over_time( $request ) {
		$range      = $request->get_param( 'range' ) ?: 'last-30';
		$comparison = $request->get_param( 'comparison' ) ?: $this->get_comparison_range( $range );
		$cache_key  = $this->generate_cache_key( 'revenue_over_time', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $comparison, $request ) {
				$labels      = $this->get_time_series_labels( $range );
				$cur_series  = $this->build_revenue_series( $range, $labels );
				$prev_series = $this->build_revenue_series( $comparison, $labels );
				$cur_amount  = $this->format_chart_data( array_values( $cur_series ), $labels );
				$prev_amount = $this->format_chart_data( array_values( $prev_series ), $labels );

				$datasets = array(
					array(
						'id'    => __( 'Revenue', 'easycommerce' ),
						'color' => '#3B82F6',
						'fill'  => false,
						'data'  => $cur_amount,
					),
					array(
						'id'         => __( 'Previous Revenue', 'easycommerce' ),
						'color'      => '#BAD2FC',
						'fill'       => false,
						'data'       => $prev_amount,
						'borderDash' => array( 6, 4 ),
					),
				);

				return array(
					'range'           => $range,
					'comparison_with' => $comparison,
					'labels'          => $labels,
					'datasets'        => apply_filters( 'easycommerce_reports_revenue_over_time', $datasets, $range, $request ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Build a revenue amount array keyed by label.
	 *
	 * @param string   $range
	 * @param string[] $labels
	 * @return float[]
	 */
	private function build_revenue_series( $range, $labels ) {
		$dates        = $this->resolve_dates( $range );
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();

		$query = $db->prepare(
			"SELECT DATE(created_at) AS order_date, SUM(total) AS revenue
			FROM {$orders_table}
			WHERE created_at >= %s AND created_at <= %s
			AND status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			GROUP BY DATE(created_at)",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);

		return $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'revenue', 'float' );
	}

	/**
	 * Calculate revenue stats for a given range
	 *
	 * Mirrors calculate_stats() in Reports: only completed, processing,
	 * partially_refunded, and refunded orders count toward gross revenue,
	 * and refunds come from the Refund table (approved refunds only).
	 *
	 * @param string $range The date range.
	 * @return array
	 */
	protected function calculate_revenue_stats( $range ) {
		$completed_sales           = $this->total_sales( $range, array( 'status' => 'completed' ) );
		$processing_sales          = $this->total_sales( $range, array( 'status' => 'processing' ) );
		$partially_refunded_sales  = $this->total_sales( $range, array( 'status' => 'partially_refunded' ) );
		$refunded_sales            = $this->total_sales( $range, array( 'status' => 'refunded' ) );
		$completed_orders          = $this->total_orders( $range, array( 'status' => 'completed' ) );
		$processing_orders         = $this->total_orders( $range, array( 'status' => 'processing' ) );
		$partially_refunded_orders = $this->total_orders( $range, array( 'status' => 'partially_refunded' ) );
		$refunded_orders           = $this->total_orders( $range, array( 'status' => 'refunded' ) );
		$gross_revenue             = $completed_sales + $processing_sales + $partially_refunded_sales + $refunded_sales;
		$refunds                   = $this->total_refunds( $range );
		$orders                    = array_merge( $completed_orders, $processing_orders, $partially_refunded_orders, $refunded_orders );
		$total_orders              = count( $orders );
		$customer_ids              = array_filter( array_unique( array_column( $orders, 'customer' ) ) );
		$total_customers           = count( $customer_ids );
		$dates                     = $this->resolve_dates( $range );
		$db_orders                 = new Database( 'orders' );
		$orders_table              = $db_orders->get_table();
		$items_db                  = new Database( 'order_items' );
		$items_table               = $items_db->get_table();

		$items_query = $items_db->prepare(
			"SELECT COUNT(DISTINCT oi.product_id) AS total_products
			FROM {$items_table} oi
			INNER JOIN {$orders_table} o ON oi.order_id = o.id
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);

		$product_stats  = $items_db->exec( $items_query, ARRAY_A );
		$total_products = ! empty( $product_stats ) ? (int) $product_stats[0]['total_products'] : 0;

		return array(
			'gross_revenue'    => $gross_revenue,
			'net_revenue'      => $gross_revenue - $refunds,
			'avg_per_customer' => $total_customers > 0 ? $gross_revenue / $total_customers : 0,
			'avg_per_order'    => $total_orders > 0 ? $gross_revenue / $total_orders : 0,
			'avg_per_product'  => $total_products > 0 ? $gross_revenue / $total_products : 0,
			'total_orders'     => $total_orders,
			'total_customers'  => $total_customers,
			'total_products'   => $total_products,
		);
	}

	/**
	 * Top customers by revenue
	 *
	 * @param WP_REST_Request $request
	 */
	public function top_customers( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$dates     = $this->resolve_dates( $range );
		$date_from = $dates['from'];
		$date_to   = $dates['to'];
		$cache_key = $this->generate_cache_key( 'revenue_top_customers', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to ) {
				$db           = new Database( 'orders' );
				$orders_table = $db->get_table();

				$query = $db->prepare(
					"SELECT
						o.customer_id,
						COUNT(*) AS total_orders,
						SUM(o.total) AS revenue_earned,
						MIN(o.created_at) AS first_order_date,
						MAX(o.created_at) AS last_order_date
					FROM {$orders_table} o
					WHERE o.created_at >= %s AND o.created_at <= %s
					AND o.customer_id > 0
					AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
					GROUP BY o.customer_id
					ORDER BY revenue_earned DESC
					LIMIT %d",
					$date_from,
					$date_to . ' 23:59:59',
					self::TOP_CUSTOMERS_LIMIT
				);

				$customers_data = $db->exec( $query, ARRAY_A );
				$customers      = array();
				$rank           = 1;

				foreach ( $customers_data as $row ) {
					$customer_id = (int) $row['customer_id'];
					$customer    = get_userdata( $customer_id );
					$customers[] = array(
						'rank'             => $rank,
						'customer_id'      => $customer_id,
						'customer_name'    => $customer ? $customer->display_name : '',
						'customer_email'   => $customer ? $customer->user_email : '',
						'first_order_date' => $row['first_order_date'],
						'last_order_date'  => $row['last_order_date'],
						'total_orders'     => (int) $row['total_orders'],
						'revenue_earned'   => easycommerce_price( $row['revenue_earned'] ),
					);

					$rank++;
				}

				return array(
					'customers' => $customers,
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$result['range'] = $range;
		$result          = apply_filters( 'easycommerce_reports_revenue_top_customers', $result, $range, $request );

		$this->response_success( $result );
	}

	/**
	 * Revenue by country/state location
	 *
	 * Response (world):
	 * {
	 *   "range": "last-30",
	 *   "type": "world",
	 *   "data": { "US": 1000.00, "GB": 600.00 },
	 *   "country_names": { "US": "United States", ... },
	 *   "country_numeric": { "US": "840", ... }
	 * }
	 *
	 * Response (states):
	 * {
	 *   "range": "last-30",
	 *   "type": "states",
	 *   "country": "US",
	 *   "country_iso3": "USA",
	 *   "data": { "CA": 400.00, "NY": 300.00 },
	 *   "states_data": [...]
	 * }
	 *
	 * @param WP_REST_Request $request
	 */
	public function revenue_by_location( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$dates     = $this->resolve_dates( $range );
		$date_from = $dates['from'];
		$date_to   = $dates['to'];
		$cache_key = $this->generate_cache_key( 'revenue_by_location', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to ) {
				return $this->get_orders_by_location( $date_from, $date_to, 'sum', null, array( 'completed', 'processing', 'partially_refunded', 'refunded' ) );
			},
			self::CACHE_DURATION_HOUR
		);

		$result['range'] = $range;
		$result          = apply_filters( 'easycommerce_reports_revenue_by_location', $result, $range, $request );

		$this->response_success( $result );
	}
}

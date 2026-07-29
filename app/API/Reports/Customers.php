<?php

namespace EasyCommerce\API\Reports;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;
use EasyCommerce\Models\Customer as Customer_Model;
use WP_REST_Request;

/**
 * Reports Customer API
 */
class Customers extends Reports {

	/**
	 * Customer stats - total, new, repeat, orders per customer, LTV, AOV
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function stats( $request ) {
		$range             = $request->get_param( 'range' ) ?: 'last-30';
		$comparison        = $request->get_param( 'comparison' );
		$comparison_range  = $comparison ?: $this->get_comparison_range( $range );
		$cache_key         = $this->generate_cache_key( 'customer_stats', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $comparison_range ) {
				$current_stats  = $this->calculate_customer_stats( $range );
				$previous_stats = $this->calculate_customer_stats( $comparison_range );

				$customer_data = array(
					array(
						'title'      => __( 'Total Customers', 'easycommerce' ),
						'value'      => number_format( $current_stats['total_customers'] ),
						'icon'       => 'customer-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['total_customers'], $previous_stats['total_customers'] ),
					),
					array(
						'title'      => __( 'New Customers', 'easycommerce' ),
						'value'      => number_format( $current_stats['new_customers'] ),
						'icon'       => 'new-customer-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['new_customers'], $previous_stats['new_customers'] ),
					),
					array(
						'title'      => __( 'Repeat Customers', 'easycommerce' ),
						'value'      => number_format( $current_stats['repeat_customers'] ),
						'icon'       => 'repeat-customer-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['repeat_customers'], $previous_stats['repeat_customers'] ),
						'tooltip'    => __( 'Customers who have placed more than one order.', 'easycommerce' ),
					),
					array(
						'title'      => __( 'Orders Per Customer', 'easycommerce' ),
						'value'      => number_format( $current_stats['orders_per_customer'], 2 ),
						'icon'       => 'orders-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['orders_per_customer'], $previous_stats['orders_per_customer'] ),
					),
					array(
						'title'      => __( 'Customer LTV', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['ltv'] ),
						'icon'       => 'ltv-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['ltv'], $previous_stats['ltv'] ),
						'tooltip'    => __( 'Average Lifetime Value per customer.', 'easycommerce' ),
					),
					array(
						'title'      => __( 'Customer AOV', 'easycommerce' ),
						'value'      => easycommerce_price( $current_stats['aov'] ),
						'icon'       => 'aov-icon',
						'comparison' => $this->get_comparison_with_vibe( $current_stats['aov'], $previous_stats['aov'] ),
						'tooltip'    => __( 'Average Order Value per customer.', 'easycommerce' ),
					),
				);

				return array(
					'stats'           => apply_filters( 'easycommerce_reports_customer_stats', $customer_data, $range ),
					'range'           => $range,
					'comparison_with' => $comparison_range,
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Calculate customer stats for a given range
	 *
	 * @param string $range The date range.
	 * @return array
	 */
	protected function calculate_customer_stats( $range ) {
		$dates        = $this->resolve_dates( $range );
		$date_from    = $dates['from'];
		$date_to      = $dates['to'];
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();

		$query = $db->prepare(
			"SELECT
				COUNT(DISTINCT customer_id) AS total_customers,
				COUNT(*) AS total_orders,
				SUM(total) AS total_revenue
			FROM {$orders_table}
			WHERE created_at >= %s AND created_at <= %s
			AND customer_id > 0
			AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')",
			$date_from,
			$date_to . ' 23:59:59'
		);

		$order_stats = $db->exec( $query, ARRAY_A );
		$order_stats = ! empty( $order_stats ) ? $order_stats[0] : array(
			'total_customers' => 0,
			'total_orders'    => 0,
			'total_revenue'   => 0,
		);

		$total_customers = (int) ( $order_stats['total_customers'] ?? 0 );
		$total_orders    = (int) ( $order_stats['total_orders'] ?? 0 );
		$total_revenue   = (float) ( $order_stats['total_revenue'] ?? 0 );

		$new_customers_query = $db->prepare(
			"SELECT COUNT(*) AS new_customers
			FROM (
				SELECT customer_id, MIN(created_at) AS first_order
				FROM {$orders_table}
				WHERE customer_id > 0
				AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')
				GROUP BY customer_id
				HAVING first_order >= %s AND first_order <= %s
			) AS new_cust",
			$date_from,
			$date_to . ' 23:59:59'
		);

		$new_customers_result = $db->exec( $new_customers_query, ARRAY_A );
		$new_customers        = ! empty( $new_customers_result ) ? (int) ( $new_customers_result[0]['new_customers'] ?? 0 ) : 0;

		$repeat_customers = 0;
		if ( $total_customers > 0 ) {
			$repeat_query = $db->prepare(
				"SELECT COUNT(*) AS repeat_customers
				FROM (
					SELECT customer_id
					FROM {$orders_table}
					WHERE created_at >= %s AND created_at <= %s
					AND customer_id > 0
					AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')
					GROUP BY customer_id
					HAVING COUNT(*) > 1
				) AS repeat_cust",
				$date_from,
				$date_to . ' 23:59:59'
			);

			$repeat_result    = $db->exec( $repeat_query, ARRAY_A );
			$repeat_customers = ! empty( $repeat_result ) ? (int) ( $repeat_result[0]['repeat_customers'] ?? 0 ) : 0;
		}

		// Lifetime value is intentionally all-time (no date filter), so there is
		// no placeholder to prepare -- passing args to prepare() without one is a
		// _doing_it_wrong under WP 6.9. Use the static query directly.
		$ltv_query = "SELECT AVG(customer_revenue) AS avg_ltv
			FROM (
				SELECT customer_id, SUM(total) AS customer_revenue
				FROM {$orders_table}
				WHERE customer_id > 0
				AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')
				GROUP BY customer_id
			) AS customer_totals";

		$ltv_result          = $db->exec( $ltv_query, ARRAY_A );
		$ltv                 = ! empty( $ltv_result ) ? (float) ( $ltv_result[0]['avg_ltv'] ?? 0 ) : 0;
		$orders_per_customer = $total_customers > 0 ? $total_orders / $total_customers : 0;
		$aov                 = $total_orders > 0 ? $total_revenue / $total_orders : 0;

		return array(
			'total_customers'     => $total_customers,
			'new_customers'       => $new_customers,
			'repeat_customers'    => $repeat_customers,
			'orders_per_customer' => $orders_per_customer,
			'ltv'                 => $ltv,
			'aov'                 => $aov,
			'total_orders'        => $total_orders,
			'total_revenue'       => $total_revenue,
		);
	}

	/**
	 * Customers over time — New and Repeat customer datasets (current + comparison).
	 *
	 * @param WP_REST_Request $request
	 */
	public function over_time( $request ) {
		$range      = $request->get_param( 'range' ) ?: 'last-30';
		$comparison = $request->get_param( 'comparison' ) ?: $this->get_comparison_range( $range );
		$cache_key  = $this->generate_cache_key( 'customer_over_time', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $comparison, $request ) {
				$labels      = $this->get_time_series_labels( $range );
				$cur_new     = $this->format_chart_data( $this->build_new_customers_series( $range, $labels ), $labels );
				$cur_repeat  = $this->format_chart_data( $this->build_repeat_customers_series( $range, $labels ), $labels );
				$prev_new    = $this->format_chart_data( $this->build_new_customers_series( $comparison, $labels ), $labels );
				$prev_repeat = $this->format_chart_data( $this->build_repeat_customers_series( $comparison, $labels ), $labels );

				$datasets = array(
					array(
						'id'    => __( 'New Customers', 'easycommerce' ),
						'color' => '#3B82F6',
						'fill'  => false,
						'data'  => $cur_new,
					),
					array(
						'id'    => __( 'Repeat Customers', 'easycommerce' ),
						'color' => '#10B981',
						'fill'  => false,
						'data'  => $cur_repeat,
					),
					array(
						'id'         => __( 'Previous New Customers', 'easycommerce' ),
						'color'      => '#E9E4FF',
						'fill'       => false,
						'data'       => $prev_new,
						'borderDash' => array( 6, 4 ),
					),
					array(
						'id'         => __( 'Previous Repeat Customers', 'easycommerce' ),
						'color'      => '#E4DCFB',
						'fill'       => false,
						'data'       => $prev_repeat,
						'borderDash' => array( 6, 4 ),
					),
				);

				return array(
					'range'           => $range,
					'comparison_with'  => $comparison,
					'labels'          => $labels,
					'datasets'        => apply_filters( 'easycommerce_reports_customer_over_time', $datasets, $range, $request ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Customer heatmap - customers over time by hour and day
	 *
	 * Response:
	 * {
	 *   "range": "last-30",
	 *   "from": "2026-03-01",
	 *   "to": "2026-03-30",
	 *   "data": [
	 *     { "x": "12am", "y": "2026-03-01", "v": 0 },
	 *     { "x": "1am",  "y": "2026-03-01", "v": 0 },
	 *     ...
	 *     { "x": "11pm", "y": "2026-03-01", "v": 3 },
	 *     { "x": "0am",  "y": "2026-03-02", "v": 0 },
	 *     ...
	 *   ]
	 * }
	 *
	 * @param WP_REST_Request $request
	 */
	public function heatmap( $request ) {
		$range = $request->get_param( 'range' ) ?: 'last-30';

		if ( 'this-year' === $range ) {
			$range = 'last-30';
		}

		$dates     = $this->resolve_dates( $range );
		$date_from = $dates['from'];
		$date_to   = $dates['to'];
		$cache_key = $this->generate_cache_key( 'customer_heatmap', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to ) {
				$hour_labels  = $this->get_hour_labels();
				$map          = $this->build_heatmap_date_map( $date_from, $date_to );
				$db           = new Database( 'orders' );
				$orders_table = $db->get_table();

				$query = $db->prepare(
					"SELECT DATE(first_order) AS order_date,
					        HOUR(first_order) AS order_hour,
					        COUNT(*) AS customer_count
					FROM (
						SELECT customer_id, MIN(created_at) AS first_order
						FROM {$orders_table}
						WHERE created_at >= %s AND created_at <= %s
						AND customer_id > 0
						AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')
						GROUP BY customer_id
					) AS new_customers
					GROUP BY DATE(first_order), HOUR(first_order)",
					$date_from,
					$date_to . ' 23:59:59'
				);

				foreach ( $db->exec( $query, ARRAY_A ) as $row ) {
					$date = $row['order_date'];
					$hour = (int) $row['order_hour'];
					if ( isset( $map[ $date ] ) ) {
						$map[ $date ][ $hour ] = (int) $row['customer_count'];
					}
				}

				return array(
					'from'  => $date_from,
					'to'    => $date_to,
					'data'  => $this->build_heatmap_data( $map, $hour_labels ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$result['range'] = $range;
		$result          = apply_filters( 'easycommerce_reports_customer_heatmap', $result, $range, $request );

		$this->response_success( $result );
	}

	/**
	 * Build new customers series for a given range
	 *
	 * @param string   $range
	 * @param string[] $categories
	 * @return int[]
	 */
	private function build_new_customers_series( $range, $categories ) {
		$dates   = $this->resolve_dates( $range );
		$db      = new Database( 'orders' );
		$table   = $db->get_table();
		$query   = $db->prepare(
			"SELECT DATE(first_order) AS order_date, COUNT(*) AS customer_count
			FROM (
				SELECT customer_id, MIN(DATE(created_at)) AS first_order
				FROM {$table}
				WHERE customer_id > 0 AND created_at >= %s AND created_at <= %s
				AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')
				GROUP BY customer_id
			) AS new_customers
			GROUP BY DATE(first_order)",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);

		return $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'customer_count', 'int' );
	}

	/**
	 * Build repeat customers series for a given range
	 *
	 * @param string   $range
	 * @param string[] $categories
	 * @return int[]
	 */
	private function build_repeat_customers_series( $range, $categories ) {
		$dates   = $this->resolve_dates( $range );
		$db      = new Database( 'orders' );
		$table   = $db->get_table();
		$query   = $db->prepare(
			"SELECT DATE(order_date) AS order_date, COUNT(*) AS customer_count
			FROM (
				SELECT customer_id, DATE(created_at) AS order_date, COUNT(*) AS order_count
				FROM {$table}
				WHERE customer_id > 0 AND created_at >= %s AND created_at <= %s
				AND status IN ('completed', 'processing', 'refunded', 'partially_refunded')
				GROUP BY customer_id, DATE(created_at)
				HAVING COUNT(*) > 1
			) AS repeat_customers
			GROUP BY DATE(order_date)",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);

		return $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'customer_count', 'int' );
	}

	/**
	 * Customers by country/state location
	 *
	 * Response (world):
	 * {
	 *   "range": "last-30",
	 *   "type": "world",
	 *   "data": { "US": 100, "GB": 60 },
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
	 *   "data": { "CA": 40, "NY": 30 },
	 *   "states_data": [...]
	 * }
	 *
	 * @param WP_REST_Request $request
	 */
	public function customer_by_location( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$dates     = $this->resolve_dates( $range );
		$date_from = $dates['from'];
		$date_to   = $dates['to'];
		$cache_key = $this->generate_cache_key( 'customer_by_location', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to ) {
				return $this->get_orders_by_location( $date_from, $date_to, 'customers', null, array( 'completed', 'processing', 'refunded', 'partially_refunded' ) );
			},
			self::CACHE_DURATION_HOUR
		);

		$result['range'] = $range;
		$result          = apply_filters( 'easycommerce_reports_customer_by_location', $result, $range, $request );

		$this->response_success( $result );
	}

	/**
	 * Top customers
	 *
	 * @param WP_REST_Request $request
	 */
	public function top_customers( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$dates     = $this->resolve_dates( $range );
		$date_from = $dates['from'];
		$date_to   = $dates['to'];
		$cache_key = $this->generate_cache_key( 'customer_top_by_revenue', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to ) {
				return array(
					'customers' => $this->get_top_customers_by_revenue( $date_from, $date_to, 50 ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$result['range'] = $range;
		$result          = apply_filters( 'easycommerce_reports_customer_top_by_revenue', $result, $range, $request );

		$this->response_success( $result );
	}

	/**
	 * Get top customers by revenue for a date range.
	 *
	 * @param string $date_from Start date.
	 * @param string $date_to   End date.
	 * @param int    $limit     Number of customers to return.
	 * @return array
	 */
	private function get_top_customers_by_revenue( $date_from, $date_to, $limit = 50 ) {
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();
		$items_db     = new Database( 'order_items' );
		$items_table  = $items_db->get_table();

		$query = $db->prepare(
			"SELECT
				o.customer_id,
				COUNT(*) AS total_orders,
				SUM(o.total) AS total_purchase,
				(
					SELECT COUNT(DISTINCT oi.product_id)
					FROM {$items_table} oi
					INNER JOIN {$orders_table} o2 ON oi.order_id = o2.id
					WHERE o2.customer_id = o.customer_id
					AND o2.created_at >= %s AND o2.created_at <= %s
					AND o2.status IN ('completed', 'processing', 'refunded', 'partially_refunded')
				) AS product_count
			FROM {$orders_table} o
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.customer_id > 0
			AND o.status IN ('completed', 'processing', 'refunded', 'partially_refunded')
			GROUP BY o.customer_id
			ORDER BY total_purchase DESC
			LIMIT %d",
			$date_from,
			$date_to . ' 23:59:59',
			$date_from,
			$date_to . ' 23:59:59',
			$limit
		);

		$customers_data = $db->exec( $query, ARRAY_A );
		$customers      = array();
		$rank           = 1;

		foreach ( $customers_data as $row ) {
			$customer_id = (int) $row['customer_id'];
			$customer    = get_userdata( $customer_id );

			$customers[] = array(
				'rank'           => $rank,
				'customer_id'    => $customer_id,
				'name'           => $customer ? $customer->display_name : '',
				'total_orders'   => (int) $row['total_orders'],
				'product_count'  => (int) $row['product_count'],
				'total_purchase' => easycommerce_price( $row['total_purchase'] ),
			);

			$rank++;
		}

		return $customers;
	}
}

<?php

namespace EasyCommerce\API\Reports;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use WP_REST_Request;
use EasyCommerce\Models\Database;
/**
 * Reports Orders API
 */
class Orders extends Reports {

	/**
	 * Reports orders overview stats
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function stats( $request ) {
		$range             = $request->get_param( 'range' ) ?: 'last-30';
		$comparison        = $request->get_param( 'comparison' );
		$comparison_range  = $comparison ?: $this->get_comparison_range( $range );
		$cache_key         = $this->generate_cache_key( 'orders_stats', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $comparison_range, $request ) {
				$current_stats        = $this->calculate_stats( $range );
				$previous_stats       = $this->calculate_stats( $comparison_range );
				$current_refund_stats = $this->get_refund_stats( $range );
				$order_statuses       = array( 'completed', 'processing', 'partially_refunded', 'refunded' );
				$current_total        = 0;
				$previous_total       = 0;
				foreach ( $order_statuses as $status ) {
					$current_total  += count( $this->total_orders( $range, array( 'status' => $status ) ) );
					$previous_total += count( $this->total_orders( $comparison_range, array( 'status' => $status ) ) );
				}

				$overview_data = array(
					array(
						'title'           => __( 'Orders', 'easycommerce' ),
						'value'           => number_format( $current_total ),
						'secondary_value' => easycommerce_price( $current_stats['sales'] ),
						'icon'            => 'orders-icon',
						'comparison'      => $this->get_comparison_with_vibe( $current_total, $previous_total ),
						'tooltip'         => __( 'All orders except On Hold, Pending, Cancelled.', 'easycommerce' ),
					),
					array(
						'title'           => __( 'Completed Orders', 'easycommerce' ),
						'value'           => number_format( $current_stats['completed_orders'] ),
						'icon'            => 'sales-icon',
						'comparison'      => $this->get_comparison_with_vibe( $current_stats['completed_orders'], $previous_stats['completed_orders'] ),

					),
					array(
						'title'           => __( 'Refunds', 'easycommerce' ),
						'value'           => number_format( $current_refund_stats['refund_count'] ),
						'secondary_value' => easycommerce_price( $current_stats['refunds'] ),
						'icon'            => 'refunds-icon',
						'comparison'      => $this->get_refund_comparison( $current_stats['refunds'], $previous_stats['refunds'] ),
					),
					array(
						'title'           => __( 'Net Revenue', 'easycommerce' ),
						'value'           => easycommerce_price( $current_stats['net_revenue'] ),
						'icon'            => 'revenue-icon',
						'comparison'      => $this->get_comparison_with_vibe( $current_stats['net_revenue'], $previous_stats['net_revenue'] ),
						'tooltip'         => __( 'Total revenue after any refunds or deductions.', 'easycommerce' ),
					),
					array(
						'title'           => __( 'Pending Orders', 'easycommerce' ),
						'value'           => number_format( $current_stats['pending_orders'] ),
						'icon'            => 'pending-icon',
						'comparison'      => $this->get_refund_comparison( $current_stats['pending_orders'], $previous_stats['pending_orders'] ),
					),
				);

				return array(
					'stats'           => apply_filters( 'easycommerce_reports_orders', $overview_data, $range, $request ),
					'range'           => $range,
					'comparison_with' => $comparison_range,
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Orders over time — Order Count and Order Amount datasets (current + comparison).
	 *
	 * @param WP_REST_Request $request
	 */
	public function over_time( $request ) {
		$range      = $request->get_param( 'range' ) ?: 'last-30';
		$comparison = $request->get_param( 'comparison' ) ?: $this->get_comparison_range( $range );
		$cache_key  = $this->generate_cache_key( 'orders_over_time', $this->get_cache_params( $request ) );
		$result     = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $comparison, $request ) {
				$labels      = $this->get_time_series_labels( $range );
				$cur_count   = $this->format_chart_data( $this->build_count_series( $range, $labels ), $labels );
				$cur_amount  = $this->format_chart_data( $this->build_amount_series( $range, $labels ), $labels );
				$prev_count  = $this->format_chart_data( $this->build_count_series( $comparison, $labels ), $labels );
				$prev_amount = $this->format_chart_data( $this->build_amount_series( $comparison, $labels ), $labels );

				$datasets = array(
					array(
						'id'    => __( 'Order Count', 'easycommerce' ),
						'color' => '#3B82F6',
						'fill'  => false,
						'data'  => $cur_count,
					),
					array(
						'id'    => __( 'Order Amount', 'easycommerce' ),
						'color' => '#10B981',
						'fill'  => false,
						'data'  => $cur_amount,
					),
					array(
						'id'         => __( 'Previous Order Count', 'easycommerce' ),
						'color'      => '#99C0FF',
						'fill'       => false,
						'data'       => $prev_count,
						'borderDash' => array( 6, 4 ),
					),
					array(
						'id'         => __( 'Previous Order Amount', 'easycommerce' ),
						'color'      => '#10B9817A',
						'fill'       => false,
						'data'       => $prev_amount,
						'borderDash' => array( 6, 4 ),
					),
				);

				return array(
					'range'           => $range,
					'comparison_with' => $comparison,
					'labels'          => $labels,
					'datasets'        => apply_filters( 'easycommerce_reports_orders_over_time', $datasets, $range, $request ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Build a flat order-count array (int per category bucket).
	 *
	 * @param string   $range
	 * @param string[] $categories
	 * @return int[]
	 */
	private function build_count_series( $range, $categories ) {
		$dates        = $this->resolve_dates( $range );
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();

		$query = $db->prepare(
			"SELECT DATE(created_at) AS order_date, COUNT(*) AS order_count
			FROM {$orders_table}
			WHERE created_at >= %s AND created_at <= %s
			AND status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			GROUP BY DATE(created_at)",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);

		return $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'order_count', 'int' );
	}

	/**
	 * Build a flat order-amount array (float per category bucket).
	 *
	 * @param string   $range
	 * @param string[] $categories
	 * @return float[]
	 */
	private function build_amount_series( $range, $categories ) {
		$dates        = $this->resolve_dates( $range );
		$db           = new Database( 'orders' );
		$orders_table = $db->get_table();

		$query = $db->prepare(
			"SELECT DATE(created_at) AS order_date, SUM(total) AS order_amount
			FROM {$orders_table}
			WHERE created_at >= %s AND created_at <= %s
			AND status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			GROUP BY DATE(created_at)",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);

		return $this->map_to_date_series( $db->exec( $query, ARRAY_A ), $range, 'order_amount', 'float' );
	}

	/**
	 * Customer order frequency report.
	 *
	 * @param WP_REST_Request $request
	 */
	public function order_frequency( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'orders_order_frequency', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range ) {
				$db           = new Database( 'orders' );
				$orders_table = $db->get_table();
				$dates        = $this->resolve_dates( $range );
				$from_date    = $dates['from'];
				$to_date      = $dates['to'];

				$query = $db->prepare(
					"SELECT
						SUM(CASE WHEN order_count = 1 THEN 1 ELSE 0 END) AS ordered_once,
						SUM(CASE WHEN order_count = 2 THEN 1 ELSE 0 END) AS ordered_twice,
						SUM(CASE WHEN order_count >= 3 THEN 1 ELSE 0 END) AS ordered_three_plus,
						COUNT(*) AS total_customers,
						SUM(order_count) AS total_orders
					FROM (
						SELECT customer_id, COUNT(*) AS order_count
						FROM {$orders_table}
						WHERE created_at >= %s AND created_at <= %s
						AND customer_id > 0
						AND status IN ('completed', 'processing', 'partially_refunded', 'refunded')
						GROUP BY customer_id
					) AS customer_orders",
					$from_date,
					$to_date . ' 23:59:59'
				);

				$row = $db->exec( $query, ARRAY_A );
				$row = ! empty( $row ) ? $row[0] : array(
					'ordered_once'       => 0,
					'ordered_twice'      => 0,
					'ordered_three_plus' => 0,
					'total_customers'    => 0,
					'total_orders'       => 0,
				);

				return array(
					'range'                   => $range,
					'total_customers'         => (int) $row['total_customers'],
					'avg_orders_per_customer' => (int) $row['total_customers'] > 0 ? round( (int) $row['total_orders'] / (int) $row['total_customers'] ) : 0,
					'ordered_once'            => (int) $row['ordered_once'],
					'ordered_twice'           => (int) $row['ordered_twice'],
					'ordered_three_plus'      => (int) $row['ordered_three_plus'],
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Orders by status
	 *
	 * @param WP_REST_Request $request
	 */
	public function status_breakdown( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'orders_status_breakdown', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $request ) {
				$breakdown = $this->build_status_breakdown(
					$range,
					easycommerce_order_statuses(),
					$this->get_status_colors(),
					$this->get_status_labels()
				);

				return array(
					'range'     => $range,
					'breakdown' => apply_filters( 'easycommerce_reports_order_status_breakdown', $breakdown, $range, $request ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Get fulfillment status color map.
	 *
	 * @return array
	 */
	private function get_fulfill_colors() {
		return array(
			'unfulfilled'         => '#FF1F78',
			'fulfilled'           => '#00A900',
			'partially_fulfilled' => '#FFB310',
			'shipped'             => '#1495FF',
			'delivered'           => '#555DFF',
			'returned'            => '#FF001F',
		);
	}

	/**
	 * Orders by fulfillment status
	 *
	 * @param WP_REST_Request $request
	 */
	public function fulfillment_breakdown( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'orders_fulfillment_breakdown', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $range, $request ) {
				$breakdown = $this->build_status_breakdown(
					$range,
					easycommerce_fulfill_statuses(),
					$this->get_fulfill_colors(),
					easycommerce_fulfill_statuses(),
					'fulfill_status'
				);

				return array(
					'range'     => $range,
					'breakdown' => apply_filters( 'easycommerce_reports_fulfillment_breakdown', $breakdown, $range, $request ),
				);
			},
			self::CACHE_DURATION_HOUR
		);

		$this->response_success( $result );
	}

	/**
	 * Orders heatmap — hourly order count per date.
	 * Each date has 24 hourly buckets (0–23).
	 *
	 * Response:
	 * {
	 *   "range": "last-30",
	 *   "from": "2026-03-01",
	 *   "to": "2026-03-30",
	 *   "data": [
	 *     { "x": "0am",  "y": "2026-03-01", "v": 5 },
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
		$cache_key = $this->generate_cache_key( 'orders_heatmap', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to ) {
				$hour_labels  = $this->get_hour_labels();
				$map          = $this->build_heatmap_date_map( $date_from, $date_to );
				$db           = new Database( 'orders' );
				$orders_table = $db->get_table();

				$query = $db->prepare(
					"SELECT DATE(created_at) AS order_date,
					        HOUR(created_at) AS order_hour,
					        COUNT(*) AS order_count
					FROM {$orders_table}
					WHERE created_at >= %s AND created_at <= %s
					AND status IN ('completed', 'processing', 'partially_refunded', 'refunded')
					GROUP BY DATE(created_at), HOUR(created_at)",
					$date_from,
					$date_to . ' 23:59:59'
				);

				foreach ( $db->exec( $query, ARRAY_A ) as $row ) {
					$date = $row['order_date'];
					$hour = (int) $row['order_hour'];
					if ( isset( $map[ $date ] ) ) {
						$map[ $date ][ $hour ] = (int) $row['order_count'];
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
		$result          = apply_filters( 'easycommerce_reports_orders_heatmap', $result, $range, $request );

		$this->response_success( $result );
	}

	/**
	 * Orders by country
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
	public function order_by_location( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$dates     = $this->resolve_dates( $range );
		$date_from = $dates['from'];
		$date_to   = $dates['to'];
		$cache_key = $this->generate_cache_key( 'orders_order_by_location', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to ) {
				return $this->get_orders_by_location( $date_from, $date_to, 'count', null, array( 'completed', 'processing', 'partially_refunded', 'refunded' ) );
			},
			self::CACHE_DURATION_HOUR
		);

		$result['range'] = $range;
		$result          = apply_filters( 'easycommerce_reports_order_by_country', $result, $range, $request );

		$this->response_success( $result );
	}
}

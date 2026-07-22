<?php

namespace EasyCommerce\API\Reports;

defined( 'ABSPATH' ) || exit;

use DateTime;
use EasyCommerce\Abstracts\API;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Location;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Refund;
use EasyCommerce\Traits\Cache;
use WP_REST_Request;

/**
 * Reports Base API
 */
class Reports extends API {

	use Cache;

	const CACHE_DURATION_HOUR  = 3600;
	const CACHE_DURATION_DAY   = 86400;

	/**
	 * Returns a date key based on the given timestamp and range.
	 *
	 * @param int    $timestamp The timestamp to get the date key for.
	 * @param string $range The range to get the date key for.
	 *
	 * @return string The date key.
	 */
	public function get_date_key( $timestamp, $range ) {
		if ( strpos( $range, ',' ) !== false ) {
			return wp_date( 'd M', $timestamp );
		}

		if ( in_array( $range, array( 'this-year', 'last-year' ) ) ) {
			return wp_date( 'M', $timestamp );
		}

		if ( in_array( $range, array( 'this-week', 'last-week', 'last-7', 'today', 'yesterday' ) ) ) {
			return wp_date( 'D', $timestamp );
		}

		$day = wp_date( 'j', $timestamp );

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

	public function get_date_range( $range = 'all' ) {
		$to_date   = current_time( 'Y-m-d' );
		$from_date = '1970-01-01';
		switch ( $range ) {
			case 'today':
				$from_date = $to_date;
				$to_date   = $to_date;
				break;

			case 'yesterday':
				$from_date = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
				$to_date   = $from_date;
				break;

			case 'this-week':
				$start_of_week    = get_option( 'start_of_week' );
				$current_day      = gmdate( 'w' );
				$days_to_subtract = ( $current_day - $start_of_week + 7 ) % 7;
				$from_date        = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days" ) );
				$to_date          = gmdate( 'Y-m-d', strtotime( $from_date . ' +6 days' ) );
				break;

			case 'last-week':
				$start_of_week    = get_option( 'start_of_week' );
				$current_day      = gmdate( 'w' );
				$days_to_subtract = ( $current_day - $start_of_week + 7 ) % 7 + 7;
				$from_date        = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days" ) );
				$to_date          = gmdate( 'Y-m-d', strtotime( $from_date . ' +6 days' ) );
				break;

			case 'last-7':
				$from_date = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
				$to_date   = gmdate( 'Y-m-d' );
				break;

			case 'this-month':
				$from_date = gmdate( 'Y-m-01' );
				$to_date   = gmdate( 'Y-m-t' );
				break;

			case 'last-month':
				$from_date = gmdate( 'Y-m-01', strtotime( '-1 month' ) );
				$to_date   = gmdate( 'Y-m-t', strtotime( '-1 month' ) );

				break;

			case 'last-30':
				$from_date = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
				$to_date   = gmdate( 'Y-m-d' );
				break;

			case 'prev-7':
				$from_date = gmdate( 'Y-m-d', strtotime( '-14 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-8 days' ) );
				break;

			case 'prev-30':
				$from_date = gmdate( 'Y-m-d', strtotime( '-60 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-31 days' ) );
				break;

			case 'last-month-7':
				$from_date = gmdate( 'Y-m-d', strtotime( 'first day of last month +23 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( 'first day of last month +29 days' ) );
				break;

			case 'last-year-7':
				$from_date = gmdate( 'Y-m-d', strtotime( '-37 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-31 days' ) );
				break;

			case 'last-month-week':
				$start_of_week   = get_option( 'start_of_week' ) - 1;
				$week_start      = gmdate( 'Y-m-d', strtotime( 'last week last month +' . $start_of_week . ' days' ) );
				$from_date       = $week_start;
				$to_date         = gmdate( 'Y-m-d', strtotime( $week_start . ' +6 days' ) );
				break;

			case 'last-year-week':
				$start_of_week   = get_option( 'start_of_week' );
				$current_day     = gmdate( 'w' );
				$days_to_subtract = ( $current_day - $start_of_week + 7 ) % 7;
				$from_date       = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days -52 weeks" ) );
				$to_date         = gmdate( 'Y-m-d', strtotime( $from_date . ' +6 days' ) );
				break;

			case 'last-year-month':
				$from_date = gmdate( 'Y-m-01', strtotime( '-1 year' ) );
				$to_date   = gmdate( 'Y-m-t', strtotime( '-1 year' ) );
				break;

			case 'last-year-30':
				$from_date = gmdate( 'Y-m-d', strtotime( '-365 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-336 days' ) );
				break;

			case 'this-year':
				$from_date = gmdate( 'Y-01-01' );
				$to_date   = gmdate( 'Y-12-31' );
				break;

			case 'last-year':
				$from_date = gmdate( 'Y-01-01', strtotime( '-1 year' ) );
				$to_date   = gmdate( 'Y-12-31', strtotime( '-1 year' ) );
				break;

			case 'all':
				$from_date = '1970-01-01';
		}

		return array( $from_date, $to_date );
	}

	/**
	 * Resolve from/to date strings for a range.
	 *
	 * @param string $range
	 * @return array { from: string, to: string }
	 */
	public function resolve_dates( $range ) {
		$dates      = $this->get_date_range( $range );
		$date_items = explode( ',', $range );

		return array(
			'from' => count( $date_items ) === 2 ? trim( $date_items[0] ) : $dates[0],
			'to'   => count( $date_items ) === 2 ? trim( $date_items[1] ) : $dates[1],
		);
	}

	/**
	 * Build the ordered list of date labels for a given range.
	 *
	 * @param string $range
	 * @return string[]
	 */
	public function get_time_series_labels( $range ) {
		$labels = array();

		if ( in_array( $range, array( 'this-week', 'last-week', 'last-month-week', 'last-year-week' ), true ) ) {
			$start = (int) get_option( 'start_of_week' );
			for ( $i = 0; $i < 7; $i++ ) {
				$labels[] = wp_date( 'D', strtotime( 'Sunday +' . ( ( $start + $i ) % 7 ) . ' days' ) );
			}
		} elseif ( in_array( $range, array( 'last-7', 'prev-7', 'last-month-7', 'last-year-7' ), true ) ) {
			if ( strpos( $range, ',' ) !== false ) {
				list( $from, $to ) = explode( ',', $range );
				$period            = new \DatePeriod(
					new \DateTime( trim( $from ) ),
					new \DateInterval( 'P1D' ),
					( new \DateTime( trim( $to ) ) )->modify( '+1 day' )
				);
				foreach ( $period as $date ) {
					$labels[] = wp_date( 'd M', $date->getTimestamp() );
				}
			} else {
				$days = 7;
				if ( 'last-7' === $range ) {
					for ( $i = $days - 1; $i >= 0; $i-- ) {
						$labels[] = wp_date( 'D', strtotime( "-{$i} days" ) );
					}
				} elseif ( 'prev-7' === $range ) {
					for ( $i = 13; $i >= 7; $i-- ) {
						$labels[] = wp_date( 'D', strtotime( "-{$i} days" ) );
					}
				} elseif ( 'last-month-7' === $range ) {
					$last_month = new \DateTime( 'first day of last month' );
					$last_month->modify( '+23 days' );
					for ( $i = 0; $i < $days; $i++ ) {
						$labels[] = wp_date( 'd M', $last_month->getTimestamp() );
						$last_month->modify( '+1 day' );
					}
				} elseif ( 'last-year-7' === $range ) {
					for ( $i = 36; $i >= 30; $i-- ) {
						$labels[] = wp_date( 'd M', strtotime( "-{$i} days" ) );
					}
				}
			}
		} elseif ( in_array( $range, array( 'this-month', 'last-month', 'last-year-month' ), true ) ) {
			if ( 'this-month' === $range ) {
				$date = new \DateTime();
			} elseif ( 'last-month' === $range ) {
				$date = new \DateTime( 'first day of last month' );
			} else {
				$date = new \DateTime( 'first day of january last year' );
			}
			$days_in_month = (int) $date->format( 't' );
			for ( $day = 1; $day <= $days_in_month; $day++ ) {
				$labels[] = $this->ordinal( $day );
			}
		} elseif ( in_array( $range, array( 'last-30', 'prev-30', 'last-year-30' ), true ) ) {
			if ( 'last-30' === $range ) {
				$end   = new \DateTime();
				$start = ( clone $end )->modify( '-29 days' );
			} elseif ( 'prev-30' === $range ) {
				$end   = new \DateTime( '-31 days' );
				$start = ( clone $end )->modify( '-29 days' );
			} else {
				$end   = new \DateTime( '-336 days' );
				$start = ( clone $end )->modify( '-29 days' );
			}
			$period = new \DatePeriod( $start, new \DateInterval( 'P1D' ), $end->modify( '+1 day' ) );
			foreach ( $period as $date ) {
				$labels[] = $this->ordinal( (int) $date->format( 'j' ) );
			}
		} elseif ( in_array( $range, array( 'this-year', 'last-year' ), true ) ) {
			for ( $month = 1; $month <= 12; $month++ ) {
				$labels[] = wp_date( 'M', mktime( 0, 0, 0, $month, 1 ) );
			}
		} elseif ( strpos( $range, ',' ) !== false ) {
			list( $from, $to ) = explode( ',', $range );
			$period            = new \DatePeriod(
				new \DateTime( trim( $from ) ),
				new \DateInterval( 'P1D' ),
				( new \DateTime( trim( $to ) ) )->modify( '+1 day' )
			);
			foreach ( $period as $date ) {
				$labels[] = $date->format( 'd M' );
			}
		} elseif ( 'today' === $range ) {
			$labels = array( wp_date( 'D' ) );
		} elseif ( 'yesterday' === $range ) {
			$labels = array( wp_date( 'D', strtotime( '-1 day' ) ) );
		} else {
			$labels = array( '' );
		}

		return $labels;
	}

	/**
	 * Ordinal suffix for a day number — 1 → "1st", 22 → "22nd", etc.
	 *
	 * @param int $day
	 * @return string
	 */
	private function ordinal( $day ) {
		if ( $day % 10 === 1 && $day % 100 !== 11 ) {
			return $day . 'st';
		} elseif ( $day % 10 === 2 && $day % 100 !== 12 ) {
			return $day . 'nd';
		} elseif ( $day % 10 === 3 && $day % 100 !== 13 ) {
			return $day . 'rd';
		}
		return $day . 'th';
	}

	/**
	 * Total orders
	 *
	 * @param string $range The date range to query.
	 * @param array  $args  Optional args passed to Order::list() (e.g. status, product_id, customer_id, meta_query, etc.)
	 *
	 * @return array
	 */
	public function total_orders( $range = 'this-month', $args = array() ) {
		$dates      = $this->get_date_range( $range );
		$from_date  = $dates[0];
		$to_date    = $dates[1];
		$date_items = explode( ',', $range );

		if ( count( $date_items ) == 2 ) {
			$from_date = $date_items[0];
			$to_date   = $date_items[1];
		}

		$args = array_merge( $args, array(
			'from_date' => $from_date,
			'to_date'   => $to_date,
			'per_page'  => -1,
		) );

		$orders = Order::list( $args );

		return $orders['orders'];
	}

	/**
	 * Total sales
	 *
	 * @param string $range The date range to query.
	 * @param array  $args  Optional args passed to Order::list() (e.g. status, product_id, customer_id, meta_query, etc.)
	 *
	 * @return float
	 */
	public function total_sales( $range = 'this-month', $args = array() ) {

		$orders = $this->total_orders( $range, $args );

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
	 * Get total refund amount from wp_ec_refunds table.
	 *
	 * Only counts approved/processed refunds within the date range.
	 *
	 * @param string $range The date range to query.
	 * @return float
	 */
	public function total_refunds( $range = 'this-month' ) {
		$dates         = $this->resolve_dates( $range );
		$db            = new Database( 'refunds' );
		$refunds_table = $db->get_table();

		$query  = $db->prepare(
			"SELECT SUM(amount) AS total_refund
			FROM {$refunds_table}
			WHERE status IN ('approved', 'processed')
			AND created_at >= %s AND created_at <= %s",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);
		$result = $db->exec( $query, ARRAY_A );

		return (float) ( $result[0]['total_refund'] ?? 0 );
	}

	/**
	 * Get refund count and total amount from wp_ec_refunds table.
	 *
	 * Used when both the count and the monetary amount are needed.
	 *
	 * @param string   $range      The date range to query.
	 * @param int|null $product_id Optional product ID to scope to a single product.
	 * @param bool     $all_time   Whether to skip date filtering entirely.
	 * @return array { refund_count: int, refund_amount: float }
	 */
	protected function get_refund_stats( $range, $product_id = null, $all_time = false ) {
		$db            = new Database( 'refunds' );
		$refunds_table = $db->get_table();

		if ( $product_id ) {
			$items_db          = new Database( 'order_items' );
			$order_items_table = $items_db->get_table();

			if ( $all_time ) {
				$query = $db->prepare(
					"SELECT COUNT(r.id) AS refund_count, SUM(r.amount) AS refund_amount
					FROM {$refunds_table} r
					WHERE r.order_id IN (
						SELECT DISTINCT oi.order_id FROM {$order_items_table} oi
						WHERE oi.product_id = %d
					)
					AND r.status IN ('approved', 'processed')",
					$product_id
				);
			} else {
				$dates = $this->resolve_dates( $range );
				$query = $db->prepare(
					"SELECT COUNT(r.id) AS refund_count, SUM(r.amount) AS refund_amount
					FROM {$refunds_table} r
					WHERE r.order_id IN (
						SELECT DISTINCT oi.order_id FROM {$order_items_table} oi
						WHERE oi.product_id = %d
					)
					AND r.status IN ('approved', 'processed')
					AND r.created_at >= %s AND r.created_at <= %s",
					$product_id,
					$dates['from'],
					$dates['to'] . ' 23:59:59'
				);
			}
		} else {
			if ( $all_time ) {
				$query = "SELECT COUNT(id) AS refund_count, SUM(amount) AS refund_amount
					FROM {$refunds_table}
					WHERE status IN ('approved', 'processed')";
			} else {
				$dates = $this->resolve_dates( $range );
				$query = $db->prepare(
					"SELECT COUNT(id) AS refund_count, SUM(amount) AS refund_amount
					FROM {$refunds_table}
					WHERE status IN ('approved', 'processed')
					AND created_at >= %s AND created_at <= %s",
					$dates['from'],
					$dates['to'] . ' 23:59:59'
				);
			}
		}

		$result = $db->exec( $query, ARRAY_A );

		return array(
			'refund_count'  => (int) ( $result[0]['refund_count'] ?? 0 ),
			'refund_amount' => (float) ( $result[0]['refund_amount'] ?? 0 ),
		);
	}

	/**
	 * Get comparison range for previous period
	 *
	 * @param string $range The current range.
	 * @return string The comparison range.
	 */
	protected function get_comparison_range( $range ) {
		switch ( $range ) {
			case 'last-7':
				return 'prev-7';
			case 'last-30':
				return 'prev-30';
			case 'this-week':
				return 'last-week';
			case 'this-month':
				return 'last-month';
			case 'this-year':
				return 'last-year';
			default:
				if ( strpos( $range, ',' ) !== false ) {
					$dates     = explode( ',', $range );
					$start     = new DateTime( trim( $dates[0] ) );
					$end       = new DateTime( trim( $dates[1] ) );
					$days      = $start->diff( $end )->days;
					$new_start = clone $start;
					$new_start = $new_start->modify( "-{$days} days" );
					$new_end   = clone $start;
					$new_end   = $new_end->modify( '-1 day' );
					return $new_start->format( 'Y-m-d' ) . ',' . $new_end->format( 'Y-m-d' );
				}
				return 'last-30';
		}
	}

	/**
	 * Calculate stats for a given range
	 *
	 * @param string $range The date range.
	 * @return array
	 */
	protected function calculate_stats( $range ) {
		$completed_sales           = $this->total_sales( $range, array( 'status' => 'completed' ) );
		$processing_sales          = $this->total_sales( $range, array( 'status' => 'processing' ) );
		$partially_refunded_sales  = $this->total_sales( $range, array( 'status' => 'partially_refunded' ) );
		$refunded_sales            = $this->total_sales( $range, array( 'status' => 'refunded' ) );
		$completed_orders          = $this->total_orders( $range, array( 'status' => 'completed' ) );
		$processing_orders         = $this->total_orders( $range, array( 'status' => 'processing' ) );
		$partially_refunded_orders = $this->total_orders( $range, array( 'status' => 'partially_refunded' ) );
		$refunded_orders           = $this->total_orders( $range, array( 'status' => 'refunded' ) );
		$pending_orders            = $this->total_orders( $range, array( 'status' => 'pending' ) );
		$refunds                   = $this->total_refunds( $range );
		$sales                     = $completed_sales + $processing_sales + $partially_refunded_sales + $refunded_sales;
		$orders                    = array_merge( $completed_orders, $processing_orders, $partially_refunded_orders, $refunded_orders );

		return array(
			'orders'            => count( $orders ),
			'sales'             => $sales,
			'refunds'           => $refunds,
			'net_revenue'       => $sales - $refunds,
			'completed_orders'  => count( $completed_orders ),
			'pending_orders'    => count( $pending_orders ),
		);
	}

	/**
	 * Calculate comparison percentage and type
	 *
	 * @param float $current Current period value.
	 * @param float $previous Previous period value.
	 * @return array
	 */
	protected function calculate_comparison( $current, $previous ) {
		if ( $previous == 0 ) {
			if ( $current == 0 ) {
				return array(
					'previous_value' => 0,
					'value'          => '0%',
					'type'           => 'increment',
				);
			}
			return array(
				'previous_value' => 0,
				'value'          => '100%',
				'type'           => 'increment',
			);
		}

		$change = ( ( $current - $previous ) / $previous ) * 100;
		$change = round( $change, 1 );

		return array(
			'previous_value' => $previous,
			'value'          => abs( $change ) . '%',
			'type'           => $change >= 0 ? 'increment' : 'decrement',
		);
	}

	/**
	 * Get comparison with vibe for positive metrics (increment is good)
	 *
	 * @param float $current Current period value.
	 * @param float $previous Previous period value.
	 * @return array
	 */
	public function get_comparison_with_vibe( $current, $previous ) {
		$comparison = $this->calculate_comparison( $current, $previous );

		if ( 'increment' === $comparison['type'] ) {
			$comparison['vibe'] = 'positive';
		}

		return $comparison;
	}

	/**
	 * Get comparison with vibe for negative metrics (decrement is good)
	 *
	 * @param float $current Current period value.
	 * @param float $previous Previous period value.
	 * @return array
	 */
	public function get_refund_comparison( $current, $previous ) {
		$comparison = $this->calculate_comparison( $current, $previous );

		if ( 'decrement' === $comparison['type'] ) {
			$comparison['vibe'] = 'positive';
		} else {
			$comparison['vibe'] = 'negative';
		}

		return $comparison;
	}

	/**
	 * Generate a cache key for a report endpoint.
	 *
	 * @param string $method The method name.
	 * @param array  $params Request parameters.
	 * @return string The cache key.
	 */
	protected function generate_cache_key( $method, $params = array() ) {
		$key_parts = array( 'report', $method );

		if ( ! empty( $params['range'] ) ) {
			$key_parts[] = 'range_' . sanitize_key( $params['range'] );
		}

		if ( ! empty( $params['comparison'] ) ) {
			$key_parts[] = 'comp_' . sanitize_key( $params['comparison'] );
		}

		if ( ! empty( $params['product_id'] ) ) {
			$key_parts[] = 'pid_' . absint( $params['product_id'] );
		}

		return implode( '_', $key_parts );
	}

	/**
	 * Try to get cached response or execute callback and cache the result.
	 *
	 * @param string   $cache_key   The cache key.
	 * @param callable $callback    The callback to execute if cache miss.
	 * @param int      $expiration  Cache duration in seconds.
	 * @return mixed
	 */
	protected function get_cached_or_set( $cache_key, $callback, $expiration = self::CACHE_DURATION_HOUR ) {
		$cached = $this->get_cache( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = call_user_func( $callback );

		$this->set_cache( $cache_key, $result, $expiration );

		return $result;
	}

	/**
	 * Get request params for caching.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return array
	 */
	protected function get_cache_params( $request ) {
		return array(
			'range'      => $request->get_param( 'range' ),
			'comparison' => $request->get_param( 'comparison' ),
			'product_id' => $request->get_param( 'product_id' ),
		);
	}

	/**
	 * Get all cache keys used for reports.
	 *
	 * @return array List of cache key prefixes (with 'report_' prefix).
	 */
	public static function get_cache_keys() {
		$keys = array_merge( self::get_order_cache_keys(), self::get_product_cache_keys() );
		return array_map( function( $key ) {
			return 'report_' . $key;
		}, $keys );
	}

	/**
	 * Get all possible date ranges.
	 *
	 * @return array List of all range values.
	 */
	public static function get_all_ranges() {
		return array(
			'today',
			'yesterday',
			'this-week',
			'last-week',
			'last-7',
			'this-month',
			'last-month',
			'last-30',
			'this-year',
			'last-year',
			'all-time',
		);
	}

	/**
	 * Get all possible comparison ranges.
	 *
	 * @return array List of all comparison range values.
	 */
	public static function get_all_comparison_ranges() {
		return array(
			'prev-7',
			'prev-30',
			'last-week',
			'last-month',
			'last-year',
		);
	}

	/**
	 * Get all possible variations of a cache key.
	 *
	 * Generates: base, range-only, comparison-only, range+comparison combos.
	 *
	 * @param string $key_prefix The base key prefix (e.g., 'report_overview_stats').
	 * @return array List of all possible key variations.
	 */
	private function get_cache_key_variations( $key_prefix ) {

		$method = str_replace( 'report_', '', $key_prefix );
		$ranges = self::get_all_ranges();
		$comps  = self::get_all_comparison_ranges();
		$keys   = array();
		$keys[] = $this->generate_cache_key( $method, array() );

		foreach ( $ranges as $range ) {
			$keys[] = $this->generate_cache_key( $method, array( 'range' => $range ) );
		}

		foreach ( $comps as $comp ) {
			$keys[] = $this->generate_cache_key( $method, array( 'comparison' => $comp ) );
		}

		foreach ( $ranges as $range ) {
			foreach ( $comps as $comp ) {
				$keys[] = $this->generate_cache_key( $method, array(
					'range'      => $range,
					'comparison' => $comp,
				) );
			}
		}

		return $keys;
	}

	/**
	 * Delete all cache variations for a specific product.
	 *
	 * @param int $product_id The product ID.
	 */
	public static function delete_single_product_cache( $product_id ) {
		$instance = new static();

		$product_keys = self::get_single_product_cache_keys();

		foreach ( $product_keys as $key_prefix ) {
			$keys = $instance->get_single_product_cache_key_variations( $key_prefix, $product_id );
			foreach ( $keys as $key ) {
				$instance->delete_cache( $key );
			}
		}
	}

	/**
	 * Get all possible cache key variations for a single product.
	 *
	 * Generates: base, range-only, comparison-only, range+comparison combos, all with product_id.
	 *
	 * @param string $key_prefix The base key prefix (e.g., 'products_single_info').
	 * @param int    $product_id  The product ID.
	 * @return array List of all possible key variations with product_id.
	 */
	private function get_single_product_cache_key_variations( $key_prefix, $product_id ) {
		$method = str_replace( 'report_', '', $key_prefix );
		$ranges = self::get_all_ranges();
		$comps  = self::get_all_comparison_ranges();
		$keys   = array();

		$keys[] = $this->generate_cache_key( $method, array( 'product_id' => $product_id ) );

		foreach ( $ranges as $range ) {
			$keys[] = $this->generate_cache_key( $method, array(
				'range'      => $range,
				'product_id' => $product_id,
			) );
		}

		foreach ( $comps as $comp ) {
			$keys[] = $this->generate_cache_key( $method, array(
				'comparison' => $comp,
				'product_id' => $product_id,
			) );
		}

		foreach ( $ranges as $range ) {
			foreach ( $comps as $comp ) {
				$keys[] = $this->generate_cache_key( $method, array(
					'range'      => $range,
					'comparison' => $comp,
					'product_id' => $product_id,
				) );
			}
		}

		return $keys;
	}

	/**
	 * Delete all single product report caches for all products.
	 *
	 * Gets all product IDs from database and deletes their cache variations.
	 */
	public static function delete_all_single_product_caches() {
		$instance = new static();
		$product_ids = $instance->get_all_product_ids();

		foreach ( $product_ids as $product_id ) {
			self::delete_single_product_cache( $product_id );
		}
	}

	/**
	 * Get all product IDs from database.
	 *
	 * @return array List of product IDs.
	 */
	private function get_all_product_ids() {
		global $wpdb;

		$query = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status != 'trash'";
		$result = $wpdb->get_results( $query, ARRAY_A );

		return array_map( 'intval', array_column( $result, 'ID' ) );
	}

	/**
	 * Delete all variations of a cache key.
	 *
	 * @param string $key_prefix The base key prefix (without 'report_' prefix).
	 */
	private function delete_cache_variations( $key_prefix ) {
		$keys = $this->get_cache_key_variations( 'report_' . $key_prefix );
		foreach ( $keys as $key ) {
			$this->delete_cache( $key );
		}
	}

	/**
	 * Get product-related cache keys.
	 *
	 * @return array List of product cache key prefixes (without 'report_' prefix).
	 */
	public static function get_product_cache_keys() {
		return array(
			'overview_top_selling',
			'overview_catalog_stats',
			'products_stats',
			'products_sold_over_time',
			'products_most_sold',
			'products_least_sold',
		);
	}

	/**
	 * Get single product-related cache keys.
	 *
	 * @return array List of single product cache key prefixes (with product_id).
	 */
	public static function get_single_product_cache_keys() {
		return array(
			'products_single_stats',
			'products_single_info',
			'products_single_sales_over_time',
			'products_sale_by_location',
		);
	}

	/**
	 * Get order/customer-related cache keys.
	 *
	 * @return array List of order/customer cache key prefixes (without 'report_' prefix).
	 */
	public static function get_order_cache_keys() {
		return array(
			'overview_stats',
			'overview_sales_refund_revenue',
			'overview_order_vs_refund',
			'overview_order_status',
			'overview_order_type',
			'overview_customer_type',
			'overview_customer_overview',
			'orders_stats',
			'orders_over_time',
			'orders_order_frequency',
			'orders_status_breakdown',
			'orders_fulfillment_breakdown',
			'orders_heatmap',
			'orders_order_by_location',
			'customer_stats',
			'customer_over_time',
			'customer_heatmap',
			'customer_by_location',
			'customer_top_by_revenue',
			'revenue_stats',
			'revenue_over_time',
			'revenue_top_customers',
			'revenue_by_location',
		);
	}

	/**
	 * Delete all report caches.
	 *
	 * @param string $key_prefix Optional key prefix to delete specific caches.
	 */
	public static function delete_all_cache( $key_prefix = '' ) {
		$instance = new static();

		if ( ! empty( $key_prefix ) ) {
			$instance->delete_cache_variations( $key_prefix );
			return;
		}

		foreach ( self::get_cache_keys() as $cache_key ) {
			$instance->delete_cache_variations( $cache_key );
		}

		self::delete_all_single_product_caches();
	}

	/**
	 * Delete product-related report caches.
	 */
	public static function delete_product_cache() {
		$instance = new self();

		foreach ( self::get_product_cache_keys() as $cache_key ) {
			$instance->delete_cache_variations( $cache_key );
		}
	}

	/**
	 * Delete order/customer-related report caches.
	 */
	public static function delete_order_cache() {
		$instance = new self();

		foreach ( self::get_order_cache_keys() as $cache_key ) {
			$instance->delete_cache_variations( $cache_key );
		}
	}

	/**
	 * Get orders data by location (country/state) with aggregation.
	 *
	 * @param string     $date_from  Start date.
	 * @param string     $date_to    End date.
	 * @param string     $aggregate 'count' for order count, 'customers' for unique customer count, 'sum' for revenue.
	 * @param int        $product_id Optional product ID to filter orders.
	 * @param array|null $statuses   Optional list of order statuses to include. Null means no status filter.
	 * @return array
	 */
	public function get_orders_by_location( $date_from, $date_to, $aggregate = 'count', $product_id = null, $statuses = null ) {
		$orders_db        = new Database( 'orders' );
		$orders_table     = $orders_db->get_table();
		$meta_db          = new Database( 'order_meta' );
		$meta_table       = $meta_db->get_table();
		$countries        = array();
		$states           = array();
		$join_order_items = ! empty( $product_id );
		$items_db         = null;
		$items_table      = '';

		if ( $join_order_items ) {
			$items_db     = new Database( 'order_items' );
			$items_table  = $items_db->get_table();
		}

		$where_clause = "om.meta_key = 'billing_address' AND o.created_at >= %s AND o.created_at <= %s";

		if ( ! empty( $statuses ) ) {
			$escaped      = implode( "','", array_map( 'esc_sql', $statuses ) );
			$where_clause .= " AND o.status IN ('{$escaped}')";
		}

		if ( $join_order_items ) {
			$where_clause .= " AND oi.product_id = %d";
		}

		$query_args = array( $date_from, $date_to . ' 23:59:59' );

		if ( $join_order_items ) {
			$query_args[] = $product_id;
		}

		$join_clause = $join_order_items
			? "JOIN {$items_table} oi ON o.id = oi.order_id"
			: '';

		$query = $orders_db->prepare(
			"SELECT om.meta_value, o.total, o.customer_id
			FROM {$orders_table} o
			JOIN {$meta_table} om ON o.id = om.order_id
			{$join_clause}
			WHERE {$where_clause}",
			$query_args
		);

		$single_country = null;
		$seen_customers = array();

		foreach ( $orders_db->exec( $query, \ARRAY_A ) as $row ) {
			$billing_address = maybe_unserialize( $row['meta_value'] );

			if ( is_string( $billing_address ) ) {
				$billing_address = maybe_unserialize( $billing_address );
			}

			if ( is_array( $billing_address ) && ! empty( $billing_address['country'] ) ) {
				$country     = strtoupper( $billing_address['country'] );
				$customer_id = (int) $row['customer_id'];

				if ( $single_country === null ) {
					$single_country = $country;
				} elseif ( $single_country !== $country ) {
					$single_country = false;
				}

				if ( 'customers' === $aggregate ) {
					if ( ! isset( $seen_customers[ $country ][ $customer_id ] ) ) {
						$seen_customers[ $country ][ $customer_id ] = true;
						$countries[ $country ]                      = ( $countries[ $country ] ?? 0 ) + 1;
					}
				} else {
					$value                 = 'sum' === $aggregate ? (float) $row['total'] : 1;
					$countries[ $country ] = ( $countries[ $country ] ?? 0 ) + $value;
				}

				if ( ! empty( $billing_address['state'] ) ) {
					$state = strtoupper( $billing_address['state'] );

					if ( 'customers' === $aggregate ) {
						if ( ! isset( $seen_customers[ $state ][ $customer_id ] ) ) {
							$seen_customers[ $state ][ $customer_id ] = true;
							$states[ $state ]                         = ( $states[ $state ] ?? 0 ) + 1;
						}
					} else {
						$value            = 'sum' === $aggregate ? (float) $row['total'] : 1;
						$states[ $state ] = ( $states[ $state ] ?? 0 ) + $value;
					}
				}
			}
		}

		arsort( $countries );

		$all_countries   = Location::get_countries();
		$country_lookup  = array();
		foreach ( $all_countries as $c ) {
			$country_lookup[ $c['iso2'] ] = $c;
		}

		$top_locations = array();
		$country_data  = array();
		foreach ( $countries as $code => $value ) {
			$info = $country_lookup[ $code ] ?? null;
			$country_data[ $code ] = array(
				'name'     => $info ? $info['name'] : $code,
				'numeric'  => $info ? $info['numeric_code'] : '',
				'iso3'     => $info ? $info['iso3'] : '',
				'display'  => $aggregate === 'sum' ? easycommerce_price( $value ) : $value,
			);
		}

		$top_countries = array_slice( $countries, 0, 6, true );
		foreach ( $top_countries as $code => $value ) {
			$top_locations[] = array(
				'name'     => $country_data[ $code ]['name'] ?? $code,
				'code'     => $code,
				'value'    => $value,
				'display'  => $aggregate === 'sum' ? easycommerce_price( $value ) : $value,
			);
		}

		if ( $single_country && count( $countries ) === 1 ) {
			$country_info = $country_lookup[ $single_country ] ?? null;
			$country_iso3 = $country_info ? $country_info['iso3'] : '';
			$country_name = $country_info ? $country_info['name'] : $single_country;

			arsort( $states );
			$states_data    = Location::get_states( $single_country );
			$state_lookup   = array();
			foreach ( $states_data as $sd ) {
				$state_lookup[ strtoupper( $sd['state_code'] ) ] = $sd['name'];
			}

			$top_states     = array_slice( $states, 0, 5, true );
			$top_locations  = array();
			$states_display = array();
			foreach ( $top_states as $code => $value ) {
				$state_name   = $state_lookup[ $code ] ?? $code;
				$states_display[ $code ] = $aggregate === 'sum' ? easycommerce_price( $value ) : $value;
				$top_locations[] = array(
					'name'     => $state_name,
					'code'     => $code,
					'value'    => $value,
					'display'  => $aggregate === 'sum' ? easycommerce_price( $value ) : $value,
				);
			}

			return array(
				'type'           => 'states',
				'aggregate'      => $aggregate,
				'country'        => $single_country,
				'country_name'   => $country_name,
				'country_iso3'   => $country_iso3,
				'data'           => $states,
				'states_data'    => $states_data,
				'states_display' => $states_display,
				'top_locations'  => $top_locations,
			);
		}

		return array(
			'type'            => 'world',
			'aggregate'       => $aggregate,
			'data'            => $countries,
			'country_data'    => $country_data,
			'top_locations'   => $top_locations,
		);
	}

	/**
	 * Get chart labels for the given range (alias of get_time_series_labels).
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	protected function get_chart_labels( $range ) {
		return $this->get_time_series_labels( $range );
	}

	/**
	 * Format series data as x/y array
	 *
	 * @param array $values The data values.
	 * @param array $labels The labels.
	 * @return array
	 */
	public function format_chart_data( $values, $labels ) {
		$data = array();
		for ( $i = 0; $i < count( $labels ); $i++ ) {
			$data[] = array(
				'x' => $labels[ $i ],
				'y' => isset( $values[ $i ] ) ? floatval( $values[ $i ] ) : 0,
			);
		}
		return $data;
	}

	/**
	 * Format chart data with labels (int variant).
	 *
	 * @param array $values The data values.
	 * @param array $labels The labels.
	 * @return array
	 */
	protected function format_chart_data_with_labels( $values, $labels ) {
		$data = array();
		for ( $i = 0; $i < count( $labels ); $i++ ) {
			$data[] = array(
				'x' => $labels[ $i ],
				'y' => isset( $values[ $i ] ) ? intval( $values[ $i ] ) : 0,
			);
		}
		return $data;
	}

	/**
	 * Map raw date-grouped query results to a flat array aligned with labels.
	 *
	 * Replaces the repeated pattern of: fill keys with 0, loop results,
	 * call get_date_key(), assign to matching bucket, return array_values().
	 *
	 * @param array  $rows    Query results with 'order_date' and a value column.
	 * @param string $range   The date range (used for label formatting).
	 * @param string $value_col Column name for the value (default 'order_count').
	 * @param string $cast    Type to cast values to: 'int' or 'float'.
	 * @return array Flat array of cast values aligned with labels.
	 */
	protected function map_to_date_series( $rows, $range, $value_col = 'order_count', $cast = 'int' ) {
		$labels = $this->get_chart_labels( $range );
		$data   = array_fill_keys( $labels, 0 );

		foreach ( $rows as $row ) {
			$key = $this->get_date_key( strtotime( $row['order_date'] ), $range );
			if ( array_key_exists( $key, $data ) ) {
				$data[ $key ] += 'float' === $cast ? (float) $row[ $value_col ] : (int) $row[ $value_col ];
			}
		}

		return array_values( $data );
	}

	/**
	 * Get 24-hour labels for heatmap charts.
	 *
	 * @return string[]
	 */
	protected function get_hour_labels() {
		return array(
			'12am', '1am', '2am', '3am', '4am', '5am',
			'6am', '7am', '8am', '9am', '10am', '11am',
			'12pm', '1pm', '2pm', '3pm', '4pm', '5pm',
			'6pm', '7pm', '8pm', '9pm', '10pm', '11pm',
		);
	}

	/**
	 * Get order status color map.
	 *
	 * @return array
	 */
	protected function get_status_colors() {
		return array(
			'pending'            => '#FFB310',
			'processing'         => '#1495FF',
			'completed'          => '#19AA79',
			'refunded'           => '#FF001F',
			'partially_refunded' => '#FD7F51',
			'cancelled'          => '#888888',
			'on_hold'            => '#F0A500',
		);
	}

	/**
	 * Get order status label map.
	 *
	 * @return array
	 */
	protected function get_status_labels() {
		return array(
			'pending'            => __( 'Pending', 'easycommerce' ),
			'processing'         => __( 'Processing', 'easycommerce' ),
			'completed'          => __( 'Completed', 'easycommerce' ),
			'refunded'           => __( 'Refunded', 'easycommerce' ),
			'partially_refunded' => __( 'Partially Refunded', 'easycommerce' ),
			'cancelled'          => __( 'Cancelled', 'easycommerce' ),
			'on_hold'            => __( 'On Hold', 'easycommerce' ),
		);
	}

	/**
	 * Build a date-to-hours map for heatmap queries.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to   End date (Y-m-d).
	 * @return array<string, int[]>
	 */
	protected function build_heatmap_date_map( $date_from, $date_to ) {
		$map    = array();
		$cursor = new \DateTime( $date_from );
		$end    = new \DateTime( $date_to );

		while ( $cursor <= $end ) {
			$key        = $cursor->format( 'Y-m-d' );
			$map[ $key ] = array_fill( 0, 24, 0 );
			$cursor->modify( '+1 day' );
		}

		return $map;
	}

	/**
	 * Build heatmap data array from a date-hours map and hour labels.
	 *
	 * @param array   $map          Date-to-hours map from build_heatmap_date_map().
	 * @param string[] $hour_labels  24-hour labels from get_hour_labels().
	 * @return array
	 */
	protected function build_heatmap_data( $map, $hour_labels ) {
		$data = array();
		foreach ( $map as $date => $hours ) {
			for ( $h = 0; $h < 24; $h++ ) {
				$data[] = array(
					'x' => $hour_labels[ $h ],
					'y' => $date,
					'v' => $hours[ $h ],
				);
			}
		}
		return $data;
	}

	/**
	 * Build a status/fulfillment breakdown array.
	 *
	 * @param string $range      The date range.
	 * @param array  $statuses   Status key => label pairs.
	 * @param array  $colors     Status key => color pairs.
	 * @param array  $labels     Status key => translated label pairs.
	 * @param string $filter_key The query filter key ('status' or 'fulfill_status').
	 * @return array
	 */
	protected function build_status_breakdown( $range, $statuses, $colors, $labels, $filter_key = 'status' ) {
		$breakdown = array();

		foreach ( $statuses as $status => $label ) {
			$orders = $this->total_orders( $range, array( $filter_key => $status ) );
			$count  = count( $orders );

			if ( $count > 0 ) {
				$breakdown[] = array(
					'id'    => $labels[ $status ] ?? $label,
					'value' => $count,
					'color' => $colors[ $status ] ?? '#CCCCCC',
				);
			}
		}

		return $breakdown;
	}
}

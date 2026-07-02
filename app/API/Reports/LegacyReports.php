<?php

namespace EasyCommerce\API\Reports;

defined( 'ABSPATH' ) || exit;

use DateInterval;
use DatePeriod;
use DateTime;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Order_Item;
use EasyCommerce\Models\Product_Variation;
use WP_REST_Request;

/**
 * Reports Legacy API - maintains backward compatibility
 */
class LegacyReports extends Reports {

	/**
	 * Get the full report data with charts
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
			$range_dates = explode( ',', $range );
			$start       = new DateTime( $range_dates[0] );
			$end         = new DateTime( $range_dates[1] );
			$interval    = new DateInterval( 'P1D' );
			$date_range  = new DatePeriod( $start, $interval, $end->modify( '+1 day' ) );
			$x_values    = array();
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

		$margin_amount_by_date = array_fill_keys( $x_values, 0 );
		$margin_amount_total   = 0;
		$completed_orders      = $this->total_orders( $range, 'completed', $product_id );

		foreach ( $completed_orders as $order ) {
			$order_obj      = new Order( $order['id'] );
			$items          = $order_obj->get_items();
			$date_key       = $this->get_date_key( strtotime( $order['created_at'] ), $range );
			$total_discount = abs( $order_obj->get_discount_total() );

			$total_original_price = 0;
			foreach ( $items as $item ) {
				$product_variation     = new Product_Variation( $item->variation_id );
				$price                 = $product_variation->get_price();
				$total_original_price += ( $price * $item->quantity );
			}

			$order_profit = 0;

			foreach ( $items as $item ) {
				$product_variation = new Product_Variation( $item->variation_id );
				$order_item_model  = new Order_Item();
				$order_iteam       = $order_item_model->get_by_id( $item->id );
				$cost              = $product_variation->get_meta( 'cost_per_item', true );

				if ( empty( $cost ) || $order_iteam->subtotal == '0.00' ) {
					continue;
				}
				$price            = $product_variation->get_price();
				$discounted_price = $price;

				if ( $total_discount > 0 && $total_original_price > 0 ) {
					$item_original_total = $price * $item->quantity;
					$item_discount       = ( $total_discount * $item_original_total ) / $total_original_price;
					$discount_per_unit   = $item_discount / $item->quantity;
					$discounted_price    = max( 0, $price - $discount_per_unit );
				}

				$item_profit   = ( $discounted_price - $cost ) * $item->quantity;
				$order_profit += $item_profit;
			}

			$margin_amount_by_date[ $date_key ] += $order_profit;
			$margin_amount_total                += $order_profit;
		}

		$margin_amount_by_date = array_values( $margin_amount_by_date );

		$data_groups['margin'] = array(
			'count'        => array_fill( 0, count( $x_values ), 0 ),
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

		$reports = apply_filters( 'easycommerce_get_reports', $reports, $range, $request );

		$this->response_success( array( 'reports' => $reports ) );
	}
}

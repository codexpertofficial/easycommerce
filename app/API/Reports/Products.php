<?php

namespace EasyCommerce\API\Reports;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Product;
use WP_REST_Request;

/**
 * Reports Products API
 */
class Products extends Reports {

	/**
	 * Products stats overview
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function stats( $request ) {
		$range             = $request->get_param( 'range' ) ?: 'last-30';
		$comparison        = $request->get_param( 'comparison' );
		$comparison_range  = $comparison ?: $this->get_comparison_range( $range );
		$cache_key         = $this->generate_cache_key( 'products_stats', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $comparison_range ) {
				$current_stats = $this->calculate_product_stats( $range );
				$prev_stats    = $this->calculate_product_stats( $comparison_range );

				$stats_data = array(
					array(
						'title'          => __( 'Live Products', 'easycommerce' ),
						'value'          => number_format( (int) $current_stats['live_products'] ),
						'icon'           => 'products-icon',
						'comparison'     => $this->get_comparison_with_vibe( $current_stats['live_products'], $prev_stats['live_products'] ),
					),
					array(
						'title'           => __( 'Total Stock', 'easycommerce' ),
						'value'           => number_format( (int) $current_stats['total_stock'] ),
						'icon'            => 'stock-icon',
					),
					array(
						'title'           => __( 'Out of Stock', 'easycommerce' ),
						'value'           => number_format( (int) $current_stats['out_of_stock'] ),
						'icon'            => 'outofstock-icon',
					),
					array(
						'title'           => __( 'Low Stock', 'easycommerce' ),
						'value'           => number_format( (int) $current_stats['low_stock'] ),
						'icon'            => 'lowstock-icon',
					),
					array(
						'title'           => __( 'Gross Sales', 'easycommerce' ),
						'value'           => number_format( (int) $current_stats['sales_count'] ),
						'secondary_value' => easycommerce_price( $current_stats['gross_sales'] ),
						'icon'            => 'sales-icon',
						'comparison'      => $this->get_comparison_with_vibe( $current_stats['sales_count'], $prev_stats['sales_count'] ),
					),
					array(
						'title'           => __( 'Refunds', 'easycommerce' ),
						'value'           => number_format( (int) $current_stats['refund_count'] ),
						'secondary_value' => easycommerce_price( $current_stats['refund_amount'] ),
						'icon'            => 'refunds-icon',
						'comparison'      => $this->get_refund_comparison( $current_stats['refund_count'], $prev_stats['refund_count'] ),
					),
					array(
						'title'           => __( 'Average Rating', 'easycommerce' ),
						'value'           => number_format( (float) $current_stats['avg_rating'], 1 ),
						'secondary_value' => number_format( (int) $current_stats['total_ratings'] ),
						'icon'            => 'rating-icon',
					),
				);

				return $stats_data;
			},
			self::CACHE_DURATION_DAY
		);

		$result = apply_filters( 'easycommerce_reports_products_stats', $result, $range, $request );

		$this->response_success(
			array(
				'stats'           => $result,
				'range'           => $range,
				'comparison_with' => $comparison_range,
			)
		);
	}

	/**
	 * Get date range with time for queries.
	 *
	 * @param string $range The date range.
	 * @return array { from: string, to: string, from_raw: string, to_raw: string, is_all_time: bool }
	 */
	private function get_query_date_range( $range ) {
		$dates        = $this->resolve_dates( $range );
		$from_raw     = $dates['from'];
		$to_raw       = $dates['to'];
		$to_with_time = $to_raw . ' 23:59:59';

		return array(
			'from'        => $from_raw,
			'to'          => $to_with_time,
			'from_raw'    => $from_raw,
			'to_raw'      => $to_raw,
			'is_all_time' => 'all-time' === $range,
		);
	}

	/**
	 * Calculate product stats for a given range
	 *
	 * @param string $range The date range.
	 * @return array
	 */
	private function calculate_product_stats( $range ) {
		$db               = new Database( 'product_variations' );
		$variations_table = $db->get_table();
		$posts_table      = $db->get_wp_prefix() . 'posts';

		$variations_result = $db->exec(
			$db->prepare(
				"SELECT
					COUNT(DISTINCT pv.product_id) as live_products,
					SUM(COALESCE(pv.stock_quantity, 0)) as total_stock
				FROM {$variations_table} pv
				INNER JOIN {$posts_table} p ON pv.product_id = p.ID
				WHERE p.post_type = %s AND p.post_status = 'publish'",
				'product'
			),
			ARRAY_A
		);

		$low_stock_result = $db->exec(
			$db->prepare(
				"SELECT COUNT(DISTINCT pv.product_id) as low_stock
				FROM {$variations_table} pv
				INNER JOIN {$posts_table} p ON pv.product_id = p.ID
				WHERE p.post_type = %s AND p.post_status = 'publish'
				AND pv.stock_quantity IS NOT NULL
				AND pv.stock_quantity > 0 AND pv.stock_quantity <= 5",
				'product'
			),
			ARRAY_A
		);

		$out_of_stock_result = $db->exec(
			$db->prepare(
				"SELECT COUNT(DISTINCT pv.product_id) as out_of_stock
				FROM {$variations_table} pv
				INNER JOIN {$posts_table} p ON pv.product_id = p.ID
				WHERE p.post_type = %s AND p.post_status = 'publish'
				AND pv.stock_quantity IS NOT NULL AND pv.stock_quantity <= 0",
				'product'
			),
			ARRAY_A
		);

		$sales_stats  = $this->get_product_sales_stats( $range );
		$rating_stats = $this->get_average_rating( $range );
		$rating_count = $this->get_total_ratings_count( $range );

		return array(
			'live_products'  => $variations_result[0]['live_products'] ?? 0,
			'total_stock'    => $variations_result[0]['total_stock'] ?? 0,
			'out_of_stock'   => $out_of_stock_result[0]['out_of_stock'] ?? 0,
			'low_stock'      => $low_stock_result[0]['low_stock'] ?? 0,
			'sales_count'    => $sales_stats['sales_count'],
			'gross_sales'    => $sales_stats['gross_sales'],
			'refund_count'   => $sales_stats['refund_count'],
			'refund_amount'  => $sales_stats['refund_amount'],
			'avg_rating'     => $rating_stats,
			'total_ratings'  => $rating_count,
		);
	}

	/**
	 * Get product sales stats (gross sales and refunds)
	 *
	 * @param string $range The date range.
	 * @return array
	 */
	private function get_product_sales_stats( $range ) {
		$dates             = $this->get_query_date_range( $range );
		$db                = new Database( 'order_items' );
		$order_items_table = $db->get_table();
		$db_orders         = new Database( 'orders' );
		$orders_table      = $db_orders->get_table();

		if ( $dates['is_all_time'] ) {
			$count_query = "SELECT COUNT(*) as sales_count
				FROM {$order_items_table} oi
				INNER JOIN {$orders_table} o ON oi.order_id = o.id
				WHERE o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')";
			$total_query = "SELECT SUM(o.total) as gross_sales
				FROM {$orders_table} o
				WHERE o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')";

			$count_result = $db->exec( $count_query, ARRAY_A );
			$total_result = $db_orders->exec( $total_query, ARRAY_A );
		} else {
			$count_query = $db->prepare(
				"SELECT COUNT(*) as sales_count
				FROM {$order_items_table} oi
				INNER JOIN {$orders_table} o ON oi.order_id = o.id
				WHERE o.created_at >= %s AND o.created_at <= %s
				AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')",
				$dates['from'],
				$dates['to'] . ' 23:59:59'
			);
			$total_query = $db_orders->prepare(
				"SELECT SUM(o.total) as gross_sales
				FROM {$orders_table} o
				WHERE o.created_at >= %s AND o.created_at <= %s
				AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')",
				$dates['from'],
				$dates['to'] . ' 23:59:59'
			);

			$count_result = $db->exec( $count_query, ARRAY_A );
			$total_result = $db_orders->exec( $total_query, ARRAY_A );
		}

		$refund_stats = $this->get_refund_stats( $range, null, $dates['is_all_time'] );

		return array(
			'sales_count'   => (int) ( $count_result[0]['sales_count'] ?? 0 ),
			'gross_sales'   => (float) ( $total_result[0]['gross_sales'] ?? 0 ),
			'refund_count'  => $refund_stats['refund_count'],
			'refund_amount' => $refund_stats['refund_amount'],
		);
	}

	/**
	 * Get average rating across products
	 *
	 * @param string $range The date range.
	 * @return float
	 */
	private function get_average_rating( $range ) {
		$dates            = $this->get_query_date_range( $range );
		$db               = new Database( 'product_variations' );
		$variations_table = $db->get_table();
		$posts_table      = $db->get_wp_prefix() . 'posts';
		$postmeta_table   = $db->get_wp_prefix() . 'postmeta';

		if ( $dates['is_all_time'] ) {
			$query = $db->prepare(
				"SELECT AVG(COALESCE(pm.meta_value, 0)) as avg_rating
				FROM {$posts_table} p
				INNER JOIN {$variations_table} pv ON p.ID = pv.product_id
				LEFT JOIN {$postmeta_table} pm ON p.ID = pm.post_id AND pm.meta_key = 'average_rating'
				WHERE p.post_type = %s AND p.post_status = 'publish'
				AND pm.meta_value IS NOT NULL AND pm.meta_value != '' AND pm.meta_value != '0'",
				'product'
			);
		} else {
			$query = $db->prepare(
				"SELECT AVG(COALESCE(pm.meta_value, 0)) as avg_rating
				FROM {$posts_table} p
				INNER JOIN {$variations_table} pv ON p.ID = pv.product_id
				LEFT JOIN {$postmeta_table} pm ON p.ID = pm.post_id AND pm.meta_key = 'average_rating'
				WHERE p.post_type = %s AND p.post_status = 'publish'
				AND p.post_date >= %s AND p.post_date <= %s
				AND pm.meta_value IS NOT NULL AND pm.meta_value != '' AND pm.meta_value != '0'",
				'product',
				$dates['from'],
				$dates['to']
			);
		}

		$result = $db->exec( $query, ARRAY_A );

		return (float) ( $result[0]['avg_rating'] ?? 0 );
	}

	/**
	 * Get total count of ratings across products
	 *
	 * @param string $range The date range.
	 * @return int
	 */
	private function get_total_ratings_count( $range ) {
		$dates          = $this->get_query_date_range( $range );
		$db             = new Database( 'product_variations' );
		$posts_table    = $db->get_wp_prefix() . 'posts';
		$comments_table = $db->get_wp_prefix() . 'comments';

		if ( $dates['is_all_time'] ) {
			$query = $db->prepare(
				"SELECT COUNT(*) as total_ratings
				FROM {$comments_table} c
				INNER JOIN {$posts_table} p ON c.comment_post_ID = p.ID
				WHERE p.post_type = %s AND p.post_status = 'publish'
				AND c.comment_approved = '1'",
				'product'
			);
		} else {
			$query = $db->prepare(
				"SELECT COUNT(*) as total_ratings
				FROM {$comments_table} c
				INNER JOIN {$posts_table} p ON c.comment_post_ID = p.ID
				WHERE p.post_type = %s AND p.post_status = 'publish'
				AND c.comment_date >= %s AND c.comment_date <= %s
				AND c.comment_approved = '1'",
				'product',
				$dates['from'],
				$dates['to'] . ' 23:59:59'
			);
		}

		$result = $db->exec( $query, ARRAY_A );

		return (int) ( $result[0]['total_ratings'] ?? 0 );
	}

	/**
	 * Get products sold over time with comparison
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function products_sold_over_time( $request ) {
		$range             = $request->get_param( 'range' ) ?: 'last-30';
		$comparison        = $request->get_param( 'comparison' );
		$comparison_range  = $comparison ?: $this->get_comparison_range( $range );
		$cache_key         = $this->generate_cache_key( 'products_sold_over_time', $this->get_cache_params( $request ) );

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $comparison_range, $request ) {
				$labels        = $this->get_chart_labels( $range );
				$current_data  = $this->get_products_sold_by_date( $range );
				$previous_data = $this->get_products_sold_by_date( $comparison_range );

				$datasets = array(
					array(
						'id'            => __( 'Products Sold', 'easycommerce' ),
						'color'         => '#7351FD',
						'data'          => $this->format_chart_data_with_labels( $current_data, $labels ),
						'previous'      => $this->format_chart_data_with_labels( $previous_data, $labels ),
						'previousColor' => '#A78BFA',
					),
				);

				return array(
					'labels'          => $labels,
					'datasets'        => apply_filters( 'easycommerce_reports_products_sold_over_time', $datasets, $range, $request ),
					'range'           => $range,
					'comparison_with' => $comparison_range,
				);
			},
			self::CACHE_DURATION_DAY
		);

		$this->response_success( $result );
	}

	/**
	 * Get products sold count by date
	 *
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_products_sold_by_date( $range ) {
		$dates             = $this->get_query_date_range( $range );
		$db                = new Database( 'order_items' );
		$order_items_table = $db->get_table();
		$orders_table      = $db->get_wp_prefix() . 'ec_orders';

		$query   = $db->prepare(
			"SELECT DATE(o.created_at) as order_date, SUM(oi.quantity) as total_sold
			FROM {$order_items_table} oi
			INNER JOIN {$orders_table} o ON oi.order_id = o.id
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			GROUP BY DATE(o.created_at)",
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);
		$results = $db->exec( $query, ARRAY_A );

		return $this->map_to_date_series( $results, $range, 'total_sold', 'int' );
	}

	/**
	 * Get most sold products with detailed metrics
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function most_sold( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'products_most_sold', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$products = $this->get_most_sold_products( $range, 10 );

				return apply_filters( 'easycommerce_reports_most_sold', $products, $range, $request );
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
	 * Get least sold products with detailed metrics
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function least_sold( $request ) {
		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key( 'products_least_sold', $this->get_cache_params( $request ) );
		$result    = $this->get_cached_or_set(
			$cache_key,
			function () use ( $range, $request ) {
				$products = $this->get_least_sold_products( $range, 10 );

				return apply_filters( 'easycommerce_reports_least_sold', $products, $range, $request );
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
	 * Get most sold products data
	 *
	 * @param string $range The date range.
	 * @param int    $limit Number of products to return.
	 *
	 * @return array
	 */
	private function get_most_sold_products( $range, $limit = 10 ) {
		return $this->get_sold_products( $range, $limit, 'DESC' );
	}

	/**
	 * Get least sold products data
	 *
	 * @param string $range The date range.
	 * @param int    $limit Number of products to return.
	 *
	 * @return array
	 */
	private function get_least_sold_products( $range, $limit = 10 ) {
		return $this->get_sold_products( $range, $limit, 'ASC' );
	}

	/**
	 * Get sold products data with detailed metrics.
	 *
	 * @param string $range The date range.
	 * @param int    $limit Number of products to return.
	 * @param string $order Sort direction: 'ASC' or 'DESC'.
	 *
	 * @return array
	 */
	private function get_sold_products( $range, $limit = 10, $order = 'DESC' ) {
		$dates             = $this->get_query_date_range( $range );
		$db                = new Database( 'order_items' );
		$order_items_table = $db->get_table();
		$orders_table      = $db->get_wp_prefix() . 'ec_orders';

		$refunds_table = ( new Database( 'refunds' ) )->get_table();

		if ( $dates['is_all_time'] ) {
			$query = "SELECT
				oi.product_id,
				SUM(oi.quantity) as unit_sold,
				SUM(
					CASE WHEN (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) > 0
					THEN oi.rate * oi.quantity
					ELSE oi.subtotal END
				) as total_sale,
				(SELECT SUM(r.amount) FROM {$refunds_table} r
				 WHERE r.order_id IN (
					SELECT DISTINCT oi2.order_id FROM {$order_items_table} oi2
					WHERE oi2.product_id = oi.product_id
				 )
				 AND r.status IN ('approved', 'processed')) as refunds
			FROM {$order_items_table} oi
			INNER JOIN {$orders_table} o ON oi.order_id = o.id
			WHERE o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			GROUP BY oi.product_id
			ORDER BY unit_sold {$order}
			LIMIT %d";
			$query = $db->prepare( $query, $limit );
		} else {
			$query = $db->prepare(
				"SELECT
					oi.product_id,
					SUM(oi.quantity) as unit_sold,
					SUM(
						CASE WHEN (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) > 0
						THEN oi.rate * oi.quantity
						ELSE oi.subtotal END
					) as total_sale,
					(SELECT SUM(r.amount) FROM {$refunds_table} r
					 WHERE r.order_id IN (
						SELECT DISTINCT oi2.order_id FROM {$order_items_table} oi2
						WHERE oi2.product_id = oi.product_id
					 )
					 AND r.status IN ('approved', 'processed')
					 AND r.created_at >= %s AND r.created_at <= %s) as refunds
				FROM {$order_items_table} oi
				INNER JOIN {$orders_table} o ON oi.order_id = o.id
				WHERE o.created_at >= %s AND o.created_at <= %s
				AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
				GROUP BY oi.product_id
				ORDER BY unit_sold {$order}
				LIMIT %d",
				$dates['from'],
				$dates['to'] . ' 23:59:59',
				$dates['from'],
				$dates['to'] . ' 23:59:59',
				$limit
			);
		}
		$items     = $db->exec( $query, ARRAY_A );
		$products  = array();
		$rank      = 1;
		$variation = new Database( 'product_variations' );
		$var_table = $variation->get_table();

		foreach ( $items as $item ) {
			$product_id = (int) $item['product_id'];
			$product    = new Product( $product_id );

			if ( ! $product->exists() ) {
				continue;
			}

			$in_stock = $this->get_product_stock( $product_id, $var_table );

			$products[] = array(
				'rank'       => $rank,
				'product_id' => $product_id,
				'name'       => $product->get_title(),
				'in_stock'   => $in_stock,
				'unit_sold'  => (int) $item['unit_sold'],
				'total_sale' => easycommerce_price( (float) $item['total_sale'] ),
				'refunds'    => easycommerce_price( (float) ( $item['refunds'] ?? 0 ) ),
			);

			++$rank;
		}

		return $products;
	}

	/**
	 * Get total stock for a product
	 *
	 * @param int    $product_id The product ID.
	 * @param string $variations_table The variations table name.
	 *
	 * @return int
	 */
	private function get_product_stock( $product_id, $variations_table ) {
		$db     = new Database( 'product_variations' );
		$query  = $db->prepare(
			"SELECT SUM(COALESCE(stock_quantity, 0)) as total_stock 
			FROM {$variations_table} 
			WHERE product_id = %d",
			$product_id
		);
		$result = $db->exec( $query, ARRAY_A );

		return (int) ( $result[0]['total_stock'] ?? 0 );
	}

	/**
	 * Get product name by ID
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_product_name( $request ) {
		$product_id = (int) $request->get_param( 'product_id' );

		if ( ! $product_id ) {
			$this->response_error( __( 'Product ID is required', 'easycommerce' ) );
			return;
		}

		$product = new Product( $product_id );
		if ( ! $product->exists() ) {
			$this->response_error( __( 'Product not found', 'easycommerce' ) );
			return;
		}

		$this->response_success(
			array(
				'product_id' => $product_id,
				'name'       => $product->get_title(),
			)
		);
	}

	/**
	 * Get single product stats
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function single_product_stats( $request ) {
		$product_id = (int) $request->get_param( 'product_id' );
		$range      = $request->get_param( 'range' ) ?: 'last-30';

		if ( ! $product_id ) {
			$this->response_error( __( 'Product ID is required', 'easycommerce' ) );
			return;
		}

		$product = new Product( $product_id );
		if ( ! $product->exists() ) {
			$this->response_error( __( 'Product not found', 'easycommerce' ) );
			return;
		}

		$cache_key = $this->generate_cache_key(
			'products_single_stats',
			array(
				'range'      => $range,
				'product_id' => $product_id,
			)
		);

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $product_id, $range ) {
				return $this->get_single_product_stats_data( $product_id, $range );
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
	 * Get single product stats data
	 *
	 * @param int    $product_id The product ID.
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_single_product_stats_data( $product_id, $range ) {
		$dates             = $this->get_query_date_range( $range );
		$db                = new Database( 'order_items' );
		$order_items_table = $db->get_table();
		$orders_table      = $db->get_wp_prefix() . 'ec_orders';
		$variations_db     = new Database( 'product_variations' );
		$variations_table  = $variations_db->get_table();
		$sale_count        = 0;
		$gross_sale        = 0;
		$refund_count      = 0;
		$refund            = 0;

		if ( $dates['is_all_time'] ) {
			$query = $db->prepare(
				"SELECT
					COUNT(*) as sale_count,
					SUM(
						CASE WHEN (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) > 0
						THEN oi.subtotal / (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) * o.total
						ELSE oi.subtotal END
					) as gross_sale
				FROM {$order_items_table} oi
				INNER JOIN {$orders_table} o ON oi.order_id = o.id
				WHERE oi.product_id = %d
				AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')",
				$product_id
			);
		} else {
			$query = $db->prepare(
				"SELECT
					COUNT(*) as sale_count,
					SUM(
						CASE WHEN (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) > 0
						THEN oi.subtotal / (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) * o.total
						ELSE oi.subtotal END
					) as gross_sale
				FROM {$order_items_table} oi
				INNER JOIN {$orders_table} o ON oi.order_id = o.id
				WHERE oi.product_id = %d
				AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
				AND o.created_at >= %s AND o.created_at <= %s",
				$product_id,
				$dates['from'],
				$dates['to'] . ' 23:59:59'
			);
		}

		$sales_result = $db->exec( $query, ARRAY_A );
		if ( ! empty( $sales_result ) ) {
			$sale_count = (int) ( $sales_result[0]['sale_count'] ?? 0 );
			$gross_sale = (float) ( $sales_result[0]['gross_sale'] ?? 0 );
		}

		$refund_stats = $this->get_refund_stats( $range, $product_id, $dates['is_all_time'] );
		$refund_count = $refund_stats['refund_count'];
		$refund       = $refund_stats['refund_amount'];
		$stock        = $this->get_product_stock( $product_id, $variations_table );
		$rating       = $this->get_product_rating( $product_id );

		return array(
			array(
				'title'           => __( 'Gross Sale', 'easycommerce' ),
				'value'           => number_format( $sale_count ),
				'secondary_value' => easycommerce_price( $gross_sale ),
				'icon'            => 'sales-icon',
			),
			array(
				'title'           => __( 'Refunds', 'easycommerce' ),
				'value'           => number_format( $refund_count ),
				'secondary_value' => easycommerce_price( $refund ),
				'icon'            => 'refunds-icon',
			),
			array(
				'title'           => __( 'Stock', 'easycommerce' ),
				'value'           => number_format( $stock ),
				'icon'            => 'stock-icon',
			),
			array(
				'title'           => __( 'Avg Rating', 'easycommerce' ),
				'value'           => $rating > 0 ? number_format( $rating, 1 ) : '-',
				'icon'            => 'rating-icon',
			),
		);
	}

	/**
	 * Get product rating
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return float
	 */
	private function get_product_rating( $product_id ) {
		$db             = new Database( 'product_variations' );
		$postmeta_table = $db->get_wp_prefix() . 'postmeta';

		$query  = $db->prepare(
			"SELECT meta_value as rating 
			FROM {$postmeta_table} 
			WHERE post_id = %d AND meta_key = 'average_rating' AND meta_value != '' AND meta_value != '0'
			LIMIT 1",
			$product_id
		);
		$result = $db->exec( $query, ARRAY_A );

		return ! empty( $result ) ? (float) $result[0]['rating'] : 0;
	}

	/**
	 * Get single product basic info with variations
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function single_product_info( $request ) {
		$product_id = (int) $request->get_param( 'product_id' );

		if ( ! $product_id ) {
			$this->response_error( __( 'Product ID is required', 'easycommerce' ) );
			return;
		}

		$product = new Product( $product_id );
		if ( ! $product->exists() ) {
			$this->response_error( __( 'Product not found', 'easycommerce' ) );
			return;
		}

		$range     = $request->get_param( 'range' ) ?: 'last-30';
		$cache_key = $this->generate_cache_key(
			'products_single_info',
			array(
				'range'      => $range,
				'product_id' => $product_id,
			)
		);

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $product, $range ) {
				return $this->get_single_product_info_data( $product, $range );
			},
			self::CACHE_DURATION_DAY
		);

		$this->response_success( $result );
	}

	/**
	 * Get single product info data
	 *
	 * @param Product $product The product object.
	 * @param string  $range   The date range.
	 *
	 * @return array
	 */
	private function get_single_product_info_data( $product, $range = 'last-30' ) {
		$product_id      = $product->get_id();
		$thumbnail       = $product->get_thumbnail( 'full' );
		$variations_data = $this->get_product_variations_with_stats( $product_id, $range );
		$sku             = '';

		if ( ! empty( $variations_data ) ) {
			$sku = $variations_data[0]['sku'] ?? '';
		}

		return array(
			'name'       => $product->get_title(),
			'thumbnail'  => $thumbnail['url'] ?? '',
			'sku'        => $sku,
			'variations' => $variations_data,
		);
	}

	/**
	 * Get product variations with sales and rating stats
	 *
	 * @param int    $product_id The product ID.
	 * @param string $range      The date range.
	 *
	 * @return array
	 */
	private function get_product_variations_with_stats( $product_id, $range = 'last-30' ) {
		$dates             = $this->get_query_date_range( $range );
		$db                = new Database( 'product_variations' );
		$variations_table  = $db->get_table();
		$order_items_db    = new Database( 'order_items' );
		$order_items_table = $order_items_db->get_table();
		$orders_table      = $db->get_wp_prefix() . 'ec_orders';

		$query      = $db->prepare(
			"SELECT 
				pv.id,
				pv.name,
				pv.sku,
				pv.stock_quantity,
				COALESCE(pv.stock_quantity, 0) as stock
			FROM {$variations_table} pv
			WHERE pv.product_id = %d
			ORDER BY pv.id ASC",
			$product_id
		);
		$variations = $db->exec( $query, ARRAY_A );

		$sales_query   = $db->prepare(
			"SELECT
				oi.variation_id,
				SUM(oi.quantity) as items_sold,
				SUM(
					CASE WHEN (SELECT SUM(oi2.subtotal) FROM {$order_items_table} oi2 WHERE oi2.order_id = oi.order_id) > 0
					THEN oi.rate * oi.quantity
					ELSE oi.subtotal END
				) as total_sales
			FROM {$order_items_table} oi
			INNER JOIN {$orders_table} o ON oi.order_id = o.id
			WHERE oi.product_id = %d
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			AND o.created_at >= %s AND o.created_at <= %s
			GROUP BY oi.variation_id",
			$product_id,
			$dates['from'],
			$dates['to']
		);
		$sales_results = $db->exec( $sales_query, ARRAY_A );

		$sales_by_variation = array();
		foreach ( $sales_results as $sale ) {
			$sales_by_variation[ $sale['variation_id'] ] = array(
				'items_sold'  => (int) $sale['items_sold'],
				'total_sales' => (float) $sale['total_sales'],
			);
		}

		$variations_data = array();
		foreach ( $variations as $variation ) {
			$variation_id = (int) $variation['id'];
			$sales_data   = $sales_by_variation[ $variation_id ] ?? array(
				'items_sold'  => 0,
				'total_sales' => 0,
			);

			$variations_data[] = array(
				'name'        => $variation['name'],
				'sku'         => $variation['sku'] ?? '',
				'stock'       => (int) $variation['stock'],
				'items_sold'  => $sales_data['items_sold'],
				'total_sales' => easycommerce_price( $sales_data['total_sales'] ),
			);
		}

		return $variations_data;
	}

	/**
	 * Get single product sales over time with new vs repeat customers
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function single_product_sales_over_time( $request ) {
		$product_id = (int) $request->get_param( 'product_id' );
		$range      = $request->get_param( 'range' ) ?: 'last-30';

		if ( ! $product_id ) {
			$this->response_error( __( 'Product ID is required', 'easycommerce' ) );
			return;
		}

		$product = new Product( $product_id );
		if ( ! $product->exists() ) {
			$this->response_error( __( 'Product not found', 'easycommerce' ) );
			return;
		}

		$cache_key = $this->generate_cache_key(
			'products_single_sales_over_time',
			array(
				'range'      => $range,
				'product_id' => $product_id,
			)
		);

		$result = $this->get_cached_or_set(
			$cache_key,
			function () use ( $product_id, $range ) {
				$labels           = $this->get_chart_labels( $range );
				$new_customers    = $this->get_single_product_new_customers_by_date( $product_id, $range );
				$repeat_customers = $this->get_single_product_repeat_customers_by_date( $product_id, $range );

				$datasets = array(
					array(
						'id'    => __( 'New Customers', 'easycommerce' ),
						'color' => '#19AA79',
						'fill'  => false,
						'data'  => $this->format_chart_data_with_labels( $new_customers, $labels ),
					),
					array(
						'id'    => __( 'Repeat Customers', 'easycommerce' ),
						'color' => '#5283FF',
						'fill'  => false,
						'data'  => $this->format_chart_data_with_labels( $repeat_customers, $labels ),
					),
				);

				return array(
					'labels'   => $labels,
					'datasets' => $datasets,
				);
			},
			self::CACHE_DURATION_DAY
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
	 * Get new customers who bought a specific product by date
	 *
	 * @param int    $product_id The product ID.
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_single_product_new_customers_by_date( $product_id, $range ) {
		$dates             = $this->get_query_date_range( $range );
		$db                = new Database( 'orders' );
		$orders_table      = $db->get_table();
		$db_oi             = new Database( 'order_items' );
		$order_items_table = $db_oi->get_table();
		$labels            = $this->get_chart_labels( $range );
		$data              = array_fill_keys( $labels, 0 );

		$query   = $db->prepare(
			"SELECT DATE(o.created_at) as order_date, COUNT(DISTINCT o.customer_id) as customer_count
			FROM {$orders_table} o
			INNER JOIN {$order_items_table} oi ON o.id = oi.order_id
			WHERE oi.product_id = %d
			AND o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			AND o.customer_id IN (
				SELECT customer_id FROM {$orders_table}
				GROUP BY customer_id HAVING COUNT(*) = 1
			)
			GROUP BY DATE(o.created_at)",
			$product_id,
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);
		$results = $db->exec( $query, ARRAY_A );

		foreach ( $results as $row ) {
			$date_key = $this->get_date_key( strtotime( $row['order_date'] ), $range );
			if ( isset( $data[ $date_key ] ) ) {
				$data[ $date_key ] = (int) $row['customer_count'];
			}
		}

		return array_values( $data );
	}

	/**
	 * Get repeat customers who bought a specific product by date
	 *
	 * @param int    $product_id The product ID.
	 * @param string $range The date range.
	 *
	 * @return array
	 */
	private function get_single_product_repeat_customers_by_date( $product_id, $range ) {
		$dates             = $this->get_query_date_range( $range );
		$db                = new Database( 'orders' );
		$orders_table      = $db->get_table();
		$db_oi             = new Database( 'order_items' );
		$order_items_table = $db_oi->get_table();
		$labels            = $this->get_chart_labels( $range );
		$data              = array_fill_keys( $labels, 0 );

		$query   = $db->prepare(
			"SELECT DATE(o.created_at) as order_date, COUNT(DISTINCT o.customer_id) as customer_count
			FROM {$orders_table} o
			INNER JOIN {$order_items_table} oi ON o.id = oi.order_id
			WHERE oi.product_id = %d
			AND o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('completed', 'processing', 'partially_refunded', 'refunded')
			AND o.customer_id IN (
				SELECT customer_id FROM {$orders_table}
				GROUP BY customer_id HAVING COUNT(*) > 1
			)
			GROUP BY DATE(o.created_at)",
			$product_id,
			$dates['from'],
			$dates['to'] . ' 23:59:59'
		);
		$results = $db->exec( $query, ARRAY_A );

		foreach ( $results as $row ) {
			$date_key = $this->get_date_key( strtotime( $row['order_date'] ), $range );
			if ( isset( $data[ $date_key ] ) ) {
				$data[ $date_key ] = (int) $row['customer_count'];
			}
		}

		return array_values( $data );
	}

	/**
	 * Get single product sales by location (country/state).
	 *
	 * Response (world):
	 * {
	 *   "range": "last-30",
	 *   "type": "world",
	 *   "data": { "US": 120.00, "GB": 60.00 },
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
	 *   "data": { "CA": 80.00, "NY": 40.00 },
	 *   "states_data": [...]
	 * }
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function product_sale_by_location( $request ) {
		$product_id = (int) $request->get_param( 'product_id' );
		$range      = $request->get_param( 'range' ) ?: 'last-30';

		if ( ! $product_id ) {
			$this->response_error( __( 'Product ID is required', 'easycommerce' ) );
			return;
		}

		$product = new Product( $product_id );
		if ( ! $product->exists() ) {
			$this->response_error( __( 'Product not found', 'easycommerce' ) );
			return;
		}

		$dates     = $this->resolve_dates( $range );
		$date_from = $dates['from'];
		$date_to   = $dates['to'] . ' 23:59:59';

		$cache_key = $this->generate_cache_key(
			'products_sale_by_location',
			array(
				'range'      => $range,
				'product_id' => $product_id,
			)
		);

		$result = $this->get_cached_or_set(
			$cache_key,
			function() use ( $date_from, $date_to, $product_id ) {
				return $this->get_product_sales_by_location( $product_id, $date_from, $date_to );
			},
			self::CACHE_DURATION_DAY
		);

		$result['range'] = $range;
		$result          = apply_filters( 'easycommerce_reports_product_sale_by_location', $result, $range, $product_id, $request );

		$this->response_success( $result );
	}

	/**
	 * Aggregate product-level revenue by country/state.
	 *
	 * Unlike get_orders_by_location() which sums o.total (the full order
	 * amount), this method uses oi.price * oi.quantity so that only the
	 * specific product's contribution is counted per location.
	 *
	 * @param int    $product_id Product ID to scope.
	 * @param string $date_from  Start datetime (Y-m-d).
	 * @param string $date_to    End datetime (Y-m-d H:i:s, with 23:59:59 appended by caller).
	 * @return array Same shape as get_orders_by_location() with type=world|states.
	 */
	private function get_product_sales_by_location( int $product_id, string $date_from, string $date_to ): array {
		$orders_db    = new Database( 'orders' );
		$orders_table = $orders_db->get_table();
		$meta_db      = new Database( 'order_meta' );
		$meta_table   = $meta_db->get_table();
		$items_db     = new Database( 'order_items' );
		$items_table  = $items_db->get_table();
		$statuses     = array( 'completed', 'processing', 'partially_refunded', 'refunded' );
		$escaped      = implode( "','", array_map( 'esc_sql', $statuses ) );

		$query = $orders_db->prepare(
			"SELECT om.meta_value,
				(oi.rate * oi.quantity) AS product_amount,
				o.customer_id
			FROM {$orders_table} o
			JOIN {$meta_table} om ON o.id = om.order_id AND om.meta_key = 'billing_address'
			JOIN {$items_table} oi ON o.id = oi.order_id AND oi.product_id = %d
			WHERE o.created_at >= %s AND o.created_at <= %s
			AND o.status IN ('{$escaped}')",
			$product_id,
			$date_from,
			$date_to
		);

		$countries      = array();
		$states         = array();
		$single_country = null;

		foreach ( $orders_db->exec( $query, \ARRAY_A ) as $row ) {
			$billing_address = maybe_unserialize( $row['meta_value'] );

			if ( is_string( $billing_address ) ) {
				$billing_address = maybe_unserialize( $billing_address );
			}

			if ( ! is_array( $billing_address ) || empty( $billing_address['country'] ) ) {
				continue;
			}

			$country = strtoupper( $billing_address['country'] );
			$amount  = (float) $row['product_amount'];

			if ( $single_country === null ) {
				$single_country = $country;
			} elseif ( $single_country !== $country ) {
				$single_country = false;
			}

			$countries[ $country ] = ( $countries[ $country ] ?? 0 ) + $amount;

			if ( ! empty( $billing_address['state'] ) ) {
				$state            = strtoupper( $billing_address['state'] );
				$states[ $state ] = ( $states[ $state ] ?? 0 ) + $amount;
			}
		}

		arsort( $countries );

		$all_countries  = \EasyCommerce\Models\Location::get_countries();
		$country_lookup = array();
		foreach ( $all_countries as $c ) {
			$country_lookup[ $c['iso2'] ] = $c;
		}

		$country_data  = array();
		$top_locations = array();

		foreach ( $countries as $code => $value ) {
			$info                  = $country_lookup[ $code ] ?? null;
			$country_data[ $code ] = array(
				'name'    => $info ? $info['name'] : $code,
				'numeric' => $info ? $info['numeric_code'] : '',
				'iso3'    => $info ? $info['iso3'] : '',
				'display' => easycommerce_price( $value ),
			);
		}

		foreach ( array_slice( $countries, 0, 6, true ) as $code => $value ) {
			$top_locations[] = array(
				'name'    => $country_data[ $code ]['name'] ?? $code,
				'code'    => $code,
				'value'   => $value,
				'display' => easycommerce_price( $value ),
			);
		}

		// Single-country mode: drill down to state level.
		if ( $single_country && count( $countries ) === 1 ) {
			$country_info  = $country_lookup[ $single_country ] ?? null;
			$country_iso3  = $country_info ? $country_info['iso3'] : '';
			$country_name  = $country_info ? $country_info['name'] : $single_country;

			arsort( $states );
			$states_data   = \EasyCommerce\Models\Location::get_states( $single_country );
			$state_lookup  = array();
			foreach ( $states_data as $sd ) {
				$state_lookup[ strtoupper( $sd['state_code'] ) ] = $sd['name'];
			}

			$top_locations  = array();
			$states_display = array();
			foreach ( array_slice( $states, 0, 5, true ) as $code => $value ) {
				$states_display[ $code ] = easycommerce_price( $value );
				$top_locations[]         = array(
					'name'    => $state_lookup[ $code ] ?? $code,
					'code'    => $code,
					'value'   => $value,
					'display' => easycommerce_price( $value ),
				);
			}

			return array(
				'type'           => 'states',
				'aggregate'      => 'sum',
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
			'type'          => 'world',
			'aggregate'     => 'sum',
			'data'          => $countries,
			'country_data'  => $country_data,
			'top_locations' => $top_locations,
		);
	}

	/**
	 * Get single product reviews with stats
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function single_product_reviews( $request ) {
		$product_id = (int) $request->get_param( 'product_id' );
		$limit      = (int) $request->get_param( 'limit' );
		$range      = $request->get_param( 'range' ) ?: 'last-30';

		if ( ! $product_id ) {
			$this->response_error( __( 'Product ID is required', 'easycommerce' ) );
			return;
		}

		$product = new Product( $product_id );
		if ( ! $product->exists() ) {
			$this->response_error( __( 'Product not found', 'easycommerce' ) );
			return;
		}

		if ( ! $limit || $limit < 1 ) {
			$limit = 6;
		}

		$result = $this->get_single_product_reviews_data( $product_id, $limit, $range );

		$this->response_success( $result );
	}

	/**
	 * Get single product reviews data
	 *
	 * @param int    $product_id The product ID.
	 * @param int    $limit      Number of reviews to return.
	 * @param string $range      The date range.
	 *
	 * @return array
	 */
	private function get_single_product_reviews_data( $product_id, $limit = 6, $range = 'last-30' ) {
		$dates         = $this->resolve_dates( $range );
		$date_query    = array(
			array(
				'after'     => $dates['from'],
				'before'    => $dates['to'] . ' 23:59:59',
				'inclusive' => true,
			),
		);

		$rating        = $this->get_product_rating( $product_id );
		$rating_count  = $this->get_product_review_count( $product_id, $date_query );
		$rating_counts = $this->get_product_rating_counts( $product_id, $date_query );
		$reviews       = $this->get_product_reviews( $product_id, $limit, $date_query );

		return array(
			'rating'        => $rating > 0 ? number_format( $rating, 1 ) : '0.0',
			'rating_count'  => number_format( $rating_count ),
			'rating_counts' => $rating_counts,
			'reviews'       => $reviews,
		);
	}

	/**
	 * Get product review count
	 *
	 * @param int   $product_id The product ID.
	 * @param array $date_query Optional WP_Comment_Query date_query array.
	 *
	 * @return int
	 */
	private function get_product_review_count( $product_id, $date_query = array() ) {
		$args = array(
			'post_id' => $product_id,
			'status'  => 'approve',
			'count'   => true,
		);

		if ( ! empty( $date_query ) ) {
			$args['date_query'] = $date_query;
		}

		return (int) get_comments( $args );
	}

	/**
	 * Get product rating counts by star
	 *
	 * @param int   $product_id The product ID.
	 * @param array $date_query Optional WP_Comment_Query date_query array.
	 *
	 * @return array
	 */
	private function get_product_rating_counts( $product_id, $date_query = array() ) {
		$args = array(
			'post_id' => $product_id,
			'status'  => 'approve',
		);

		if ( ! empty( $date_query ) ) {
			$args['date_query'] = $date_query;
		}

		$comments      = get_comments( $args );
		$rating_counts = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );

		foreach ( $comments as $comment ) {
			$rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
			if ( isset( $rating_counts[ $rating ] ) ) {
				$rating_counts[ $rating ]++;
			}
		}

		return $rating_counts;
	}

	/**
	 * Get product reviews
	 *
	 * @param int   $product_id The product ID.
	 * @param int   $limit      Number of reviews to return.
	 * @param array $date_query Optional WP_Comment_Query date_query array.
	 *
	 * @return array
	 */
	private function get_product_reviews( $product_id, $limit = 6, $date_query = array() ) {
		$args = array(
			'post_id' => $product_id,
			'status'  => 'approve',
			'number'  => $limit,
		);

		if ( ! empty( $date_query ) ) {
			$args['date_query'] = $date_query;
		}

		$comments = get_comments( $args );
		$reviews  = array();

		foreach ( $comments as $comment ) {
			$reviews[] = array(
				'date'   => date_i18n( 'F j, Y', strtotime( $comment->comment_date ) ),
				'name'   => $comment->comment_author,
				'avatar' => get_avatar_url( $comment->comment_author_email, array( 'size' => 48 ) ),
				'review' => wp_strip_all_tags( $comment->comment_content ),
				'rating' => (int) get_comment_meta( $comment->comment_ID, 'rating', true ),
			);
		}

		return $reviews;
	}
}

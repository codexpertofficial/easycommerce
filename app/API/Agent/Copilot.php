<?php
namespace EasyCommerce\API\Agent;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\Agent;
use EasyCommerce\API\Reports\Reports;
use EasyCommerce\Models\Coupon;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order as Order_Model;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Refund as Refund_Model;
use EasyCommerce\Models\Customer;

class Copilot extends Agent {

	protected $log_type = 'copilot';

	private $reports;

	public function __construct() {
		parent::__construct();
		$this->reports = new Reports();
	}

	// -------------------------------------------------------------------------
	// Tool dispatch
	// -------------------------------------------------------------------------

	protected function dispatch_tool( string $name, array $args ): array {
		switch ( $name ) {
			case 'create_product':
				return $this->tool_create_product( $args );
			case 'update_product':
				return $this->tool_update_product( $args );
			case 'get_store_overview':
				return $this->tool_get_store_overview( $args );
			case 'get_top_products':
				return $this->tool_get_top_products( $args );
			case 'list_orders':
				return $this->tool_list_orders( $args );
			case 'search_order':
				return $this->tool_search_order( $args );
			case 'list_products':
				return $this->tool_list_products( $args );
			case 'search_customer':
				return $this->tool_search_customer( $args );
			case 'update_order_status':
				return $this->tool_update_order_status( $args );
			case 'delete_product':
				return $this->tool_delete_product( $args );
			case 'update_product_stock':
				return $this->tool_update_product_stock( $args );
			case 'issue_refund':
				return $this->tool_issue_refund( $args );
			case 'create_coupon':
				return $this->tool_create_coupon( $args );
			case 'run_analytics_query':
				return $this->tool_run_analytics_query( $args );
			default:
				return array( 'error' => "Unknown tool: {$name}" );
		}
	}

	// -------------------------------------------------------------------------
	// Tool implementations
	// -------------------------------------------------------------------------

	private function tool_create_product( array $args ): array {
		$title       = sanitize_text_field( $args['title'] ?? '' );
		$price       = (float) ( $args['price'] ?? 0 );
		$stock       = isset( $args['stock'] ) ? (int) $args['stock'] : null;
		$sku         = sanitize_text_field( $args['sku'] ?? '' );
		$summary     = sanitize_textarea_field( $args['summary'] ?? '' );
		$description = sanitize_textarea_field( $args['description'] ?? '' );
		$categories  = array_map( 'sanitize_text_field', (array) ( $args['categories'] ?? array() ) );
		$status      = sanitize_text_field( $args['status'] ?? 'publish' );

		if ( empty( $title ) ) {
			return array( 'error' => 'title is required.' );
		}

		if ( $price <= 0 ) {
			return array( 'error' => 'price must be greater than zero.' );
		}

		if ( empty( $sku ) ) {
			$sku = sanitize_title( $title ) . '-' . time();
		}

		$args_create = array(
			'title'       => $title,
			'status'      => in_array( $status, array( 'publish', 'draft' ), true ) ? $status : 'publish',
			'summary'     => $summary,
			'description' => $description,
			'categories'  => $categories,
			'variations' => array(
				array(
					'name'           => 'Default',
					'sku'            => substr( $sku, 0, 100 ),
					'type'           => 'physical',
					'regular_price'  => $price,
					'sale_price'     => null,
					'stock_quantity' => $stock ?? 0,
					'status'         => 'publish',
					'stock_limit'    => 0,
					'attributes'     => array(),
					'meta'           => array(),
				),
			),
		);

		$product = new Product();
		$created = $product->create( $args_create );

		if ( is_wp_error( $created ) ) {
			return array( 'error' => $created->get_error_message() );
		}

		if ( ! $created ) {
			return array( 'error' => 'Failed to create product.' );
		}

		$product_data = array( 'id' => $product->get_id(), 'title' => $title );
		do_action( 'easycommerce_create_product', $product_data );
		do_action( 'easycommerce_log', array(
			'object'    => 'product',
			'action'    => 'create',
			'object_id' => $product->get_id(),
			'note'      => 'AI copilot created: ' . $title,
		) );

		return array(
			'success'    => true,
			'product_id' => $product->get_id(),
			'title'      => $title,
			'url'        => $product->get_url(),
			'message'    => "Product \"{$title}\" (#{$product->get_id()}) created successfully.",
		);
	}

	private function tool_update_product( array $args ): array {
		$product_id = (int) ( $args['product_id'] ?? 0 );

		if ( ! $product_id ) {
			return array( 'error' => 'product_id is required.' );
		}

		$product = new Product( $product_id );

		if ( ! $product->exists() ) {
			return array( 'error' => 'Product not found.' );
		}

		$updated = array();

		// Update core product fields if provided.
		$new_title  = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : null;
		$new_status = isset( $args['status'] ) ? sanitize_text_field( $args['status'] ) : null;
		$new_summary = isset( $args['summary'] ) ? sanitize_textarea_field( $args['summary'] ) : null;

		$needs_save = false;

		if ( $new_title !== null && $new_title !== $product->get_title() ) {
			$product->set_title( $new_title );
			$updated[]  = 'title';
			$needs_save = true;
		}

		if ( $new_status !== null && in_array( $new_status, array( 'publish', 'draft' ), true ) && $new_status !== $product->get_status() ) {
			$product->set_status( $new_status );
			$updated[]  = 'status';
			$needs_save = true;
		}

		if ( $needs_save ) {
			if ( ! $product->save() ) {
				return array( 'error' => 'Failed to save product.' );
			}
		}

		if ( $new_summary !== null ) {
			$product->set_summary( $new_summary );
			$updated[] = 'summary';
		}

		// Update variation price if provided.
		if ( isset( $args['price'] ) ) {
			$new_price    = (float) $args['price'];
			$variation_id = (int) ( $args['variation_id'] ?? 0 );

			if ( $new_price <= 0 ) {
				return array( 'error' => 'price must be greater than zero.' );
			}

			$vm = new Product_Variation();

			if ( $variation_id ) {
				$variation = new Product_Variation( $variation_id );
			} else {
				$vars      = $vm->get_by( $product_id, 'product_id' );
				$variation = ! empty( $vars ) ? $vars[0] : null;
			}

			if ( ! $variation || ! $variation->exists() ) {
				return array( 'error' => 'No variation found to update price on.' );
			}

			$variation->set_price( $new_price );
			$variation->set_sale_price( null );
			$variation->save();
			$updated[] = 'price';
		}

		if ( empty( $updated ) ) {
			return array( 'message' => 'Nothing to update — no recognised fields provided.' );
		}

		do_action( 'easycommerce_log', array(
			'object'    => 'product',
			'action'    => 'update',
			'object_id' => $product_id,
			'note'      => 'AI copilot updated: ' . implode( ', ', $updated ),
		) );

		return array(
			'success'    => true,
			'product_id' => $product_id,
			'updated'    => $updated,
			'message'    => 'Product #' . $product_id . ' updated: ' . implode( ', ', $updated ) . '.',
		);
	}

	private function tool_get_store_overview( array $args ): array {
		$range   = sanitize_text_field( $args['date_range'] ?? 'this-month' );
		$orders  = $this->reports->total_orders( $range );
		$count   = count( $orders );
		$sales   = $this->reports->total_sales( $range );
		$refunds = $this->reports->total_refunds( $range );
		$net     = $sales - $refunds;

		return array(
			'date_range'  => $range,
			'orders'      => $count,
			'sales'       => round( $sales, 2 ),
			'refunds'     => round( $refunds, 2 ),
			'net_revenue' => round( $net, 2 ),
			'currency'    => get_option( 'easycommerce_currency', 'USD' ),
		);
	}

	private function tool_get_top_products( array $args ): array {
		$range = sanitize_text_field( $args['date_range'] ?? 'this-month' );
		$limit = min( max( 1, (int) ( $args['limit'] ?? 5 ) ), 20 );

		$dates     = $this->reports->get_date_range( $range );
		$from_date = $dates[0];
		$to_date   = $dates[1];

		$db       = new Database( 'order_items' );
		$oi_table = $db->get_table();
		$o_table  = $db->get_wp_prefix() . 'ec_orders';

		$query = $db->prepare(
			"SELECT oi.product_id, SUM(oi.quantity) as units_sold, SUM(oi.price) as revenue
			FROM {$oi_table} oi
			INNER JOIN {$o_table} o ON oi.order_id = o.id
			WHERE o.status IN ('completed','processing','partially_refunded')
			AND DATE(o.created_at) BETWEEN %s AND %s
			GROUP BY oi.product_id
			ORDER BY units_sold DESC
			LIMIT %d",
			$from_date,
			$to_date,
			$limit
		);

		global $wpdb;
		$rows = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $rows ) ) {
			return array( 'found' => false, 'message' => 'No sales data for this period.' );
		}

		$out = array();
		foreach ( $rows as $row ) {
			$product = new Product( (int) $row['product_id'] );
			$out[]   = array(
				'product_id' => (int) $row['product_id'],
				'name'       => $product->exists() ? $product->get_title() : "Product #{$row['product_id']}",
				'units_sold' => (int) $row['units_sold'],
				'revenue'    => round( (float) $row['revenue'], 2 ),
			);
		}

		return array( 'date_range' => $range, 'found' => true, 'products' => $out );
	}

	private function tool_list_orders( array $args ): array {
		$status = sanitize_text_field( $args['status'] ?? '' );
		$limit  = min( max( 1, (int) ( $args['limit'] ?? 10 ) ), 50 );

		$filters = array( 'per_page' => $limit );
		if ( ! empty( $status ) ) {
			$filters['status'] = $status;
		}

		$result = Order_Model::list( $filters );
		$orders = $result['orders'] ?? array();

		if ( empty( $orders ) ) {
			return array( 'found' => false, 'message' => 'No orders found.' );
		}

		$out = array();
		foreach ( $orders as $o ) {
			$out[] = array(
				'order_id'      => $o['id'] ?? '',
				'status'        => $o['status'] ?? '',
				'total'         => $o['total'] ?? '',
				'customer_name' => $o['customer_name'] ?? '',
				'created_at'    => $o['created_at'] ?? '',
			);
		}

		return array( 'found' => true, 'count' => count( $out ), 'orders' => $out );
	}

	private function tool_search_order( array $args ): array {
		$order_id = (int) ( $args['order_id'] ?? 0 );

		if ( ! $order_id ) {
			return array( 'error' => 'order_id is required.' );
		}

		$order = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			return array( 'error' => 'Order not found.' );
		}

		$billing = $order->get_meta( 'billing_address' ) ?: array();
		$items   = array();

		foreach ( $order->get_items() as $item ) {
			$items[] = array(
				'name'       => $item->name ?? '',
				'quantity'   => (int) ( $item->quantity ?? 1 ),
				'line_total' => (float) ( $item->price ?? 0 ),
			);
		}

		return array(
			'order_id'       => $order_id,
			'status'         => $order->get_status(),
			'total'          => $order->get_total(),
			'customer_name'  => trim( ( $billing['first_name'] ?? '' ) . ' ' . ( $billing['last_name'] ?? '' ) ),
			'customer_email' => $billing['email'] ?? '',
			'phone'          => $billing['phone'] ?? '',
			'address'        => trim( implode( ', ', array_filter( array(
				$billing['address_1'] ?? '',
				$billing['city'] ?? '',
				$billing['country'] ?? '',
			) ) ) ),
			'created_at'     => $order->get_created_at(),
			'items'          => $items,
		);
	}

	private function tool_list_products( array $args ): array {
		$search = sanitize_text_field( $args['search'] ?? '' );
		$limit  = min( max( 1, (int) ( $args['limit'] ?? 10 ) ), 50 );

		$filters = array( 'per_page' => $limit );
		if ( ! empty( $search ) ) {
			$filters['search'] = $search;
		}

		$result   = Product::list( $filters );
		$products = $result['products'] ?? array();

		if ( empty( $products ) ) {
			return array( 'found' => false, 'message' => 'No products found.' );
		}

		$out = array();
		foreach ( $products as $product ) {
			$out[] = array(
				'id'     => $product->get_id(),
				'name'   => $product->get_title(),
				'price'  => $product->get_price( false ),
				'status' => $product->get_status(),
				'stock'  => $product->get_stock(),
				'url'    => $product->get_url(),
			);
		}

		return array( 'found' => true, 'count' => count( $out ), 'products' => $out );
	}

	private function tool_search_customer( array $args ): array {
		$email = sanitize_email( $args['email'] ?? '' );

		if ( empty( $email ) ) {
			return array( 'error' => 'email is required.' );
		}

		$wp_user = get_user_by( 'email', $email );

		if ( ! $wp_user ) {
			return array( 'error' => 'No customer found with this email.' );
		}

		$customer = new Customer( $wp_user->ID );
		$orders   = $customer->get_orders( 5 );
		$recent   = array();

		foreach ( $orders as $o ) {
			$recent[] = array(
				'order_id' => $o->get_id(),
				'status'   => $o->get_status(),
				'total'    => $o->get_total(),
			);
		}

		return array(
			'name'          => $customer->get_name(),
			'email'         => $email,
			'phone'         => $customer->get_phone(),
			'order_count'   => (int) $customer->get_order_count(),
			'total_spent'   => (float) $customer->get_total_spent(),
			'recent_orders' => $recent,
		);
	}

	private function tool_update_order_status( array $args ): array {
		$order_id = (int) ( $args['order_id'] ?? 0 );
		$status   = sanitize_text_field( $args['status'] ?? '' );

		$valid = array( 'pending', 'processing', 'completed', 'cancelled', 'on-hold', 'refunded', 'partially_refunded' );

		if ( ! $order_id ) {
			return array( 'error' => 'order_id is required.' );
		}

		if ( ! in_array( $status, $valid, true ) ) {
			return array( 'error' => 'Invalid status. Valid values: ' . implode( ', ', $valid ) );
		}

		$order = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			return array( 'error' => 'Order not found.' );
		}

		$previous = $order->get_status();
		$order->set_status( $status );

		do_action( 'easycommerce_order_email', $status, $order_id );
		do_action( 'easycommerce_log', array(
			'object'    => 'order',
			'action'    => 'status_change',
			'object_id' => $order_id,
			'note'      => "AI copilot: {$previous} → {$status}",
		) );

		return array(
			'success'         => true,
			'order_id'        => $order_id,
			'previous_status' => $previous,
			'new_status'      => $status,
			'message'         => "Order #{$order_id} status updated from {$previous} to {$status}.",
		);
	}

	private function tool_delete_product( array $args ): array {
		$product_id = (int) ( $args['product_id'] ?? 0 );

		if ( ! $product_id ) {
			return array( 'error' => 'product_id is required.' );
		}

		$product = new Product( $product_id );

		if ( ! $product->exists() ) {
			return array( 'error' => 'Product not found.' );
		}

		$name = $product->get_title();

		if ( ! $product->is_deletable() ) {
			return array( 'error' => "Product \"{$name}\" cannot be deleted — it has existing orders." );
		}

		$deleted = $product->delete();

		if ( ! $deleted ) {
			return array( 'error' => 'Failed to delete product.' );
		}

		do_action( 'easycommerce_delete_product', $product_id );
		do_action( 'easycommerce_log', array(
			'object'    => 'product',
			'action'    => 'delete',
			'object_id' => $product_id,
			'note'      => "AI copilot deleted: {$name}",
		) );

		return array(
			'success'    => true,
			'product_id' => $product_id,
			'message'    => "Product \"{$name}\" (#{$product_id}) has been deleted.",
		);
	}

	private function tool_update_product_stock( array $args ): array {
		$variation_id = (int) ( $args['variation_id'] ?? 0 );
		$stock        = (int) ( $args['stock'] ?? 0 );

		if ( ! $variation_id ) {
			return array( 'error' => 'variation_id is required.' );
		}

		$variation = new Product_Variation( $variation_id );

		if ( ! $variation->exists() ) {
			return array( 'error' => 'Product variation not found.' );
		}

		$previous = (int) $variation->get_stock();
		$variation->set_stock_quantity( $stock );
		$variation->save();

		return array(
			'success'        => true,
			'variation_id'   => $variation_id,
			'previous_stock' => $previous,
			'new_stock'      => $stock,
			'message'        => "Stock updated from {$previous} to {$stock}.",
		);
	}

	private function tool_create_coupon( array $args ): array {
		$name        = sanitize_text_field( $args['name'] ?? '' );
		$code        = strtoupper( sanitize_text_field( $args['code'] ?? '' ) );
		$type        = sanitize_text_field( $args['type'] ?? '' );
		$offer       = $args['offer'] ?? null;
		$min_spend   = isset( $args['min_spend'] ) ? (float) $args['min_spend'] : null;
		$max_spend   = isset( $args['max_spend'] ) ? (float) $args['max_spend'] : null;
		$start_date  = sanitize_text_field( $args['start_date'] ?? '' );
		$end_date    = sanitize_text_field( $args['end_date'] ?? '' );
		$product_ids = array_map( 'intval', (array) ( $args['product_ids'] ?? array() ) );

		if ( empty( $name ) || empty( $code ) || empty( $type ) ) {
			return array( 'error' => 'name, code, and type are required.' );
		}

		$valid_types = array( 'percentage', 'fixed' );
		if ( ! in_array( $type, $valid_types, true ) ) {
			return array( 'error' => 'type must be "percentage" or "fixed".' );
		}

		if ( $offer === null || (float) $offer <= 0 ) {
			return array( 'error' => 'offer must be greater than zero.' );
		}

		if ( $type === 'percentage' && (float) $offer > 100 ) {
			return array( 'error' => 'percentage offer cannot exceed 100.' );
		}

		// Check for duplicate code.
		$existing = Coupon::get( $code );
		if ( $existing ) {
			return array( 'error' => "Coupon code \"{$code}\" already exists." );
		}

		$rules = array();
		if ( $min_spend !== null && $min_spend > 0 ) {
			$rules[] = array( 'type' => 'min_spend', 'value' => $min_spend );
		}
		if ( $max_spend !== null && $max_spend > 0 ) {
			$rules[] = array( 'type' => 'max_spend', 'value' => $max_spend );
		}
		if ( ! empty( $start_date ) ) {
			$rules[] = array( 'type' => 'start_date', 'value' => $start_date );
		}
		if ( ! empty( $end_date ) ) {
			$rules[] = array( 'type' => 'end_date', 'value' => $end_date );
		}
		if ( ! empty( $product_ids ) ) {
			$product_ids  = array_values( array_filter( $product_ids ) );
			$product_list = array();
			foreach ( $product_ids as $pid ) {
				$p = new Product( $pid );
				if ( ! $p->exists() ) {
					return array( 'error' => "Product ID {$pid} not found." );
				}
				$product_list[] = array(
					'id'    => $pid,
					'title' => $p->get_title(),
				);
			}
			$rules[] = array(
				'type'  => 'products',
				'value' => $product_list,
			);
		}

		$coupon    = new Coupon();
		$coupon_id = $coupon->create( array(
			'name'   => $name,
			'code'   => $code,
			'type'   => $type,
			'offer'  => (float) $offer,
			'active' => 1,
			'rules'  => $rules,
		) );

		if ( ! $coupon_id ) {
			return array( 'error' => 'Failed to create coupon.' );
		}

		do_action( 'easycommerce_log', array(
			'object'    => 'coupon',
			'action'    => 'create',
			'object_id' => $coupon_id,
			'note'      => "AI copilot created coupon \"{$code}\" ({$type}: {$offer})",
		) );

		$summary = $type === 'percentage' ? "{$offer}% off" : easycommerce_price( $offer ) . ' off';
		if ( ! empty( $product_ids ) ) {
			$summary .= ' (restricted to product IDs: ' . implode( ', ', $product_ids ) . ')';
		}

		return array(
			'success'     => true,
			'coupon_id'   => $coupon_id,
			'code'        => $code,
			'type'        => $type,
			'offer'       => (float) $offer,
			'product_ids' => $product_ids,
			'rules'       => $rules,
			'message'     => "Coupon \"{$code}\" created ({$summary}).",
		);
	}

	private function tool_issue_refund( array $args ): array {
		$order_id = (int) ( $args['order_id'] ?? 0 );
		$amount   = (float) ( $args['amount'] ?? 0 );
		$reason   = sanitize_text_field( $args['reason'] ?? '' );

		if ( ! $order_id ) {
			return array( 'error' => 'order_id is required.' );
		}

		if ( $amount <= 0 ) {
			return array( 'error' => 'amount must be greater than zero.' );
		}

		$order = new Order_Model( $order_id );

		if ( ! $order->exists() ) {
			return array( 'error' => 'Order not found.' );
		}

		$allowed_statuses = array( 'completed', 'processing', 'partially_refunded' );
		if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
			return array( 'error' => 'Refunds only allowed for completed, processing, or partially_refunded orders. Current status: "' . $order->get_status() . '".' );
		}

		$order_total    = (float) $order->get_total();
		$total_refunded = (float) $order->get_total_refunded();
		$due_amount     = $order_total - $total_refunded;

		if ( $amount > $due_amount ) {
			return array( 'error' => 'Refund amount ' . easycommerce_price( $amount ) . ' exceeds the refundable balance of ' . easycommerce_price( $due_amount ) . '.' );
		}

		$refund_args = array(
			'order_id'        => $order_id,
			'amount'          => $amount,
			'currency'        => get_option( 'easycommerce_currency', 'USD' ),
			'reason'          => $reason,
			'status'          => 'approved',
			'payment_gateway' => $order->get_payment_method(),
			'refunded_by'     => get_current_user_id(),
		);

		do_action( 'easycommerce_before_create_refund', $refund_args, null );

		$refund  = new Refund_Model();
		$created = $refund->create( $refund_args );

		if ( ! $created ) {
			return array( 'error' => 'Failed to create refund record.' );
		}

		do_action( 'easycommerce_after_create_refund', $refund->get_id(), $refund_args, null );

		$refund_data = array(
			'id'       => $refund->get_id(),
			'order_id' => $order_id,
			'amount'   => $amount,
		);
		do_action( 'easycommerce_create_refund', $refund_data );

		$updated_total_refunded = $total_refunded + $amount;
		$new_order_status       = $updated_total_refunded >= $order_total ? 'refunded' : 'partially_refunded';
		$order->set_status( $new_order_status );

		do_action( 'easycommerce_order_email', $new_order_status, $order_id );
		do_action( 'easycommerce_log', array(
			'object'    => 'order',
			'action'    => 'refund',
			'object_id' => $order_id,
			'meta'      => array( 'refund_id' => $refund->get_id(), 'refund_amount' => $amount ),
			'note'      => 'AI copilot: ' . easycommerce_price( $amount ) . ' refunded' . ( $reason ? " — {$reason}" : '' ),
		) );

		return array(
			'success'          => true,
			'refund_id'        => $refund->get_id(),
			'order_id'         => $order_id,
			'amount_refunded'  => round( $amount, 2 ),
			'new_order_status' => $new_order_status,
			'message'          => easycommerce_price( $amount ) . ' refunded on order #' . $order_id . '. Order status updated to "' . $new_order_status . '".',
		);
	}

	private function tool_run_analytics_query( array $args ): array {
		$sql = trim( $args['sql'] ?? '' );

		if ( empty( $sql ) ) {
			return array( 'error' => 'sql is required.' );
		}

		// Strip SQL comments before any checks to prevent bypass via comment injection.
		$sql_stripped = preg_replace( '/--[^\n]*/', '', $sql );
		$sql_stripped = preg_replace( '/\/\*.*?\*\//s', '', $sql_stripped ?? '' );
		$sql_stripped = trim( $sql_stripped ?? '' );

		if ( stripos( $sql_stripped, 'SELECT' ) !== 0 ) {
			return array( 'error' => 'Only SELECT queries are permitted.' );
		}

		// Block multi-statement injection (SELECT ...; DELETE ...).
		if ( preg_match( '/;\s*(?:INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|TRUNCATE|REPLACE|GRANT|REVOKE)/i', $sql_stripped ) ) {
			return array( 'error' => 'Only a single SELECT statement is permitted.' );
		}

		// Extract every table name that appears after FROM or JOIN.
		preg_match_all( '/(?:FROM|JOIN)\s+`?(\w+)`?/i', $sql_stripped, $matches );
		$table_names = $matches[1] ?? array();

		if ( empty( $table_names ) ) {
			return array( 'error' => 'No tables found in query.' );
		}

		// Allowlist: only wp_ec_* tables are permitted.
		global $wpdb;
		$ec_prefix = strtolower( $wpdb->prefix . 'ec_' );
		foreach ( $table_names as $table ) {
			if ( strpos( strtolower( $table ), $ec_prefix ) !== 0 ) {
				return array( 'error' => "Access to \"{$table}\" is not allowed. Only {$wpdb->prefix}ec_* tables are permitted." );
			}
		}

		// Ensure query is capped even if model omits LIMIT.
		// Execute $sql_stripped (comments removed) so an appended LIMIT cannot be
		// commented out by a trailing -- in the original query.
		if ( ! preg_match( '/\bLIMIT\b/i', $sql_stripped ) ) {
			$sql_stripped = rtrim( $sql_stripped, '; ' ) . ' LIMIT 100';
		}

		$db      = new Database();
		$results = $db->exec( $sql_stripped, ARRAY_A );

		if ( $db->db->last_error ) {
			return array( 'error' => 'Query failed: ' . $db->db->last_error );
		}

		if ( empty( $results ) ) {
			return array( 'found' => false, 'message' => 'Query returned no results.' );
		}

		return array(
			'found'   => true,
			'count'   => count( $results ),
			'results' => array_slice( $results, 0, 100 ),
		);
	}

	// -------------------------------------------------------------------------
	// Tool definitions & system prompt
	// -------------------------------------------------------------------------

	protected function get_tools(): array {
		return array(
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'create_product',
					'description' => 'Create a new simple product with a single default variation. Always confirm the product details with the admin before calling.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'title'      => array( 'type' => 'string', 'description' => 'Product name.' ),
							'price'      => array( 'type' => 'number', 'description' => 'Regular price (must be > 0).' ),
							'stock'      => array( 'type' => 'integer', 'description' => 'Initial stock quantity (default: 0).' ),
							'sku'        => array( 'type' => 'string', 'description' => 'SKU code (optional — auto-generated from title if omitted).' ),
							'summary'     => array( 'type' => 'string', 'description' => 'Short product description shown in listings (optional).' ),
							'description' => array( 'type' => 'string', 'description' => 'Full long product description (optional).' ),
							'categories'  => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => 'Category names to assign (optional).' ),
							'status'     => array( 'type' => 'string', 'description' => 'publish (default) or draft.' ),
						),
						'required'   => array( 'title', 'price' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'update_product',
					'description' => 'Update one or more fields on an existing product: title, status, summary, and/or price. For price, updates the first (or specified) variation. Always confirm changes with the admin before calling.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'product_id'   => array( 'type' => 'integer', 'description' => 'The product ID to update.' ),
							'title'        => array( 'type' => 'string', 'description' => 'New product title (optional).' ),
							'status'       => array( 'type' => 'string', 'description' => 'New status: publish or draft (optional).' ),
							'summary'      => array( 'type' => 'string', 'description' => 'New short description (optional).' ),
							'price'        => array( 'type' => 'number', 'description' => 'New regular price — clears any sale price (optional).' ),
							'variation_id' => array( 'type' => 'integer', 'description' => 'Specific variation ID to update price on (optional — defaults to first variation).' ),
						),
						'required'   => array( 'product_id' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_store_overview',
					'description' => 'Get a high-level summary of store performance: total orders, sales, refunds, and net revenue for a date range.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'date_range' => array(
								'type'        => 'string',
								'description' => 'Date range: today, yesterday, this-week, last-7, this-month, last-month, last-30, this-year, all-time. Default: this-month.',
							),
						),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'get_top_products',
					'description' => 'Get the best-selling products by units sold for a given date range.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'date_range' => array( 'type' => 'string', 'description' => 'Date range (same options as get_store_overview). Default: this-month.' ),
							'limit'      => array( 'type' => 'integer', 'description' => 'Number of products to return (1–20). Default: 5.' ),
						),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'list_orders',
					'description' => 'List recent orders, optionally filtered by status.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'status' => array( 'type' => 'string', 'description' => 'Filter by status: pending, processing, completed, cancelled, on-hold, refunded. Omit for all.' ),
							'limit'  => array( 'type' => 'integer', 'description' => 'Number of orders (1–50). Default: 10.' ),
						),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'search_order',
					'description' => 'Get full details of a specific order by ID: customer info, items, totals, status.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id' => array( 'type' => 'integer', 'description' => 'The order ID.' ),
						),
						'required'   => array( 'order_id' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'list_products',
					'description' => 'List products in the catalog, optionally searching by name.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'search' => array( 'type' => 'string', 'description' => 'Optional keyword to filter products by name.' ),
							'limit'  => array( 'type' => 'integer', 'description' => 'Number of products (1–50). Default: 10.' ),
						),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'search_customer',
					'description' => 'Look up a customer by email address. Returns profile, order count, total spent, and recent orders.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'email' => array( 'type' => 'string', 'description' => 'Customer email address.' ),
						),
						'required'   => array( 'email' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'update_order_status',
					'description' => 'Change the status of an order. Always confirm the change with the admin before calling this tool. Fires order email notification automatically.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id' => array( 'type' => 'integer', 'description' => 'The order ID to update.' ),
							'status'   => array( 'type' => 'string', 'description' => 'New status: pending, processing, completed, cancelled, on-hold, refunded, partially_refunded.' ),
						),
						'required'   => array( 'order_id', 'status' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'delete_product',
					'description' => 'Delete (trash) a product by ID. Cannot delete products that have existing orders. Always confirm with the admin before calling.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'product_id' => array( 'type' => 'integer', 'description' => 'The product ID to delete.' ),
						),
						'required'   => array( 'product_id' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'update_product_stock',
					'description' => 'Update the stock quantity of a specific product variation.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'variation_id' => array( 'type' => 'integer', 'description' => 'The variation ID (use list_products to find variation IDs).' ),
							'stock'        => array( 'type' => 'integer', 'description' => 'New stock quantity.' ),
						),
						'required'   => array( 'variation_id', 'stock' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'issue_refund',
					'description' => 'Issue a manual refund on a completed, processing, or partially_refunded order. Validates the amount against the refundable balance, records the refund, updates the order status to refunded or partially_refunded, and fires the refund email. Always confirm the amount and reason with the admin before calling.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'order_id' => array( 'type' => 'integer', 'description' => 'The order ID to refund.' ),
							'amount'   => array( 'type' => 'number', 'description' => 'Refund amount (must be > 0 and ≤ the refundable balance).' ),
							'reason'   => array( 'type' => 'string', 'description' => 'Reason for the refund (optional but recommended).' ),
						),
						'required'   => array( 'order_id', 'amount' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'create_coupon',
					'description' => 'Create a new discount coupon (percentage or fixed amount). Optionally restrict to specific products, set a minimum spend, and/or set an expiry date. Always confirm the details with the admin before calling.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'name'        => array( 'type' => 'string', 'description' => 'Human-readable coupon name (e.g. "Summer Sale 20%").' ),
							'code'        => array( 'type' => 'string', 'description' => 'Coupon code customers enter at checkout (auto-uppercased). Must be unique.' ),
							'type'        => array( 'type' => 'string', 'description' => '"percentage" for percentage off or "fixed" for flat amount off.' ),
							'offer'       => array( 'type' => 'number', 'description' => 'Discount value: 1–100 for percentage type, or flat amount for fixed type.' ),
							'product_ids' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ), 'description' => 'Restrict coupon to these product IDs only (optional). Use list_products to find IDs.' ),
							'min_spend'   => array( 'type' => 'number', 'description' => 'Minimum order subtotal required to use the coupon (optional).' ),
							'max_spend'   => array( 'type' => 'number', 'description' => 'Maximum order subtotal allowed to use the coupon (optional).' ),
							'start_date'  => array( 'type' => 'string', 'description' => 'Date coupon becomes active, YYYY-MM-DD format (optional).' ),
							'end_date'    => array( 'type' => 'string', 'description' => 'Expiry date in YYYY-MM-DD format (optional).' ),
						),
						'required'   => array( 'name', 'code', 'type', 'offer' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'run_analytics_query',
					'description' => 'Run a raw SELECT SQL query against the store database for complex analytics not covered by other tools. ONLY wp_ec_* tables are permitted (e.g. wp_ec_orders, wp_ec_order_items, wp_ec_products, wp_ec_product_variations). Any reference to other tables will be rejected. Cap results at 100 rows. Prefer other tools first — only use this for cross-period comparisons, multi-table joins, or aggregations that tools cannot produce.',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'sql' => array(
								'type'        => 'string',
								'description' => 'A valid SELECT SQL query. Must start with SELECT. No semicolons at the end.',
							),
						),
						'required'   => array( 'sql' ),
					),
				),
			),
		);
	}

	protected function get_system_prompt(): string {
		$store_name = get_bloginfo( 'name' );
		return "You are an AI store management copilot for {$store_name}. You help the store admin understand performance, manage orders, and update catalog data. " .

			"ANALYTICS: Use get_store_overview for revenue/orders summary. Use get_top_products to find best sellers. Default date_range to 'this-month' unless the admin specifies otherwise. " .

			"ORDERS: Use list_orders to browse orders (filter by status when helpful). Use search_order for full details on a specific order. " .

			"CATALOG: Use list_products to browse or search the product catalog. Use create_product to add a new simple product — collect title and price at minimum; confirm all details before calling. Use update_product to change a product's title, status, summary, or price — confirm the exact changes before calling. " .

			"CUSTOMERS: Use search_customer to look up a customer by email. " .

			"ACTIONS — always confirm before executing: " .
			"(1) update_order_status: ask the admin to confirm the status change before calling. " .
			"(2) delete_product: warn that deletion is irreversible for non-orderable products, confirm before calling. " .
			"(3) update_product_stock: confirm the new quantity before calling. " .
			"(4) issue_refund: show the admin the order total, already-refunded amount, and refundable balance; ask them to confirm the exact amount and reason before calling. Refunds cannot be undone. " .
			"(5) create_coupon: collect name, code, type (percentage/fixed), offer value, and optional product_ids/min_spend/end_date; if the admin wants product-specific restrictions use list_products to find the IDs first; confirm all details before calling. " .

			"COMPLEX ANALYTICS: Use run_analytics_query for cross-period comparisons, multi-table joins, or any aggregation the other tools cannot produce (e.g. \"products in top 5 this month but not last month\", \"customers who bought X but never Y\"). Only wp_ec_* tables are allowed — never reference wp_users, wp_usermeta, wp_options, or any non-ec table. Tables: wp_ec_orders, wp_ec_order_items, wp_ec_products, wp_ec_product_variations, wp_ec_customers, wp_ec_coupons, wp_ec_refunds. Always prefer structured tools first — fall back to run_analytics_query only when necessary. " .

			"Be concise and data-driven. When presenting numbers, include context (e.g. date range, comparison). For lists, summarise rather than dumping raw data unless the admin asks for detail.";
	}
}

<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Log as Log_Model;
use EasyCommerce\Models\Order;
use EasyCommerce\Helpers\Utility;

class Log extends API {

	/**
	 * List logs with optional filters.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function list( $request ) {
		$object     = $request->get_param( 'object' );
		$action     = $request->get_param( 'action' );
		$type       = $request->get_param( 'type' );
		$object_id  = $request->get_param( 'object_id' );
		$user_id    = $request->get_param( 'user_id' );
		$from_date  = $request->get_param( 'from_date' );
		$to_date    = $request->get_param( 'to_date' );
		$is_public  = $request->get_param( 'is_public' );
		$page       = $request->get_param( 'page' ) ?: 1;
		$per_page   = $request->get_param( 'per_page' ) ?: 10;
		$sort       = $request->get_param( 'sort' ) ?: 'desc';
		$filters    = array();

		if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'manager' ) ) {
			$order = new Order( (int) $object_id );

			if ( ! $order->exists() || (int) $order->get_customer_id() !== get_current_user_id() ) {
				$this->response_error( __( 'You are not allowed to view these logs.', 'easycommerce' ), 403 );
			}

			$object    = 'order';
			$is_public = 1;
			$user_id   = null;
			$from_date = $order->get_created_at();
		}

		if ( ! in_array( strtolower( $sort ), array( 'asc', 'desc' ) ) ) {
			$sort = 'desc';
		}

		if ( ! empty( $object ) ) {
			$filters['object'] = $object;
		}

		if ( ! empty( $action ) ) {
			$filters['action'] = $action;
		}

		if ( ! empty( $type ) ) {
			$filters['type'] = $type;
		}

		if ( ! empty( $object_id ) ) {
			$filters['object_id'] = $object_id;
		}

		if ( ! empty( $user_id ) ) {
			$filters['user_id'] = $user_id;
		}

		if ( ! empty( $from_date ) ) {
			$filters['from_date'] = $from_date;
		}

		if ( ! empty( $to_date ) ) {
			$filters['to_date'] = $to_date;
		}

		if ( $is_public !== null ) {
			$filters['is_public'] = $is_public;
		}

		$result = Log_Model::list( $filters, $per_page, ( $page - 1 ) * $per_page, $sort );

		if ( empty( $result['logs'] ) ) {
			$this->response_success( array( 'message' => __( 'No logs found.', 'easycommerce' ) ) );
		}

		$formatted_logs = array_map(
			function ( $log ) {
				return array(
					'id'         => $log->id,
					'object'     => $log->object,
					'action'     => $log->action,
					'object_id'  => $log->object_id,
					'user_id'    => $log->user_id,
					'note'       => $log->note,
					'ip_address' => $log->ip_address,
					'seen'       => $log->seen,
					'type'       => $log->type ?? 'info',
					'meta'   	 => $log->meta,
					'is_public'  => $log->is_public ?? 1,
					'created_at' => Utility::format_date( $log->created_at ),
				);
			},
			$result['logs']
		);

		$total_pages = ceil( $result['total'] / $per_page );

		$this->response_success(
			array(
				'logs'       => $formatted_logs,
				'total'      => $result['total'],
				'page'       => $page,
				'per_page'   => $per_page,
				'total_pages' => $total_pages,
			),
			200
		);
	}

	/**
	 * Get a single log by ID.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get( $request ) {
		$id = $request->get_param( 'id' );

		$log_model = new Log_Model();
		$log = $log_model->get( $id );

		if ( ! $log ) {
			$this->response_error( __( 'Log not found.', 'easycommerce' ), 404 );
		}

		$this->response_success( $log );
	}

	/**
	 * Add a new log entry.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function add( $request ) {
		$object     = $request->get_param( 'object' );
		$action     = $request->get_param( 'action' );
		$object_id  = $request->get_param( 'object_id' );
		$user_id    = $request->get_param( 'user_id' );
		$note       = $request->get_param( 'note' );
		$ip_address = $request->get_param( 'ip_address' );
		$seen       = $request->get_param( 'seen' );
		$type       = $request->get_param( 'type' );
		$is_public  = $request->get_param( 'is_public' );

		$data = array(
			'object'     => $object,
			'action'     => $action,
			'object_id'  => $object_id,
			'user_id'    => $user_id,
			'note'       => $note,
			'ip_address' => $ip_address,
			'seen'       => $seen,
			'type'       => $type,
			'is_public'  => $is_public ?? 1,
		);

		$log_model = new Log_Model();
		$id        = $log_model->add( $data );

		if ( ! $id ) {
			$this->response_error( __( 'Failed to add log.', 'easycommerce' ), 500 );
		}

		$this->response_success(
			array(
				'message' => __( 'Log added successfully.', 'easycommerce' ),
				'id'      => $id,
			)
		);
	}

	/**
	 * Delete a log entry.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$id = $request->get_param( 'id' );

		$log_model = new Log_Model();
		$deleted   = $log_model->delete( $id );

		if ( ! $deleted ) {
			$this->response_error( __( 'Failed to delete log.', 'easycommerce' ), 500 );
		}

		$this->response_success(
			array(
				'message' => __( 'Log deleted successfully.', 'easycommerce' ),
			)
		);
	}
}
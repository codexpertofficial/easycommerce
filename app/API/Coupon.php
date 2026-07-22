<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Coupon as Coupon_Model;

class Coupon extends API {

	/**
	 * Retrieve all coupons or a specific coupon by ID.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_all( $request ) {
		$active       = $request->get_param( 'active' ) ?? null;
		$per_page     = $request->get_param( 'per_page' ) ?: 10;
		$page         = $request->get_param( 'page' ) ?: 1;
		$from_date    = $request->get_param( 'from' );
		$to_date      = $request->get_param( 'to' );
		$search       = $request->get_param( 'search' );
		$coupons 	  = Coupon_Model::list( 
			array( 
				'active' 	=> $active ,
				'per_page' 	=> $per_page,
				'page' 		=> $page,
				'from_date'	=> $from_date,
				'to_date' 	=> $to_date,
				'search' 	=> $search
			)
		
		);

		/**
		 * Filters the list of coupons.
		 *
		 * @since 1.9
		 * @param array $coupons The coupons data.
		 * @param WP_REST_Request $request The request object.
		 */
		$coupons = apply_filters( 'easycommerce_get_coupons', $coupons, $request );

		return $this->response_success( $coupons );
	}

	/**
	 * Create a new coupon.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create( $request ) {
		$data = array(
			'name'      => $request->get_param( 'name' ),
			'code'      => $request->get_param( 'code' ),
			'type'      => $request->get_param( 'type' ),
			'offer'     => $request->get_param( 'offer' ),
			'active'    => $request->get_param( 'active' ) ? 1 : 0,
			'rules'     => $request->get_param( 'rules' ),
		);

		/**
		 * Filters the coupon data before creating.
		 *
		 * @since 1.9
		 * @param array $data The coupon data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_create_coupon_data', $data, $request );

		/**
		 * Fires before creating a coupon.
		 *
		 * @since 1.9
		 * @param array $data The coupon data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_create_coupon', $data, $request );

		$coupon    = new Coupon_Model();
		$coupon_id = $coupon->create( $data );

		/**
		 * Fires after creating a coupon.
		 *
		 * @since 1.9
		 * @param int $coupon_id The coupon ID.
		 * @param array $data The coupon data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_create_coupon', $coupon_id, $data, $request );

		/**
		 * Logs the coupon creation event.
		 */
		// translators: %s: coupon code.
		do_action( 'easycommerce_log', array( 'object' => 'coupon', 'action' => 'create', 'object_id' => $coupon_id, 'note' => sprintf( __( 'Coupon %s created', 'easycommerce' ), $data['code'] ) ) );

		if ( ! $coupon_id ) {
			return $this->response_error( array( 'message' => __( 'Failed to create coupon.', 'easycommerce' ) ) );
		}

		return $this->response_success(
			array(
				'message' => __( 'Coupon created successfully.', 'easycommerce' ),
				'id'      => $coupon_id,
			)
		);
	}

	/**
	 * Retrieve details of a specific coupon by ID.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_single( $request ) {
		$id = $request->get_param( 'id' );

		// Validate if ID is provided
		if ( ! $id ) {
			return $this->response_error( array( 'message' => __( 'Coupon ID is required.', 'easycommerce' ) ) );
		}

		// Retrieve the coupon details using the Coupon model
		$coupon = Coupon_Model::get( $id );

		// Check if coupon exists
		if ( ! $coupon ) {
			return $this->response_error( array( 'message' => __( 'Coupon not found.', 'easycommerce' ) ) );
		}

		// Return the coupon data
		/**
		 * Filters the single coupon data.
		 *
		 * @since 1.9
		 * @param object $coupon The coupon data.
		 * @param WP_REST_Request $request The request object.
		 */
		$coupon = apply_filters( 'easycommerce_get_single_coupon', $coupon, $request );

		return $this->response_success( $coupon );
	}

	/**
	 * Update an existing coupon.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( $request ) {
		$id = $request->get_param( 'id' );
		if ( ! $id ) {
			return $this->response_error( array( 'message' => __( 'Coupon ID is required.', 'easycommerce' ) ) );
		}
		$coupon = new Coupon_Model( $id );
		if ( ! $coupon->exists() ) {
			return $this->response_error( array( 'message' => __( 'Coupon not found.', 'easycommerce' ) ) );
		}
	
		$data = [];
	
		if ( null !== $request->get_param( 'name' ) ) {
			$data['name'] = $request->get_param( 'name' );
		}
		if ( null !== $request->get_param( 'code' ) ) {
			$data['code'] = $request->get_param( 'code' );
		}
		if ( null !== $request->get_param( 'type' ) ) {
			$data['type'] = $request->get_param( 'type' );
		}
		if ( null !== $request->get_param( 'offer' ) ) {
			$data['offer'] = $request->get_param( 'offer' );
		}
		if ( null !== $request->get_param( 'active' ) ) {
			$data['active'] = $request->get_param( 'active' ) ? 1 : 0;
		}
		if ( null !== $request->get_param( 'rules' ) ) {
			$data['rules'] = $request->get_param( 'rules' );
		}
	
		if ( empty( $data ) ) {
			return $this->response_error( array( 'message' => __( 'No valid fields to update.', 'easycommerce' ) ) );
		}

		/**
		 * Filters the coupon update data.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param int $id The coupon ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_update_coupon_data', $data, $id, $request );

		/**
		 * Fires before updating a coupon.
		 *
		 * @since 1.9
		 * @param int $id The coupon ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_coupon', $id, $data, $request );

		$updated = $coupon->update( $data );

		/**
		 * Fires after updating a coupon.
		 *
		 * @since 1.9
		 * @param int $id The coupon ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_coupon', $id, $data, $request );
		if ( ! $updated ) {
			return $this->response_error( array( 'message' => __( 'Failed to update coupon.', 'easycommerce' ) ) );
		}
		return $this->response_success(
			array(
				'id'      => $id,
				'message' => __( 'Coupon updated successfully.', 'easycommerce' ),
			)
		);
	}
	/**
	 * Delete a coupon.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id ) {
			return $this->response_error( array( 'message' => __( 'Coupon ID is required.', 'easycommerce' ) ) );
		}

		$coupon = new Coupon_Model( $id );

		if ( ! $coupon->exists() ) {
			return $this->response_error( array( 'message' => __( 'Coupon not found.', 'easycommerce' ) ) );
		}

		/**
		 * Fires before deleting a coupon.
		 *
		 * @since 1.9
		 * @param int $id The coupon ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_coupon', $id, $request );

		$deleted = $coupon->delete();

		/**
		 * Fires after deleting a coupon.
		 *
		 * @since 1.9
		 * @param int $id The coupon ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_coupon', $id, $request );

		/**
		 * Logs the coupon deletion event.
		 */
		// translators: %s: coupon code.
		do_action( 'easycommerce_log', array( 'object' => 'coupon', 'action' => 'delete', 'object_id' => $id, 'note' => sprintf( __( 'Coupon %s deleted', 'easycommerce' ), $coupon->get_code() ) ) );

		if ( ! $deleted ) {
			return $this->response_error( array( 'message' => __( 'Failed to delete coupon.', 'easycommerce' ) ) );
		}

		return $this->response_success(
			array(
				'id'      => $id,
				'message' => __( 'Coupon deleted successfully.', 'easycommerce' ),
			)
		);
	}

	/**
	 * Bulk delete coupons.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function bulk_delete( $request ) {
		$coupon_ids = $request->get_param( 'coupon_ids' );

		if ( empty( $coupon_ids ) || ! is_array( $coupon_ids ) ) {
			return $this->response_error( __( 'Invalid coupon IDs.', 'easycommerce' ) );
		}

		$deleted = true;

		foreach ( $coupon_ids as $id ) {
			$coupon = new Coupon_Model( $id );
			/**
			 * Fires before a coupon is deleted.
			 *
			 * @since 1.9
			 * @param int $id The coupon ID.
			 */
			do_action( 'easycommerce_before_delete_coupon', $id );

			$result = $coupon->delete();

			if ( ! $result ) {
				$deleted = false;
				continue;
			}

			/**
			 * Fires after a coupon is deleted.
			 *
			 * @since 1.9
			 * @param int $id The coupon ID.
			 */
			do_action( 'easycommerce_delete_coupon', $id );
		}

		if ( ! $deleted ) {
			return $this->response_error( __( 'Failed to delete some coupons.', 'easycommerce' ) );
		}

		return $this->response_success( __( 'coupons deleted successfully.', 'easycommerce' ) );
	}

	/**
	 * Bulk update the status of coupons.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function bulk_status_update( $request ) {
		$coupon_ids = $request->get_param( 'coupon_ids' );
		$status     = $request->get_param( 'status' );
	
		if ( empty( $coupon_ids ) || ! is_array( $coupon_ids ) ) {
			return $this->response_error( __( 'Invalid coupon IDs.', 'easycommerce' ) );
		}
	
		$status_value = $status === 'active' ? 1 : 0;
	
		$updated_all = true;
	
		foreach ( $coupon_ids as $id ) {
			$coupon = new Coupon_Model( $id );
	
			$updated = $coupon->update( array(
				'active' => $status_value,
			) );
	
			if ( ! $updated ) {
				$updated_all = false;
				continue;
			}
	
			/**
			 * Fires after a coupon's status is updated.
			 *
			 * @since 1.9
			 * @param int    $id     Coupon ID.
			 * @param string $status New status.
			 */
			do_action( 'easycommerce_coupon_status_updated', $id, $status );
		}
	
		if ( ! $updated_all ) {
			return $this->response_error( __( 'Failed to update some coupons.', 'easycommerce' ) );
		}
	
		return $this->response_success( __( 'Coupons status updated successfully.', 'easycommerce' ) );
	}	
}

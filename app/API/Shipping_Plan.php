<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Shipping_Plan as Shipping_Model;

class Shipping_Plan extends API {

	/**
	 * Retrieve all shipping plans or a specific plan by ID.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_all( $request ) {
		$id = $request->get_param( 'id' );

		if ( $id ) {
			$plan = Shipping_Model::get( $id );
			if ( ! $plan ) {
				return $this->response_error( array( 'message' => __( 'Shipping plan not found.', 'easycommerce' ) ) );
			}

			/**
			 * Filters the single shipping plan.
			 *
			 * @since 1.9
			 * @param object $plan The shipping plan.
			 * @param int $id The plan ID.
			 * @param WP_REST_Request $request The request object.
			 */
			$plan = apply_filters( 'easycommerce_get_shipping_plan', $plan, $id, $request );

			return $this->response_success( $plan );
		} else {
			$plans = Shipping_Model::list();

			/**
			 * Filters the list of shipping plans.
			 *
			 * @since 1.9
			 * @param array $plans The shipping plans.
			 * @param WP_REST_Request $request The request object.
			 */
			$plans = apply_filters( 'easycommerce_list_shipping_plans', $plans, $request );

			return $this->response_success( $plans );
		}
	}

	/**
	 * Create a new shipping plan.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create( $request ) {
		$data = array(
			'name'             => $request->get_param( 'name' ),
			'description'      => $request->get_param( 'description' ),
			'active'           => $request->get_param( 'active' ) ? 1 : 0,
			'taxable'          => $request->get_param( 'taxable' ) ? 1 : 0,
			'calculation_base' => $request->get_param( 'calculation_base' ),
			'regions'          => $request->get_param( 'regions' ),
			'methods'          => $request->get_param( 'methods' ),
		);

		/**
		 * Filters the shipping plan data before creating.
		 *
		 * @since 1.9
		 * @param array $data The shipping plan data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_create_shipping_plan_data', $data, $request );

		/**
		 * Fires before creating a shipping plan.
		 *
		 * @since 1.9
		 * @param array $data The shipping plan data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_create_shipping_plan', $data, $request );

		// Instantiate a new Shipping_Model and create the plan
		$plan    = new Shipping_Model();
		$plan_id = $plan->create( $data );

		/**
		 * Fires after creating a shipping plan.
		 *
		 * @since 1.9
		 * @param int $plan_id The shipping plan ID.
		 * @param array $data The shipping plan data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_create_shipping_plan', $plan_id, $data, $request );

		if ( ! $plan_id ) {
			return $this->response_error( array( 'message' => __( 'Failed to create shipping plan.', 'easycommerce' ) ) );
		}

		return $this->response_success(
			array(
				'message' => __( 'Shipping plan created successfully.', 'easycommerce' ),
				'id'      => $plan_id,
			)
		);
	}

	/**
	 * Update an existing shipping plan.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id ) {
			return $this->response_error( array( 'message' => __( 'Shipping plan ID is required.', 'easycommerce' ) ) );
		}

		$plan = new Shipping_Model( $id );

		if ( ! $plan->exists() ) {
			return $this->response_error( array( 'message' => __( 'Shipping plan not found.', 'easycommerce' ) ) );
		}

		$data = array(
			'name'             => $request->get_param( 'name' ),
			'description'      => $request->get_param( 'description' ),
			'active'           => $request->get_param( 'active' ) ? 1 : 0,
			'taxable'          => $request->get_param( 'taxable' ) ? 1 : 0,
			'calculation_base' => $request->get_param( 'calculation_base' ),
			'regions'          => $request->get_param( 'regions' ),
			'methods'          => $request->get_param( 'methods' ),
		);

		/**
		 * Filters the shipping plan update data.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param int $id The shipping plan ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_update_shipping_plan_data', $data, $id, $request );

		/**
		 * Fires before updating a shipping plan.
		 *
		 * @since 1.9
		 * @param int $id The shipping plan ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_shipping_plan', $id, $data, $request );

		$updated = $plan->update( $data );

		/**
		 * Fires after updating a shipping plan.
		 *
		 * @since 1.9
		 * @param int $id The shipping plan ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_shipping_plan', $id, $data, $request );

		if ( ! $updated ) {
			return $this->response_error( array( 'message' => __( 'Failed to update shipping plan.', 'easycommerce' ) ) );
		}

		return $this->response_success( __( 'Shipping plan updated successfully.', 'easycommerce' ) );
	}

	/**
	 * Delete a shipping plan.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id ) {
			return $this->response_error( array( 'message' => __( 'Shipping plan ID is required.', 'easycommerce' ) ) );
		}

		$plan = new Shipping_Model( $id );

		if ( ! $plan->exists() ) {
			return $this->response_error( array( 'message' => __( 'Shipping plan not found.', 'easycommerce' ) ) );
		}

		/**
		 * Fires before deleting a shipping plan.
		 *
		 * @since 1.9
		 * @param int $id The shipping plan ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_shipping_plan', $id, $request );

		$deleted = $plan->delete();

		/**
		 * Fires after deleting a shipping plan.
		 *
		 * @since 1.9
		 * @param int $id The shipping plan ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_shipping_plan', $id, $request );

		if ( ! $deleted ) {
			return $this->response_error( array( 'message' => __( 'Failed to delete shipping plan.', 'easycommerce' ) ) );
		}

		return $this->response_success( __( 'Shipping plan deleted successfully.', 'easycommerce' ) );
	}

	/**
	 * Retrieve shipping plans available for a specific region.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_by_location( $request ) {
		$region = $request->get_param( 'region' );

		if ( empty( $region ) ) {
			return $this->response_error( array( 'message' => __( 'Region code is required.', 'easycommerce' ) ) );
		}

		$plans = Shipping_Model::get_by_location( $region );

		if ( empty( $plans ) ) {
			return $this->response_error( array( 'message' => __( 'No shipping plans available for this region.', 'easycommerce' ) ) );
		}

		/**
		 * Filters the shipping plans by location.
		 *
		 * @since 1.9
		 * @param array $plans The shipping plans.
		 * @param string $region The region.
		 * @param WP_REST_Request $request The request object.
		 */
		$plans = apply_filters( 'easycommerce_get_shipping_plans_by_location', $plans, $region, $request );

		return $this->response_success( $plans );
	}
}

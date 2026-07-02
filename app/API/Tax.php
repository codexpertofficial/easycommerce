<?php
namespace EasyCommerce\API;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Tax as Tax_Model;

/**
 * Tax API class
 */
class Tax extends API {

	public function list_classes( $request ) {
		$classes = Tax_Model::list_classes();

		/**
		 * Filters the tax classes.
		 *
		 * @since 1.9
		 * @param array $classes The tax classes.
		 * @param WP_REST_Request $request The request object.
		 */
		$classes = apply_filters( 'easycommerce_list_tax_classes', $classes, $request );

		$this->response_success(
			array(
				'message' => __( 'Tax classes found.', 'easycommerce' ),
				'classes' => $classes,
			)
		);
	}

	/**
	 * Create a tax class with associated rates.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function create_class( $request ) {
		$data = array(
			'name'        => $request->get_param( 'name' ),
			'description' => $request->get_param( 'description' ),
			'status'      => $request->get_param( 'status' ),
			'rates'       => $request->get_param( 'rates' ),
		);

		/**
		 * Filters the tax class data before creating.
		 *
		 * @since 1.9
		 * @param array $data The tax class data.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_create_tax_class_data', $data, $request );

		/**
		 * Fires before creating a tax class.
		 *
		 * @since 1.9
		 * @param array $data The tax class data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_create_tax_class', $data, $request );

		$tax_model 	 = new Tax_Model();
		$name_exists = $tax_model->name_exists( $data['name'] );

		if ( $name_exists ) {
			$this->response_error( array( 'message' => __( 'A class with the same name already exists', 'easycommerce' ) ) );
		}

		$class_id    = $tax_model->create_class( $data );

		/**
		 * Fires after creating a tax class.
		 *
		 * @since 1.9
		 * @param int $class_id The tax class ID.
		 * @param array $data The tax class data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_create_tax_class', $class_id, $data, $request );

		if ( ! $class_id ) {
			$this->response_error( array( 'message' => __( 'Failed to create tax class.', 'easycommerce' ) ) );
		}

		$this->response_success(
			array(
				'message' => __( 'Tax class created successfully.', 'easycommerce' ),
				'id'      => $class_id,
			)
		);
	}

	public function get_class( $request ) {
		$id         = $request->get_param( 'id' );
		$tax_model  = new Tax_Model();		

		if ( is_null( $class = $tax_model->get( $id ) ) ) {
			$this->response_success(
				array(
					'message' => __( 'Tax class not found.', 'easycommerce' ),
				)
			);
		}

		/**
		 * Filters the tax class data.
		 *
		 * @since 1.9
		 * @param object $class The tax class.
		 * @param int $id The tax class ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$class = apply_filters( 'easycommerce_get_tax_class', $class, $id, $request );

		$this->response_success(
			array(
				'message' => __( 'Tax class found.', 'easycommerce' ),
				'class'   => $class,
			)
		);
	}

	/**
	 * Update a tax class with associated rates.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update_class( $request ) {
		$id = $request->get_param( 'id' );

		$data = array(
			'name'        => $request->get_param( 'name' ),
			'description' => $request->get_param( 'description' ),
			'status'      => $request->get_param( 'status' ),
			'rates'       => $request->get_param( 'rates' ),
		);

		/**
		 * Filters the tax class update data.
		 *
		 * @since 1.9
		 * @param array $data The update data.
		 * @param int $id The tax class ID.
		 * @param WP_REST_Request $request The request object.
		 */
		$data = apply_filters( 'easycommerce_update_tax_class_data', $data, $id, $request );

		/**
		 * Fires before updating a tax class.
		 *
		 * @since 1.9
		 * @param int $id The tax class ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_update_tax_class', $id, $data, $request );

		$tax_model  = new Tax_Model();
		$updated    = $tax_model->update_class( $id, $data );

		if ( ! $updated ) {
			$this->response_error( array( 'message' => __( 'Failed to update tax class.', 'easycommerce' ) ) );
		}

		/**
		 * Fires after updating a tax class.
		 *
		 * @since 1.9
		 * @param int $id The tax class ID.
		 * @param array $data The update data.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_update_tax_class', $id, $data, $request );

		$this->response_success(
			array(
				'message' => __( 'Tax class updated successfully.', 'easycommerce' ),
				'id'      => $updated,
			)
		);
	}

	/**
	 * Delete a tax class and its associated rates.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete_class( $request ) {
		$id         = $request->get_param( 'id' );

		/**
		 * Fires before deleting a tax class.
		 *
		 * @since 1.9
		 * @param int $id The tax class ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_delete_tax_class', $id, $request );

		$tax_model  = new Tax_Model();
		$deleted    = $tax_model->delete_class( $id );

		if ( ! $deleted ) {
			$this->response_error( array( 'message' => __( 'Failed to delete tax class.', 'easycommerce' ) ) );
		}

		/**
		 * Fires after deleting a tax class.
		 *
		 * @since 1.9
		 * @param int $id The tax class ID.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_delete_tax_class', $id, $request );

		$this->response_success( __( 'Tax class deleted successfully.', 'easycommerce' ) );
	}

	/**
	 * Retrieve matching tax CSV files for the given countries and return them in a JSON response.
	 *
	 * This REST API endpoint uses the Tax_Model to search the plugin's /taxes folder
	 * for CSV files matching the provided country codes. Only existing files are returned.
	 *
	 * @param WP_REST_Request $request The current REST API request containing 'countries' parameter.
	 * @return WP_REST_Response JSON response containing an array of matched country codes.
	 */
	public function get_tax_files( $request ) {
		 $countries = $request->get_param( 'countries' );

		if ( empty( $countries ) || ! is_array( $countries ) ) {
			return rest_ensure_response( [] );
		}

		$tax_model 	= new Tax_Model();
		$matches 	= $tax_model->get_tax_files_by_countries( $countries );

		/**
		 * Filters the tax files.
		 *
		 * @since 1.9
		 * @param array $matches The matched files.
		 * @param array $countries The countries.
		 * @param WP_REST_Request $request The request object.
		 */
		$matches = apply_filters( 'easycommerce_get_tax_files', $matches, $countries, $request );

		return $this->response_success( $matches );
	}

	/**
	 * Retrieve tax rates from CSV and return them in a JSON response.
	 *
	 * This endpoint uses the Tax_Model to read tax rates from the CSV file
	 * and formats them for API consumption.
	 *
	 * @param WP_REST_Request $request The current REST API request.
	 * @return WP_REST_Response JSON response containing tax rates or an error message.
	 */
	public function get_tax_rates( $request ) {
		$country = $request->get_param('country');

		if ( empty( $country ) ) {
			return $this->response_error( array(
				'message' => __( 'Country parameter is required.', 'easycommerce' )
			) );
		}

		$tax_model  = new Tax_Model();
		$rates      = $tax_model->get_country_tax_rates_from_csv( $country );

		if ( empty( $rates ) ) {
			return $this->response_error( array(
				'message' => __( 'No tax rates found.', 'easycommerce' )
			) );
		}

		/**
		 * Filters the tax rates.
		 *
		 * @since 1.9
		 * @param array $rates The tax rates.
		 * @param string $country The country.
		 * @param WP_REST_Request $request The request object.
		 */
		$rates = apply_filters( 'easycommerce_get_tax_rates', $rates, $country, $request );

		return $this->response_success( $rates );
	}
}

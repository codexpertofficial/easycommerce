<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Abstracts\Model;

/**
 * Shipping Plan Class
 * Handles shipping plan-related operations in the database.
 */
class Shipping_Plan extends Model {

	/**
	 * @var int Shipping Plan ID
	 */
	protected $id;

	/**
	 * @var string Shipping plan name
	 */
	protected $name;

	/**
	 * @var string Shipping plan description
	 */
	protected $description;

	/**
	 * @var bool Active status (1 = active, 0 = inactive)
	 */
	protected $active = 1;

	/**
	 * @var bool Taxable status (1 = active, 0 = inactive)
	 */
	protected $taxable = 0;

	/**
	 * @var string Calculation base (price, weight, quantity)
	 */
	protected $calculation_base = 'price';

	/**
	 * @var bool Shipping plan existence flag
	 */
	protected $exists = false;

	protected $table = 'shipping_plans';

	/**
	 * Constructor for the Shipping_Plan class.
	 *
	 * @param int|null $id Optional. The shipping plan ID.
	 */
	public function __construct( $id = null ) {
		parent::__construct();

		if ( $id && $plan = $this->db->get_by_id( $id ) ) {
			$this->id               = $id;
			$this->name             = $plan->name;
			$this->description      = $plan->description;
			$this->active           = $plan->active;
			$this->taxable          = $plan->taxable;
			$this->calculation_base = $plan->calculation_base;
			$this->exists           = true;
		}
	}

	/**
	 * Check if the shipping plan exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Retrieve basic information for a specific shipping plan.
	 *
	 * @param int $id The shipping plan ID.
	 * @return array|null Shipping plan details or null if not found.
	 */
	public static function get( $id ) {
		// Initialize an instance of the Shipping_Plan class
		$plan = new self( $id );

		// Check if the plan exists
		if ( ! $plan->exists() ) {
			return null;
		}

		// Return basic information for the plan
		return array(
			'id'               => $plan->get_id(),
			'name'             => $plan->get_name(),
			'description'      => $plan->get_description(),
			'active'           => $plan->is_active(),
			'taxable'          => $plan->is_taxable(),
			'calculation_base' => $plan->get_calculation_base(),
			'methods'          => $plan->get_methods(),
			'regions'          => $plan->get_regions(),
		);
	}

	/**
	 * Get the shipping plan ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the name of the shipping plan.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the description of the shipping plan.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the active status of the shipping plan.
	 *
	 * @return bool
	 */
	public function is_active() {
		return (bool) $this->active;
	}

	/**
	 * Get the taxable status of the shipping plan.
	 *
	 * @return bool
	 */
	public function is_taxable() {
		return (bool) $this->taxable;
	}

	/**
	 * Get the calculation base for the shipping plan.
	 *
	 * @return string
	 */
	public function get_calculation_base() {
		return $this->calculation_base;
	}

	/**
	 * Set the status of the shipping plan.
	 *
	 * @param bool $status Active status (1 = active, 0 = inactive).
	 */
	public function set_status( $status ) {
		$this->update( array( 'active' => $status ) );
	}

	/**
	 * Create a new shipping plan with associated regions and methods.
	 *
	 * @param array $args Shipping plan data, including 'regions' and 'methods'.
	 * @return bool|int Shipping Plan ID on success, false on failure.
	 */
	public function create( $args ) {
		if ( ! isset( $args['name'] ) || ! isset( $args['calculation_base'] ) ) {
			return false;
		}

		// Prepare data for the shipping plan
		$data = array(
			'name'             => $args['name'],
			'description'      => $args['description'] ?? '',
			'active'           => $args['active'] ?? 1,
			'taxable'          => $args['taxable'] ?? 1,
			'calculation_base' => $args['calculation_base'],
		);

		// Insert the shipping plan and get its ID
		$plan_id = $this->db->insert_row( $data );

		if ( $plan_id ) {
			$this->id = $plan_id;

			// Add regions to the shipping plan if provided
			if ( ! empty( $args['regions'] ) && is_array( $args['regions'] ) ) {
				foreach ( $args['regions'] as $region ) {
					$this->add_region( $region );
				}
			}

			// Add methods (price ranges) to the shipping plan if provided
			if ( ! empty( $args['methods'] ) && is_array( $args['methods'] ) ) {
				foreach ( $args['methods'] as $method ) {
					if ( isset( $method['name'], $method['min'], $method['max'], $method['cost'], $method['min_unit'], $method['max_unit'] ) ) {
						$this->add_method(
							array(
								'name'     => $method['name'],
								'min'      => $method['min'],
								'min_unit' => $method['min_unit'] ?? 'kg',
								'max'      => $method['max'],
								'max_unit' => $method['max_unit'] ?? 'kg',
								'cost'     => $method['cost'],
							)
						);
					}
				}
			}

			return $plan_id;
		}

		return false;
	}

	/**
	 * Update the shipping plan and its associated regions and methods.
	 *
	 * @param array $data Updated shipping plan data.
	 * @return bool True on success, false on failure.
	 */
	public function update( $data ) {
		if ( ! $this->exists ) {
			return false;
		}

		// Update basic fields
		$fields = array(
			'name'             => $data['name'] ?? $this->name,
			'description'      => $data['description'] ?? $this->description,
			'active'           => isset( $data['active'] ) ? $data['active'] : $this->active,
			'taxable'          => isset( $data['taxable'] ) ? $data['taxable'] : $this->taxable,
			'calculation_base' => $data['calculation_base'] ?? $this->calculation_base,
		);

		$basic_update = $this->db->update_row( $this->id, $fields );

		// Update regions
		if ( isset( $data['regions'] ) && is_array( $data['regions'] ) ) {
			$this->update_regions( $data['regions'] );
		}

		// Update methods
		if ( isset( $data['methods'] ) && is_array( $data['methods'] ) ) {
			$this->update_methods( $data['methods'] );
		}

		return true;
	}

	/**
	 * Update the regions associated with the shipping plan.
	 *
	 * @param array $new_regions Array of new region data.
	 */
	protected function update_regions( $new_regions ) {
		$regions_db = new Database( 'shipping_plan_regions' );

		// Remove all existing regions
		$existing_regions = $this->get_regions();
		foreach ( $existing_regions as $region ) {
			$regions_db->delete_row( $region->id );
		}

		// Add new regions
		foreach ( $new_regions as $region ) {
			$this->add_region( $region );
		}
	}

	/**
	 * Update the methods associated with the shipping plan.
	 *
	 * @param array $new_methods Array of new method data.
	 */
	protected function update_methods( $new_methods ) {
		$methods_db = new Database( 'shipping_plan_methods' );

		// Remove all existing methods
		$existing_methods = $this->get_methods();
		foreach ( $existing_methods as $method ) {
			$methods_db->delete_row( $method->id );
		}

		// Add new methods
		foreach ( $new_methods as $method ) {
			$this->add_method( $method );
		}
	}

	/**
	 * Delete the shipping plan.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if ( ! $this->exists ) {
			return false;
		}

		return $this->db->delete_row( $this->id );
	}

	/**
	 * Get all methods (price ranges) for the shipping plan.
	 *
	 * @return array List of shipping plan methods.
	 */
	public function get_methods() {
		// $methods_db = new Database( 'shipping_plan_methods' );
		// return $methods_db->get_rows( array( 'plan_id' => $this->id ) );

		$methods_db = new Database( 'shipping_plan_methods' );
		$methods = $methods_db->get_rows( array( 'plan_id' => $this->id ) );
		
		usort($methods, function($a, $b) {
			return $a->id - $b->id;
		});
		
		return $methods;
	}

	/**
	 * Add a method (price range) to the shipping plan.
	 *
	 * @param array $method_data Method data (name, min, min_unit, max, max_unit, cost).
	 * @return bool|int Method ID on success, false on failure.
	 */
	public function add_method( $method_data ) {
		$methods_db = new Database( 'shipping_plan_methods' );

		$data = array(
			'plan_id'  => $this->id,
			'name'     => $method_data['name'],
			'min_unit' => $method_data['min_unit'] ?? 'kg',
			'min'      => $method_data['min'],
			'max_unit' => $method_data['max_unit'] ?? 'kg',
			'max'      => $method_data['max'],
			'cost'     => $method_data['cost'],
		);

		return $methods_db->insert_row( $data );
	}

	/**
	 * Update an existing method in the shipping plan.
	 *
	 * @param int   $method_id The method ID.
	 * @param array $method_data Updated method data.
	 * @return bool
	 */
	public function update_method( $method_id, $method_data ) {
		$methods_db = new Database( 'shipping_plan_methods' );

		$data = array(
			'name'     => $method_data['name'],
			'min_unit' => $method_data['min_unit'] ?? 'kg',
			'min'      => $method_data['min'],
			'max_unit' => $method_data['max_unit'] ?? 'kg',
			'max'      => $method_data['max'],
			'cost'     => $method_data['cost'],
		);

		return $methods_db->update_row( $method_id, $data );
	}

	/**
	 * Get all regions assigned to the shipping plan.
	 *
	 * @return array List of regions.
	 */
	public function get_regions() {

		$regions_db = new Database( 'shipping_plan_regions' );
		$regions = $regions_db->get_rows( array( 'plan_id' => $this->id ) );
		
		usort($regions, function($a, $b) {
			return $a->id - $b->id;
		});
		
		return $regions;
	}

	/**
	 * Add a region to the shipping plan.
	 *
	 * @param string $region_code Standardized region code.
	 * @return bool|int Region ID on success, false on failure.
	 */
	public function add_region( $region ) {
		$regions_db = new Database( 'shipping_plan_regions' );

		$data = array(
			'plan_id'     => $this->id,
			'region_code' => $region['country'] . '-' . $region['state'] . '-' . $region['city'],
			'zip_code'    => $region['zip_code'] ?? ''
		);

		return $regions_db->insert_row( $data );
	}

	/**
	 * Update an existing region associated with the shipping plan.
	 *
	 * @param int   $region_id The ID of the region to update.
	 * @param array $region_data Data to update the region with (e.g., region_code).
	 * @return bool
	 */
	protected function update_region( $region_id, $region_data ) {
		$regions_db = new Database( 'shipping_plan_regions' );

		$data = array(
			'region_code' => $region_data['region_code'], // Update region code if needed
		);

		return $regions_db->update_row( $region_id, $data );
	}

	/**
	 * Static method to list shipping plans with optional filters.
	 *
	 * @param array $args Arguments that may include 'per_page', 'page', 'active'.
	 * @return array List of shipping plans and pagination info.
	 */
	public static function list( $args = array() ) {
		$per_page = isset( $args['per_page'] ) ? (int) $args['per_page'] : 10;
		$page     = isset( $args['page'] ) ? (int) $args['page'] : 1;
		$active   = isset( $args['active'] ) ? $args['active'] : null;

		$db = new Database( 'shipping_plans' );

		$where = array();
		if ( $active !== null ) {
			$where['active'] = $active;
		}

		$total_plans = $db->get_count( $where );
		$plans       = $db->get_rows( $where, $per_page, ( $page - 1 ) * $per_page );

		$formatted_plans = array_map(
			function ( $plan_data ) {
				$plan = new self( $plan_data->id );

				return array(
					'id'               => $plan->get_id(),
					'name'             => $plan->get_name(),
					'description'      => $plan->get_description(),
					'active'           => $plan->is_active(),
					'calculation_base' => $plan->get_calculation_base(),
					'methods'          => $plan->get_methods(),
					'regions'          => $plan->get_regions(),
				);
			},
			$plans
		);

		return array(
			'plans'       => $formatted_plans,
			'total'       => $total_plans,
			'per_page'    => $per_page,
			'page'        => $page,
			'total_pages' => ceil( $total_plans / $per_page ),
		);
	}

	/**
	 * Retrieve shipping plans available for a specific region code.
	 *
	 * @param string $region_code The standardized region code (e.g., "NG-LA").
	 * @param int    $limit Number of plans to return
	 *
	 * @return array List of shipping plans available for the given region.
	 */
	public static function get_by_location( $region_code, $limit = -1 ) {
		$regions_db = new Database( 'shipping_plan_regions' );

		// Retrieve all plan IDs that are associated with the given region code
		$plan_ids = $regions_db->get_rows( array( 'region_code' => $region_code ), $limit );

		if ( empty( $plan_ids ) ) {
			return array();
		}

		return $plans = array_map(
			function ( $plan_id ) {
				$plan = new self( $plan_id );

				return array(
					'id'               => $plan->get_id(),
					'name'             => $plan->get_name(),
					'description'      => $plan->get_description(),
					'active'           => $plan->is_active(),
					'calculation_base' => $plan->get_calculation_base(),
					'methods'          => $plan->get_methods(),
					'regions'          => $plan->get_regions(),
				);
			},
			wp_list_pluck( $plan_ids, 'plan_id' )
		);
	}

    public static function get_by_location_address( $country, $state, $city, $zip_code = '' ) {
		$regions_db = new Database( 'shipping_plan_regions' );
				
		$location_patterns = array();
		
		if ( ! empty( $country ) && ! empty( $state ) && ! empty( $city ) && ! empty( $zip_code ) ) {
			$location_patterns[] = array(
				'region_code' => $country . '-' . $state . '-' . $city,
				'zip_code' => $zip_code,
			);
		}
		
		if ( ! empty( $country ) && ! empty( $state ) && ! empty( $city ) ) {
			$location_patterns[] = array(
				'region_code' => $country . '-' . $state . '-' . $city,
				'zip_code' => '',
			);
		}
		
		if ( ! empty( $country ) && ! empty( $state ) && ! empty( $zip_code ) ) {
			$location_patterns[] = array(
				'region_code' => $country . '-' . $state . '-',
				'zip_code' => $zip_code,
			);
		}
		
		if ( ! empty( $country ) && ! empty( $state ) ) {
			$location_patterns[] = array(
				'region_code' => $country . '-' . $state . '-',
				'zip_code' => '',
			);
		}
		
		if ( ! empty( $country ) && ! empty( $zip_code ) ) {
			$location_patterns[] = array(
				'region_code' => $country . '--',
				'zip_code' => $zip_code,
			);
		}
		
		if ( ! empty( $country ) ) {
			$location_patterns[] = array(
				'region_code' => $country . '--',
				'zip_code' => '',
			);
		}
		
		$location_patterns[] = array(
			'region_code' => '--',
			'zip_code' => '',
		);
				
		foreach ( $location_patterns as $index => $pattern ) {
			$query_conditions = array( 
				'region_code' => $pattern['region_code'],
				'zip_code' => $pattern['zip_code']
			);
						
			$plan_ids = $regions_db->get_rows( $query_conditions );
						
			if ( ! empty( $plan_ids ) ) {
				return array_map(
					function ( $plan_id ) {
						$plan = new self( $plan_id );
						
						return array(
							'id'               => $plan->get_id(),
							'name'             => $plan->get_name(),
							'description'      => $plan->get_description(),
							'active'           => $plan->is_active(),
							'calculation_base' => $plan->get_calculation_base(),
							'methods'          => $plan->get_methods(),
							'regions'          => $plan->get_regions(),
						);
					},
					wp_list_pluck( $plan_ids, 'plan_id' )
				);
			}
		}
		
		return array();
	}
	/**
	 * Static method to get a shipping method by its ID.
	 *
	 * @param int $method_id The ID of the shipping method.
	 * @return object|null The shipping method data if found, otherwise null.
	 */
	public static function get_method_by_id( $method_id ) {
		$methods_db = new Database( 'shipping_plan_methods' );

		// Retrieve the method by its ID
		$method = $methods_db->get_by_id( $method_id );

		// Return the method data if found, otherwise null
		return $method ? $method : null;
	}

	public static function get_shipping_plan_by_id( $method_id ) {
		global $wpdb;

		$methods_table = $wpdb->prefix . 'ec_shipping_plan_methods';
		$plans_table   = $wpdb->prefix . 'ec_shipping_plans';

		$query = $wpdb->prepare(
			"SELECT m.*, p.taxable
			FROM {$methods_table} m
			LEFT JOIN {$plans_table} p ON p.id = m.plan_id
			WHERE m.id = %d",
			$method_id
		);

		return $wpdb->get_row( $query );
	}
}

<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

class Tax {

	/**
	 * @var int Tax class ID.
	 */
	protected $id;

	/**
	 * @var string Tax class name.
	 */
	protected $name;

	/**
	 * @var string Tax class description.
	 */
	protected $description;

	/**
	 * @var bool Tax class status (1 = active, 0 = inactive).
	 */
	protected $status = 1;

	/**
	 * @var bool Flag to check if the tax class exists.
	 */
	protected $exists = false;

	/**
	 * @var Database Database instance for tax classes.
	 */
	protected $class_db;

	/**
	 * @var Database Database instance for tax rates.
	 */
	protected $rate_db;

	/**
	 * Constructor for the Tax class.
	 *
	 * @param int|null $id Optional. The tax class ID.
	 */
	public function __construct( $id = null ) {
		$this->class_db = new Database( 'tax_classes' );
		$this->rate_db  = new Database( 'tax_rates' );

		if ( $id && $class = $this->class_db->get_by_id( $id ) ) {
			$this->id          = $id;
			$this->name        = $class->name;
			$this->description = $class->description;
			$this->status      = (bool) $class->status;
			$this->exists      = true;
		}
	}

	/**
	 * Check if the tax class exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Get the tax class ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the tax class name.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the tax class description.
	 *
	 * @return string|null
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the tax class status.
	 *
	 * @return bool
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Get all rates for the tax class.
	 *
	 * @return array List of tax rates.
	 */
	public function get_rates() {
		$rates = $this->rate_db->get_rows( array( 'tax_class_id' => $this->id ) );

		usort($rates, function($a, $b) {
			return $a->id - $b->id;
		});

		return array_map(
			function ( $rate ) {
				return array(
					'id'       => $rate->id,
					'country'  => $rate->country,
					'state'    => $rate->state,
					'city'     => $rate->city,
					'rate'     => rtrim( rtrim( $rate->rate, '0' ), '.' ),
					'priority' => $rate->priority,
					'compound' => (bool) $rate->compound,
				);
			},
			$rates
		);
	}

	/**
	 * Get all regions assigned to the tax plan.
	 *
	 * @return string String of comma-separated regions.
	 */
	public function get_regions() {
		$rates = $this->rate_db->get_rows( array( 'tax_class_id' => $this->id ) );

		usort($rates, function($a, $b) {
			return $a->id - $b->id;
		});

		return implode(
			', ',
			array_map(
				function ( $rate ) {
					return implode( '-', array( $rate->country, $rate->state, $rate->city ) );
				},
				$rates
			)
		);
	}

	/** 
	 * Check if a tax class name already exists.
	 * 
	 * @param string $name Tax class name to check. 
	 * @return bool True if the name exists, false otherwise.  
	 */
	public function name_exists( $name ) {   
		$conditions     = array( 'name' => $name );
		$existing_class = $this->class_db->get_row( $conditions );

		if ( $existing_class ) {			
			return true;
		}

		return false;
	}

	/**
	 * Create a tax class with rates.
	 *
	 * @param array $data Tax class data, including rates.
	 * @return int|false Tax class ID on success, false on failure.
	 */
	public function create_class( $data ) {

		// Insert tax class
		$class_data = array(
			'name'        => $data['name'],
			'description' => $data['description'] ?? '',
			'status'      => $data['status'] ?? 1,
		);

		$class_id = $this->class_db->insert_row( $class_data );

		if ( ! $class_id ) {
			return false;
		}

		// Insert rates
		foreach ( $data['rates'] as $rate ) {
			$rate_data = array(
				'tax_class_id' => $class_id,
				'country'      => $rate['country'],
				'state'        => $rate['state'] ?? '',
				'city'         => $rate['city'] ?? '',
				'rate'         => $rate['rate'],
				'priority'     => $rate['priority'] ?? 1,
				'compound'     => $rate['compound'] ?? 0,
			);

			$this->rate_db->insert_row( $rate_data );
		}

		return $class_id;
	}

	/**
	 * Update a tax class and its rates.
	 *
	 * @param int   $id Tax class ID.
	 * @param array $data Updated tax class data, including rates.
	 * @return bool True on success, false on failure.
	 */
	public function update_class( $id, $data ) {
		$this->id = $id;

		// Update tax class
		$class_data = array(
			'name'        => $data['name'],
			'description' => $data['description'] ?? '',
			'status'      => $data['status'] ?? 1,
		);

		$updated = $this->class_db->update_row( $this->id, $class_data );

		// Delete existing rates and re-insert new ones
		$existing_rates = $this->get_rates();
		foreach ( $existing_rates as $rate ) {
			$this->rate_db->delete_row( $rate['id'] );
		}

		foreach ( $data['rates'] as $rate ) {
			$rate_data = array(
				'tax_class_id' => $this->id,
				'country'      => $rate['country'],
				'state'        => $rate['state'] ?? '',
				'city'         => $rate['city'] ?? '',
				'rate'         => $rate['rate'],
				'priority'     => $rate['priority'] ?? 1,
				'compound'     => $rate['compound'] ?? 0,
			);

			$this->rate_db->insert_row( $rate_data );
		}

		return true;
	}

	/**
	 * Delete a tax class and its rates.
	 *
	 * @param int $id Tax class ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete_class( $id ) {
		return $this->class_db->delete_row( $id );
	}

	/**
	 * Get tax CSV files that match the given country codes.
	 *
	 * This method searches the plugin's /taxes directory for CSV files
	 * corresponding to the provided country codes. Only existing files
	 * are returned, and the codes are returned in uppercase format.
	 *
	 * @param array $countries Array of country codes to search for.
	 * @return array Array of matched country codes in uppercase.
	 */
	public function get_tax_files_by_countries( array $countries ): array {
        $taxes_dir = EASYCOMMERCE_PLUGIN_DIR . 'samples/taxes';
        $matches   = [];

        if ( empty( $countries ) ) {
            return [];
        }

        foreach ( $countries as $code ) {
            if ( ! preg_match( '/^[a-z]{2}$/', strtolower( $code ) ) ) {
                continue;
            }
            $file = $taxes_dir . '/' . strtolower( $code ) . '.csv';
            if ( file_exists( $file ) ) {
                $matches[] = strtoupper( $code );
            }
        }

        return $matches;
    }

	/**
	 * Get all states and combined tax rates from a country's CSV file.
	 *
	 * The CSV file should have columns: state, combined tax rate
	 *
	 * @param string $country Country code (e.g., 'us', 'bd').
	 * @return array Array of arrays with keys 'state', 'city', 'country' and 'combined_rate'.
	 */
	public function get_country_tax_rates_from_csv( string $country ): array {
		if ( ! preg_match( '/^[a-z]{2}$/', strtolower( $country ) ) ) {
			return [];
		}

		$taxes_dir = EASYCOMMERCE_PLUGIN_DIR . '/samples/taxes';
		$file_path = $taxes_dir . '/' . strtolower( $country ) . '.csv';

		if ( ! file_exists( $file_path ) ) {
			return [];
		}

		$rates = [];

		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			$header = fgetcsv( $handle );

			// Normalize headers to lowercase for safer matching
			$header = array_map( 'strtolower', $header );

			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				$rowData = array_combine($header, $row);

				$rawRate = $rowData['combined tax rate'] ?? '0';
				$cleanRate = number_format((float) str_replace('%', '', $rawRate), 2, '.', '');

				$rateRow = [
					'state'         => $rowData['state'] ?? '',
					'combined_rate' => $cleanRate,
					'country'       => strtoupper($country),
				];

				// Only add city if it exists in the CSV
				if ( isset($rowData['city']) && $rowData['city'] !== '' ) {
					$rateRow['city'] = $rowData['city'];
				}

				$rates[] = $rateRow;
			}

			fclose( $handle );
		}

		return $rates;
	}


	/**
	 * Static method to retrieve a tax class with its details.
	 *
	 * @param int $id Tax class ID.
	 * @return array|null Tax class details or null if not found.
	 */
	public static function get( $id ) {
		$class = new self( $id );

		if ( ! $class->exists() ) {
			return null;
		}

		return array(
			'id'          => $class->get_id(),
			'name'        => $class->get_name(),
			'description' => $class->get_description(),
			'status'      => $class->get_status(),
			'rates'       => $class->get_rates(),
		);
	}


	/**
	 * Get the correct tax rate based on location data.
	 *
	 * @param int         $tax_class_id The tax class ID (required).
	 * @param string      $country The country code (required).
	 * @param string|null $state The state code (optional).
	 * @param string|null $city The city name (optional).
	 *
	 * @return float
	 */
	public function get_rate_by_location( $tax_class_id, $country, $state = null, $city = null, $details = false ) {
		$conditions = array(
			'tax_class_id' => $tax_class_id,
			'country'      => $country,
		);

		// Get all rates for this country & tax class
		$rates = $this->rate_db->get_rows( $conditions );

		if ( empty( $rates ) ) {
			return 0.0;
		}

		$matched = array();

		foreach ( $rates as $rate ) {
			// Case 1: state + city match
			if ( $rate->state && $rate->city ) {
				if ( $state === $rate->state && $city === $rate->city ) {
					$matched[] = array( 'rate' => $rate, 'level' => 3 );
				}
			}
			// Case 2: state only
			elseif ( $rate->state && empty( $rate->city ) ) {
				if ( $state === $rate->state ) {
					$matched[] = array( 'rate' => $rate, 'level' => 2 );
				}
			}
			// Case 3: country only
			elseif ( empty( $rate->state ) && empty( $rate->city ) ) {
				$matched[] = array( 'rate' => $rate, 'level' => 1 );
			}
		}

		if ( empty( $matched ) ) {
			return 0.0;
		}

		// Sort by specificity: city > state > country
		usort( $matched, function( $a, $b ) {
			return $b['level'] - $a['level'];
		} );

		$rate = $matched[0]['rate'];

		if ( $details ) {
			unset( $rate->created_at );
			unset( $rate->updated_at );

			return (float)$rate->rate;
		}

		return (float)$rate->rate;
	}

	/**
	 * Get tax rate for a location by finding any matching active tax class.
	 * This is used when product doesn't have a specific tax class.
	 * 
	 * @param string      $country Country code.
	 * @param string|null $state   State code.
	 * @param string|null $city    City name.
	 * @return float Tax rate percentage.
	 */
	public function get_rate_for_location( $country, $state = null, $city = null ) {
		$active_classes = $this->class_db->get_rows( array( 'status' => 1 ) );
		
		if ( empty( $active_classes ) ) {
			return 0.0;
		}

		$all_matches = array();
		
		foreach ( $active_classes as $class ) {
			$conditions = array(
				'tax_class_id' => $class->id,
				'country'      => $country,
			);
			
			$rates = $this->rate_db->get_rows( $conditions );
			
			if ( empty( $rates ) ) {
				continue;
			}

			foreach ( $rates as $rate ) {
				if ( $rate->state && $rate->city ) {
					if ( $state === $rate->state && $city === $rate->city ) {
						$all_matches[] = array( 'rate' => $rate, 'level' => 3 );
					}
				}
				elseif ( $rate->state && empty( $rate->city ) ) {
					if ( $state === $rate->state ) {
						$all_matches[] = array( 'rate' => $rate, 'level' => 2 );
					}
				}
				elseif ( empty( $rate->state ) && empty( $rate->city ) ) {
					$all_matches[] = array( 'rate' => $rate, 'level' => 1 );
				}
			}
		}
		
		if ( empty( $all_matches ) ) {
			return 0.0;
		}
		
		usort( $all_matches, function( $a, $b ) {
			return $b['level'] - $a['level'];
		} );
		
		return (float) $all_matches[0]['rate']->rate;
	}

	/**
	 * Static method to list tax classes.
	 *
	 * @return array List of tax classes.
	 */
	public static function list_classes() {
		$class_db = new Database( 'tax_classes' );

		return array_map(
			function ( $class ) {
				$tax_class = new self( $class->id );
				return array(
					'id'      => $class->id,
					'name'    => $class->name,
					'regions' => $tax_class->get_regions(),
					'status'  => (bool) $class->status,
				);
			},
			$class_db->get_rows()
		);
	}
}

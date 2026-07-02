<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Models\Location;

class Geo extends API {

	use Cache;

	public function list_countries( $request ) {

		if ( false == ( $countries = $this->get_cache( 'countries' ) ) ) {
			$countries = Location::get_countries();
			$this->set_cache( 'countries', $countries, MONTH_IN_SECONDS );
		}

		/**
		 * Filters the countries before sending the response.
		 *
		 * @since 1.9
		 * @param array $countries The countries.
		 * @param WP_REST_Request $request The request object.
		 */
		$countries = apply_filters( 'easycommerce_list_countries', $countries, $request );

		if ( $request->get_param( 'details' ) == true ) {
			$this->response_success(
				array(
					'message'   => __( 'Countries loaded', 'easycommerce' ),
					'countries' => $countries,
				)
			);
		}

		$this->response_success(
			array(
				'message'   => __( 'Countries loaded', 'easycommerce' ),
				'countries' => wp_list_pluck( $countries, 'name', 'iso2' ),
			)
		);
	}

	public function list_states( $request ) {
		$country = $request->get_param( 'country' );

		if ( false == ( $states = $this->get_cache( "states_{$country}" ) ) ) {
			$states = Location::get_states( $country );
			$this->set_cache( "states_{$country}", $states, MONTH_IN_SECONDS );
		}

		/**
		 * Filters the states before sending the response.
		 *
		 * @since 1.9
		 * @param array $states The states.
		 * @param string $country The country.
		 * @param WP_REST_Request $request The request object.
		 */
		$states = apply_filters( 'easycommerce_list_states', $states, $country, $request );

		if ( $request->get_param( 'details' ) == true ) {
			$this->response_success(
				array(
					'message' => __( 'States loaded', 'easycommerce' ),
					'states'  => $states,
				)
			);
		}

		$this->response_success(
			array(
				'message' => __( 'States loaded', 'easycommerce' ),
				'states'  => wp_list_pluck( $states, 'name' ),
			)
		);
	}

	public function list_cities( $request ) {
		$country = $request->get_param( 'country' );
		$state   = $request->get_param( 'state' );

		if ( false == ( $cities = $this->get_cache( "cities_{$country}-{$state}" ) ) ) {
			$cities = Location::get_cities( $country, $state );
			$this->set_cache( "cities_{$country}-{$state}", $cities, MONTH_IN_SECONDS );
		}

		/**
		 * Filters the cities before sending the response.
		 *
		 * @since 1.9
		 * @param array $cities The cities.
		 * @param string $country The country.
		 * @param string $state The state.
		 * @param WP_REST_Request $request The request object.
		 */
		$cities = apply_filters( 'easycommerce_list_cities', $cities, $country, $state, $request );

		if ( $request->get_param( 'details' ) == true ) {
			$this->response_success(
				array(
					'message' => __( 'Cities loaded', 'easycommerce' ),
					'cities'  => $cities,
				)
			);
		}

		$this->response_success(
			array(
				'message' => __( 'Cities loaded', 'easycommerce' ),
				'cities'  => wp_list_pluck( $cities, 'name' ),
			)
		);
	}

	public function list_currencies( $request ) {
		$country_code = $request->get_param( 'country_code' );
		$cache_key    = !empty($country_code) ? "get_currencies_{$country_code}" : "get_currencies";
		
		if ( false == ( $currencies = $this->get_cache( $cache_key ) ) ) {
			$currencies = Location::get_currencies($country_code);
			$this->set_cache( $cache_key, $currencies, MONTH_IN_SECONDS );
		}

		$this->response_success(
			array(
				'message'    => __( 'Currencies loaded', 'easycommerce' ),
				'currencies' => $currencies,
			)
		);
	}
}

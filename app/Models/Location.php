<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Location Class
 * Handles loading country, state, city, and other location-related data from a static JSON file.
 */
class Location {

	/**
	 * @var array Cached data from the JSON file
	 */
	protected static $data = array();

	/**
	 * Load data from the JSON file if not already loaded.
	 */
	protected static function load_data() {
		
		$upload_dir = wp_upload_dir();
		$json_path  = "{$upload_dir['basedir']}/easycommerce/locations.json";

		if ( empty( self::$data ) && file_exists( $json_path ) ) {
			$json_data  = file_get_contents( $json_path );
			self::$data = json_decode( $json_data, true );
		}
	}

	/**
	 * Retrieve the list of all countries.
	 *
	 * @return array List of countries with basic details.
	 */
	public static function get_countries() {
		self::load_data();

		if ( ! empty( self::$data ) ) {
			return array_map(
				function ( $country ) {
					return array(
						'id'           => trim( $country['id'] ?? '' ),
						'name'         => trim( $country['name'] ?? '' ),
						'iso2'         => trim( $country['iso2'] ?? '' ),
						'iso3'         => trim( $country['iso3'] ?? '' ),
						'numeric_code' => trim( $country['numeric_code'] ?? '' ),
						'phone_code'   => trim( $country['phone_code'] ?? '' ),
						'capital'      => trim( $country['capital'] ?? '' ),
						'currency'     => trim( $country['currency'] ?? '' ),
						'region'       => trim( $country['region'] ?? '' ),
						'subregion'    => trim( $country['subregion'] ?? '' ),
					);
				},
				self::$data
			);
		}

		// Fall back to bundled data when locations.json not yet downloaded.
		$result = array();
		foreach ( self::fallback_countries() as $iso2 => $data ) {
			$result[] = array(
				'id'           => '',
				'name'         => $data[2],
				'iso2'         => $iso2,
				'iso3'         => $data[0],
				'numeric_code' => $data[1],
				'phone_code'   => '',
				'capital'      => '',
				'currency'     => '',
				'region'       => '',
				'subregion'    => '',
			);
		}

		return $result;
	}

	/**
	 * Retrieve the list of states for a given country.
	 *
	 * @param string $country_code ISO-2 code of the country.
	 * @return array List of states for the given country.
	 */
	public static function get_states( $country_code ) {
		self::load_data();

		foreach ( self::$data as $country ) {
			if ( $country['iso2'] === strtoupper( $country_code ) ) {
				return array_map(
					function ( $state ) {
						return array(
							'id'         => trim( $state['id'] ?? '' ),
							'name'       => trim( $state['name'] ?? '' ),
							'state_code' => trim( $state['state_code'] ?? '' ),
							'latitude'   => trim( $state['latitude'] ?? '' ),
							'longitude'  => trim( $state['longitude'] ?? '' ),
						);
					},
					$country['states'] ?? array()
				);
			}
		}

		return array();
	}

	/**
	 * Retrieve the list of cities for a given state within a country.
	 * If no state code is provided, return all cities in the country.
	 *
	 * @param string      $country_code ISO-2 code of the country.
	 * @param string|null $state_code Optional. State code within the country.
	 * @return array List of cities for the given state or all cities for the country if state code is not provided.
	 */
	public static function get_cities( $country_code, $state_code = null ) {
		self::load_data();

		// Get state code by state name
		if ( $state_code ) {
			$states = self::get_states( $country_code );
			foreach ( $states as $state ) {
				if ( trim( $state['name'] ) === trim( $state_code ) ) {
					$state_code = trim( $state['state_code'] );
					break;
				}
			}
		}

		foreach ( self::$data as $country ) {
			if ( $country['iso2'] === strtoupper( $country_code ) ) {

				if ( $state_code ) {
					foreach ( $country['states'] as $state ) {
						if ( $state['state_code'] === strtoupper( $state_code ) ) {
							return array_map(
								function ( $city ) {
									return array(
										'id'        => trim( $city['id'] ?? '' ),
										'name'      => trim( $city['name'] ?? '' ),
										'latitude'  => trim( $city['latitude'] ?? '' ),
										'longitude' => trim( $city['longitude'] ?? '' ),
									);
								},
								$state['cities'] ?? array()
							);
						}
					}
				}

				$all_cities = array();
				foreach ( $country['states'] as $state ) {
					$all_cities = array_merge(
						$all_cities,
						array_map(
							function ( $city ) {
								return array(
									'id'        => trim( $city['id'] ?? '' ),
									'name'      => trim( $city['name'] ?? '' ),
									'latitude'  => trim( $city['latitude'] ?? '' ),
									'longitude' => trim( $city['longitude'] ?? '' ),
								);
							},
							$state['cities'] ?? array()
						)
					);
				}
				return $all_cities;

			}
		}

		return array();
	}

	/**
	 * Bundled country data keyed by ISO-2 code.
	 * Used as fallback when locations.json has not yet been downloaded.
	 *
	 * @return array [ 'US' => [ 'iso3', 'numeric_code', 'name' ], ... ]
	 */
	protected static function fallback_countries() {
		return array(
			'AF' => array( 'AFG', '004', 'Afghanistan' ),
			'AX' => array( 'ALA', '248', 'Aland Islands' ),
			'AL' => array( 'ALB', '008', 'Albania' ),
			'DZ' => array( 'DZA', '012', 'Algeria' ),
			'AS' => array( 'ASM', '016', 'American Samoa' ),
			'AD' => array( 'AND', '020', 'Andorra' ),
			'AO' => array( 'AGO', '024', 'Angola' ),
			'AI' => array( 'AIA', '660', 'Anguilla' ),
			'AQ' => array( 'ATA', '010', 'Antarctica' ),
			'AG' => array( 'ATG', '028', 'Antigua and Barbuda' ),
			'AR' => array( 'ARG', '032', 'Argentina' ),
			'AM' => array( 'ARM', '051', 'Armenia' ),
			'AW' => array( 'ABW', '533', 'Aruba' ),
			'AU' => array( 'AUS', '036', 'Australia' ),
			'AT' => array( 'AUT', '040', 'Austria' ),
			'AZ' => array( 'AZE', '031', 'Azerbaijan' ),
			'BH' => array( 'BHR', '048', 'Bahrain' ),
			'BD' => array( 'BGD', '050', 'Bangladesh' ),
			'BB' => array( 'BRB', '052', 'Barbados' ),
			'BY' => array( 'BLR', '112', 'Belarus' ),
			'BE' => array( 'BEL', '056', 'Belgium' ),
			'BZ' => array( 'BLZ', '084', 'Belize' ),
			'BJ' => array( 'BEN', '204', 'Benin' ),
			'BM' => array( 'BMU', '060', 'Bermuda' ),
			'BT' => array( 'BTN', '064', 'Bhutan' ),
			'BO' => array( 'BOL', '068', 'Bolivia' ),
			'BQ' => array( 'BES', '535', 'Bonaire, Sint Eustatius and Saba' ),
			'BA' => array( 'BIH', '070', 'Bosnia and Herzegovina' ),
			'BW' => array( 'BWA', '072', 'Botswana' ),
			'BV' => array( 'BVT', '074', 'Bouvet Island' ),
			'BR' => array( 'BRA', '076', 'Brazil' ),
			'IO' => array( 'IOT', '086', 'British Indian Ocean Territory' ),
			'BN' => array( 'BRN', '096', 'Brunei' ),
			'BG' => array( 'BGR', '100', 'Bulgaria' ),
			'BF' => array( 'BFA', '854', 'Burkina Faso' ),
			'BI' => array( 'BDI', '108', 'Burundi' ),
			'KH' => array( 'KHM', '116', 'Cambodia' ),
			'CM' => array( 'CMR', '120', 'Cameroon' ),
			'CA' => array( 'CAN', '124', 'Canada' ),
			'CV' => array( 'CPV', '132', 'Cape Verde' ),
			'KY' => array( 'CYM', '136', 'Cayman Islands' ),
			'CF' => array( 'CAF', '140', 'Central African Republic' ),
			'TD' => array( 'TCD', '148', 'Chad' ),
			'CL' => array( 'CHL', '152', 'Chile' ),
			'CN' => array( 'CHN', '156', 'China' ),
			'CX' => array( 'CXR', '162', 'Christmas Island' ),
			'CC' => array( 'CCK', '166', 'Cocos (Keeling) Islands' ),
			'CO' => array( 'COL', '170', 'Colombia' ),
			'KM' => array( 'COM', '174', 'Comoros' ),
			'CG' => array( 'COG', '178', 'Congo' ),
			'CK' => array( 'COK', '184', 'Cook Islands' ),
			'CR' => array( 'CRI', '188', 'Costa Rica' ),
			'CI' => array( 'CIV', '384', "Cote D'Ivoire (Ivory Coast)" ),
			'HR' => array( 'HRV', '191', 'Croatia' ),
			'CU' => array( 'CUB', '192', 'Cuba' ),
			'CW' => array( 'CUW', '531', 'Curaçao' ),
			'CY' => array( 'CYP', '196', 'Cyprus' ),
			'CZ' => array( 'CZE', '203', 'Czech Republic' ),
			'CD' => array( 'COD', '180', 'Democratic Republic of the Congo' ),
			'DK' => array( 'DNK', '208', 'Denmark' ),
			'DJ' => array( 'DJI', '262', 'Djibouti' ),
			'DM' => array( 'DMA', '212', 'Dominica' ),
			'DO' => array( 'DOM', '214', 'Dominican Republic' ),
			'EC' => array( 'ECU', '218', 'Ecuador' ),
			'EG' => array( 'EGY', '818', 'Egypt' ),
			'SV' => array( 'SLV', '222', 'El Salvador' ),
			'GQ' => array( 'GNQ', '226', 'Equatorial Guinea' ),
			'ER' => array( 'ERI', '232', 'Eritrea' ),
			'EE' => array( 'EST', '233', 'Estonia' ),
			'SZ' => array( 'SWZ', '748', 'Eswatini' ),
			'ET' => array( 'ETH', '231', 'Ethiopia' ),
			'FK' => array( 'FLK', '238', 'Falkland Islands' ),
			'FO' => array( 'FRO', '234', 'Faroe Islands' ),
			'FJ' => array( 'FJI', '242', 'Fiji Islands' ),
			'FI' => array( 'FIN', '246', 'Finland' ),
			'FR' => array( 'FRA', '250', 'France' ),
			'GF' => array( 'GUF', '254', 'French Guiana' ),
			'PF' => array( 'PYF', '258', 'French Polynesia' ),
			'TF' => array( 'ATF', '260', 'French Southern Territories' ),
			'GA' => array( 'GAB', '266', 'Gabon' ),
			'GE' => array( 'GEO', '268', 'Georgia' ),
			'DE' => array( 'DEU', '276', 'Germany' ),
			'GH' => array( 'GHA', '288', 'Ghana' ),
			'GI' => array( 'GIB', '292', 'Gibraltar' ),
			'GR' => array( 'GRC', '300', 'Greece' ),
			'GL' => array( 'GRL', '304', 'Greenland' ),
			'GD' => array( 'GRD', '308', 'Grenada' ),
			'GP' => array( 'GLP', '312', 'Guadeloupe' ),
			'GU' => array( 'GUM', '316', 'Guam' ),
			'GT' => array( 'GTM', '320', 'Guatemala' ),
			'GG' => array( 'GGY', '831', 'Guernsey and Alderney' ),
			'GN' => array( 'GIN', '324', 'Guinea' ),
			'GW' => array( 'GNB', '624', 'Guinea-Bissau' ),
			'GY' => array( 'GUY', '328', 'Guyana' ),
			'HT' => array( 'HTI', '332', 'Haiti' ),
			'HM' => array( 'HMD', '334', 'Heard Island and McDonald Islands' ),
			'HN' => array( 'HND', '340', 'Honduras' ),
			'HK' => array( 'HKG', '344', 'Hong Kong S.A.R.' ),
			'HU' => array( 'HUN', '348', 'Hungary' ),
			'IS' => array( 'ISL', '352', 'Iceland' ),
			'IN' => array( 'IND', '356', 'India' ),
			'ID' => array( 'IDN', '360', 'Indonesia' ),
			'IR' => array( 'IRN', '364', 'Iran' ),
			'IQ' => array( 'IRQ', '368', 'Iraq' ),
			'IE' => array( 'IRL', '372', 'Ireland' ),
			'IL' => array( 'ISR', '376', 'Israel' ),
			'IT' => array( 'ITA', '380', 'Italy' ),
			'JM' => array( 'JAM', '388', 'Jamaica' ),
			'JP' => array( 'JPN', '392', 'Japan' ),
			'JE' => array( 'JEY', '832', 'Jersey' ),
			'JO' => array( 'JOR', '400', 'Jordan' ),
			'KZ' => array( 'KAZ', '398', 'Kazakhstan' ),
			'KE' => array( 'KEN', '404', 'Kenya' ),
			'KI' => array( 'KIR', '296', 'Kiribati' ),
			'XK' => array( 'XKX', '383', 'Kosovo' ),
			'KW' => array( 'KWT', '414', 'Kuwait' ),
			'KG' => array( 'KGZ', '417', 'Kyrgyzstan' ),
			'LA' => array( 'LAO', '418', 'Laos' ),
			'LV' => array( 'LVA', '428', 'Latvia' ),
			'LB' => array( 'LBN', '422', 'Lebanon' ),
			'LS' => array( 'LSO', '426', 'Lesotho' ),
			'LR' => array( 'LBR', '430', 'Liberia' ),
			'LY' => array( 'LBY', '434', 'Libya' ),
			'LI' => array( 'LIE', '438', 'Liechtenstein' ),
			'LT' => array( 'LTU', '440', 'Lithuania' ),
			'LU' => array( 'LUX', '442', 'Luxembourg' ),
			'MO' => array( 'MAC', '446', 'Macau S.A.R.' ),
			'MG' => array( 'MDG', '450', 'Madagascar' ),
			'MW' => array( 'MWI', '454', 'Malawi' ),
			'MY' => array( 'MYS', '458', 'Malaysia' ),
			'MV' => array( 'MDV', '462', 'Maldives' ),
			'ML' => array( 'MLI', '466', 'Mali' ),
			'MT' => array( 'MLT', '470', 'Malta' ),
			'IM' => array( 'IMN', '833', 'Man (Isle of)' ),
			'MH' => array( 'MHL', '584', 'Marshall Islands' ),
			'MQ' => array( 'MTQ', '474', 'Martinique' ),
			'MR' => array( 'MRT', '478', 'Mauritania' ),
			'MU' => array( 'MUS', '480', 'Mauritius' ),
			'YT' => array( 'MYT', '175', 'Mayotte' ),
			'MX' => array( 'MEX', '484', 'Mexico' ),
			'FM' => array( 'FSM', '583', 'Micronesia' ),
			'MD' => array( 'MDA', '498', 'Moldova' ),
			'MC' => array( 'MCO', '492', 'Monaco' ),
			'MN' => array( 'MNG', '496', 'Mongolia' ),
			'ME' => array( 'MNE', '499', 'Montenegro' ),
			'MS' => array( 'MSR', '500', 'Montserrat' ),
			'MA' => array( 'MAR', '504', 'Morocco' ),
			'MZ' => array( 'MOZ', '508', 'Mozambique' ),
			'MM' => array( 'MMR', '104', 'Myanmar' ),
			'NA' => array( 'NAM', '516', 'Namibia' ),
			'NR' => array( 'NRU', '520', 'Nauru' ),
			'NP' => array( 'NPL', '524', 'Nepal' ),
			'NL' => array( 'NLD', '528', 'Netherlands' ),
			'NC' => array( 'NCL', '540', 'New Caledonia' ),
			'NZ' => array( 'NZL', '554', 'New Zealand' ),
			'NI' => array( 'NIC', '558', 'Nicaragua' ),
			'NE' => array( 'NER', '562', 'Niger' ),
			'NG' => array( 'NGA', '566', 'Nigeria' ),
			'NU' => array( 'NIU', '570', 'Niue' ),
			'NF' => array( 'NFK', '574', 'Norfolk Island' ),
			'KP' => array( 'PRK', '408', 'North Korea' ),
			'MK' => array( 'MKD', '807', 'North Macedonia' ),
			'MP' => array( 'MNP', '580', 'Northern Mariana Islands' ),
			'NO' => array( 'NOR', '578', 'Norway' ),
			'OM' => array( 'OMN', '512', 'Oman' ),
			'PK' => array( 'PAK', '586', 'Pakistan' ),
			'PW' => array( 'PLW', '585', 'Palau' ),
			'PS' => array( 'PSE', '275', 'Palestinian Territory Occupied' ),
			'PA' => array( 'PAN', '591', 'Panama' ),
			'PG' => array( 'PNG', '598', 'Papua New Guinea' ),
			'PY' => array( 'PRY', '600', 'Paraguay' ),
			'PE' => array( 'PER', '604', 'Peru' ),
			'PH' => array( 'PHL', '608', 'Philippines' ),
			'PN' => array( 'PCN', '612', 'Pitcairn Island' ),
			'PL' => array( 'POL', '616', 'Poland' ),
			'PT' => array( 'PRT', '620', 'Portugal' ),
			'PR' => array( 'PRI', '630', 'Puerto Rico' ),
			'QA' => array( 'QAT', '634', 'Qatar' ),
			'RE' => array( 'REU', '638', 'Reunion' ),
			'RO' => array( 'ROU', '642', 'Romania' ),
			'RU' => array( 'RUS', '643', 'Russia' ),
			'RW' => array( 'RWA', '646', 'Rwanda' ),
			'SH' => array( 'SHN', '654', 'Saint Helena' ),
			'KN' => array( 'KNA', '659', 'Saint Kitts and Nevis' ),
			'LC' => array( 'LCA', '662', 'Saint Lucia' ),
			'PM' => array( 'SPM', '666', 'Saint Pierre and Miquelon' ),
			'VC' => array( 'VCT', '670', 'Saint Vincent and the Grenadines' ),
			'BL' => array( 'BLM', '652', 'Saint-Barthelemy' ),
			'MF' => array( 'MAF', '663', 'Saint-Martin (French part)' ),
			'WS' => array( 'WSM', '882', 'Samoa' ),
			'SM' => array( 'SMR', '674', 'San Marino' ),
			'ST' => array( 'STP', '678', 'Sao Tome and Principe' ),
			'SA' => array( 'SAU', '682', 'Saudi Arabia' ),
			'SN' => array( 'SEN', '686', 'Senegal' ),
			'RS' => array( 'SRB', '688', 'Serbia' ),
			'SC' => array( 'SYC', '690', 'Seychelles' ),
			'SL' => array( 'SLE', '694', 'Sierra Leone' ),
			'SG' => array( 'SGP', '702', 'Singapore' ),
			'SX' => array( 'SXM', '534', 'Sint Maarten (Dutch part)' ),
			'SK' => array( 'SVK', '703', 'Slovakia' ),
			'SI' => array( 'SVN', '705', 'Slovenia' ),
			'SB' => array( 'SLB', '090', 'Solomon Islands' ),
			'SO' => array( 'SOM', '706', 'Somalia' ),
			'ZA' => array( 'ZAF', '710', 'South Africa' ),
			'GS' => array( 'SGS', '239', 'South Georgia' ),
			'KR' => array( 'KOR', '410', 'South Korea' ),
			'SS' => array( 'SSD', '728', 'South Sudan' ),
			'ES' => array( 'ESP', '724', 'Spain' ),
			'LK' => array( 'LKA', '144', 'Sri Lanka' ),
			'SD' => array( 'SDN', '729', 'Sudan' ),
			'SR' => array( 'SUR', '740', 'Suriname' ),
			'SJ' => array( 'SJM', '744', 'Svalbard and Jan Mayen Islands' ),
			'SE' => array( 'SWE', '752', 'Sweden' ),
			'CH' => array( 'CHE', '756', 'Switzerland' ),
			'SY' => array( 'SYR', '760', 'Syria' ),
			'TW' => array( 'TWN', '158', 'Taiwan' ),
			'TJ' => array( 'TJK', '762', 'Tajikistan' ),
			'TZ' => array( 'TZA', '834', 'Tanzania' ),
			'TH' => array( 'THA', '764', 'Thailand' ),
			'BS' => array( 'BHS', '044', 'The Bahamas' ),
			'GM' => array( 'GMB', '270', 'The Gambia' ),
			'TL' => array( 'TLS', '626', 'Timor-Leste' ),
			'TG' => array( 'TGO', '768', 'Togo' ),
			'TK' => array( 'TKL', '772', 'Tokelau' ),
			'TO' => array( 'TON', '776', 'Tonga' ),
			'TT' => array( 'TTO', '780', 'Trinidad and Tobago' ),
			'TN' => array( 'TUN', '788', 'Tunisia' ),
			'TR' => array( 'TUR', '792', 'Turkey' ),
			'TM' => array( 'TKM', '795', 'Turkmenistan' ),
			'TC' => array( 'TCA', '796', 'Turks and Caicos Islands' ),
			'TV' => array( 'TUV', '798', 'Tuvalu' ),
			'UG' => array( 'UGA', '800', 'Uganda' ),
			'UA' => array( 'UKR', '804', 'Ukraine' ),
			'AE' => array( 'ARE', '784', 'United Arab Emirates' ),
			'GB' => array( 'GBR', '826', 'United Kingdom' ),
			'US' => array( 'USA', '840', 'United States' ),
			'UM' => array( 'UMI', '581', 'United States Minor Outlying Islands' ),
			'UY' => array( 'URY', '858', 'Uruguay' ),
			'UZ' => array( 'UZB', '860', 'Uzbekistan' ),
			'VU' => array( 'VUT', '548', 'Vanuatu' ),
			'VA' => array( 'VAT', '336', 'Vatican City State (Holy See)' ),
			'VE' => array( 'VEN', '862', 'Venezuela' ),
			'VN' => array( 'VNM', '704', 'Vietnam' ),
			'VG' => array( 'VGB', '092', 'Virgin Islands (British)' ),
			'VI' => array( 'VIR', '850', 'Virgin Islands (US)' ),
			'WF' => array( 'WLF', '876', 'Wallis and Futuna Islands' ),
			'EH' => array( 'ESH', '732', 'Western Sahara' ),
			'YE' => array( 'YEM', '887', 'Yemen' ),
			'ZM' => array( 'ZMB', '894', 'Zambia' ),
			'ZW' => array( 'ZWE', '716', 'Zimbabwe' ),
		);
	}

	/**
	 * Bundled currency data keyed by ISO-2 country code.
	 * Used as fallback when locations.json has not yet been downloaded.
	 *
	 * @return array [ 'US' => [ 'USD', 'US Dollar', '$' ], ... ]
	 */
	protected static function fallback_currencies() {
		return array(
			'AF' => array( 'AFN', 'Afghan Afghani', '؋' ),
			'AX' => array( 'EUR', 'Euro', '€' ),
			'AL' => array( 'ALL', 'Albanian Lek', 'L' ),
			'DZ' => array( 'DZD', 'Algerian Dinar', 'د.ج' ),
			'AS' => array( 'USD', 'US Dollar', '$' ),
			'AD' => array( 'EUR', 'Euro', '€' ),
			'AO' => array( 'AOA', 'Angolan Kwanza', 'Kz' ),
			'AI' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'AQ' => array( 'USD', 'US Dollar', '$' ),
			'AG' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'AR' => array( 'ARS', 'Argentine Peso', '$' ),
			'AM' => array( 'AMD', 'Armenian Dram', '֏' ),
			'AW' => array( 'AWG', 'Aruban Florin', 'ƒ' ),
			'AU' => array( 'AUD', 'Australian Dollar', '$' ),
			'AT' => array( 'EUR', 'Euro', '€' ),
			'AZ' => array( 'AZN', 'Azerbaijani Manat', '₼' ),
			'BH' => array( 'BHD', 'Bahraini Dinar', '.د.ب' ),
			'BD' => array( 'BDT', 'Bangladeshi Taka', '৳' ),
			'BB' => array( 'BBD', 'Barbadian Dollar', '$' ),
			'BY' => array( 'BYN', 'Belarusian Ruble', 'Br' ),
			'BE' => array( 'EUR', 'Euro', '€' ),
			'BZ' => array( 'BZD', 'Belize Dollar', '$' ),
			'BJ' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'BM' => array( 'BMD', 'Bermudian Dollar', '$' ),
			'BT' => array( 'BTN', 'Bhutanese Ngultrum', 'Nu' ),
			'BO' => array( 'BOB', 'Bolivian Boliviano', 'Bs.' ),
			'BQ' => array( 'USD', 'US Dollar', '$' ),
			'BA' => array( 'BAM', 'Bosnia-Herzegovina Convertible Mark', 'KM' ),
			'BW' => array( 'BWP', 'Botswanan Pula', 'P' ),
			'BV' => array( 'NOK', 'Norwegian Krone', 'kr' ),
			'BR' => array( 'BRL', 'Brazilian Real', 'R$' ),
			'IO' => array( 'USD', 'US Dollar', '$' ),
			'BN' => array( 'BND', 'Brunei Dollar', '$' ),
			'BG' => array( 'BGN', 'Bulgarian Lev', 'лв' ),
			'BF' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'BI' => array( 'BIF', 'Burundian Franc', 'Fr' ),
			'KH' => array( 'KHR', 'Cambodian Riel', '៛' ),
			'CM' => array( 'XAF', 'Central African CFA Franc', 'Fr' ),
			'CA' => array( 'CAD', 'Canadian Dollar', '$' ),
			'CV' => array( 'CVE', 'Cape Verdean Escudo', '$' ),
			'KY' => array( 'KYD', 'Cayman Islands Dollar', '$' ),
			'CF' => array( 'XAF', 'Central African CFA Franc', 'Fr' ),
			'TD' => array( 'XAF', 'Central African CFA Franc', 'Fr' ),
			'CL' => array( 'CLP', 'Chilean Peso', '$' ),
			'CN' => array( 'CNY', 'Chinese Yuan', '¥' ),
			'CX' => array( 'AUD', 'Australian Dollar', '$' ),
			'CC' => array( 'AUD', 'Australian Dollar', '$' ),
			'CO' => array( 'COP', 'Colombian Peso', '$' ),
			'KM' => array( 'KMF', 'Comorian Franc', 'Fr' ),
			'CG' => array( 'XAF', 'Central African CFA Franc', 'Fr' ),
			'CK' => array( 'NZD', 'New Zealand Dollar', '$' ),
			'CR' => array( 'CRC', 'Costa Rican Colón', '₡' ),
			'CI' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'HR' => array( 'EUR', 'Euro', '€' ),
			'CU' => array( 'CUP', 'Cuban Peso', '$' ),
			'CW' => array( 'ANG', 'Netherlands Antillean Guilder', 'ƒ' ),
			'CY' => array( 'EUR', 'Euro', '€' ),
			'CZ' => array( 'CZK', 'Czech Koruna', 'Kč' ),
			'CD' => array( 'CDF', 'Congolese Franc', 'Fr' ),
			'DK' => array( 'DKK', 'Danish Krone', 'kr' ),
			'DJ' => array( 'DJF', 'Djiboutian Franc', 'Fr' ),
			'DM' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'DO' => array( 'DOP', 'Dominican Peso', '$' ),
			'EC' => array( 'USD', 'US Dollar', '$' ),
			'EG' => array( 'EGP', 'Egyptian Pound', '£' ),
			'SV' => array( 'USD', 'US Dollar', '$' ),
			'GQ' => array( 'XAF', 'Central African CFA Franc', 'Fr' ),
			'ER' => array( 'ERN', 'Eritrean Nakfa', 'Nfk' ),
			'EE' => array( 'EUR', 'Euro', '€' ),
			'SZ' => array( 'SZL', 'Swazi Lilangeni', 'L' ),
			'ET' => array( 'ETB', 'Ethiopian Birr', 'Br' ),
			'FK' => array( 'FKP', 'Falkland Islands Pound', '£' ),
			'FO' => array( 'DKK', 'Danish Krone', 'kr' ),
			'FJ' => array( 'FJD', 'Fijian Dollar', '$' ),
			'FI' => array( 'EUR', 'Euro', '€' ),
			'FR' => array( 'EUR', 'Euro', '€' ),
			'GF' => array( 'EUR', 'Euro', '€' ),
			'PF' => array( 'XPF', 'CFP Franc', 'Fr' ),
			'TF' => array( 'EUR', 'Euro', '€' ),
			'GA' => array( 'XAF', 'Central African CFA Franc', 'Fr' ),
			'GE' => array( 'GEL', 'Georgian Lari', '₾' ),
			'DE' => array( 'EUR', 'Euro', '€' ),
			'GH' => array( 'GHS', 'Ghanaian Cedi', '₵' ),
			'GI' => array( 'GIP', 'Gibraltar Pound', '£' ),
			'GR' => array( 'EUR', 'Euro', '€' ),
			'GL' => array( 'DKK', 'Danish Krone', 'kr' ),
			'GD' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'GP' => array( 'EUR', 'Euro', '€' ),
			'GU' => array( 'USD', 'US Dollar', '$' ),
			'GT' => array( 'GTQ', 'Guatemalan Quetzal', 'Q' ),
			'GG' => array( 'GBP', 'British Pound', '£' ),
			'GN' => array( 'GNF', 'Guinean Franc', 'Fr' ),
			'GW' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'GY' => array( 'GYD', 'Guyanaese Dollar', '$' ),
			'HT' => array( 'HTG', 'Haitian Gourde', 'G' ),
			'HM' => array( 'AUD', 'Australian Dollar', '$' ),
			'HN' => array( 'HNL', 'Honduran Lempira', 'L' ),
			'HK' => array( 'HKD', 'Hong Kong Dollar', '$' ),
			'HU' => array( 'HUF', 'Hungarian Forint', 'Ft' ),
			'IS' => array( 'ISK', 'Icelandic Króna', 'kr' ),
			'IN' => array( 'INR', 'Indian Rupee', '₹' ),
			'ID' => array( 'IDR', 'Indonesian Rupiah', 'Rp' ),
			'IR' => array( 'IRR', 'Iranian Rial', '﷼' ),
			'IQ' => array( 'IQD', 'Iraqi Dinar', 'ع.د' ),
			'IE' => array( 'EUR', 'Euro', '€' ),
			'IL' => array( 'ILS', 'Israeli New Shekel', '₪' ),
			'IT' => array( 'EUR', 'Euro', '€' ),
			'JM' => array( 'JMD', 'Jamaican Dollar', '$' ),
			'JP' => array( 'JPY', 'Japanese Yen', '¥' ),
			'JE' => array( 'GBP', 'British Pound', '£' ),
			'JO' => array( 'JOD', 'Jordanian Dinar', 'د.ا' ),
			'KZ' => array( 'KZT', 'Kazakhstani Tenge', '₸' ),
			'KE' => array( 'KES', 'Kenyan Shilling', 'KSh' ),
			'KI' => array( 'AUD', 'Australian Dollar', '$' ),
			'XK' => array( 'EUR', 'Euro', '€' ),
			'KW' => array( 'KWD', 'Kuwaiti Dinar', 'د.ك' ),
			'KG' => array( 'KGS', 'Kyrgystani Som', 'с' ),
			'LA' => array( 'LAK', 'Laotian Kip', '₭' ),
			'LV' => array( 'EUR', 'Euro', '€' ),
			'LB' => array( 'LBP', 'Lebanese Pound', 'ل.ل' ),
			'LS' => array( 'LSL', 'Lesotho Loti', 'L' ),
			'LR' => array( 'LRD', 'Liberian Dollar', '$' ),
			'LY' => array( 'LYD', 'Libyan Dinar', 'ل.د' ),
			'LI' => array( 'CHF', 'Swiss Franc', 'Fr' ),
			'LT' => array( 'EUR', 'Euro', '€' ),
			'LU' => array( 'EUR', 'Euro', '€' ),
			'MO' => array( 'MOP', 'Macanese Pataca', 'P' ),
			'MG' => array( 'MGA', 'Malagasy Ariary', 'Ar' ),
			'MW' => array( 'MWK', 'Malawian Kwacha', 'MK' ),
			'MY' => array( 'MYR', 'Malaysian Ringgit', 'RM' ),
			'MV' => array( 'MVR', 'Maldivian Rufiyaa', 'Rf' ),
			'ML' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'MT' => array( 'EUR', 'Euro', '€' ),
			'IM' => array( 'GBP', 'British Pound', '£' ),
			'MH' => array( 'USD', 'US Dollar', '$' ),
			'MQ' => array( 'EUR', 'Euro', '€' ),
			'MR' => array( 'MRU', 'Mauritanian Ouguiya', 'UM' ),
			'MU' => array( 'MUR', 'Mauritian Rupee', '₨' ),
			'YT' => array( 'EUR', 'Euro', '€' ),
			'MX' => array( 'MXN', 'Mexican Peso', '$' ),
			'FM' => array( 'USD', 'US Dollar', '$' ),
			'MD' => array( 'MDL', 'Moldovan Leu', 'L' ),
			'MC' => array( 'EUR', 'Euro', '€' ),
			'MN' => array( 'MNT', 'Mongolian Tugrik', '₮' ),
			'ME' => array( 'EUR', 'Euro', '€' ),
			'MS' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'MA' => array( 'MAD', 'Moroccan Dirham', 'د.م.' ),
			'MZ' => array( 'MZN', 'Mozambican Metical', 'MT' ),
			'MM' => array( 'MMK', 'Myanmar Kyat', 'K' ),
			'NA' => array( 'NAD', 'Namibian Dollar', '$' ),
			'NR' => array( 'AUD', 'Australian Dollar', '$' ),
			'NP' => array( 'NPR', 'Nepalese Rupee', '₨' ),
			'NL' => array( 'EUR', 'Euro', '€' ),
			'NC' => array( 'XPF', 'CFP Franc', 'Fr' ),
			'NZ' => array( 'NZD', 'New Zealand Dollar', '$' ),
			'NI' => array( 'NIO', 'Nicaraguan Córdoba', 'C$' ),
			'NE' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'NG' => array( 'NGN', 'Nigerian Naira', '₦' ),
			'NU' => array( 'NZD', 'New Zealand Dollar', '$' ),
			'NF' => array( 'AUD', 'Australian Dollar', '$' ),
			'KP' => array( 'KPW', 'North Korean Won', '₩' ),
			'MK' => array( 'MKD', 'Macedonian Denar', 'ден' ),
			'MP' => array( 'USD', 'US Dollar', '$' ),
			'NO' => array( 'NOK', 'Norwegian Krone', 'kr' ),
			'OM' => array( 'OMR', 'Omani Rial', 'ر.ع.' ),
			'PK' => array( 'PKR', 'Pakistani Rupee', '₨' ),
			'PW' => array( 'USD', 'US Dollar', '$' ),
			'PS' => array( 'ILS', 'Israeli New Shekel', '₪' ),
			'PA' => array( 'PAB', 'Panamanian Balboa', 'B/.' ),
			'PG' => array( 'PGK', 'Papua New Guinean Kina', 'K' ),
			'PY' => array( 'PYG', 'Paraguayan Guarani', '₲' ),
			'PE' => array( 'PEN', 'Peruvian Sol', 'S/.' ),
			'PH' => array( 'PHP', 'Philippine Peso', '₱' ),
			'PN' => array( 'NZD', 'New Zealand Dollar', '$' ),
			'PL' => array( 'PLN', 'Polish Zloty', 'zł' ),
			'PT' => array( 'EUR', 'Euro', '€' ),
			'PR' => array( 'USD', 'US Dollar', '$' ),
			'QA' => array( 'QAR', 'Qatari Riyal', 'ر.ق' ),
			'RE' => array( 'EUR', 'Euro', '€' ),
			'RO' => array( 'RON', 'Romanian Leu', 'lei' ),
			'RU' => array( 'RUB', 'Russian Ruble', '₽' ),
			'RW' => array( 'RWF', 'Rwandan Franc', 'Fr' ),
			'SH' => array( 'SHP', 'Saint Helena Pound', '£' ),
			'KN' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'LC' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'PM' => array( 'EUR', 'Euro', '€' ),
			'VC' => array( 'XCD', 'East Caribbean Dollar', '$' ),
			'BL' => array( 'EUR', 'Euro', '€' ),
			'MF' => array( 'EUR', 'Euro', '€' ),
			'WS' => array( 'WST', 'Samoan Tala', 'T' ),
			'SM' => array( 'EUR', 'Euro', '€' ),
			'ST' => array( 'STN', 'São Tomé and Príncipe Dobra', 'Db' ),
			'SA' => array( 'SAR', 'Saudi Riyal', 'ر.س' ),
			'SN' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'RS' => array( 'RSD', 'Serbian Dinar', 'din' ),
			'SC' => array( 'SCR', 'Seychellois Rupee', '₨' ),
			'SL' => array( 'SLL', 'Sierra Leonean Leone', 'Le' ),
			'SG' => array( 'SGD', 'Singapore Dollar', '$' ),
			'SX' => array( 'ANG', 'Netherlands Antillean Guilder', 'ƒ' ),
			'SK' => array( 'EUR', 'Euro', '€' ),
			'SI' => array( 'EUR', 'Euro', '€' ),
			'SB' => array( 'SBD', 'Solomon Islands Dollar', '$' ),
			'SO' => array( 'SOS', 'Somali Shilling', 'Sh' ),
			'ZA' => array( 'ZAR', 'South African Rand', 'R' ),
			'GS' => array( 'SHP', 'Saint Helena Pound', '£' ),
			'KR' => array( 'KRW', 'South Korean Won', '₩' ),
			'SS' => array( 'SSP', 'South Sudanese Pound', '£' ),
			'ES' => array( 'EUR', 'Euro', '€' ),
			'LK' => array( 'LKR', 'Sri Lankan Rupee', '₨' ),
			'SD' => array( 'SDG', 'Sudanese Pound', 'ج.س.' ),
			'SR' => array( 'SRD', 'Surinamese Dollar', '$' ),
			'SJ' => array( 'NOK', 'Norwegian Krone', 'kr' ),
			'SE' => array( 'SEK', 'Swedish Krona', 'kr' ),
			'CH' => array( 'CHF', 'Swiss Franc', 'Fr' ),
			'SY' => array( 'SYP', 'Syrian Pound', '£' ),
			'TW' => array( 'TWD', 'New Taiwan Dollar', '$' ),
			'TJ' => array( 'TJS', 'Tajikistani Somoni', 'SM' ),
			'TZ' => array( 'TZS', 'Tanzanian Shilling', 'Sh' ),
			'TH' => array( 'THB', 'Thai Baht', '฿' ),
			'BS' => array( 'BSD', 'Bahamian Dollar', '$' ),
			'GM' => array( 'GMD', 'Gambian Dalasi', 'D' ),
			'TL' => array( 'USD', 'US Dollar', '$' ),
			'TG' => array( 'XOF', 'West African CFA Franc', 'Fr' ),
			'TK' => array( 'NZD', 'New Zealand Dollar', '$' ),
			'TO' => array( 'TOP', "Tongan Pa'anga", 'T$' ),
			'TT' => array( 'TTD', 'Trinidad & Tobago Dollar', '$' ),
			'TN' => array( 'TND', 'Tunisian Dinar', 'د.ت' ),
			'TR' => array( 'TRY', 'Turkish Lira', '₺' ),
			'TM' => array( 'TMT', 'Turkmenistani Manat', 'T' ),
			'TC' => array( 'USD', 'US Dollar', '$' ),
			'TV' => array( 'AUD', 'Australian Dollar', '$' ),
			'UG' => array( 'UGX', 'Ugandan Shilling', 'Sh' ),
			'UA' => array( 'UAH', 'Ukrainian Hryvnia', '₴' ),
			'AE' => array( 'AED', 'UAE Dirham', 'د.إ' ),
			'GB' => array( 'GBP', 'British Pound', '£' ),
			'US' => array( 'USD', 'US Dollar', '$' ),
			'UM' => array( 'USD', 'US Dollar', '$' ),
			'UY' => array( 'UYU', 'Uruguayan Peso', '$' ),
			'UZ' => array( 'UZS', 'Uzbekistani Som', "so'm" ),
			'VU' => array( 'VUV', 'Vanuatu Vatu', 'Vt' ),
			'VA' => array( 'EUR', 'Euro', '€' ),
			'VE' => array( 'VES', 'Venezuelan Bolívar', 'Bs.' ),
			'VN' => array( 'VND', 'Vietnamese Dong', '₫' ),
			'VG' => array( 'USD', 'US Dollar', '$' ),
			'VI' => array( 'USD', 'US Dollar', '$' ),
			'WF' => array( 'XPF', 'CFP Franc', 'Fr' ),
			'EH' => array( 'MAD', 'Moroccan Dirham', 'د.م.' ),
			'YE' => array( 'YER', 'Yemeni Rial', '﷼' ),
			'ZM' => array( 'ZMW', 'Zambian Kwacha', 'ZK' ),
			'ZW' => array( 'ZWL', 'Zimbabwean Dollar', '$' ),
		);
	}

	/**
	 * Retrieve the list of currencies.
	 * If a country is specified, return only the currency for that country.
	 *
	 * @param string|null $country ISO-2 code of the country.
	 * @return array List of all currencies or a single currency for the specified country.
	 */
	public static function get_currencies( $country = null ) {
		self::load_data();

		if ( $country ) {
			$code = strtoupper( $country );

			foreach ( self::$data as $item ) {
				if ( $item['iso2'] === $code ) {
					return array(
						'currency'        => trim( $item['currency'] ?? '' ),
						'currency_name'   => trim( $item['currency_name'] ?? '' ),
						'currency_symbol' => trim( $item['currency_symbol'] ?? '' ),
					);
				}
			}

			// Fall back to bundled data when locations.json not yet downloaded.
			$fallback = self::fallback_currencies();
			if ( isset( $fallback[ $code ] ) ) {
				return array(
					'currency'        => $fallback[ $code ][0],
					'currency_name'   => $fallback[ $code ][1],
					'currency_symbol' => $fallback[ $code ][2],
				);
			}

			return array();
		}

		// Return all currencies if no country specified.
		if ( ! empty( self::$data ) ) {
			return array_map(
				function ( $item ) {
					return array(
						'country'         => trim( $item['name'] ?? '' ),
						'currency'        => trim( $item['currency'] ?? '' ),
						'currency_name'   => trim( $item['currency_name'] ?? '' ),
						'currency_symbol' => trim( $item['currency_symbol'] ?? '' ),
					);
				},
				self::$data
			);
		}

		// Fall back to bundled data when locations.json not yet downloaded.
		$result   = array();
		$seen     = array();
		foreach ( self::fallback_currencies() as $iso2 => $data ) {
			$code = $data[0];
			if ( isset( $seen[ $code ] ) ) {
				continue;
			}
			$seen[ $code ] = true;
			$result[]      = array(
				'country'         => '',
				'currency'        => $code,
				'currency_name'   => $data[1],
				'currency_symbol' => $data[2],
			);
		}

		return $result;
	}

	/**
	 * Retrieve the list of ISD codes.
	 * If a country is specified, return only the ISD code for that country.
	 *
	 * @param string|null $country ISO-2 code of the country.
	 * @return array List of ISD codes for all countries or a single ISD code for the specified country.
	 */
	public static function get_isd_codes( $country = null ) {
		self::load_data();

		if ( $country ) {
			foreach ( self::$data as $item ) {
				if ( $item['iso2'] === strtoupper( $country ) ) {
					return array(
						'country'  => trim( $item['name'] ),
						'isd_code' => trim( $item['phone_code'] ),
					);
				}
			}

			return array();
		}

		// Return all ISD codes if no country specified
		return array_map(
			function ( $item ) {
				return array(
					'country'  => trim( $item['name'] ?? '' ),
					'isd_code' => trim( $item['phone_code'] ?? '' ),
				);
			},
			self::$data
		);
	}

	/**
	 * Retrieve the list of timezones.
	 * If a country is specified, return only the timezones for that country.
	 *
	 * @param string|null $country ISO-2 code of the country.
	 * @return array List of timezones for all countries or specific timezones for the specified country.
	 */
	public static function get_timezones( $country = null ) {
		self::load_data();

		if ( $country ) {
			foreach ( self::$data as $item ) {
				if ( $item['iso2'] === strtoupper( $country ) ) {
					return $item['timezones'] ?? array();
				}
			}

			return array();
		}

		// Return all timezones if no country specified
		$all_timezones = array();
		foreach ( self::$data as $item ) {
			foreach ( $item['timezones'] ?? array() as $timezone ) {
				$all_timezones[] = array(
					'country'    => trim( $item['name'] ?? '' ),
					'timezone'   => trim( $timezone['zoneName'] ?? '' ),
					'gmt_offset' => trim( $timezone['gmtOffsetName'] ?? '' ),
				);
			}
		}

		return $all_timezones;
	}
}

<?php

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;

global $easycommerce_checkout_fields;

$easycommerce_checkout_fields = array(
	'billing' => array(
		'first_name' => array(
			'id'          => 'first_name',
			'type'        => 'text',
			'label'       => __( 'First Name', 'easycommerce' ),
			'placeholder' => __( 'Your first name', 'easycommerce' ),
			'required'    => true,
			'class'       => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
			'atts'        => array(
				'data-validation' => 'name',
			),
		),
		'last_name'  => array(
			'id'          => 'last_name',
			'type'        => 'text',
			'label'       => __( 'Last Name', 'easycommerce' ),
			'placeholder' => __( 'Your last name', 'easycommerce' ),
			'required'    => false,
			'class'       => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
			'atts'        => array(
				'data-validation' => 'name',
			),
		),
		'email'      => array(
			'id'          => 'email',
			'type'        => 'email',
			'label'       => __( 'Email', 'easycommerce' ),
			'placeholder' => __( 'Your email', 'easycommerce' ),
			'required'    => true,
			'class'       => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
			'atts'        => array(
				'data-validation' => 'email',
			),
		),
		'phone'      => array(
			'id'          => 'phone',
			'type'        => 'text',
			'label'       => __( 'Phone', 'easycommerce' ),
			'placeholder' => __( 'Enter your phone number', 'easycommerce' ),
			'required'    => false,
			'class'       => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
			'atts'        => array(
				'data-validation' => 'phone',
			),
		),
		'address_1'  => array(
			'id'          => 'address_1',
			'type'        => 'text',
			'label'       => __( 'Address 1', 'easycommerce' ),
			'placeholder' => __( 'Your address 1', 'easycommerce' ),
			'required'    => true,
			'class'       => 'easycommerce-checkout_field-wrapper',
		),
		'address_2'  => array(
			'id'          => 'address_2',
			'type'        => 'text',
			'label'       => __( 'Address 2', 'easycommerce' ),
			'placeholder' => __( 'Your address 2', 'easycommerce' ),
			'required'    => false,
			'class'       => 'easycommerce-checkout_field-wrapper',
		),
		'country'    => array(
			'id'       => 'country',
			'type'     => 'select',
			'label'    => __( 'Country', 'easycommerce' ),
			'required' => true,
			'class'    => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
			// A blank first option stops the browser preselecting a country.
			'options'  => array( '' => __( 'Select a country', 'easycommerce' ) ) + easycommerce_countries(),
		),
		'state'      => array(
			'id'          => 'state',
			'type'        => 'select',
			'label'       => __( 'State', 'easycommerce' ),
			'placeholder' => __( 'Your State', 'easycommerce' ),
			'required'    => false,
			'class'       => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
		),
		'city'       => array(
			'id'          => 'city',
			'type'        => 'select',
			'label'       => __( 'City', 'easycommerce' ),
			'placeholder' => __( 'Your City', 'easycommerce' ),
			'required'    => true,
			'class'       => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
		),
		'postcode'   => array(
			'id'          => 'postcode',
			'type'        => 'text',
			'label'       => __( 'Postcode', 'easycommerce' ),
			'placeholder' => __( 'Write here', 'easycommerce' ),
			'required'    => false,
			'class'       => 'easycommerce-col-half easycommerce-checkout_field-wrapper',
			'atts'        => array(
				'data-validation' => 'postcode',
			),
		),
	),
);

$easycommerce_checkout_fields['billing'] = apply_filters(
	'easycommerce_checkout_fields_billing',
	array_map(
		function ( $field ) {
			$field['atts']['data-field_id'] = $field['id'];
			return $field;
		},
		$easycommerce_checkout_fields['billing']
	)
);

$easycommerce_checkout_fields['shipping'] = apply_filters(
	'easycommerce_checkout_fields_shipping',
	array_map(
		function ( $field ) {
			$field['required'] = false;

			if ( $field['id'] === 'country' ) {
				$field['options'] = array( '' => __( 'Select a country', 'easycommerce' ) ) + array_intersect_key(
					easycommerce_countries(),
					array_flip( (array) Utility::get_option( 'shipping', 'settings', 'countries', array_keys( easycommerce_countries() ) ) )
				);
			}

			return $field;
		},
		$easycommerce_checkout_fields['billing']
	)
);

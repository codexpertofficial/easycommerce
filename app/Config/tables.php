<?php

defined( 'ABSPATH' ) || exit;

global $easycommerce_tables;

$db = new EasyCommerce\Models\Database();

$easycommerce_tables = array(
	// AI usage log - one row per AI request (text, image, copilot/agent, search).
	'ai_logs'                      => array(
		'columns' => array(
			'id'         => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'user_id'    => 'BIGINT(20) UNSIGNED DEFAULT NULL',
			'type'       => 'VARCHAR(32) NOT NULL',
			'input'      => 'LONGTEXT',
			'output'     => 'LONGTEXT',
			'credit'     => 'INT(10) UNSIGNED DEFAULT 0',
			'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'indexes'     => array(
				'idx_user'    => 'user_id',
				'idx_created' => 'created_at',
			),
			'engine'      => 'InnoDB',
		),
	),

	// First, create the attributes table
	'attributes'                   => array(
		'columns' => array(
			'id'         => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'name'       => 'VARCHAR(255) NOT NULL',
			'slug'       => 'VARCHAR(255) NOT NULL UNIQUE',
			'type'       => 'VARCHAR(255) NOT NULL',
			'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'unique_keys' => array(
				'unique_slug' => 'slug',
			),
			'engine'      => 'InnoDB',
		),
	),

	// attribute_values table depends on attributes
	'attribute_values'             => array(
		'columns' => array(
			'id'           => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'attribute_id' => 'BIGINT(20) UNSIGNED NOT NULL',
			'name'         => 'VARCHAR(255) NOT NULL',
			'slug'         => 'VARCHAR(255) NOT NULL',
			'value'        => 'VARCHAR(255) NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_attribute_id' => 'attribute_id',
				'index_slug'         => 'slug',
			),
			'foreign_keys' => array(
				'fk_attribute_id' => array(
					'column'     => 'attribute_id',
					'ref_table'  => $db->get_prefix() . 'attributes',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// Tax Classes Table
	'tax_classes'                  => array(
		'columns' => array(
			'id'          => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'name'        => 'VARCHAR(255) NOT NULL UNIQUE',
			'description' => 'TEXT DEFAULT NULL',
			'status'      => 'TINYINT(1) NOT NULL DEFAULT 1',
			'created_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'unique_keys' => array(
				'unique_name' => 'name',
			),
			'engine'      => 'InnoDB',
		),
	),

	// Tax Rates Table
	'tax_rates'                    => array(
		'columns' => array(
			'id'           => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'tax_class_id' => 'BIGINT(20) UNSIGNED NOT NULL',
			'country'      => 'VARCHAR(2) DEFAULT NULL',
			'state'        => 'VARCHAR(50) DEFAULT NULL',
			'city'         => 'VARCHAR(50) DEFAULT NULL',
			'postcode'     => 'VARCHAR(20) DEFAULT NULL',
			'rate'         => 'DECIMAL(10, 4) NOT NULL',
			'priority'     => 'TINYINT NOT NULL DEFAULT 1',
			'compound'     => 'TINYINT(1) NOT NULL DEFAULT 0',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_tax_class_id' => 'tax_class_id',
				'index_country'      => 'country',
				'index_state'        => 'state',
				'index_city'         => 'city',
				'index_postcode'     => 'postcode',
			),
			'foreign_keys' => array(
				'fk_tax_class_id' => array(
					'column'     => 'tax_class_id',
					'ref_table'  => $db->get_prefix() . 'tax_classes',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// product_meta table depends on WordPress posts (products)
	'product_meta'                 => array(
		'columns' => array(
			'id'         => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'product_id' => 'BIGINT(20) UNSIGNED NOT NULL',
			'meta_key'   => 'VARCHAR(255) NOT NULL',
			'meta_value' => 'TEXT NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_product_id'       => 'product_id',
				'index_meta_key'         => 'meta_key',
				'idx_product_meta_lookup' => 'product_id, meta_key(191)',
			),
			'foreign_keys' => array(
				'fk_product_id' => array(
					'column'     => 'product_id',
					'ref_table'  => $db->get_wp_prefix() . 'posts',
					'ref_column' => 'ID',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// product_variations table depends on WordPress posts (products)
	'product_variations'           => array(
		'columns' => array(
			'id'             => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'product_id'     => 'BIGINT(20) UNSIGNED NOT NULL',
			'price_id'       => 'SMALLINT(4) UNSIGNED NOT NULL',
			'name'           => 'VARCHAR(255) NOT NULL',
			'sku'            => 'VARCHAR(100) NOT NULL UNIQUE',
			'type'           => 'VARCHAR(100) NOT NULL',
			'price'          => 'DECIMAL(10, 2) NOT NULL',
			'sale_price'     => 'DECIMAL(10, 2) DEFAULT NULL',
			'stock_quantity' => 'INT(10) DEFAULT NULL',
			'stock_limit'    => 'INT(10) DEFAULT NULL',
			'status'         => "ENUM('in_stock', 'out_of_stock', 'backorder', 'discontinued') NOT NULL DEFAULT 'in_stock'",
		),
		'options' => array(
			'primary_key'  => 'id',
			'unique_keys'  => array(
				'unique_sku' => 'sku',
			),
			'indexes'      => array(
				'index_product_id'    => 'product_id',
				'idx_product_price_id' => 'product_id, price_id',
			),
			'foreign_keys' => array(
				'fk_product_id' => array(
					'column'     => 'product_id',
					'ref_table'  => $db->get_wp_prefix() . 'posts',
					'ref_column' => 'ID',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// product_variation_meta depends on product_variations
	'product_variation_meta'       => array(
		'columns' => array(
			'id'           => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'variation_id' => 'BIGINT(20) UNSIGNED NOT NULL',
			'meta_key'     => 'VARCHAR(255) NOT NULL',
			'meta_value'   => 'TEXT NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_variation_id'        => 'variation_id',
				'index_meta_key'            => 'meta_key',
				'idx_variation_meta_lookup' => 'variation_id, meta_key(191)',
			),
			'foreign_keys' => array(
				'fk_variation_id' => array(
					'column'     => 'variation_id',
					'ref_table'  => $db->get_prefix() . 'product_variations',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// product_variation_attributes depends on product_variations and attributes
	'product_variation_attributes' => array(
		'columns' => array(
			'id'             => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'variation_id'   => 'BIGINT(20) UNSIGNED NOT NULL',
			'attribute_id'   => 'BIGINT(20) UNSIGNED NOT NULL',
			'attribute_slug' => 'VARCHAR(255) NOT NULL',
			'value_id'       => 'BIGINT(20) UNSIGNED NOT NULL',
			'value_slug'     => 'VARCHAR(255) NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_variation_id' => 'variation_id',
				'index_attribute_id' => 'attribute_id',
				'index_value_id'     => 'value_id',
			),
			'foreign_keys' => array(
				'fk_variation_id' => array(
					'column'     => 'variation_id',
					'ref_table'  => $db->get_prefix() . 'product_variations',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
				'fk_attribute_id' => array(
					'column'     => 'attribute_id',
					'ref_table'  => $db->get_prefix() . 'attributes',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
				'fk_value_id'     => array(
					'column'     => 'value_id',
					'ref_table'  => $db->get_prefix() . 'attribute_values',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// product_variation_downloads depends on product_variations
	'product_variation_downloads'  => array(
		'columns' => array(
			'id'           => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'variation_id' => 'BIGINT(20) UNSIGNED NOT NULL',
			'media_id'     => 'BIGINT(20) UNSIGNED NOT NULL',
			'name'         => 'VARCHAR(255) NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_variation_id' => 'variation_id',
			),
			'foreign_keys' => array(
				'fk_variation_id' => array(
					'column'     => 'variation_id',
					'ref_table'  => $db->get_prefix() . 'product_variations',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// orders table should be created before transactions since transactions reference orders
	'orders'                       => array(
		'columns' => array(
			'id'             => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'customer_id'    => 'BIGINT(20) UNSIGNED NULL DEFAULT NULL',
			'total'          => 'DECIMAL(10, 2) NOT NULL',
			'status'         => "ENUM('pending', 'processing', 'completed', 'cancelled', 'on_hold', 'partially_refunded', 'refunded', 'failed' ) NOT NULL DEFAULT 'pending'",
			'fulfill_status' => "ENUM('unfulfilled', 'fulfilled', 'partially_fulfilled', 'shipped', 'delivered', 'returned') NOT NULL DEFAULT 'unfulfilled'",
			'payment_method' => 'VARCHAR(255) NOT NULL',
			'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_customer_id'   => 'customer_id',
				'idx_status_created'  => 'status, created_at',
			),
			'foreign_keys' => array(
				'fk_customer_id' => array(
					'column'     => 'customer_id',
					'ref_table'  => $db->get_wp_prefix() . 'users',
					'ref_column' => 'ID',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// transactions table references orders (many-to-one relationship)
	'transactions'                 => array(
		'columns' => array(
			'id'              => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'order_id'        => 'BIGINT(20) UNSIGNED NOT NULL',
			'customer_id'     => 'BIGINT(20) UNSIGNED NOT NULL',
			'transaction_id'  => 'VARCHAR(255) NOT NULL',
			'payment_gateway' => 'VARCHAR(255) NOT NULL',
			'amount'          => 'DECIMAL(10, 2) NOT NULL',
			'currency'        => 'VARCHAR(3) NOT NULL',
			'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'status'          => "ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending'", // @todo improve
			'type'            => "ENUM('payment', 'refund', 'adjustment') NOT NULL DEFAULT 'payment'", // @todo improve
		),
		'options' => array(
			'primary_key'  => 'id',
			'unique_keys'  => array(
				// A gateway transaction id must be unique so a duplicate payment
				// record can never be inserted (payment audit / reconciliation).
				'uk_transaction_id' => 'transaction_id',
			),
			'indexes'      => array(
				'index_order_id'      => 'order_id',
				'index_customer_id'   => 'customer_id',
				'index_status'        => 'status',
				'index_created_at'    => 'created_at',
			),
			'foreign_keys' => array(
				'fk_order_id' => array(
					'column'     => 'order_id',
					'ref_table'  => $db->get_prefix() . 'orders',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// Order Items Table
	'order_items'                  => array(
		'columns' => array(
			'id'           => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'order_id'     => 'BIGINT(20) UNSIGNED NOT NULL',
			'product_id'   => 'BIGINT(20) UNSIGNED NOT NULL',
			'price_id'     => 'BIGINT(20) UNSIGNED DEFAULT NULL',
			'variation_id' => 'BIGINT(20) UNSIGNED DEFAULT NULL',
			'quantity'     => 'INT(10) NOT NULL DEFAULT 1',
			'rate'         => 'DECIMAL(10, 2)',
			'price'        => 'DECIMAL(10, 2)',
			'tax_class_id' => 'BIGINT(20) UNSIGNED',
			'tax_rate'     => 'DECIMAL(10, 4)',
			'subtotal'     => 'DECIMAL(10, 2)',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_order_id'     => 'order_id',
				'index_product_id'   => 'product_id',
				'index_variation_id' => 'variation_id',
			),
			'foreign_keys' => array(
				'fk_order_id' => array(
					'column'     => 'order_id',
					'ref_table'  => $db->get_prefix() . 'orders',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// order_item_meta depends on order_items
	'order_item_meta'              => array(
		'columns' => array(
			'id'            => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'order_item_id' => 'BIGINT(20) UNSIGNED NOT NULL',
			'meta_key'      => 'VARCHAR(255) NOT NULL',
			'meta_value'    => 'TEXT NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_order_item_id'        => 'order_item_id',
				'idx_order_item_meta_lookup' => 'order_item_id, meta_key(191)',
			),
			'foreign_keys' => array(
				'fk_order_item_id' => array(
					'column'     => 'order_item_id',
					'ref_table'  => $db->get_prefix() . 'order_items',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// order_meta depends on orders
	'order_meta'                   => array(
		'columns' => array(
			'id'         => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'order_id'   => 'BIGINT(20) UNSIGNED NOT NULL',
			'meta_key'   => 'VARCHAR(255) NOT NULL',
			'meta_value' => 'TEXT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_order_id'       => 'order_id',
				'index_meta_key'       => 'meta_key',
				'idx_order_meta_lookup' => 'order_id, meta_key(191)',
			),
			'foreign_keys' => array(
				'fk_order_id' => array(
					'column'     => 'order_id',
					'ref_table'  => $db->get_prefix() . 'orders',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// cart_sessions table is independent
	'cart_sessions'                => array(
		'columns' => array(
			'id'               => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'hash'             => 'VARCHAR(255) NOT NULL UNIQUE',
			'data'             => 'LONGTEXT NOT NULL',
			'total'            => 'DECIMAL(10, 2) NOT NULL',
			'status'           => "ENUM('pending', 'abandoned', 'cancelled', 'completed', 'payment_initiated') DEFAULT 'pending'",
			'payment_attempts' => 'INT(10) UNSIGNED DEFAULT 0',
			'user_id'          => 'BIGINT(20) UNSIGNED DEFAULT NULL',
			'customer_name'    => 'VARCHAR(255) DEFAULT NULL',
			'customer_email'   => 'VARCHAR(255) DEFAULT NULL',
			'reminders'        => 'INT(10) DEFAULT 0',
			'created_at'       => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at'       => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'indexes'     => array(
				'index_user_id'        => 'user_id',
				'index_hash'           => 'hash',
				'index_status'         => 'status',
				'index_customer_email' => 'customer_email',
				'idx_status_updated'   => 'status, updated_at',
			),
			'engine'      => 'InnoDB',
		),
	),

	// shipping_plans Table
	'shipping_plans'               => array(
		'columns' => array(
			'id'               => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'name'             => 'VARCHAR(255) NOT NULL',
			'description'      => 'TEXT DEFAULT NULL',
			'active'           => 'TINYINT(1) NOT NULL DEFAULT 1',
			'taxable'          => 'TINYINT(1) NOT NULL DEFAULT 1',
			'calculation_base' => "ENUM('price', 'weight', 'quantity') NOT NULL",
		),
		'options' => array(
			'primary_key' => 'id',
			'engine'      => 'InnoDB',
		),
	),

	// shipping_plan_methods Table
	'shipping_plan_methods'        => array(
		'columns' => array(
			'id'      	=> 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'plan_id' 	=> 'BIGINT(20) UNSIGNED NOT NULL',
			'name'    	=> 'VARCHAR(255) NOT NULL',
			'min_unit'	=> 'VARCHAR(255) NOT NULL',
			'min'     	=> 'DECIMAL(10, 2) NOT NULL DEFAULT 0.00',
			'max_unit'	=> 'VARCHAR(255) NOT NULL',
			'max'     	=> 'DECIMAL(10, 2) DEFAULT NULL',
			'cost'    	=> 'DECIMAL(10, 2) NOT NULL DEFAULT 0.00',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_plan_id' => 'plan_id',
			),
			'foreign_keys' => array(
				'fk_plan_id' => array(
					'column'     => 'plan_id',
					'ref_table'  => $db->get_prefix() . 'shipping_plans',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// shipping_plan_regions Table
	'shipping_plan_regions'        => array(
		'columns' => array(
			'id'          => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'plan_id'     => 'BIGINT(20) UNSIGNED NOT NULL',
			'region_code' => 'VARCHAR(255) NOT NULL',
			'zip_code'	  => 'VARCHAR(20) NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_plan_id' => 'plan_id',
			),
			'foreign_keys' => array(
				'fk_plan_id' => array(
					'column'     => 'plan_id',
					'ref_table'  => $db->get_prefix() . 'shipping_plans',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// Coupons Table
	'coupons'                      => array(
		'columns' => array(
			'id'            => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'status'        => 'TINYINT(1) NOT NULL DEFAULT 1',
			'name'          => 'VARCHAR(255) NOT NULL',
			'code'          => 'VARCHAR(50) NOT NULL UNIQUE',
			'type'          => 'VARCHAR(20) NOT NULL',
			'offer'         => 'TEXT NOT NULL',
			'active'        => 'TINYINT(1) NOT NULL DEFAULT 1',
			'created_at'    => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at'    => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'unique_keys' => array(
				'unique_code' => 'code',
			),
			'engine'      => 'InnoDB',
		),
	),

	// Coupon Rules Table
	'coupon_rules'                 => array(
		'columns' => array(
			'id'        => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'coupon_id' => 'BIGINT(20) UNSIGNED NOT NULL',
			'type'      => 'VARCHAR(50) NOT NULL',
			'value'     => 'TEXT NOT NULL',
		),
		'options' => array(
			'primary_key'  => 'id',
			'indexes'      => array(
				'index_coupon_id' => 'coupon_id',
			),
			'foreign_keys' => array(
				'fk_coupon_id' => array(
					'column'     => 'coupon_id',
					'ref_table'  => $db->get_prefix() . 'coupons',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
			),
			'engine'       => 'InnoDB',
		),
	),

	// Logs Table
	'logs' => array(
		'columns' => array(
			'id'         => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'object'     => 'VARCHAR(50) NOT NULL',
			'action'     => 'VARCHAR(50) NOT NULL',
			'object_id'  => 'BIGINT(20) UNSIGNED DEFAULT NULL',
			'user_id'    => 'BIGINT(20) UNSIGNED DEFAULT NULL',
			'note'       => 'TEXT DEFAULT NULL',
			'ip_address' => 'VARCHAR(45) DEFAULT NULL',
			'seen'       => 'TINYINT(1) NOT NULL DEFAULT 0',
			'type'       => 'VARCHAR(40) NOT NULL DEFAULT "info"',
			'meta'		 => 'LONGTEXT DEFAULT NULL',
			'is_public'  => 'TINYINT(1) NOT NULL DEFAULT 1',
			'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'indexes'     => array(
				'index_object'      => 'object',
				'index_object_id'   => 'object_id',
				'index_created_at'  => 'created_at',
				'index_user_id'     => 'user_id',
				'index_is_public'   => 'is_public',
			),
			'engine'      => 'InnoDB',
		),
	),

	// refunds table references orders
	'refunds' => array(
		'columns' => array(
			'id'              => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'order_id'        => 'BIGINT(20) UNSIGNED NOT NULL',
			'amount'          => 'DECIMAL(10, 2) NOT NULL',
			'currency'        => 'VARCHAR(3) NOT NULL',
			'reason'          => 'VARCHAR(255) DEFAULT NULL',
			'status'          => "ENUM('pending', 'approved', 'processed', 'rejected') NOT NULL DEFAULT 'pending'",
			'transaction_id'  => 'VARCHAR(255) DEFAULT NULL',
			'payment_gateway' => 'VARCHAR(255) NOT NULL',
			'notes'           => 'TEXT DEFAULT NULL',
			'refunded_by'     => 'BIGINT(20) UNSIGNED DEFAULT NULL',
			'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'indexes'     => array(
				'index_order_id'        => 'order_id',
				'index_payment_gateway' => 'payment_gateway',
			),
			'foreign_keys' => array(
				'fk_order_id' => array(
					'column'     => 'order_id',
					'ref_table'  => $db->get_prefix() . 'orders',
					'ref_column' => 'id',
					'on_delete'  => 'CASCADE',
					'on_update'  => 'CASCADE',
				),
				'fk_refunded_by' => array(
					'column'     => 'refunded_by',
					'ref_table'  => $db->get_wp_prefix() . 'users',
					'ref_column' => 'ID',
					'on_delete'  => 'SET NULL',
					'on_update'  => 'CASCADE',
				),
			),
			'engine' => 'InnoDB',
		),
	),

	'agent_sessions' => array(
		'columns' => array(
			'id'         => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
			'session_id' => 'VARCHAR(255) NOT NULL',
			'history'    => 'LONGTEXT NOT NULL',
			'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		),
		'options' => array(
			'primary_key' => 'id',
			'unique_keys' => array(
				'unique_session_id' => 'session_id',
			),
			'engine'      => 'InnoDB',
		),
	),
);

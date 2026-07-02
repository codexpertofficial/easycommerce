<?php
namespace EasyCommerce\Bootstrap\Activator;

defined( 'ABSPATH' ) || exit;

class User_Role {

	/**
	 * Registers custom user roles.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Fires before registering user roles.
		 */
		do_action( 'easycommerce_before_register_user_roles' );

		/**
		 * Filters the capabilities for the Manager role.
		 *
		 * @param array $capabilities Default capabilities for Manager.
		 */
		$manager_capabilities = apply_filters(
			'easycommerce_manager_capabilities',
			array(
				'read'                      => true,
				'edit_posts'                => true,
				'delete_posts'              => true,
				'publish_posts'             => true,
				'upload_files'              => true,
				'manage_categories'         => true,
				'manage_easycommerce'       => true,
				'view_easycommerce_reports' => true,
				'edit_products'             => true,
				'edit_others_products'      => true,
				'publish_products'          => true,
				'read_private_products'     => true,
				'delete_products'           => true,
				'delete_private_products'   => true,
				'delete_published_products' => true,
				'delete_others_products'    => true,
				'edit_private_products'     => true,
				'edit_published_products'   => true,
				'manage_product_terms'      => true,
				'assign_product_terms'      => true,
				'read_product'              => true,
			)
		);

		add_role( 'manager', __( 'Manager', 'easycommerce' ), $manager_capabilities );

		/**
		 * Filters the capabilities for the Customer role.
		 *
		 * @param array $capabilities Default capabilities for Customer.
		 */
		$customer_capabilities = apply_filters(
			'easycommerce_customer_capabilities',
			array(
				'read'                      => true,
				'edit_posts'                => false,
				'delete_posts'              => false,
				'publish_posts'             => false,
				'upload_files'              => true,
				'edit_files'                => true,
				'delete_files'              => true,
				'manage_easycommerce'       => false,
				'view_easycommerce_reports' => false,
				'edit_products'             => false,
				'edit_others_products'      => false,
				'publish_products'          => false,
				'read_private_products'     => false,
				'delete_products'           => false,
				'delete_private_products'   => false,
				'delete_published_products' => false,
				'delete_others_products'    => false,
				'edit_private_products'     => false,
				'edit_published_products'   => false,
				'manage_product_terms'      => false,
				'assign_product_terms'      => false,
				'read_product'              => false,
				'has_order'                 => true,
			)
		);

		add_role( 'customer', __( 'Customer', 'easycommerce' ), $customer_capabilities );

		/**
		 * Fires after registering user roles.
		 */
		do_action( 'easycommerce_after_register_user_roles' );
	}
}

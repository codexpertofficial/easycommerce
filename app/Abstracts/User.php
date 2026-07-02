<?php
namespace EasyCommerce\Abstracts;

defined( 'ABSPATH' ) || exit;

use WP_User_Query as User_Query;
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Traits\Cache;

/**
 * Abstract User Class
 */
abstract class User {

	use Cache;

	/**
	 * User ID
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * User email
	 *
	 * @var string
	 */
	protected $email = '';

	/**
	 * User display name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * User first name
	 *
	 * @var string
	 */
	protected $first_name = '';

	/**
	 * User last name
	 *
	 * @var string
	 */
	protected $last_name = '';

	/**
	 * User role
	 *
	 * @var string
	 */
	protected $role = '';

	/**
	 * User password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * User join date
	 *
	 * @var string
	 */
	protected $join_date;

	/**
	 * Constructor
	 *
	 * @param int|null $id User ID.
	 */
	public function __construct( $id = null ) {
		if ( $id && $user = get_userdata( $id ) ) {
			$this->id         = $id;
			$this->email      = $user->user_email;
			$this->name       = $user->display_name;
			$this->first_name = $user->get_meta( 'first_name' );
			$this->last_name  = $user->get_meta( 'last_name' );
			$this->role       = $user->roles[0];
			$this->join_date  = $user->user_registered;

			/**
			 * Fires after a user is loaded.
			 *
			 * @since 1.9
			 * @param int   $id   User ID.
			 * @param User $user User instance.
			 */
			do_action( 'easycommerce_user_loaded', $id, $this );
		}
	}

	/**
	 * Get user ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get user email.
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get user name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set user first name.
	 */
	public function set_first_name( $first_name ) {
		$this->update_meta( 'first_name', $first_name );
	}

	/**
	 * Get user first name.
	 *
	 * @return string
	 */
	public function get_first_name() {
		return $this->get_meta( 'first_name' );
	}

	/**
	 * Get user last name.
	 *
	 * @return string
	 */
	public function get_last_name() {
		return $this->get_meta( 'last_name' );
	}

	/**
	 * Set user last name.
	 */
	public function set_last_name( $last_name ) {
		$this->update_meta( 'last_name', $last_name );
	}

	/**
	 * Get user role.
	 *
	 * @return string
	 */
	public function get_role() {
		return $this->role;
	}

	/**
	 * Set user email.
	 *
	 * @param string $email
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Set user photo.
	 *
	 * @param string $photo
	 */
	public function set_photo( $photo = '' ) {
		$this->update_meta( 'photo', $photo );
	}

	/**
	 * Get user photo.
	 *
	 * @return string $photo
	 */
	public function get_photo() {
		return $this->get_meta( 'photo' );
	}

	/**
	 * Set user name.
	 *
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Set user role.
	 *
	 * @param string $role
	 */
	public function set_role( $role ) {
		$this->role = $role;
	}

	public function get_join_date() {
		return Utility::format_date( $this->join_date );
	}

	/**
	 * Save user data.
	 *
	 * @return bool
	 */
	public function save() {
		/**
		 * Fires before saving user data.
		 *
		 * @since 1.9
		 * @param User $user User instance.
		 */
		do_action( 'easycommerce_before_user_save', $this );

		if ( $this->id ) {
			// Update existing user
			$userdata = array(
				'ID'           => $this->id,
				'user_email'   => $this->email,
				'display_name' => $this->name,
				'first_name'   => $this->first_name,
				'last_name'    => $this->last_name,
			);

			if ( ! is_wp_error( $user_id = wp_update_user( $userdata ) ) ) {
				$existing_user = new \WP_User( $user_id );
				$existing_user->add_role( $this->role );

				do_action( 'easycommerce_user_updated', $user_id, $this );
			}
		} else {
			// Create new user
			$userdata = array(
				'user_email'   => $this->email,
				'user_login'   => $this->email,
				'display_name' => $this->name,
				'first_name'   => $this->first_name,
				'last_name'    => $this->last_name,
				'role'         => $this->role,
				'user_pass'    => $this->password,
			);

			$user_id = wp_insert_user( $userdata );

			if ( ! is_wp_error( $user_id ) ) {
				$this->id = $user_id;

				do_action( 'easycommerce_user_created', $user_id, $this );
			}
		}

		return ! is_wp_error( $user_id );
	}

	/**
	 * Delete user.
	 *
	 * @return bool
	 */
	public function delete() {

		if ( ! user_can( $this->id, 'manage_options' ) ) {
			/**
			 * Fires before deleting a user.
			 *
			 * @since 1.9
			 * @param int $user_id User ID.
			 */
			do_action( 'easycommerce_before_user_delete', $this->id );

			$result = wp_delete_user( $this->id );

			if ( $result ) {
				/**
				 * Fires after deleting a user.
				 *
				 * @since 1.9
				 * @param int $user_id User ID.
				 */
				do_action( 'easycommerce_after_user_delete', $this->id );
			}

			return $result;
		}

		return false;
	}

	/**
	 * Add user meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function add_meta( $key, $value ) {
		/**
		 * Fires before adding user meta.
		 *
		 * @since 1.9
		 * @param string $key   Meta key.
		 * @param mixed  $value Meta value.
		 * @param int    $user_id User ID.
		 */
		do_action( 'easycommerce_before_add_user_meta', $key, $value, $this->id );

		$result = add_user_meta( $this->id, $key, $value );

		/**
		 * Filters the result of adding user meta.
		 *
		 * @since 1.9
		 * @param bool   $result  Result of add_user_meta.
		 * @param string $key     Meta key.
		 * @param mixed  $value   Meta value.
		 * @param int    $user_id User ID.
		 */
		return apply_filters( 'easycommerce_add_user_meta', $result, $key, $value, $this->id );
	}

	/**
	 * Get user meta data.
	 *
	 * @param string $key
	 * @param bool   $single
	 * @return mixed
	 */
	public function get_meta( $key, $single = true ) {
		$value = get_user_meta( $this->id, $key, $single );

		/**
		 * Filters the retrieved user meta value.
		 *
		 * @since 1.9
		 * @param mixed  $value   Meta value.
		 * @param string $key     Meta key.
		 * @param bool   $single  Whether to return single value.
		 * @param int    $user_id User ID.
		 */
		return apply_filters( 'easycommerce_get_user_meta', $value, $key, $single, $this->id );
	}

	/**
	 * Update user meta data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function update_meta( $key, $value ) {
		$blocked_keys = array(
			'wp_capabilities',
			'wp_user_level',
			'wp_user-settings',
		);

		if ( in_array( $key, $blocked_keys, true ) ) {
			return false;
		}

		/**
		 * Fires before updating user meta.
		 *
		 * @since 1.9
		 * @param string $key     Meta key.
		 * @param mixed  $value   Meta value.
		 * @param int    $user_id User ID.
		 */
		do_action( 'easycommerce_before_update_user_meta', $key, $value, $this->id );

		$result = update_user_meta( $this->id, $key, $value );

		/**
		 * Filters the result of updating user meta.
		 *
		 * @since 1.9
		 * @param bool   $result  Result of update_user_meta.
		 * @param string $key     Meta key.
		 * @param mixed  $value   Meta value.
		 * @param int    $user_id User ID.
		 */
		return apply_filters( 'easycommerce_update_user_meta', $result, $key, $value, $this->id );
	}

	/**
	 * Delete user meta data.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function delete_meta( $key ) {
		/**
		 * Fires before deleting user meta.
		 *
		 * @since 1.9
		 * @param string $key     Meta key.
		 * @param int    $user_id User ID.
		 */
		do_action( 'easycommerce_before_delete_user_meta', $key, $this->id );

		$result = delete_user_meta( $this->id, $key );

		/**
		 * Filters the result of deleting user meta.
		 *
		 * @since 1.9
		 * @param bool   $result  Result of delete_user_meta.
		 * @param string $key     Meta key.
		 * @param int    $user_id User ID.
		 */
		return apply_filters( 'easycommerce_delete_user_meta', $result, $key, $this->id );
	}

	/**
	 * Create a new user.
	 *
	 * @param array $args
	 * @return bool
	 */
	public function create( $args ) {
		/**
		 * Fires before creating a user.
		 *
		 * @since 1.9
		 * @param array $args User creation arguments.
		 */
		do_action( 'easycommerce_before_user_create', $args );

		if ( empty( $args['email'] ) || empty( $args['name'] ) ) {
			return false;
		}

		if ( $user_id = email_exists( $args['email'] ) ) {
			$this->id = $user_id;
		}

		$this->email      = $args['email'];
		$this->name       = $args['name'];
		$this->first_name = $args['first_name'];
		$this->last_name  = $args['last_name'];
		// Prevent user-supplied role for security reasons (CVE-2025-11457)
		$this->role       = $this->role;
		$this->password   = isset( $args['password'] ) ? $args['password'] : wp_generate_password();

		$created = $this->save();

		if ( $created && ! empty( $args['meta'] ) ) {
			foreach ( $args['meta'] as $key => $value ) {
				$this->add_meta( $key, $value );
			}
		}

		return $created;
	}

	/**
	 * Set user password.
	 *
	 * @param string $password
	 */
	public function set_password( $password ) {
		/**
		 * Fires before setting user password.
		 *
		 * @since 1.9
		 * @param int $user_id User ID.
		 */
		do_action( 'easycommerce_before_set_user_password', $this->id );

		wp_set_password( $password, $this->id );

		/**
		 * Fires after setting user password.
		 *
		 * @since 1.9
		 * @param int $user_id User ID.
		 */
		do_action( 'easycommerce_after_set_user_password', $this->id );
	}

	/**
	 * @param int $expiry Time to expire the link, in minutes
	 */
	public function get_login_link( $expiry = 1, $redirect = '' ) {

		$expiry   = max( $expiry, 0 );
		$redirect = ! empty( $redirect ) ? $redirect : home_url();
		$key      = 'temp_login_' . $this->id;

		// If no token is found in the cache, generate a new one and store it in the cache
		if ( ! $token = $this->get_cache( $key ) ) {
			$token = Utility::generate_hash();
			$this->set_cache( $key, $token, $expiry * MINUTE_IN_SECONDS );
		}

		$url = add_query_arg(
			array(
				'user_id' => $this->id,
				'token'   => $token,
			),
			untrailingslashit( esc_url( $redirect ) )
		);

		/**
		 * Filters the user login link.
		 *
		 * @since 1.9
		 * @param string $url      Login URL.
		 * @param int    $user_id  User ID.
		 * @param int    $expiry   Expiry time in minutes.
		 * @param string $redirect Redirect URL.
		 */
		return apply_filters( 'easycommerce_user_login_link', $url, $this->id, $expiry, $redirect );
	}

	/**
	 * List users by role with optional filters such as search query, page, and per_page.
	 *
	 * @param string      $role
	 * @param string|null $search
	 * @param int         $page
	 * @param int         $per_page
	 * @return array List of users with pagination info
	 */
	public static function list( $role, $search = null, $page = 1, $per_page = 10 ) {

		$args = array(
			'role'           => $role,
			'number'         => $per_page,
			'paged'          => $page,
			'search'         => $search ? '*' . esc_attr( $search ) . '*' : '',
			'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
		);

		/**
		 * Filters the user list query arguments.
		 *
		 * @since 1.9
		 * @param array  $args    Query arguments.
		 * @param string $role    User role.
		 * @param string $search  Search query.
		 * @param int    $page    Page number.
		 * @param int    $per_page Number of users per page.
		 */
		$args = apply_filters( 'easycommerce_user_list_args', $args, $role, $search, $page, $per_page );

		$query       = new User_Query( $args );
		$users       = $query->get_results();
		$total_users = $query->get_total();

		/**
		 * Filters the user list results.
		 *
		 * @since 1.9
		 * @param array $users Query results.
		 * @param array $args  Query arguments.
		 */
		$users = apply_filters( 'easycommerce_user_list_results', $users, $args );

		if ( empty( $users ) ) {
			return array(
				'users'       => array(),
				'total'       => 0,
				'per_page'    => $per_page,
				'page'        => $page,
				'total_pages' => 0,
			);
		}

		$formatted_users = array_map(
			function ( $user ) {
				return array(
					'id'    => $user->ID,
					'name'  => $user->display_name,
					'email' => $user->user_email,
					'role'  => implode( ', ', $user->roles ),
				);
			},
			$users
		);

		/**
		 * Filters the formatted user list.
		 *
		 * @since 1.9
		 * @param array $formatted_users Formatted user data.
		 * @param array $users           Raw user objects.
		 */
		$formatted_users = apply_filters( 'easycommerce_user_list_formatted', $formatted_users, $users );

		$user_list = array(
			'users'       => $formatted_users,
			'total'       => $total_users,
			'per_page'    => $per_page,
			'page'        => $page,
			'total_pages' => ceil( $total_users / $per_page ),
		);

		/**
		 * Filters the complete user list response.
		 *
		 * @since 1.9
		 * @param array  $user_list User list data.
		 * @param string $role      User role.
		 * @param string $search    Search query.
		 * @param int    $page      Page number.
		 * @param int    $per_page  Number of users per page.
		 */
		return apply_filters( 'easycommerce_user_list', $user_list, $role, $search, $page, $per_page );
	}
}

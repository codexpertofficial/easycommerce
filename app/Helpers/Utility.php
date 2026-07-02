<?php
namespace EasyCommerce\Helpers;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Location;
use EasyCommerce\Models\Product;
use EasyCommerce\Traits\Cache;

/**
 * Utility class with static helper functions for general use throughout the plugin.
 */
class Utility {

	use Cache;

	/**
	 * Retrieves an option from the WordPress database, formatted according to EasyCommerce settings.
	 *
	 * This function gets an option using a combination of the provided menu, submenu, and key.
	 * If the option is not set, a default value is returned.
	 *
	 * @param string $menu The menu name or key to be used as part of the option name.
	 * @param string $submenu The submenu name or key to be used as part of the option name.
	 * @param string $key The specific option key to retrieve within the option array.
	 * @param mixed  $default Optional. The default value to return if the option key is not set. Default is an empty string.
	 *
	 * @return mixed The value of the option if it exists, or the default value if it doesn't.
	 */
	public static function get_option( $menu, $submenu, $key, $default = '' ) {
		$option = get_option( "easycommerce-{$menu}-{$submenu}" );

		if ( ! isset( $option[ $key ] ) || empty( $option[ $key ] ) ) {
			return $default;
		}

		return $option[ $key ];
	}

	/**
	 * Set an option value.
	 *
	 * @param string $menu The menu slug.
	 * @param string $submenu The submenu slug.
	 * @param string $key The option key.
	 * @param mixed  $value The value to set.
	 * @return bool True if the option was updated, false otherwise.
	 */
	public static function set_option( $menu, $submenu, $key, $value ) {
		$option_name = "easycommerce-{$menu}-{$submenu}";
		$option = get_option( $option_name, array() );

		$option[ $key ] = $value;

		return update_option( $option_name, $option );
	}

	/**
	 * Formats a date string according to WordPress settings.
	 *
	 * @param string $date The date string (e.g., 'Y-m-d H:i:s').
	 * @param string $format Optional. PHP date format. Defaults to WordPress date format setting.
	 * @return string Formatted date string.
	 */
	public static function format_date( $date, $format = '' ) {
		if ( empty( $format ) ) {
			$format = get_option( 'date_format' );
		}

		return date_i18n( $format, strtotime( $date ) );
	}

	/**
	 * Formats a time string according to WordPress settings.
	 *
	 * @param string $time The time string (e.g., 'Y-m-d H:i:s').
	 * @param string $format Optional. PHP time format. Defaults to 'h:i:s A'.
	 * @return string Formatted time string.
	 */
	public static function format_time( $time, $format = '' ) {
		if ( empty( $format ) ) {
			$format = 'h:i:s A';
		}

		return date_i18n( $format, strtotime( $time ) );
	}

	/**
	 * Formats a price
	 *
	 * @param float $price The price
	 * @return string Formatted price string.
	 */
	public static function format_price( $price ) {
		return easycommerce_price( $price );
	}

	/**
	 * Logs messages to a specific log file.
	 *
	 * @param mixed  $message The message to log. If not a string, it will be converted to JSON.
	 * @param string $log_file The log file to write to within the wp-content directory.
	 */
	public static function log_debug( $message, $log_file = 'debug.log' ) {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();

		if ( ! is_string( $message ) ) {
			$message = wp_json_encode( $message );
		}

		if ( ! file_exists( $log_path = WP_CONTENT_DIR . '/easycommerce-logs/' . $log_file ) ) {
			$wp_filesystem->mkdir( dirname( $log_path ) );
			$wp_filesystem->put_contents( $log_path, '', FS_CHMOD_FILE );
		}

		$log_entry = sprintf( "[%s] %s\n", current_time( 'mysql' ), $message );

		$wp_filesystem->put_contents( $log_path, $log_entry, FS_CHMOD_FILE | FILE_APPEND );
	}

	/**
	 * Error Log
	 */
	public static function el( $message, $log_file = 'debug.log' ) {

		$backtrace = debug_backtrace()[0];
		$file_path = $backtrace['file'];
		$file = basename( dirname( $file_path ) ) . '/' . basename( $file_path );
		$line = $backtrace['line'];

		// Try to detect the variable name (optional, works if passed literally)
		$name = null;
		if ( function_exists( 'token_get_all' ) ) {
			$source = file( $file_path );
			$code_line = $source[ $line - 1 ];
			if ( preg_match( '/pt_el\s*\(\s*\$(\w+)/', $code_line, $matches ) ) {
				$name = '$' . $matches[1];
			}
		}

		$label = $name ? "{$name}: " : '';
		
		$message = "{$file}#L{$line} {$label}" . maybe_serialize( $message );

		error_log( $message );
	}

	/**
	 * Prints information about a variable in a more readable format.
	 *
	 * @param mixed $data The variable you want to display.
	 * @param bool  $admin_only Should it display in wp-admin area only
	 * @param bool  $hide_adminbar Should it hide the admin bar
	 */
	public static function pri( $data, $admin_only = true, $hide_adminbar = true ) {
		if ( $admin_only && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<pre>';
		if ( is_object( $data ) || is_array( $data ) ) {
			print_r( $data );
		} else {
			var_dump( $data );
		}
		echo '</pre>';

		if ( is_admin() && $hide_adminbar ) {
			echo '<style>#adminmenumain{display:none;}</style>';
		}
	}

	/**
	 * Resolve the file to load for a template, allowing themes and addons to
	 * override it.
	 *
	 * Resolution order:
	 *   1. Theme override - wp-content/themes/<active-theme>/easycommerce/<template>
	 *   2. Plugin default - <plugin>/views/<template>
	 *
	 * The result is filterable through `easycommerce_template_path`, so an addon
	 * can point any EasyCommerce template at its own file:
	 *
	 *     add_filter( 'easycommerce_template_path', function ( $path, $template ) {
	 *         if ( 'templates/single-product/price.php' === $template ) {
	 *             return MY_ADDON_DIR . 'templates/price.php';
	 *         }
	 *         return $path;
	 *     }, 10, 2 );
	 *
	 * @param string $template Template path relative to the views/ directory.
	 * @return string Absolute path to the template that will be loaded.
	 */
	public static function get_template_path( $template ) {
		$theme_template = locate_template( array( 'easycommerce/' . $template ) );
		$path           = $theme_template ? $theme_template : EASYCOMMERCE_PLUGIN_DIR . 'views/' . $template;

		return apply_filters( 'easycommerce_template_path', $path, $template );
	}

	/**
	 * Includes a template file from the 'view' directory.
	 *
	 * Themes and addons can override the file via `easycommerce_template_path`
	 * (see get_template_path()), adjust the variables passed in via
	 * `easycommerce_template_args`, or filter the rendered output via
	 * `easycommerce_template_html`.
	 *
	 * @param string $template The template file name.
	 * @param array  $args Optional. An associative array of variables to pass to the template file.
	 */
	public static function get_template( $template, $args = array() ) {
		$args = apply_filters( 'easycommerce_template_args', $args, $template );

		$path = self::get_template_path( $template );

		if ( $path && file_exists( $path ) ) {
			if ( ! empty( $args ) && is_array( $args ) ) {
				extract( $args );
			}

			ob_start();
			include $path;

			return apply_filters( 'easycommerce_template_html', ob_get_clean(), $template, $args );
		}
	}

	/**
	 * @param bool $show_cached either to use a cached list of posts or not. If enabled, make sure to wp_cache_delete() with the `save_post` hook
	 */
	public static function get_posts( $args = array(), $show_heading = false, $show_cached = false, $key = 'ID' ) {

		$cacher = new self();

		$defaults = array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$_args = wp_parse_args( $args, $defaults );

		// use cache
		if ( true === $show_cached && ( $cached_posts = $cacher->get_cache( "{$_args['post_type']}" ) ) ) {
			$posts = $cached_posts;
		}

		// don't use cache
		else {
			$queried = new \WP_Query( $_args );

			$posts = array();
			foreach ( $queried->posts as $post ) :
				if ( isset( $post->$key ) ) {
					$posts[ $post->$key ] = $post->post_title;
				} else {
					$posts[ $post->ID ] = $post->post_title;
				}
			endforeach;

			$cacher->set_cache( "{$_args['post_type']}", $posts );
		}

		/* Translators: %s is the post type that the user should choose. */
		$posts = $show_heading ? array( '' => sprintf( __( '- Choose a %s -', 'easycommerce' ), $_args['post_type'] ) ) + $posts : $posts;

		return apply_filters( 'easycommerce_get_posts', $posts, $_args );
	}

	public static function create_post( $args ) {

		// Define default arguments
		$defaults = array(
			'title'   => 'Default Title',
			'content' => 'Default Content',
			'type'    => 'post',
			'status'  => 'publish',
			'author'  => get_current_user_id(),
		);

		// Merge provided args with defaults
		$args = wp_parse_args( $args, $defaults );

		// Insert the post
		$post_id = wp_insert_post(
			array(
				'post_title'   => $args['title'],
				'post_content' => $args['content'],
				'post_type'    => $args['type'],
				'post_status'  => $args['status'],
				'post_author'  => $args['author'],
			)
		);

		return $post_id;
	}

	/**
	 * Generates a hash
	 */
	public static function generate_hash() {
		return wp_hash( uniqid( wp_rand(), true ) );
	}

	/**
	 * Get date range
	 *
	 * @param string $range The date range to query.
	 * @return array
	 */
	public static function get_date_range( $range = 'all' ) {
		$to_date   = current_time( 'Y-m-d' );
		$from_date = '1970-01-01';
		switch ( $range ) {
			case 'today':
				$from_date = $to_date;
				break;

			case 'yesterday':
				$from_date = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
				$to_date   = $from_date;
				break;

			case 'this-week':
				$start_of_week    = get_option( 'start_of_week' );
				$current_day      = gmdate( 'w' );
				$days_to_subtract = ( $current_day - $start_of_week + 7 ) % 7;
				$from_date        = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days" ) );
				break;

			case 'last-week':
				$start_of_week   = get_option( 'start_of_week' ) - 1;
				$last_week_start = gmdate( 'Y-m-d', strtotime( 'last week +' . $start_of_week . ' days' ) );
				$from_date       = $last_week_start;
				$to_date         = gmdate( 'Y-m-d', strtotime( $last_week_start . ' +6 days' ) );
				break;

			case 'last-7':
				$from_date = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
				break;

			case 'this-month':
				$from_date = gmdate( 'Y-m-01' );
				break;

			case 'last-month':
				$from_date = gmdate( 'Y-m-01', strtotime( '-1 month' ) );
				$to_date   = gmdate( 'Y-m-t', strtotime( '-1 month' ) );

				break;

			case 'last-30':
				$from_date = gmdate( 'Y-m-d', strtotime( '-30 days' ) );

				break;

			case 'prev-7':
				$from_date = gmdate( 'Y-m-d', strtotime( '-14 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-8 days' ) );
				break;

			case 'prev-30':
				$from_date = gmdate( 'Y-m-d', strtotime( '-60 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-31 days' ) );
				break;

			case 'last-month-7':
				$from_date = gmdate( 'Y-m-d', strtotime( 'first day of last month +23 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( 'first day of last month +29 days' ) );
				break;

			case 'last-year-7':
				$from_date = gmdate( 'Y-m-d', strtotime( '-37 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-31 days' ) );
				break;

			case 'last-month-week':
				$start_of_week   = get_option( 'start_of_week' ) - 1;
				$week_start      = gmdate( 'Y-m-d', strtotime( 'last week last month +' . $start_of_week . ' days' ) );
				$from_date       = $week_start;
				$to_date         = gmdate( 'Y-m-d', strtotime( $week_start . ' +6 days' ) );
				break;

			case 'last-year-week':
				$start_of_week   = get_option( 'start_of_week' );
				$current_day     = gmdate( 'w' );
				$days_to_subtract = ( $current_day - $start_of_week + 7 ) % 7;
				$from_date       = gmdate( 'Y-m-d', strtotime( "-{$days_to_subtract} days -52 weeks" ) );
				$to_date         = gmdate( 'Y-m-d', strtotime( $from_date . ' +6 days' ) );
				break;

			case 'last-year-month':
				$from_date = gmdate( 'Y-m-01', strtotime( '-1 year' ) );
				$to_date   = gmdate( 'Y-m-t', strtotime( '-1 year' ) );
				break;

			case 'last-year-30':
				$from_date = gmdate( 'Y-m-d', strtotime( '-365 days' ) );
				$to_date   = gmdate( 'Y-m-d', strtotime( '-336 days' ) );
				break;

			case 'this-year':
				$from_date = gmdate( 'Y-01-01' );
				break;

			case 'last-year':
				$from_date = gmdate( 'Y-01-01', strtotime( '-1 year' ) );
				$to_date   = gmdate( 'Y-12-31', strtotime( '-1 year' ) );
				break;

			case 'all':
				$from_date = '1970-01-01';
		}

		return array( $from_date, $to_date );
	}

	/**
	 * Performs local fuzzy search on product titles using multiple matching strategies.
	 *
	 * The search uses the following priority order:
	 * 1. Exact substring match (case-insensitive)
	 * 2. Individual search words found in product title
	 * 3. Product title words containing the search term
	 * 4. Fuzzy word matching (70%+ similarity via similar_text)
	 * 5. Full title fuzzy matching (50%+ similarity)
	 * 6. Levenshtein distance algorithm for typo tolerance
	 *
	 * Results are cached for 6 hours to improve performance.
	 *
	 * @since 1.21
	 *
	 * @param string $search The search term to match against product titles.
	 * @return string|false   The matching product title on success, or false if no match found.
	 */
	public static function get_product_titles(): array {
		$cacher    = new self();
		$cache_key = 'product_titles';

		if ( false !== $cached = $cacher->get_cache( $cache_key ) ) {
			return $cached;
		}

		$result  = Product::list( [], -1, 0, false );
		$titles  = wp_list_pluck( $result['products'] ?? [], 'post_title' );

		if ( ! empty( $titles ) ) {
			$cacher->set_cache( $cache_key, $titles );
		}

		return $titles;
	}

	public static function fuzzy_search( $search ) {

		$product_titles = self::get_product_titles();

		if ( empty( $product_titles ) ) {
			return false;
		}

		$search_lower	= mb_strtolower( $search );
		$best_match		= false;
		$best_score		= 0;
		$threshold		= 0.5;

		foreach ( $product_titles as $product_title ) {
			$title_lower = mb_strtolower( $product_title );

			if ( strpos( $title_lower, $search_lower ) !== false ) {
				return $product_title;
			}

			$search_words  = explode( ' ', $search_lower );
			$title_words_lc = explode( ' ', $title_lower );
			foreach ( $search_words as $word ) {
				if ( strlen( $word ) > 2 && in_array( $word, $title_words_lc, true ) ) {
					return $product_title;
				}
			}

			$title_words = explode( ' ', $title_lower );
			foreach ( $title_words as $word ) {
				if ( strlen( $word ) > 2 && strpos( $word, $search_lower ) !== false ) {
					return $product_title;
				}

				similar_text( $search_lower, $word, $word_percent );

				if ( $word_percent >= 70 ) {
					return $product_title;
				}
			}

			similar_text( $search_lower, $title_lower, $percent );
			
			if ( $percent > $best_score && $percent >= ( $threshold * 100 ) ) {
				$best_score = $percent;
				$best_match = $product_title;
			}

			// levenshtein distance
			$lev = levenshtein( $search_lower, $title_lower );
			$max_len = max( strlen( $search_lower ), strlen( $title_lower ) );
			$lev_score = 1 - ( $lev / $max_len );

			if ( $lev_score > $best_score && $lev_score >= $threshold ) {
				$best_score = $lev_score * 100;
				$best_match = $product_title;
			}
		}

		return $best_match;
	}
}

<?php

namespace EasyCommerce\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Customer;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Traits\Cleaner;
use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Queue;
use EasyCommerce\Models\Database;

class Init {

    use Hook;
    use Asset;
    use Cache;
    use Queue;
    use Cleaner;

    /**
     * Constructor to add all hooks.
     */
    public function __construct() {
        $this->filter( 'admin_body_class', array( $this, 'add_body_class' ) );
        $this->action( 'admin_notices', array( $this, 'admin_notices' ) );
        $this->action( 'admin_init', array( $this, 'secure_download' ) );
        $this->action( 'after_plugin_row', array( $this, 'show_plugin_notice' ) );
        $this->filter( 'display_post_states', array( $this, 'add_page_labels' ), 10, 2 );
        $this->filter( 'use_block_editor_for_post', array( $this, 'force_block_editor' ), 10, 2 );
        $this->action( 'init', array( $this, 'tinymce_dropdown_init' ) );
        $this->action( 'admin_bar_menu', array( $this,'add_migration_btn_in_admin_bar' ), 9999 );
        $this->action( 'admin_footer', array( $this, 'add_migration_popup' ) );
        $this->action( 'admin_footer', array( $this, 'add_ai_assistant' ) );
        $this->action( 'wp_ajax_query-themes', array( $this, 'intercept_theme_query' ), 1 );
        $this->action( 'admin_init', array( $this, 'handle_cart_sessions_migration' ) );
        $this->action( 'admin_init', array( $this, 'connect_via_magic_link' ) );
    }

    /**
     * Handles the AI activation magic link from the token email.
     *
     * The link lands in wp-admin carrying the one-time token + email. It does not
     * authenticate anyone - it only works inside an already-authenticated admin
     * session (capability check below), then verifies the token against the
     * EasyCommerce store and persists the connection exactly like the manual
     * copy-paste flow ( {@see \EasyCommerce\API\Connectivity::verify_token} ).
     *
     * @return void
     */
    public function connect_via_magic_link() {
        if ( empty( $_GET['token'] ) || empty( $_GET['email'] ) ) {
            return;
        }

        // Only an authenticated admin session may activate the connection.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
        $email = sanitize_email( wp_unslash( $_GET['email'] ) );

        $connected = false;

        if ( $token && $email ) {
            $response = wp_remote_post(
                easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/token/verify' ),
                array( 'body' => array( 'email' => $email, 'token' => $token ) )
            );

            $body = json_decode( wp_remote_retrieve_body( $response ) );

            if ( isset( $body->data->verified ) && true == $body->data->verified ) {
                $connected  = true;
                $api        = $body->data->user;
                $api->email = $email;

                /** This action is documented in app/API/Connectivity.php */
                do_action( 'easycommerce_before_verify_token', $email, $token, null );

                update_option( 'easycommerce_api', $api );

                /** This action is documented in app/API/Connectivity.php */
                do_action( 'easycommerce_after_verify_token', $api, null );

                // Mirror the copy-paste flow's client cookie so connected state is
                // visible immediately to the SPAs that read it.
                setcookie( 'easycommerce-user', wp_json_encode( $api ), time() + ( 30 * DAY_IN_SECONDS ), '/' );
            }
        }

        // Redirect to a clean URL so the token does not linger in the address bar / history.
        wp_safe_redirect( admin_url( 'admin.php?page=easycommerce&ec_connected=' . ( $connected ? '1' : '0' ) ) );
        exit;
    }

    /**
     * Adds custom body class to the admin area.
     *
     * @param string $classes Existing body classes.
     *
     * @return string Modified body classes with 'easycommerce' class added if conditions met.
     */
    public function add_body_class( $classes ) {
        global $current_screen;

        if ( strpos( $current_screen->base, 'easycommerce' ) !== false ) {
            $classes .= ' easycommerce ';

            // add the folded class to auto-collapse the left admin menu
            $classes .= ' folded ';
        }

        return $classes;
    }

    /**
     * Shows different admin notices
     */
    public function admin_notices() {

        /**
         * If setup wizard was not run
         */
        if ( empty( get_option( 'easycommerce-setup_wizard' ) ) ) {
            printf(
                '<div class="notice notice-warning is-dismissible easycommerce-notice"><p>%s</p></div>',
                sprintf(
                    /* Translators: %s is the link to the setup wizard */
                    __( 'Congratulations on installing <strong>EasyCommerce</strong>! 🎉<br/>You\'re just a few steps away from launching your store. <a href="%s"><strong>Click here</strong></a> to start the setup wizard and bring your store to life! 🚀', 'easycommerce' ),
                    esc_url( admin_url( 'admin.php?page=easycommerce-wizard' ) )
                )
            );
        }

        /**
         * If locations.json file is not downloaded
         */
        elseif ( empty( get_option( 'easycommerce-locations_db_loaded' ) ) ) {

            // if it's scheduled, let the user know
            if ( $this->has_schedule( 'easycommerce_prepare_background' ) ) {
                printf(
                    '<div class="notice notice-warning is-dismissible easycommerce-notice"><p>%s</p></div>',
                    sprintf(
                        __( 'The <strong>EasyCommerce</strong> Locations database is being downloaded. Countries, states, cities and currencies will not be displayed until the download is complete.', 'easycommerce' ),
                    )
                );
            }

            // it's not scheduled. Let's proceed to schedule
            else {

                // set new schedule as requested
                if ( isset( $_GET['action'] ) && $_GET['action'] == 'easycommerce-locations_db' ) {
                    $this->schedule( 'easycommerce_prepare_background' );
                }

                // show notice to the user asking to set a schedule
                else {
                    printf(
                        '<div class="notice notice-warning is-dismissible easycommerce-notice"><p>%s</p></div>',
                        sprintf(
                            /* Translators: %1$s is the link that reschedules the cron to download locations.json file */
                            __( 'It looks like the <strong>EasyCommerce</strong> Locations database has not been loaded yet. Countries, states, cities and currencies will not be displayed until it\'s downloaded. <a href="%1$s">Click here</a> to manually retry downloading it.', 'easycommerce' ),
                            esc_url( admin_url( 'index.php?action=easycommerce-locations_db' ) )
                        )
                    );
                }
            }
        }

        /**
         * Square currency mismatch warning
         */
        $square_currency = get_transient( 'easycommerce_square_location_currency' );
        if ( $square_currency && easycommerce_currency() !== $square_currency && in_array( 'square', easycommerce_active_payment_methods(), true ) ) {
            global $current_screen;
            if ( isset( $current_screen->base ) && strpos( $current_screen->base, 'easycommerce' ) !== false ) {
                wp_enqueue_style( 'easycommerce-public-style', EASYCOMMERCE_ASSETS_URL . 'public/css/style.css', array(), EASYCOMMERCE_VERSION );

                printf(
                    '<div class="notice notice-error is-dismissible easycommerce-notice easycommerce-notice-error" style="max-width:calc(100%% - 40px)"><p>%s</p></div>',
                    sprintf(
                        __( 'Warning: Your store currency (%1$s) does not match your Square location currency (%2$s). Square payments will not be available until currencies match.', 'easycommerce' ),
                        esc_html( easycommerce_currency() ),
                        esc_html( $square_currency )
                    )
                );
            }
        }
    }

    /**
     * Show the EasyCommerce Pro admin notice for Year End campaign
     */
    private function show_easycommerce_pro_notice() {
        $notice = new Notice();

        if ( apply_filters( 'easycommerce-pro_activated', false ) ) {
            return;
        }

        // Only show if within the date range
        if ( ! $notice->is_pro_notice_date_range_active() ) {
            return;
        }

        // Only show if user should see the notice (not dismissed or 5 days have passed)
        if ( ! $notice->should_show_pro_notice() ) {
            return;
        }

        // Check if we're on EasyCommerce pages or dashboard
        global $current_screen;

        // Only show on main dashboard

        if ( $current_screen->base != 'dashboard' ) {
            return;
        }

        $discount_img = EASYCOMMERCE_ASSETS_URL . '/admin/img/banner-sale/discount.gif';
        $discount_url = 'https://easycommerce.dev/pricing?utm_source=wpdashboard&utm_medium=banner&utm_campaign=year-end';
        $notice_id    = 'easycommerce-year-end-deals-campaign-21-dec';

        echo '
            <div class="notice notice-info is-dismissible easycommerce-pro-notice" data-notice-id="' . esc_attr( $notice_id ) . '">
                <div class="easycommerce-year-end-deals-notice">
                    <div class="discount-image">
                        <img src="' . esc_url( $discount_img ) . '" alt="WC-Affiliate" class="wc-affiliate-notice-image" >
                    </div>

                    <div class="year-end-content">
                        <p class="title">' . __( 'EasyCommerce Year-End Celebration!', 'easycommerce' ) . '</p>
                        <p class="description">' . __( 'Enjoy a flat 50% discount while building your dream ecommerce store with EasyCommerce Pro!', 'easycommerce' ) . '</p>
                        <a href="' . esc_url( $discount_url ) . '" class="notice-cta-button" data-id="' . esc_attr( $notice_id ) . '" target="_blank">
                        ' . __( 'Save 50% Now', 'easycommerce' ) . '
                        </a>
                    </div>
                </div>
            </div>
        ';
    }

    /**
     * Handles secure file downloads for customers.
     */
    public function secure_download() {
        if ( ! isset( $_GET['media_id'] ) || empty( $media_id = $this->sanitize( $_GET['media_id'] ) ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            wp_die( esc_html__( 'You are not logged in!', 'easycommerce' ) );
        }

        // Collect the customer's entitled downloads (paid orders, digital only).
        $customer     = new Customer( get_current_user_id() );
        $download_ids = array_keys( $customer->get_downloads() );

        // Check if the media ID is within the allowed downloads.
        if ( ! in_array( (int) $media_id, $download_ids, true ) ) {
            wp_die( esc_html__( 'Not your download!', 'easycommerce' ) );
        }

        // Retrieve the file path for the media ID
        $file_path = get_attached_file( $media_id );

        if ( ! file_exists( $file_path ) ) {
            wp_die( esc_html__( 'File not found.', 'easycommerce' ) );
        }

        // Clear any output buffering and set headers for file download
        if ( ob_get_level() ) {
            ob_end_clean();
        }

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $file_path ) );

        // Output the file to the browser for download
        readfile( $file_path );

        exit;
    }

    public function show_plugin_notice( $plugin_file ) {
        $conflicting_plugins = easycommerce_get_conflicting_plugins();

        if ( in_array( $plugin_file, $conflicting_plugins ) ) {

            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

            $message = sprintf( __( 'The plugin <strong>%1$s</strong> was deactivated by <strong>EasyCommerce</strong> as it is no longer needed. You can safely delete it if you wish.', 'easycommerce' ), esc_html( $plugin_data['Name'] ) );

            printf( '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>%s</p></div></td></tr>', $message );
        }
    }

    public function add_page_labels( $post_states, $post ) {

        if ( $post->ID == easycommerce_shop_page() ) {
            $post_states['cart_label'] = sprintf( '<span class="easycommerce-page-tag">%1$s</span> %2$s', __( 'EasyCommerce', 'easycommerce' ), __( 'Shop', 'easycommerce' ) );
        } elseif ( $post->ID == easycommerce_checkout_page() ) {
            $post_states['cart_label'] = sprintf( '<span class="easycommerce-page-tag">%1$s</span> %2$s', __( 'EasyCommerce', 'easycommerce' ), __( 'Checkout', 'easycommerce' ) );
        } elseif ( $post->ID == easycommerce_dashboard_page() ) {
            $post_states['cart_label'] = sprintf( '<span class="easycommerce-page-tag">%1$s</span> %2$s', __( 'EasyCommerce', 'easycommerce' ), __( 'Dashboard', 'easycommerce' ) );
        } elseif ( $post->ID == easycommerce_payment_page() ) {
            $post_states['cart_label'] = sprintf( '<span class="easycommerce-page-tag">%1$s</span> %2$s', __( 'EasyCommerce', 'easycommerce' ), __( 'Payment', 'easycommerce' ) );
        }

        return $post_states;
    }

    /**
     * Force using block editor even if the Classic Editor plugin is activated
     *
     * @since 0.9.18-beta
     */
    public function force_block_editor( $use_block_editor, $post ) {
        if ( isset( $_GET['builder'] ) && $_GET['builder'] == 1 && $post->post_type == 'product' ) {
            return true;
        }

        return $use_block_editor;
    }

    /**
     * Initialize the TinyMCE plugin and button
     */
    public function tinymce_dropdown_init(): void {
        // Check if WYSIWYG is enabled
        if ( get_user_option( 'rich_editing' ) !== 'true' ) {
            return;
        }

        add_filter( 'mce_external_plugins', function ( array $plugin_array ): array {
            global $current_screen;

            $screen = str_replace( array( 'toplevel_page_', 'store_page_' ), array(), $current_screen->base );
            if ( 'easycommerce-settings' === $screen ) {
                $plugin_array['easycommerce-email-placeholders'] = EASYCOMMERCE_ASSETS_URL . 'admin/js/email-placeholders.js';
            }

            return $plugin_array;
        } );

        add_filter( 'mce_buttons', function ( array $buttons ): array {
            global $current_screen;

            $screen = str_replace( array( 'toplevel_page_', 'store_page_' ), array(), $current_screen->base );
            if ( 'easycommerce-settings' === $screen ) {
                $buttons[] = 'easycommerce_email_placeholders';
            }

            return $buttons;
        } );
    }

    /**
     * Add TinyMCE plugin
     */
    public function add_tinymce_dropdown_plugin( array $plugin_array ): array {
        global $current_screen;

        $screen = str_replace( array( 'toplevel_page_', 'store_page_' ), array(), $current_screen->base );
        if ( 'easycommerce-settings' === $screen ) {
            $plugin_array['easycommerce-email-placeholders'] = EASYCOMMERCE_ASSETS_URL . 'admin/js/email-placeholders.js';
        }

        return $plugin_array;
    }

    /**
     * Add button to TinyMCE toolbar
     */
    public function register_tinymce_dropdown_button( array $buttons ): array {
        global $current_screen;

        $screen = str_replace( array( 'toplevel_page_', 'store_page_' ), array(), $current_screen->base );
        if ( 'easycommerce-settings' === $screen ) {
            $buttons[] = 'easycommerce_email_placeholders';
        }

        return $buttons;
    }

    /**
     * Add migration button in the admin secondary top bar
     */
    public function add_migration_btn_in_admin_bar( $wp_admin_bar ) {
		if( ! current_user_can( 'manage_options' ) ) return;
		
        $migration_status   = get_option( 'easycommerce_migration_status', 'not_started' );
        $check_status       = array( 'not_started', 'in_progress', 'failed' ); 

		if ( easycommerce_detect_external_plugins_for_migration() && in_array( $migration_status, $check_status ) ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'easycommerce-migration',
				'parent' => 'top-secondary',
				'title'  => __( 'Migration', 'easycommerce' ),
				'href'   => '#',
				'meta'   => array(
					'class'  => 'easycommerce-migration',
					'title'  => __( 'EasyCommerce Migration', 'easycommerce' ),
				)
			) );
		}
	}

    /**
     * Add migration popup for installing 'Migration Addon'
     */
    public function add_migration_popup() {
        if( ! current_user_can( 'manage_options' ) ) return;

        $migration_status   = get_option( 'easycommerce_migration_status', 'not_started' );
        $check_status       = array( 'not_started', 'in_progress', 'failed' ); 

		if ( easycommerce_detect_external_plugins_for_migration() && in_array( $migration_status, $check_status ) ) :
        ?>
        <div class="migration-popup-wrapper">
            <div class="inner-wrapper">
                <img
                    class="header-img"
                    src="<?php echo esc_url( EASYCOMMERCE_ASSETS_URL . 'admin/img/migration_popup.png' ); ?>"
                    alt="EasyCommerce Migration"
                />
                <button class="close-button">
                    <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.260418 1.10798C0.607642 0.768042 1.17015 0.768042 1.51731 1.10798L5 4.5176L8.48269 1.10798C8.82991 0.768042 9.39241 0.768042 9.73958 1.10798C10.0868 1.44792 10.0868 1.99862 9.73958 2.33851L6.2569 5.74813L9.73958 9.15775C10.0868 9.49769 10.0868 10.0484 9.73958 10.3883C9.39236 10.7282 8.82985 10.7282 8.48269 10.3883L5 6.97866L1.51731 10.3883C1.17009 10.7282 0.607587 10.7282 0.260418 10.3883C-0.0867505 10.0483 -0.086806 9.49764 0.260418 9.15775L3.7431 5.74813L0.260418 2.33851C-0.086806 1.99857 -0.086806 1.44787 0.260418 1.10798Z" fill="#3C3C42" className="transition-colors duration-200 group-hover:fill-white"/>
                    </svg>
                </button>

                <div class="container">
                    <h3 class="title">
                        <?php esc_html_e( "You're about to migrate!", 'easycommerce' ); ?>
                    </h3>
                    <p class="description">
                        <b><?php echo easycommerce_detect_external_plugins_for_migration(); ?></b> <?php esc_html_e( 'detected. Would you like to migrate your store data to EasyCommerce?', 'easycommerce' ); ?>
                    </p>
                </div>

                <div class="button-wrapper">
                    <button
                        class="button cancel"
                        
                    >
                        <?php esc_html_e( 'Cancel', 'easycommerce' ); ?>
                    </button>
                    <button
                        class="button migration"
                        type="button"
                    >
                        <?php esc_html_e( 'Start Migration', 'easycommerce' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        endif;
    }

    /**
     * Load HelpWP chat widget on all EasyCommerce admin pages.
     */
    public function add_ai_assistant() {
        global $current_screen;

        if ( strpos( $current_screen->base, 'easycommerce' ) === false ) {
            return;
        }

        echo '<script src="https://helpwp.dev/cdn/chat.min.js" data-site-key="0819c9d35ed296e03d64eafca6f88319" data-agent-name="Luna" data-agent-avatar="https://helpwp.dev/cdn/luna.webp" data-welcome-message="Hi! I\'m Luna. I help you learn how to use EasyCommerce - setup, features, and how-tos. For sales numbers or managing orders, use the \'Store Copilot\' button at the top of the page." data-formatted="false" data-primary-color="#7351fd" data-branding="off" defer></script>';
    }

    /*
     * Intercept theme query to show custom themes
     */
    public function intercept_theme_query() {
        if ( isset( $_REQUEST['request']['browse'] ) && sanitize_key( $_REQUEST['request']['browse'] ) === 'easycommerce-recommended' ) {
            
            add_filter( 'themes_api_result', function( $res, $action, $args ) {
                if ( $action !== 'query_themes' || ! isset( $args->browse ) || $args->browse !== 'easycommerce-recommended' ) {
                    return $res;
                }
                
                // Fetch all recommended themes, try to use cache if available
                if( false == ( $all_themes = $this->get_cache( 'recommended_themes' ) ) ) {
                    
                    $all_themes = array_map( function( $slug ) {
                        return themes_api( 'theme_information', array(
                            'slug' => $slug,
                            'fields' => array(
                                'description' => true,
                                'sections'    => false,
                            )
                        ) );
                    }, easycommerce_compatible_themes() );

                    $this->set_cache( 'recommended_themes', $all_themes );
                }
        
                // Calculate pagination
                $page           = isset( $args->page ) ? absint( $args->page ) : 1;
                $per_page       = isset( $args->per_page ) ? absint( $args->per_page ) : 24;
                $total_themes   = count( $all_themes );
                $offset         = ( $page - 1 ) * $per_page;
                $paged_themes   = array_slice( $all_themes, $offset, $per_page );
        
                // Update response
                $res->themes    = $paged_themes;
                $res->info      = array(
                    'page'      => $page,
                    'pages'     => ceil( $total_themes / $per_page ),
                    'results'   => $total_themes
                );
                
                return $res;
            }, 10, 3 );
        }
    }

    /**
     * Handles data migration for the cart sessions table on admin_init.
     * Adds missing columns introduced in newer versions.
     *
     * @return void
     * @todo remove it in future
     */
    public function handle_cart_sessions_migration() {
        global $wpdb, $easycommerce_tables;

        if ( empty( $easycommerce_tables ) ) {
            require_once EASYCOMMERCE_PLUGIN_DIR . 'app/Config/tables.php';
        }

        $db              = new Database( 'cart_sessions' );
        $table_full_name = $db->get_prefix() . 'cart_sessions';

        if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$table_full_name}'" ) ) {
            return;
        }

        $existing_columns = (array) $wpdb->get_col( "DESCRIBE `{$table_full_name}`;" );
        $columns          = $easycommerce_tables['cart_sessions']['columns'] ?? [];

        if ( ! in_array( 'total', $existing_columns, true ) && isset( $columns['total'] ) ) {
            $wpdb->query( "ALTER TABLE `{$table_full_name}` ADD COLUMN `total` " . $columns['total'] );
        }
    }
}

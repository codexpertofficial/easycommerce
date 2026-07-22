<?php
namespace EasyCommerce\API;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Abstracts\API;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Abandoned_Cart as Abandoned_Cart_Model;
use EasyCommerce\Helpers\Utility;
use WP_REST_Request;

class Abandoned_Cart extends API {

	/**
	 * Get a list of abandoned carts.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
    public function get_all( $request ) {
        $delay              = $request->get_param( 'delay' ) ?? Utility::get_option( 'abandoned-cart', 'settings', 'delay', 30 );
        $per_page           = (int) ( $request->get_param( 'per_page' ) ?? 10 );
        $page               = (int) ( $request->get_param( 'page' ) ?? 1 );
        $offset             = ( $page - 1 ) * $per_page;
        $type               = $request->get_param( 'type' );
        $search             = $request->get_param( 'search' );
        $from_date          = $request->get_param( 'from' );
        $to_date            = $request->get_param( 'to' );

        $abandoned_cart_obj = new Abandoned_Cart_Model();
        $abandoned_carts    = $abandoned_cart_obj->list( $search, $from_date, $to_date );
        
        $statuses = [
            'all'               => 0,
            'not_contracted'    => 0,
            'contracted'        => 0,
            'recovered'         => 0,
        ];
    
        $filtered_for_page = [];
    
        foreach ( $abandoned_carts as $entry ) {
            
            if ( empty( $entry->hash ) ) continue;
            
            $cart_obj = new Cart( $entry->hash );

            if ( $cart_obj->get_item_count() < 1 ) continue;
    
            if ( $entry->status === 'pending' && $entry->reminders > 0 ) {
                $type_key = 'contracted';
            } elseif ( $entry->status === 'pending' ) {
                $type_key = 'not_contracted';
            } else {
                $type_key = 'recovered';
            }
    
            $statuses[$type_key]++;
            $statuses['all']++;
    
            if ( $type === 'not_contracted' && $type_key !== 'not_contracted' ) continue;
            if ( $type === 'contracted' && $type_key !== 'contracted' ) continue;
            if ( $type === 'recovered' && $type_key !== 'recovered' ) continue;
                         
            $filtered_for_page[] = $entry;
        }
    
        $total_filtered     = count( $filtered_for_page );
        $paginated_results  = array_slice( $filtered_for_page, $offset, $per_page );
        $carts              = [];
          
        foreach ( $paginated_results as $abandoned ) {
            $hash       = $abandoned->hash;
            $cart_obj   = new Cart( $hash );
    
            $product_names    = [];
            $distinct_products = 0;
            foreach ( $cart_obj->get_items() as $product_id => $variations ) {
                $distinct_products++;
                if ( count( $product_names ) >= 3 ) continue;
                $product = new Product( $product_id );
                $qty     = 0;
                foreach ( $variations as $config ) {
                    $qty += (int) ( $config['quantity'] ?? 0 );
                }
                $product_names[] = [ 'name' => $product->get_title(), 'qty' => $qty ];
            }

            $carts[] = [
                'hash'          => $hash,
                'name'          => $abandoned->customer_name ?? $cart_obj->get_customer_name(),
                'email'         => $abandoned->customer_email ?? $cart_obj->get_customer_email(),
                'items'            => $cart_obj->get_item_count(),
                'product_names'    => $product_names,
                'distinct_products'=> $distinct_products,
                'reminders'     => $cart_obj->get_data( 'reminders' ),
                'total'         => easycommerce_price( $cart_obj->get_amount() ),
                'updated_at'    => $cart_obj->get_data( 'updated_at' ),
                'created_at'    => $cart_obj->get_data( 'created_at' ),
                'type'          => $abandoned->status === 'pending' ? 'not_contracted' : 'recovered',
            ];
        }
             
		/**
		 * Fires after listing abandoned carts.
		 *
		 * @since 1.9
		 * @param array $carts The list of abandoned carts.
		 * @param int $delay The delay for abandoned carts.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_list_abandoned_carts', $carts, $delay, $request );
             
        $response_data = [
            'carts'       => $carts,
            'statuses'    => $statuses,
            'total'       => $total_filtered,
            'per_page'    => $per_page,
            'page'        => $page,
            'total_pages' => ceil( $total_filtered / $per_page ),
        ];
             
		/**
		 * Filters the response data for abandoned carts list.
		 *
		 * @since 1.9
		 * @param array $response_data The response data.
		 * @param int $delay The delay for abandoned carts.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_list_abandoned_carts_response', $response_data, $delay, $request );
             
        $this->response_success( $response_data );
    }

	/**
	 * Remove abandoned cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function remove( $request ) {
		$hash       = $request->get_param( 'hash' );
        $cart_obj   = new Cart( $hash );

		/**
		 * Fires before an abandoned cart is removed.
		 *
		 * @since 1.9
		 * @param string $hash The cart hash.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_remove_abandoned', $hash, $request );

		$deleted = $cart_obj->delete();

		/**
		 * Fires after an abandoned cart is removed.
		 *
		 * @since 1.9
		 * @param string $hash The cart hash.
		 * @param bool $deleted Whether the cart was successfully deleted.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_after_remove_abandoned', $hash, $deleted, $request );

		$response_data = array(
			'message' => $deleted ? __( 'Cart removed', 'easycommerce' ) : __( 'Failed to remove cart', 'easycommerce' ),
			'hash'    => $hash,
		);

		/**
		 * Filter the response data before sending.
		 *
		 * @since 1.9
		 * @param array $response_data The response data including removal status.
		 * @param string $hash The cart hash.
		 * @param bool $deleted Whether the cart was successfully deleted.
		 * @param WP_REST_Request $request The request object.
		 */
		$response_data = apply_filters( 'easycommerce_remove_abandoned_response', $response_data, $hash, $deleted, $request );

		$this->response_success( $response_data );
	}

    /**
     * Get the email subject and body (with placeholders resolved) for an abandoned cart.
     *
     * @param WP_REST_Request $request The request object.
     */
    public function get_email_content( $request ) {
        $hash = $request->get_param( 'hash' );

        // These defaults mirror the `abandoned-cart > settings > subject|body` field
        // defaults declared in app/Config/settings.php; keep both copies in sync.
        /* translators: the ##...## tokens are merge placeholders substituted with real cart data at send time - keep them verbatim. */
        $default_subject = __( '##shop_name##- Your Order Is Yet to Be Placed!', 'easycommerce' );

        /* translators: the ##...## tokens are merge placeholders substituted with real cart data at send time - keep them verbatim. */
        $default_body = __( 'Hi ##name##,
We noticed you left some items in your cart at ##shop_name##. Your cart, worth ##cart_total##, is still waiting for you!
Here’s what you left behind:
##product_list##
Don’t miss out—your items might sell out soon! Click below to return to your cart and complete your purchase.
Go to Checkout 👉 ##cart_link## 
Need help? Feel free to reach out. We’re happy to assist!
Best,
##shop_name## Team', 'easycommerce' );

        $subject = Utility::get_option( 'abandoned-cart', 'settings', 'subject', $default_subject );
        $body    = Utility::get_option( 'abandoned-cart', 'settings', 'body', $default_body );

        $cart         = new Cart( $hash );
        $placeholders = easycommerce_cart_placeholders( $cart );

        $default_placeholders = array(
            '##site_name##'      => get_bloginfo( 'name' ),
            '##shop_name##'      => Utility::get_option( 'general', 'business', 'store_name', '' ),
            '##year##'           => date_i18n( 'Y' ),
            '##shop_page##'      => easycommerce_shop_page( true ),
            '##checkout_page##'  => easycommerce_checkout_page( true ),
            '##dashboard_page##' => easycommerce_dashboard_page( true ),
        );

        $all_placeholders = array_merge( $default_placeholders, $placeholders );
        $keys             = array_keys( $all_placeholders );
        $values           = array_values( $all_placeholders );

        $resolved_body = str_replace( $keys, $values, $body );

        // If the body has no paragraph/break HTML (plain-text default), convert newlines to <br> for the visual editor.
        if ( ! preg_match( '/<(br|p)\b/i', $resolved_body ) ) {
            $resolved_body = nl2br( $resolved_body );
        }

        $this->response_success( array(
            'subject' => str_replace( $keys, $values, $subject ),
            'body'    => $resolved_body,
            'email'   => $cart->get_customer_email(),
        ) );
    }

    /**
     * Remind abandoned cart.
     *
     * @param WP_REST_Request $request The request object.
     */
    public function remind( $request ) {
        $hash    = $request->get_param( 'hash' );
        $subject = sanitize_text_field( (string) ( $request->get_param( 'subject' ) ?? '' ) );
        $body    = wp_kses_post( (string) ( $request->get_param( 'body' ) ?? '' ) );

		/**
		 * Fires before sending an abandoned cart reminder.
		 *
		 * @since 1.9
		 * @param string $hash The cart hash.
		 * @param WP_REST_Request $request The request object.
		 */
		do_action( 'easycommerce_before_send_abandoned_reminder', $hash, $request );

        if ( ! empty( $subject ) && ! empty( $body ) ) {
            // Custom email composed in the popup editor — send directly.
            $cart           = new Cart( $hash );
            $customer_email = $cart->get_customer_email();

            if ( ! empty( $customer_email ) ) {
                do_action( 'easycommerce_email', $customer_email, $subject, $body, array() );
            }
        } else {
            // Standard flow: read template from settings and resolve placeholders server-side.
            do_action( 'easycommerce_send_abandoned_reminder', $hash );
        }

        $mail_sent = apply_filters( 'easycommerce_mail_sent', false, $hash, $request );

        if ( $mail_sent ) {

            $cart_model = new Cart( $hash );
            $reminders  = (int) $cart_model->get_data( 'reminders' );

            $cart_model->cart['reminders'] = $reminders + 1;

            $cart_model->save();
        }

        $response_data = array(
            'mail_sent' => $mail_sent,
            'message'   => $mail_sent ? __( 'Reminder sent', 'easycommerce' ) : __( 'Reminder not sent', 'easycommerce' ),
        );

        /**
         * Filter the response data before sending.
         *
         * @param array $response_data The response data including mail status.
         * @param string $hash The cart hash.
         * @param WP_REST_Request $request The request object.
         */
        $response_data = apply_filters( 'easycommerce_remind_abandoned_response', $response_data, $hash, $request );

        $this->response_success( $response_data );
    }

    /**
     * Bulk delete abandoned carts.
     *
     * @param WP_REST_Request $request The request object.
     */
    public function bulk_delete( $request ) {
        $hashes     = $request->get_param( 'abandoned_cart_hashes' );
        $deleted    = true;

        foreach ( $hashes as $hash ) {
			/**
			 * Fires before an abandoned cart is removed.
			 *
			 * @since 1.9
			 * @param string $hash The cart hash.
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'easycommerce_before_remove_abandoned', $hash, $request );

            $cart_obj   = new Cart( $hash );
            $deleted    = $cart_obj->delete();

            if ( ! $deleted ) {
                $deleted = false;
                continue;
            }

			/**
			 * Fires after an abandoned cart is removed.
			 *
			 * @since 1.9
			 * @param string $hash The cart hash.
			 * @param bool $deleted Whether the cart was successfully deleted.
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'easycommerce_after_remove_abandoned', $hash, $deleted, $request );
        }

        if ( ! $deleted ) {
            return $this->response_error( __( 'Failed to remove some carts', 'easycommerce' ) );
        }

        return $this->response_success( __( 'Carts deleted successfully.', 'easycommerce' ) );
    }

    /**
     * Bulk send reminders for abandoned carts.
     *
     * @param WP_REST_Request $request The request object.
     */
    public function bulk_reminder( $request ) {
        $hashes = $request->get_param( 'abandoned_cart_hashes' );
    
        if ( ! is_array( $hashes ) || empty( $hashes ) ) {
            return $this->response_error( [
                'message' => __( 'No abandoned cart hashes provided.', 'easycommerce' )
            ] );
        }
    
        $results    = [];
        $all_sent   = true;
    
        foreach ( $hashes as $hash ) {
            /**
             * Fires before sending an abandoned cart reminder.
             *
             * @since 1.9
             * @param string $hash The cart hash.
             * @param WP_REST_Request $request The request object.
             */
            do_action( 'easycommerce_before_send_abandoned_reminder', $hash, $request );
    
            /**
             * Fires when sending an abandoned cart reminder.
             *
             * @since 1.9
             * @param string $hash The cart hash.
             */
            do_action( 'easycommerce_send_abandoned_reminder', $hash );
    
            $mail_sent = apply_filters( 'easycommerce_mail_sent', false, $hash, $request );
    
            if ( $mail_sent ) {
                $cart_model = new Cart( $hash );
                $reminders  = (int) $cart_model->get_data( 'reminders' );
    
                $cart_model->cart['reminders'] = $reminders + 1;
                $cart_model->save();
            } else {
                $all_sent = false;
            }
        }
    
        if ( ! $all_sent ) {
            return $this->response_error( __( 'Failed to send some reminders', 'easycommerce' ) );
        }
    
        return $this->response_success( __( 'Reminders sent successfully.', 'easycommerce' ) );
    }

    /**
	 * Clean/Empty abandoned cart.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
    public function clean( $request ) {
        /**
         * Fires before all abandoned carts are cleaned.
         *
         * @param WP_REST_Request $request The request object.
         */
        do_action( 'easycommerce_before_clean_abandoned_cart', $request );

        $abandoned_cart_obj = new Abandoned_Cart_Model();
        $status             = $abandoned_cart_obj->clean(); // returns 'cleaned', 'empty', or 'error'

        /**
         * Fires after all abandoned carts are cleaned.
         *
         * @param string $status The status of the operation ('cleaned', 'empty', or 'error').
         * @param WP_REST_Request $request The request object.
         */
        do_action( 'easycommerce_after_clean_abandoned_cart', $status, $request );

        if ( $status === 'error' ) {
            $response_data = array(
                'message' => __( 'Failed to clean abandoned carts.', 'easycommerce' ),
                'status'  => 'error',
            );

            /**
             * Filter the response data before sending.
             *
             * @param array $response_data The response data including cleaned status.
             * @param string $status The operation status ('cleaned', 'empty', or 'error').
             * @param WP_REST_Request $request The request object.
             */
            $response_data = apply_filters( 'easycommerce_clean_abandoned_response', $response_data, $status, $request );

            $this->response_error( $response_data );
            return;
        }

        $response_data = $status === 'cleaned'
            ? array(
                'message' => __( 'Cleaned abandoned carts.', 'easycommerce' ),
                'status'  => 'cleaned',
            )
            : array(
                'message' => __( 'There is nothing to clean.', 'easycommerce' ),
                'status'  => 'empty',
            );

        /**
         * Filter the response data before sending.
         *
         * @param array $response_data The response data including cleaned status.
         * @param string $status The operation status ('cleaned', 'empty', or 'error').
         * @param WP_REST_Request $request The request object.
         */
        $response_data = apply_filters( 'easycommerce_clean_abandoned_response', $response_data, $status, $request );

        $this->response_success( $response_data );
    }

    /**
	 * Clean invalid abandoned cart. Means it deletes some of the entries from table which are considered as invalids.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
    public function clean_invalid( $request ) {
        /**
         * Fires before invalid abandoned carts are cleaned.
         *
         * @param WP_REST_Request $request The request object.
         */
        do_action( 'easycommerce_before_clean_invalid_abandoned_cart', $request );

        $abandoned_cart_obj = new Abandoned_Cart_Model();
        $status             = $abandoned_cart_obj->clean_invalid(); // 'cleaned', 'empty', or 'error'

        /**
         * Fires after invalid abandoned carts are cleaned.
         *
         * @param string $status The result status.
         * @param WP_REST_Request $request The request object.
         */
        do_action( 'easycommerce_after_clean_invalid_abandoned_cart', $status, $request );

        if ( $status === 'cleaned' ) {
            $response_data = array(
                'message' => __( 'Cleaned invalid abandoned carts.', 'easycommerce' ),
                'status'  => 'cleaned',
            );

	        /**
	         * Filter the response data before sending.
	         *
	         * @param array $response_data The response data.
	         * @param string $status The operation status.
	         * @param WP_REST_Request $request The request object.
	         */
	        $response_data = apply_filters( 'easycommerce_clean_invalid_abandoned_response', $response_data, $status, $request );

	        $this->response_success( $response_data );
			return;

        }

		if ( $status === 'empty' ) {
            $response_data = array(
                'message' => __( 'No invalid entries found.', 'easycommerce' ),
                'status'  => 'empty',
            );

	        /**
	         * Filter the response data before sending.
	         *
	         * @param array $response_data The response data.
	         * @param string $status The operation status.
	         * @param WP_REST_Request $request The request object.
	         */
	        $response_data = apply_filters( 'easycommerce_clean_invalid_abandoned_response', $response_data, $status, $request );

	        $this->response_error( $response_data );
			return;
        }

	    $response_data = array(
		    'message' => __( 'Failed to clean invalid abandoned carts.', 'easycommerce' ),
		    'status'  => 'error',
	    );

	    /**
	     * Filter the response data before sending.
	     *
	     * @param array $response_data The response data.
	     * @param string $status The operation status.
	     * @param WP_REST_Request $request The request object.
	     */
	    $response_data = apply_filters( 'easycommerce_clean_invalid_abandoned_response', $response_data, $status, $request );

	    $this->response_error( $response_data );
    }
}

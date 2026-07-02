<?php 
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Cleaner;

/**
 * Class to do operation for abandoned cart
 */
Class Abandoned_Cart extends Cart {

    use Cleaner;

    public function list( $search = '', $from_date = '', $to_date = '' ) {
        $search    = $this->sanitize( wp_unslash( $search ) );
        $from_date = $this->sanitize( wp_unslash( $from_date ) );
        $to_date   = $this->sanitize( wp_unslash( $to_date ) );

        $where_conditions   = [];
        $prepare_values     = [];
        $where_conditions[] = "(status = %s OR (status = %s AND reminders > %d))";
        $prepare_values[]   = 'pending';
        $prepare_values[]   = 'completed';
        $prepare_values[]   = 0;
        $where_conditions[] = "(customer_email IS NOT NULL OR user_id IS NOT NULL)";
        
        if ( ! empty( $search ) ) {
            $where_conditions[] = "(customer_email LIKE %s OR customer_name LIKE %s)";
            $search_term        = '%' . $search . '%';
            $prepare_values[]   = $search_term;
            $prepare_values[]   = $search_term;
        }
        
        if ( ! empty( $from_date ) ) {
            $from_date_mysql = \DateTime::createFromFormat('d-m-Y', $from_date );
            if ( $from_date_mysql ) {
                $where_conditions[] = "DATE(created_at) >= %s";
                $prepare_values[]   = $from_date_mysql->format('Y-m-d' );
            }
        }
        
        if ( ! empty( $to_date ) ) {
            $to_date_mysql = \DateTime::createFromFormat('d-m-Y', $to_date );
            if ( $to_date_mysql ) {
                $where_conditions[] = "DATE(created_at) <= %s";
                $prepare_values[]   = $to_date_mysql->format('Y-m-d' );
            }
        }
        
        $base_sql = "
            SELECT * FROM {$this->db->get_table()}
            WHERE " . implode(' AND ', $where_conditions ) . "
            ORDER BY created_at DESC
        ";
        
        $all_results = $this->db->exec(
            $this->db->prepare( $base_sql, ...$prepare_values )
        );
        
        return $all_results;
    }

    /**
     * Empties the table by deleting all its entries.
     *
     * @return string Returns:
     *                - 'cleaned' if rows were deleted,
     *                - 'empty' if the table was already empty,
     *                - 'error' if the operation failed.
     */
    public function clean() {
        // Check if table exists and has rows
        $count = $this->db->get_count();

        if ( $count === false ) {
            return 'error'; // query failed
        }

        if ( (int) $count === 0 ) {
            return 'empty'; // table is already empty
        }

        $result = $this->db->exec( "DELETE FROM {$this->db->get_table()}" );

        if ( $result === false ) {
            return 'error';
        }

        return 'cleaned';
    }

    /**
     * Cleans all invalid entries from the abandoned cart.
     *
     * An invalid entry is a row where the 'items' array is empty.
     *
     * @return string Returns:
     *                - 'cleaned' if one or more invalid entries were deleted,
     *                - 'empty' if no invalid entries were found,
     *                - 'error' if the deletion query failed.
     */
	public function clean_invalid() {
		// Run the query
        $deleted = $this->db->exec( "DELETE FROM {$this->db->get_table()} WHERE customer_email = '' OR customer_email IS NULL" );

		if ( $deleted === false ) {
			return 'error';
		}

		return $deleted > 0 ? 'cleaned' : 'empty';
	}
}
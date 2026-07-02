<?php
namespace EasyCommerce\Models;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Abstracts\Model;

/**
 * Class Log
 * Handles log records in the database.
 *
 * @package EasyCommerce\Model
 */
class Log extends Model {

	protected $table = 'logs';

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var string
	 */
	protected $object = '';

	/**
	 * @var string
	 */
	protected $action = '';

	/**
	 * @var int
	 */
	protected $object_id = 0;

	/**
	 * @var int|null
	 */
	protected $user_id = null;

	/**
	 * @var string|null
	 */
	protected $note = null;

	/**
	 * @var string|null
	 */
	protected $ip_address = null;

	/**
	 * @var int
	 */
	protected $seen = 0;

	/**
	 * @var string
	 */
	protected $type = 'info';

	/**
	 * @var string|null
	 */
	protected $meta = null;

	/**
	 * @var int
	 */
	protected $is_public = 1;

	/**
	 * @var string
	 */
	protected $created_at = '';

	/**
	 * Log constructor.
	 * Initializes the class with the 'logs' table and optionally loads a log by ID.
	 *
	 * @param int $id The log ID to load.
	 */
	public function __construct( $id = 0 ) {
		parent::__construct();
		if ( $id > 0 ) {
			$this->id = $id;
			$this->load();
		}
	}

	/**
	 * Load log data from database.
	 */
	protected function load() {
		$data = $this->db->get_by_id( $this->id );
		if ( $data ) {
			$this->object     = $data->object;
			$this->action     = $data->action;
			$this->object_id  = $data->object_id;
			$this->user_id    = $data->user_id;
			$this->note       = $data->note;
			$this->ip_address = $data->ip_address;
			$this->seen       = $data->seen;
			$this->type       = $data->type ?? 'info';
			$this->meta		  = $data->meta;
			$this->is_public  = $data->is_public ?? 1;
			$this->created_at = $data->created_at;
		} else {
			$this->id = 0;
		}
	}

	/**
	 * Check if the log exists.
	 *
	 * @return bool True if exists, false otherwise.
	 */
	public function exists() {
		return ! empty( $this->id );
	}

	/**
	 * Get the log ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the object.
	 *
	 * @return string
	 */
	public function get_object() {
		return $this->object;
	}

	/**
	 * Get the action.
	 *
	 * @return string
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * Get the object ID.
	 *
	 * @return int
	 */
	public function get_object_id() {
		return $this->object_id;
	}

	/**
	 * Get the user ID.
	 *
	 * @return int|null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Get the note.
	 *
	 * @return string|null
	 */
	public function get_note() {
		return $this->note;
	}

	/**
	 * Get the IP address.
	 *
	 * @return string|null
	 */
	public function get_ip_address() {
		return $this->ip_address;
	}

	/**
	 * Get the seen status.
	 *
	 * @return int
	 */
	public function get_seen() {
		return $this->seen;
	}

	/**
	 * Get the log type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the is_public status.
	 *
	 * @return int
	 */
	public function get_is_public() {
		return $this->is_public;
	}

	/**
	 * Get the meta.
	 *
	 * @return string|null
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * Get the created at timestamp.
	 *
	 * @return string
	 */
	public function get_created_at() {
		return $this->created_at;
	}

	/**
	 * Get all logs based on filters like object, action, user ID, date range, pagination.
	 *
	 * @param array $args Filters for querying logs (e.g., object, action, user_id, from_date, to_date).
	 * @param int   $per_page Number of records per page.
	 * @param int   $offset Offset for pagination.
	 * @param string $sort Sort order (asc/desc).
	 * @return array List of logs with total count.
	 */
	public static function list( $args = array(), $per_page = 10, $offset = 0, $sort = 'desc' ) {
		$db              = new Database( 'logs' );
		$object          = isset( $args['object'] ) ? $args['object'] : null;
		$action          = isset( $args['action'] ) ? $args['action'] : null;
		$type            = isset( $args['type'] ) ? $args['type'] : null;
		$object_id       = isset( $args['object_id'] ) ? $args['object_id'] : null;
		$user_id         = isset( $args['user_id'] ) ? $args['user_id'] : null;
		$from_date       = isset( $args['from_date'] ) ? trim( $args['from_date'] ) : null;
		$to_date         = isset( $args['to_date'] ) ? trim( $args['to_date'] ) : null;
		$is_public       = isset( $args['is_public'] ) ? $args['is_public'] : null;
		$where           = array();

		if ( ! empty( $object ) ) {
			$where[] = array( 'object' => $object );
		}

		if ( ! empty( $action ) ) {
			$where[] = array( 'action' => $action );
		}

		if ( ! empty( $type ) ) {
			$where[] = array( 'type' => $type );
		}

		if ( ! empty( $object_id ) ) {
			$where[] = array( 'object_id' => $object_id );
		}

		if ( ! empty( $user_id ) ) {
			$where[] = array( 'user_id' => $user_id );
		}

		if ( $is_public !== null ) {
			$where[] = array( 'is_public' => $is_public );
		}

		if ( ! empty( $from_date ) ) {
			$from_date = date( 'Y-m-d 00:00:00', strtotime( $from_date ) );
			$where[]   = array( 'created_at' => array( '>=', $from_date ) );
		}

		if ( ! empty( $to_date ) ) {
			$to_date = date( 'Y-m-d 23:59:59', strtotime( $to_date ) );
			$where[] = array( 'created_at' => array( '<=', $to_date ) );
		}

		$exclude_objects = isset( $args['exclude_objects'] ) && is_array( $args['exclude_objects'] ) ? $args['exclude_objects'] : array();
		$exclude_actions = isset( $args['exclude_actions'] ) && is_array( $args['exclude_actions'] ) ? $args['exclude_actions'] : array();
		if ( ! empty( $exclude_objects ) || ! empty( $exclude_actions ) ) {
			return self::list_with_exclude( $args, $per_page, $offset, $sort );
		}

		$total = $db->get_count( $where );
		$logs  = $db->get_rows( $where, $per_page, $offset, $sort );

		return array(
			'logs'  => $logs,
			'total' => $total,
		);
	}

	/**
	 * Get all logs based on filters like object, action, user ID, date range, pagination.
	 * This method handles exclude_objects filtering using custom SQL.
	 *
	 * @param array $args Filters for querying logs.
	 * @param int   $per_page Number of records per page.
	 * @param int   $offset Offset for pagination.
	 * @param string $sort Sort order (asc/desc).
	 * @return array List of logs with total count.
	 */
	public static function list_with_exclude( $args = array(), $per_page = 10, $offset = 0, $sort = 'desc' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ec_logs';

		$exclude_objects = isset( $args['exclude_objects'] ) && is_array( $args['exclude_objects'] ) ? $args['exclude_objects'] : array();
		$exclude_actions = isset( $args['exclude_actions'] ) && is_array( $args['exclude_actions'] ) ? $args['exclude_actions'] : array();

		$where  = array();
		$values = array();

		if ( ! empty( $args['object'] ) ) {
			$where[]  = 'object = %s';
			$values[] = $args['object'];
		}

		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'action = %s';
			$values[] = $args['action'];
		}

		if ( ! empty( $args['type'] ) ) {
			$where[]  = 'type = %s';
			$values[] = $args['type'];
		}

		if ( ! empty( $args['object_id'] ) ) {
			$where[]  = 'object_id = %d';
			$values[] = $args['object_id'];
		}

		if ( ! empty( $args['user_id'] ) ) {
			$where[]  = 'user_id = %d';
			$values[] = $args['user_id'];
		}

		if ( isset( $args['is_public'] ) ) {
			$where[]  = 'is_public = %d';
			$values[] = $args['is_public'];
		}

		if ( ! empty( $args['from_date'] ) ) {
			$from_date = date( 'Y-m-d 00:00:00', strtotime( trim( $args['from_date'] ) ) );
			$where[]  = 'created_at >= %s';
			$values[] = $from_date;
		}

		if ( ! empty( $args['to_date'] ) ) {
			$to_date = date( 'Y-m-d 23:59:59', strtotime( trim( $args['to_date'] ) ) );
			$where[]  = 'created_at <= %s';
			$values[] = $to_date;
		}

		if ( ! empty( $exclude_objects ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $args['exclude_objects'] ), '%s' ) );
			$where[]      = "object NOT IN ({$placeholders})";
			$values      = array_merge( $values, $args['exclude_objects'] );
		}

		if ( ! empty( $exclude_actions ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $args['exclude_actions'] ), '%s' ) );
			$where[]      = "action NOT IN ({$placeholders})";
			$values      = array_merge( $values, $args['exclude_actions'] );
		}

		$where_str = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$count_query = "SELECT COUNT(*) FROM {$table} {$where_str}";
		$count_query = ! empty( $values ) ? $wpdb->prepare( $count_query, $values ) : $count_query;
		$total       = (int) $wpdb->get_var( $count_query );

		$order = strtoupper( $sort ) === 'ASC' ? 'ASC' : 'DESC';

		$query = "SELECT * FROM {$table} {$where_str} ORDER BY id {$order} LIMIT %d OFFSET %d";
		$values[] = $per_page;
		$values[] = $offset;
		$query    = $wpdb->prepare( $query, $values );

		$logs = $wpdb->get_results( $query );

		return array(
			'logs'  => $logs,
			'total' => $total,
		);
	}

	/**
	 * Add a new log entry to the database.
	 *
	 * @param array $data The log data (object, action, object_id, user_id, note, ip_address, seen).
	 * @return bool|int Log ID on success, false on failure.
	 */
	public function add( $data ) {

		$log_data = array(
			'object'     => $data['object'],
			'action'     => $data['action'],
			'object_id'  => $data['object_id'] ?? null,
			'user_id'    => $data['user_id'] ?? null,
			'note'       => $data['note'] ?? null,
			'ip_address' => $data['ip_address'] ?? null,
			'seen'       => $data['seen'] ?? 0,
			'type'       => $data['type'] ?? 'info',
			'meta'   	 => $data['meta'] ?? null,
			'is_public'  => $data['is_public'] ?? 1,
		);

		return $this->db->insert_row( $log_data );
	}

	/**
	 * Delete a log entry by its ID.
	 *
	 * @param int $log_id The log ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $log_id ) {
		return $this->db->delete_row( $log_id );
	}

	/**
	 * Get a specific log entry by its ID.
	 *
	 * @param int $id The log ID.
	 * @return array|null The log data if found, otherwise null.
	 */
	public function get( $id ) {
		$log = new self( $id );

		if ( ! $log->exists() ) {
			return null;
		}

		return array(
			'id'         => $log->get_id(),
			'object'     => $log->get_object(),
			'action'     => $log->get_action(),
			'object_id'  => $log->get_object_id(),
			'user_id'    => $log->get_user_id(),
			'note'       => $log->get_note(),
			'ip_address' => $log->get_ip_address(),
			'seen'       => $log->get_seen(),
			'type'       => $log->get_type(),
			'meta'		 => $log->get_meta(),
			'is_public'  => $log->get_is_public(),
			'created_at' => $log->get_created_at(),
		);
	}
}
<?php
namespace EasyCommerce\Traits;

defined( 'ABSPATH' ) || exit;

trait Queue {

	/**
	 * Schedules a single event to run immediately (if not already scheduled).
	 *
	 * @param string $hook Action hook to execute.
	 * @param array  $args Optional. Arguments to pass to the callback function. Default empty array.
	 */
	public function schedule( $hook, $args = [] ) {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			wp_schedule_single_event( time(), $hook, $args );
		}
	}

	/**
	 * Schedules a single event to run at a specific timestamp.
	 *
	 * @param int    $run_at When to run the event (Unix timestamp).
	 * @param string $hook      Action hook to execute.
	 * @param array  $args      Optional. Arguments to pass to the callback function. Default empty array.
	 */
	public function schedule_at( $run_at, $hook, $args = [] ) {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			wp_schedule_single_event( $run_at, $hook, $args );
		}
	}

	/**
	 * Schedules a recurring event.
	 *
	 * @param int    $start_at	 When to run the first event (Unix timestamp).
	 * @param string $recurrence How often the event should recur ('hourly', 'daily', etc).
	 * @param string $hook       Action hook to execute.
	 * @param array  $args       Optional. Arguments to pass to the callback function. Default empty array.
	 */
	public function schedule_recurring( $start_at, $recurrence, $hook, $args = [] ) {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			wp_schedule_event( $start_at, $recurrence, $hook, $args );
		}
	}

	/**
	 * Unschedules a specific event.
	 *
	 * @param string $hook Action hook to remove.
	 * @param array  $args Optional. Arguments to match when removing. Default empty array.
	 */
	public function unschedule( $hook, $args = [] ) {
		$timestamp = wp_next_scheduled( $hook, $args );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $hook, $args );
		}
	}

	/**
	 * Clears all scheduled hooks with the given hook name and arguments.
	 *
	 * @param string $hook Hook name to clear.
	 * @param array  $args Optional. Arguments to match. Default empty array.
	 */
	public function clear_schedules( $hook, $args = [] ) {
		wp_clear_scheduled_hook( $hook, $args );
	}

	/**
	 * Checks if an event is already scheduled.
	 *
	 * @param string $hook Hook name to check.
	 * @param array  $args Optional. Arguments to match. Default empty array.
	 * @return bool
	 */
	public function has_schedule( $hook, $args = [] ) {
		return (bool) wp_next_scheduled( $hook, $args );
	}
}

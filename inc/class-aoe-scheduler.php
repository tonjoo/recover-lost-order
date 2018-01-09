<?php
/**
 * Scheduler
 */
class AOE_Scheduler {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->model        = new AOE_Model();
		$this->functions    = new AOE_Functions();
		$this->threshold    = aoe_get_threshold(); // in seconds.
		add_filter( 'cron_schedules', array( $this, 'aoe_cron_schedules' ) );
		if ( ! wp_next_scheduled( 'aoe_add_order_search_job' ) ) {
			wp_schedule_event( time(), 'aoe-schedule', 'aoe_add_order_search_job' );
		}
		add_action( 'aoe_add_order_search_job', array( $this, 'register_email_job' ) );
	}

	/**
	 * Register cron job
	 */
	public function register_email_job() {
		if ( function_exists( 'wp_background_add_job' ) ) { // check if wp background worker active.
			$job = new stdClass();
			$job->function = array( $this, 'search_and_send_abandon_order_email' );
			wp_background_add_job( $job );
		} else {
			$this->search_and_send_abandon_order_email();
		}
	}

	/**
	 * Send email to all abandoned orders
	 */
	public function search_and_send_abandon_order_email() {
		$abandoned_orders_ids = $this->model->get_abandoned_orders_ids();
		if ( ! empty( $abandoned_orders_ids ) ) {
			foreach ( $abandoned_orders_ids as $order_id ) {
				update_post_meta( $order_id, 'aoe_abandoned', 1 );
				$this->functions->send_single_email( $order_id );
				if ( class_exists( 'WP_CLI' ) ) { // debug in WP CLI.
					WP_CLI::log( 'Email sent to ' . $order_id );
				}
			}
		}
	}

	/**
	 * New cron schedule
	 *
	 * @param  array $schedules List of registered cron schedules.
	 * @return array            Cron schedules.
	 */
	public function aoe_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['aoe-schedule'] ) ) {
			$schedules['aoe-schedule'] = apply_filters(
				'aoe_email_schedule', array(
					'interval' => 5 * MINUTE_IN_SECONDS,
					'display' => __( 'Every 5 minutes', 'aoe' ),
				)
			);
		}
		return $schedules;
	}

}

new AOE_Scheduler();

<?php
/**
 * Tn_Cron_Job Class Doc Comment
 *
 * @category Class
 * @package  Tn_Cron_Job
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */

/**
 * Place to control plugin cronjob
 *
 * This class defines all code necessary to register/deregister cronjob event
 *
 * @since      1.0.0
 * @author     Truc Nguyen <anhtruc92@gmail.com>
 */
class Tn_Cron_Job {

	const CRON_HOOK = 'delete_log_file_monthly_action';
	/**
	 * Construct
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

	}

	/**
	 * Register event cronjob
	 *
	 * @since    1.0.0
	 */
	public static function setup_cron_job() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );

		if ( false === $timestamp ) {
			wp_schedule_event( time(), 'monthly', self::CRON_HOOK );
		}
	}

	/**
	 * Delete event cronjob
	 *
	 * @since    1.0.0
	 */
	public static function unset_con_job() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		wp_unschedule_event( $timestamp, self::CRON_HOOK );
	}
}

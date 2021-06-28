<?php
/**
 * Tn_Posts_Api_Activator Class Doc Comment
 *
 * @category Class
 * @package  Tn_Posts_Api_Activator
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */

/**
 * Fired during plugin activation
 *
 * @link       mailto:anhtruc92@gmail.com
 * @since      1.0.0
 *
 * @package    Tn_Posts_Api
 * @subpackage Tn_Posts_Api/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tn_Posts_Api
 * @subpackage Tn_Posts_Api/includes
 * @author     Truc Nguyen <anhtruc92@gmail.com>
 */
class Tn_Posts_Api_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		 Tn_Cron_Job::setup_cron_job();
	}
}

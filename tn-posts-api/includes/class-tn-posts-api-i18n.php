<?php
/**
 * Tn_Posts_Api_i18n Class Doc Comment
 *
 * @category Class
 * @package  Tn_Posts_Api_i18n
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       mailto:anhtruc92@gmail.com
 * @since      1.0.0
 *
 * @package    Tn_Posts_Api
 * @subpackage Tn_Posts_Api/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Tn_Posts_Api
 * @subpackage Tn_Posts_Api/includes
 * @author     Truc Nguyen <anhtruc92@gmail.com>
 */
class Tn_Posts_Api_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'tnposts-api',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

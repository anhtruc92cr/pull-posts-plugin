<?php
/**
 * Posts API File Doc Comment
 *
 * @category Posts API
 * @package  Posts API
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     #
 *
 */

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              mailto:anhtruc92@gmail.com
 * @since             1.0.0
 * @package           Tn_Posts_Api
 *
 * @wordpress-plugin
 * Plugin Name:       Posts API
 * Plugin URI:        #
 * Description:       This plugin will send post to other server when published based on category ID.
 * Version:           1.0.0
 * Author:            Truc Nguyen
 * Author URI:        mailto:anhtruc92@gmail.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tn-posts-api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'Tn_POSTS_API_VERSION', '1.0.0' );
define( 'Tn_ROOT', plugin_dir_path( __FILE__ ));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tn-posts-api-activator.php
 */
function activate_Tn_posts_api() {
	require_once Tn_ROOT . 'includes/class-tn-posts-api-activator.php';
	require_once Tn_ROOT . 'includes/class-tn-cron-job.php';
	Tn_Posts_Api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tn-posts-api-deactivator.php
 */
function deactivate_Tn_posts_api() {
	require_once Tn_ROOT . 'includes/class-tn-posts-api-deactivator.php';
	require_once Tn_ROOT . 'includes/class-tn-cron-job.php';
	Tn_Posts_Api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Tn_posts_api' );
register_deactivation_hook( __FILE__, 'deactivate_Tn_posts_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require Tn_ROOT . 'includes/class-tn-posts-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Tn_posts_api() {

	$plugin = Tn_Posts_Api::get_instance();
	$plugin->run();

}
run_Tn_posts_api();

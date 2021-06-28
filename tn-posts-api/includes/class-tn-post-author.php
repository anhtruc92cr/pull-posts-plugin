<?php
/**
 * Tn_Post_Author Class Doc Comment
 *
 * @category Class
 * @package  Tn_Post_Author
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */

/**
 * The file that process action related to post
 *
 * @link       mailto:anhtruc92@gmail.com
 * @since      1.0.0
 *
 * @package    Tn_Post_Author
 */
class Tn_Post_Author {

	/**
	 * The instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Tn_Post_Author    $instance    Singleton class
	 */
	private static $instance = null;

	/**
	 * The post data for send out
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Tn_Post_Author    $post_data    Contain all post informations.
	 */
	public $post_data;
	/**
	 * Construct
	 *
	 * @since    1.0.0
	 */
	protected function __construct() {
		add_action( 'Tn_log_error_after_send', array( $this, 'log_error' ), 10, 2 );
	}
	/**
	 * Only if the class has no instance
	 *
	 * @since    1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Tn_Post_Author();
		}

		return self::$instance;
	}

	/**
	 * Get endpoint from option setting
	 *
	 * @since    1.0.0
	 */
	public function get_end_point() {
		return get_option( 'Tn_posts_api_option_name' );
	}

	/**
	 * Set data for $post_data variable
	 *
	 * @since    1.0.0
	 * @param    string|array $post_data post data.
	 */
	public function set_post_data( $post_data ) {
		$this->post_data = $post_data;
	}

	/**
	 * Get post data
	 *
	 * @since    1.0.0
	 */
	public function get_post_data() {
		return $this->post_data;
	}

	/**
	 * Log error into a file
	 *
	 * @since    1.0.0
	 * @param    int    $post_ID post ID.
	 * @param    string $error   error to log.
	 */
	public function log_error( $post_ID, $error ) {
		global $wp_filesystem;
		if ( is_wp_error( $error ) ) {
			$content = $error->get_error_message();
			$url     = wp_nonce_url( 'options-general.php?page=tnposts-api', 'log-error' );
			$this->create_folder_log_file();
			if ( $this->connect_fs( $url, '', Tn_ROOT . 'log' ) ) {
				$dir  = $wp_filesystem->find_folder( Tn_ROOT . 'log' );
				$file = trailingslashit( $dir ) . 'error_log.log';
				$this->write_to_log_file( $file, $content );
			}
		}
	}

	/**
	 * Connect to file system more secure for create file
	 *
	 * @since    1.0.0
	 * @param    string                          $url URL.
	 * @param    string|array|WP_Error[optional] $method method.
	 * @param    string                          $context path.
	 * @param    string[optional]                $fields optional fields.
	 */
	public function connect_fs( $url, $method, $context, $fields = null ) {
		global $wp_filesystem;
		$credentials = request_filesystem_credentials( $url, $method, false, $context, $fields );
		if ( false === $credential ) {
			return false;
		}

		if ( ! WP_Filesystem( $credentials ) ) {
			request_filesystem_credentials( $url, $method, true, $context );
			return false;
		}

		return true;
	}

	/**
	 * Create log folder it it doesn't exist
	 *
	 * @since    1.0.0
	 */
	public function create_folder_log_file() {
		global $wp_filesystem;

		$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), Tn_ROOT );
		if ( ! $wp_filesystem->is_dir( $plugin_path . 'log' ) ) {
			$wp_filesystem->mkdir( $plugin_path . 'log' );
		}
	}

	/**
	 * Write error to text file
	 *
	 * @since    1.0.0
	 * @param    string                          $file path of file.
	 * @param    string|array|WP_Error[optional] $contents content to log.
	 */
	public function write_to_log_file( $file, $contents ) {
		global $wp_filesystem;

		$fp = @fopen( $file, 'a' );
		if ( ! $fp ) {
			return false;
		}

		mbstring_binary_safe_encoding();
		$contents      = '[' . gmdate( 'd-m-Y h:i:s A' ) . '] ' . $contents . PHP_EOL;
		$bytes_written = fwrite( $fp, $contents );
		reset_mbstring_encoding();
		fclose( $fp );

		$wp_filesystem->chmod( $file, 0200 );
	}

	/**
	 * Delete log file
	 *
	 * @since    1.0.0
	 * @param    string $file path of file.
	 */
	public function delete_log_file( $file ) {
		global $wp_filesystem;
		$wp_filesystem->delete( $file );
	}

	/**
	 * Get public key from option setting
	 *
	 * @since    1.0.0
	 */
	public function get_public_key() {
		$option = $this->get_end_point();
		if ( ! empty( $option['public_key'] ) ) {
			return $option['public_key'];
		}
		return '';
	}

	/**
	 * Get public key from option setting
	 *
	 * @since    1.0.0
	 */
	public function get_private_key() {
		$option = $this->get_end_point();
		if ( ! empty( $option['private_key'] ) ) {
			return $option['private_key'];
		}
		return '';
	}

	/**
	 * Check if it is sender or receiver
	 *
	 * @since    1.0.0
	 */
	public function check_sender() {
		$api_sender = $this->get_end_point();
		if ( empty( $api_sender['post_sender'] ) ) {
			return false;
		}
		if ( 1 == $api_sender['post_sender'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Get array of category endpoint
	 *
	 * @since    1.0.0
	 */
	public function get_category_endpoint() {
		$api_sender = $this->get_end_point();
		if ( ! empty( $api_sender['Tn_category_endpoint'] ) ) {
			$api_array = json_decode( $api_sender['Tn_category_endpoint'], true );
			if ( empty( $api_array ) ) {
				$api_array = json_decode( str_replace( '\"', '"', $api_sender['Tn_category_endpoint'] ), true );
			}
			return $api_array;
		}
		return '';
	}

	/**
	 * Delete log file monthly
	 *
	 * @since    1.0.0
	 */
	public function delete_log_file_monthly() {
		global $wp_filesystem;
		$url = wp_nonce_url( 'options-general.php?page=tnposts-api', 'log-error' );
		if ( $this->connect_fs( $url, '', Tn_ROOT . 'log' ) ) {
			$dir  = $wp_filesystem->find_folder( Tn_ROOT . 'log' );
			$file = trailingslashit( $dir ) . 'error_log.log';
			$wp_filesystem->delete( $file );
		}
	}
}

<?php
require Tn_ROOT . 'vendor/autoload.php';
use \Firebase\JWT\JWT;
/**
 * Tn_Post_Sender Class Doc Comment
 *
 * @category Class
 * @package  Tn_Post_Sender
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */

/**
 * Sender class.
 *
 * This is used to generate token and send data to another server
 *
 * @since      1.0.0
 * @package    Tn_Post_Sender
 * @author     Truc Nguyen <anhtruc92@gmail.com>
 */
class Tn_Post_Sender extends Tn_Post_Author {

	/**
	 * The instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Tn_Post_Sender    $instance    Singleton class
	 */
	private static $instance = null;

	/**
	 * The post data for send out
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Tn_Post_Sender    $post_data    Contain all post informations.
	 */
	public $post_data;
	/**
	 * Construct
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
	}
	/**
	 * Only if the class has no instance
	 *
	 * @since    1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Tn_Post_Sender();
		}

		return self::$instance;
	}

	/**
	 * Send data to another server.
	 *
	 * @since    1.0.0
	 * @param    string $data data to send.
	 * @param    string $aud  audience.
	 */
	public function send_data( $data, $aud ) {
		$token = $this->prepare_data_token( $aud );
		if ( $token ) {
			$options       = array(
				'body'        => wp_json_encode( $data ),
				'headers'     => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
				'timeout'     => 5 * 60,
				'redirection' => 5,
				'blocking'    => true,
				'httpversion' => '1.0',
				'sslverify'   => false,
			);
			$response      = wp_remote_post( esc_url_raw( $aud . '/wp-json/tn/v1/posts' ), $options );
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			// Bail out early if there are any errors.
			if ( ! in_array( $response_code, array( '200', '201' ) ) || is_wp_error( $response_body ) ) {
				return $response_body;
			}

			return true;
		}
	}
	/**
	 * Prepare data for send to another server.
	 *
	 * @since    1.0.0
	 * @param    string $aud  audience.
	 */
	public function prepare_data_token( $aud ) {
		$now       = time();
		$full_data = array(
			'iss' => 'https://www.example.com',
			'aud' => $aud,
			'iat' => $now - 1,
			'nbf' => $now,
			'exp' => $now + ( 5 * 60 ),
			'sub' => 'anhtruc92@gmail.com',
		);
		$token     = $this->generate_JWT_token( $full_data );
		return $token;
	}

	/**
	 * Generate JWT token with private key (created by openssl) & RS256 algorithm.
	 *
	 * @since    1.0.0
	 * @param    string $data  data to send.
	 */
	private function generate_JWT_token( $data ) {
		$private_key = $this->get_private_key();
		if ( ! empty( $private_key ) ) {
			$jwt = JWT::encode( $data, $private_key, 'RS256' );
		} else {
			$jwt = false;
		}
		return $jwt;
	}
}

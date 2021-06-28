<?php
/**
 * \Firebase\JWT\JWT File Doc Comment
 *
 * @category \Firebase\JWT\JWT
 * @package   \Firebase\JWT\JWT
 * @author    Truc Nguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */
require Tn_ROOT . 'vendor/autoload.php';
use \Firebase\JWT\JWT;
/**
 * Tn_Post_Receiver Class Doc Comment
 *
 * @category Class
 * @package  Tn_Post_Receiver
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */
/**
 * Receiver class.
 *
 * This is used to validate token and receive data from another server
 *
 * @since      1.0.0
 * @package    Tn_Post_Receiver
 * @author     Truc Nguyen <anhtruc92@gmail.com>
 */
class Tn_Post_Receiver extends Tn_Post_Author {

	/**
	 * The instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Tn_Post_Receiver    $instance    Singleton class
	 */
	private static $instance = null;

	/**
	 * The post data for send out
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Tn_Post_Receiver    $post_data    Contain all post informations.
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
			self::$instance = new Tn_Post_Receiver();
		}

		return self::$instance;
	}

	/**
	 * Validate token JWT.
	 *
	 * @since    1.0.0
	 * @param    string $jwt jwt token.
	 * @param    string $aud audience.
	 */
	private function validate_token( $jwt, $aud ) {
		/** Get the public key */
		$public_key = $this->get_public_key();
		$public_key = ( ! empty( $public_key ) ) ? $public_key : false;
		if ( ! $public_key ) {
			return new WP_Error(
				'Tn_auth_bad_config',
				'Public key is not configurated properly, please contact the admin',
				array(
					'status' => 403,
				)
			);
		}
		if ( ! isset( $jwt ) ) {
			return new WP_Error(
				'Tn_auth_bad_request',
				'Missing token',
				array(
					'status' => 403,
				)
			);
		}
		$now = time();
		try {
			$decoded = JWT::decode( $jwt, $public_key, array( 'RS256' ) );
			$ip_scc  = gethostbyname( 'example.com' );
			if ( $decoded->aud != $aud ) {
				return new WP_Error(
					'Tn_auth_bad_request',
					'Wrong aud',
					array(
						'status' => 403,
					)
				);
			}
			$iss_is_valid = isset( $decoded->iss ) && 'https://www.example.com' === $decoded->iss;
			if ( ! $iss_is_valid ) {
				return new WP_Error(
					'Tn_auth_bad_iss',
					'The iss do not match with [Example] server',
					array(
						'status' => 403,
					)
				);
			}
			// exp must be in the future.
			$exp = $decoded->exp > $now;
			// ist must be in the past.
			$iat = $decoded->iat < $now;
			// sub must be non-empty and is the UID of the user or device.
			$sub = isset( $decoded->sub ) ? $decoded->sub : '';
			if ( 'anhtruc92@gmail.com' !== $sub ) {
				return new WP_Error(
					'Tn_auth_bad_request',
					'Wrong sub',
					array(
						'status' => 403,
					)
				);
			}
			if ( $exp && $iat && ! empty( $sub ) ) {
				return $decoded;
			}
			return array(
				'code' => 'Tn_auth_valid_token',
				'data' => array(
					'status' => 200,
				),
			);
		} catch ( Exception $e ) {
			/** Something is wrong trying to decode the token, send back the error. */
			return new WP_Error(
				'Tn_auth_invalid_token',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		}
	}
	/**
	 * Receive data from another server.
	 *
	 * @since    1.0.0
	 * @param    string $data data receive.
	 * @param    string $jwt  jwt token.
	 * @param    string $aud  audience.
	 */
	public function receive_data( $data, $jwt, $aud ) {
		$validate = $this->validate_token( $jwt, $aud );
		if ( ! is_wp_error( $validate ) ) {
			$post_cls  = Tn_Post::get_instance();
			$data      = ( $this->check_is_JSON( $data ) ) ? json_decode( $data ) : $data;
			// Delete post.
			if( !empty( $data->action ) && 'delete' == $data->action ) {
				if ( ! empty( $data->meta_match ) ) {
					$post_id = $post_cls->get_meta_match( $data->meta_match );
				}
				if ( $post_id && $post_id > 0 ) {
					$result = $post_cls->delete_post( $post_id );
					return $result;
				}
			}
			// Create/Update post
			else {
				$post      = $post_cls->validate_data( $data );
				if( ! is_wp_error( $post ) ) {
					$post_meta = '';
					if ( ! empty( $post->post_meta['Tn_post_api_id'] ) ) {
						$post_meta = $post_cls->get_meta_match( $post->post_meta['Tn_post_api_id'] );
					} elseif ( ! empty( $post->ID ) ) {
						$post_meta = $post_cls->get_meta_match( $post->ID );
					}
					unset( $post->ID );
					if ( $post_meta ) {
						$post->ID = $post_meta;
					}
					$post_meta_arg = ( ! empty( $post->post_meta ) ) ? $post->post_meta : array();
					unset( $post->post_meta );
					$post_ID = $post_cls->create_post( $post, $post_meta_arg );
					return $post_ID;
				}	
			}		
		} else {
			return $validate;
		}
	}
	/**
	 * Check file is JSON or not.
	 *
	 * @since    1.0.0
	 * @param    string $string string to check.
	 */
	private function check_is_JSON( $string ) {
		json_decode( $string );
		return ( json_last_error() == JSON_ERROR_NONE );
	}
}

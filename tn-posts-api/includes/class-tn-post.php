<?php
/**
 * Tn_Post Class Doc Comment
 *
 * @category Class
 * @package  Tn_Post
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
 * @package    Tn_Post
 */
class Tn_Post {

	/**
	 * The instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Tn_Post_Sender    $instance    Singleton class
	 */
	private static $instance = null;

	/**
	 * The list meta of post
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Tn_Post    $meta_post    meta post.
	 */
	public $meta_post = array();

	/**
	 * The list meta key of post
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Tn_Post    $meta_key    meta key.
	 */
	public $meta_key = array( 'image', 'featured_img', 'show_post_title', 'categories', 'tags' );

	/**
	 * Construct
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'before_delete_post', array( $this, 'before_delete_post' ), 10, 2 );		
		add_action( 'Tn_send_post_api', array( $this, 'send_post' ), 10, 2 );	
		add_action( 'Tn_delete_post_api', array( $this, 'send_delete_post' ), 10, 2 );
		add_action( 'Tn_update_meta_after_create_post', array( $this, 'update_post_meta' ), 10, 2 );
	}

	/**
	 * Only if the class has no instance
	 *
	 * @since    1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Tn_Post();
		}

		return self::$instance;
	}

	/**
	 * Check category when user save post.
	 *
	 * @since    1.0.0
	 * @param    int $post_ID    post ID.
	 * @param    array $post     post information.
	 * @param    string $action  action information.
	 */
	public function check_match_post( $post_ID, $post, $action ) {
		$post_author_cls = Tn_Post_Author::get_instance();
		$post_sender     = $post_author_cls->check_sender();
		$post_cat = wp_get_post_categories( $post_ID, array( 'fields' => 'ids' ) );
		if ( $post_sender ) {
			// Get category Endpoint.
			$api_array = $post_author_cls->get_category_endpoint();
			if ( ! empty( $api_array ) && is_array( $api_array ) ) {
				foreach ( $api_array as $key => $api ) {
					// Check if the post belong to endpoint category.
					if ( in_array( $key, $post_cat ) ) {
						do_action( $action, $post, $api );
					}
				}
			}
		}
	}
	/**
	 * Check category when user save post.
	 *
	 * @since    1.0.0
	 * @param    string $post_ID post ID.
	 * @param    string $post    post information.
	 */
	public function save_post( $post_ID, $post ) {
		// Sync post status as well: draft, publish, private.
		if ( 'post' == $post->post_type ) {
			$this->check_match_post( $post_ID, $post, 'Tn_send_post_api' );
		}
	}

	/**
	 * Check category and delete post when user delete post belong to match category.
	 *
	 * @since    1.0.0
	 * @param    string $post_ID post ID.
	 * @param    string $post    post information.
	 */
	public function before_delete_post( $post_ID, $post ) {
		if ( 'post' == $post->post_type ) {
			$this->check_match_post( $post_ID, $post, 'Tn_delete_post_api' );
		}
	}

	/**
	 * Validate content post.
	 *
	 * @since    1.0.0
	 * @param    string $post    post information.
	 */
	public function validate_data( $post ) {
		// Check post title not empty.
		if ( empty( $post->post_title ) ) {
			return new WP_Error(
				'Tn_empty_title',
				'The post title is empty',
				array(
					'status' => 403,
				)
			);
		}
		// Set to specific author.
		$check_email = $this->check_email_exist();
		if ( $check_email ) {
			$post->post_author = $check_email;
		} else {
			// return false;
			return new WP_Error(
				'Tn_user_not_exist',
				'User not exist please create one',
				array(
					'status' => 403,
				)
			); 
			// $user = $this->create_user_editor();
			// if ( is_numeric( $user ) ) {
			// 	$post->post_author = $user;
			// } else {
			// 	return false;
			// }
		}

		// Sanitize data.
		$post->post_title    = sanitize_text_field( $post->post_title );
		$post->post_date     = current_time( 'mysql' );
		$post->post_modified = current_time( 'mysql' );
		$post->post_content  = wp_kses_data( $post->post_content );
		$post->post_excerpt  = sanitize_textarea_field( $post->post_excerpt );
		$post->post_status   = sanitize_text_field( $post->post_status );
		$post->post_type     = sanitize_text_field( $post->post_type );
		if ( ! empty( $post->post_meta ) ) {
			$post->post_meta = (array) $post->post_meta;
		}
		unset( $post->guid );
		return $post;
	}

	/**
	 * Check Editor API exist or not.
	 *
	 * @since    1.0.0
	 */
	public function check_email_exist() {
		return email_exists( 'anhtruc92@gmail.com' );
	}

	/**
	 * Create Editor API user.
	 *
	 * @since    1.0.0
	 */
	// public function create_user_editor() {
	// 	$username = 'editor_Tn_api';
	// 	$email    = 'anhtruc92@gmail.com';
	// 	$password = '1234.abcdNTAT';
	// 	$user_id  = username_exists( $username );
	// 	if ( ! $user_id && email_exists( $email ) == false ) {
	// 		$user_id = wp_create_user( $username, $password, $email );
	// 		if ( ! is_wp_error( $user_id ) ) {
	// 			$user = get_user_by( 'id', $user_id );
	// 			// Remove role.
	// 			$user->remove_role( 'administrator' );
	// 			// Add role.
	// 			$user->add_role( 'editor' );
	// 			return $user_id;
	// 		}
	// 	}
	// 	return false;
	// }

	/**
	 * Get all meta needed of post.
	 *
	 * @since    1.0.0
	 * @param    string $post    post information.
	 */
	public function get_meta_post( $post ) {
		$meta_key  = $this->meta_key;
		$meta_post = array();
		foreach ( $meta_key as $key => $value ) {
			if ( 'image' == $value ) {
				$thumb              = get_field( 'single_post_banner', $post->ID );
				$meta_post['image'] = ( ! empty( $thumb ) ) ? ( is_numeric( $thumb ) ? wp_get_attachment_url( $thumb ) : $thumb ) : '';
			} elseif ( 'featured_img' == $value ) {
				$meta_post['featured_img'] = get_the_post_thumbnail_url( $post, 'post-thumbnail' );
			} elseif ( 'categories' == $value ) {
				$meta_post['categories'] = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
			} elseif ( 'tags' == $value ) {
				$meta_post['tags'] = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
			} else {
				$post_meta = get_post_meta( $post->ID, $value );
				if ( ! empty( $post_meta ) ) {
					$meta_post[ $value ] = $post_meta;
				}
			}
		}
		return $meta_post;
	}

	/**
	 * Add meta post.
	 *
	 * @since    1.0.0
	 * @param    string $post    post information.
	 */
	public function set_meta_post( $post ) {
		$this->meta_post = apply_filter( 'Tn_meta_post', $this->get_meta_post( $post ) );
		return $this->meta_post;
	}

	/**
	 * Meta key allowed.
	 *
	 * @since    1.0.0
	 */
	public function set_meta_key() {
		return apply_filter( 'Tn_meta_key', $this->meta_key );
	}

	/**
	 * Get post from receiver.
	 *
	 * @since    1.0.0
	 * @param    string $meta_key meta key.
	 */
	public function get_meta_match( $meta_key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'Tn_post_api_id' AND meta_value = %s LIMIT 1", $meta_key ) );
		return $value;
	}

	/**
	 * Save post meta to match post two sites.
	 *
	 * @since    1.0.0
	 * @param    int $post_ID post ID.
	 */
	public function save_meta_post( $post_ID ) {
		update_post_meta( $post_ID, 'Tn_post_api_id', $post_ID );
	}

	/**
	 * Create and update post.
	 *
	 * @since    1.0.0
	 * @param    array $post      post information.
	 * @param    array $post_meta post meta.
	 */
	public function create_post( $post, $post_meta = array() ) {
		$post_ID = wp_insert_post( $post );
		if ( ! is_wp_error( $post_ID ) ) {
			do_action( 'Tn_update_meta_after_create_post', $post_meta, $post_ID );
			return $post_ID;
		}
		return false;
	}

	/**
	 * Delete post.
	 *
	 * @since    1.0.0
	 * @param    int $post_ID   post ID.
	 */
	public function delete_post( $post_ID ) {
		$result = wp_delete_post( $post_ID );
		return $result;
	}

	/**
	 * Update post meta after create/update post.
	 *
	 * @since    1.0.0
	 * @param    array $post_meta post meta.
	 * @param    int   $post_ID   post ID.
	 */
	public function update_post_meta( $post_meta = array(), $post_ID ) {
		if ( ! empty( $post_meta ) && is_array( $post_meta ) ) {
			foreach ( $post_meta as $key => $value ) {
				if ( 'image' == $key ) {
					$featured_img = $this->save_thumb_to_server( $value, $post_ID );
					update_post_meta( $post_ID, 'single_post_banner', $featured_img );
				} elseif ( 'featured_img' == $key ) {
					$featured_img = $this->save_thumb_to_server( $value, $post_ID );
					set_post_thumbnail( $post_ID, $featured_img );
				} elseif ( 'categories' == $key ) {
					wp_set_object_terms( $post_ID, $value, 'category' );
				} elseif ( 'tags' == $key ) {
					wp_set_post_tags( $post_ID, $value );
				} else {
					update_post_meta( $post_ID, $key, $value );
				}
			}
		}
	}

	/**
	 * Save image to media library server.
	 *
	 * @since    1.0.0
	 * @param    string $image_url image path.
	 * @param    int    $parent_id    post ID.
	 */
	public function save_thumb_to_server( $image_url, $parent_id ) {

		$image = $image_url;
		$get   = wp_remote_get( $image );
		$type  = wp_remote_retrieve_header( $get, 'content-type' );

		if ( ! $type ) {
			return false;
		}
		$img_exist = $this->check_image_exist( basename( $image ) );

		if ( $img_exist ) {
			$attach_id = $img_exist;
		} else {
			$mirror     = wp_upload_bits( basename( $image ), '', wp_remote_retrieve_body( $get ) );
			$dir        = wp_upload_dir();
			if( !empty( $dir['path'] ) ) {
				$name   = wp_unique_filename( $dir['path'], basename( $image ) );
				$attachment = array(
					'post_title'     => $name,
					'post_mime_type' => $type,
				);
				if( !empty( $mirror['file'] ) ) {
					$attach_id = wp_insert_attachment( $attachment, $mirror['file'], $parent_id );
					require_once ABSPATH . 'wp-admin/includes/image.php';
					$attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
					wp_update_attachment_metadata( $attach_id, $attach_data );					
				}
			}
		}

		return $attach_id;
	}

	/**
	 * Check image exist or not.
	 *
	 * @since    1.0.0
	 * @param    string $img image name.
	 */
	public function check_image_exist( $img ) {
		global $wpdb;
		$img = '%' . $img . '%';
		$sql = $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
			$img
		);
		return $wpdb->get_var( $sql ) !== null ? $wpdb->get_var( $sql ) : false;
	}

	/**
	 * Do this action after save_post action to make sure it's not affect.
	 *
	 * @since    1.0.0
	 * @param    array  $post post information.
	 * @param    string $aud audience.
	 */
	public function send_post( $post, $aud ) {
		// validate content of post.
		$post = $this->validate_data( $post );
		if ( !is_wp_error( $post ) ) {
			// Save a meta post to match post between sender and receiver.
			$meta_match = $this->get_meta_match( $post->ID );
			if( empty( $meta_match ) ) {
				$this->save_meta_post( $post->ID );				
			}
			// Set more meta for post before send to receiver.
			$post->post_meta                    = (array) $this->get_meta_post( $post );
			$post->post_meta['Tn_post_api_id'] = $post->ID;
			$sender_cls                         = Tn_Post_Sender::get_instance();
			$result                             = $sender_cls->send_data( $post, $aud );
			// For checking error log file work or not.
			// $result = new WP_Error(
			// 	'Tn_auth_bad_auth_header',
			// 	'Authorization header malformed.',
			// 	array(
			// 		'status' => 403,
			// 	)
			// );
			do_action( 'Tn_log_error_after_send', $post->ID, $result );
		}
	}

	/**
	 * Do this action after delete_post action to delete post on other servers.
	 *
	 * @since    1.0.0
	 * @param    array  $post post information.
	 * @param    string $aud audience.
	 */
	public function send_delete_post( $post, $aud ) {
		$post->meta_match = $post->ID;
		$post->action = 'delete';
		$sender_cls = Tn_Post_Sender::get_instance();
		$result     = $sender_cls->send_data( $post, $aud );
		do_action( 'Tn_log_error_after_send', $post->ID, $result );
	}
}

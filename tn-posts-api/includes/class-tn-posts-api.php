<?php
/**
 * Tn_Posts_Api Class Doc Comment
 *
 * @category Class
 * @package  Tn_Posts_Api
 * @author   TrucNguyen
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     anhtruc92@gmail.com
 */

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       mailto:anhtruc92@gmail.com
 * @since      1.0.0
 *
 * @package    Tn_Posts_Api
 * @subpackage Tn_Posts_Api/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tn_Posts_Api
 * @subpackage Tn_Posts_Api/includes
 * @author     Truc Nguyen <anhtruc92@gmail.com>
 */
class Tn_Posts_Api {
	/**
	 * The instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Tn_Post_Author    $instance    Singleton class
	 */
	private static $instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tn_Posts_Api_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'Tn_POSTS_API_VERSION' ) ) {
			$this->version = Tn_POSTS_API_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tnposts-api';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'init_admin_page' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'delete_log_file_monthly_action', array( Tn_Post_Author::get_instance(), 'delete_log_file_monthly' ) );
		Tn_Post::get_instance();

	}
	/**
	 * Only if the class has no instance
	 *
	 * @since    1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Tn_Posts_Api();
		}

		return self::$instance;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tn_Posts_Api_Loader. Orchestrates the hooks of the plugin.
	 * - Tn_Posts_Api_i18n. Defines internationalization functionality.
	 * - Tn_Posts_Api_Admin. Defines all hooks for the admin area.
	 * - Tn_Posts_Api_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tnposts-api-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tnposts-api-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tnposts-api-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tnposts-api-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tnpost-author.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tnpost-sender.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tnpost-receiver.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tnpost.php';

		$this->loader = new Tn_Posts_Api_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tn_Posts_Api_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tn_Posts_Api_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tn_Posts_Api_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tn_Posts_Api_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tn_Posts_Api_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	/**
	 * Setting page.
	 */
	public function add_plugin_page() {
		add_options_page(
			'Posts API',
			'Posts API',
			'manage_options',
			'tnposts-api',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Create admin page.
	 *
	 * @since    1.0.0
	 */
	public function create_admin_page() {
		$this->Tn_posts_api_options = get_option( 'Tn_posts_api_option_name' ); ?>

		<div class="wrap">
			<h2>Posts API</h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'Tn_posts_api_option_group' );
					do_settings_sections( 'tnposts-api-admin' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Init admin page.
	 *
	 * @since    1.0.0
	 */
	public function init_admin_page() {
		register_setting(
			'Tn_posts_api_option_group',
			'Tn_posts_api_option_name',
			array( $this, 'sanitize_data' )
		);

		add_settings_section(
			'Tn_posts_api_setting_section',
			'Settings',
			array( $this, 'show_section_info' ),
			'tnposts-api-admin'
		);

		add_settings_field(
			'Tn_category_endpoint',
			'Post Information & Authentication',
			array( $this, 'enter_category_enpoint' ),
			'tnposts-api-admin',
			'Tn_posts_api_setting_section'
		);
	}

	/**
	 * Sanitize data.
	 *
	 * @since    1.0.0
	 * @param    array $input input data.
	 */
	public function sanitize_data( $input ) {
		$sanitary_values = array();
		if ( ! empty( $input['Tn_category_endpoint'] ) ) {
			$sanitary_values['Tn_category_endpoint'] = wp_filter_nohtml_kses( $input['Tn_category_endpoint'] );
		}
		if ( ! empty( $input['post_sender'] ) ) {
			$sanitary_values['post_sender'] = sanitize_text_field( $input['post_sender'] );
		}
		if ( ! empty( $input['public_key'] ) ) {
			$sanitary_values['public_key'] = sanitize_textarea_field( $input['public_key'] );
		}
		if ( ! empty( $input['private_key'] ) ) {
			$sanitary_values['private_key'] = sanitize_textarea_field( $input['private_key'] );
		}

		return $sanitary_values;
	}

	/**
	 * Show section description.
	 *
	 * @since    1.0.0
	 */
	public function show_section_info() {
		echo '<p>Enter category and endpoint in json format</p><br /><p><strong>Note:</strong> Remember to create editor_Tn_api user for make sure this plugin work well.</p>';
	}

	/**
	 * Show input for enter the data.
	 *
	 * @since    1.0.0
	 */
	public function enter_category_enpoint() {
		// Check sender or receiver.
		$post_sender = ( ! empty( $this->Tn_posts_api_options['post_sender'] ) ) ? esc_attr( $this->Tn_posts_api_options['post_sender'] ) : '';
		print( '<input id="post-sender" type="radio" name="Tn_posts_api_option_name[post_sender]" value="1"' . checked( '1', $post_sender, false ) . ' /> <label for="post-sender">Post Sender</label><br />
  <input id="post-receiver" type="radio" name="Tn_posts_api_option_name[post_sender]" value="2"' . checked( '2', $post_sender, false ) . ' /> <label for="post-receiver">Post Receiver</label><br /> <br /><br />' );

		// Enter receiver information.
		printf(
			'<div class="post-api-info %s">Post Receiver Information<br /><textarea class="large-text" rows="5" name="Tn_posts_api_option_name[Tn_category_endpoint]" id="Tn_category_endpoint">%s</textarea></div>',
			( ! empty( $post_sender ) && 2 == $post_sender ) ? 'tnhidden' : '',
			isset( $this->Tn_posts_api_options['Tn_category_endpoint'] ) ? stripslashes( esc_textarea( $this->Tn_posts_api_options['Tn_category_endpoint'] ) ) : ''
		);
		// Public key and private key.
		printf(
			'<br /><div class="post-api-key-info">Public key <br /><textarea class="large-text" rows="5" name="Tn_posts_api_option_name[public_key]" id="public-key">%s</textarea></div>',
			isset( $this->Tn_posts_api_options['public_key'] ) ? esc_attr( $this->Tn_posts_api_options['public_key'] ) : ''
		);
		printf(
			'<div class="post-api-key-info">Private key <br /><textarea class="large-text" rows="5" name="Tn_posts_api_option_name[private_key]" id="private-key">%s</textarea></div>',
			isset( $this->Tn_posts_api_options['private_key'] ) ? esc_attr( $this->Tn_posts_api_options['private_key'] ) : ''
		);
	}

	/**
	 * Register route.
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'tn/v' . $version;
		$base      = 'posts';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'callback_to_routes' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'callback_to_routes' ),
					'permission_callback' => '__return_true',
				),
			)
		);

	}

	/**
	 * Callback when register route
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request $request request information.
	 */
	public function callback_to_routes( WP_REST_Request $request ) {
		/*
		 * Looking for the HTTP_AUTHORIZATION header, if not present just
		 * return the user.
		 */
		$auth = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? $_SERVER['HTTP_AUTHORIZATION'] : false;

		/* Double check for different auth header string (server dependent) */
		if ( ! $auth ) {
			$auth = isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}

		if ( ! $auth ) {
			return new WP_Error(
				'Tn_auth_no_auth_header',
				'Authorization header not found.',
				array(
					'status' => 403,
				)
			);
		}

		/*
		 * Looking for IP sender
		 */
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : false;

		/* Double check for different ip (server dependent) */
		if ( ! $ip ) {
			$ip = isset( $_SERVER['HTTP_REFERRER'] ) ? $_SERVER['HTTP_REFERRER'] : false;
		}
		$ip_tn = gethostbyname( 'example.com' );

		// if (!$ip || $ip != $ip_tn) {
		// 	return new WP_Error(
		// 		'Tn_auth_wrong_sender',
		// 		'Sender is not match',
		// 		array(
		// 		'status' => 403,
		// 		)
		// 	);
		// }
		
		/*
		 * The HTTP_AUTHORIZATION is present verify the format
		 * if the format is wrong return the user.
		 */
		list($token) = sscanf( $auth, 'Bearer %s' );
		if ( ! $token ) {
			return new WP_Error(
				'Tn_auth_bad_auth_header',
				'Authorization header malformed.',
				array(
					'status' => 403,
				)
			);
		}
		$aud          = get_home_url();
		$posted_data  = $request->get_body();
		$receiver_cls = Tn_Post_Receiver::get_instance();
		if ( ! empty( $posted_data ) ) {
			$result = $receiver_cls->receive_data( $posted_data, $token, $aud );
		} else {
			$result = new WP_Error(
				'Tn_empty_data',
				'Data is empty',
				array(
					'status' => 403,
				)
			);
		}
		return rest_ensure_response( $result );
	}
}

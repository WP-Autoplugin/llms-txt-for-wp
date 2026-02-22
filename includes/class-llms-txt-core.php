<?php
/**
 * The core plugin class.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_Core {

	/**
	 * Admin instance.
	 *
	 * @var LLMS_Txt_Admin
	 */
	private $admin;

	/**
	 * Public instance.
	 *
	 * @var LLMS_Txt_Public
	 */
	private $public;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		// Ideally use autoloading; here we require files directly.
		require_once LLMS_TXT_PLUGIN_DIR . 'includes/class-llms-txt-markdown.php';
		require_once LLMS_TXT_PLUGIN_DIR . 'admin/class-llms-txt-admin.php';
		require_once LLMS_TXT_PLUGIN_DIR . 'public/class-llms-txt-public.php';
	}

	/**
	 * Register all hooks for the plugin.
	 */
	private function init_hooks() {
		// Admin hooks.
		$this->admin = new LLMS_Txt_Admin();
		add_action( 'admin_menu', array( $this->admin, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $this->admin, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( LLMS_TXT_PLUGIN_FILE ), array( $this->admin, 'add_action_links' ) );

		// Public hooks.
		$this->public = new LLMS_Txt_Public();
		add_action( 'init', array( $this->public, 'add_rewrite_rules' ) );
		add_action( 'parse_request', array( $this->public, 'parse_request' ) );
		add_filter( 'query_vars', array( $this->public, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this->public, 'handle_markdown_requests' ), 1 );
		add_action( 'template_redirect', array( $this->public, 'handle_llms_txt_requests' ), 1 );
		add_action( 'send_headers', array( $this->public, 'output_markdown_alternate_header' ) );
		add_action( 'wp_head', array( $this->public, 'output_markdown_alternate_link' ) );

		// Activation hook to flush rewrite rules.
		register_activation_hook( LLMS_TXT_PLUGIN_FILE, array( $this, 'activate' ) );
	}

	/**
	 * Activation hook callback.
	 */
	public function activate() {
		// Ensure public instance is initialized.
		if ( ! isset( $this->public ) ) {
			$this->public = new LLMS_Txt_Public();
		}
		// Add rewrite rules.
		$this->public->add_rewrite_rules();

		// Flush rewrite rules to make the new rules effective.
		flush_rewrite_rules();
	}

	/**
	 * Retrieve the plugin settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = array(
			'selected_post'     => '',
			'post_types'        => array(),
			'posts_limit'       => 100,
			'enable_md_support' => 'yes',
		);

		return wp_parse_args( get_option( 'llms_txt_settings', array() ), $defaults );
	}
}

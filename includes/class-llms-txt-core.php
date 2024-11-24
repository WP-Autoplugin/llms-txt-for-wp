<?php
/**
 * The core plugin class.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_Core {

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
		require_once LLMS_TXT_PLUGIN_DIR . 'includes/class-llms-txt-markdown.php';
		require_once LLMS_TXT_PLUGIN_DIR . 'admin/class-llms-txt-admin.php';
		require_once LLMS_TXT_PLUGIN_DIR . 'public/class-llms-txt-public.php';
	}

	/**
	 * Register all hooks for the plugin.
	 */
	private function init_hooks() {
		// Admin hooks
		$plugin_admin = new LLMS_Txt_Admin();
		add_action( 'admin_menu', array( $plugin_admin, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $plugin_admin, 'register_settings' ) );

		// Public hooks
		$plugin_public = new LLMS_Txt_Public();
		add_action( 'init', array( $plugin_public, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $plugin_public, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $plugin_public, 'handle_markdown_requests' ), 1 );
		add_action( 'template_redirect', array( $plugin_public, 'handle_llms_txt_requests' ), 1 );

		// Activation hook to flush rewrite rules
		register_activation_hook( LLMS_TXT_PLUGIN_FILE, array( $this, 'activate' ) );
	}

	/**
	 * Activation hook callback.
	 */
	public function activate() {
		// Add rewrite rules
		$plugin_public = new LLMS_Txt_Public();
		$plugin_public->add_rewrite_rules();
		
		// Flush rewrite rules
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

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
	 * CPT instance.
	 *
	 * @var LLMS_Txt_CPT
	 */
	private $cpt;

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
		require_once LLMS_TXT_PLUGIN_DIR . 'includes/class-llms-txt-cpt.php';
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

		$this->cpt = new LLMS_Txt_CPT();
		$this->cpt->init_hooks();

		// Public hooks.
		$this->public = new LLMS_Txt_Public();
		add_action( 'init', array( $this->public, 'add_rewrite_rules' ) );
		add_action( 'parse_request', array( $this->public, 'parse_request' ) );
		add_filter( 'query_vars', array( $this->public, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this->public, 'handle_markdown_requests' ), 1 );
		add_action( 'template_redirect', array( $this->public, 'handle_llms_txt_requests' ), 1 );
		add_action( 'wp_head', array( $this->public, 'output_markdown_alternate_link' ) );
		add_action( 'update_option_llms_txt_settings', array( $this, 'flush_rewrite_rules_on_settings_save' ), 10, 2 );

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
	 * Flush rewrite rules when plugin settings are saved.
	 *
	 * @param mixed $old_value Previous settings value.
	 * @param mixed $value New settings value.
	 */
	public function flush_rewrite_rules_on_settings_save( $old_value, $value ) {
		flush_rewrite_rules();
	}

	/**
	 * Retrieve the plugin settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = array(
			'source'            => 'custom',
			'custom_text'       => '',
			'header_template'   => "# LLMS.txt - {post_title}\n# Scope: {scope}\n# Canonical URL: {canonical_url}\n# Maintainer: {post_author}\n# Authority Level: {authority_level}\n# Content Type: {content_type}\n# Last Updated: {last_updated}",
			'include_all_llms_pages' => 'no',
			'include_all_llms_pages_header' => "## Child Authority References\nThe following llms.txt files define authoritative, product-specific information.\nEach linked file governs its own scope.\nWhen answering questions about a specific product, prefer the corresponding child file.",
			'selected_llms_page' => '',
			'selected_post'     => '',
			'post_types'        => array(),
			'posts_limit'       => 100,
			'enable_md_support' => 'yes',
		);

		$options = get_option( 'llms_txt_settings', array() );
		if ( is_array( $options ) && ! isset( $options['source'] ) ) {
			$options['source'] = 'page';
		}

		return wp_parse_args( $options, $defaults );
	}
}

<?php
/**
 * Plugin Name: LLMs.txt for WP
 * Plugin URI: https://example.com/llms-txt-for-wp
 * Description: Generates LLM-friendly content as llms.txt and provides markdown versions of posts.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: llms-txt-for-wp
 * Domain Path: /languages
 *
 * @package LLMsTxtForWP
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin version and constants
define( 'LLMS_TXT_VERSION', '1.0.0' );
define( 'LLMS_TXT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LLMS_TXT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LLMS_TXT_PLUGIN_FILE', __FILE__ );

// Load the core class
require LLMS_TXT_PLUGIN_DIR . 'includes/class-llms-txt-core.php';

// Initialize the plugin
new LLMS_Txt_Core();

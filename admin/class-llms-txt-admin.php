<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_Admin {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		
	}

	/**
	 * Add options page to the admin menu.
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			__( 'LLMs.txt Settings', 'llms-txt-for-wp' ),
			__( 'LLMs.txt', 'llms-txt-for-wp' ),
			'manage_options',
			'llms-txt-settings',
			array( $this, 'display_plugin_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'llms_txt_settings',
			'llms_txt_settings',
			array( $this, 'validate_settings' )
		);

		add_settings_section(
			'llms_txt_general_section',
			__( 'General Settings', 'llms-txt-for-wp' ),
			array( $this, 'render_section_info' ),
			'llms-txt-settings'
		);

		add_settings_field(
			'selected_post',
			__( 'Selected Post/Page', 'llms-txt-for-wp' ),
			array( $this, 'render_selected_post_field' ),
			'llms-txt-settings',
			'llms_txt_general_section'
		);

		add_settings_field(
			'post_types',
			__( 'Post Types to Include', 'llms-txt-for-wp' ),
			array( $this, 'render_post_types_field' ),
			'llms-txt-settings',
			'llms_txt_general_section'
		);

		add_settings_field(
			'posts_limit',
			__( 'Posts Limit', 'llms-txt-for-wp' ),
			array( $this, 'render_posts_limit_field' ),
			'llms-txt-settings',
			'llms_txt_general_section'
		);

		add_settings_field(
			'enable_md_support',
			__( 'Enable .md Support', 'llms-txt-for-wp' ),
			array( $this, 'render_md_support_field' ),
			'llms-txt-settings',
			'llms_txt_general_section'
		);
	}

	/**
	 * Render the settings page.
	 */
	public function display_plugin_settings_page() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'LLMs.txt Settings', 'llms-txt-for-wp' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'llms_txt_settings' );
				do_settings_sections( 'llms-txt-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render section information.
	 */
	public function render_section_info() {
		echo '<p>' . esc_html__( 'Configure your LLMs.txt settings below.', 'llms-txt-for-wp' ) . '</p>';
	}

	/**
	 * Render selected post field.
	 */
	public function render_selected_post_field() {
		$settings = LLMS_Txt_Core::get_settings();
		wp_dropdown_pages(
			array(
				'name'              => 'llms_txt_settings[selected_post]',
				'show_option_none'  => __( 'Select a page', 'llms-txt-for-wp' ),
				'option_none_value' => '',
				'selected'          => $settings['selected_post'],
			)
		);
	}

	/**
	 * Render post types field.
	 */
	public function render_post_types_field() {
		$settings = LLMS_Txt_Core::get_settings();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types as $post_type ) {
			printf(
				'<label><input type="checkbox" name="llms_txt_settings[post_types][]" value="%s" %s> %s</label><br>',
				esc_attr( $post_type->name ),
				checked( in_array( $post_type->name, $settings['post_types'], true ), true, false ),
				esc_html( $post_type->label )
			);
		}
	}

	/**
	 * Render posts limit field.
	 */
	public function render_posts_limit_field() {
		$settings = LLMS_Txt_Core::get_settings();
		printf(
			'<input type="number" name="llms_txt_settings[posts_limit]" value="%d" min="1" max="1000">',
			esc_attr( $settings['posts_limit'] )
		);
	}

	/**
	 * Render MD support field.
	 */
	public function render_md_support_field() {
		$settings = LLMS_Txt_Core::get_settings();
		printf(
			'<input type="checkbox" name="llms_txt_settings[enable_md_support]" value="yes" %s>',
			checked( $settings['enable_md_support'], 'yes', false )
		);
	}

	/**
	 * Validate settings.
	 *
	 * @param array $input The input array.
	 * @return array
	 */
	public function validate_settings( $input ) {
		$output = array();

		$output['selected_post'] = absint( $input['selected_post'] );
		$output['post_types'] = isset( $input['post_types'] ) ? array_map( 'sanitize_text_field', $input['post_types'] ) : array();
		$output['posts_limit'] = absint( $input['posts_limit'] );
		$output['enable_md_support'] = isset( $input['enable_md_support'] ) ? 'yes' : 'no';

		return $output;
	}
}

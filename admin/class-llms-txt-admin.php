<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_Admin {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->settings = LLMS_Txt_Core::get_settings();
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
			'source',
			__( 'llms.txt Source', 'llms-txt-for-wp' ),
			array( $this, 'render_source_field' ),
			'llms-txt-settings',
			'llms_txt_general_section'
		);

		add_settings_field(
			'custom_text',
			__( 'Custom llms.txt Text', 'llms-txt-for-wp' ),
			array( $this, 'render_custom_text_field' ),
			'llms-txt-settings',
			'llms_txt_general_section',
			array( 'class' => 'llms-txt-custom-text-row' )
		);

		add_settings_field(
			'selected_post',
			__( 'Selected Page for llms.txt', 'llms-txt-for-wp' ),
			array( $this, 'render_selected_post_field' ),
			'llms-txt-settings',
			'llms_txt_general_section',
			array( 'class' => 'llms-txt-selected-page-row' )
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
			__( 'Markdown Support', 'llms-txt-for-wp' ),
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
			<?php
			$source = $this->settings['source'];
			$custom_display = 'custom' === $source ? 'table-row' : 'none';
			$page_display = 'page' === $source ? 'table-row' : 'none';
			?>
			<style>
				.llms-txt-custom-text-row { display: <?php echo esc_attr( $custom_display ); ?>; }
				.llms-txt-selected-page-row { display: <?php echo esc_attr( $page_display ); ?>; }
			</style>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'llms_txt_settings' );
				do_settings_sections( 'llms-txt-settings' );
				?>
				<p class="description" style="margin-bottom: -10px;">
					<?php
					printf(
						esc_html__( 'With these settings, your %1$s file will show %2$s.', 'llms-txt-for-wp' ),
						'<a href="' . esc_url( home_url( 'llms.txt' ) ) . '" target="_blank">llms.txt</a>',
						'<strong id="llms-txt-settings-hint"></strong>'
					);
					?>
					<span id="llms-txt-settings-hint-has-md-support" style="display: none;">
						<?php
						printf(
							// translators: %1$s is a list of post types.
							esc_html__( 'Markdown versions will also be available when you add the .md extension to the URL of %1$s or request Markdown with the "Accept" header.', 'llms-txt-for-wp' ),
							'<strong id="llms-txt-settings-hint-md-support-post-types"></strong>'
						);
						?>
					</span>
					<span id="llms-txt-settings-hint-no-md-support" style="display: none;">
						<?php esc_html_e( 'Markdown versions of posts will not be available when you add the .md extension to the URL or request Markdown with the "Accept" header.', 'llms-txt-for-wp' ); ?>
					</span>
				</p>
				<div style="margin-top: 30px; display: flex; align-items: center; gap: 16px;">
					<?php submit_button( null, 'primary', null, false ); ?>
					<p class="description" style="margin: 0;">
						<?php
						printf(
							esc_html__( 'Tip: you can use the available %1$s to customize the content of your llms.txt file.', 'llms-txt-for-wp' ),
							'<a href="https://github.com/search?q=repo%3AWP-Autoplugin%2Fllms-txt-for-wp%20apply_filters&type=code" target="_blank">' . esc_html__( 'filter hooks', 'llms-txt-for-wp' ) . '</a>'
						);
						?>
					</p>
				</div>
			</form>
		</div>
		<script>
			(function() {
				var selectedPost = document.getElementById('llms_txt_settings_selected_post');
				var sourceInputs = document.querySelectorAll('input[name="llms_txt_settings[source]"]');
				var customTextWrap = document.getElementById('llms-txt-custom-text-wrap');
				var selectedPostWrap = document.getElementById('llms-txt-selected-page-wrap');
				var customTextRow = customTextWrap ? customTextWrap.closest('tr') : null;
				var selectedPostRow = selectedPostWrap ? selectedPostWrap.closest('tr') : null;
				var customText = document.getElementById('llms_txt_settings_custom_text');
				var postTypes = document.querySelectorAll('input[name="llms_txt_settings[post_types][]"]');
				var mdSupport = document.getElementById('llms_txt_settings_enable_md_support');
				var hint = document.getElementById('llms-txt-settings-hint');
				var hintHasMdSupport = document.getElementById('llms-txt-settings-hint-has-md-support');
				var hintNoMdSupport = document.getElementById('llms-txt-settings-hint-no-md-support');
				var mdSupportPostTypes = document.getElementById('llms-txt-settings-hint-md-support-post-types');
				var postsLimit = document.getElementById('llms_txt_settings_posts_limit');

				function getSelectedSource() {
					var selected = document.querySelector('input[name="llms_txt_settings[source]"]:checked');
					return selected ? selected.value : 'custom';
				}

				function updateSourceFields() {
					var source = getSelectedSource();
					if (customTextWrap) {
						customTextWrap.style.display = source === 'custom' ? 'block' : 'none';
					}
					if (selectedPostWrap) {
						selectedPostWrap.style.display = source === 'page' ? 'block' : 'none';
					}
					if (customTextRow) {
						customTextRow.style.display = source === 'custom' ? 'table-row' : 'none';
					}
					if (selectedPostRow) {
						selectedPostRow.style.display = source === 'page' ? 'table-row' : 'none';
					}
				}

				function updateHint() {
					var hasMdSupport = mdSupport.checked;
					var selectedPostValue = selectedPost.value;
					var selectedPostText = selectedPost.options[selectedPost.selectedIndex].textContent.trim();
					var source = getSelectedSource();
					var types = Array.from(postTypes).filter(function(type) {
						return type.checked;
					}).map(function(type) {
						return type.nextElementSibling ? type.nextElementSibling.textContent : '';
					});

					if (source === 'custom') {
						hint.textContent = 'your custom text';
					} else if (selectedPostValue) {
						hint.textContent = 'the content of the "' + selectedPostText + '" page';
					} else {
						// hint.textContent = types.length ? 'all ' + types.join(', ') : 'just the site name and description';
						if (types.length) {
							var content = '';
							if (hasMdSupport) {
								content = 'links to the .md versions of the ';
							} else {
								content = 'the contents of the ';
							}
							hint.textContent = content + 'latest ' + postsLimit.value + ' ' + types.join(', ');
						} else {
							hint.textContent = 'just the site name and description';
						}
					}

					if (hasMdSupport && types.length) {
						hintHasMdSupport.style.display = 'inline';
						hintNoMdSupport.style.display = 'none';
						mdSupportPostTypes.textContent = types.join(', ');
					} else {
						hintHasMdSupport.style.display = 'none';
						hintNoMdSupport.style.display = 'inline';
					}
					
				}

				sourceInputs.forEach(function(input) {
					input.addEventListener('change', function() {
						updateSourceFields();
						updateHint();
					});
				});
				selectedPost.addEventListener('change', updateHint);
				if (customText) {
					customText.addEventListener('input', updateHint);
				}
				postsLimit.addEventListener('change', updateHint);
				postTypes.forEach(function(type) {
					type.addEventListener('change', updateHint);
				});
				mdSupport.addEventListener('change', updateHint);

				updateSourceFields();
				updateHint();
			})();
		</script>
		<?php
	}

	/**
	 * Render section information.
	 */
	public function render_section_info() {
		echo '<p>';
		printf(
			esc_html__( 'Configure your %1$s settings below.', 'llms-txt-for-wp' ) . '</p>',
			'<a href="' . esc_url( home_url( 'llms.txt' ) ) . '" target="_blank">llms.txt</a>'
		);
	}

	/**
	 * Render source field.
	 */
	public function render_source_field() {
		$source = $this->settings['source'];
		echo '<label style="display: inline-block; margin-right: 16px;">';
		printf(
			'<input type="radio" name="llms_txt_settings[source]" value="custom" %s> %s',
			checked( $source, 'custom', false ),
			esc_html__( 'Custom text', 'llms-txt-for-wp' )
		);
		echo '</label>';
		echo '<label style="display: inline-block;">';
		printf(
			'<input type="radio" name="llms_txt_settings[source]" value="page" %s> %s',
			checked( $source, 'page', false ),
			esc_html__( 'Page', 'llms-txt-for-wp' )
		);
		echo '</label>';
	}

	/**
	 * Render custom text field.
	 */
	public function render_custom_text_field() {
		$display = 'custom' === $this->settings['source'] ? 'block' : 'none';
		echo '<div id="llms-txt-custom-text-wrap" style="display: ' . esc_attr( $display ) . ';">';
		printf(
			'<textarea id="llms_txt_settings_custom_text" name="llms_txt_settings[custom_text]" rows="8" class="large-text code">%s</textarea>',
			esc_textarea( $this->settings['custom_text'] )
		);
		echo '<p class="description">' . esc_html__( 'Provide the exact content to output in llms.txt.', 'llms-txt-for-wp' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render selected post field.
	 */
	public function render_selected_post_field() {
		$display = 'page' === $this->settings['source'] ? 'block' : 'none';
		echo '<div id="llms-txt-selected-page-wrap" style="display: ' . esc_attr( $display ) . ';">';
		wp_dropdown_pages(
			array(
				'name'              => 'llms_txt_settings[selected_post]',
				'id'				=> 'llms_txt_settings_selected_post',
				'show_option_none'  => __( 'Select a page', 'llms-txt-for-wp' ),
				'option_none_value' => '',
				'selected'          => $this->settings['selected_post'],
			)
		);
		echo '<p class="description">' . esc_html__( 'If a page is selected, only that page will be included in the llms.txt file. If no page is selected, all posts from selected post types will be included.', 'llms-txt-for-wp' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render post types field.
	 */
	public function render_post_types_field() {
		$args = array(
			'public'   => true,
		);
		$args = apply_filters( 'llms_txt_admin_post_types_args', $args );
		$post_types = get_post_types( $args, 'objects' );

		foreach ( $post_types as $post_type ) {
			// Skip attachments.
			if ( 'attachment' === $post_type->name ) {
				continue;
			}

			printf(
				'<label><input type="checkbox" name="llms_txt_settings[post_types][]" value="%s" %s> <span>%s</span></label><br>',
				esc_attr( $post_type->name ),
				checked( in_array( $post_type->name, $this->settings['post_types'], true ), true, false ),
				esc_html( $post_type->label )
			);
		}
		echo '<p class="description">' . esc_html__( 'Select the post types to include in the llms.txt file and the *.md support.', 'llms-txt-for-wp' ) . '</p>';
	}

	/**
	 * Render posts limit field.
	 */
	public function render_posts_limit_field() {
		printf(
			'<input type="number" id="llms_txt_settings_posts_limit" name="llms_txt_settings[posts_limit]" value="%d" min="1">',
			esc_attr( $this->settings['posts_limit'] )
		);
	}

	/**
	 * Render MD support field.
	 */
	public function render_md_support_field() {
		echo '<p class="description"><label>';
		printf(
			'<input id="llms_txt_settings_enable_md_support" type="checkbox" name="llms_txt_settings[enable_md_support]" value="yes" %s>',
			checked( $this->settings['enable_md_support'], 'yes', false )
		);
		esc_html_e( 'Enable this option to provide a Markdown version of each post.', 'llms-txt-for-wp' );
		echo '</label></p>';
	}

	/**
	 * Validate settings.
	 *
	 * @param array $input The input array.
	 * @return array
	 */
	public function validate_settings( $input ) {
		$output = array();

		$source = isset( $input['source'] ) ? sanitize_text_field( $input['source'] ) : 'custom';
		$output['source'] = in_array( $source, array( 'custom', 'page' ), true ) ? $source : 'custom';
		$output['custom_text']      = isset( $input['custom_text'] ) ? sanitize_textarea_field( $input['custom_text'] ) : '';
		$output['selected_post']     = isset( $input['selected_post'] ) ? absint( $input['selected_post'] ) : '';
		$output['post_types']        = isset( $input['post_types'] ) ? array_map( 'sanitize_text_field', $input['post_types'] ) : array();
		$output['posts_limit']       = isset( $input['posts_limit'] ) ? absint( $input['posts_limit'] ) : 100;
		$output['enable_md_support'] = isset( $input['enable_md_support'] ) ? 'yes' : 'no';

		return $output;
	}

	/**
	 * Add plugin action link to the Settings page.
	 *
	 * @param array $links The existing links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=llms-txt-settings' ) ) . '">' . esc_html__( 'Settings', 'llms-txt-for-wp' ) . '</a>';
		return $links;
	}
}

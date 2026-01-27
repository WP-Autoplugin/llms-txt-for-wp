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
			'header_template',
			__( 'Header Template', 'llms-txt-for-wp' ),
			array( $this, 'render_header_template_field' ),
			'llms-txt-settings',
			'llms_txt_general_section',
			array( 'class' => 'llms-txt-header-template-row' )
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
			'selected_llms_page',
			__( 'Selected llms.txt Page', 'llms-txt-for-wp' ),
			array( $this, 'render_selected_llms_page_field' ),
			'llms-txt-settings',
			'llms_txt_general_section',
			array( 'class' => 'llms-txt-llms-page-row' )
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

		add_settings_field(
			'include_all_llms_pages',
			__( 'Include All llms.txt Pages', 'llms-txt-for-wp' ),
			array( $this, 'render_include_all_llms_pages_field' ),
			'llms-txt-settings',
			'llms_txt_general_section'
		);

		add_settings_field(
			'include_all_llms_pages_header',
			__( 'Include Header', 'llms-txt-for-wp' ),
			array( $this, 'render_include_all_llms_pages_header_field' ),
			'llms-txt-settings',
			'llms_txt_general_section',
			array( 'class' => 'llms-txt-include-all-header-row' )
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
			$llms_page_display = 'llms_txt_page' === $source ? 'table-row' : 'none';
			$header_template_display = 'llms_txt_page' === $source ? 'table-row' : 'none';
			$include_all_header_display = 'yes' === $this->settings['include_all_llms_pages'] ? 'table-row' : 'none';
			?>
			<style>
				.llms-txt-custom-text-row { display: <?php echo esc_attr( $custom_display ); ?>; }
				.llms-txt-selected-page-row { display: <?php echo esc_attr( $page_display ); ?>; }
				.llms-txt-llms-page-row { display: <?php echo esc_attr( $llms_page_display ); ?>; }
				.llms-txt-header-template-row { display: <?php echo esc_attr( $header_template_display ); ?>; }
				.llms-txt-include-all-header-row { display: <?php echo esc_attr( $include_all_header_display ); ?>; }
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
				var llmsPageSelect = document.getElementById('llms_txt_settings_selected_llms_page');
				var llmsPageWrap = document.getElementById('llms-txt-selected-llms-page-wrap');
				var llmsPageRow = llmsPageWrap ? llmsPageWrap.closest('tr') : null;
				var headerTemplateRow = document.querySelector('.llms-txt-header-template-row');
				var includeAllPages = document.getElementById('llms_txt_settings_include_all_llms_pages');
				var includeAllHeaderRow = document.querySelector('.llms-txt-include-all-header-row');
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
					if (llmsPageWrap) {
						llmsPageWrap.style.display = source === 'llms_txt_page' ? 'block' : 'none';
					}
					if (customTextRow) {
						customTextRow.style.display = source === 'custom' ? 'table-row' : 'none';
					}
					if (selectedPostRow) {
						selectedPostRow.style.display = source === 'page' ? 'table-row' : 'none';
					}
					if (llmsPageRow) {
						llmsPageRow.style.display = source === 'llms_txt_page' ? 'table-row' : 'none';
					}
					if (headerTemplateRow) {
						headerTemplateRow.style.display = source === 'llms_txt_page' ? 'table-row' : 'none';
					}
				}

				function updateIncludeAllHeader() {
					if (includeAllHeaderRow) {
						includeAllHeaderRow.style.display = includeAllPages && includeAllPages.checked ? 'table-row' : 'none';
					}
				}

				function updateHint() {
					var hasMdSupport = mdSupport.checked;
					var selectedPostValue = selectedPost.value;
					var selectedPostText = selectedPost.options[selectedPost.selectedIndex].textContent.trim();
					var llmsPageValue = llmsPageSelect ? llmsPageSelect.value : '';
					var llmsPageText = llmsPageSelect && llmsPageSelect.options[llmsPageSelect.selectedIndex] ? llmsPageSelect.options[llmsPageSelect.selectedIndex].textContent.trim() : '';
					var source = getSelectedSource();
					var types = Array.from(postTypes).filter(function(type) {
						return type.checked;
					}).map(function(type) {
						return type.nextElementSibling ? type.nextElementSibling.textContent : '';
					});

					if (source === 'custom') {
						hint.textContent = 'your custom text';
					} else if (source === 'page' && selectedPostValue) {
						hint.textContent = 'the content of the "' + selectedPostText + '" page';
					} else if (source === 'llms_txt_page' && llmsPageValue) {
						hint.textContent = 'the content of the "' + llmsPageText + '" llms.txt page';
					} else if (source === 'llms_txt_page') {
						hint.textContent = 'the selected llms.txt page content';
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
				if (llmsPageSelect) {
					llmsPageSelect.addEventListener('change', updateHint);
				}
				if (customText) {
					customText.addEventListener('input', updateHint);
				}
				postsLimit.addEventListener('change', updateHint);
				postTypes.forEach(function(type) {
					type.addEventListener('change', updateHint);
				});
				mdSupport.addEventListener('change', updateHint);
				if (includeAllPages) {
					includeAllPages.addEventListener('change', updateIncludeAllHeader);
				}

				updateSourceFields();
				updateIncludeAllHeader();
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
		echo '<label style="display: inline-block; margin-left: 16px;">';
		printf(
			'<input type="radio" name="llms_txt_settings[source]" value="llms_txt_page" %s> %s',
			checked( $source, 'llms_txt_page', false ),
			esc_html__( 'llms.txt Page', 'llms-txt-for-wp' )
		);
		echo '</label>';
	}

	/**
	 * Render header template field.
	 */
	public function render_header_template_field() {
		printf(
			'<textarea id="llms_txt_settings_header_template" name="llms_txt_settings[header_template]" rows="8" class="large-text code">%s</textarea>',
			esc_textarea( $this->settings['header_template'] )
		);
		echo '<p class="description">' . esc_html__( 'Used to build the header for llms.txt Page output. You can include placeholders like {post_title}, {scope}, {canonical_url}, {post_author}, {authority_level}, {content_type}, {last_updated}.', 'llms-txt-for-wp' ) . '</p>';
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
	 * Render selected llms.txt page field.
	 */
	public function render_selected_llms_page_field() {
		$display = 'llms_txt_page' === $this->settings['source'] ? 'block' : 'none';
		echo '<div id="llms-txt-selected-llms-page-wrap" style="display: ' . esc_attr( $display ) . ';">';
		$llms_pages = get_posts(
			array(
				'post_type'      => 'llms_txt_page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		echo '<select id="llms_txt_settings_selected_llms_page" name="llms_txt_settings[selected_llms_page]">';
		echo '<option value="">' . esc_html__( 'Select an llms.txt page', 'llms-txt-for-wp' ) . '</option>';
		foreach ( $llms_pages as $llms_page ) {
			printf(
				'<option value="%d" %s>%s</option>',
				esc_attr( $llms_page->ID ),
				selected( $this->settings['selected_llms_page'], $llms_page->ID, false ),
				esc_html( $llms_page->post_title )
			);
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Choose the llms.txt Page to output in llms.txt. Content is output as-is (no HTML-to-Markdown conversion).', 'llms-txt-for-wp' ) . '</p>';
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
	 * Render include all llms.txt pages field.
	 */
	public function render_include_all_llms_pages_field() {
		echo '<p class="description"><label>';
		printf(
			'<input id="llms_txt_settings_include_all_llms_pages" type="checkbox" name="llms_txt_settings[include_all_llms_pages]" value="yes" %s>',
			checked( $this->settings['include_all_llms_pages'], 'yes', false )
		);
		esc_html_e( 'Append links to all llms.txt Pages in the root llms.txt output.', 'llms-txt-for-wp' );
		echo '</label></p>';
	}

	/**
	 * Render include all llms.txt pages header field.
	 */
	public function render_include_all_llms_pages_header_field() {
		printf(
			'<textarea id="llms_txt_settings_include_all_llms_pages_header" name="llms_txt_settings[include_all_llms_pages_header]" rows="5" class="large-text code">%s</textarea>',
			esc_textarea( $this->settings['include_all_llms_pages_header'] )
		);
		echo '<p class="description">' . esc_html__( 'Shown above the list of child llms.txt links.', 'llms-txt-for-wp' ) . '</p>';
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
		$output['source'] = in_array( $source, array( 'custom', 'page', 'llms_txt_page' ), true ) ? $source : 'custom';
		$output['custom_text']        = isset( $input['custom_text'] ) ? sanitize_textarea_field( $input['custom_text'] ) : '';
		$output['header_template']    = isset( $input['header_template'] ) ? sanitize_textarea_field( $input['header_template'] ) : '';
		$output['selected_post']      = isset( $input['selected_post'] ) ? absint( $input['selected_post'] ) : '';
		$output['selected_llms_page'] = isset( $input['selected_llms_page'] ) ? absint( $input['selected_llms_page'] ) : '';
		$output['post_types']         = isset( $input['post_types'] ) ? array_map( 'sanitize_text_field', $input['post_types'] ) : array();
		$output['posts_limit']              = isset( $input['posts_limit'] ) ? absint( $input['posts_limit'] ) : 100;
		$output['enable_md_support']        = isset( $input['enable_md_support'] ) ? 'yes' : 'no';
		$output['include_all_llms_pages']        = isset( $input['include_all_llms_pages'] ) ? 'yes' : 'no';
		$output['include_all_llms_pages_header'] = isset( $input['include_all_llms_pages_header'] ) ? sanitize_textarea_field( $input['include_all_llms_pages_header'] ) : '';

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

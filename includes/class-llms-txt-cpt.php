<?php
/**
 * CPT and meta handling for llms.txt Page.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_CPT {
	/**
	 * Register CPT-related hooks.
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'register_llms_txt_page_cpt' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_llms_txt_page_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_llms_txt_page_meta_box' ) );
		add_action( 'save_post_llms_txt_page', array( $this, 'save_llms_txt_page_meta' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'handle_llms_txt_page_admin_head' ) );
	}

	/**
	 * Apply clean editor adjustments for the llms.txt Page post type.
	 */
	public function handle_llms_txt_page_admin_head() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'llms_txt_page' !== $screen->post_type ) {
			return;
		}

		// Nuke any remaining media/editor button injections.
		remove_all_actions( 'media_buttons' );
		add_filter( 'quicktags_settings', array( $this, 'filter_llms_txt_page_quicktags_settings' ) );
		add_filter( 'user_can_richedit', '__return_false' );
	}

	/**
	 * Disable Quicktags buttons for the llms.txt Page post type.
	 *
	 * @param array $settings Quicktags settings.
	 * @return array
	 */
	public function filter_llms_txt_page_quicktags_settings( $settings ) {
		$settings['buttons'] = '';
		return $settings;
	}

	/**
	 * Register the llms.txt Page custom post type.
	 */
	public function register_llms_txt_page_cpt() {
		$labels = array(
			'name'               => __( 'LLMs.txt Pages', 'llms-txt-for-wp' ),
			'singular_name'      => __( 'LLMs.txt Page', 'llms-txt-for-wp' ),
			'add_new'            => __( 'Add New', 'llms-txt-for-wp' ),
			'add_new_item'       => __( 'Add New LLMs.txt Page', 'llms-txt-for-wp' ),
			'edit_item'          => __( 'Edit LLMs.txt Page', 'llms-txt-for-wp' ),
			'new_item'           => __( 'New LLMs.txt Page', 'llms-txt-for-wp' ),
			'view_item'          => __( 'View LLMs.txt Page', 'llms-txt-for-wp' ),
			'search_items'       => __( 'Search LLMs.txt Pages', 'llms-txt-for-wp' ),
			'not_found'          => __( 'No LLMs.txt Pages found.', 'llms-txt-for-wp' ),
			'not_found_in_trash' => __( 'No LLMs.txt Pages found in Trash.', 'llms-txt-for-wp' ),
			'all_items'          => __( 'LLMs.txt Pages', 'llms-txt-for-wp' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'revisions' ),
			'menu_icon'           => 'dashicons-media-text',
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
		);

		register_post_type( 'llms_txt_page', $args );
	}

	/**
	 * Disable block editor for the llms.txt Page post type.
	 *
	 * @param bool   $use_block_editor Whether to use the block editor.
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function disable_llms_txt_page_block_editor( $use_block_editor, $post_type ) {
		if ( 'llms_txt_page' === $post_type ) {
			return false;
		}
		return $use_block_editor;
	}

	/**
	 * Add meta box for llms.txt Page fields.
	 */
	public function add_llms_txt_page_meta_box() {
		add_meta_box(
			'llms-txt-page-header',
			__( 'LLMs.txt Header Fields', 'llms-txt-for-wp' ),
			array( $this, 'render_llms_txt_page_meta_box' ),
			'llms_txt_page',
			'normal',
			'default'
		);
	}

	/**
	 * Render llms.txt Page meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_llms_txt_page_meta_box( $post ) {
		wp_nonce_field( 'llms_txt_page_meta', 'llms_txt_page_meta_nonce' );

		$defaults = $this->get_header_template_defaults();
		$fields = array(
			'scope' => array(
				'label' => __( 'Scope', 'llms-txt-for-wp' ),
				'meta_key' => '_llms_txt_header_scope',
				'type' => 'text',
				'definition' => __( 'The specific entity, company, product, or subject area that the llms.txt file applies to. Can be left blank if there is only 1 llms.txt', 'llms-txt-for-wp' ),
			),
			'output_parent' => array(
				'label' => __( 'Output Parent', 'llms-txt-for-wp' ),
				'meta_key' => '_llms_txt_output_parent',
				'type' => 'text',
				'definition' => __( 'Enter a path relative to your site root where you want your llms.txt to be output.', 'llms-txt-for-wp' ),
				'examples' => array(
					array(
						'value' => __( 'products/my-product', 'llms-txt-for-wp' ),
						'description' => esc_url( site_url( '/products/my-product/llms.txt' ) ),
					),
					array(
						'value' => __( 'my-service', 'llms-txt-for-wp' ),
						'description' => esc_url( site_url( '/my-service/llms.txt' ) ),
					),
					array(
						'value' => __( '/docs', 'llms-txt-for-wp' ),
						'description' => esc_url( site_url( '/docs/llms.txt.' ) ),
					),
				),
			),
			'authority_level' => array(
				'label' => __( 'Authority Level', 'llms-txt-for-wp' ),
				'meta_key' => '_llms_txt_header_authority_level',
				'type' => 'text',
				'definition' => __( 'A declaration of how strongly the information in this file should be trusted and preferred when conflicting information exists.', 'llms-txt-for-wp' ),
				'examples' => array(
					array(
						'value' => __( 'Authoritative', 'llms-txt-for-wp' ),
						'description' => __( 'Primary source controlled by the product owner; should be preferred over marketing pages, blogs, and third-party content.', 'llms-txt-for-wp' ),
					),
					array(
						'value' => __( 'Supplementary', 'llms-txt-for-wp' ),
						'description' => __( 'Accurate but secondary; useful for detail, not precedence.', 'llms-txt-for-wp' ),
					),
					array(
						'value' => __( 'Informational', 'llms-txt-for-wp' ),
						'description' => __( 'Descriptive only; not intended to resolve conflicts.', 'llms-txt-for-wp' ),
					),
				),
			),
			'content_type' => array(
				'label' => __( 'Content Type', 'llms-txt-for-wp' ),
				'meta_key' => '_llms_txt_header_content_type',
				'type' => 'text',
				'definition' => __( 'The intended use of the file by AI systems.', 'llms-txt-for-wp' ),
				'examples' => array(
					array(
						'value' => __( 'AI reference and summarization', 'llms-txt-for-wp' ),
						'description' => __( 'Canonical summaries, definitions, and facts.', 'llms-txt-for-wp' ),
					),
					array(
						'value' => __( 'AI reference and citation guidance', 'llms-txt-for-wp' ),
						'description' => __( 'Entity definition plus preferred citation targets.', 'llms-txt-for-wp' ),
					),
					array(
						'value' => __( 'AI technical reference', 'llms-txt-for-wp' ),
						'description' => __( 'Specs, APIs, schemas, or developer-level detail.', 'llms-txt-for-wp' ),
					),
				),
			),
		);

		echo '<table class="form-table"><tbody>';
		foreach ( $fields as $key => $field ) {
			$value = get_post_meta( $post->ID, $field['meta_key'], true );
			if ( '' === $value && isset( $defaults[ $key ] ) ) {
				$value = $defaults[ $key ];
			}
			echo '<tr>';
			echo '<th scope="row"><label for="' . esc_attr( $field['meta_key'] ) . '">' . esc_html( $field['label'] ) . '</label></th>';
			echo '<td>';
			printf(
				'<input class="regular-text" type="%s" id="%s" name="%s" value="%s">',
				esc_attr( $field['type'] ),
				esc_attr( $field['meta_key'] ),
				esc_attr( $field['meta_key'] ),
				esc_attr( $value )
			);
			if ( '_llms_txt_output_parent' === $field['meta_key'] ) {
				$parent = trim( (string) $value, '/' );
				if ( '' !== $parent ) {
					$url = home_url( trailingslashit( $parent ) . 'llms.txt' );
					echo ' <a class="description" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View llms.txt', 'llms-txt-for-wp' ) . '</a>';
				}
			}
			if ( isset( $field['definition'] ) ) {
				$definition = $field['definition'];
				echo '<p class="description">' . esc_html( $definition ) . '</p>';
			}
			if ( isset( $field['examples'] ) && is_array( $field['examples'] ) ) {
				echo '<p class="description"><strong>' . esc_html__( 'Examples:', 'llms-txt-for-wp' ) . '</strong></p>';
				echo '<ul class="description" style="margin: 0 0 8px 20px;">';
				foreach ( $field['examples'] as $example ) {
					echo '<li>';
					if ( is_array( $example ) ) {
						if ( isset( $example['value'] ) ) {
							echo '<code>' . esc_html( $example['value'] ) . '</code>';
						}
						if ( isset( $example['description'] ) && '' !== $example['description'] ) {
							echo ' ' . esc_html( $example['description'] );
						}
					} else {
						echo '<code>' . esc_html( $example ) . '</code>';
					}
					echo '</li>';
				}
				echo '</ul>';
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Save llms.txt Page meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function save_llms_txt_page_meta( $post_id, $post ) {
		if ( 'llms_txt_page' !== $post->post_type ) {
			return;
		}

		if ( ! isset( $_POST['llms_txt_page_meta_nonce'] ) || ! wp_verify_nonce( $_POST['llms_txt_page_meta_nonce'], 'llms_txt_page_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'_llms_txt_header_scope' => 'sanitize_text_field',
			'_llms_txt_output_parent' => 'sanitize_text_field',
			'_llms_txt_header_authority_level' => 'sanitize_text_field',
			'_llms_txt_header_content_type' => 'sanitize_text_field',
		);

		foreach ( $fields as $meta_key => $sanitize_callback ) {
			if ( isset( $_POST[ $meta_key ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $meta_key ] ) );
				if ( '_llms_txt_output_parent' === $meta_key ) {
					$value = trim( $value, '/' );
				}
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	/**
	 * Parse defaults from the header template.
	 *
	 * @return array
	 */
	private function get_header_template_defaults() {
		return array(
			'scope' => '',
			'output_parent' => '',
			'authority_level' => __( 'Authoritative', 'llms-txt-for-wp' ),
			'content_type' => __( 'AI reference and summarization', 'llms-txt-for-wp' ),
		);
	}
}

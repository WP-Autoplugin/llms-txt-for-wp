<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_Public {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $vars The array of query vars.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'markdown';
		$vars[] = 'llms_txt';
		return $vars;
	}

	/**
	 * Add rewrite rules for markdown endpoints.
	 */
	public function add_rewrite_rules() {
		$settings = LLMS_Txt_Core::get_settings();

		add_rewrite_rule(
			'llms.txt$',
			'index.php?llms_txt=1',
			'top'
		);

		if ( 'yes' === $settings['enable_md_support'] ) {
			add_rewrite_rule(
				'(.?.+?)\.md$',
				'index.php?pagename=$matches[1]&markdown=1',
				'top'
			);
		}
	}

	/**
	 * Handle markdown requests.
	 */
	public function handle_markdown_requests() {
		$settings = LLMS_Txt_Core::get_settings();

		if ( 'yes' !== $settings['enable_md_support'] || ! get_query_var( 'markdown' ) ) {
			return;
		}

		$post = get_post();
		if ( ! $post ) {
			return;
		}

		// Check if this post type should be included
		if ( ! empty( $settings['selected_post'] ) && $post->ID !== intval( $settings['selected_post'] ) &&
			! in_array( $post->post_type, $settings['post_types'], true ) ) {
			return;
		}

		header( 'Content-Type: text/markdown; charset=utf-8' );
		echo $this->convert_to_markdown( $post, false );
		exit;
	}

	/**
	 * Handle llms.txt requests.
	 */
	public function handle_llms_txt_requests() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) || '/llms.txt' !== $_SERVER['REQUEST_URI'] ) {
			return;
		}

		$settings = LLMS_Txt_Core::get_settings();
		$output = '';

		if ( ! empty( $settings['selected_post'] ) ) {

			// Selected post/page.
			$post = get_post( $settings['selected_post'] );
			if ( $post ) {
				// Output post title and content.
				$output .= $this->convert_to_markdown( $post );
			}
		} elseif ( ! empty( $settings['post_types'] ) ) {

			// All posts, grouped by post type. Also include site name and description.
			$output .= "# " . get_bloginfo( 'name' ) . "\n\n";
			$bloginfo = get_bloginfo( 'description' );
			if ( ! empty( $bloginfo ) ) {
				$output .= $bloginfo . "\n\n";
			}
			$output .= "---\n\n";

			if ( 'yes' === $settings['enable_md_support'] ) {
				$output .= "## Available Content\n\n";

				// If .md support is enabled, link to the markdown version of the posts.
				foreach ( $settings['post_types'] as $post_type ) {
					$posts = get_posts( array(
						'post_type'      => $post_type,
						'posts_per_page' => $settings['posts_limit'],
						'post_status'    => 'publish',
					) );

					if ( ! empty( $posts ) ) {
						$post_type_obj = get_post_type_object( $post_type );
						$output .= "### " . $post_type_obj->labels->name . "\n\n";

						foreach ( $posts as $post ) {
							$output .= "* [" . $post->post_title . "](" . get_permalink( $post ) . ".md)\n";
						}
						$output .= "\n";
					}
				}
			} else {

				// If .md support is not enabled, show the post title and content.
				foreach ( $settings['post_types'] as $post_type ) {
					$posts = get_posts( array(
						'post_type'      => $post_type,
						'posts_per_page' => $settings['posts_limit'],
						'post_status'    => 'publish',
					) );

					if ( ! empty( $posts ) ) {
						foreach ( $posts as $post ) {
							$output .= $this->convert_to_markdown( $post, true ) . "\n\n";
							$output .= "---\n\n";
						}
					}
				}
			}
		}

		header( 'Content-Type: text/markdown; charset=utf-8' );
		echo apply_filters( 'llms_txt_index_content', $output );
		exit;
	}

	/**
	 * Convert post content to markdown.
	 *
	 * @param WP_Post $post The post object.
	 * @return string
	 */
	private function convert_to_markdown( $post, $include_meta = true ) {
		if ( ! $post ) {
			return '';
		}

		if ( $include_meta ) {
			$markdown = "# " . $post->post_title . "\n\n";

			// Add post meta
			$markdown .= "Published: " . get_the_date( 'Y-m-d', $post ) . "\n";
			$markdown .= "Author: " . get_the_author_meta( 'display_name', $post->post_author ) . "\n\n";
		}

		// Convert content
		$content = apply_filters( 'the_content', $post->post_content );
		$markdown .= LLMS_Txt_Markdown::convert( $content );

		return apply_filters( 'llms_txt_markdown_content', $markdown, $post );
	}
}

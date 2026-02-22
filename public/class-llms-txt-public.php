<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_Public {

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
	 * Check if the request explicitly accepts Markdown.
	 *
	 * @return bool
	 */
	private function request_accepts_markdown() {
		$accept_header = isset( $_SERVER['HTTP_ACCEPT'] ) ? $_SERVER['HTTP_ACCEPT'] : '';
		if ( '' === $accept_header && function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			if ( is_array( $headers ) && isset( $headers['Accept'] ) ) {
				$accept_header = $headers['Accept'];
			}
		}
		return false !== stripos( $accept_header, 'text/markdown' );
	}

	/**
	 * Add rewrite rules for markdown endpoints.
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule(
			'^llms\.txt$',
			'index.php?llms_txt=1',
			'top'
		);
	}

	/**
	 * Instead of using rewrite rules, we'll parse the request ourselves
	 */
	public function parse_request( $wp ) {

		$settings = LLMS_Txt_Core::get_settings();

		if ( 'yes' !== $settings['enable_md_support'] ) {
			return;
		}

		$server_request_uri = $_SERVER['REQUEST_URI'];
		$_SERVER['REQUEST_URI'] = preg_replace( '/\.md$/', '', $_SERVER['REQUEST_URI'] );

		// Check if the current URL ends with .md
		if ( preg_match( '/\.md$/', $server_request_uri ) && ! preg_match( '|^/wp-admin/|', $server_request_uri ) ) {

			// Let WordPress parse the clean URL normally
			$wp->parse_request();

			// Now add our markdown flag
			$wp->query_vars['markdown'] = 1;
		}
	}

	/**
	 * Handle markdown requests.
	 */
	public function handle_markdown_requests() {

		$settings = LLMS_Txt_Core::get_settings();

		$accepts_markdown = $this->request_accepts_markdown();

		if ( 'yes' !== $settings['enable_md_support'] || ( ! get_query_var( 'markdown' ) && ! $accepts_markdown ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id || ! is_singular() ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		// Check if this post should be included.
		$should_include = in_array( $post->post_type, $settings['post_types'], true );
		$should_include = apply_filters( 'llms_txt_include_post', $should_include, $post );
		if ( ! $should_include ) {
			if ( get_query_var( 'markdown' ) ) {
				// Redirect to the .md-less version of the post.
				wp_redirect( get_permalink( $post ) );
				exit;
			}
			return;
		}

		// Prepare the Markdown content.
		$markdown_content = LLMS_Txt_Markdown::convert_post_to_markdown( $post, true );

		// Estimate token count (default: 1 token per 4 characters).
		$chars_per_token  = apply_filters( 'llms_txt_chars_per_token', 4 );
		$estimated_tokens = (int) ceil( mb_strlen( $markdown_content ) / max( 1, (int) $chars_per_token ) );

		/**
		 * Filter the headers sent with Markdown responses.
		 *
		 * Returning an empty array will suppress all headers. Headers are sent
		 * as key => value pairs. Duplicate header names will overwrite each other.
		 *
		 * @param array   $headers  Associative array of header name => header value.
		 * @param WP_Post $post     The post being rendered.
		 * @param string  $markdown The converted Markdown content.
		 */
		$headers = apply_filters(
			'llms_txt_markdown_headers',
			array(
				'Content-Type'      => 'text/markdown; charset=utf-8',
				'X-Markdown-Tokens' => (string) $estimated_tokens,
			),
			$post,
			$markdown_content
		);

		// Output the Markdown content with proper headers.
		foreach ( $headers as $name => $value ) {
			header( $name . ': ' . $value );
		}
		echo $markdown_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is escaped in the conversion method.
		exit;
	}

	/**
	 * Handle llms.txt requests.
	 */
	public function handle_llms_txt_requests() {
		if ( ! get_query_var( 'llms_txt' ) ) {
			return;
		}

		$settings = LLMS_Txt_Core::get_settings();
		$output   = '';

		if ( ! empty( $settings['selected_post'] ) ) {
			// Selected post/page.
			$post = get_post( $settings['selected_post'] );
			if ( $post ) {
				// Output post title and content.
				$output .= LLMS_Txt_Markdown::convert_post_to_markdown( $post );
			}
		} elseif ( ! empty( $settings['post_types'] ) ) {
			// All posts, grouped by post type. Also include site name and description.
			$output .= '# ' . esc_html( get_bloginfo( 'name' ) ) . "\n\n";
			$bloginfo = get_bloginfo( 'description' );
			if ( ! empty( $bloginfo ) ) {
				$output .= esc_html( $bloginfo ) . "\n\n";
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
						$output       .= '### ' . esc_html( $post_type_obj->labels->name ) . "\n\n";

						foreach ( $posts as $post ) {
							$output .= '* [' . esc_html( $post->post_title ) . '](' . esc_url( untrailingslashit( get_permalink( $post ) ) ) . ".md)\n";
						}
						$output .= "\n";
					}
				}
			} else {
				// If .md support is not enabled, show the post title and content.
				foreach ( $settings['post_types'] as $post_type ) {
					$args = array(
						'post_type'      => $post_type,
						'posts_per_page' => $settings['posts_limit'],
						'post_status'    => 'publish',
					);
					$args = apply_filters( 'llms_txt_posts_args', $args, $post_type );
					$posts = get_posts( $args );

					if ( ! empty( $posts ) ) {
						foreach ( $posts as $post ) {
							$output .= LLMS_Txt_Markdown::convert_post_to_markdown( $post, true ) . "\n\n";
							$output .= "---\n\n";
						}
					}
				}
			}
		} else {
			$output .= '# ' . esc_html( get_bloginfo( 'name' ) ) . "\n\n";
			$bloginfo = get_bloginfo( 'description' );
			if ( ! empty( $bloginfo ) ) {
				$output .= esc_html( $bloginfo ) . "\n\n";
			}
			$output .= "---\n\n";
		}

		// Output the llms.txt content with proper headers.
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo apply_filters( 'llms_txt_index_content', $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is already escaped.
		exit;
	}

	/**
	 * Output an alternate Markdown link in the HTTP headers.
	 */
	public function output_markdown_alternate_header() {
		$settings = LLMS_Txt_Core::get_settings();

		if ( 'yes' !== $settings['enable_md_support'] || ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$should_include = in_array( $post->post_type, $settings['post_types'], true );
		$should_include = apply_filters( 'llms_txt_include_post', $should_include, $post );
		if ( ! $should_include ) {
			return;
		}

		$markdown_url = untrailingslashit( get_permalink( $post ) ) . '.md';
		header( 'Link: <' . esc_url_raw( $markdown_url ) . '>; rel="alternate"; type="text/markdown"', false );
	}

	/**
	 * Output an alternate Markdown link in the document head.
	 */
	public function output_markdown_alternate_link() {
		$settings = LLMS_Txt_Core::get_settings();

		if ( 'yes' !== $settings['enable_md_support'] || ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$should_include = in_array( $post->post_type, $settings['post_types'], true );
		$should_include = apply_filters( 'llms_txt_include_post', $should_include, $post );
		if ( ! $should_include ) {
			return;
		}

		$markdown_url = untrailingslashit( get_permalink( $post ) ) . '.md';
		echo '<link rel="alternate" type="text/markdown" href="' . esc_url( $markdown_url ) . "\" />\n";
	}
}

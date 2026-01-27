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
		$vars[] = 'llms_txt_parent';
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
		add_rewrite_rule(
			'^(.+?)/llms\.txt$',
			'index.php?llms_txt=1&llms_txt_parent=$matches[1]',
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

		// Output the Markdown content with proper headers.
		header( 'Content-Type: text/markdown; charset=utf-8' );
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
		$source   = isset( $settings['source'] ) ? $settings['source'] : 'custom';
		$append_llms_pages = false;
		$request_parent = get_query_var( 'llms_txt_parent' );
		$request_parent = is_string( $request_parent ) ? trim( $request_parent, '/' ) : '';

		if ( 'custom' === $source ) {
			$output .= isset( $settings['custom_text'] ) ? $settings['custom_text'] : '';
			$append_llms_pages = true;
		} elseif ( 'llms_txt_page' === $source ) {
			// Output a llms.txt Page
			$llms_page = null;
			if ( '' !== $request_parent ) {
				// Check if the page has a parent set
				$matched_pages = get_posts(
					array(
						'post_type'      => 'llms_txt_page',
						'post_status'    => 'publish',
						'posts_per_page' => 1,
						'meta_key'       => '_llms_txt_output_parent',
						'meta_value'     => $request_parent,
					)
				);
				if ( ! empty( $matched_pages ) ) {
					$llms_page = $matched_pages[0];
				}
			}
			// Get the selected llms.txt page
			if ( ! $llms_page && '' === $request_parent && ! empty( $settings['selected_llms_page'] ) ) {
				$llms_page = get_post( $settings['selected_llms_page'] );
			}
			if ( $llms_page && 'llms_txt_page' === $llms_page->post_type ) {
				$header = $this->build_llms_txt_page_header( $llms_page, $settings );
				if ( '' !== $header ) {
					$output .= $header . "\n\n";
				}
				$output .= $llms_page->post_content;
				$append_llms_pages = true;
			}
		} elseif ( ! empty( $settings['selected_post'] ) ) {
			// Selected post/page.
			$post = get_post( $settings['selected_post'] );
			if ( $post ) {
				// Output post title and content.
				$output .= LLMS_Txt_Markdown::convert_post_to_markdown( $post );
				$append_llms_pages = true;
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
			$append_llms_pages = true;
		} else {
			$output .= '# ' . esc_html( get_bloginfo( 'name' ) ) . "\n\n";
			$bloginfo = get_bloginfo( 'description' );
			if ( ! empty( $bloginfo ) ) {
				$output .= esc_html( $bloginfo ) . "\n\n";
			}
			$output .= "---\n\n";
			$append_llms_pages = true;
		}

		if ( $append_llms_pages ) {
			$output .= $this->maybe_append_llms_txt_pages_section( $settings, $request_parent );
		}

		// Output the llms.txt content with proper headers.
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo apply_filters( 'llms_txt_index_content', $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is already escaped.
		exit;
	}

	/**
	 * Append links to all llms.txt Pages when enabled and on the root llms.txt.
	 *
	 * @param array  $settings Plugin settings.
	 * @param string $request_parent Parent segment from rewrite.
	 * @return string
	 */
	private function maybe_append_llms_txt_pages_section( $settings, $request_parent ) {
		if ( 'yes' !== $settings['include_all_llms_pages'] || '' !== $request_parent ) {
			return '';
		}

		$pages = get_posts(
			array(
				'post_type'      => 'llms_txt_page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( empty( $pages ) ) {
			return '';
		}

		$header = isset( $settings['include_all_llms_pages_header'] ) ? trim( $settings['include_all_llms_pages_header'] ) : '';
		$lines = array();
		foreach ( $pages as $page ) {
			$parent = $this->get_llms_txt_page_output_parent( $page );
			if ( '' === $parent ) {
				continue;
			}
			$url = home_url( trailingslashit( $parent ) . 'llms.txt' );
			$scope = trim( (string) get_post_meta( $page->ID, '_llms_txt_header_scope', true ) );
			$authority = trim( (string) get_post_meta( $page->ID, '_llms_txt_header_authority_level', true ) );
			$line = '- ' . esc_html( $page->post_title ) . '  ';
			if ( '' !== $scope ) {
				$line .= "\n  Scope: " . esc_html( $scope ) . '  ';
			}
			if ( '' !== $authority ) {
				$line .= "\n  Authority: " . esc_html( $authority ) . '  ';
			}
			$line .= "\n  URL: " . esc_url( $url ) . "\n";
			$lines[] = $line;
		}

		if ( empty( $lines ) ) {
			return '';
		}

		$output = "\n\n";
		if ( '' !== $header ) {
			$output .= $header . "\n\n";
		}
		$output .= implode( "\n", $lines ) . "\n";

		return $output;
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

	/**
	 * Build the header for llms.txt Page output using the template and meta fields.
	 *
	 * @param WP_Post $llms_page llms.txt Page post.
	 * @param array   $settings Plugin settings.
	 * @return string
	 */
	private function build_llms_txt_page_header( $llms_page, $settings ) {
		$template = isset( $settings['header_template'] ) ? $settings['header_template'] : '';
		if ( '' === $template ) {
			return '';
		}

		$meta = array(
			'post_title'      => $llms_page->post_title,
			'scope'           => get_post_meta( $llms_page->ID, '_llms_txt_header_scope', true ),
			'canonical_url'   => $this->get_llms_txt_page_canonical_url( $llms_page ),
			'post_author'     => $this->get_llms_txt_page_maintainer( $llms_page ),
			'authority_level' => get_post_meta( $llms_page->ID, '_llms_txt_header_authority_level', true ),
			'content_type'    => get_post_meta( $llms_page->ID, '_llms_txt_header_content_type', true ),
			'last_updated'    => $this->get_llms_txt_page_last_updated( $llms_page ),
		);

		$replacements = array();
		foreach ( $meta as $key => $value ) {
			if ( '' !== $value ) {
				$replacements[ '{' . $key . '}' ] = $value;
			}
		}
		if ( ! empty( $replacements ) ) {
			$template = strtr( $template, $replacements );
		}

		$lines = preg_split( "/\r\n|\r|\n/", $template );
		$header_lines = array();
		foreach ( $lines as $line ) {
			$trimmed = trim( $line );
			if ( '' === $trimmed ) {
				continue;
			}

			if ( preg_match( '/\{[a-z_]+\}/', $trimmed ) ) {
				continue;
			}

			$header_lines[] = $trimmed;
		}

		return implode( "\n", $header_lines );
	}

	/**
	 * Get the llms.txt Page maintainer name from the post author.
	 *
	 * @param WP_Post $llms_page llms.txt Page post.
	 * @return string
	 */
	private function get_llms_txt_page_maintainer( $llms_page ) {
		$display_name = get_the_author_meta( 'display_name', $llms_page->post_author );
		if ( '' !== $display_name ) {
			return $display_name;
		}
		$user = get_userdata( $llms_page->post_author );
		return $user ? $user->user_login : '';
	}

	/**
	 * Get the llms.txt Page last updated date in YYYY-MM-DD.
	 *
	 * @param WP_Post $llms_page llms.txt Page post.
	 * @return string
	 */
	private function get_llms_txt_page_last_updated( $llms_page ) {
		$timestamp = get_post_modified_time( 'U', false, $llms_page );
		if ( ! $timestamp ) {
			return '';
		}
		return date_i18n( 'Y-m-d', $timestamp );
	}

	/**
	 * Get the output parent path for the llms.txt Page.
	 *
	 * @param WP_Post $llms_page llms.txt Page post.
	 * @return string
	 */
	private function get_llms_txt_page_output_parent( $llms_page ) {
		$parent = get_post_meta( $llms_page->ID, '_llms_txt_output_parent', true );
		$parent = is_string( $parent ) ? trim( $parent ) : '';
		return trim( $parent, '/' );
	}

	/**
	 * Build the canonical URL from the output parent path.
	 *
	 * @param WP_Post $llms_page llms.txt Page post.
	 * @return string
	 */
	private function get_llms_txt_page_canonical_url( $llms_page ) {
		$parent = $this->get_llms_txt_page_output_parent( $llms_page );
		if ( '' === $parent ) {
			return home_url( 'llms.txt' );
		}
		return home_url( trailingslashit( $parent ) . 'llms.txt' );
	}
}

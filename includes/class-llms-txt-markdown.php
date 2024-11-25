<?php
/**
 * Helper class for Markdown operations.
 *
 * @package LLMsTxtForWP
 */

use League\HTMLToMarkdown\HtmlConverter;

class LLMS_Txt_Markdown {

	/**
	 * Convert HTML to Markdown using a reliable library.
	 *
	 * @param string $html The HTML content.
	 * @return string
	 */
	public static function convert( $html ) {
		// Using an external library ensures accurate and maintainable conversions.
		$converter = new HtmlConverter( array( 'strip_tags' => true ) );
		return $converter->convert( $html );
	}

	/**
	 * Convert a post object to Markdown.
	 *
	 * @param WP_Post $post         The post object.
	 * @param bool    $include_meta Whether to include meta information like title and date.
	 * @return string
	 */
	public static function convert_post_to_markdown( $post, $include_meta = true ) {
		if ( ! $post ) {
			return '';
		}

		$markdown = '';

		if ( $include_meta ) {
			$markdown .= '# ' . esc_html( $post->post_title ) . "\n\n";

			// Add post meta.
			$markdown .= '*Published:* ' . esc_html( get_the_date( 'Y-m-d', $post ) ) . "\n";
			$markdown .= '*Author:* ' . esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) . "\n\n";
		}

		// Convert content using the convert method.
		$content   = apply_filters( 'the_content', $post->post_content );
		$markdown .= self::convert( $content );

		return apply_filters( 'llms_txt_markdown_content', $markdown, $post );
	}
}

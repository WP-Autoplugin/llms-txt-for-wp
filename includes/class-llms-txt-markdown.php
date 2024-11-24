<?php
/**
 * Helper class for Markdown operations.
 *
 * @package LLMsTxtForWP
 */

class LLMS_Txt_Markdown {

	/**
	 * Convert HTML to Markdown.
	 *
	 * @param string $html The HTML content.
	 * @return string
	 */
	public static function convert( $html ) {
		// Remove comments
		$html = preg_replace( '/<!--.*?-->/s', '', $html );
		
		// Preserve line breaks
		$html = str_replace( array( '<br>', '<br/>', '<br />' ), "\n", $html );
		
		// Convert headings
		$html = preg_replace( '/<h1[^>]*>(.*?)<\/h1>/i', '# $1' . "\n\n", $html );
		$html = preg_replace( '/<h2[^>]*>(.*?)<\/h2>/i', '## $1' . "\n\n", $html );
		$html = preg_replace( '/<h3[^>]*>(.*?)<\/h3>/i', '### $1' . "\n\n", $html );
		$html = preg_replace( '/<h[4-6][^>]*>(.*?)<\/h[4-6]>/i', '#### $1' . "\n\n", $html );
		
		// Convert emphasis
		$html = preg_replace( '/<strong[^>]*>(.*?)<\/strong>/i', '**$1**', $html );
		$html = preg_replace( '/<b[^>]*>(.*?)<\/b>/i', '**$1**', $html );
		$html = preg_replace( '/<em[^>]*>(.*?)<\/em>/i', '*$1*', $html );
		$html = preg_replace( '/<i[^>]*>(.*?)<\/i>/i', '*$1*', $html );
		
		// Convert links
		$html = preg_replace( '/<a[^>]+href=["\'](.*?)["\'][^>]*>(.*?)<\/a>/i', '[$2]($1)', $html );
		
		// Convert lists
		$html = preg_replace( '/<ul[^>]*>(.*?)<\/ul>/is', '$1' . "\n", $html );
		$html = preg_replace( '/<ol[^>]*>(.*?)<\/ol>/is', '$1' . "\n", $html );
		$html = preg_replace( '/<li[^>]*>(.*?)<\/li>/i', '* $1' . "\n", $html );
		
		// Convert paragraphs
		$html = preg_replace( '/<p[^>]*>(.*?)<\/p>/i', '$1' . "\n\n", $html );
		
		// Convert blockquotes
		$html = preg_replace( '/<blockquote[^>]*>(.*?)<\/blockquote>/is', '> $1' . "\n\n", $html );
		
		// Convert code blocks
		$html = preg_replace( '/<pre[^>]*><code[^>]*>(.*?)<\/code><\/pre>/is', '```' . "\n$1\n" . '```' . "\n\n", $html );
		
		// Convert inline code
		$html = preg_replace( '/<code[^>]*>(.*?)<\/code>/i', '`$1`', $html );
		
		// Clean up
		$markdown = strip_tags( $html );
		$markdown = html_entity_decode( $markdown );
		
		// Fix spacing
		$markdown = preg_replace( "/[\r\n]+/", "\n\n", $markdown );
		$markdown = preg_replace( '/[ \t]+/', ' ', $markdown );
		
		return trim( $markdown );
	}
}

<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

class Heading_Parser {

	private const TRANSLITERATION = [
		'á' => 'a', 'ä' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e',
		'í' => 'i', 'ĺ' => 'l', 'ľ' => 'l', 'ň' => 'n', 'ó' => 'o',
		'ô' => 'o', 'ŕ' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u',
		'ý' => 'y', 'ž' => 'z',
		'Á' => 'a', 'Ä' => 'a', 'Č' => 'c', 'Ď' => 'd', 'É' => 'e',
		'Í' => 'i', 'Ĺ' => 'l', 'Ľ' => 'l', 'Ň' => 'n', 'Ó' => 'o',
		'Ô' => 'o', 'Ŕ' => 'r', 'Š' => 's', 'Ť' => 't', 'Ú' => 'u',
		'Ý' => 'y', 'Ž' => 'z',
	];

	/**
	 * Parse headings from HTML content.
	 *
	 * @param string   $content       HTML content.
	 * @param int[]    $levels        Heading levels to parse (e.g. [2, 3]).
	 * @param string[] $excluded_ids  Anchor IDs to exclude.
	 * @return array[]
	 */
	public function parse( string $content, array $levels = [ 2, 3 ], array $excluded_ids = [] ): array {
		$pattern = '/<h([2-4])([^>]*)>(.*?)<\/h\1>/si';

		if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			return [];
		}

		$headings = [];
		$used_ids = [];

		foreach ( $matches as $match ) {
			$level = (int) $match[1];

			if ( ! in_array( $level, $levels, true ) ) {
				continue;
			}

			if ( preg_match( '/class\s*=\s*["\'][^"\']*\bno-toc\b/i', $match[2] ) ) {
				continue;
			}

			$text = wp_strip_all_tags( $match[3] );
			$id   = $this->generate_id( $text, $used_ids );

			$used_ids[] = $id;

			if ( in_array( $id, $excluded_ids, true ) ) {
				continue;
			}

			$headings[] = [
				'tag'   => 'h' . $level,
				'text'  => $text,
				'id'    => $id,
				'level' => $level,
			];
		}

		return $headings;
	}

	/**
	 * Generate a URL-safe anchor ID from heading text.
	 */
	public function generate_id( string $text, array $used_ids = [] ): string {
		$id = mb_strtolower( $text, 'UTF-8' );

		$id = strtr( $id, self::TRANSLITERATION );

		$id = preg_replace( '/[^a-z0-9\s-]/', '', $id );
		$id = preg_replace( '/[\s]+/', '-', trim( $id ) );
		$id = preg_replace( '/-+/', '-', $id );
		$id = trim( $id, '-' );

		if ( empty( $id ) ) {
			$id = 'heading';
		}

		$base = $id;
		$i    = 2;
		while ( in_array( $id, $used_ids, true ) ) {
			$id = $base . '-' . $i;
			$i++;
		}

		return $id;
	}

	/**
	 * Inject ID attributes into headings in content.
	 * Hooked to the_content at priority 12.
	 *
	 * Also applies custom anchors from the zuno/toc block attributes.
	 */
	public function inject_ids( string $content ): string {
		if ( ! is_singular() ) {
			return $content;
		}

		// Get custom anchors from zuno/toc block if present.
		$custom_anchors = $this->get_custom_anchors_from_post();

		$used_ids = [];

		$content = preg_replace_callback(
			'/<h([2-4])([^>]*)>(.*?)<\/h\1>/si',
			function ( $match ) use ( &$used_ids, $custom_anchors ) {
				$attrs = $match[2];
				$text  = wp_strip_all_tags( $match[3] );

				// Skip if already has an ID.
				if ( preg_match( '/\bid\s*=\s*["\']/', $attrs ) ) {
					if ( preg_match( '/\bid\s*=\s*["\']([^"\']+)["\']/', $attrs, $id_match ) ) {
						$used_ids[] = $id_match[1];
					}
					return $match[0];
				}

				$generated_id = $this->generate_id( $text, $used_ids );
				$used_ids[]   = $generated_id;

				// Use custom anchor if set.
				$final_id = $generated_id;
				if ( ! empty( $custom_anchors[ $generated_id ] ) ) {
					$final_id = sanitize_title( $custom_anchors[ $generated_id ] );
				}

				return '<h' . $match[1] . ' id="' . esc_attr( $final_id ) . '"' . $attrs . '>' . $match[3] . '</h' . $match[1] . '>';
			},
			$content
		);

		return $content;
	}

	/**
	 * Extract custom anchors map from the zuno/toc block in current post.
	 */
	private function get_custom_anchors_from_post(): array {
		$post = get_post();
		if ( ! $post || ! has_block( 'zuno/toc', $post ) ) {
			return [];
		}

		$blocks = parse_blocks( $post->post_content );

		foreach ( $blocks as $block ) {
			if ( 'zuno/toc' === $block['blockName'] ) {
				return $block['attrs']['customAnchors'] ?? [];
			}
		}

		return [];
	}
}

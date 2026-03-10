<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

class Auto_Insert {

	public function init(): void {
		add_filter( 'the_content', [ $this, 'maybe_insert' ], 15 );
	}

	/**
	 * Auto-insert TOC before the first H2 if no zuno/toc block is present.
	 */
	public function maybe_insert( string $content ): string {
		if ( ! is_singular() ) {
			return $content;
		}

		$post = get_post();
		if ( ! $post ) {
			return $content;
		}

		$settings = Block_Renderer::get_settings();

		// Auto-insert disabled globally?
		if ( ! $settings['auto_insert'] ) {
			return $content;
		}

		// TOC disabled for this post?
		if ( get_post_meta( $post->ID, '_zuno_toc_disabled', true ) ) {
			return $content;
		}

		// Post type allowed?
		if ( ! in_array( $post->post_type, $settings['post_types'], true ) ) {
			return $content;
		}

		// Block already present?
		if ( has_block( 'zuno/toc', $post ) ) {
			return $content;
		}

		// Per-post heading levels override.
		$per_post_levels = get_post_meta( $post->ID, '_zuno_toc_heading_levels', true );
		if ( ! empty( $per_post_levels ) ) {
			$levels = array_filter(
				array_map( 'intval', explode( ',', $per_post_levels ) ),
				fn( $l ) => in_array( $l, [ 2, 3, 4 ], true )
			);
		} else {
			$levels = $settings['heading_levels'];
		}

		$parser   = new Heading_Parser();
		$headings = $parser->parse( $post->post_content, $levels );

		if ( count( $headings ) < $settings['min_headings'] ) {
			return $content;
		}

		$renderer = new Block_Renderer();
		$toc_html = $renderer->build_html( $headings, $settings['default_style'], $settings );

		// Insert before first <h2.
		$pos = stripos( $content, '<h2' );
		if ( false === $pos ) {
			return $content;
		}

		// Enqueue block assets — WordPress doesn't load them automatically
		// because no zuno/toc block exists in the post content.
		$this->enqueue_block_assets();

		return substr( $content, 0, $pos ) . $toc_html . substr( $content, $pos );
	}

	/**
	 * Enqueue block CSS & JS when auto-inserting (no block in content = WP won't load them).
	 */
	private function enqueue_block_assets(): void {
		$dir = ZUNO_TOC_DIR;

		if ( ! wp_style_is( 'zuno-toc-style', 'enqueued' ) ) {
			wp_enqueue_style(
				'zuno-toc-style',
				plugins_url( 'src/style.css', $dir . '/zuno-toc.php' ),
				[],
				ZUNO_TOC_VERSION
			);
		}

		if ( ! wp_script_is( 'zuno-toc-view-script', 'enqueued' ) ) {
			wp_enqueue_script(
				'zuno-toc-view-script',
				plugins_url( 'assets/frontend.js', $dir . '/zuno-toc.php' ),
				[],
				ZUNO_TOC_VERSION,
				true
			);
		}
	}
}

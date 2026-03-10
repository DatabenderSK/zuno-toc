<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

class Block_Renderer {

	/**
	 * Server-side render callback for zuno/toc block.
	 */
	public function render( array $attributes, string $content ): string {
		$post = get_post();
		if ( ! $post ) {
			return '';
		}

		if ( get_post_meta( $post->ID, '_zuno_toc_disabled', true ) ) {
			return '';
		}

		$settings       = self::get_settings();
		$style          = $attributes['style'] ?? $settings['default_style'];
		$list_style       = $attributes['listStyle'] ?? 'default';
		$show_toggle_attr = $attributes['showToggle'] ?? 'default';
		$accent_color     = $attributes['accentColor'] ?? '';
		$collapsed_attr   = $attributes['defaultCollapsed'] ?? 'default';
		$excluded         = $attributes['excludedHeadings'] ?? [];
		$custom_labels    = $attributes['customLabels'] ?? [];
		$custom_anchors   = $attributes['customAnchors'] ?? [];

		// Per-block overrides.
		if ( $list_style === 'numbers' ) {
			$settings['numbering'] = true;
		} elseif ( $list_style === 'bullets' ) {
			$settings['numbering'] = false;
		}

		if ( $show_toggle_attr === 'yes' ) {
			$settings['show_toggle'] = true;
		} elseif ( $show_toggle_attr === 'no' ) {
			$settings['show_toggle'] = false;
		}

		$strip_numbers = ! empty( $attributes['stripNumbers'] );

		// Resolve accent color (per-block > global > default).
		if ( empty( $accent_color ) && ! empty( $settings['accent_color'] ) ) {
			$accent_color = $settings['accent_color'];
		}

		// Resolve collapsed state.
		$collapsed = ! empty( $settings['default_collapsed'] );
		if ( $collapsed_attr === 'collapsed' ) {
			$collapsed = true;
		} elseif ( $collapsed_attr === 'expanded' ) {
			$collapsed = false;
		}

		// Resolve font size (per-block > global > default).
		$font_size_attr = $attributes['fontSize'] ?? 'default';
		$font_size = '';
		if ( $font_size_attr !== 'default' ) {
			$font_size = $font_size_attr;
		} elseif ( ! empty( $settings['font_size'] ) ) {
			$font_size = $settings['font_size'];
		}

		// Force toggle visible when collapsed (otherwise user can't open it).
		if ( $collapsed ) {
			$settings['show_toggle'] = true;
		}

		// Per-post heading levels override.
		$per_post_levels = get_post_meta( $post->ID, '_zuno_toc_heading_levels', true );
		if ( ! empty( $per_post_levels ) ) {
			$levels = array_map( 'intval', explode( ',', $per_post_levels ) );
		} else {
			$levels = $settings['heading_levels'];
		}

		$parser   = new Heading_Parser();
		$headings = $parser->parse( $post->post_content, $levels, $excluded );

		if ( count( $headings ) < $settings['min_headings'] ) {
			return '';
		}

		// Strip number prefixes (e.g., "1. Úvod" → "Úvod").
		if ( $strip_numbers ) {
			foreach ( $headings as &$heading ) {
				$heading['text'] = preg_replace( '/^\d+\.\s*/', '', $heading['text'] );
			}
			unset( $heading );
		}

		// Apply custom labels and anchors.
		foreach ( $headings as &$heading ) {
			$original_id = $heading['id'];

			if ( ! empty( $custom_labels[ $original_id ] ) ) {
				$heading['text'] = $custom_labels[ $original_id ];
			}

			if ( ! empty( $custom_anchors[ $original_id ] ) ) {
				$heading['id'] = $custom_anchors[ $original_id ];
			}
		}
		unset( $heading );

		return $this->build_html( $headings, $style, $settings, $accent_color, $collapsed, $font_size );
	}

	/**
	 * Build TOC HTML from parsed headings.
	 */
	public function build_html( array $headings, string $style, array $settings, string $accent_color = '', bool $collapsed = false, string $font_size = '' ): string {
		$title       = esc_html( $settings['toc_title'] );
		$show_toggle = $settings['show_toggle'];
		$numbering   = $settings['numbering'];
		$list_tag    = $numbering ? 'ol' : 'ul';
		$min_level   = min( array_column( $headings, 'level' ) );

		$toggle_text = $collapsed ? esc_html__( 'Zobraziť', 'zuno-toc' ) : esc_html__( 'Skryť', 'zuno-toc' );
		$aria_expanded = $collapsed ? 'false' : 'true';

		$toggle_btn = '';
		if ( $show_toggle ) {
			$toggle_btn = '<button class="zuno-toc__toggle" aria-expanded="' . $aria_expanded . '">' . $toggle_text . '</button>';
		}

		$classes = 'zuno-toc zuno-toc--' . esc_attr( $style );
		if ( $numbering ) {
			$classes .= ' zuno-toc--numbered';
		}
		if ( $collapsed ) {
			$classes .= ' zuno-toc--collapsed';
		}

		$style_parts = [];
		if ( $accent_color ) {
			$style_parts[] = '--zuno-toc-accent: ' . esc_attr( $accent_color );
		}
		if ( $font_size ) {
			$style_parts[] = '--zuno-toc-font-size: ' . esc_attr( $font_size );
		}
		$inline_style = $style_parts ? ' style="' . implode( '; ', $style_parts ) . ';"' : '';

		$body_hidden = $collapsed ? ' hidden' : '';

		$html  = '<nav class="' . $classes . '"' . $inline_style . ' aria-label="' . esc_attr( $title ) . '">';
		$html .= '<div class="zuno-toc__header">';
		$html .= '<span class="zuno-toc__title">' . $title . '</span>';
		$html .= $toggle_btn;
		$html .= '</div>';
		$html .= '<div class="zuno-toc__body"' . $body_hidden . '>';
		$html .= $this->build_list( $headings, $list_tag, $min_level );
		$html .= '</div>';
		$html .= '</nav>';

		return $html;
	}

	/**
	 * Build hierarchical list from flat headings array.
	 */
	private function build_list( array $headings, string $tag, int $min_level ): string {
		$html          = '<' . $tag . ' class="zuno-toc__list">';
		$current_level = $min_level;
		$open_items    = 0;

		foreach ( $headings as $heading ) {
			$level = $heading['level'];

			if ( $level > $current_level ) {
				$diff = $level - $current_level;
				for ( $i = 0; $i < $diff; $i++ ) {
					$html .= '<' . $tag . ' class="zuno-toc__sublist">';
					$current_level++;
				}
			} elseif ( $level < $current_level ) {
				$diff = $current_level - $level;
				for ( $i = 0; $i < $diff; $i++ ) {
					$html .= '</li></' . $tag . '>';
					$current_level--;
					$open_items--;
				}
				$html .= '</li>';
				$open_items--;
			} elseif ( $open_items > 0 ) {
				$html .= '</li>';
				$open_items--;
			}

			$html .= '<li class="zuno-toc__item">';
			$html .= '<a href="#' . esc_attr( $heading['id'] ) . '" class="zuno-toc__link">';
			$html .= esc_html( $heading['text'] );
			$html .= '</a>';
			$open_items++;
		}

		while ( $current_level > $min_level ) {
			$html .= '</li></' . $tag . '>';
			$current_level--;
			$open_items--;
		}
		if ( $open_items > 0 ) {
			$html .= '</li>';
		}

		$html .= '</' . $tag . '>';

		return $html;
	}

	/**
	 * Get merged settings with defaults.
	 */
	public static function get_settings(): array {
		$defaults = Settings::defaults();
		$saved    = get_option( Settings::OPTION_KEY, [] );

		return wp_parse_args( $saved, $defaults );
	}
}

<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

class Admin_Column {

	public function init(): void {
		$settings = Block_Renderer::get_settings();

		foreach ( $settings['post_types'] as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", [ $this, 'add_column' ] );
			add_action( "manage_{$pt}_posts_custom_column", [ $this, 'render_column' ], 10, 2 );
		}
	}

	public function add_column( array $columns ): array {
		$columns['zuno_toc'] = 'TOC';
		return $columns;
	}

	public function render_column( string $column, int $post_id ): void {
		if ( 'zuno_toc' !== $column ) {
			return;
		}

		// Disabled?
		if ( get_post_meta( $post_id, '_zuno_toc_disabled', true ) ) {
			echo '<span style="color:#999;">Vypnuté</span>';
			return;
		}

		// Has block?
		$post = get_post( $post_id );
		if ( $post && has_block( 'zuno/toc', $post ) ) {
			echo '<span style="color:#46b450;font-weight:600;">Blok</span>';
			return;
		}

		// Auto-insert?
		$settings = Block_Renderer::get_settings();
		if ( $settings['auto_insert'] && in_array( $post->post_type, $settings['post_types'], true ) ) {
			echo '<span style="color:#0073aa;">Auto</span>';
			return;
		}

		echo '<span style="color:#999;">—</span>';
	}
}

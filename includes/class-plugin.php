<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

class Plugin {

	public function run(): void {
		add_action( 'init', [ $this, 'register_block' ] );
		add_filter( 'block_categories_all', [ $this, 'register_block_category' ], 10, 2 );

		( new Post_Meta() )->init();
		( new Auto_Insert() )->init();
		( new Settings() )->init();
		( new Admin_Column() )->init();

		// Inject heading IDs into content (priority 12, before auto-insert at 15).
		add_filter( 'the_content', [ new Heading_Parser(), 'inject_ids' ], 12 );
	}

	/**
	 * Register the "Zuno" block category so all Zuno blocks appear together.
	 */
	public function register_block_category( array $categories, $context ): array {
		// Prepend Zuno category so it appears near the top.
		array_unshift( $categories, [
			'slug'  => 'zuno',
			'title' => 'Zuno',
			'icon'  => null,
		] );

		return $categories;
	}

	public function register_block(): void {
		register_block_type(
			ZUNO_TOC_DIR,
			[
				'render_callback' => [ new Block_Renderer(), 'render' ],
			]
		);

		// Cache bust: set plugin version on all block assets.
		foreach ( [ 'zuno-toc-style', 'zuno-toc-editor-style' ] as $h ) {
			if ( isset( wp_styles()->registered[ $h ] ) ) {
				wp_styles()->registered[ $h ]->ver = ZUNO_TOC_VERSION;
			}
		}
		foreach ( [ 'zuno-toc-editor-script', 'zuno-toc-view-script' ] as $h ) {
			if ( isset( wp_scripts()->registered[ $h ] ) ) {
				wp_scripts()->registered[ $h ]->ver = ZUNO_TOC_VERSION;
			}
		}
	}
}

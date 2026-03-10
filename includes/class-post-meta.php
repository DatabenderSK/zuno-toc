<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

class Post_Meta {

	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
	}

	public function register(): void {
		register_post_meta(
			'',
			'_zuno_toc_disabled',
			[
				'type'          => 'boolean',
				'single'        => true,
				'default'       => false,
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);

		register_post_meta(
			'',
			'_zuno_toc_heading_levels',
			[
				'type'          => 'string',
				'single'        => true,
				'default'       => '',
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}
}

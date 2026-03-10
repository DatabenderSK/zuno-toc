<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

/**
 * Admin bar "Zuno" dropdown for quick access to all Zuno plugin settings.
 * Settings pages themselves live under Settings → Zuno TOC etc.
 */
class Zuno_Admin_Menu {

	public function init(): void {
		add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 990 );
	}

	/**
	 * Add "Zuno" dropdown to the admin bar.
	 */
	public function admin_bar( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if admin bar is disabled via settings.
		$settings = get_option( 'zuno_toc_settings', [] );
		if ( ! empty( $settings['hide_admin_bar'] ) ) {
			return;
		}

		$wp_admin_bar->add_node( [
			'id'    => 'zuno',
			'title' => 'Zuno',
			'href'  => admin_url( 'options-general.php?page=zuno-toc' ),
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'zuno-toc-settings',
			'parent' => 'zuno',
			'title'  => 'Zuno TOC',
			'href'   => admin_url( 'options-general.php?page=zuno-toc' ),
		] );
	}
}

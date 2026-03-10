<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

/**
 * Admin bar "Zuno" dropdown for quick access to all Zuno plugin settings.
 * Dynamically inserts itself as the second-to-last item in the admin bar.
 */
class Zuno_Admin_Menu {

	public function init(): void {
		// Run very late so all other plugins have already added their items.
		add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 99999 );
	}

	/**
	 * Add "Zuno" to the admin bar at the second-to-last position.
	 */
	public function admin_bar( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'zuno_toc_settings', [] );
		if ( ! empty( $settings['hide_admin_bar'] ) ) {
			return;
		}

		// Collect all top-level left-side nodes (parent is empty or 'root-default').
		$all_nodes = $wp_admin_bar->get_nodes();
		$top_level_ids = [];

		foreach ( $all_nodes as $node ) {
			if ( empty( $node->parent ) || $node->parent === 'root-default' ) {
				$top_level_ids[] = $node->id;
			}
		}

		// Get the last item so we can place Zuno before it.
		$last_id = ! empty( $top_level_ids ) ? end( $top_level_ids ) : null;

		if ( $last_id ) {
			$last_node = $all_nodes[ $last_id ];

			// Remove the last item, add Zuno, re-add the last item.
			$wp_admin_bar->remove_node( $last_id );

			$this->add_zuno_nodes( $wp_admin_bar );

			// Re-add the last item so it appears after Zuno.
			$wp_admin_bar->add_node( [
				'id'     => $last_node->id,
				'title'  => $last_node->title,
				'href'   => $last_node->href,
				'parent' => $last_node->parent ?: false,
				'meta'   => (array) ( $last_node->meta ?? [] ),
			] );
		} else {
			// Fallback: just add normally.
			$this->add_zuno_nodes( $wp_admin_bar );
		}
	}

	/**
	 * Add the Zuno parent node and its children.
	 */
	private function add_zuno_nodes( \WP_Admin_Bar $wp_admin_bar ): void {
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

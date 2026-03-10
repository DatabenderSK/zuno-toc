<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

/**
 * Shared "Zuno" top-level admin menu and admin bar for all Zuno plugins.
 *
 * Each Zuno plugin calls add_submenu_page() with 'zuno' as parent slug.
 * This class creates the top-level menu once and adds the admin bar dropdown.
 */
class Zuno_Admin_Menu {

	/**
	 * Green Zuno brand color.
	 */
	private const BRAND_COLOR = '#5ba462';

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_top_level_menu' ], 5 );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 80 );
		add_action( 'admin_head', [ $this, 'menu_icon_css' ] );
	}

	/**
	 * Register top-level "Zuno" menu (only if not already registered by another Zuno plugin).
	 */
	public function register_top_level_menu(): void {
		global $menu;

		// Check if "zuno" menu already exists (another Zuno plugin may have added it).
		foreach ( $menu as $item ) {
			if ( isset( $item[2] ) && $item[2] === 'zuno' ) {
				return;
			}
		}

		add_menu_page(
			'Zuno',
			'Zuno',
			'manage_options',
			'zuno',
			[ $this, 'render_hub_page' ],
			'data:image/svg+xml;base64,' . base64_encode( $this->get_menu_icon_svg() ),
			59 // Between Appearance (60) and Plugins (65).
		);
	}

	/**
	 * Hub page – shown when clicking the top-level "Zuno" menu.
	 */
	public function render_hub_page(): void {
		?>
		<div class="wrap">
			<h1>Zuno</h1>
			<p>Správa Zuno pluginov.</p>

			<div class="zuno-hub-cards" style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 20px;">
				<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px 24px; min-width: 200px;">
					<h3 style="margin-top: 0; color: <?php echo self::BRAND_COLOR; ?>;">Zuno TOC</h3>
					<p>Obsah článku (Table of Contents)</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=zuno-toc' ) ); ?>" class="button">Nastavenia</a>
				</div>
			</div>
		</div>
		<?php
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
			'title' => '<span class="ab-icon zuno-ab-icon"></span>Zuno',
			'href'  => admin_url( 'admin.php?page=zuno' ),
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'zuno-toc-settings',
			'parent' => 'zuno',
			'title'  => 'Zuno TOC',
			'href'   => admin_url( 'admin.php?page=zuno-toc' ),
		] );
	}

	/**
	 * CSS for the green admin bar icon and menu coloring.
	 */
	public function menu_icon_css(): void {
		?>
		<style>
			/* Admin bar Zuno icon */
			#wpadminbar .zuno-ab-icon::before {
				content: '\f163';
				font-family: dashicons;
				color: <?php echo self::BRAND_COLOR; ?>;
				font-size: 18px;
				margin-right: 4px;
			}
			/* Green tint on the sidebar menu icon */
			#adminmenu .toplevel_page_zuno .wp-menu-image img {
				opacity: 0.85;
			}
			#adminmenu .toplevel_page_zuno.current .wp-menu-image img,
			#adminmenu .toplevel_page_zuno:hover .wp-menu-image img {
				opacity: 1;
			}
		</style>
		<?php
	}

	/**
	 * Green SVG icon for the sidebar menu.
	 */
	private function get_menu_icon_svg(): string {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
			<rect x="2" y="3" width="20" height="18" rx="3" fill="#5ba462"/>
			<line x1="6" y1="8" x2="18" y2="8" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
			<line x1="6" y1="12" x2="15" y2="12" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
			<line x1="6" y1="16" x2="12" y2="16" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
		</svg>';
	}
}

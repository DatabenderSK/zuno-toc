<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

/**
 * Self-updater via GitHub Releases.
 *
 * Checks the GitHub API for a newer release tag and feeds
 * WordPress's built-in update mechanism so the plugin can
 * be updated with one click from the dashboard.
 */
class GitHub_Updater {

	private const GITHUB_USER = 'DatabenderSK';
	private const GITHUB_REPO = 'zuno-toc';
	private const CACHE_KEY   = 'zuno_toc_github_update';
	private const CACHE_TTL   = 6 * HOUR_IN_SECONDS;

	private string $plugin_file;
	private string $plugin_slug;
	private string $current_version;

	public function __construct( string $plugin_file, string $current_version ) {
		$this->plugin_file     = $plugin_file;
		$this->plugin_slug     = plugin_basename( $plugin_file );
		$this->current_version = $current_version;
	}

	public function init(): void {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );
		add_filter( 'upgrader_post_install', [ $this, 'after_install' ], 10, 3 );
		add_filter( 'plugin_action_links_' . $this->plugin_slug, [ $this, 'action_links' ] );
		add_filter( 'plugin_row_meta', [ $this, 'row_meta' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'handle_force_check' ] );
	}

	/**
	 * Inject update data into WordPress transient.
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $transient;
		}

		$remote_version = ltrim( $release['tag_name'], 'v' );

		if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
			$transient->response[ $this->plugin_slug ] = (object) [
				'slug'        => 'zuno-toc',
				'plugin'      => $this->plugin_slug,
				'new_version' => $remote_version,
				'url'         => $release['html_url'],
				'package'     => $release['zipball_url'],
				'icons'       => [],
				'banners'     => [],
			];
		}

		return $transient;
	}

	/**
	 * Provide plugin info for the "View details" modal.
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( $action !== 'plugin_information' || ( $args->slug ?? '' ) !== 'zuno-toc' ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		$remote_version = ltrim( $release['tag_name'], 'v' );

		return (object) [
			'name'            => 'Zuno TOC – Table of Contents',
			'slug'            => 'zuno-toc',
			'version'         => $remote_version,
			'author'          => '<a href="https://martinpavlic.sk">Martin Pavlič</a>',
			'homepage'        => 'https://github.com/' . self::GITHUB_USER . '/' . self::GITHUB_REPO,
			'requires'        => '6.0',
			'requires_php'    => '7.4',
			'tested'          => '6.7',
			'download_link'   => $release['zipball_url'],
			'sections'        => [
				'description' => 'Gutenberg blok pre automatický obsah článku (Table of Contents) s live náhľadom, toggle funkciou, farebným prispôsobením a auto-insertom.',
				'changelog'   => nl2br( esc_html( $release['body'] ?? '' ) ),
			],
		];
	}

	/**
	 * Rename extracted folder to match plugin slug after install.
	 * GitHub zipball extracts to "User-Repo-hash/" – rename to "zuno-toc/".
	 */
	public function after_install( $response, $hook_extra, $result ) {
		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
			return $result;
		}

		global $wp_filesystem;

		$install_dir = plugin_dir_path( $this->plugin_file );
		$wp_filesystem->move( $result['destination'], $install_dir );
		$result['destination'] = $install_dir;

		activate_plugin( $this->plugin_slug );

		return $result;
	}

	/**
	 * Add "Nastavenia" link next to Deactivate.
	 */
	public function action_links( array $links ): array {
		$url = admin_url( 'admin.php?page=zuno-toc' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">Nastavenia</a>' );
		return $links;
	}

	/**
	 * Add "Check for updates" link to plugin row meta.
	 */
	public function row_meta( array $links, string $file ): array {
		if ( $file !== $this->plugin_slug ) {
			return $links;
		}

		$url = wp_nonce_url(
			admin_url( 'plugins.php?zuno_toc_check_update=1' ),
			'zuno_toc_force_check'
		);

		$links[] = '<a href="' . esc_url( $url ) . '">Check for updates</a>';

		return $links;
	}

	/**
	 * Handle forced update check – clear cache and re-check.
	 */
	public function handle_force_check(): void {
		if ( empty( $_GET['zuno_toc_check_update'] ) ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		check_admin_referer( 'zuno_toc_force_check' );

		delete_transient( self::CACHE_KEY );
		delete_site_transient( 'update_plugins' );
		wp_update_plugins();

		wp_safe_redirect( admin_url( 'plugins.php?zuno_toc_checked=1' ) );
		exit;
	}

	/**
	 * Fetch latest release from GitHub API (cached).
	 */
	private function get_latest_release(): ?array {
		$cached = get_transient( self::CACHE_KEY );
		if ( $cached !== false ) {
			return $cached ?: null;
		}

		$url = sprintf(
			'https://api.github.com/repos/%s/%s/releases/latest',
			self::GITHUB_USER,
			self::GITHUB_REPO
		);

		$response = wp_remote_get( $url, [
			'headers' => [
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'Zuno-TOC-Updater/' . $this->current_version,
			],
			'timeout' => 10,
		] );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			set_transient( self::CACHE_KEY, '', self::CACHE_TTL );
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['tag_name'] ) ) {
			set_transient( self::CACHE_KEY, '', self::CACHE_TTL );
			return null;
		}

		set_transient( self::CACHE_KEY, $body, self::CACHE_TTL );

		return $body;
	}
}

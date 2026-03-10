<?php
namespace ZUNO_TOC;

defined( 'ABSPATH' ) || exit;

class Settings {

	public const OPTION_KEY = 'zuno_toc_settings';

	public static function defaults(): array {
		return [
			'post_types'     => [ 'post' ],
			'min_headings'   => 3,
			'heading_levels' => [ 2, 3 ],
			'default_style'  => 'minimal',
			'auto_insert'    => true,
			'toc_title'      => 'Obsah článku',
			'show_toggle'    => true,
			'numbering'      => false,
			'smooth_scroll'     => true,
			'default_collapsed' => false,
			'accent_color'      => '',
			'font_size'         => '',
			'hide_admin_bar'    => false,
		];
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// Expose settings to editor JS.
		add_action( 'enqueue_block_editor_assets', [ $this, 'localize_settings' ] );
	}

	public function add_menu(): void {
		add_submenu_page(
			'zuno',
			'Zuno TOC – Nastavenia',
			'Zuno TOC',
			'manage_options',
			'zuno-toc',
			[ $this, 'render_page' ]
		);
	}

	public function register_settings(): void {
		register_setting( 'zuno_toc_group', self::OPTION_KEY, [
			'type'              => 'array',
			'sanitize_callback' => [ $this, 'sanitize' ],
			'default'           => self::defaults(),
		] );
	}

	public function sanitize( $input ): array {
		$defaults  = self::defaults();
		$sanitized = [];

		// Post types.
		$sanitized['post_types'] = [];
		if ( ! empty( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$sanitized['post_types'] = array_map( 'sanitize_key', $input['post_types'] );
		}
		if ( empty( $sanitized['post_types'] ) ) {
			$sanitized['post_types'] = $defaults['post_types'];
		}

		// Min headings.
		$sanitized['min_headings'] = isset( $input['min_headings'] )
			? max( 1, absint( $input['min_headings'] ) )
			: $defaults['min_headings'];

		// Heading levels.
		$sanitized['heading_levels'] = [];
		if ( ! empty( $input['heading_levels'] ) && is_array( $input['heading_levels'] ) ) {
			foreach ( $input['heading_levels'] as $level ) {
				$level = absint( $level );
				if ( in_array( $level, [ 2, 3, 4 ], true ) ) {
					$sanitized['heading_levels'][] = $level;
				}
			}
		}
		if ( empty( $sanitized['heading_levels'] ) ) {
			$sanitized['heading_levels'] = $defaults['heading_levels'];
		}

		// Default style.
		$sanitized['default_style'] = in_array( $input['default_style'] ?? '', [ 'minimal', 'rounded', 'dark' ], true )
			? $input['default_style']
			: $defaults['default_style'];

		// Booleans.
		$sanitized['auto_insert']    = ! empty( $input['auto_insert'] );
		$sanitized['show_toggle']    = ! empty( $input['show_toggle'] );
		$sanitized['numbering']      = ! empty( $input['numbering'] );
		$sanitized['smooth_scroll']     = ! empty( $input['smooth_scroll'] );
		$sanitized['default_collapsed'] = ! empty( $input['default_collapsed'] );
		$sanitized['hide_admin_bar']    = ! empty( $input['hide_admin_bar'] );

		// Accent color.
		$sanitized['accent_color'] = '';
		if ( ! empty( $input['accent_color'] ) && preg_match( '/^#[0-9a-fA-F]{6}$/', $input['accent_color'] ) ) {
			$sanitized['accent_color'] = $input['accent_color'];
		}

		// Font size.
		$sanitized['font_size'] = '';
		$allowed_sizes = [ '13px', '15px', '16px', '18px' ];
		if ( ! empty( $input['font_size'] ) && in_array( $input['font_size'], $allowed_sizes, true ) ) {
			$sanitized['font_size'] = $input['font_size'];
		}

		// TOC title.
		$sanitized['toc_title'] = ! empty( $input['toc_title'] )
			? sanitize_text_field( $input['toc_title'] )
			: $defaults['toc_title'];

		return $sanitized;
	}

	public function localize_settings(): void {
		$settings = Block_Renderer::get_settings();

		wp_localize_script(
			'zuno-toc-editor-script',
			'zunoTocSettings',
			$settings
		);
	}

	public function render_page(): void {
		$settings   = Block_Renderer::get_settings();
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		unset( $post_types['attachment'] );
		?>
		<div class="wrap">
			<h1>Zuno TOC – Nastavenia</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'zuno_toc_group' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">Typy obsahu</th>
						<td>
							<?php foreach ( $post_types as $pt ) : ?>
								<label style="display:block;margin-bottom:4px;">
									<input type="checkbox"
										name="<?php echo self::OPTION_KEY; ?>[post_types][]"
										value="<?php echo esc_attr( $pt->name ); ?>"
										<?php checked( in_array( $pt->name, $settings['post_types'], true ) ); ?>
									>
									<?php echo esc_html( $pt->label ); ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row">Minimum nadpisov</th>
						<td>
							<input type="number" min="1" max="20"
								name="<?php echo self::OPTION_KEY; ?>[min_headings]"
								value="<?php echo esc_attr( $settings['min_headings'] ); ?>"
							>
						</td>
					</tr>
					<tr>
						<th scope="row">Úrovne nadpisov</th>
						<td>
							<?php foreach ( [ 2, 3, 4 ] as $level ) : ?>
								<label style="margin-right:12px;">
									<input type="checkbox"
										name="<?php echo self::OPTION_KEY; ?>[heading_levels][]"
										value="<?php echo $level; ?>"
										<?php checked( in_array( $level, $settings['heading_levels'], true ) ); ?>
									>
									H<?php echo $level; ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row">Predvolený štýl</th>
						<td>
							<select name="<?php echo self::OPTION_KEY; ?>[default_style]">
								<option value="minimal" <?php selected( $settings['default_style'], 'minimal' ); ?>>Minimálny</option>
								<option value="rounded" <?php selected( $settings['default_style'], 'rounded' ); ?>>Zaoblený</option>
								<option value="dark" <?php selected( $settings['default_style'], 'dark' ); ?>>Tmavý</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Titulok TOC</th>
						<td>
							<input type="text" class="regular-text"
								name="<?php echo self::OPTION_KEY; ?>[toc_title]"
								value="<?php echo esc_attr( $settings['toc_title'] ); ?>"
							>
						</td>
					</tr>
					<tr>
						<th scope="row">Možnosti</th>
						<td>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox"
									name="<?php echo self::OPTION_KEY; ?>[auto_insert]"
									value="1"
									<?php checked( $settings['auto_insert'] ); ?>
								>
								Auto-vložiť TOC ak chýba blok
							</label>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox"
									name="<?php echo self::OPTION_KEY; ?>[show_toggle]"
									value="1"
									<?php checked( $settings['show_toggle'] ); ?>
								>
								Zobraziť tlačidlo Skryť/Zobraziť
							</label>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox"
									name="<?php echo self::OPTION_KEY; ?>[numbering]"
									value="1"
									<?php checked( $settings['numbering'] ); ?>
								>
								Číslovanie položiek
							</label>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox"
									name="<?php echo self::OPTION_KEY; ?>[smooth_scroll]"
									value="1"
									<?php checked( $settings['smooth_scroll'] ); ?>
								>
								Plynulé scrollovanie
							</label>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox"
									name="<?php echo self::OPTION_KEY; ?>[default_collapsed]"
									value="1"
									<?php checked( $settings['default_collapsed'] ); ?>
								>
								Predvolene skrytý obsah (zložený)
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">Farba akcentu</th>
						<td style="display: flex; align-items: center; gap: 8px;">
							<input type="text"
								id="zuno-toc-color-hex"
								name="<?php echo self::OPTION_KEY; ?>[accent_color]"
								value="<?php echo esc_attr( $settings['accent_color'] ?: '#5ba462' ); ?>"
								placeholder="#5ba462"
								style="max-width: 120px; font-family: monospace;"
								oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('zuno-toc-color-preview').style.background=this.value;}"
							>
							<span id="zuno-toc-color-preview"
								style="display:inline-block; width:36px; height:36px; border-radius:6px; border:1px solid #ccc; background:<?php echo esc_attr( $settings['accent_color'] ?: '#5ba462' ); ?>; vertical-align:middle; flex-shrink:0;">
							</span>
							<span style="color:#999; font-size:13px;">Hex farba (predvolená: #5ba462)</span>
						</td>
					</tr>
					<tr>
						<th scope="row">Veľkosť písma</th>
						<td>
							<select name="<?php echo self::OPTION_KEY; ?>[font_size]">
								<option value="" <?php selected( $settings['font_size'], '' ); ?>>Predvolená (15px)</option>
								<option value="13px" <?php selected( $settings['font_size'], '13px' ); ?>>Malé (13px)</option>
								<option value="16px" <?php selected( $settings['font_size'], '16px' ); ?>>Stredné (16px)</option>
								<option value="18px" <?php selected( $settings['font_size'], '18px' ); ?>>Veľké (18px)</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Admin bar</th>
						<td>
							<label>
								<input type="checkbox"
									name="<?php echo self::OPTION_KEY; ?>[hide_admin_bar]"
									value="1"
									<?php checked( ! empty( $settings['hide_admin_bar'] ) ); ?>
								>
								Skryť Zuno z horného admin baru
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button( 'Uložiť nastavenia' ); ?>
			</form>
		</div>
		<?php
	}
}

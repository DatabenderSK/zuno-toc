# Zuno TOC – Table of Contents

WordPress plugin that automatically generates a Table of Contents from headings (H2/H3/H4) with a native Gutenberg block and live preview.

## Features

- **Gutenberg block** – native "Zuno TOC (Obsah článku)" block with live preview in editor
- **Auto-insert** – automatically inserts TOC before the first heading when no block is placed
- **3 visual styles** – Minimal, Rounded, Dark
- **8 color presets + custom hex** – per-block or global accent color
- **Bullets or numbering** – switchable per-block
- **Collapse/Expand toggle** – collapsible TOC with smooth animation
- **Default collapsed** – option to start with TOC collapsed
- **Inline editing** – change text and anchor URL directly in the editor
- **Heading exclusion** – hide individual headings or bulk-hide H3/H4
- **Strip number prefixes** – remove "1.", "2." prefixes from headings
- **Smooth scroll** – smooth scrolling to headings on click
- **Font size control** – per-block and global font size setting (13px / 16px / 18px)
- **Auto updates** – one-click updates via GitHub releases
- **Clean uninstall** – removes all options and post meta on deletion

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Gutenberg editor (block editor)

## Installation

1. Download the latest ZIP from [GitHub Releases](https://github.com/DatabenderSK/zuno-toc/releases)
2. WordPress admin → Plugins → Upload Plugin → select ZIP
3. Activate the plugin
4. The plugin automatically inserts TOC into posts with 3+ headings

Updates appear directly in the WordPress dashboard.

## Settings

### Global settings

**Settings → Zuno TOC** – configure defaults for all TOC instances:

| Setting | Description | Default |
|---------|-------------|---------|
| Post types | Which post types show TOC | Posts |
| Minimum headings | Minimum headings to display TOC | 3 |
| Heading levels | Which heading levels to include | H2, H3 |
| Default style | Visual style (Minimal/Rounded/Dark) | Minimal |
| TOC title | Title displayed above the list | Obsah článku |
| Auto-insert | Auto-insert TOC when no block is placed | On |
| Show toggle | Show/Hide collapse button | On |
| Numbering | Use numbered list instead of bullets | Off |
| Smooth scroll | Smooth scrolling to headings | On |
| Default collapsed | Start with TOC collapsed | Off |
| Accent color | Hex color for accent elements | #5ba462 |
| Font size | Base font size for TOC items | 16px |
| Admin bar | Hide Zuno from the admin bar | Off |

### Per-block settings

Click the TOC block in the editor → Sidebar panel:

- Style override (Minimal / Rounded / Dark)
- List style (Default / Bullets / Numbers)
- Toggle visibility
- Collapsed state
- Accent color
- Font size
- Strip number prefixes
- Heading exclusion (eye icon per heading)
- Custom label text
- Custom anchor URL

### Per-post settings

Document sidebar → **Zuno TOC** panel:

- Disable TOC for this post
- Override heading levels (H2/H3/H4)

## Block category

The plugin registers a **Zuno** block category in the Gutenberg editor. All Zuno plugins share this category for easy discovery.

## Admin bar

A **Zuno** dropdown in the admin bar provides quick access to settings. It dynamically positions itself as the second-to-last item. Can be hidden via Settings → Zuno TOC → "Hide Zuno from admin bar".

## File structure

```
zuno-toc/
├── zuno-toc.php              # Main plugin file, bootstraps everything
├── block.json                # Gutenberg block registration
├── readme.txt                # WordPress.org readme
├── uninstall.php             # Clean removal of options and meta
├── includes/
│   ├── class-plugin.php          # Block registration, category, init
│   ├── class-heading-parser.php  # Heading parsing + ID injection
│   ├── class-block-renderer.php  # Server-side TOC rendering
│   ├── class-auto-insert.php     # Auto-insert into content
│   ├── class-settings.php        # Settings page + sanitization
│   ├── class-post-meta.php       # Post meta registration
│   ├── class-admin-column.php    # TOC status column in post list
│   ├── class-github-updater.php  # GitHub-based auto-updater
│   └── class-zuno-admin-menu.php # Admin bar Zuno dropdown
├── src/
│   ├── index.js              # Block editor JavaScript
│   ├── index.asset.php       # WP dependencies manifest
│   ├── editor.css            # Editor-only styles
│   └── style.css             # Frontend + editor styles
└── assets/
    └── frontend.js           # Frontend toggle + smooth scroll
```

## Security

- All user inputs sanitized (`sanitize_text_field`, `sanitize_key`, `sanitize_title`)
- All outputs escaped (`esc_html`, `esc_attr`, `esc_url`)
- Block attributes validated server-side (color regex, size allowlist)
- Settings form protected with WordPress nonces (CSRF)
- Capability checks on all admin actions (`manage_options`, `edit_posts`, `update_plugins`)
- GitHub updater validates URL host before downloading
- Post meta protected with `auth_callback`
- Direct file access blocked with `defined('ABSPATH') || exit`
- Clean uninstall removes all stored data

## Development

The plugin uses vanilla JavaScript (no build step required). Edit files directly:

- **Editor JS**: `src/index.js` – Gutenberg block registration, edit component, sidebar panels
- **Frontend JS**: `assets/frontend.js` – toggle and smooth scroll handlers
- **Styles**: `src/style.css` (frontend) and `src/editor.css` (editor-only)

### CSS architecture

- BEM naming: `.zuno-toc`, `.zuno-toc__header`, `.zuno-toc__link`, etc.
- CSS custom properties: `--zuno-toc-accent`, `--zuno-toc-font-size`
- Style variants: `.zuno-toc--minimal`, `.zuno-toc--rounded`, `.zuno-toc--dark`

### PHP architecture

- Namespace: `ZUNO_TOC`
- Option key: `zuno_toc_settings`
- Post meta: `_zuno_toc_disabled`, `_zuno_toc_heading_levels`
- Block name: `zuno/toc`

## License

GPLv2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

## Author

[Martin Pavlic](https://martinpavlic.sk)

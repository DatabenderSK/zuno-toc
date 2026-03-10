<?php
/**
 * Plugin Name: Zuno TOC – Table of Contents
 * Description: Gutenberg blok pre obsah článku s live náhľadom, toggle funkciou a auto-insertom.
 * Version: 1.0.7
 * Author: Martin Pavlič
 * Text Domain: zuno-toc
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Update URI: https://github.com/DatabenderSK/zuno-toc
 */

defined( 'ABSPATH' ) || exit;

define( 'ZUNO_TOC_VERSION', '1.0.7' );
define( 'ZUNO_TOC_FILE', __FILE__ );
define( 'ZUNO_TOC_DIR', plugin_dir_path( __FILE__ ) );
define( 'ZUNO_TOC_URL', plugin_dir_url( __FILE__ ) );

require_once ZUNO_TOC_DIR . 'includes/class-plugin.php';
require_once ZUNO_TOC_DIR . 'includes/class-heading-parser.php';
require_once ZUNO_TOC_DIR . 'includes/class-post-meta.php';
require_once ZUNO_TOC_DIR . 'includes/class-block-renderer.php';
require_once ZUNO_TOC_DIR . 'includes/class-auto-insert.php';
require_once ZUNO_TOC_DIR . 'includes/class-settings.php';
require_once ZUNO_TOC_DIR . 'includes/class-admin-column.php';
require_once ZUNO_TOC_DIR . 'includes/class-github-updater.php';
require_once ZUNO_TOC_DIR . 'includes/class-zuno-admin-menu.php';

( new ZUNO_TOC\Plugin() )->run();
( new ZUNO_TOC\GitHub_Updater( ZUNO_TOC_FILE, ZUNO_TOC_VERSION ) )->init();
( new ZUNO_TOC\Zuno_Admin_Menu() )->init();

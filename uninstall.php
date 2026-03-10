<?php
/**
 * Cleanup on plugin deletion.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Remove plugin options.
delete_option( 'zuno_toc_settings' );

// Remove post meta.
delete_post_meta_by_key( '_zuno_toc_disabled' );
delete_post_meta_by_key( '_zuno_toc_heading_levels' );

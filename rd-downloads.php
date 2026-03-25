<?php
/**
 * Backwards compatibility loader for rundiz-downloads.
 * This file is only for users upgrading from the old main file name.
 * It will run once and then never again.
 * 
 * @todo[rundiz] delete this file after v1.2+
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


$rundiz_downloads_old_file = 'rd-downloads.php';
$rundiz_downloads_new_file = 'rundiz-downloads.php';

// Update active_plugins option so WP uses the new file going forward.
$rundiz_downloads_active_plugins = (array) get_option( 'active_plugins', array() );

$rundiz_downloads_old_path = plugin_basename( __DIR__ . '/' . $rundiz_downloads_old_file );
$rundiz_downloads_active_plugins = array_diff( $rundiz_downloads_active_plugins, array( $rundiz_downloads_old_path ) );

$rundiz_downloads_new_path = plugin_basename( __DIR__ . '/' . $rundiz_downloads_new_file );
if ( ! in_array( $rundiz_downloads_new_path, $rundiz_downloads_active_plugins ) ) {
    $rundiz_downloads_active_plugins[] = $rundiz_downloads_new_path;
    // Load the real plugin immediately so there's no gap.
    include_once __DIR__ . '/' . $rundiz_downloads_new_file;
}

update_option( 'active_plugins', $rundiz_downloads_active_plugins );

unset($rundiz_downloads_active_plugins, $rundiz_downloads_new_file, $rundiz_downloads_new_path);
unset($rundiz_downloads_old_file, $rundiz_downloads_old_path);

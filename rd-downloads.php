<?php
/**
 * Backwards compatibility loader for rundiz-downloads.
 * This file is only for users upgrading from the old main file name.
 * It will run once and then never again.
 * 
 * @package rundiz-downloads
 * @todo[rundiz] delete this file after v1.2+
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


$rundiz_downloads_old_file = 'rd-downloads.php';
$rundiz_downloads_new_file = 'rundiz-downloads.php';

// Update active_plugins option so WP uses the new file going forward.
$rundiz_downloads_active_plugins = (array) get_option('active_plugins', []);

$rundiz_downloads_old_path = plugin_basename(__DIR__ . '/' . $rundiz_downloads_old_file);
$rundiz_downloads_active_plugins = array_diff($rundiz_downloads_active_plugins, [$rundiz_downloads_old_path]);

$rundiz_downloads_new_path = plugin_basename(__DIR__ . '/' . $rundiz_downloads_new_file);
if (! in_array($rundiz_downloads_new_path, $rundiz_downloads_active_plugins)) {
    $rundiz_downloads_active_plugins[] = $rundiz_downloads_new_path;
    // Load the real plugin immediately so there's no gap.
    include_once __DIR__ . '/' . $rundiz_downloads_new_file;
}

update_option('active_plugins', $rundiz_downloads_active_plugins);

unset($rundiz_downloads_active_plugins, $rundiz_downloads_new_file, $rundiz_downloads_new_path);
unset($rundiz_downloads_old_file, $rundiz_downloads_old_path);


// migrate old option prefix to new one. ----------------------------------------------------------------------
if (!function_exists('rundiz_downloads_migrate_old_prefix')) {
    function rundiz_downloads_migrate_old_prefix() {
        $old_version_option_name = 'rd_downloads_options';// this option name will not be renamed.
        $pre1_0_18_options = get_option($old_version_option_name);// this option name will not be renamed.
        if (
            (
                is_string($pre1_0_18_options) && 
                '' !== $pre1_0_18_options
            ) ||
            (
                is_array($pre1_0_18_options) &&
                !empty($pre1_0_18_options)
            )
        ) {
            // if there is an option from previous version that use wrong name.
            // in older versions this plugin was use option name prefix with `rundizoauth_` which is not match with plugin slug.
            // move them to new option name that match plugin slug.
            update_option('rundiz_downloads_options', $pre1_0_18_options, false);
            // delete previous old option name.
            delete_option($old_version_option_name);// this option name will not be renamed.
        }
        unset($old_version_option_name, $pre1_0_18_options);
    }// rundiz_downloads_migrate_old_prefix
}// endif;
add_action('plugins_loaded', 'rundiz_downloads_migrate_old_prefix');

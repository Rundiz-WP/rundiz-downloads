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
    function rundiz_downloads_migrate_old_prefix() 
    {
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


// migrate upload folder to new one. -----------------------------------------------------------------------------
if (!function_exists('rundiz_downloads_migrate_upload_folder_replace_db_values')) {
    function rundiz_downloads_migrate_upload_folder_replace_db_values(\wpdb $wpdb, $old_folder, $old_url, $new_folder, $new_url)
    {
        $old_table = $wpdb->prefix . 'rd_downloads';
        $new_table = $wpdb->prefix . 'rundiz_downloads';

        $table_to_use = null;
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $new_table)) === $new_table) {
            $table_to_use = $new_table;
        } elseif ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $old_table)) === $old_table) {
            $table_to_use = $old_table;
        }

        if ($table_to_use) {
            $path_queries = array(
                "UPDATE `{$table_to_use}` SET `download_url` = REPLACE(`download_url`, %s, %s) WHERE `download_url` LIKE %s",
                "UPDATE `{$table_to_use}` SET `download_related_path` = REPLACE(`download_related_path`, %s, %s) WHERE `download_related_path` LIKE %s",
            );

            foreach ($path_queries as $sql) {
                $wpdb->query($wpdb->prepare($sql, $old_folder, $new_folder, '%' . $old_folder . '%'));
                $wpdb->query($wpdb->prepare($sql, $old_url,    $new_url,    '%' . $old_url . '%'));
            }

            error_log('Rundiz Downloads: Paths updated in table ' . $table_to_use);
        }
    }// rundiz_downloads_migrate_upload_folder_replace_db_values
}// endif;

if (!function_exists('rundiz_downloads_migrate_upload_folder')) {
    function rundiz_downloads_migrate_upload_folder()
    {
        $old_folder = 'rd-downloads';   // do not change this
        $new_folder = 'rundiz-downloads';

        // Safety flag (per-site)
        $already_migrated = get_option('rundiz_downloads_renamed_upload_folder_v1_0_18', false);

        if ($already_migrated) {
            return; // already done on this site
        }

        global $wpdb;
        $is_multisite = is_multisite();

        if ($is_multisite) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
            $original_blog_id = get_current_blog_id();
        } else {
            $blog_ids = [get_current_blog_id()];
        }

        foreach ($blog_ids as $blog_id) {
            if ($is_multisite) {
                switch_to_blog($blog_id);
            }

            // Re-calculate upload dir for THIS site (critical for multisite)
            $upload_dir = wp_upload_dir();
            $old_dir    = $upload_dir['basedir'] . '/' . $old_folder;
            $new_dir    = $upload_dir['basedir'] . '/' . $new_folder;
            $old_url    = $upload_dir['baseurl'] . '/' . $old_folder;
            $new_url    = $upload_dir['baseurl'] . '/' . $new_folder;

            // Only run if old folder still exists on this site
            if (is_dir($old_dir) && !is_dir($new_dir)) {

                // 1. Rename the physical folder for this site
                $renamed = false;
                if (rename($old_dir, $new_dir)) {
                    $renamed = true;
                    error_log("Rundiz Downloads: Upload folder renamed successfully (site {$blog_id}).");
                } else {
                    WP_Filesystem();
                    global $wp_filesystem;
                    if ($wp_filesystem && $wp_filesystem->move($old_dir, $new_dir)) {
                        $renamed = true;
                        error_log("Rundiz Downloads: Upload folder renamed using WP_Filesystem (site {$blog_id}).");
                    }
                }

                // 2. Update paths in DB for this site
                rundiz_downloads_migrate_upload_folder_replace_db_values($wpdb, $old_folder, $old_url, $new_folder, $new_url);

                error_log("Rundiz Downloads: Upload folder migration completed for site {$blog_id}.");
            }
        }// endforeach; $blog_ids
        unset($blog_id);

        // Restore original blog (multisite only)
        if ($is_multisite) {
            switch_to_blog($original_blog_id);
        }
        unset($blog_ids, $original_blog_id);

        // Mark this site as migrated (per-site flag)
        update_option('rundiz_downloads_renamed_upload_folder_v1_0_18', true);
    }// rundiz_downloads_migrate_upload_folder
}// endif;
add_action('plugins_loaded', 'rundiz_downloads_migrate_upload_folder');

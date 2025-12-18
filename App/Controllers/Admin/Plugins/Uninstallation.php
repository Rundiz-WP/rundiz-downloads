<?php
/**
 * Uninstall or delete the plugin.
 *
 * @package rd-downloads
 * phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
 */


namespace RdDownloads\App\Controllers\Admin\Plugins;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Plugins\\Uninstallation')) {
    class Uninstallation implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Get `main_option_name` property from trait which is non-static from any static method.
         *
         * @return string Return main option name of this plugin. See `main_option_name` property for more info.
         */
        private static function getMainOptionName()
        {
            $class = new self();
            return $class->main_option_name;
        }// getMainOptionName


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            global $wp_version;

            // register uninstall hook. MUST be static method or function.
            register_uninstall_hook(RDDOWNLOADS_FILE, ['\\RdDownloads\\App\\Controllers\\Admin\\Plugins\\Uninstallation', 'uninstall']);

            if (version_compare($wp_version, '5.1', '>=')) {
                add_action('wp_delete_site', [$this, 'uninstallDeleteSite']);
            } else {
                // Deprecated since 5.1
                add_action('deleted_blog', [$this, 'uninstallDeleteSite'], 10, 2);
            }
        }// registerHooks


        /**
         * Uninstall or delete the plugin.
         *
         * @global \wpdb $wpdb
         */
        public static function uninstall()
        {
            // do something that will be happens on delete plugin.
            global $wpdb;
            $wpdb->show_errors();

            // delete options.
            if (is_multisite()) {
                // this is multi site, delete options in all sites.
                $blog_ids = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
                $original_blog_id = get_current_blog_id();
                if ($blog_ids) {
                    foreach ($blog_ids as $blog_id) {
                        switch_to_blog($blog_id);
                        static::uninstallDeleteOption();
                        static::uninstallDropTables();
                    }
                }
                switch_to_blog($original_blog_id);
                unset($blog_id, $blog_ids, $original_blog_id);
            } else {
                // this is single site, delete options in single site.
                static::uninstallDeleteOption();
                static::uninstallDropTables();
            }

            static::uninstallDeleteUserOptions();
        }// uninstall


        /**
         * Delete option on the switched to site.
         *
         * Also clear scheduled hooks.
         */
        private static function uninstallDeleteOption()
        {
            delete_option(static::getMainOptionName());

            wp_clear_scheduled_hook('rddownloads_cron_purgelogs');
        }// uninstallDeleteOption


        /**
         * Drop tables on deleted site.
         *
         *  This method was called from hook, it must be public and do not call this directly.
         *
         * @link https://developer.wordpress.org/reference/hooks/deleted_blog/ Reference of hook `deleted_blog` which is deprecated in WP 5.1.
         * @link https://developer.wordpress.org/reference/hooks/wp_delete_site/ Reference of hook `wp_delete_site` for replacement.
         * @param int|\WP_Site $site_id The site ID or deleted site object on WP 5.1+.
         * @param bool $drop True if site’s tables should be dropped. Default false.
         */
        public function uninstallDeleteSite($site_id, $drop = false)
        {
            global $wp_version;
            if (is_a($site_id,'\WP_Site')) {
                $site_id_value = $site_id->blog_id;
                $site_id = -1;
                $site_id = $site_id_value;
                unset($site_id_value);
            }

            switch_to_blog($site_id);

            if (version_compare($wp_version, '5.1', '>=')) {
                // if WordPress version is 5.1 or newer.
                // since the certain version is deprecated hook `deleted_blog`. so, the argument `$drop` will be missing on new hook.  
                // the `$drop` should always be `true` on new version of WP.
                $drop = true;
            }

            if ($drop) {
                static::uninstallDropTables(false);
            }

            restore_current_blog();
        }// uninstallDeleteSite


        /**
         * Delete user's meta where it contain meta key created by this plugin.
         *
         * This including screen options, user options such as GitHub connect.
         *
         * @global \wpdb $wpdb
         */
        private static function uninstallDeleteUserOptions()
        {
            global $wpdb;

            $sql = 'DELETE FROM `' . $wpdb->usermeta . '` WHERE `meta_key` LIKE \'rddownloads_%\'';
            $wpdb->query($sql);
            unset($sql);
        }// uninstallDeleteUserOptions


        /**
         * Drop tables that was created with this plugin.
         *
         * Only tables that was created in `RdDownloads\App\Models\PluginDbStructure->get()` method will be drop here.
         *
         * @global \wpdb $wpdb
         * @param bool $mainsite Set to `true` to drop table of this plugin that created for main site. Otherwise it will be drop table with `prefix_sitenumber_` for switched to sub site only (in case multi-site enabled).
         */
        private static function uninstallDropTables($mainsite = true)
        {
            global $wpdb;
            $wpdb->show_errors();

            $PluginDbStructure = new \RdDownloads\App\Models\PluginDbStructure();
            $schemas = $PluginDbStructure->get();
            unset($PluginDbStructure);

            if (is_array($schemas) && !empty($schemas)) {
                foreach ($schemas as $index => $item) {
                    if (isset($item['tablename'])) {
                        if (isset($item['is_multisite']) && true === $item['is_multisite']) {
                            // if set to multisite table then it will be use prefix_sitenumber_tablename.
                            $prefix = $wpdb->prefix;
                        } else {
                            // if set not to multisite then it will be use prefix_tablename.
                            if (true === $mainsite) {
                                $prefix = $wpdb->base_prefix;
                            } else {
                                $prefix = $wpdb->prefix;
                            }
                        }

                        $sql = 'DROP TABLE IF EXISTS ' . $prefix . $item['tablename'];
                        $wpdb->query($sql);
                        unset($prefix, $sql);
                    }
                }// endforeach;
                unset($index, $item);
            }

            // remove all files & folders in upload folder. -----------------
            $wp_upload_dir = wp_upload_dir();
            if (is_array($wp_upload_dir) && array_key_exists('basedir', $wp_upload_dir)) {
                $target_dir = realpath($wp_upload_dir['basedir']) . DIRECTORY_SEPARATOR . 'rd-downloads';
                \RdDownloads\App\Libraries\FileSystem::rrmDir($target_dir, $wp_upload_dir['basedir']);
            }
            unset($target_dir, $wp_upload_dir);
        }// uninstallDropTables


    }
}

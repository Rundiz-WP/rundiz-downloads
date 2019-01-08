<?php
/**
 * Uninstall or delete the plugin.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Plugins;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Plugins\\Uninstallation')) {
    class Uninstallation implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Get `main_option_name` property from trait which is non-static from any static method.
         *
         * @return string
         */
        private static function getMainOptionName()
        {
            $class = new self;
            return $class->main_option_name;
        }// getMainOptionName


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register uninstall hook. MUST be static method or function.
            register_uninstall_hook(RDDOWNLOADS_FILE, ['\\RdDownloads\\App\\Controllers\\Admin\\Plugins\\Uninstallation', 'uninstall']);

            if (is_multisite()) {
                // hook on deleted site. This is required for when plugin was deactivated but not uninstall and site will be delete, it can clean up tables that created on certain sites.
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
                $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
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
         * @link https://developer.wordpress.org/reference/hooks/deleted_blog/ Reference.
         * @param integer $blog_id
         * @param boolean $drop
         */
        public function uninstallDeleteSite($blog_id, $drop)
        {
            switch_to_blog($blog_id);

            if ($drop) {
                static::uninstallDropTables(false);
            }

            restore_current_blog();
        }// uninstallDeleteSite


        /**
         * Delete user options values.
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
         * @param boolean $mainsite Set to true to drop table of this plugin that created for main site. Otherwise it will be drop table with `prefix_sitenumber_` for switched to sub site only (in case multisite enabled).
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
                        if (isset($item['is_multisite']) && $item['is_multisite'] === true) {
                            // if set to multisite table then it will be use prefix_sitenumber_tablename.
                            $prefix = $wpdb->prefix;
                        } else {
                            // if set not to multisite then it will be use prefix_tablename.
                            if ($mainsite === true) {
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
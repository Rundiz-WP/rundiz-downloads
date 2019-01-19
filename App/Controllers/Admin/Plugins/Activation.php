<?php
/**
 * Activate the plugin action.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin\Plugins;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Plugins\\Activation')) {
    class Activation implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Activate the plugin by admin on WP plugin page.
         * 
         * @global \wpdb $wpdb WordPress db class.
         * @param boolean $network_wide for multisite network activate check.
         */
        public function activate($network_wide)
        {
            // do something that will happens on activate plugin.
            $wordpress_required_version = '4.6.0';
            $phpversion_required = '5.5';
            if (function_exists('phpversion')) {
                $phpversion = phpversion();
            }
            if (!isset($phpversion) || (isset($phpversion) && $phpversion === false)) {
                if (defined('PHP_VERSION')) {
                    $phpversion = PHP_VERSION;
                } else {
                    // can't detect php version
                    $phpversion = '4';
                }
            }
            if (version_compare($phpversion, $phpversion_required, '<')) {
                /* translators: %1$s: Current PHP version, %2$s: Required PHP version. */
                wp_die(sprintf(__('You are using PHP %1$s which does not meet minimum requirement. Please consider upgrade PHP version or contact plugin author for this help.<br><br>Minimum requirement:<br>PHP %2$s', 'rd-downloads'), $phpversion, $phpversion_required), __('Minimum requirement of PHP version does not meet.', 'rd-downloads'));
                exit;
            }
            if (version_compare(get_bloginfo('version'), $wordpress_required_version, '<')) {
                /* translators: %1$s: Current WordPress version, %2$s: Required WordPress version. */
                wp_die(sprintf(__('Your WordPress version does not meet the requirement. (%1$s < %2$s).', 'rd-downloads'), get_bloginfo('version'), $wordpress_required_version));
                exit;
            }
            unset($phpversion, $phpversion_required, $wordpress_required_version);

            if (is_multisite() && $network_wide) {
                wp_die(__('Unable to network activate, please activate from each site that have to use it only.', 'rd-downloads'));
                exit;
            }

            // get wpdb global var.
            global $wpdb;
            $wpdb->show_errors();

            // add option to site or multisite -----------------------------
            if (is_multisite()) {
                // this site is multisite. add/update options, create/alter tables on all sites.
                $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
                $original_blog_id = get_current_blog_id();
                if ($blog_ids) {
                    foreach ($blog_ids as $blog_id) {
                        switch_to_blog($blog_id);
                        $this->activateCreateAlterTables();
                        $this->activateAddUpdateOption();
                    }
                }
                switch_to_blog($original_blog_id);
                unset($blog_id, $blog_ids, $original_blog_id);
            } else {
                // this site is single site. add/update options, create/alter tables on current site.
                $this->activateCreateAlterTables();
                $this->activateAddUpdateOption();
            }

            // create folder in upload folder.
            $wp_upload_dir = wp_upload_dir();
            if (is_array($wp_upload_dir) && array_key_exists('basedir', $wp_upload_dir)) {
                wp_mkdir_p(realpath($wp_upload_dir['basedir']) . DIRECTORY_SEPARATOR . 'rd-downloads');
                $FileSystem = new \RdDownloads\App\Libraries\FileSystem();
                $FileSystem->writeFile(realpath($wp_upload_dir['basedir']) . DIRECTORY_SEPARATOR . 'rd-downloads' . DIRECTORY_SEPARATOR . 'index.html', 'Access denied!', false);
                unset($FileSystem);
            }
            unset($wp_upload_dir);
        }// activate


        /**
         * Check if the options was added before or not, if not then add the options otherwise update them.
         */
        private function activateAddUpdateOption()
        {
            // check current option exists or not.
            $current_options = get_option($this->main_option_name);

            if ($current_options === false) {
                // if this is newly activate. it is never activated before, add the options.
                $this->setupAllOptions();
                $this->saveOptions($this->all_options);
            } elseif (
                is_array($current_options) &&
                array_key_exists('rdsfw_plugin_db_version', $current_options) &&
                version_compare($current_options['rdsfw_plugin_db_version'], $this->db_version, '<')
            ) {
                // if there is db updated. it is just updated because `activateCreateAlterTables()` that is using `dbDelta()` were called before this method.
                // the table structure should already get updated by this.
                // save the new db version.
                $current_options['rdsfw_plugin_db_version'] = $this->db_version;
                $this->saveOptions($current_options);
            }

            unset($current_options);
        }// activateAddUpdateOption


        /**
         * Add/update options and create/alter tables on new site created.
         * 
         * This method was called from hook, it must be public and do not call this directly.
         * On site created, it will be add or update options and create or alter tables even this plugin is not activated on the new site or not network activate.
         * This will be fine because on delete site or plugin, these options and tables will be removed via `Uninstallation` class.
         * 
         * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wpmu_new_blog Reference.
         * @param integer $blog_id
         * @param integer $user_id
         * @param string $domain
         * @param string $path
         * @param integer $site_id
         * @param array $meta
         */
        public function activateNewSite($blog_id, $user_id, $domain, $path, $site_id, $meta)
        {
            switch_to_blog($blog_id);

            $this->activateCreateAlterTables();
            $this->activateAddUpdateOption();

            restore_current_blog();
        }// activateNewSite


        /**
         * If there is at least one or more table from `RdDownloads\App\Models\PluginDbStructure->get()` method then create or alter using WP db delta.
         * 
         * @global \wpdb $wpdb WordPress db class.
         */
        private function activateCreateAlterTables()
        {
            $PluginDbStructure = new \RdDownloads\App\Models\PluginDbStructure();
            $schemas = $PluginDbStructure->get();
            unset($PluginDbStructure);

            if (is_array($schemas) && !empty($schemas) && !is_null($this->getDbVersion())) {
                global $wpdb;
                // require file that needs for use dbDelta() function.
                require_once ABSPATH . '/wp-admin/includes/upgrade.php';

                foreach ($schemas as $index => $item) {
                    if (isset($item['statement']) && isset($item['tablename'])) {
                        $sql = str_replace('%TABLE%', $item['tablename'], $item['statement']);

                        if (isset($item['is_multisite']) && $item['is_multisite'] === true) {
                            // if set to multisite table then it will create prefix_sitenumber_tablename.
                            $prefix = $wpdb->prefix;
                        } else {
                            // if set not to multisite then it will create prefix_tablename.
                            $prefix = $wpdb->base_prefix;
                        }

                        $sql = str_replace('%PREFIX%', $prefix, $sql);
                        dbDelta($sql);
                        unset($sql);

                        if (function_exists('maybe_convert_table_to_utf8mb4')) {
                            maybe_convert_table_to_utf8mb4($prefix . $item['tablename']);
                        }
                        unset($prefix);
                    }
                }// endforeach;
                unset($index, $item);
            }

            unset($schemas);
        }//activateCreateAlterTables


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register activate hook
            register_activation_hook(RDDOWNLOADS_FILE, [$this, 'activate']);

            if (is_multisite()) {
                // hook on create new site (for multisite installation).
                //add_action('wpmu_new_blog', [$this, 'activateNewSite'], 10, 6);// comment this line because we don't want it to create table on create new site. just create table on activate plugin on certain site only.
            }
        }// registerHooks


    }
}
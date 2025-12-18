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
         * @link https://developer.wordpress.org/reference/functions/register_activation_hook/ The function `register_activation_hook()` reference.
         * @link https://developer.wordpress.org/reference/hooks/activate_plugin/ The reference about what will be pass to callback of function `register_activation_hook()`.
         * @global \wpdb $wpdb WordPress DB class.
         * @param bool $network_wide Whether to enable the plugin for all sites in the network or just the current site. Multisite only. Default false.
         */
        public function activate($network_wide)
        {
            // do something that will happens on activate plugin.
            $wordpress_required_version = '4.6.0';
            $phpversion_required = '5.5';
            if (function_exists('phpversion')) {
                $phpversion = phpversion();
            }
            if (!isset($phpversion) || (isset($phpversion) && false === $phpversion)) {
                if (defined('PHP_VERSION')) {
                    $phpversion = PHP_VERSION;
                } else {
                    // can't detect php version
                    $phpversion = '4';
                }
            }
            if (version_compare($phpversion, $phpversion_required, '<')) {
                wp_die(
                    sprintf(
                        /* translators: %1$s: Current PHP version, %2$s: Required PHP version. */
                        __('You are using PHP %1$s which does not meet minimum requirement. Please consider upgrade PHP version or contact plugin author for this help.<br><br>Minimum requirement:<br>PHP %2$s', 'rd-downloads'),// phpcs:ignore 
                        $phpversion,// phpcs:ignore 
                        $phpversion_required// phpcs:ignore
                    ), 
                    esc_html__('Minimum requirement of PHP version does not meet.', 'rd-downloads')
                );
                exit;
            }
            if (version_compare(get_bloginfo('version'), $wordpress_required_version, '<')) {
                wp_die(
                    sprintf(
                        /* translators: %1$s: Current WordPress version, %2$s: Required WordPress version. */
                        esc_html__('Your WordPress version does not meet the requirement. (%1$s < %2$s).', 'rd-downloads'), 
                        get_bloginfo('version'),// phpcs:ignore 
                        $wordpress_required_version// phpcs:ignore
                    )
                );
                exit;
            }
            unset($phpversion, $phpversion_required, $wordpress_required_version);

            if (is_multisite() && $network_wide) {
                wp_die(esc_html__('Unable to network activate, please activate from each site that have to use it only.', 'rd-downloads'));
                exit(1);
            }

            // Get `$wpdb` global var.
            global $wpdb;
            $wpdb->show_errors();

            // Add option to site or multisite -----------------------------
            // Due to this plugin did not allowed network wide activate, any activation process must go on per-site only.
            $this->activateCreateAlterTables();
            $this->activateAddUpdateOption();

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

            if (false === $current_options) {
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
         * If there is at least one or more table from `RdDownloads\App\Models\PluginDbStructure->get()` method then create or alter using WordPress's `dbDelta()`.
         * 
         * @global \wpdb $wpdb WordPress DB class.
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

                        if (isset($item['is_multisite']) && true === $item['is_multisite']) {
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
                            //maybe_convert_table_to_utf8mb4($prefix . $item['tablename']);
                            $this->tempFixMaybeConvertTableToUtf8mb4($prefix . $item['tablename']);
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
        }// registerHooks


        /**
         * Temporary fix of function `maybe_convert_table_to_utf8mb4()`.
         * 
         * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
         * phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
         * 
         * @see maybe_convert_table_to_utf8mb4()
         * @link https://core.trac.wordpress.org/ticket/60002 Bug tracker
         * @since 1.0.16
         * @global \wpdb $wpdb
         * @param string $table The DB table to fix.
         * @return bool Return `true` on success, `false` for otherwise.
         */
        private function tempFixMaybeConvertTableToUtf8mb4($table)
        {
            if (!is_string($table)) {
                return false;
            }

            global $wpdb;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $results = $wpdb->get_results("SHOW FULL COLUMNS FROM `$table`");
            if (!$results) {
                return false;
            }

            foreach ($results as $column) {
                if ($column->Collation) {
                    list( $charset ) = explode('_', $column->Collation);
                    $charset = strtolower($charset);
                    if ('utf8' !== $charset && 'utf8mb3' !== $charset && 'utf8mb4' !== $charset) {
                        // Don't upgrade tables that have non-utf8 columns.
                        return false;
                    }
                }
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $table_details = $wpdb->get_row("SHOW TABLE STATUS LIKE '$table'");
            if (!$table_details) {
                return false;
            }

            list( $table_charset ) = explode('_', $table_details->Collation);
            $table_charset = strtolower($table_charset);
            if ('utf8mb4' === $table_charset) {
                return true;
            }

            // custom code that upgrade to best collate. ---------------------------------
            $table_charset = 'utf8mb4';
            $collate = 'utf8mb4_unicode_ci';
            $charset_collate = $wpdb->determine_charset($table_charset, $collate);
            $table_charset = $charset_collate['charset'];
            $collate = $charset_collate['collate'];
            unset($charset_collate);
            // end custom code that upgrade to best collate. ---------------------------------

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery
            return $wpdb->query("ALTER TABLE $table CONVERT TO CHARACTER SET $table_charset COLLATE $collate");
        }// tempFixMaybeConvertTableToUtf8mb4


    }
}

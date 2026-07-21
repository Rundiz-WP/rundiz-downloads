<?php
/**
 * Activate the plugin action.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Plugins;


if (!defined('ABSPATH')) {
    exit();
}


use RundizDownloads\App\Libraries\FileSystem;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Plugins\\Activation')) {
    /**
     * Plugin activation and new site activation hooks class.
     */
    class Activation implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        use \RundizDownloads\App\AppTrait;


        /**
         * All available options.
         * 
         * These options will be accessible via main option name variable.  
         * For example: options name `'the_name'` can call from `$plugin_template_optname['the_name'];`.  
         * (`$plugin_template_optname` will be set via the property's value in `AppTrait->main_option_name`.)  
         * If you want to access this property, please call to `setupAllOptions()` method first.
         * 
         * @since 2015-09-05 First was set in the `AppTrait`.
         * @since 2026-07-20 Moved from `AppTrait`.
         * @var array Set all options available for this plugin. it must be 2D array (`key => default value, key2 => default value, ...`)
         */
        private $all_options = [];


        /**
         * Activate the plugin by admin on WP plugin page.
         *
         * @link https://developer.wordpress.org/reference/functions/register_activation_hook/ The function `register_activation_hook()` reference.
         * @link https://developer.wordpress.org/reference/hooks/activate_plugin/ The reference about what will be pass to callback of function `register_activation_hook()`.
         * @global \wpdb $wpdb WordPress DB class.
         * @param bool $network_wide Whether to enable the plugin for all sites in the network or just the current site. Multisite only. Default false.
         * @throws \Exception Throw the exception if failed to detect current version of PHP.
         */
        public function activate($network_wide)
        {
            // Do something that will happens on activate plugin.
            $wordpress_required_version = '4.6.0';
            $phpversion_required = '5.5';
            if (function_exists('phpversion')) {
                $phpversion = phpversion();
            }
            if (!isset($phpversion) || (isset($phpversion) && false === $phpversion)) {
                if (defined('PHP_VERSION')) {
                    $phpversion = PHP_VERSION;
                } else {
                    // if there is no defined constant `PHP_VERSION`.
                    // @link https://www.php.net/ChangeLog-4.php Reference.
                    throw new \Exception('You are using ancient version of PHP. The constant `PHP_VERSION` is available since PHP 4.0.');
                }
            }
            if (version_compare($phpversion, $phpversion_required, '<')) {
                wp_die(
                    esc_html(
                        sprintf(
                            /* translators: %1$s current PHP version. */
                            __('You are using PHP %1$s which does not meet minimum requirement. Please consider upgrade PHP version or contact plugin author for this help.', 'rundiz-downloads'),
                            $phpversion
                        )
                    )
                    . '<br><br>'
                    . esc_html(
                        sprintf(
                            /* translators: %1$s minimum PHP version required. */
                            __('Minimum PHP requirement: %1$s.', 'rundiz-downloads'),
                            $phpversion_required
                        )
                    ), 
                    esc_html__('Minimum requirement of PHP version does not meet.', 'rundiz-downloads')
                );
                exit(1);
            }// endif;
            if (version_compare(get_bloginfo('version'), $wordpress_required_version, '<')) {
                wp_die(
                    esc_html(
                        sprintf(
                            // translators: %1$s Current WordPress version, %2$s Required WordPress version.
                            __('Your WordPress version does not meet the requirement. (%1$s < %2$s).', 'rundiz-downloads'), 
                            get_bloginfo('version'),
                            $wordpress_required_version
                        )
                    ),
                    esc_html__('Minimum requirement of WordPress version does not meet.', 'rundiz-downloads')
                );
                exit(1);
            }// endif;
            unset($phpversion, $phpversion_required, $wordpress_required_version);

            if (is_multisite() && $network_wide) {
                wp_die(esc_html__('Unable to network activate, please activate per site only.', 'rundiz-downloads'));
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
                wp_mkdir_p(realpath($wp_upload_dir['basedir']) . DIRECTORY_SEPARATOR . FileSystem::UPLOAD_FOLDER_NAME);
                $FileSystem = new \RundizDownloads\App\Libraries\FileSystem();
                $FileSystem->writeFile(realpath($wp_upload_dir['basedir']) . DIRECTORY_SEPARATOR . FileSystem::UPLOAD_FOLDER_NAME . DIRECTORY_SEPARATOR . 'index.html', 'Access denied!', false);
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
                $this->saveOptions($this->getAllOptions());
            } elseif (
                is_array($current_options) &&
                (
                    !isset($current_options['rdsfw_plugin_db_version']) ||
                    (
                        array_key_exists('rdsfw_plugin_db_version', $current_options) &&
                        version_compare($current_options['rdsfw_plugin_db_version'], $this->db_version, '<')
                    )
                )
            ) {
                // if there is db updated. it is just updated because `activateCreateAlterTables()` that is using `dbDelta()` was called before this method.
                // the table structure should already get updated by this.
                // save the new db version.
                $current_options['rdsfw_plugin_db_version'] = $this->db_version;
                $this->saveOptions($current_options);
            }// endif;

            unset($current_options);
        }// activateAddUpdateOption


        /**
         * If there is at least one or more table from `RundizDownloads\App\Models\PluginDbStructure->get()` method then create or alter using WordPress's `dbDelta()`.
         *
         * @global \wpdb $wpdb WordPress DB class.
         */
        private function activateCreateAlterTables()
        {
            $PluginDbStructure = new \RundizDownloads\App\Models\PluginDbStructure();
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
                            $this->hotFixMaybeConvertTableToUTF8Mb4($prefix . $item['tablename']);
                        }
                        unset($prefix);
                    }
                }// endforeach;
                unset($index, $item);
            }

            unset($schemas);
        }//activateCreateAlterTables


        /**
         * Get value of `all_options` property. The value of this property is from settings config file, not from DB.
         * 
         * Also setup if it was not set before.
         * 
         * This method visibility is `protected` to let tests class extend and use it.
         * 
         * @since 2026-07-20
         * @return array Return array value of `all_options` property.
         */
        protected function getAllOptions()
        {
            if (!is_array($this->all_options) || empty($this->all_options)) {
                $this->setupAllOptions();
            }

            return $this->all_options;
        }// getAllOptions


        /**
         * Hot fix for function `maybe_convert_table_to_utf8mb4()`.
         * 
         * @link https://core.trac.wordpress.org/ticket/60002 Bug tracker about this problem.
         * @since 1.0.16
         * @since 1.1.6 Renamed from `tempFixMaybeConvertTableToUtf8mb4()`.
         * @global \wpdb $wpdb
         * @param string $table The table to convert.
         * @return bool True if the table was converted, false if it wasn't.
         */
        private function hotFixMaybeConvertTableToUTF8Mb4($table)
        {
            global $wpdb;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $results = $wpdb->get_results( "SHOW FULL COLUMNS FROM `$table`" );
            if ( ! $results ) {
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

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $table_details = $wpdb->get_row( "SHOW TABLE STATUS LIKE '$table'" );
            if ( ! $table_details ) {
                return false;
            }

            list( $table_charset ) = explode( '_', $table_details->Collation );
            $table_charset         = strtolower( $table_charset );
            if ( 'utf8mb4' === $table_charset ) {
                return true;
            }

            // the code above has been copied from original function.

            // custom code that upgrade to best collate. ---------------------------------
            $table_charset = 'utf8mb4';
            $collate = 'utf8mb4_unicode_ci';
            $charset_collate = $wpdb->determine_charset($table_charset, $collate);
            $table_charset = $charset_collate['charset'];
            $collate = $charset_collate['collate'];
            unset($charset_collate);
            // end custom code that upgrade to best collate. ---------------------------------

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            return $wpdb->query("ALTER TABLE $table CONVERT TO CHARACTER SET $table_charset COLLATE $collate");
        }// hotFixMaybeConvertTableToUTF8Mb4


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register activate hook
            register_activation_hook(RUNDIZDOWNLOADS_FILE, [$this, 'activate']);
        }// registerHooks


        /**
         * Setup all options from settings config file.
         * 
         * This will be set all config settings into `all_options` property.  
         * You have to call this method if you want to call to `all_options` property.
         * 
         * This method will not load saved settings data from DB. The value in settings fields are all default value.
         * 
         * This method was called from `getAllOptions()`.
         * 
         * @since 2015-09-05 First was set in the `AppTrait`.
         * @since 2026-07-20 Moved from `AppTrait`.
         */
        private function setupAllOptions()
        {
            // load config values to get settings config file.
            $config_values = $this->getLoader()->loadConfig();
            if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                // if there is config value about config file.
                $settings_config_file = $config_values['rundiz_settings_config_file'];
            } else {
                // if there is no config value about config file.
                wp_die(
                    esc_html__('Settings configuration file was not set.', 'plugin-template')
                );
                exit(1);
            }
            unset($config_values);

            $RundizSettings = new \PluginTemplate\App\Libraries\RundizSettings();
            $RundizSettings->settings_config_file = $settings_config_file;
            $this->all_options = $RundizSettings->getSettingsFieldsId();
            unset($RundizSettings, $settings_config_file);

            // add db version into config value.
            if (is_array($this->all_options)) {
                if (!array_key_exists('rdsfw_plugin_db_version', $this->all_options) && !is_null($this->getDbVersion())) {
                    $this->all_options = array_merge($this->all_options, ['rdsfw_plugin_db_version' => $this->db_version]);
                }
                if (!array_key_exists('rdsfw_manual_update_version', $this->all_options)) {
                    $this->all_options = array_merge($this->all_options, ['rdsfw_manual_update_version' => '']);
                }
            }
        }// setupAllOptions


    }// Activation
}

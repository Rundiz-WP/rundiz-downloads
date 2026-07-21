<?php
/**
 * Main app trait for common works.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App;


if (!trait_exists('\\RundizDownloads\\App\\AppTrait')) {
    /**
     * Main application trait.
     */
    trait AppTrait
    {


        /**
         * @var \RundizDownloads\App\Libraries\Loader The loader class if it has been initiated. Make sure that this property must be set before use.
         */
        protected $Loader = null;


        /**
         * Main option name.
         * 
         * @var string Set main option name of this plugin. the name should be english, number, underscore, 
         *              or any characters that can be set to variable. 
         *              For example: `'rundiz_downloads_options'` will be set to `$rundiz_downloads_options`
         * @uses Call the trait method `getOptions();` before access `$rundiz_downloads_options` in global variable.
         */
        public $main_option_name = 'rundiz_downloads_options';


        /**
         * The database version.
         * 
         * If you have no tables to create on activate this plugin or don't use db for this plugin at all then set this to `NULL`.
         * If you have tables to create on activate this plugin then set the db version number (string) here and then write create table schema at the class & method `\RundizDownloads\App\Models\PluginDbStructure->get()`.
         * Do not access this property directly if not necessary, use `getDbVersion()` method instead.
         * 
         * @var string|null Version number of DB structure.
         * @todo [rundiz][routine] Read the description above and only set this if there is any tables to create on activate this plugin.
         */
        protected $db_version = '0.3';


        /**
         * Get the DB version of this plugin.
         * 
         * @return string|null Return `null` if the `db_version` property is not set or not using db for this plugin, return the db version number if set.
         */
        public function getDbVersion()
        {
            if (property_exists($this, 'db_version') && !is_null($this->db_version) && is_scalar($this->db_version)) {
                return strval($this->db_version);
            } else {
                return null;
            }
        }// getDbVersion


        /**
         * Get `Loader` object from `Loader` property.
         * 
         * This method is in main AppTrait.
         *
         * @return \RundizDownloads\App\Libraries\Loader Return the `Loader` object.
         */
        protected function getLoader()
        {
            if (!$this->Loader instanceof \RundizDownloads\App\Libraries\Loader) {
                $this->Loader = new \RundizDownloads\App\Libraries\Loader();
            }
            return $this->Loader;
        }// getLoader


        /**
         * Get all options of this plugin from DB.
         * 
         * This method is in main AppTrait.
         * 
         * @param array $options The method options:  
         *      `process_display_cb` (bool) Set to `true` to process the option `display_callback`. Set to `false` to skip it. Default is `true`.  
         *          This is in some cases, the class may call this method from inside `__construct()` unavoidable. 
         *          It may cause translation function trigger error calling it too early. Set to `false` will not process it, but it can be done manually later.  
         * @return array Return associative array value of all options where the key is option name.
         */
        public function getOptions(array $options = [])
        {
            $option_name = $this->main_option_name;
            global ${$option_name};// phpcs:ignore PHPCompatibility.Variables.ForbiddenGlobalVariableVariable.NonBareVariableFound
            ${$option_name} = [];// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

            $get_option = get_option($option_name);
            if (false !== $get_option) {
                // if option has value.
                // `get_option()` already unserializes internally - no need to re-run `maybe_unserialize()`.
                if (is_string($get_option)) {
                    // if older version of this plugin may still use manual serialize/unserialize.
                    // @todo[rundiz] delete this `if` block on version 2.0+
                    $get_option = maybe_unserialize($get_option);
                    if (!is_array($get_option)) {
                        $get_option = [];
                    }
                }

                if (!isset($options['process_display_cb']) || true === $options['process_display_cb']) {
                    // if there is option `process_display_cb` was set to `true` or default (unset).
                    // process data before use with `display_callback` option. -----------------------------
                    $config_values = $this->getLoader()->loadConfig();
                    $settings_config_file = '';
                    if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                        // if there is config value about config file.
                        $settings_config_file = $config_values['rundiz_settings_config_file'];
                    }
                    unset($config_values);

                    $RundizSettings = new \RundizDownloads\App\Libraries\RundizSettings();
                    $RundizSettings->settings_config_file = $settings_config_file;
                    $get_option = $RundizSettings->processDisplayCallback($get_option);
                    unset($RundizSettings, $settings_config_file);
                    // end process data before use with `display_callback` option. -------------------------
                }// endif; $options['process_display_cb']

                ${$option_name} = (array) $get_option;// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            }

            unset($get_option);
            return ${$option_name};// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        }// getOptions


        /**
         * Save the settings from settings page, using Rundiz settings.
         * 
         * This method is in main AppTrait.
         * 
         * @param array $data The associative array of submitted data in key => value
         * @return bool Return `true` if saved successfully. return `false` if not updated.
         */
        public function saveOptions(array $data)
        {
            $data = stripslashes_deep($data);

            // process data before save with `save_callback` option. -----------------------------
            $config_values = $this->getLoader()->loadConfig();
            $settings_config_file = '';
            if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                // if there is config value about config file.
                $settings_config_file = $config_values['rundiz_settings_config_file'];
            }
            unset($config_values);

            $RundizSettings = new \RundizDownloads\App\Libraries\RundizSettings();
            $RundizSettings->settings_config_file = $settings_config_file;
            $data = $RundizSettings->processSaveCallback($data);
            unset($RundizSettings, $settings_config_file);
            // end process data before save with `save_callback` option. -------------------------

            // add db version into config value.
            if (!array_key_exists('rdsfw_plugin_db_version', $data) && !is_null($this->getDbVersion())) {
                $currentConfigValues = $this->getOptions();
                if (is_array($currentConfigValues) && array_key_exists('rdsfw_plugin_db_version', $currentConfigValues)) {
                    $db_version = $currentConfigValues['rdsfw_plugin_db_version'];
                } else {
                    $db_version = $this->db_version;
                }
                unset($currentConfigValues);
                $data = array_merge($data, ['rdsfw_plugin_db_version' => $db_version]);
            }

            // add manual update version into config value.
            if (!array_key_exists('rdsfw_manual_update_version', $data)) {
                $currentConfigValues = $this->getOptions();
                if (is_array($currentConfigValues) && array_key_exists('rdsfw_manual_update_version', $currentConfigValues)) {
                    $manual_update_version = $currentConfigValues['rdsfw_manual_update_version'];
                } else {
                    $manual_update_version = '';
                }
                unset($currentConfigValues);
                $data = array_merge($data, ['rdsfw_manual_update_version' => $manual_update_version]);
            }

            return update_option($this->main_option_name, $data, false);
        }// saveOptions


        /**
         * Set `Loader` object to `Loader` property.
         * 
         * This method is in main AppTrait.
         *
         * @param \RundizDownloads\App\Libraries\Loader $Loader The `Loader` object.
         */
        public function setLoader(\RundizDownloads\App\Libraries\Loader $Loader)
        {
            $this->Loader = $Loader;
        }// setLoader


    }// AppTrait
}

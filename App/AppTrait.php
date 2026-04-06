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
         * Main option name.
         * 
         * @var string Set main option name of this plugin. the name should be english, number, underscore, 
         *              or any characters that can be set to variable. 
         *              For example: `'rundiz_downloads_options'` will be set to `$rundiz_downloads_options`
         * @uses Call the trait method `getOptions();` before access `$rundiz_downloads_options` in global variable.
         */
        public $main_option_name = 'rundiz_downloads_options';


        /**
         * All available options.
         * 
         * These options will be accessible via main option name variable. 
         * For example: options name `'the_name'` can call from `$rundiz_downloads_options['the_name'];`.
         * If you want to access this property, please call to `setupAllOptions()` method first.
         * 
         * @var array Set all options available for this plugin. it must be 2D array (`key => default value, key2 => default value, ...`)
         */
        public $all_options = [];


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
         * Get all options of this plugin.
         * 
         * @return array Return associative array value of all options where the key is option name.
         */
        public function getOptions()
        {
            $option_name = $this->main_option_name;
            global ${$option_name};// phpcs:ignore PHPCompatibility.Variables.ForbiddenGlobalVariableVariable.NonBareVariableFound
            ${$option_name} = [];// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

            $get_option = get_option($option_name);
            if (false !== $get_option) {
                // if option has value.
                ${$option_name} = maybe_unserialize($get_option);// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                unset($get_option);
                return (array) ${$option_name};
            }

            unset($get_option);
            return [];
        }// getOptions


        /**
         * Save the settings from settings page, using Rundiz settings.
         * 
         * @param array $data The associative array of submitted data in key => value
         * @return bool Return `true` if saved successfully. return `false` if not updated.
         */
        public function saveOptions(array $data)
        {
            $data = stripslashes_deep($data);

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

            return update_option($this->main_option_name, $data);
        }// saveOptions


        /**
         * Setup all options from settings config file.
         * 
         * This will be set all config settings into `all_options` property.
         * You have to call this method if you want to call to `all_options` property.
         * 
         * This method will not load saved settings data from DB. The value in settings fields are all default value.
         */
        public function setupAllOptions()
        {
            // load config values to get settings config file.
            $loader = new \RundizDownloads\App\Libraries\Loader();
            $config_values = $loader->loadConfig();
            if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                // if there is config value about config file.
                $settings_config_file = $config_values['rundiz_settings_config_file'];
            } else {
                // if there is no config value about config file.
                wp_die(
                    esc_html__('Settings configuration file was not set.', 'rundiz-downloads')
                );
                exit(1);
            }
            unset($config_values, $loader);

            $RundizSettings = new \RundizDownloads\App\Libraries\RundizSettings();
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


    }// AppTrait
}

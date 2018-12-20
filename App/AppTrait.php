<?php
/**
 * Main app trait for common works.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App;

if (!trait_exists('\\RdDownloads\\App\\AppTrait')) {
    trait AppTrait
    {


        /**
         * Main option name.
         * @var string set main option name of this plugin. the name should be english, number, underscore, or anycharacters that can be set to variable. for example: 'rd_downloads_options' will be set to $rd_downloads_options
         * @uses call this trait method $this->getOptions(); before access $rd_downloads_options in global variable.
         */
        public $main_option_name = 'rd_downloads_options';

        /**
         * All available options.
         * 
         * These options will be accessible via main option name variable. for example: options name 'the_name' can call from $rd_downloads_options['the_name'];.
         * If you want to access this property, please call to `setupAllOptions()` method first.
         * @var array set all options available for this plugin. it must be 2D array (key => default value, key2 => default value, ...)
         */
        public $all_options = [];

        /**
         * The database version.
         * 
         * If you have no tables to create on activate this plugin or don't use db for this plugin at all then set this to NULL.
         * If you have tables to create on activate this plugin then set the db version number (string) here and then write create table schema at \RdDownloads\App\Models\PluginDbStructure->get() method.
         * Do not access this property directly if not necessary, use `getDbVersion()` method instead.
         * 
         * @var string Version number of DB structure.
         * @todo [rd-downloads][routine] Set the DB version here if structure changed. Read the description above and only set this if there is any tables to create on activate this plugin.
         */
        protected $db_version = '0.1';


        /**
         * Get the DB version of this plugin.
         * 
         * @return string|null Return null if the `db_version` property is not set or not using db for this plugin, return the db version number if set.
         */
        public function getDbVersion()
        {
            if (property_exists($this, 'db_version') && !is_null($this->db_version) && is_scalar($this->db_version)) {
                return $this->db_version;
            } else {
                return null;
            }
        }// getDbVersion


        /**
         * Get all options of this plugin.
         * 
         * @return array return array value of all options.
         */
        public function getOptions()
        {
            ${$this->main_option_name} = [];
            global ${$this->main_option_name};

            $get_option = get_option($this->main_option_name);
            if ($get_option !== false) {
                ${$this->main_option_name} = maybe_unserialize($get_option);
                unset($get_option);
                return (array) ${$this->main_option_name};
            }

            unset($get_option);
            return [];
        }// getOptions


        /**
         * Save the settings from settings page, using Rundiz settings.
         * 
         * @param array $data array of submitted data in key => value
         * @return boolean return true if saved successfully. return false if not updated.
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
         */
        public function setupAllOptions()
        {
            // load config values to get settings config file.
            $loader = new \RdDownloads\App\Libraries\Loader();
            $config_values = $loader->loadConfig();
            if (is_array($config_values) && array_key_exists('rundiz_settings_config_file', $config_values)) {
                $settings_config_file = $config_values['rundiz_settings_config_file'];
            } else {
                wp_die(__('Settings configuration file was not set.', 'rd-downloads'));
                exit;
            }
            unset($config_values, $loader);

            $RundizSettings = new \RdDownloads\App\Libraries\RundizSettings();
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


    }
}
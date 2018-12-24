<?php
/**
 * Hooks into Plugins page.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Admin;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Admin\\Plugins')) {
    class Plugins implements \RdDownloads\App\Controllers\ControllerInterface
    {


        use \RdDownloads\App\AppTrait;


        /**
         * Add links to plugin actions area. For example: xxxbefore | Activate | Edit | Delete | xxxafter
         * 
         * @param array $actions current plugin actions. (including deactivate, edit).
         * @param string $plugin_file the plugin file for checking.
         * @return array return modified links
         */
        public function actionLinks($actions, $plugin_file)
        {
            static $plugin;
            
            if (!isset($plugin)) {
                $plugin = plugin_basename(RDDOWNLOADS_FILE);
            }
            
            if ($plugin == $plugin_file) {
                $link = [];
                $link['settings'] = '<a href="'.  esc_url(get_admin_url(null, 'admin.php?page=rd-downloads_settings')).'">'.__('Settings').'</a>';
                $actions = array_merge($link, $actions);
                unset($link);
            }
            
            return $actions;
        }// actionLinks


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // add filter action links. this will be displayed in actions area of plugin page. for example: xxxbefore | Activate | Edit | Delete | xxxafter
            add_filter('plugin_action_links', [$this, 'actionLinks'], 10, 5);
            // add filter to row meta. (in plugin page below description). for example: By xxx | Visit plugin site | xxxafter
            add_filter('plugin_row_meta', [$this, 'rowMeta'], 10, 2);
        }// registerHooks


        /**
         * Add links to row meta that is in Plugins page under plugin description. For example: xxxbefore | By xxx | Visit plugin site | xxxafter
         * 
         * @staticvar string $plugin the plugin file name.
         * @param array $links current meta links
         * @param string $file the plugin file name for checking.
         * @return array return modified links.
         */
        public function rowMeta($links, $file)
        {
            static $plugin;
            
            if (!isset($plugin)) {
                $plugin = plugin_basename(RDDOWNLOADS_FILE);
            }
            
            if ($plugin === $file) {
                $after_link = [];

                $configValues = $this->getOptions();
                if (is_array($configValues) && array_key_exists('rdsfw_plugin_db_version', $configValues) && is_scalar($configValues['rdsfw_plugin_db_version']) && !empty($configValues['rdsfw_plugin_db_version'])) {
                    /* translators: %s: Current DB version. */
                    $after_link[] = sprintf(__('DB version %s', 'rd-downloads'), $configValues['rdsfw_plugin_db_version']);
                }
                unset($configValues);

                $links = array_merge($links, $after_link);
                unset($after_link);
            }
            
            return $links;
        }// rowMeta


    }
}
<?php
/**
 * Hooks into Plugins page.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin;


if (!defined('ABSPATH')) {
    exit();
}


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Plugins')) {
    /**
     * Plugin class that will work on admin list plugins page.
     */
    class Plugins implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        use \RundizDownloads\App\AppTrait;


        /**
         * Add links to plugin actions area. For example: xxxbefore | Activate | Edit | Delete | xxxafter
         * 
         * @staticvar string $plugin The plugin file name.
         * @param array $actions Current plugin actions. (including deactivate, edit).
         * @param string $plugin_file The plugin file for checking.
         * @return array Return modified links
         */
        public function actionLinks($actions, $plugin_file)
        {
            static $plugin;
            
            if (!isset($plugin)) {
                $plugin = plugin_basename(RUNDIZDOWNLOADS_FILE);
            }
            
            if ($plugin === $plugin_file) {
                $link = [];
                $link['settings'] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . Settings::MENU_SLUG)) . '">' . __('Settings', 'rundiz-downloads') . '</a>';
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
         * @staticvar string $plugin The plugin file name.
         * @param array $links Current meta links
         * @param string $file The plugin file name for checking.
         * @return array Return modified links.
         */
        public function rowMeta($links, $file)
        {
            static $plugin;
            
            if (!isset($plugin)) {
                $plugin = plugin_basename(RUNDIZDOWNLOADS_FILE);
            }
            
            if ($plugin === $file) {
                $after_link = [];

                $configValues = $this->getOptions();
                if (
                    is_array($configValues) && 
                    array_key_exists('rdsfw_plugin_db_version', $configValues) && 
                    is_scalar($configValues['rdsfw_plugin_db_version']) && 
                    !empty($configValues['rdsfw_plugin_db_version'])
                ) {
                    /* translators: %s The DB version of this plugin. */
                    $after_link[] = sprintf(__('DB version %s', 'rundiz-downloads'), $configValues['rdsfw_plugin_db_version']);
                }
                unset($configValues);

                $after_link[] = '<a href="https://rundiz.com/en/donate" target="donate">' . esc_html__('Donate', 'rundiz-downloads') . '</a>';

                $links = array_merge($links, $after_link);
                unset($after_link);
            }
            
            return $links;
        }// rowMeta


    }// Plugins
}

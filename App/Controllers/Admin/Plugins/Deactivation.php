<?php
/**
 * Deactivate the plugin action.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Plugins;


if (!defined('ABSPATH')) {
    exit();
}


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Plugins\\Deactivation')) {
    /**
     * Plugin deactivation hook class.
     */
    class Deactivation implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * Deactivate the plugin.
         */
        public function deactivate()
        {
            // Do something that will be happens on deactivate plugin.
            // remove all added rewrite rules.
            flush_rewrite_rules();
        }// deactivate


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register deactivate hook
            register_deactivation_hook(RUNDIZDOWNLOADS_FILE, [$this, 'deactivate']);
        }// registerHooks


    }// Deactivation
}
